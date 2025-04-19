<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests visitors_install().
 *
 * @group visitors
 */
class HookInstallTest extends UnitTestCase {

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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->urlGenerator = $this->createMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $container->set('url_generator', $this->urlGenerator);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('Drupal\Core\Config\Config');
  }

  /**
   * Tests visitors_install().
   */
  public function testIsSyncing(): void {
    $this->moduleHandler->expects($this->never())
      ->method('moduleExists');

    visitors_install(TRUE);
  }

  /**
   * Tests visitors_install().
   */
  public function testStatisticsNotInstalled(): void {
    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('statistics')
      ->willReturn(FALSE);

    visitors_install(FALSE);
  }

  /**
   * Tests visitors_install().
   */
  public function testInstall(): void {
    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('statistics')
      ->willReturn(TRUE);

    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->settings->expects($this->once())
      ->method('set')
      ->with('counter.enabled', FALSE)
      ->willReturnSelf();
    $this->settings->expects($this->once())
      ->method('save');

    $this->messenger->expects($this->once())
      ->method('addWarning');

    visitors_install(FALSE);
  }

}
