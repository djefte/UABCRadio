<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8214().
 *
 * @group visitors
 */
class HookUpdate8214Test extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_update_8214.
   */
  public function testUpdate8214(): void {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);
    $schema->expects($this->once())
      ->method('changeField')
      ->with('visitors', 'visitors_ip', 'visitors_ip', [
        'type' => 'varchar',
        'length' => 45,
        'not null' => TRUE,
        'default' => '',
      ])
      ->willReturn(TRUE);

    $this->state->expects($this->once())
      ->method('set')
      ->with('visitors.rebuild.ip_address', TRUE);

    visitors_update_8214();
  }

}
