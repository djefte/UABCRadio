<?php

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests visitors_geoip_update_8215().
 *
 * @group visitors_geoip
 */
class HookUpdate8215Test extends UnitTestCase {

  /**
   * The Database service.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_geoip_update_8215.
   */
  public function testUpdate8215(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $schema->expects($this->exactly(2))
      ->method('dropField')
      ->willReturnMap([
        ['visitors', 'visitors_country_code3'],
        ['visitors', 'visitors_dma_code'],
      ]);

    $varchar_2 = [
      'type' => 'varchar',
      'length' => 2,
      'not null' => TRUE,
      'default' => '',
    ];
    $varchar_128 = [
      'type' => 'varchar',
      'length' => 128,
      'not null' => TRUE,
      'default' => '',
    ];
    $numeric = [
      'type' => 'numeric',
      'precision' => 13,
      'scale' => 10,
      'default' => NULL,
    ];
    $integer = [
      'type' => 'int',
      'unsigned' => TRUE,
      'default' => NULL,
    ];
    $schema->expects($this->exactly(9))
      ->method('changeField')
      ->willReturnMap([
        ['visitors', 'visitors_continent_code', 'location_continent_code', $varchar_2, TRUE],
        ['visitors', 'visitors_country_code', 'location_country_code', $varchar_2, TRUE],
        ['visitors', 'visitors_country_name', 'location_country_name', $varchar_128, TRUE],
        ['visitors', 'visitors_region', 'location_region', $varchar_128, TRUE],
        ['visitors', 'visitors_city', 'location_city', $varchar_128, TRUE],
        ['visitors', 'visitors_postal', 'location_postal', $varchar_128, TRUE],
        ['visitors', 'visitors_latitude', 'location_latitude', $numeric, TRUE],
        ['visitors', 'visitors_longitude', 'location_longitude', $numeric, TRUE],
        ['visitors', 'visitors_area_code', 'location_area_code', $integer, TRUE],
      ]);

    visitors_geoip_update_8215();
  }

}
