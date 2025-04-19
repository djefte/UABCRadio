<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

if (!defined('REQUIREMENT_WARNING')) {
  define('REQUIREMENT_WARNING', 'warning');
}
if (!defined('REQUIREMENT_OK')) {
  define('REQUIREMENT_OK', 'ok');
}

/**
 * Tests visitors_requirements().
 *
 * @group visitors
 */
class HookRequirementsTest extends UnitTestCase {

  /**
   * The device service.
   *
   * @var \Drupal\visitors\VisitorsDeviceInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $device;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $state;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $pathValidator;

  /**
   * The unrouted URL assembler service.
   *
   * @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $unroutedUrlAssembler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->device = $this->createMock('Drupal\visitors\VisitorsDeviceInterface');
    $container->set('visitors.device', $this->device);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->pathValidator = $this->createMock('Drupal\Core\Path\PathValidatorInterface');
    $container->set('path.validator', $this->pathValidator);

    $this->unroutedUrlAssembler = $this->createMock('Drupal\Core\Utility\UnroutedUrlAssemblerInterface');
    $container->set('unrouted_url_assembler', $this->unroutedUrlAssembler);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_requirements().
   */
  public function testUpdate(): void {

    $requirements = visitors_requirements('update');
    $this->assertIsArray($requirements);
    $this->assertCount(0, $requirements);
  }

  /**
   * Tests visitors_requirements().
   */
  public function testNoIssues(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(FALSE);
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);

    $requirements = visitors_requirements('runtime');
    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
  }

  /**
   * Tests visitors_requirements().
   */
  public function testMissingDeviceLibrary(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(FALSE);
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(FALSE);

    $requirements = visitors_requirements('runtime');
    $this->assertIsArray($requirements);
    $this->assertCount(2, $requirements);
  }

  /**
   * Tests visitors_requirements().
   */
  public function testRebuildIpAddresses(): void {
    $this->state->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['visitors.rebuild.route', FALSE, FALSE],
        ['visitors.rebuild.ip_address', FALSE, TRUE],
      ]);

    $status_report_url = $this->createMock('Drupal\Core\Url');
    $status_report_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $status_report_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/reports/status');
    $ip_rebuild_url = $this->createMock('Drupal\Core\Url');
    $ip_rebuild_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $ip_rebuild_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/config/system/visitors/rebuild-ip-address?destination=/admin/reports/status');
    $this->pathValidator->expects($this->exactly(2))
      ->method('getUrlIfValidWithoutAccessCheck')
      ->willReturnMap([
        ['admin/config/system/visitors/rebuild-ip-address', $ip_rebuild_url],
        ['admin/reports/status', $status_report_url],
      ]);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(FALSE);
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);

    $requirements = visitors_requirements('runtime');
    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
  }

  /**
   * Tests visitors_requirements().
   *
   * The routes need to be rebuilt.
   */
  public function testRebuildRoute(): void {
    $this->state->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['visitors.rebuild.route', FALSE, TRUE],
        ['visitors.rebuild.ip_address', FALSE, FALSE],
      ]);

    $status_report_url = $this->createMock('Drupal\Core\Url');
    $status_report_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $status_report_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/reports/status');
    $route_rebuild_url = $this->createMock('Drupal\Core\Url');
    $route_rebuild_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $route_rebuild_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/config/system/visitors/rebuild-route?destination=/admin/reports/status');
    $this->pathValidator->expects($this->exactly(2))
      ->method('getUrlIfValidWithoutAccessCheck')
      ->willReturnMap([
        ['admin/config/system/visitors/rebuild-route', $route_rebuild_url],
        ['admin/reports/status', $status_report_url],
      ]);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(FALSE);
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);

    $requirements = visitors_requirements('runtime');
    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
  }

  /**
   * Tests visitors_requirements().
   *
   * The routes need to be rebuilt.
   */
  public function testRebuildRouteAndIp(): void {
    $this->state->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['visitors.rebuild.route', FALSE, TRUE],
        ['visitors.rebuild.ip_address', FALSE, TRUE],
      ]);

    $status_report_url = $this->createMock('Drupal\Core\Url');
    $status_report_url->expects($this->exactly(2))
      ->method('getOptions')
      ->willReturn([]);
    $status_report_url->expects($this->exactly(2))
      ->method('toString')
      ->willReturn('/admin/reports/status');
    $route_rebuild_url = $this->createMock('Drupal\Core\Url');
    $route_rebuild_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $route_rebuild_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/config/system/visitors/rebuild-route?destination=/admin/reports/status');

    $ip_rebuild_url = $this->createMock('Drupal\Core\Url');
    $ip_rebuild_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $ip_rebuild_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/config/system/visitors/rebuild-ip-address?destination=/admin/reports/status');

    $this->pathValidator->expects($this->exactly(4))
      ->method('getUrlIfValidWithoutAccessCheck')
      ->willReturnMap([
        ['admin/config/system/visitors/rebuild-route', $route_rebuild_url],
        ['admin/reports/status', $status_report_url],
        ['admin/config/system/visitors/rebuild-ip-address', $ip_rebuild_url],
      ]);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(FALSE);
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);

    $requirements = visitors_requirements('runtime');
    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
  }

  /**
   * Tests visitors_requirements().
   *
   * The performance table exists.
   */
  public function testPerformanceTable(): void {
    $this->state->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['visitors.rebuild.route', FALSE, FALSE],
        ['visitors.rebuild.ip_address', FALSE, FALSE],
      ]);

    $status_report_url = $this->createMock('Drupal\Core\Url');
    $status_report_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $status_report_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/reports/status');
    $performance_rebuild_url = $this->createMock('Drupal\Core\Url');
    $performance_rebuild_url->expects($this->once())
      ->method('getOptions')
      ->willReturn([]);
    $performance_rebuild_url->expects($this->once())
      ->method('toString')
      ->willReturn('/admin/config/system/visitors/performance?destination=/admin/reports/status');
    $this->pathValidator->expects($this->exactly(2))
      ->method('getUrlIfValidWithoutAccessCheck')
      ->willReturnMap([
        ['admin/config/system/visitors/performance', $performance_rebuild_url],
        ['admin/reports/status', $status_report_url],
      ]);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(TRUE);
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);

    $requirements = visitors_requirements('runtime');
    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
  }

}
