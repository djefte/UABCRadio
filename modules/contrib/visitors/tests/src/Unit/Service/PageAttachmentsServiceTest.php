<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\PageAttachmentsService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the PageAttachmentsService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\PageAttachmentsService
 *
 * @group visitors
 */
class PageAttachmentsServiceTest extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The service.
   *
   * @var \Drupal\visitors\Service\PageAttachmentsService
   */
  protected $service;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentRouteMatch;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The visitors visibility service.
   *
   * @var \Drupal\visitors\VisitorsVisibilityInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $visibilityService;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheContextsManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->currentUser = $this->createMock('Drupal\Core\Session\AccountInterface');
    $container->set('current_user', $this->currentUser);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->currentRouteMatch = $this->createMock('Drupal\Core\Routing\ResettableStackedRouteMatchInterface');
    $container->set('current_route_match', $this->currentRouteMatch);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $this->visibilityService = $this->createMock('Drupal\visitors\VisitorsVisibilityInterface');
    $container->set('visitors.visibility', $this->visibilityService);

    $this->logger = $this->createMock('Psr\Log\LoggerInterface');
    $container->set('logger.factory', $this->logger);

    $this->cacheContextsManager = $this->createMock('Drupal\Core\Cache\Context\CacheContextsManager');
    $container->set('cache_contexts_manager', $this->cacheContextsManager);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');

    $this->service = new PageAttachmentsService($this->configFactory, $this->currentUser, $this->moduleHandler, $this->currentRouteMatch, $this->requestStack, $this->visibilityService, $this->logger);

  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $service = new PageAttachmentsService($this->configFactory, $this->currentUser, $this->moduleHandler, $this->currentRouteMatch, $this->requestStack, $this->visibilityService, $this->logger);
    $this->assertInstanceOf(PageAttachmentsService::class, $service);
  }

  /**
   * Tests the pageAttachments method.
   *
   * @covers ::pageAttachments
   */
  public function testPageAttachments() {

    $this->cacheContextsManager->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);

    $this->visibilityService->expects($this->once())
      ->method('isVisible')
      ->willReturn(TRUE);

    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getBasePath')
      ->willReturn('/');
    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $module = $this->createMock('Drupal\Core\Extension\Extension');
    $module->expects($this->once())
      ->method('getPath')
      ->willReturn('modules/custom/visitors');
    $this->moduleHandler->expects($this->once())
      ->method('getModule')
      ->with('visitors')
      ->willReturn($module);

    $this->currentRouteMatch->expects($this->exactly(2))
      ->method('getRouteName')
      ->willReturn('visitors.index');

    $page = [];
    $this->service->pageAttachments($page);

    $this->assertCount(2, $page);

    $this->assertArrayHasKey('#cache', $page);
  }

  /**
   * Tests the pageAttachments method when visibility is false.
   *
   * @covers ::pageAttachments
   */
  public function testPageAttachmentsWithException() {
    $this->cacheContextsManager->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);

    $this->visibilityService->expects($this->once())
      ->method('isVisible')
      ->willThrowException(new \Exception('Visibility check failed'));

    $page = [];
    $this->service->pageAttachments($page);

    $this->assertArrayHasKey('#cache', $page);
  }

  /**
   * Tests the attachToolbar method.
   *
   * @covers ::attachToolbar
   */
  public function testAttachToolbar() {
    $this->cacheContextsManager->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);

    $this->currentUser->expects($this->exactly(2))
      ->method('hasPermission')
      ->willReturnMap([
        ['access visitors', TRUE],
        ['access toolbar', TRUE],
      ]);

    $reflection = new \ReflectionMethod($this->service, 'attachToolbar');
    $reflection->setAccessible(TRUE);

    $page = [];
    $access = $reflection->invokeArgs($this->service, [&$page]);

    $this->assertArrayHasKey('#attached', $page);
    $this->assertArrayHasKey('library', $page['#attached']);
    $this->assertContains('visitors/menu', $page['#attached']['library']);
  }

  /**
   * Tests the attachToolbar method.
   *
   * @covers ::attachToolbar
   */
  public function testAttachToolbarNoVisitorsAccess() {
    $this->cacheContextsManager->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);

    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->willReturnMap([
        ['access visitors', FALSE],
        ['access toolbar', TRUE],
      ]);

    $reflection = new \ReflectionMethod($this->service, 'attachToolbar');
    $reflection->setAccessible(TRUE);

    $page = [];
    $access = $reflection->invokeArgs($this->service, [&$page]);

    $this->assertCount(0, $page);
  }

  /**
   * Tests the attachToolbar method.
   *
   * @covers ::attachToolbar
   */
  public function testAttachToolbarNoToolbar() {
    $this->cacheContextsManager->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);

    $this->currentUser->expects($this->exactly(2))
      ->method('hasPermission')
      ->willReturnMap([
        ['access visitors', TRUE],
        ['access toolbar', FALSE],
      ]);

    $reflection = new \ReflectionMethod($this->service, 'attachToolbar');
    $reflection->setAccessible(TRUE);

    $page = [];
    $access = $reflection->invokeArgs($this->service, [&$page]);

    $this->assertCount(0, $page);
  }

  /**
   * Tests the attachMetaData method.
   *
   * @covers ::attachMetaData
   */
  public function testAttachMetaData() {

    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getBasePath')
      ->willReturn('');

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $module = $this->createMock('Drupal\Core\Extension\Extension');
    $module->expects($this->once())
      ->method('getPath')
      ->willReturn('modules/custom/visitors');
    $this->moduleHandler->expects($this->once())
      ->method('getModule')
      ->with('visitors')
      ->willReturn($module);

    $this->currentRouteMatch->expects($this->exactly(1))
      ->method('getRouteName')
      ->willReturn('visitors.index');

    // Make attachMetaData public for testing.
    $reflection = new \ReflectionMethod($this->service, 'attachMetaData');
    $reflection->setAccessible(TRUE);

    $page = [];
    $reflection->invokeArgs($this->service, [&$page]);

    $this->assertArrayHasKey('#attached', $page);
    $this->assertArrayHasKey('drupalSettings', $page['#attached']);
    $this->assertArrayHasKey('visitors', $page['#attached']['drupalSettings']);
    $this->assertArrayHasKey('module', $page['#attached']['drupalSettings']['visitors']);
    $this->assertEquals('/modules/custom/visitors', $page['#attached']['drupalSettings']['visitors']['module']);
  }

  /**
   * Tests the attachEntityCounter method.
   *
   * @covers ::attachEntityCounter
   */
  public function testAttachEntityCounter() {

    $this->currentRouteMatch->expects($this->once())
      ->method('getRouteName')
      ->willReturn('entity.node.canonical');

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);
    $this->settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['counter.entity_types', ['node']],
        ['counter.enabled', TRUE],
      ]);

    $node = $this->createMock('Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $this->currentRouteMatch->expects($this->once())
      ->method('getParameter')
      ->with('node')
      ->willReturn($node);

    // Make attachEntityCounter public for testing.
    $reflection = new \ReflectionMethod($this->service, 'attachEntityCounter');
    $reflection->setAccessible(TRUE);

    $page = [];
    $reflection->invokeArgs($this->service, [&$page]);

    $this->assertArrayHasKey('#attached', $page);
    $this->assertArrayHasKey('drupalSettings', $page['#attached']);
    $this->assertArrayHasKey('visitors', $page['#attached']['drupalSettings']);
    $this->assertArrayHasKey('counter', $page['#attached']['drupalSettings']['visitors']);
    $this->assertEquals('node:1', $page['#attached']['drupalSettings']['visitors']['counter']);
  }

  /**
   * Tests the attachEntityCounter method.
   *
   * @covers ::attachEntityCounter
   */
  public function testAttachEntityCounterNotConfigured() {

    $this->currentRouteMatch->expects($this->once())
      ->method('getRouteName')
      ->willReturn('entity.user.canonical');

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);
    $this->settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['counter.entity_types', ['node']],
        ['counter.enabled', TRUE],
      ]);

    // Make attachEntityCounter public for testing.
    $reflection = new \ReflectionMethod($this->service, 'attachEntityCounter');
    $reflection->setAccessible(TRUE);

    $page = [];
    $reflection->invokeArgs($this->service, [&$page]);

    $this->assertCount(0, $page);
  }

}
