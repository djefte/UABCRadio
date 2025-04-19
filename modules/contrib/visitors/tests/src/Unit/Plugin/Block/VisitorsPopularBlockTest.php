<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\Block;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\Block\VisitorsPopularBlock;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\Block\VisitorsPopularBlock
 * @covers \Drupal\visitors\Plugin\Block\VisitorsPopularBlock
 */
class VisitorsPopularBlockTest extends UnitTestCase {

  /**
   * The block.
   *
   * @var \Drupal\visitors\Plugin\Block\VisitorsPopularBlock
   */
  protected $block;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityRepository;

  /**
   * The counter service.
   *
   * @var \Drupal\visitors\Service\CounterService|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $counterService;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $renderer;

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cache;

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

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->entityRepository = $this->createMock('Drupal\Core\Entity\EntityRepositoryInterface');
    $container->set('entity.repository', $this->entityRepository);

    $this->counterService = $this->createMock('Drupal\visitors\Service\CounterService');
    $container->set('visitors.counter', $this->counterService);

    $this->renderer = $this->createMock('Drupal\Core\Render\Renderer');
    $container->set('renderer', $this->renderer);

    $this->cache = $this->createMock('Drupal\Core\Cache\Context\CacheContextsManager');
    $container->set('cache_contexts_manager', $this->cache);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    \Drupal::setContainer($container);

    $configuration = [
      'top_day_num' => 1,
      'top_all_num' => 1,
      'top_last_num' => 1,
    ];
    $plugin_id = 'visitors_popular_block';
    $plugin_definition = [
      'provider' => 'visitors',
    ];
    $this->block = VisitorsPopularBlock::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_popular_block';
    $plugin_definition = [
      'provider' => 'visitors',
    ];
    $block = VisitorsPopularBlock::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(VisitorsPopularBlock::class, $block);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $configuration = [
      'top_day_num' => 1,
      'top_all_num' => 1,
      'top_last_num' => 1,
      'entity_type' => 'node',
    ];
    $plugin_id = 'visitors_popular_block';
    $plugin_definition = [
      'provider' => 'visitors',
    ];
    $block = new VisitorsPopularBlock(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $this->entityTypeManager,
      $this->entityRepository,
      $this->counterService,
      $this->renderer,
      $this->configFactory,
    );
    $this->assertInstanceOf(VisitorsPopularBlock::class, $block);
  }

  /**
   * Tests the block defaultConfiguration method.
   *
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration(): void {
    $configuration = $this->block->defaultConfiguration();
    $this->assertIsArray($configuration);
    $this->assertEquals($configuration['top_day_num'], 0);
    $this->assertEquals($configuration['top_all_num'], 0);
    $this->assertEquals($configuration['top_last_num'], 0);
  }

  /**
   * Tests the blockAccess method.
   *
   * @covers ::blockAccess
   */
  public function testBlockAccessAllowed(): void {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
       ['counter.entity_types', ['node']],
       ['counter.enabled', TRUE],
      ]);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($settings);

    $this->cache->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);
    $account = $this->createMock('Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('access content')
      ->willReturn(TRUE);
    $reflection = new \ReflectionMethod($this->block, 'blockAccess');
    $reflection->setAccessible(TRUE);

    $access = $reflection->invoke($this->block, $account);
    $this->assertTrue($access->isAllowed());
  }

  /**
   * Tests the blockAccess method.
   *
   * @covers ::blockAccess
   */
  public function testBlockAccessNotAllowed(): void {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
       ['counter.entity_types', ['node']],
       ['counter.enabled', TRUE],
      ]);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($settings);

    $this->cache->expects($this->once())
      ->method('assertValidTokens')
      ->with(['user.permissions'])
      ->willReturn(TRUE);
    $account = $this->createMock('Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('access content')
      ->willReturn(FALSE);
    $reflection = new \ReflectionMethod($this->block, 'blockAccess');
    $reflection->setAccessible(TRUE);

    $access = $reflection->invoke($this->block, $account);
    $this->assertFalse($access->isAllowed());
  }

  /**
   * Tests the blockAccess method.
   *
   * @covers ::blockAccess
   */
  public function testBlockAccessNotEnabled(): void {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
       ['counter.entity_types', ['node']],
       ['counter.enabled', FALSE],
      ]);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($settings);

    $account = $this->createMock('Drupal\Core\Session\AccountInterface');

    $reflection = new \ReflectionMethod($this->block, 'blockAccess');
    $reflection->setAccessible(TRUE);

    $access = $reflection->invoke($this->block, $account);
    $this->assertFalse($access->isAllowed());
  }

  /**
   * Tests the blockAccess method.
   *
   * @covers ::blockAccess
   */
  public function testBlockAccessNotEntityType(): void {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
       ['counter.entity_types', ['user']],
       ['counter.enabled', TRUE],
      ]);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($settings);

    $account = $this->createMock('Drupal\Core\Session\AccountInterface');

    $reflection = new \ReflectionMethod($this->block, 'blockAccess');
    $reflection->setAccessible(TRUE);

    $access = $reflection->invoke($this->block, $account);
    $this->assertFalse($access->isAllowed());
  }

  /**
   * Tests the blockForm method.
   *
   * @covers ::blockForm
   * @covers ::entityTypes
   */
  public function testBlockForm(): void {
    $node_definition = $this->createMock('Drupal\Core\Entity\ContentEntityType');
    $node_definition->expects($this->once())
      ->method('getLabel')
      ->willReturn('Content');
    $node_type_definition = $this->createMock('Drupal\Core\Config\Entity\ConfigEntityType');

    $this->entityTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn([
        'node' => $node_definition,
        'node_type' => $node_type_definition,
      ]);

    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->once())
      ->method('get')
      ->with('counter.entity_types')
      ->willReturn(['node']);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($settings);
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->block->blockForm([], $form_state);
    $this->assertIsArray($form);
    $this->assertArrayHasKey('statistics_block_top_day_num', $form);
    $this->assertArrayHasKey('statistics_block_top_all_num', $form);
    $this->assertArrayHasKey('statistics_block_top_last_num', $form);
  }

  /**
   * Tests the blockSubmit method.
   *
   * @covers ::blockSubmit
   */
  public function testBlockSubmit(): void {
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->exactly(4))
      ->method('getValue')
      ->willReturnMap([
        ['statistics_block_top_day_num', NULL, 5],
        ['statistics_block_top_all_num', NULL, 10],
        ['statistics_block_top_last_num', NULL, 15],
        ['entity_type', NULL, 'node'],
      ]);
    $this->block->blockSubmit($form, $form_state);
    $this->assertEquals(5, $this->block->getConfiguration()['top_day_num']);
    $this->assertEquals(10, $this->block->getConfiguration()['top_all_num']);
    $this->assertEquals(15, $this->block->getConfiguration()['top_last_num']);
  }

  /**
   * Tests the build method.
   *
   * @covers ::build
   * @covers ::entityLabelList
   */
  public function testBuild(): void {
    $node_type = $this->createMock('Drupal\Core\Entity\EntityTypeInterface');
    $node_type->expects($this->exactly(3))
      ->method('getListCacheTags')
      ->willReturn([]);

    $node = $this->createMock('Drupal\node\NodeInterface');
    $node_storage = $this->createMock('Drupal\node\NodeStorageInterface');
    $node_storage->expects($this->exactly(3))
      ->method('loadMultiple')
      ->with([1])
      ->willReturn([1 => $node]);

    $link = $this->createMock('Drupal\Core\Link');
    $link->expects($this->exactly(3))
      ->method('toRenderable')
      ->willReturn(['#markup' => 'Title']);
    $node->expects($this->exactly(3))
      ->method('toLink')
      ->willReturn($link);
    $this->entityRepository->expects($this->exactly(3))
      ->method('getTranslationFromContext')
      ->with($node)
      ->willReturn($node);

    $this->entityTypeManager->expects($this->exactly(3))
      ->method('getDefinition')
      ->with('node')
      ->willReturn($node_type);

    $this->entityTypeManager->expects($this->exactly(3))
      ->method('getStorage')
      ->with('node')
      ->willReturn($node_storage);

    $this->counterService->expects($this->exactly(3))
      ->method('fetchAll')
      ->willReturn([0 => 1]);

    $build = $this->block->build();
    $this->assertIsArray($build);
    $this->assertCount(3, $build);
    $this->assertArrayHasKey('top_day', $build);
    $this->assertArrayHasKey('top_all', $build);
    $this->assertArrayHasKey('top_last', $build);
  }

  /**
   * Tests the getCacheTags method.
   *
   * @covers ::getCacheTags
   */
  public function testGetCacheTags(): void {
    $this->assertEquals(['config:visitors.config'], $this->block->getCacheTags());
  }

}
