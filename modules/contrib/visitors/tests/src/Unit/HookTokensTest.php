<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\StatisticsViewsResult;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_tokens.
 *
 * @group visitors
 */
class HookTokensTest extends UnitTestCase {

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
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $token;

  /**
   * The visitors counter.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $counter;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateFormatter;

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

    $this->token = $this->createMock('Drupal\Core\Utility\Token');
    $container->set('token', $this->token);

    $this->counter = $this->createMock('Drupal\visitors\VisitorsCounterInterface');
    $container->set('visitors.counter', $this->counter);

    $this->dateFormatter = $this->createMock('Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $this->dateFormatter);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_tokens().
   */
  public function testVisitorsTokens() {
    $tokens = [
      'total-count' => '[node:total-count]',
      'day-count' => '[node:day-count]',
      'last-view' => '[node:last-view]',
    ];
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);
    $this->settings->expects($this->once())
      ->method('get')
      ->with('counter.entity_types')
      ->willReturn(['node']);

    $this->token->expects($this->once())
      ->method('findWithPrefix')
      ->with($tokens, 'last-view')
      ->willReturn(['[node:last-view]' => 1234567890]);
    $this->token->expects($this->once())
      ->method('generate')
      ->willReturn(['[node:last-view]' => 'Fri Feb 13 2009 23:31:30']);

    $this->dateFormatter->expects($this->once())
      ->method('format')
      ->with(1234567890)
      ->willReturn('Fri Feb 13 2009 23:31:30');

    $counter_result = new StatisticsViewsResult(5, 2, 1234567890);
    $this->counter->expects($this->once())
      ->method('fetchView')
      ->with('node', 1)
      ->willReturn($counter_result);

    $node = $this->createMock('Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $metadata = $this->createMock('Drupal\Core\Render\BubbleableMetadata');

    $replacements = visitors_tokens('node', $tokens, ['node' => $node], [], $metadata);

    $this->assertEquals(3, count($replacements));
    $this->assertArrayHasKey('[node:total-count]', $replacements);
    $this->assertEquals(5, $replacements['[node:total-count]']);
    $this->assertArrayHasKey('[node:day-count]', $replacements);
    $this->assertEquals(2, $replacements['[node:day-count]']);
    $this->assertArrayHasKey('[node:last-view]', $replacements);
    $this->assertEquals('Fri Feb 13 2009 23:31:30', $replacements['[node:last-view]']);
  }

  /**
   * Tests visitors_tokens() with non-entity type.
   */
  public function testVisitorsTokensNonEntity() {

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);
    $this->settings->expects($this->once())
      ->method('get')
      ->with('counter.entity_types')
      ->willReturn(['node']);

    $metadata = $this->createMock('Drupal\Core\Render\BubbleableMetadata');

    $replacements = visitors_tokens('user', [], [], [], $metadata);

    $this->assertEquals(0, count($replacements));
  }

}
