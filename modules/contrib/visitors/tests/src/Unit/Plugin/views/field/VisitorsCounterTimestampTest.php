<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsCounterTimestamp;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsCounterTimestamp
 */
class VisitorsCounterTimestampTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsCounterTimestamp
   */
  protected $field;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateFormatter;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The date time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateTime;

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateFormatStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->dateFormatter = $this->createMock('Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $this->dateFormatter);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->dateTime = $this->createMock('Drupal\Component\Datetime\TimeInterface');
    $container->set('datetime.time', $this->dateTime);

    \Drupal::setContainer($container);

    $this->dateFormatStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('date_format')
      ->willReturn($this->dateFormatStorage);

    $configuration = [];
    $plugin_id = 'visitors_counter_timestamp';
    $plugin_definition = [];
    $this->field = VisitorsCounterTimestamp::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the access method.
   *
   * @covers ::access
   */
  public function testAccess() {
    $account = $this->createMock('Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('view visitors counter')
      ->willReturn(TRUE);

    $access = $this->field->access($account);
    $this->assertTrue($access);
  }

}
