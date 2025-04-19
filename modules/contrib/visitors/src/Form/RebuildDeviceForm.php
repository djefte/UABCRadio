<?php

namespace Drupal\visitors\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\visitors\VisitorsDeviceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to rebuild routes with batch.
 */
final class RebuildDeviceForm extends ConfirmFormBase {

  /**
   * The route rebuild service.
   *
   * @var \Drupal\visitors\VisitorsDeviceInterface
   */
  protected $service;

  /**
   * Constructs a new RebuildDeviceForm.
   *
   * @param \Drupal\visitors\VisitorsDeviceInterface $service
   *   The route rebuild service.
   */
  public function __construct(VisitorsDeviceInterface $service) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('visitors.device')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['slow'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('The visitors log is missing device information for some visitors. You can rebuild the device with the user agent.'),
      '#suffix' => '</div>',
    ];
    $form['drush'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('Available drush command: <code>drush visitors:rebuild:device</code>'),
      '#suffix' => '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $records = $this->service->getUniqueUserAgents();
    $operations = [];
    foreach ($records as $record) {
      $operations[] = [
        'Drupal\visitors\Form\RebuildDeviceForm::batch',
        [$record],
      ];
    }
    $batch = [
      'operations' => $operations,
      'finished' => 'Drupal\visitors\Form\RebuildDeviceForm::batchFinished',
      'title' => $this->t('Rebuilding visitors devices.'),
      'init_message' => $this->t('Fetching paths.'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Device rebuild has encountered an error.'),
    ];

    \batch_set($batch);
  }

  /**
   * Batch callback.
   *
   * @param string $user_agent
   *   The user agent to rebuild.
   * @param array $context
   *   The batch context.
   */
  public static function batch(string $user_agent, array &$context) {
    $service = \Drupal::service('visitors.device');
    $result = $service->bulkUpdate($user_agent);
    if ($result) {
      $context['results'][] = $user_agent;
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
      \Drupal::state()->delete('visitors.rebuild.device');
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
    return "confirm_visitor_rebuild_device_form";
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
  public function getQuestion() {
    return $this->t('Do you want to rebuild the devices?');
  }

}
