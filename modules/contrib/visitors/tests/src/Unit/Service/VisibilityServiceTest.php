<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserDataInterface;
use Drupal\visitors\Service\VisibilityService;
use Drupal\visitors\VisitorsVisibilityInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests the VisibilityService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\VisibilityService
 * @uses \Drupal\visitors\Service\VisibilityService
 * @group visitors
 */
class VisibilityServiceTest extends UnitTestCase {

  /**
   * The mocked config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The mocked current path stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentPathStack;

  /**
   * The mocked alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $aliasManager;

  /**
   * The mocked path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcher|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $pathMatcher;

  /**
   * The mocked user data service.
   *
   * @var \Drupal\user\UserDataInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $userData;

  /**
   * The visibility service under test.
   *
   * @var \Drupal\visitors\Service\VisibilityService
   */
  protected $visibilityService;

  /**
   * The mocked request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stack;

  /**
   * The mocked account proxy.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $accountProxy;

  /**
   * The visibility service under test.
   *
   * @var \Drupal\visitors\Service\VisibilityService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->currentPathStack = $this->createMock(CurrentPathStack::class);
    $this->aliasManager = $this->createMock(AliasManagerInterface::class);
    $this->pathMatcher = $this->createMock(PathMatcher::class);
    $this->userData = $this->createMock(UserDataInterface::class);
    $this->stack = $this->createMock(RequestStack::class);
    $this->accountProxy = $this->createMock(AccountProxyInterface::class);

    $this->service = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

  }

  /**
   * Tests the user() method.
   *
   * @dataProvider userDataProvider
   * @covers ::user
   * @covers ::__construct
   */
  public function testUser($accountRoles, $visibilityConfig, $userData, $user_id, $account_mode, $expectedResult) {
    $account = $this->createMock(AccountInterface::class);
    $account->expects($this->any())
      ->method('getRoles')
      ->willReturn($accountRoles);
    $account->expects($this->any())
      ->method('id')
      ->willReturn($user_id);

    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['visibility.exclude_user1', TRUE],
        ['visibility.user_role_mode', $visibilityConfig],
        ['visibility.user_role_roles', $accountRoles],
        ['visibility.user_account_mode', $account_mode],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $this->userData->expects($this->any())
      ->method('get')
      ->with('visitors', $account->id())
      ->willReturn($userData);

    $this->visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $this->visibilityService->user($account);
    $this->assertEquals($expectedResult, $result);
  }

