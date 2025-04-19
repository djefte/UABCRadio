<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Commands;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Commands\RebuildCommands;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests Visitors Drush commands.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Commands\RebuildCommands
 */
class RebuildCommandsTest extends UnitTestCase {

  /**
   * The Drush command.
   *
   * @var \Drupal\visitors\Commands\RebuildCommands
   */
  protected $command;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $state;

  /**
   * The visitors rebuild route service.
   *
   * @var \Drupal\visitors\VisitorsRebuildRouteInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $route;

  /**
   * The visitors rebuild ip address service.
   *
   * @var \Drupal\visitors\VisitorsRebuildIpAddressInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $address;

  /**
   * The visitors device service.
   *
   * @var \Drupal\visitors\VisitorsDeviceInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $device;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $this->route = $this->createMock('Drupal\visitors\VisitorsRebuildRouteInterface');
    $container->set('visitors.rebuild_route', $this->route);

    $this->address = $this->createMock('Drupal\visitors\VisitorsRebuildIpAddressInterface');
    $container->set('visitors.rebuild_ip_address', $this->address);

    $this->device = $this->createMock('Drupal\visitors\VisitorsDeviceInterface');
    $container->set('visitors.device', $this->device);

    \Drupal::setContainer($container);

    $this->command = new RebuildCommands($this->state, $this->route, $this->address, $this->device);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $command = new RebuildCommands($this->state, $this->route, $this->address, $this->device);

    $this->assertInstanceOf(RebuildCommands::class, $command);
  }

  /**
   * Tests the rebuild routes.
   *
   * @covers ::routes
   */
  public function testRebuildRoute(): void {
    $this->route->expects($this->once())
      ->method('getPaths')
      ->willReturn([
        (object) ['visitors_path' => 'node/1'],
        (object) ['visitors_path' => 'node/2'],
      ]);

    $this->route->expects($this->exactly(2))
      ->method('rebuild')
      ->willReturnMap([
        ['node/1', 1],
        ['node/2', 1],
      ]);

    $this->state->expects($this->once())
      ->method('delete')
      ->with('visitors.rebuild.route');

    $this->command->routes();
  }

  /**
   * Tests rebuild IP addresses.
   *
   * @covers ::addresses
   */
  public function testAddresses(): void {
    $this->address->expects($this->once())
      ->method('getIpAddresses')
      ->willReturn([
        (object) ['visitors_ip' => '127.0.0.1'],
      ]);
    $this->address->expects($this->once())
      ->method('rebuild')
      ->with('127.0.0.1');
    $this->state->expects($this->once())
      ->method('delete')
      ->with('visitors.rebuild.ip_address');

    $this->command->addresses();
  }

  /**
   * Tests rebuild devices.
   *
   * @covers ::device
   */
  public function testDeviceWithoutLibrary(): void {
    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(FALSE);

    $this->device->expects($this->never())
      ->method('getUniqueUserAgents');

    $this->command->device();
  }

  /**
   * Tests rebuild devices.
   *
   * @covers ::device
   */
  public function testDeviceWithLibrary(): void {
    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);

    $this->device->expects($this->once())
      ->method('getUniqueUserAgents')
      ->willReturn(['Mozilla/5.0']);

    $this->device->expects($this->once())
      ->method('bulkUpdate')
      ->with('Mozilla/5.0')
      ->willReturn(1);

    $this->state->expects($this->once())
      ->method('delete')
      ->with('visitors.rebuild.device');

    $this->command->device();
  }

}
