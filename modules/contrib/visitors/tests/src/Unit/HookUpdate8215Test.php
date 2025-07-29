<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8215().
 *
 * @group visitors
 */
class HookUpdate8215Test extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

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

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_update_8215.
   */
  public function testUpdate8215(): void {
    $settings = $this->createMock('Drupal\Core\Config\Config');
    $settings->expects($this->exactly(4))
      ->method('clear')
      ->willReturnSelf();
    $settings->expects($this->exactly(12))
      ->method('set')
      ->willReturnSelf();
    $settings->expects($this->once())
      ->method('save')
      ->willReturnSelf();
    $config = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $config->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap([
        ['excluded_roles', ['administrator']],
        ['exclude_user1', TRUE],
      ]);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($config);
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($settings);

    $sandbox = [];
    visitors_update_8215($sandbox);
  }

  /**
   * Tests visitors_update_8215 with a delete operation.
   */
  public function testUpdate8215DeleteProgress7(): void {

    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $delete->expects($this->once())
      ->method('execute')
      ->willReturn(1);
    $this->database->expects($this->once())
      ->method('delete')
      ->with('visitors')
      ->willReturn($delete);

    $sandbox = [
      'progress' => 7,
      'max' => 10,
    ];

    visitors_update_8215($sandbox);

    $this->assertEquals(8 / 10, $sandbox['#finished'], 'The progress was updated correctly.');
  }

  /**
   * Tests visitors_update_8215 with a delete operation.
   *
   * @dataProvider deleteDataProvider
   */
  public function testUpdate8215Delete($progress, $column, $value, $operator = '='): void {

    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->once())
      ->method('condition')
      ->with($column, $value, $operator)
      ->willReturnSelf();
    $delete->expects($this->once())
      ->method('execute')
      ->willReturn(1);
    $this->database->expects($this->once())
      ->method('delete')
      ->with('visitors')
      ->willReturn($delete);

    $sandbox = [
      'progress' => $progress,
      'max' => 10,
    ];

    visitors_update_8215($sandbox);

    $this->assertEquals(($progress + 1) / 10, $sandbox['#finished'], 'The progress was updated correctly.');
  }

  /**
   * Data provider for testUpdate8215Delete.
   *
   * @return array
   *   An array of data for the test.
   */
  public static function deleteDataProvider() {
    return [
      [
        'progress' => 1,
        'column' => 'visitors_url',
        'value' => 'http://default/',
      ],
      [
        'progress' => 2,
        'column' => 'visitors_path',
        'value' => '/batch',
      ],
      [
        'progress' => 3,
        'column' => 'visitors_path',
        'value' => '/history/get_node_read_timestamps',
      ],
      [
        'progress' => 4,
        'column' => 'visitors_path',
        'value' => '/history/%/read',
        'operator' => 'LIKE',
      ],
      [
        'progress' => 5,
        'column' => 'visitors_path',
        'value' => '/ckeditor5/upload-image/%',
        'operator' => 'LIKE',
      ],
      [
        'progress' => 6,
        'column' => 'visitors_path',
        'value' => '/jsnlog/log%',
        'operator' => 'LIKE',
      ],
      [
        'progress' => 8,
        'column' => 'visitors_path',
        'value' => '/tagify_autocomplete/%',
        'operator' => 'LIKE',
      ],
      [
        'progress' => 9,
        'column' => 'visitors_path',
        'value' => '/entity_reference_autocomplete_id/%/%/%',
        'operator' => 'LIKE',
      ],
      [
        'progress' => 10,
        'column' => 'visitors_path',
        'value' => '/entity_reference_autocomplete/%/%/%',
        'operator' => 'LIKE',
      ],
    ];
  }

}
