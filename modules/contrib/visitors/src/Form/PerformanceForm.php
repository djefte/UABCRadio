<?php

declare(strict_types=1);

namespace Drupal\visitors\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;

/**
 * Form to migrate the performance data.
 */
final class PerformanceForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visitors_performance_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('This will migrate the performance data from the old table. Are you sure?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('visitors.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $batch = [
      'operations' => [

        ['Drupal\visitors\Form\PerformanceForm::batch', []],
        ['Drupal\visitors\Form\PerformanceForm::dropTable', []],
      ],
      'title' => $this->t('Migrating performance data.'),
    ];

    \batch_set($batch);
  }

  /**
   * Batch update performance data.
   *
   * @param array $context
   *   The batch context.
   */
  public static function batch(array &$context) {
    $time = \Drupal::service('datetime.time');
    $database = \Drupal::database();

    $start_time = $time->getRequestTime();

    if (!isset($context['sandbox']['total'])) {
      $select = $database->select('visitors_performance', 'vp');
      $select->fields('vp', ['visitors_id']);
      $select->join('visitors', 'v', 'vp.visitors_id = v.visitors_id');
      $select->condition('v.pf_total', NULL, 'IS NOT NULL');
      $total = $select->countQuery()->execute()->fetchField();
      $context['sandbox']['total'] = $total;
      $context['sandbox']['current'] = 0;

      if ($total == 0) {
        $context['finished'] = 1;
        return;
      }
    }

    $select = $database->select('visitors_performance', 'vp');
    $select->fields('vp');
    $select->join('visitors', 'v', 'vp.visitors_id = v.visitors_id');
    $select->condition('v.pf_total', NULL, 'IS NOT NULL');
    $select->range(0, 1000);

    $rows = $select->execute()->fetchAll();

    do {
      $row = array_shift($rows);

      $update = $database->update('visitors')
        ->fields([
          'pf_total' => $row->total,
          'pf_network' => $row->network,
          'pf_server' => $row->server,
          'pf_transfer' => $row->transfer,
          'pf_dom_processing' => $row->dom_processing,
          'pf_dom_complete' => $row->dom_complete,
          'pf_on_load' => $row->on_load,
        ])
        ->condition('visitors_id', $row->visitors_id)
        ->execute();

      $delete = $database->delete('visitors_performance')
        ->condition('visitors_id', $row->id)
        ->execute();

      $context['sandbox']['current'] += 1;
      $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['total'];
      $now = $time->getCurrentTime();
      $diff = $now - $start_time;
    } while ($diff < 30 && !empty($rows));

  }

  /**
   * Drop the performance table.
   */
  public static function dropTable() {
    $database = \Drupal::database();
    $schema = $database->schema();
    $schema->dropTable('visitors_performance');
  }

}
