<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\Block;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\Block\VisitorsOnlineBlock;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Visitors Online block test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\Block\VisitorsOnlineBlock
 * @covers \Drupal\visitors\Plugin\Block\VisitorsOnlineBlock
 */
class VisitorsOnlineBlockTest extends UnitTestCase {

  /**
   * The block.
   *
   * @var \Drupal\visitors\Plugin\Block\VisitorsOnlineBlock
   */
  protected $block;

  /**
   * The online service.
   *
   * @var \Drupal\visitors\VisitorsOnlineInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $online;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->online = $this->createMock('Drupal\visitors\VisitorsOnlineInterface');
    $container->set('visitors.online', $this->online);

    \Drupal::setContainer($container);

    $configuration = [
      'now_30_minute' => TRUE,
      'now_24_hour' => TRUE,
      'yesterday_30_minute' => TRUE,
      'yesterday_24_hour' => TRUE,
      'last_week_30_minute' => TRUE,
      'last_week_24_hour' => TRUE,
    ];
    $plugin_id = 'visitors_online';
    $plugin_definition = [
      'provider' => 'visitors',
    ];
    $this->block = VisitorsOnlineBlock::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_online';
    $plugin_definition = [
      'provider' => 'visitors',
    ];
    $block = VisitorsOnlineBlock::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(VisitorsOnlineBlock::class, $block);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $configuration = [];
    $plugin_id = 'visitors_online';
    $plugin_definition = [
      'provider' => 'visitors',
    ];
    $block = new VisitorsOnlineBlock(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $this->online,
    );
    $this->assertInstanceOf(VisitorsOnlineBlock::class, $block);
  }

  /**
   * Tests the block defaultConfiguration method.
   *
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration(): void {
    $configuration = $this->block->defaultConfiguration();
    $this->assertIsArray($configuration);
    $this->assertCount(6, $configuration);
    $this->assertTrue($configuration['now_30_minute']);
    $this->assertFalse($configuration['now_24_hour']);
    $this->assertFalse($configuration['yesterday_30_minute']);
    $this->assertFalse($configuration['yesterday_24_hour']);
    $this->assertFalse($configuration['last_week_30_minute']);
    $this->assertFalse($configuration['last_week_24_hour']);
  }

  /**
   * Tests the blockForm method.
   *
   * @covers ::blockForm
   */
  public function testBlockForm(): void {

    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->block->blockForm([], $form_state);
    $this->assertIsArray($form);
    $this->assertArrayHasKey('now_30_minute', $form);
    $this->assertArrayHasKey('now_24_hour', $form);
    $this->assertArrayHasKey('yesterday_30_minute', $form);
    $this->assertArrayHasKey('yesterday_24_hour', $form);
    $this->assertArrayHasKey('last_week_30_minute', $form);
    $this->assertArrayHasKey('last_week_24_hour', $form);
  }

  /**
   * Tests the blockSubmit method.
   *
   * @covers ::blockSubmit
   */
  public function testBlockSubmit(): void {
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->exactly(6))
      ->method('getValue')
      ->willReturnMap([
        ['now_30_minute', NULL, FALSE],
        ['now_24_hour', NULL, FALSE],
        ['yesterday_30_minute', NULL, FALSE],
        ['yesterday_24_hour', NULL, FALSE],
        ['last_week_30_minute', NULL, FALSE],
        ['last_week_24_hour', NULL, FALSE],
      ]);
    $this->block->blockSubmit($form, $form_state);
    $configuration = $this->block->getConfiguration();
    $this->assertFalse($configuration['now_30_minute']);
    $this->assertFalse($configuration['now_24_hour']);
    $this->assertFalse($configuration['yesterday_30_minute']);
    $this->assertFalse($configuration['yesterday_24_hour']);
    $this->assertFalse($configuration['last_week_30_minute']);
    $this->assertFalse($configuration['last_week_24_hour']);
  }

  /**
   * Tests the build method.
   *
   * @covers ::build
   */
  public function testBuild(): void {
    $this->online->expects($this->once())
      ->method('getLast30Minutes')
      ->willReturn(1);
    $this->online->expects($this->once())
      ->method('getLast24Hours')
      ->willReturn(2);
    $this->online->expects($this->once())
      ->method('getYesterday30Minutes')
      ->willReturn(3);
    $this->online->expects($this->once())
      ->method('getYesterday24Hours')
      ->willReturn(4);
    $this->online->expects($this->once())
      ->method('getLastWeek30Minutes')
      ->willReturn(5);
    $this->online->expects($this->once())
      ->method('getLastWeek24Hours')
      ->willReturn(6);

    $build = $this->block->build();

    $this->assertIsArray($build);
    $this->assertCount(2, $build);
    $this->assertCount(6, $build['#items']);
  }

  /**
   * Tests the getCacheMaxAge method.
   *
   * @covers ::getCacheMaxAge
   */
  public function testGetCacheMaxAge() {
    $this->assertEquals(300, $this->block->getCacheMaxAge());
  }

}
