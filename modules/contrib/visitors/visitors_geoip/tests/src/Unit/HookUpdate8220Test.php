<?php

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests visitors_geoip_update_8220().
 *
 * @group visitors_geoip
 */
class HookUpdate8220Test extends UnitTestCase {

  /**
   * The Database service.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_geoip_update_8220.
   */
  public function testUpdate8220(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $schema->expects($this->exactly(2))
      ->method('fieldExists')
      ->willReturnMap([
        ['visitors', 'location_postal', TRUE],
        ['visitors', 'location_country_name', TRUE],
      ]);
    $schema->expects($this->once())
      ->method('dropField')
      ->with('visitors', 'location_country_name');
    $schema->expects($this->once())
      ->method('changeField')
      ->with('visitors', 'location_postal', 'location_postal', [
        'type' => 'varchar',
        'length' => 128,
        'not null' => FALSE,
        'default' => NULL,
      ]);

    $view = $this->createMock('Drupal\views\Entity\View');
    $view->expects($this->once())
      ->method('save');
    $view_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $view_storage->expects($this->once())
      ->method('create')
      ->willReturn($view);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('view')
      ->willReturn($view_storage);

    visitors_geoip_update_8220();
  }

}
