<?php

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests visitors_geoip_update_8222().
 *
 * @group visitors_geoip
 */
class HookUpdate8222Test extends UnitTestCase {

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
   * Tests visitors_geoip_update_8222.
   */
  public function testUpdate8222(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $schema->expects($this->exactly(2))
      ->method('fieldExists')
      ->willReturnMap([
        ['visitors', 'location_postal', TRUE],
        ['visitors', 'location_area_code', TRUE],
      ]);
    $schema->expects($this->exactly(2))
      ->method('dropField')
      ->willReturnMap([
        ['visitors', 'location_postal'],
        ['visitors', 'location_area_code'],
      ]);

    visitors_geoip_update_8222();
  }

}
