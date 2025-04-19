<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8212().
 *
 * @group visitors
 */
class HookUpdate8212Test extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The settings config.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');

    $this->blockStorage = $this->createMock('Drupal\Core\Config\Entity\ConfigEntityStorageInterface');

    $this->blockQuery = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');

  }

  /**
   * Tests visitors_update_8212.
   */
  public function testUpdate8212(): void {
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('block')
      ->willReturn($this->blockStorage);

    $this->blockStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($this->blockQuery);

    $this->blockQuery->expects($this->once())
      ->method('condition')
      ->with('plugin', 'visitors_block')
      ->willReturnSelf();
    $this->blockQuery->expects($this->once())
      ->method('accessCheck')
      ->with(FALSE)
      ->willReturnSelf();
    $this->blockQuery->expects($this->once())
      ->method('execute')
      ->willReturn(['visitors_block_1']);

    $this->settings->expects($this->exactly(7))
      ->method('get')
      ->willReturnMap([
        ['show_last_registered_user', TRUE],
        ['show_published_nodes', TRUE],
        ['show_registered_users_count', TRUE],
        ['show_since_date', TRUE],
        ['show_total_visitors', TRUE],
        ['show_unique_visitor', TRUE],
        ['show_user_ip', TRUE],
      ]);

    $block = $this->createMock('Drupal\block\Entity\Block');
    $block->expects($this->once())
      ->method('set')
      ->with('settings', [
        'show_last_registered_user' => TRUE,
        'show_published_nodes' => TRUE,
        'show_registered_users_count' => TRUE,
        'show_since_date' => TRUE,
        'show_total_visitors' => TRUE,
        'show_unique_visitor' => TRUE,
        'show_user_ip' => TRUE,
      ]);
    $block->expects($this->once())
      ->method('save');

    $this->blockStorage->expects($this->once())
      ->method('load')
      ->with('visitors_block_1')
      ->willReturn($block);

    visitors_update_8212();
  }

}
