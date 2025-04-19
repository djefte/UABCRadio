<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\ReportService;
use Drupal\visitors\VisitorsReportInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * Tests the ReportService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\ReportService
 *
 * @group visitors
 */
class ReportServiceTest extends UnitTestCase {

  /**
   * The report service.
   *
   * @var \Drupal\visitors\Service\ReportService
   */
  protected $service;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $renderer;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $date;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\VisitorsDateRangeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateRange;

  /**
   * The settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The system date config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $systemDate;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->database = $this->createMock('\Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->configFactory = $this->createMock('\Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->requestStack = $this->createMock('\Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $this->renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $container->set('renderer', $this->renderer);

    $this->date = $this->createMock('\Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $this->date);

    $this->entityTypeManager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->moduleHandler = $this->createMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->dateRange = $this->createMock('\Drupal\visitors\VisitorsDateRangeInterface');
    $container->set('visitors.date_range', $this->dateRange);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $this->settings->expects($this->any())
      ->method('get')
      ->with('items_per_page')
      ->willReturn(10);
    $this->systemDate = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $this->systemDate->expects($this->any())
      ->method('get')
      ->with('first_day')
      ->willReturn(0);

    $this->configFactory->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['visitors.config', $this->settings],
        ['system.date', $this->systemDate],
      ]);

    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $this->requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($request);
    $query = new InputBag();

    $request->query = $query;

    $this->service = new ReportService(
      $this->database,
      $this->configFactory,
      $this->requestStack,
      $this->renderer,
      $this->date,
      $this->entityTypeManager,
      $this->moduleHandler,
      $this->dateRange,
    );
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $service = new ReportService(
      $this->database,
      $this->configFactory,
      $this->requestStack,
      $this->renderer,
      $this->date,
      $this->entityTypeManager,
      $this->moduleHandler,
      $this->dateRange,
    );
    $this->assertInstanceOf(ReportService::class, $service);
  }

  /**
   * Tests the addDateFilter method.
   *
   * @covers ::addDateFilter
   */
  public function testAddDateFilter(): void {
    $this->dateRange->expects($this->once())
      ->method('getStartTimestamp')
      ->willReturn(100);
    $this->dateRange->expects($this->once())
      ->method('getEndTimestamp')
      ->willReturn(200);

    $query = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $query->expects($this->once())
      ->method('condition')
      ->with('visitors_date_time', [100, 200], 'BETWEEN')
      ->willReturnSelf();

    // Make addDateFilter method public.
    $method = new \ReflectionMethod($this->service, 'addDateFilter');
    $method->setAccessible(TRUE);
    $method->invokeArgs($this->service, [&$query]);
  }

  /**
   * Tests the setReferrersCondition method with no date range.
   *
   * @covers ::setReferrersCondition
   */
  public function testSetReferrersConditionInternal(): void {
    $_SERVER['HTTP_HOST'] = 'example.com';
    $_SESSION['referer_type'] = VisitorsReportInterface::REFERER_TYPE_INTERNAL_PAGES;
    $query = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $query->expects($this->exactly(2))
      ->method('condition')
      ->willReturnMap([
        ['referrer_url', '%example.com%', 'LIKE'],
        ['referrer_url', '', '<>'],
      ]);

    // Make addDateFilter method public.
    $method = new \ReflectionMethod($this->service, 'setReferrersCondition');
    $method->setAccessible(TRUE);
    $method->invoke($this->service, $query);
  }

  /**
   * Tests the setReferrersCondition method with no date range.
   *
   * @covers ::setReferrersCondition
   */
  public function testSetReferrersConditionExternal(): void {
    $_SERVER['HTTP_HOST'] = 'example.com';
    $_SESSION['referer_type'] = VisitorsReportInterface::REFERER_TYPE_EXTERNAL_PAGES;
    $query = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $query->expects($this->once())
      ->method('condition')
      ->willReturnMap([
        ['referrer_url', '%example.com%', 'NOT LIKE'],
      ]);

    // Make addDateFilter method public.
    $method = new \ReflectionMethod($this->service, 'setReferrersCondition');
    $method->setAccessible(TRUE);
    $method->invoke($this->service, $query);
  }

  /**
   * Tests the referer method.
   *
   * @covers ::referer
   */
  public function testReferer(): void {
    $_SERVER['HTTP_HOST'] = 'example.com';
    $_SESSION['referer_type'] = VisitorsReportInterface::REFERER_TYPE_EXTERNAL_PAGES;
    $select = $this->createMock('\Drupal\Core\Database\Query\TableSortExtender');
    $select->expects($this->exactly(2))
      ->method('extend')
      ->willReturnSelf();
    $this->database->expects($this->exactly(2))
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $select->expects($this->once())
      ->method('execute')
      ->willReturn([
        (object) ['referrer_url' => 'http://example.com', 'count' => 1],
      ]);

    $header = [
      'referrer_url' => [
        'data'      => 'Referer',
        'field'     => 'referrer_url',
        'specifier' => 'referrer_url',
      ],
      'count' => [
        'data'      => 'Count',
        'field'     => 'count',
        'specifier' => 'count',
        'sort'      => 'desc',
      ],
    ];

    $this->service->referer($header);

  }

  /**
   * Tests the hitDetails method.
   *
   * @covers ::hitDetails
   */
  public function testHitDetails(): void {
    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('visitors_geoip')
      ->willReturn(TRUE);

    $select = $this->createMock('\Drupal\Core\Database\Query\TableSortExtender');
    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);
    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);
    $statement->expects($this->once())
      ->method('fetch')
      ->willReturn((object) [
        'visitor_id' => 'visitor_id_string',
        'visitors_referer' => 'http://example.com',
        'visitors_date_time' => 100,
        'visitors_ip' => '127.0.0.1',
        'visitors_url' => 'http://example.com',
        'visitors_title' => 'Title',
      ]);

    $this->service->hitDetails(1);
  }

}
