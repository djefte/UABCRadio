<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_page_attachments.
 *
 * @group visitors
 */
class HookPageAttachmentsTest extends UnitTestCase {

  /**
   * The cron service.
   *
   * @var \Drupal\visitors\VisitorsPageAttachmentsInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->service = $this->createMock('Drupal\visitors\VisitorsPageAttachmentsInterface');
    $container->set('visitors.page_attachments', $this->service);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_page_attachments().
   */
  public function testVisitorsCron() {
    $page = [];
    $this->service->expects($this->once())
      ->method('pageAttachments')
      ->with($page);

    visitors_page_attachments($page);
  }

}
