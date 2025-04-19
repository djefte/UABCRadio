<?php

declare(strict_types=1);

namespace Drupal\visitors\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\visitors\VisitorsRebuildIpAddressInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to convert IP address with bath.
 */
final class RebuildIpAddressForm extends ConfirmFormBase {
  /**
   * The IP address rebuild service.
   *
   * @var \Drupal\visitors\VisitorsRebuildIpAddressInterface
   */
  protected $service;

  /**
   * Constructs a new RebuildIpAddressForm.
   *
   * @param \Drupal\visitors\VisitorsRebuildIpAddressInterface $service
   *   The route rebuild service.
   */
  public function __construct(VisitorsRebuildIpAddressInterface $service) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('visitors.rebuild.ip_address')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['slow'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('The visitors log may have some addresses in the legacy, IPv4 only, format. You can convert the IP address to the new format supporting IPv4 and IPv6.'),
      '#suffix' => '</div>',
    ];
    $form['drush'] = [
      '#prefix' => '<div class="container">',
      '#markup' => $this->t('Available drush command: <code>drush visitors:rebuild:ip-address</code>'),
      '#suffix' => '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $records = $this->service->getIpAddresses();
    $operations = [];
    foreach ($records as $record) {
      $operations[] = [
        'Drupal\visitors\Form\RebuildIpAddressForm::batch',
        [$record->visitors_ip],
      ];
    }
    $batch = [
      'operations' => $operations,
      'finished' => 'Drupal\visitors\Form\RebuildIpAddressForm::batchFinished',
      'title' => $this->t('Rebuilding visitors IP addresses.'),
      'init_message' => $this->t('Fetching ip addresses.'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('IP address rebuild has encountered an error.'),
    ];

    \batch_set($batch);
  }

  /**
   * Batch callback.
   *
   * @param string $ip_address
   *   The ip address to rebuild.
   * @param array $context
   *   The batch context.
   */
  public static function batch(string $ip_address, array &$context) {
    $service = \Drupal::service('visitors.rebuild.ip_address');
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
      $message = t('@success items updates processed.', [
        '@success' => count($results),
      ]);
      \Drupal::messenger()->addMessage($message);
      \Drupal::state()->delete('visitors.rebuild.ip_address');
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
    return "confirm_visitor_rebuild_ip_address_form";
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
    return $this->t('Do you want to rebuild the IP Address?');
  }

}
