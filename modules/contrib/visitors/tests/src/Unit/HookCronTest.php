<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_cron.
 *
 * @group visitors
 */
class HookCronTest extends UnitTestCase {

  /**
   * The cron service.
   *
   * @var \Drupal\visitors\VisitorsCronInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cron;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->cron = $this->createMock('Drupal\visitors\VisitorsCronInterface');
    $container->set('visitors.cron', $this->cron);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_cron().
   */
  public function testVisitorsCron() {
    $this->cron->expects($this->once())
      ->method('execute');

    visitors_cron();
  }

}
