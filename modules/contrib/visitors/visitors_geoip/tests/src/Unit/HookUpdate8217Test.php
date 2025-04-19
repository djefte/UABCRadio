<?php

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests visitors_geoip_update_8217().
 *
 * @group visitors_geoip
 */
class HookUpdate8217Test extends UnitTestCase {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The File System service.
   *
   * @var \Drupal\Core\File\FileSystem|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileSystem;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->fileSystem = $this->createMock('Drupal\Core\File\FileSystem');
    $container->set('file_system', $this->fileSystem);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');

  }

  /**
   * Tests visitors_geoip_update_8217.
   */
  public function testUpdate8217BlankPath(): void {
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors_geoip.settings')
      ->willReturn($this->settings);

    $this->settings->expects($this->exactly(2))
      ->method('set')
      ->willReturnMap([
        ['geoip_path', '../', $this->settings],
        ['license', '', $this->settings],
      ]);

    $this->settings->expects($this->once())
      ->method('get')
      ->with('geoip_path')
      ->willReturn('');

    $this->settings->expects($this->once())
      ->method('save');

    visitors_geoip_update_8217();
  }

  /**
   * Tests visitors_geoip_update_8217.
   */
  public function testUpdate8217(): void {
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors_geoip.settings')
      ->willReturn($this->settings);

    $this->settings->expects($this->exactly(2))
      ->method('set')
      ->willReturnMap([
        ['geoip_path', 'some/path', $this->settings],
        ['license', '', $this->settings],
      ]);

    $this->settings->expects($this->once())
      ->method('get')
      ->with('geoip_path')
      ->willReturn('some/path/maxmind.mmdb');

    $this->settings->expects($this->once())
      ->method('save');

    $this->fileSystem->expects($this->once())
      ->method('dirname')
      ->with('some/path/maxmind.mmdb')
      ->willReturn('some/path');

    visitors_geoip_update_8217();
  }

}
