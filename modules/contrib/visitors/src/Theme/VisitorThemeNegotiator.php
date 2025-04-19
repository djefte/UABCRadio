<?php

namespace Drupal\visitors\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * A negotiator for custom visitors' theme.
 */
class VisitorThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ThemeNegotiator.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $access = $this->currentUser->hasPermission('access visitors');
    if (!$access) {
      return FALSE;
    }

    $route_object = $route_match->getRouteObject();
    if (!$route_object) {
      return FALSE;
    }

    $route_name = $route_match->getRouteName();
    if (strpos($route_name, 'visitors.') === 0) {
      return TRUE;
    }
    $path = $route_object->getPath();
    if (strpos($path, '/visitors') === 0) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Get the visitors config.
    $config = $this->configFactory->get('visitors.config');
    $theme = $config->get('theme') ?: 'admin';

    return $this->configFactory->get('system.theme')->get($theme) ?: $theme;
  }

}
