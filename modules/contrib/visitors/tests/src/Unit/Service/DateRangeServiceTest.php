<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\DateRangeService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the DateRangeService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\DateRangeService
 * @uses \Drupal\visitors\Service\DateRangeService
 * @group visitors
 */
class DateRangeServiceTest extends UnitTestCase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateFormatter;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $request;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $session;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The current language.
   *
   * @var \Drupal\Core\Language\LanguageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentLanguage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $container = new ContainerBuilder();

    $this->dateFormatter = $this->createMock('\Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $this->dateFormatter);

    $this->requestStack = $this->createMock('\Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $container->set('language_manager', $this->languageManager);

    $this->request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $this->session = $this->createMock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
    $this->currentLanguage = $this->createMock('\Drupal\Core\Language\LanguageInterface');

    \Drupal::setContainer($container);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor() {

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);

    $this->assertInstanceOf('\Drupal\visitors\Service\DateRangeService', $service);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::getPeriod
   */
  public function testGetPeriod() {

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $period = $service->getPeriod();

    $this->assertEquals('day', $period);
  }

  /**
   * Tests the getStartTimestamp method.
   *
   * @covers ::getStartTimestamp
   */
  public function testGetStartTimestamp() {
    $this->currentLanguage->expects($this->once())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $start = $service->getStartTimestamp();
    $this->assertEquals(strtotime('yesterday'), $start);
  }

  /**
   * Tests the getStartDate method.
   *
   * @covers ::getStartDate
   */
  public function testGetStartDate() {
    $this->currentLanguage->expects($this->exactly(2))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(2))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $start = $service->getStartDate();
    $this->assertEquals(date('Y-m-d', strtotime('yesterday')), $start);
  }

  /**
   * Tests the getEndTimestamp method.
   *
   * @covers ::getEndTimestamp
   */
  public function testGetEndTimestamp() {
    $this->currentLanguage->expects($this->once())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $end = $service->getEndTimestamp();
    $this->assertEquals(strtotime('today'), $end);

  }

  /**
   * Tests the getEndDate method.
   *
   * @covers ::getEndDate
   */
  public function testGetEndDate() {
    $this->currentLanguage->expects($this->exactly(2))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(2))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $end = $service->getEndDate();
    $this->assertEquals(date('Y-m-d', strtotime('today') - DateRangeService::ONE_DAY), $end);
  }

  /**
   * Tests the getSummary method.
   *
   * @covers ::getSummary
   */
  public function testGetSummaryDay() {

    $visitors_from = strtotime('yesterday');
    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);
    $this->session->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap([
        ['visitors_period', NULL, 'day'],
        ['visitors_from', NULL, $visitors_from],
        ['visitors_to', NULL, strtotime('today')],
      ]);
    $formatted_date = date('l, F j, Y', $visitors_from);
    $this->dateFormatter->expects($this->once())
      ->method('format')
      ->with($visitors_from, 'custom', 'l, F j, Y')
      ->willReturn($formatted_date);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $summary = $service->getSummary();
    $this->assertEquals($formatted_date, $summary);
  }

  /**
   * Tests the getSummary method.
   *
   * @covers ::getSummary
   */
  public function testGetSummaryWeek() {

    $visitors_from = strtotime('this sunday');
    $visitors_to = strtotime('next sunday', $visitors_from);
    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);
    $this->session->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap([
        ['visitors_period', NULL, 'week'],
        ['visitors_from', NULL, $visitors_from],
        ['visitors_to', NULL, $visitors_to],
      ]);
    $visitors_to_one_day = $visitors_to - DateRangeService::ONE_DAY;
    $from_formatted_date = date('F j, Y', $visitors_from);
    $to_formatted_date = date('F j, Y', $visitors_to_one_day);

    $this->dateFormatter->expects($this->exactly(2))
      ->method('format')
      ->willReturnMap([
        [$visitors_from, 'custom', 'F j, Y', NULL, NULL, $from_formatted_date],
        [$visitors_to_one_day, 'custom', 'F j, Y', NULL, NULL, $to_formatted_date],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $summary = $service->getSummary();
    $this->assertEquals("$from_formatted_date - $to_formatted_date", $summary);
  }

  /**
   * Tests the getSummary method.
   *
   * @covers ::getSummary
   */
  public function testGetSummaryRange() {

    $visitors_from = strtotime('this week');
    $visitors_to = strtotime('+3 weeks');
    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);
    $this->session->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap([
        ['visitors_period', NULL, 'range'],
        ['visitors_from', NULL, $visitors_from],
        ['visitors_to', NULL, $visitors_to],
      ]);
    $visitors_to_one_day = $visitors_to - DateRangeService::ONE_DAY;
    $from_formatted_date = date('F j, Y', $visitors_from);
    $to_formatted_date = date('F j, Y', $visitors_to_one_day);

    $this->dateFormatter->expects($this->exactly(2))
      ->method('format')
      ->willReturnMap([
        [$visitors_from, 'custom', 'F j, Y', NULL, NULL, $from_formatted_date],
        [$visitors_to_one_day, 'custom', 'F j, Y', NULL, NULL, $to_formatted_date],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $summary = $service->getSummary();
    $this->assertEquals("$from_formatted_date - $to_formatted_date", $summary);
  }

  /**
   * Tests the getSummary method.
   *
   * @covers ::getSummary
   */
  public function testGetSummaryMonth() {
    $start_date = date('Y-m-01');
    $end_date = strtotime("$start_date +1 month");

    $visitors_from = strtotime($start_date);
    $visitors_to = $end_date;
    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);
    $this->session->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap([
        ['visitors_period', NULL, 'month'],
        ['visitors_from', NULL, $visitors_from],
        ['visitors_to', NULL, $visitors_to],
      ]);
    $from_formatted_date = date('F Y', $visitors_from);
    $this->dateFormatter->expects($this->once())
      ->method('format')
      ->with($visitors_from, 'custom', 'F Y')
      ->willReturn($from_formatted_date);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $summary = $service->getSummary();
    $this->assertEquals($from_formatted_date, $summary);
  }

  /**
   * Tests the getSummary method.
   *
   * @covers ::getSummary
   */
  public function testGetSummaryYear() {
    $start_date = date('Y-m-01');
    $end_date = strtotime("$start_date +1 year");

    $visitors_from = strtotime($start_date);
    $visitors_to = $end_date;
    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);
    $this->session->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap([
        ['visitors_period', NULL, 'year'],
        ['visitors_from', NULL, $visitors_from],
        ['visitors_to', NULL, $visitors_to],
      ]);
    $from_formatted_date = date('Y', $visitors_from);
    $this->dateFormatter->expects($this->once())
      ->method('format')
      ->with($visitors_from, 'custom', 'Y')
      ->willReturn($from_formatted_date);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $summary = $service->getSummary();
    $this->assertEquals($from_formatted_date, $summary);
  }

  /**
   * Tests the setPeriod method.
   *
   * @covers ::setPeriod
   * @covers ::setPeriodAndDates
   */
  public function testSetPeriodAndDatesInvalid() {
    $this->currentLanguage->expects($this->exactly(2))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(2))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $this->session->expects($this->exactly(3))
      ->method('set')
      ->willReturnMap([
        ['visitors_period', 'day', NULL],
        ['visitors_from', strtotime('yesterday'), NULL],
        ['visitors_to', strtotime('today'), NULL],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);

    $service->setPeriodAndDates('invalid', 'invalid', 'invalid');

  }

  /**
   * Tests the setPeriod method.
   *
   * @covers ::setPeriod
   * @covers ::setPeriodAndDates
   */
  public function testSetPeriodAndDatesDay() {
    $this->currentLanguage->expects($this->exactly(2))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(2))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $this->session->expects($this->exactly(3))
      ->method('set')
      ->willReturnMap([
        ['visitors_period', 'day', NULL],
        ['visitors_from', strtotime('yesterday'), NULL],
        ['visitors_to', strtotime('today'), NULL],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);

    $service->setPeriodAndDates('day', strtotime('yesterday'), strtotime('today'));
  }

  /**
   * Tests the setPeriod method.
   *
   * @covers ::setPeriod
   * @covers ::setPeriodAndDates
   */
  public function testSetPeriodAndDatesWeek() {
    $this->currentLanguage->expects($this->exactly(4))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(4))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $this->session->expects($this->exactly(3))
      ->method('set')
      ->willReturnMap([
        ['visitors_period', 'day', NULL],
        ['visitors_from', strtotime('yesterday'), NULL],
        ['visitors_to', strtotime('today'), NULL],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);

    $last_sunday = strtotime('today');
    $service->setPeriodAndDates('week', $last_sunday, $last_sunday);
  }

  /**
   * Tests the setPeriod method.
   *
   * @covers ::setPeriod
   * @covers ::setPeriodAndDates
   */
  public function testSetPeriodAndDatesMonth() {

    $this->currentLanguage->expects($this->exactly(4))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(4))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $this->session->expects($this->exactly(3))
      ->method('set')
      ->willReturnMap([
        ['visitors_period', 'day', NULL],
        ['visitors_from', strtotime('yesterday'), NULL],
        ['visitors_to', strtotime('today'), NULL],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $service->setPeriodAndDates('month', strtotime('this month'), strtotime('next month'));
  }

  /**
   * Tests the setPeriod method.
   *
   * @covers ::setPeriod
   * @covers ::setPeriodAndDates
   */
  public function testSetPeriodAndDatesYear() {
    $this->currentLanguage->expects($this->exactly(4))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(4))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $this->session->expects($this->exactly(3))
      ->method('set')
      ->willReturnMap([
        ['visitors_period', 'day', NULL],
        ['visitors_from', strtotime('yesterday'), NULL],
        ['visitors_to', strtotime('today'), NULL],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $service->setPeriodAndDates('year', strtotime('this year'), strtotime('next year'));
  }

  /**
   * Tests the setPeriod method.
   *
   * @covers ::setPeriod
   * @covers ::setPeriodAndDates
   */
  public function testSetPeriodAndDatesRange() {
    $this->currentLanguage->expects($this->exactly(2))
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->exactly(2))
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);

    $this->requestStack->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($this->request);
    $this->request->expects($this->once())
      ->method('getSession')
      ->willReturn($this->session);

    $this->session->expects($this->exactly(3))
      ->method('set')
      ->willReturnMap([
        ['visitors_period', 'day', NULL],
        ['visitors_from', strtotime('yesterday'), NULL],
        ['visitors_to', strtotime('today'), NULL],
      ]);

    $service = new DateRangeService($this->dateFormatter, $this->requestStack);
    $service->setPeriodAndDates('range', strtotime('this week'), strtotime('+3 weeks'));
  }

}
