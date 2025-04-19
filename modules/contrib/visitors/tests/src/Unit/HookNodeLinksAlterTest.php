<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\StatisticsViewsResult;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_node_links_alter.
 *
 * @group visitors
 */
class HookNodeLinksAlterTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The visitors counter.
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

    $this->currentUser = $this->createMock('Drupal\Core\Session\AccountInterface');
    $container->set('current_user', $this->currentUser);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->counter = $this->createMock('Drupal\visitors\VisitorsCounterInterface');
    $container->set('visitors.counter', $this->counter);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_node_links_alter().
   */
  public function testRss() {
    $links = [];
    $node = $this->createMock('Drupal\node\NodeInterface');
    $context = [
      'view_mode' => 'rss',
    ];

    $this->currentUser->expects($this->never())
      ->method('hasPermission');

    visitors_node_links_alter($links, $node, $context);

    $this->assertEmpty($links);
  }

  /**
   * Tests visitors_node_links_alter().
   */
  public function testNoPermission() {
    $links = [];
    $node = $this->createMock('Drupal\node\NodeInterface');
    $context = [
      'view_mode' => 'full',
    ];

    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('view visitors counter')
      ->willReturn(FALSE);

    visitors_node_links_alter($links, $node, $context);

    $this->assertEquals('user.permissions', $links['#cache']['contexts'][0]);
  }

  /**
   * Tests visitors_node_links_alter().
   */
  public function testLinks() {
    $links = [];
    $node = $this->createMock('Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $context = [
      'view_mode' => 'full',
    ];

    $statistics_views_result = new StatisticsViewsResult(5, 0, 0);

    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('view visitors counter')
      ->willReturn(TRUE);

    $settings = $this->createMock('Drupal\Core\Config\Config');
    $settings->expects($this->once())
      ->method('get')
      ->with('counter.display_max_age')
      ->willReturn(3600);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($settings);

    $this->counter->expects($this->once())
      ->method('fetchView')
      ->with('node', 1)
      ->willReturn($statistics_views_result);

    visitors_node_links_alter($links, $node, $context);

    $this->assertEquals('user.permissions', $links['#cache']['contexts'][0]);
    $this->assertEquals(3600, $links['#cache']['max-age']);
    $this->assertIsArray($links['visitors']);
  }

}
