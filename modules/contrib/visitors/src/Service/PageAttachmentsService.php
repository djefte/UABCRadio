<?php

declare(strict_types=1);

namespace Drupal\visitors\Service;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Error;
use Drupal\visitors\VisitorsPageAttachmentsInterface;
use Drupal\visitors\VisitorsVisibilityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Visitors Page Attachments Service.
 */
class PageAttachmentsService implements VisitorsPageAttachmentsInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The visitors visibility service.
   *
   * @var \Drupal\visitors\VisitorsVisibilityInterface
   */
  protected $visibilityService;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new Page Attachments Service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   The route match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\visitors\VisitorsVisibilityInterface $visibility_service
   *   The visitors visibility service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AccountInterface $current_user,
    ModuleHandlerInterface $module_handler,
    RouteMatchInterface $route_match,
    RequestStack $request_stack,
    VisitorsVisibilityInterface $visibility_service,
    LoggerInterface $logger,
  ) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->moduleHandler = $module_handler;
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
    $this->visibilityService = $visibility_service;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function pageAttachments(array &$page) {

    $page['#cache']['tags'][] = 'user:' . $this->currentUser->id();
    $page['#cache']['contexts'][] = 'user';
    $page['#cache']['tags'][] = 'config:visitors.settings';

    $this->attachToolbar($page);

    try {
      if ($this->visibilityService->isVisible()) {
        $this->attachMetaData($page);
        $this->attachEntityCounter($page);
      }
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }
  }

  /**
   * Attach meta data to the page.
   *
   * @param array $page
   *   The page attachments array.
   */
  protected function attachMetaData(array &$page): void {
    $route = $this->routeMatch->getRouteName();
    $request = $this->requestStack->getCurrentRequest();
    $base_path = $request->getBasePath();
    $module_path = $this->moduleHandler->getModule('visitors')->getPath();

    $page['#attached']['drupalSettings']['visitors']['module'] = "$base_path/$module_path";
    $page['#attached']['drupalSettings']['visitors']['route'] = $route;
    $page['#attached']['drupalSettings']['visitors']['server'] = gethostname();
    $page['#attached']['library'][] = 'visitors/visitors';

  }

  /**
   * Visitors toolbar integration.
   *
   * @param array $page
   *   The page attachments array.
   */
  protected function attachToolbar(array &$page): void {
    $required_permissions = ['access visitors', 'access toolbar'];

    $access = AccessResult::allowedIfHasPermissions($this->currentUser, $required_permissions);
    if ($access->isAllowed()) {
      $page['#attached']['library'][] = 'visitors/menu';
    }
  }

  /**
   * Attach entity counter to the page.
   *
   * @param array $page
   *   The page attachments array.
   */
  protected function attachEntityCounter(array &$page): void {
    $route = $this->routeMatch->getRouteName();

    $route_array = explode('.', $route);
    if (count($route_array) == 3 && $route_array[0] == 'entity' && $route_array[2] == 'canonical') {
      $entity_type = $route_array[1];

      $settings = $this->configFactory->get('visitors.config');
      $entity_types = $settings->get('counter.entity_types') ?? [];
      if (!$settings->get('counter.enabled') || !in_array($entity_type, $entity_types)) {
        return;
      }

      $entity_id = $this->routeMatch->getParameter($entity_type)->id();
      $page['#attached']['drupalSettings']['visitors']['counter'] = "$entity_type:$entity_id";
    }
  }

}
