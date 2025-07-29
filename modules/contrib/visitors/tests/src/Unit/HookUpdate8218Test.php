<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8218().
 *
 * @group visitors
 */
class HookUpdate8218Test extends UnitTestCase {

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
   * The database connection.
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

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_update_8218.
   */
  public function testUpdate8210Step1(): void {
    $ie = new \stdClass();
    $ie->config_browser_name = 'ie';
    $chrome = new \stdClass();
    $chrome->config_browser_name = 'chrome';

    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->settings->expects($this->once())
      ->method('set')
      ->with('script_type', 'minified')
      ->willReturnSelf();
    $this->settings->expects($this->once())
      ->method('save');

    $result = $this->createMock('Drupal\Core\Database\StatementInterface');
    $result->expects($this->once())
      ->method('fetchAll')
      ->willReturn([$ie, $chrome]);

    $select = $this->createMock('Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('fields')
      ->with('v', ['config_browser_name'])
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('distinct')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($result);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $sandbox = [];

    visitors_update_8218($sandbox);

    $this->assertEquals(0, $sandbox['progress']);
    $this->assertEquals(2, $sandbox['max']);
    $this->assertEquals([$ie, $chrome], $sandbox['browser']);
    $this->assertEquals(0, $sandbox['#finished']);
  }

  /**
   * Tests visitors_update_8218.
   */
  public function testUpdate8210Step2(): void {

    $update = $this->createMock('Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with([
        'config_browser_name' => 'CH',
      ])
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('condition')
      ->with('config_browser_name', 'chrome')
      ->willReturnSelf();

    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors')
      ->willReturn($update);

    $ie = new \stdClass();
    $ie->config_browser_name = 'ie';
    $chrome = new \stdClass();
    $chrome->config_browser_name = 'chrome';

    $sandbox = [
      'progress' => 0,
      'max' => 2,
      'browser' => [$ie, $chrome],
    ];

    visitors_update_8218($sandbox);

    $this->assertEquals(1, $sandbox['progress']);
    $this->assertEquals(2, $sandbox['max']);
    $this->assertEquals([$ie], $sandbox['browser']);
    $this->assertEquals(0.5, $sandbox['#finished']);

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('changeField')
      ->with('visitors', 'config_browser_name', 'config_browser_name', [
        'type' => 'varchar',
        'length' => '2',
        'not null' => FALSE,
        'default' => NULL,
      ]);

    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    visitors_update_8218($sandbox);

    $this->assertEquals(2, $sandbox['progress']);
    $this->assertEquals(2, $sandbox['max']);
    $this->assertEquals([], $sandbox['browser']);
    $this->assertEquals(1, $sandbox['#finished']);

  }

}
