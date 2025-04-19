<?php

namespace Drupal\Tests\visitors_geoip\Unit\Service;

use GeoIp2\Model\City;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors_geoip\Service\RebuildLocationService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the RebuildLocationService.
 *
 * @coversDefaultClass \Drupal\visitors_geoip\Service\RebuildLocationService
 *
 * @group visitors_geoip
 */
class RebuildLocationServiceTest extends UnitTestCase {

  /**
   * The rebuild location service.
   *
   * @var \Drupal\visitors_geoip\Service\RebuildLocationService
   */
  protected $service;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The GeoIP service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $geoipService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->geoipService = $this->createMock('Drupal\visitors_geoip\VisitorsGeoIpInterface');
    $container->set('visitors_geoip.rebuild.location', $this->geoipService);

    $this->service = new RebuildLocationService($this->database, $this->geoipService);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstruct(): void {
    $service = new RebuildLocationService($this->database, $this->geoipService);

    $this->assertInstanceOf(RebuildLocationService::class, $service);
  }

  /**
   * Tests getLocations() method.
   *
   * @covers ::getLocations
   */
  public function testGetLocations(): void {
    $statement = $this->createMock('Drupal\Core\Database\StatementInterface');
    $select = $this->createMock('Drupal\Core\Database\Query\SelectInterface');
    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $select->expects($this->once())
      ->method('fields')
      ->with('v', ['visitors_ip'])
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('location_latitude', NULL, 'IS NULL')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('distinct')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);
    $statement->expects($this->once())
      ->method('fetchAll')
      ->with(\PDO::FETCH_COLUMN, 0)
      ->willReturn(['127.0.0.1']);

    $result = $this->service->getLocations();
  }

  /**
   * Tests the rebuild() method.
   *
   * @covers ::rebuild
   */
  public function testRebuildNone(): void {
    $ip_address = '127.0.0.1';
    $this->geoipService->expects($this->once())
      ->method('city')
      ->with($ip_address)
      ->willReturn(NULL);

    $this->service->rebuild($ip_address);
  }

  /**
   * Tests the rebuild() method.
   *
   * @covers ::rebuild
   */
  public function testRebuild(): void {

    $city = new City([
      'city' => ['names' => ['en' => 'Los Angeles']],
      'country' => ['iso_code' => 'US'],
      'subdivisions' => [['iso_code' => 'CA']],
      'continent' => ['code' => 'NA'],
      'location' => ['latitude' => 34.0522, 'longitude' => -118.2437],
    ]);

    $ip_address = '127.0.0.1';
    $this->geoipService->expects($this->once())
      ->method('city')
      ->with($ip_address)
      ->willReturn($city);

    $update = $this->createMock('Drupal\Core\Database\Query\Update');
    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors')
      ->willReturn($update);
    $update->expects($this->once())
      ->method('fields')
      ->with([
        'location_continent' => 'NA',
        'location_country' => 'US',
        'location_region' => 'CA',
        'location_city' => 'Los Angeles',
        'location_latitude' => 34.0522,
        'location_longitude' => -118.2437,
      ])
      ->willReturnSelf();
    $update->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('execute')
      ->willReturn(1);

    $this->service->rebuild($ip_address);
  }

}
