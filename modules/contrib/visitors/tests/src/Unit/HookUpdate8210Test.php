<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_update_8210().
 *
 * @group visitors
 */
class HookUpdate8210Test extends UnitTestCase {

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

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_update_8210.
   */
  public function testUpdate8210(): void {
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->settings->expects($this->once())
      ->method('get')
      ->with('exclude_administer_users')
      ->willReturn(1);
    $this->settings->expects($this->exactly(2))
      ->method('set')
      ->willReturnMap([
        ['exclude_user1', FALSE],
        ['excluded_roles', ['administrator' => 'administrator']],
      ]);
    $this->settings->expects($this->once())
      ->method('save');

    visitors_update_8210();
  }

}
