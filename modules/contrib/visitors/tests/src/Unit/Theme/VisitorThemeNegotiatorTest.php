<?php

namespace Drupal\Tests\visitors\Unit\Theme;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Theme\VisitorThemeNegotiator;

/**
 * Tests the CookieService class.
 *
 * @coversDefaultClass \Drupal\visitors\Theme\VisitorThemeNegotiator
 * @covers \Drupal\visitors\Theme\VisitorThemeNegotiator
 * @group visitors
 */
class VisitorThemeNegotiatorTest extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * The theme negotiator.
   *
   * @var \Drupal\visitors\Theme\VisitorThemeNegotiator
   */
  protected $themeNegotiator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->currentUser = $this->createMock('Drupal\Core\Session\AccountInterface');

    $this->themeNegotiator = new VisitorThemeNegotiator($this->configFactory, $this->currentUser);

  }

  /**
   * Tests the __construct method.
   *
   * @covers ::__construct
   */
  public function testConstructor() {

    $theme_negotiator = new VisitorThemeNegotiator($this->configFactory, $this->currentUser);
    $this->assertInstanceOf(VisitorThemeNegotiator::class, $theme_negotiator);
  }

  /**
   * Tests the applies method.
   *
   * @covers ::applies
   */
  public function testAppliesUserNotHasPermission(): void {
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');

    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('access visitors')
      ->willReturn(FALSE);

    $this->assertFalse($this->themeNegotiator->applies($route_match));
  }

  /**
   * Tests the applies method.
   *
   * @covers ::applies
   */
  public function testAppliesNoRouteObject(): void {
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');
    $route_match->expects($this->once())
      ->method('getRouteObject')
      ->willReturn(NULL);
    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('access visitors')
      ->willReturn(TRUE);

    $this->assertFalse($this->themeNegotiator->applies($route_match));
  }

  /**
   * Tests the applies method.
   *
   * @covers ::applies
   */
  public function testAppliesVisitorsRoute(): void {
    $route_object = $this->createMock('Symfony\Component\Routing\Route');
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');
    $route_match->expects($this->once())
      ->method('getRouteObject')
      ->willReturn($route_object);
    $route_match->expects($this->once())
      ->method('getRouteName')
      ->willReturn('visitors.index');
    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('access visitors')
      ->willReturn(TRUE);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
  }

  /**
   * Tests the applies method.
   *
   * @covers ::applies
   */
  public function testAppliesVisitorsPath(): void {
    $route_object = $this->createMock('Symfony\Component\Routing\Route');
    $route_object->expects($this->once())
      ->method('getPath')
      ->willReturn('/visitors');
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');
    $route_match->expects($this->once())
      ->method('getRouteObject')
      ->willReturn($route_object);
    $route_match->expects($this->once())
      ->method('getRouteName')
      ->willReturn('not_a_visitors_route');
    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('access visitors')
      ->willReturn(TRUE);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
  }

  /**
   * Tests the applies method.
   *
   * @covers ::applies
   */
  public function testAppliesNotVisitorsPath(): void {
    $route_object = $this->createMock('Symfony\Component\Routing\Route');
    $route_object->expects($this->once())
      ->method('getPath')
      ->willReturn('/not/a/visitors/path');
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');
    $route_match->expects($this->once())
      ->method('getRouteObject')
      ->willReturn($route_object);
    $route_match->expects($this->once())
      ->method('getRouteName')
      ->willReturn('not_a_visitors_route');
    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('access visitors')
      ->willReturn(TRUE);

    $this->assertFalse($this->themeNegotiator->applies($route_match));
  }

  /**
   * Tests the determineActiveTheme method.
   *
   * @covers ::determineActiveTheme
   */
  public function testDetermineActiveTheme(): void {
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');
    $system = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $visitors = $this->createMock('Drupal\Core\Config\ImmutableConfig');

    $this->configFactory->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['system.theme', $system],
        ['visitors.config', $visitors],
      ]);

    $this->assertEquals('admin', $this->themeNegotiator->determineActiveTheme($route_match));
  }

}