  /**
   * Provides test data for the testUser() method.
   */
  public static function userDataProvider() {
    return [
      // User is a member of a tracked role and user account mode is 0.
      [
        ['anonymous', 'authenticated', 'editor'],
        0,
        [],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User is a member of a tracked role and user account mode is 1.
      [
        ['anonymous', 'authenticated', 'editor'],
        1,
        [],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User is a member of a tracked role and user account mode is 2.
      [
        ['anonymous', 'authenticated', 'editor'],
        2,
        [],
        2,
        VisitorsVisibilityInterface::USER_OPT_OUT,
        FALSE,
      ],
      // User is not a member of a tracked role and user account mode is 0.
      [
        ['anonymous', 'authenticated'],
        0,
        [],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User is not a member of a tracked role and user account mode is 1.
      [
        ['anonymous', 'authenticated'],
        1,
        [],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User is not a member of a tracked role and user account mode is 2.
      [
        ['anonymous', 'authenticated'],
        2,
        [],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User is a member of a tracked role and user account mode is 1, and user
      // data has 'user_account_users' set to TRUE.
      [
        ['anonymous', 'authenticated', 'editor'],
        1,
        ['user_account_users' => TRUE],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User is a member of a tracked role and user account mode is 1, and user
      // data has 'user_account_users' set to FALSE.
      [
        ['anonymous', 'authenticated', 'editor'],
        1,
        ['user_account_users' => FALSE],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      // User1 is excluded.
      [
        ['anonymous', 'authenticated', 'editor'],
        1,
        ['user_account_users' => FALSE],
        1,
        VisitorsVisibilityInterface::USER_OPT_IN,
        FALSE,
      ],
      [
        ['anonymous', 'authenticated', 'editor'],
        0,
        ['user_account_users' => TRUE],
        2,
        VisitorsVisibilityInterface::USER_OPT_IN,
        TRUE,
      ],
      // No personalization.
      [
        ['anonymous', 'authenticated', 'editor'],
        0,
        [],
        2,
        VisitorsVisibilityInterface::USER_NO_PERSONALIZATION,
        TRUE,
      ],
    ];
  }

  /**
   * Tests the page() method.
   *
   * @dataProvider pageDataProvider
   * @covers ::page
   */
  public function testPage($visibilityConfig, $pages, $path, $aliasPath, $pathMatcherResult, $expectedResult) {
    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['visibility.request_path_mode', $visibilityConfig],
        ['visibility.request_path_pages', $pages],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $this->currentPathStack->expects($this->any())
      ->method('getPath')
      ->willReturn($path);

    $this->aliasManager->expects($this->any())
      ->method('getAliasByPath')
      ->with($path)
      ->willReturn($aliasPath);

    $this->pathMatcher->expects($this->any())
      ->method('matchPath')
      ->willReturnMap([
        [$aliasPath, $pages, $pathMatcherResult],
        [$path, $pages, $pathMatcherResult],
      ]);

    $this->visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $this->visibilityService->page();
    $this->assertEquals($expectedResult, $result);
  }

  /**
   * Provides test data for the testPage() method.
   */
  public static function pageDataProvider() {
    return [
      [
        VisitorsVisibilityInterface::PATH_EXCLUDE,
        'page/*',
        '/page/1',
        '/alias1',
        TRUE,
        FALSE,
      ],
      [
        VisitorsVisibilityInterface::PATH_EXCLUDE,
        '',
        '/page/1',
        '/alias1',
        FALSE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_INCLUDE,
        'page/*',
        '/page/1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_INCLUDE,
        '',
        '/page/1',
        '/alias1',
        FALSE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_EXCLUDE,
        '',
        '/page/1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_INCLUDE,
        '',
        '/page/1',
        '/alias1',
        FALSE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_EXCLUDE,
        '',
        '/page/1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_INCLUDE,
        '',
        '/page/1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      [
        VisitorsVisibilityInterface::PATH_EXCLUDE,
        '',
        '/page/1',
        '/alias1',
        TRUE,
        TRUE,
      ],
    ];
  }

  /**
   * Tests the page() method.
   *
   * @dataProvider pageDataProviderNoAlias
   * @covers ::page
   */
  public function testPageNoAlias($visibilityConfig, $path, $aliasPath, $pathMatcherResult, $expectedResult) {
    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['visibility.request_path_mode', $visibilityConfig],
        ['visibility.request_path_pages', ''],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $this->currentPathStack->expects($this->any())
      ->method('getPath')
      ->willReturn($path);

    $this->pathMatcher->expects($this->any())
      ->method('matchPath')
      ->with($aliasPath, $pathMatcherResult)
      ->willReturn($pathMatcherResult);

    $this->visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      NULL,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $this->visibilityService->page();
    $this->assertEquals($expectedResult, $result);
  }

  /**
   * Provides test data for the testPage() method.
   */
  public static function pageDataProviderNoAlias() {
    return [
      // Visibility request path mode is 0, page match is TRUE.
      [
        0,
        '/page1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      // Visibility request path mode is 0, page match is FALSE.
      [
        0,
        '/page1',
        '/alias1',
        FALSE,
        TRUE,
      ],
      // Visibility request path mode is 1, page match is TRUE.
      [
        1,
        '/page1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      // Visibility request path mode is 1, page match is FALSE.
      [
        1,
        '/page1',
        '/alias1',
        FALSE,
        TRUE,
      ],
      // Visibility request path mode is 2, page match is TRUE.
      [
        2,
        '/page1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      // Visibility request path mode is 2, page match is FALSE.
      [
        2,
        '/page1',
        '/alias1',
        FALSE,
        TRUE,
      ],
      // Visibility request path mode is 0, page match is TRUE,
      // PHP module exists.
      [
        0,
        '/page1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      // Visibility request path mode is 1, page match is TRUE,
      // PHP module exists.
      [
        1,
        '/page1',
        '/alias1',
        TRUE,
        TRUE,
      ],
      // Visibility request path mode is 2, page match is TRUE,
      // PHP module exists.
      [
        2,
        '/page1',
        '/alias1',
        TRUE,
        TRUE,
      ],
    ];
  }

  /**
   * Tests the roles() method.
   *
   * @dataProvider rolesDataProvider
   * @covers ::roles
   */
  public function testRoles($accountRoles, $visibilityConfig, $config_roles, $expectedResult) {
    $this->accountProxy->expects($this->any())
      ->method('getRoles')
      ->willReturn($accountRoles);

    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['visibility.user_role_mode', $visibilityConfig],
        ['visibility.user_account_mode', $visibilityConfig],
        ['visibility.user_role_roles', $config_roles],
      ]);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $this->visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $this->visibilityService->roles($this->accountProxy);
    $this->assertEquals($expectedResult, $result);
  }

  /**
   * Provides test data for the testRoles() method.
   */
  public static function rolesDataProvider() {
    return [
      // User is a member of a tracked role and user role mode is 0.
      [
        ['anonymous', 'authenticated', 'editor'],
        0,
        ['editor'],
        TRUE,
      ],
      // User is a member of a tracked role and user role mode is 1.
      [
        ['anonymous', 'authenticated', 'editor'],
        1,
        [],
        TRUE,
      ],
      // User is a member of a tracked role and user role mode is 2.
      [
        ['anonymous', 'authenticated', 'editor'],
        2,
        [],
        TRUE,
      ],
      // User is not a member of a tracked role and user role mode is 0.
      [
        ['anonymous', 'authenticated'],
        0,
        [],
        TRUE,
      ],
      // User is not a member of a tracked role and user role mode is 1.
      [
        ['anonymous', 'authenticated'],
        1,
        [],
        TRUE,
      ],
      // User is not a member of a tracked role and user role mode is 2.
      [
        ['anonymous', 'authenticated'],
        2,
        [],
        TRUE,
      ],
    ];
  }

  /**
   * Tests the trackingDisabled.
   *
   * @covers ::isVisible
   */
  public function testIsVisibleTrackingDisabled() {
    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->once())
      ->method('get')
      ->with('disable_tracking')
      ->willReturn(TRUE);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $visibilityService->isVisible();
    $this->assertFalse($result);
  }

  /**
   * Tests the trackingDisabled.
   *
   * @covers ::isVisible
   */
  public function testIsVisible() {
    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['disable_tracking', FALSE],
        ['visibility.exclude_user1', TRUE],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $attributes = $this->createMock('\Symfony\Component\HttpFoundation\ParameterBag');
    $attributes->expects($this->any())
      ->method('get')
      ->with('exception')
      ->willReturn(NULL);
    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->attributes = $attributes;
    $this->stack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $this->accountProxy->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $visibilityService->isVisible();
    $this->assertFalse($result);
  }

  /**
   * Tests the trackingDisabled.
   *
   * @covers ::isVisible
   */
  public function testIsVisiblePage() {
    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['disable_tracking', FALSE],
        ['visibility.exclude_user1', TRUE],
        ['visibility.user_role_mode', ['anonymous', 'authenticated']],
        ['visibility.user_account_mode', 0],
        ['visibility.request_path_mode', 0],
        ['visibility.request_path_pages', ''],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);

    $attributes = $this->createMock('\Symfony\Component\HttpFoundation\ParameterBag');
    $attributes->expects($this->any())
      ->method('get')
      ->with('exception')
      ->willReturn(NULL);
    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->attributes = $attributes;
    $this->stack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $this->accountProxy->expects($this->once())
      ->method('id')
      ->willReturn(2);

    $visibilityService = new VisibilityService(
      $this->configFactory,
      $this->currentPathStack,
      $this->aliasManager,
      $this->pathMatcher,
      $this->userData,
      $this->stack,
      $this->accountProxy
    );

    $result = $visibilityService->isVisible();
    $this->assertTrue($result);
  }

  /**
   * Tests the getPathAlias method.
   *
   * @covers ::getPathAlias
   */
  public function testGetPathAlias(): void {

    $method = new \ReflectionMethod($this->service, 'getPathAlias');
    $method->setAccessible(TRUE);

    $path = '/node/1';
    $alias = '/blog/bluegeek9/1';

    $this->aliasManager->expects($this->once())
      ->method('getAliasByPath')
      ->with($path)
      ->willReturn($alias);

    $result = $method->invokeArgs($this->service, [$path]);
    $this->assertEquals($alias, $result);
  }

}
