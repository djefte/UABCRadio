<?php

namespace Drupal\Tests\visitors\Unit\Controller\Report;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Controller\Report\Referer;
use Drupal\visitors\VisitorsReportInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the Referer controller.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Controller\Report\Referer
 * @uses \Drupal\visitors\Controller\Report\Referer
 */
class RefererTest extends UnitTestCase {

  /**
   * The mocked date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateFormatter;

  /**
   * The mocked form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $formBuilder;

  /**
   * The mocked visitors report service.
   *
   * @var \Drupal\visitors\VisitorsReportInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $report;

  /**
   * The Referer controller instance.
   *
   * @var \Drupal\visitors\Controller\Report\Referer
   */
  protected $controller;

  /**
   * The mocked string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    if (!defined('RESPONSIVE_PRIORITY_LOW')) {
      define('RESPONSIVE_PRIORITY_LOW', 'priority-low');
    }

    $container = new ContainerBuilder();

    $this->dateFormatter = $this->createMock(DateFormatterInterface::class);
    $container->set('date.formatter', $this->dateFormatter);

    $this->formBuilder = $this->createMock(FormBuilderInterface::class);
    $container->set('form_builder', $this->formBuilder);

    $this->report = $this->createMock(VisitorsReportInterface::class);
    $container->set('visitors.report', $this->report);

    $this->stringTranslation = $this->getStringTranslationStub();
    $container->set('string_translation', $this->stringTranslation);

    \Drupal::setContainer($container);

    $this->controller = Referer::create($container);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $controller = Referer::create($container);
    $this->assertInstanceOf(Referer::class, $controller);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $controller = new Referer($this->dateFormatter, $this->formBuilder, $this->report, $this->stringTranslation);
    $this->assertInstanceOf(Referer::class, $controller);
  }

  /**
   * Tests the display method.
   *
   * @covers ::display
   * @covers ::getHeader
   */
  public function testDisplay(): void {
    $date_form = $this->createMock(FormInterface::class);
    $referer_form = $this->createMock(FormInterface::class);

    // Mock the behavior of the form builder.
    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->willReturnMap([
        ['Drupal\visitors\Form\Referer', $referer_form],
        ['Drupal\visitors\Form\DateFilter', $date_form],
      ]);

    $this->report->expects($this->once())
      ->method('referer')
      ->willReturn([]);

    // Call the display() method.
    $renderArray = $this->controller->display();

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $renderArray);
    $this->assertArrayHasKey('visitors_table', $renderArray);
    $this->assertArrayHasKey('visitors_pager', $renderArray);

  }

}
