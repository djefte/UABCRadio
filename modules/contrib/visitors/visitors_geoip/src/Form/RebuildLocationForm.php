<?php

namespace Drupal\visitors_geoip\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to rebuild routes with batch.
 */
final class RebuildLocationForm extends ConfirmFormBase {

  /**
   * The route rebuild service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface
   */
  protected $service;

  /**
   * Constructs a new RebuildLocationForm.
   *
   * @param \Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface $service
   *   The route rebuild service.
   */
  public function __construct(VisitorsGeoIpRebuildLocationInterface $service) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('visitors_geoip.rebuild.location')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['slow'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('The visitors log is missing location for some visits. You can rebuild the location with the ip address.'),
      '#suffix' => '</div>',
    ];
    $form['drush'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('Available drush command: <code>drush visitors:rebuild:location</code>'),
      '#suffix' => '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $records = $this->service->getLocations();

    $operations = [];
    foreach ($records as $record) {
      $operations[] = [
        'Drupal\visitors_geoip\Form\RebuildLocationForm::batch',
        [$record],
      ];
    }
    $batch = [
      'operations' => $operations,
      'finished' => 'Drupal\visitors_geoip\Form\RebuildLocationForm::batchFinished',
      'title' => $this->t('Rebuilding visitors locations.'),
      'init_message' => $this->t('Fetching locations.'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Location rebuild has encountered an error.'),
    ];

    \batch_set($batch);
  }

  /**
   * Batch callback.
   *
   * @param string $ip_address
   *   The ip_address to rebuild.
   * @param array $context
   *   The batch context.
   */
  public static function batch(string $ip_address, array &$context) {
    $service = \Drupal::service('visitors_geoip.rebuild.location');
    $result = $service->rebuild($ip_address);
    if ($result) {
      $context['results'][] = $ip_address;
    }

    $context['finished'] = 1;
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   If the batch was successful.
   * @param array $results
   *   The results of the batch.
   * @param array $operations
   *   The operations of the batch.
   */
  public static function batchFinished(bool $success, array $results, array $operations) {
    if ($success) {
      // Here we do something meaningful with the results.
      $message = t('@count items successfully processed:', [
        '@count' => count($results),
      ]);
      \Drupal::messenger()->addMessage($message);
      \Drupal::state()->delete('visitors_geoip.rebuild.location');
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      \Drupal::messenger()->addMessage($message, 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "confirm_visitor_rebuild_location_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('visitors_geoip.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to rebuild missing locations?');
  }

}
