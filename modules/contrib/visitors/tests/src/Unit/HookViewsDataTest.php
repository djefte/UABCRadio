<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_views_data.
 *
 * @group visitors
 */
class HookViewsDataTest extends UnitTestCase {

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

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_views_data().
   */
  public function testVisitorsViewsData() {
    $this->settings->expects($this->once())
      ->method('get')
      ->with('counter.entity_types')
      ->willReturn(['node', 'comment']);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $node_type = $this->createMock('Drupal\Core\Entity\EntityTypeInterface');
    $node_type->expects($this->once())
      ->method('entityClassImplements')
      ->with(ContentEntityInterface::class)
      ->willReturn(TRUE);
    $node_type->expects($this->once())
      ->method('getBaseTable')
      ->willReturn('node');
    $node_type->expects($this->exactly(2))
      ->method('getDataTable')
      ->willReturn('node_field_data');

    $comment_type = $this->createMock('Drupal\Core\Entity\EntityTypeInterface');
    $comment_type->expects($this->once())
      ->method('entityClassImplements')
      ->with(ContentEntityInterface::class)
      ->willReturn(TRUE);
    $comment_type->expects($this->once())
      ->method('getBaseTable')
      ->willReturn('comment');

    $this->entityTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn([
        'node' => $node_type,
        'comment' => $comment_type,
      ]);

    $views_data = visitors_views_data();

    $this->assertIsArray($views_data);
    $this->assertCount(4, $views_data);

    $this->assertArrayHasKey('visitors', $views_data);
    $this->assertArrayHasKey('visitors_counter', $views_data);
    $this->assertArrayHasKey('node_field_data', $views_data);
    $this->assertArrayHasKey('comment', $views_data);
  }

}
