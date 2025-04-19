<?php

declare(strict_types=1);

namespace Drupal\visitors\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Form for migrating statistics to visitors.
 */
final class StatisticsMigrateForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'visitors_statistics_migrate';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Migrate statistics node view count?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): TranslatableMarkup {
    return $this->t('This will migrate the Statistics view count to Visitors, and uninstall Statistics.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('visitors.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $operations = [];
    $operations[] = [
      ['Drupal\visitors\Form\StatisticsMigrateForm', 'delete'],
      [],
    ];
    $operations[] = [
      ['Drupal\visitors\Form\StatisticsMigrateForm', 'insert'],
      [],
    ];
    $operations[] = [
      ['Drupal\visitors\Form\StatisticsMigrateForm', 'uninstallStatistics'],
      [],
    ];
    $operations[] = [
      ['Drupal\visitors\Form\StatisticsMigrateForm', 'enableVisitorsLogging'],
      [],
    ];

    // Define the batch.
    $batch = [
      'title' => t('Migrating statistics...'),
      'operations' => $operations,
    ];

    // batch_set may not be available in tests.
    if (function_exists('batch_set')) {
      // Set the batch.
      batch_set($batch);
    }
    $form_state->setRedirectUrl(Url::fromRoute('visitors.settings'));
  }

  /**
   * Delete the statistics table.
   */
  public static function delete() {
    $database = \Drupal::database();
    $database
      ->delete('visitors_counter')
      ->condition('entity_type', 'node')
      ->execute();
  }

  /**
   * Insert the statistics table into the visitors table.
   */
  public static function insert() {
    $database = \Drupal::database();

    $query = $database->select('node_counter', 's');
    $query->addExpression("'node'", 'entity_type');
    $query->addField('s', 'nid', 'entity_id');
    $query->addField('s', 'totalcount', 'total');
    $query->addField('s', 'daycount', 'today');
    $query->addField('s', 'timestamp', 'timestamp');

    $insert = $database->insert('visitors_counter')
      ->fields([
        'entity_id',
        'total',
        'today',
        'timestamp',
        'entity_type',
      ])
      ->from($query);

    $insert->execute();
  }

  /**
   * Uninstall the Statistics module.
   */
  public static function uninstallStatistics() {
    $module_installer = \Drupal::service('module_installer');
    if ($module_installer->uninstall(['statistics'])) {
      \Drupal::messenger()->addStatus(t('The Statistics module has been uninstalled.'));
    }
    else {
      \Drupal::messenger()->addError(t('The Statistics module could not be uninstalled.'));
    }
  }

  /**
   * Enable visitors logging.
   */
  public static function enableVisitorsLogging() {
    $config = \Drupal::configFactory()->getEditable('visitors.config');
    $config->set('counter.enabled', TRUE);
    $config->save();
  }

}
