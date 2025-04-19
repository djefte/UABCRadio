<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests hook_entity_delete().
 *
 * @group visitors
 */
class HookEntityDeleteTest extends UnitTestCase {

  /**
   * The counter service.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $counter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->counter = $this->createMock('Drupal\visitors\VisitorsCounterInterface');
    $container->set('visitors.counter', $this->counter);

    \Drupal::setContainer($container);
  }

  /**
   * Tests hook_entity_delete().
   */
  public function testConfigEntity(): void {
    $node_type = $this->createMock('Drupal\node\NodeTypeInterface');
    $node_type->expects($this->once())
      ->method('id')
      ->willReturn('page');
    $this->counter->expects($this->never())
      ->method('deleteViews');

    visitors_entity_delete($node_type);
  }

  /**
   * Tests hook_entity_delete().
   */
  public function testNodeEntity(): void {
    $node = $this->createMock('Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $this->counter->expects($this->once())
      ->method('deleteViews')
      ->with('node', 1);

    visitors_entity_delete($node);
  }

}
