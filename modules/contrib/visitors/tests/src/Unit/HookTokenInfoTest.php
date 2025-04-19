<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_token_info.
 *
 * @group visitors
 */
class HookTokenInfoTest extends UnitTestCase {

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_token_info().
   */
  public function testVisitorsTokenInfo() {
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);
    $this->settings->expects($this->once())
      ->method('get')
      ->with('counter.entity_types')
      ->willReturn(['node']);

    $token_info = visitors_token_info();

    $this->assertArrayHasKey('tokens', $token_info);
    $this->assertArrayHasKey('node', $token_info['tokens']);
    $this->assertArrayHasKey('total-count', $token_info['tokens']['node']);
    $this->assertArrayHasKey('day-count', $token_info['tokens']['node']);
    $this->assertArrayHasKey('last-view', $token_info['tokens']['node']);
  }

}
