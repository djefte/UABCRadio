<?php

namespace Drupal\visitors\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\visitors\VisitorsRebuildRouteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to rebuild routes with batch.
 */
final class RebuildRouteForm extends ConfirmFormBase {

  /**
   * The route rebuild service.
   *
   * @var \Drupal\visitors\VisitorsRebuildRouteInterface
   */
  protected $service;

  /**
   * Constructs a new RebuildRouteForm.
   *
   * @param \Drupal\visitors\VisitorsRebuildRouteInterface $service
   *   The route rebuild service.
   */
  public function __construct(VisitorsRebuildRouteInterface $service) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('visitors.rebuild.route')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['slow'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('The visitors log is missing routes for some pages. You can rebuild the routes with the path.'),
      '#suffix' => '</div>',
    ];
    $form['drush'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('Available drush command: <code>drush visitors:rebuild:routes</code>'),
      '#suffix' => '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $records = $this->service->getPaths();
    $operations = [];
    foreach ($records as $record) {
      $operations[] = [
        'Drupal\visitors\Form\RebuildRouteForm::batch',
        [$record->visitors_path],
      ];
    }
    $batch = [
      'operations' => $operations,
      'finished' => 'Drupal\visitors\Form\RebuildRouteForm::batchFinished',
      'title' => $this->t('Rebuilding visitors routes.'),
      'init_message' => $this->t('Fetching paths.'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Route rebuild has encountered an error.'),
    ];

    \batch_set($batch);
  }

  /**
   * Batch callback.
   *
   * @param string $path
   *   The path to rebuild.
   * @param array $context
   *   The batch context.
   */
  public static function batch(string $path, array &$context) {
    $service = \Drupal::service('visitors.rebuild.route');
    $result = $service->rebuild($path);
    if ($result) {
      $context['results'][] = $path;
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
      \Drupal::state()->delete('visitors.rebuild.route');
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
    return "confirm_visitor_rebuild_route_form";
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
    return $this->t('Do you want to rebuild the routes?');
  }

}
