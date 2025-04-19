<?php

namespace Drupal\visitors\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for the statistics migration form.
 */
class StatisticsMigrateController extends ControllerBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs the counter service.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(ModuleHandlerInterface $module_handler, FormBuilderInterface $form_builder, MessengerInterface $messenger) {
    $this->moduleHandler = $module_handler;
    $this->formBuilder = $form_builder;
    $this->setMessenger($messenger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('form_builder'),
      $container->get('messenger'),
    );
  }

  /**
   * Migrate statistics.
   */
  public function migrate() {

    $statistics_is_installed = $this->moduleHandler->moduleExists('statistics');
    if (!$statistics_is_installed) {
      $this->messenger()->addWarning('The Statistics module is not installed.');
      $url = Url::fromRoute('visitors.settings');
      $response = new RedirectResponse($url->toString());
      return $response->send();
    }

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\StatisticsMigrateForm');
    return $form;
  }

}
