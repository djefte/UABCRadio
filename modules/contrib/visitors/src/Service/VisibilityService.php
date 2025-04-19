<?php

namespace Drupal\visitors\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\user\UserDataInterface;
use Drupal\visitors\VisitorsVisibilityInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for checking visitors visibility.
 */
class VisibilityService implements VisitorsVisibilityInterface {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $path;

  /**
   * The alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcher
   */
  protected $pathMatcher;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The status codes.
   *
   * @var array
   */
  protected $statusCodes;

  /**
   * Constructs a new VisibilityService.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Path\CurrentPathStack $path_current
   *   The current path.
   * @param \Drupal\path_alias\AliasManagerInterface|null $alias_manager
   *   The alias manager.
   * @param \Drupal\Core\Path\PathMatcher $path_matcher
   *   The path matcher.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CurrentPathStack $path_current, ?AliasManagerInterface $alias_manager, PathMatcher $path_matcher, UserDataInterface $user_data, RequestStack $request_stack, AccountProxyInterface $current_user) {

    $this->config = $config_factory->get('visitors.config');
    $this->path = $path_current;
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->userData = $user_data;
    $this->request = $request_stack->getCurrentRequest();
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible(): bool {
    if ($this->config->get('disable_tracking')) {
      return FALSE;
    }

    if (!$this->user($this->currentUser)) {
      return FALSE;
    }

    return $this->page();
  }

  /**
   * {@inheritdoc}
   */
  public function user(AccountInterface $account): bool {
    $enabled = FALSE;
    if ($this->config->get('visibility.exclude_user1') && $account->id() == 1) {
      return FALSE;
    }

    // Is current user a member of a role that should be tracked?
    if ($this->roles($account)) {

      // Use the user's block visibility setting, if necessary.
      $visibility_user_account_mode = $this->config->get('visibility.user_account_mode');
      if ($visibility_user_account_mode != 0) {
        $user_data_visitors = $this->userData->get('visitors', $account->id());
        if ($account->id() && isset($user_data_visitors['user_account_users'])) {
          $enabled = $user_data_visitors['user_account_users'];
        }
        else {
          $enabled = ($visibility_user_account_mode == 1);
        }
      }
      else {
        $enabled = TRUE;
      }

    }

    return $enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function page(): bool {
    $page_match = NULL;

    $visibility_request_path_mode = $this->config->get('visibility.request_path_mode');
    $visibility_request_path_pages = $this->config->get('visibility.request_path_pages');

    // Match path if necessary.
    if (empty($visibility_request_path_pages)) {
      $page_match = TRUE;

      return $page_match;
    }
    // Convert path to lowercase. This allows comparison of the same path
    // with different case. Ex: /Page, /page, /PAGE.
    $pages = mb_strtolower($visibility_request_path_pages);

    // Compare the lowercase path alias (if any) and internal path.
    $path = $this->path->getPath();
    $path_alias = $this->getPathAlias($path);

    $alias_match = $this->pathMatcher->matchPath($path_alias, $pages);
    $path_match = $this->pathMatcher->matchPath($path, $pages);
    $page_match = $alias_match || ($path != $path_alias && $path_match);

    // When $visibility_request_path_mode has a value of 0, the tracking
    // code is displayed on all pages except those listed in $pages. When
    // set to 1, it is displayed only on those pages listed in $pages.
    $page_match = !($visibility_request_path_mode xor $page_match);

    return $page_match;
  }

  /**
   * Get the path alias.
   */
  protected function getPathAlias($path) {
    $path_alias = mb_strtolower($path);

    if ($this->aliasManager) {
      $path_alias = $this->aliasManager->getAliasByPath($path);
      if (!empty($path_alias)) {
        $path_alias = mb_strtolower($path_alias);
      }
    }

    return $path_alias;
  }

  /**
   * {@inheritdoc}
   */
  public function roles(AccountInterface $account): bool {
    $enabled = $visibility_user_role_mode = $this->config->get('visibility.user_role_mode') ?? 0;
    $user_role_roles = $this->config->get('visibility.user_role_roles');

    if (empty($user_role_roles)) {
      return TRUE;
    }
    // One or more roles are selected.
    foreach (array_values($account->getRoles()) as $user_role) {
      // Is the current user a member of one of these roles?
      if (in_array($user_role, $user_role_roles)) {
        // Current user is a member of a role that should be tracked/excluded
        // from tracking.
        $enabled = !$visibility_user_role_mode;
        break;
      }
    }

    return $enabled;
  }

}
