<?php

namespace Drupal\Tests\visitors_geoip\Unit\Controller\Report;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors_geoip\Controller\ReportController;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit tests for the ReportController.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors_geoip\Controller\ReportController
 * @uses \Drupal\visitors\Controller\Report\ReportBaseController
 */
class ReportControllerTest extends UnitTestCase {

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
   * The Hours controller under test.
   *
   * @var \Drupal\visitors_geoip\Controller\ReportController
   */
  protected $controller;

  /**
   * The mocked messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The mocked messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * The mocked user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $user;

  /**
   * The mocked config service.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $config;

  /**
   * The mocked location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $location;

  /**
   * The mocked module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    require_once __DIR__ . '/../../../../../tests/fixtures/views_embed_view.php';

    if (!defined('RESPONSIVE_PRIORITY_LOW')) {
      define('RESPONSIVE_PRIORITY_LOW', 'priority-low');
    }

    $container = new ContainerBuilder();

    $this->dateFormatter = $this->createMock(DateFormatterInterface::class);
    $container->set('date.formatter', $this->dateFormatter);

    $this->formBuilder = $this->createMock(FormBuilderInterface::class);
    $container->set('form_builder', $this->formBuilder);

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->user = $this->createMock('Drupal\Core\Session\AccountProxyInterface');
    $container->set('current_user', $this->user);

    $this->config = $this->createMock('Drupal\Core\Config\Config');

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->location = $this->createMock('Drupal\visitors\VisitorsLocationInterface');
    $container->set('visitors.location', $this->location);

    \Drupal::setContainer($container);

    $this->controller = ReportController::create($container);
  }

  /**
   * Tests the create() method of the Report controller.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $controller = ReportController::create($container);
    $this->assertInstanceOf(ReportController::class, $controller);
  }

  /**
   * Tests the construct() method of the Report controller.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $translation = $this->getStringTranslationStub();
    $controller = new ReportController(
      $this->formBuilder,
      $this->location,
    );

    $this->assertInstanceOf(ReportController::class, $controller);
  }

  /**
   * Adds the necessary mocks for the form builder.
   */
  protected function form() {
    // Mock the necessary objects and their methods.
    $form = $this->createMock(FormInterface::class);

    // Mock the behavior of the form builder.
    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with('Drupal\visitors\Form\DateFilter')
      ->willReturn($form);
  }

  /**
   * Tests the getCityTitle() method.
   *
   * @covers ::getCityTitle
   */
  public function testGetCityTitle() {
    $markup = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $markup->expects($this->once())
      ->method('__toString')
      ->willReturn('United States');
    $this->location->expects($this->once())
      ->method('getCountryLabel')
      ->with('US')
      ->willReturn($markup);
    $title = $this->controller->getCityTitle('US', 'Illinois', 'Chicago');
    $this->assertEquals('Chicago, Illinois, United States', (string) $title);
  }

  /**
   * Tests the region() method of the Report controller.
   *
   * @covers ::region
   */
  public function testRegion() {
    $this->form();

    $render_array = $this->controller->region('US', 'ILLinois');

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the getRegionTitle() method.
   *
   * @covers ::getRegionTitle
   */
  public function testGetRegionTitle() {
    $markup = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $markup->expects($this->once())
      ->method('__toString')
      ->willReturn('United States');
    $this->location->expects($this->once())
      ->method('getCountryLabel')
      ->with('US')
      ->willReturn($markup);
    $title = $this->controller->getRegionTitle('US', 'Illinois');

    $this->assertEquals('Illinois, United States', (string) $title);
  }

  /**
   * Tests the getRegionTitle() method.
   *
   * @covers ::getRegionTitle
   */
  public function testGetRegionTitleUnknownRegion() {
    $markup = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $markup->expects($this->once())
      ->method('__toString')
      ->willReturn('United States');
    $this->location->expects($this->once())
      ->method('getCountryLabel')
      ->with('US')
      ->willReturn($markup);
    $title = $this->controller->getRegionTitle('US', '_none');

    $this->assertEquals('Unknown, United States', (string) $title);
  }

  /**
   * Tests the city() method of the Report controller.
   *
   * @covers ::city
   */
  public function testCity() {
    $this->form();

    $render_array = $this->controller->city('US', 'IL', 'Chicago');

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);

  }

}
