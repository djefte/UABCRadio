<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8220().
 *
 * @group visitors
 */
class HookUpdate8220Test extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The settings config.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleInstaller;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The entity type manager.
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

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->moduleInstaller = $this->createMock('Drupal\Core\Extension\ModuleInstallerInterface');
    $container->set('module_installer', $this->moduleInstaller);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_update_8220.
   */
  public function testUpdate8210WithoutGeoIp(): void {
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->settings->expects($this->exactly(2))
      ->method('clear')
      ->willReturnMap([
        ['chart_height', $this->settings],
        ['chart_width', $this->settings],
      ]);
    $this->settings->expects($this->once())
      ->method('save');

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('charts_chartjs')
      ->willReturn(FALSE);

    $this->moduleInstaller->expects($this->once())
      ->method('install')
      ->with(['charts_chartjs']);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->exactly(3))
      ->method('addField')
      ->willReturnMap([
        ['visitors', 'language', [
          'type' => 'varchar',
          'length' => 2,
          'not null' => TRUE,
          'default' => '',
        ],
        ],
        ['visitors', 'location_country', [
          'type' => 'varchar',
          'length' => 2,
          'not null' => TRUE,
          'default' => '',
        ],
        ],
        ['visitors', 'location_continent', [
          'type' => 'varchar',
          'length' => 2,
          'not null' => TRUE,
          'default' => '',
        ],
        ],
      ]);
    $schema->expects($this->once())
      ->method('createTable');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $visitors_view = $this->createMock('Drupal\views\Entity\View');
    $visitors_view->expects($this->once())
      ->method('save');
    $old_view = $this->createMock('Drupal\views\Entity\View');
    $old_view->expects($this->exactly(5))
      ->method('delete');
    $view_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $view_storage->expects($this->once())
      ->method('create')
      ->willReturn($visitors_view);
    $view_storage->expects($this->exactly(5))
      ->method('load')
      ->willReturn($old_view);

    $block_query = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');
    $block_query->expects($this->once())
      ->method('condition')
      ->with('plugin', 'visitors_block')
      ->willReturnSelf();
    $block_query->expects($this->once())
      ->method('accessCheck')
      ->with(FALSE)
      ->willReturnSelf();
    $block_query->expects($this->once())
      ->method('execute')
      ->willReturn([]);
    $block_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $block_storage->expects($this->once())
      ->method('getQuery')
      ->willReturn($block_query);

    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->willReturnMap([
        ['view', $view_storage],
        ['block', $block_storage],
      ]);

    visitors_update_8220();
  }

  /**
   * Tests visitors_update_8220.
   */
  public function testUpdate8210WithGeoIp(): void {
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->settings->expects($this->exactly(2))
      ->method('clear')
      ->willReturnMap([
        ['chart_height', $this->settings],
        ['chart_width', $this->settings],
      ]);
    $this->settings->expects($this->once())
      ->method('save');

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('charts_chartjs')
      ->willReturn(FALSE);

    $this->moduleInstaller->expects($this->once())
      ->method('install')
      ->with(['charts_chartjs']);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('addField')
      ->willReturnMap([
        ['visitors', 'language', [
          'type' => 'varchar',
          'length' => 2,
          'not null' => TRUE,
          'default' => '',
        ],
        ],
      ]);
    $schema->expects($this->exactly(3))
      ->method('fieldExists')
      ->willReturnMap([
        ['visitors', 'location_continent_code', TRUE],
        ['visitors', 'location_country_code', TRUE],
        ['visitors', 'language', FALSE],
      ]);
    $schema->expects($this->exactly(2))
      ->method('changeField')
      ->willReturnMap([
      ['visitors', 'location_continent_code', 'location_country', [
        'type' => 'varchar',
        'length' => 2,
        'not null' => TRUE,
        'default' => '',
      ],
      ],
      ['visitors', 'location_country_code', 'location_continent', [
        'type' => 'varchar',
        'length' => 2,
        'not null' => TRUE,
        'default' => '',
      ],
      ],
      ]);
    $schema->expects($this->once())
      ->method('createTable');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $visitors_view = $this->createMock('Drupal\views\Entity\View');
    $visitors_view->expects($this->once())
      ->method('save');
    $old_view = $this->createMock('Drupal\views\Entity\View');
    $old_view->expects($this->exactly(5))
      ->method('delete');
    $view_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $view_storage->expects($this->once())
      ->method('create')
      ->willReturn($visitors_view);
    $view_storage->expects($this->exactly(5))
      ->method('load')
      ->willReturn($old_view);

    $visitors_block = $this->createMock('Drupal\block\Entity\Block');
    $visitors_block->expects($this->once())
      ->method('delete');

    $block_query = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');
    $block_query->expects($this->once())
      ->method('condition')
      ->with('plugin', 'visitors_block')
      ->willReturnSelf();
    $block_query->expects($this->once())
      ->method('accessCheck')
      ->with(FALSE)
      ->willReturnSelf();
    $block_query->expects($this->once())
      ->method('execute')
      ->willReturn(['visitors_block']);
    $block_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $block_storage->expects($this->once())
      ->method('getQuery')
      ->willReturn($block_query);
    $block_storage->expects($this->once())
      ->method('load')
      ->with('visitors_block')
      ->willReturn($visitors_block);

    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->willReturnMap([
        ['view', $view_storage],
        ['block', $block_storage],
      ]);

    visitors_update_8220();
  }

}
