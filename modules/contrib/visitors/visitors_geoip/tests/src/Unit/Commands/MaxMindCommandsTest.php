<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors_geoip\Unit\Commands;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors_geoip\Commands\MaxMindCommands;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests Visitors GeoIp Drush commands.
 *
 * @group visitors_geoip
 * @coversDefaultClass \Drupal\visitors_geoip\Commands\MaxMindCommands
 */
class MaxMindCommandsTest extends UnitTestCase {

  /**
   * The Drush command.
   *
   * @var \Drupal\visitors_geoip\Commands\MaxMindCommands
   */
  protected $command;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $client;

  /**
   * The geo ip settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileSystem;

  /**
   * The visitors rebuild location service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $location;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->client = $this->createMock('\GuzzleHttp\Client');
    $container->set('http_client', $this->client);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->fileSystem = $this->createMock('Drupal\Core\File\FileSystemInterface');
    $container->set('file_system', $this->fileSystem);

    $this->location = $this->createMock('Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface');
    $container->set('visitors_geoip.rebuild_location', $this->location);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('visitors_geoip.settings')
      ->willReturn($this->settings);

    $this->command = new MaxMindCommands(
      $this->client,
      $this->configFactory,
      $this->fileSystem,
      $this->location
    );
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $command = new MaxMindCommands(
      $this->client,
      $this->configFactory,
      $this->fileSystem,
      $this->location
    );
    $this->assertInstanceOf(MaxMindCommands::class, $command);
  }

  /**
   * Tests the locations command.
   *
   * @covers ::locations
   */
  public function testLocations(): void {
    $records = [
      'record1',
      '',
    ];

    $this->location->expects($this->once())
      ->method('getLocations')
      ->willReturn($records);

    $this->location->expects($this->once())
      ->method('rebuild')
      ->with('record1')
      ->willReturn(TRUE);

    $this->command->locations();

  }

  /**
   * Tests downloading cities.
   *
   * @covers ::downloadCities
   */
  public function testDownloadCitiesNoLicense(): void {
    $this->settings->expects($this->once())
      ->method('get')
      ->with('license')
      ->willReturn('');

    $this->command->downloadCities();
  }

}
