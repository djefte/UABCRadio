<?php

namespace Drupal\visitors\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;

/**
 * Controller for the statistics migration form.
 */
class PerformanceController extends ControllerBase {

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs the counter service.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(Connection $database, FormBuilderInterface $form_builder, MessengerInterface $messenger) {
    $this->database = $database;
    $this->formBuilder = $form_builder;
    $this->setMessenger($messenger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('form_builder'),
      $container->get('messenger'),
    );
  }

  /**
   * Migrate statistics.
   */
  public function migrate() {
    $schema = $this->database->schema();
    $visitors_performance_exists = $schema->tableExists('visitors_performance');
    if (!$visitors_performance_exists) {
      $this->messenger()->addWarning('Performance table does not exist. No data to migrate.');
      $url = Url::fromRoute('visitors.settings');
      $response = new RedirectResponse($url->toString());
      return $response->send();
    }

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\PerformanceForm');
    return $form;
  }

}
