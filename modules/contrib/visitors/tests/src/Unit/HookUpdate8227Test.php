<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests Release 8.x-2.27.
 *
 * @group visitors
 */
class HookUpdate8227Test extends UnitTestCase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $blockStorage;

  /**
   * The block query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $blockQuery;

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

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->blockStorage = $this->createMock('Drupal\Core\Config\Entity\ConfigEntityStorageInterface');
    $this->blockQuery = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_update_8227.
   */
  public function testUpdate8227(): void {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('block')
      ->willReturn($this->blockStorage);
    $this->blockStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($this->blockQuery);
    $this->blockQuery->expects($this->once())
      ->method('condition')
      ->with('plugin', 'visitors_popular_block')
      ->willReturnSelf();
    $this->blockQuery->expects($this->once())
      ->method('accessCheck')
      ->with(FALSE)
      ->willReturnSelf();
    $this->blockQuery->expects($this->once())
      ->method('execute')
      ->willReturn(['visitors_popular_block_1', 'visitors_popular_block_2']);

    $block_1 = $this->createMock('Drupal\block\Entity\Block');
    $block_1->expects($this->once())
      ->method('get')
      ->with('settings')
      ->willReturn(['block_id' => 'visitors_popular_block_1']);
    $block_1->expects($this->once())
      ->method('set')
      ->with('settings', ['block_id' => 'visitors_popular_block_1', 'entity_type' => 'node'])
      ->willReturnSelf();
    $block_1->expects($this->once())
      ->method('save');

    $block_2 = $this->createMock('Drupal\block\Entity\Block');
    $block_2->expects($this->once())
      ->method('get')
      ->with('settings')
      ->willReturn([
        'block_id' => 'visitors_popular_block_2',
        'entity_type' => 'node',
      ]);

    $this->blockStorage->expects($this->exactly(2))
      ->method('load')
      ->willReturnMap([
        ['visitors_popular_block_1', $block_1],
        ['visitors_popular_block_2', $block_2],
      ]);

    $view_config = $this->createMock('Drupal\Core\Config\Config');
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('views.view.visitors')
      ->willReturn($view_config);

    visitors_update_8227();
  }

}
