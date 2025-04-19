<?php

namespace Drupal\Tests\visitors\Unit\Controller\Report;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Controller\Report\ReportController;
use Drupal\visitors\VisitorsReportInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * Unit tests for the ReportController.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Controller\Report\ReportController
 * @uses \Drupal\visitors\Controller\Report\ReportController
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
   * @var \Drupal\visitors\Controller\Report\ReportController
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
    require_once __DIR__ . '/../../../../fixtures/views_embed_view.php';

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

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->config = $this->createMock('Drupal\Core\Config\Config');
    $this->configFactory
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->config);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->user = $this->createMock('Drupal\Core\Session\AccountProxyInterface');
    $container->set('current_user', $this->user);

    $this->location = $this->createMock('Drupal\visitors\VisitorsLocationInterface');
    $container->set('visitors.location', $this->location);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

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
      $translation,
      $this->configFactory,
      $this->messenger,
      $this->user,
      $this->location,
      $this->moduleHandler
    );

    $this->assertInstanceOf(ReportController::class, $controller);
  }

  /**
   * Tests the time() method of the report controller.
   *
   * @covers ::time
   */
  public function testTime(): void {
    $this->form();

    // Execute the time() method.
    $render_array = $this->controller->time();

    // Assertions for the returned render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(6, $render_array['main']);
  }

  /**
   * Tests the topPages() method.
   *
   * @covers ::topPages
   */
  public function testTopPages(): void {
    $this->form();

    $render_array = $this->controller->topPages();

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the topHost() method of the Report controller.
   *
   * @covers ::topHost
   */
  public function testTopHost(): void {
    $this->form();

    // Execute the topHost() method.
    $render_array = $this->controller->topHost();

    // Assertions for the returned render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the recentHost() method.
   *
   * @covers ::recentHost
   */
  public function testRecent(): void {
    $this->form();

    $render_array = $this->controller->recentHost('10.10.10.10');

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the getHostTitle() method.
   *
   * @covers ::getHostTitle
   */
  public function testHostTitle(): void {
    $title = $this->controller->getHostTitle('10.10.10.10');
    $this->assertEquals('Visits from 10.10.10.10', $title);
  }

  /**
   * Tests the topRoute() method.
   *
   * @covers ::topRoute
   */
  public function testTopRoute(): void {
    $this->form();

    $render_array = $this->controller->topRoute();

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the recentRoute() method.
   *
   * @covers ::recentRoute
   */
  public function testRecentRoute(): void {
    $this->form();

    $render_array = $this->controller->recentRoute('entity.node.canonical');

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the getRouteTitle() method.
   *
   * @covers ::getRouteTitle
   */
  public function testRouteTitle(): void {
    $title = $this->controller->getRouteTitle('entity.node.canonical');
    $this->assertEquals('Route entity.node.canonical', $title);
  }

  /**
   * Tests the recentViews() method of the Report controller.
   *
   * @covers ::recentViews
   */
  public function testRecentViews(): void {
    $this->form();

    // Call the recentViews() method.
    $render_array = $this->controller->recentViews();

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the software() method of the Report controller.
   *
   * @covers ::software
   */
  public function testSoftware(): void {
    $this->form();

    // Call the software() method.
    $render_array = $this->controller->software();

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(4, $render_array['main']);
  }

  /**
   * Tests the device() method of the Report controller.
   *
   * @covers ::device
   */
  public function testDevice(): void {
    $this->form();

    // Call the device() method.
    $render_array = $this->controller->device();

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(4, $render_array['main']);
  }

  /**
   * Tests the location() method of the Report controller.
   *
   * @covers ::location
   */
  public function testLocation(): void {
    $this->form();

    // Call the location() method.
    $render_array = $this->controller->location();

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(5, $render_array['main']);
  }

  /**
   * Tests the performance() method of the Report controller.
   *
   * @covers ::performance
   */
  public function testPerformance(): void {
    $this->form();

    $this->config->expects($this->never())
      ->method('get');

    // Call the performance() method.
    $render_array = $this->controller->performance();

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertCount(2, $render_array);
  }

  /**
   * Tests the performance() method of the Report controller.
   *
   * @covers ::performance
   */
  public function testPerformanceHour(): void {
    $this->form();

    $this->config->expects($this->never())
      ->method('get');

    // Call the performance() method.
    $render_array = $this->controller->performance('hour');

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertCount(2, $render_array);
  }

  /**
   * Tests the performance() method of the Report controller.
   *
   * @covers ::performance
   */
  public function testPerformanceDay(): void {
    $this->form();

    $this->config->expects($this->never())
      ->method('get');

    // Call the performance() method.
    $render_array = $this->controller->performance('day');

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertCount(2, $render_array);
  }

  /**
   * Tests the nodeViews() method of the Report controller.
   *
   * @covers ::nodeViews
   */
  public function testNode(): void {
    $this->form();

    // Call the performance() method.
    $render_array = $this->controller->nodeViews(1);

    // Assert the expected render array.
    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertCount(2, $render_array);
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
   * Tests the getContinentTitle() method.
   *
   * @covers ::getContinentTitle
   */
  public function testGetContinentTitle() {
    $markup = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $markup->expects($this->once())
      ->method('__toString')
      ->willReturn('Europe');
    $this->location->expects($this->once())
      ->method('getContinentLabel')
      ->with('EU')
      ->willReturn($markup);
    $title = $this->controller->getContinentTitle('EU');
    $this->assertEquals('Europe', (string) $title);
  }

  /**
   * Tests the continent() method of the Report controller.
   *
   * @covers ::continent
   */
  public function testContinent() {
    $this->form();

    $render_array = $this->controller->continent('NA');

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the getCountryTitle() method.
   *
   * @covers ::getCountryTitle
   */
  public function testGetCountryTitle() {
    $markup = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $markup->expects($this->once())
      ->method('__toString')
      ->willReturn('United States');
    $this->location->expects($this->once())
      ->method('getCountryLabel')
      ->with('US')
      ->willReturn($markup);
    $title = $this->controller->getCountryTitle('US');
    $this->assertEquals('United States', (string) $title);
  }

  /**
   * Tests the country() method of the Report controller.
   *
   * @covers ::country
   */
  public function testCountry() {
    $this->form();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('visitors_geoip')
      ->willReturn(TRUE);

    $render_array = $this->controller->country('US');

    $this->assertArrayHasKey('visitors_date_filter_form', $render_array);
    $this->assertArrayHasKey('main', $render_array);
    $this->assertCount(3, $render_array['main']);
  }

  /**
   * Tests the report() method of the Report controller.
   *
   * @covers ::report
   */
  public function testReport() {
    $view_id = 'visitors';
    $display_id = 'daily_column';
    $query = new InputBag();
    $query->set('class', 'visitors-report');
    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request->query = $query;

    $response = $this->controller->report($request, $view_id, $display_id);

    $this->assertInstanceOf('Drupal\Core\Ajax\AjaxResponse', $response);
  }

  /**
   * Tests the renderViews() method of the ReportBaseController.
   *
   * @covers Drupal\visitors\Controller\Report\ReportBaseController::renderViews
   */
  public function testRenderViews() {

    $render_array = $this->controller->renderViews([
      '#view_id' => 'visitors',
      '#view_display' => 'daily_column',
    ]);

    $this->assertCount(3, $render_array);

  }

  /**
   * Tests the renderViews() method of the ReportBaseController.
   *
   * @covers Drupal\visitors\Controller\Report\ReportBaseController::renderViews
   */
  public function testRenderViews2() {

    $first_row['performance_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'performance_hourly_column',
    ];
    $render_array = $this->controller->renderViews($first_row);

    $this->assertCount(3, $render_array);

  }

  /**
   * Tests the renderViews() method of the ReportBaseController.
   *
   * @covers Drupal\visitors\Controller\Report\ReportBaseController::renderViews
   */
  public function testRenderViews3() {

    $render_array = $this->controller->renderViews([[
      '#view_id' => 'visitors',
      '#view_display' => 'daily_column',
    ],
    ], 'layout-row', 'node/1');

    $this->assertCount(3, $render_array);

  }

  /**
   * Tests the renderViews() method of the ReportBaseController.
   *
   * @covers Drupal\visitors\Controller\Report\ReportBaseController::renderViews
   */
  public function testRenderViews4() {

    $render_array = $this->controller->renderViews([[
      '#view_id' => 'visitors',
      '#view_display' => 'daily_column',
      '#attributes' => ['class' => ['visitors-report']],
    ],
    ], 'layout-row', 'node/1');

    $this->assertCount(3, $render_array);

  }

  /**
   * Tests the renderViews() method of the ReportBaseController.
   *
   * @covers Drupal\visitors\Controller\Report\ReportBaseController::renderViews
   */
  public function testRenderViews5() {
    $third_row['city_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'city_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $render_array = $this->controller->renderViews($third_row, 'layout-row');

    $this->assertCount(3, $render_array);

  }

}
