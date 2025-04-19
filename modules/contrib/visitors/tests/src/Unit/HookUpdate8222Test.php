<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8222().
 *
 * @group visitors
 */
class HookUpdate8222Test extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The database connection.
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

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    \Drupal::setContainer($container);

  }

  /**
   * Tests visitors_update_8222().
   */
  public function testStepZero(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');

    $this->database->expects($this->any())
      ->method('schema')
      ->willReturn($schema);

    $schema->expects($this->exactly(8))
      ->method('fieldExists')
      ->willReturnMap([
        ['visitors', 'pf_network', TRUE],
        ['visitors', 'pf_server', FALSE],
        ['visitors', 'pf_transfer', FALSE],
        ['visitors', 'pf_dom_processing', FALSE],
        ['visitors', 'pf_dom_complete', FALSE],
        ['visitors', 'pf_on_load', FALSE],
        ['visitors', 'pf_total', FALSE],
        ['visitors', 'server', FALSE],
      ]);
    $schema->expects($this->exactly(7))
      ->method('addField')
      ->willReturnMap([
        ['visitors', 'pf_server', [
          'description' => 'Server performance',
          'type' => 'int',
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
        ['visitors', 'pf_transfer', [
          'description' => 'Transfer performance',
          'type' => 'int',
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
        ['visitors', 'pf_dom_processing', [
          'description' => 'DOM processing performance',
          'type' => 'int',
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
        ['visitors', 'pf_dom_complete', [
          'description' => 'DOM complete performance',
          'type' => 'int',
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
        ['visitors', 'pf_on_load', [
          'description' => 'On load performance',
          'type' => 'int',
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
        ['visitors', 'pf_total', [
          'description' => 'Total performance',
          'type' => 'int',
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
        ['visitors', 'server', [
          'description' => 'The server that generated the response',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
          'default' => NULL,
        ],
        ],
      ]);

    $schema->expects($this->once())
      ->method('changeField')
      ->with('visitors', 'visitors_title', 'visitors_title', [
        'type' => 'text',
        'not null' => TRUE,
      ]);

    $sandbox = [];
    visitors_update_8222($sandbox);

    $this->assertEquals(1, $sandbox['progress']);
    $this->assertEquals(2, $sandbox['max']);

  }

  /**
   * Tests visitors_update_8222().
   */
  public function testStepOne(): void {
    $settings = $this->createMock('Drupal\Core\Config\Config');
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($settings);

    $settings->expects($this->once())
      ->method('clear')
      ->with('performance')
      ->willReturnSelf();
    $settings->expects($this->once())
      ->method('save');

    $view_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('view')
      ->willReturn($view_storage);

    $visitors_view = $this->createMock('Drupal\views\ViewEntityInterface');
    $view_storage->expects($this->once())
      ->method('load')
      ->with('visitors')
      ->willReturn($visitors_view);

    $visitors_view->expects($this->once())
      ->method('get')
      ->with('display')
      ->willReturn([]);
    $visitors_view->expects($this->once())
      ->method('set');
    $visitors_view->expects($this->once())
      ->method('save');

    $sandbox = ['progress' => 1, 'max' => 2];
    visitors_update_8222($sandbox);

    $this->assertEquals(2, $sandbox['progress']);
    $this->assertEquals(2, $sandbox['max']);
  }

}
