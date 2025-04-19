<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\SequenceService;
use Drupal\views\ResultRow;

/**
 * Tests the SequenceService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\SequenceService
 *
 * @group visitors
 */
class SequenceServiceTest extends UnitTestCase {

  /**
   * Tests the fill method when result is empty.
   *
   * @covers ::fill
   */
  public function testEmptyResult(): void {
    $result = [];
    $this->assertCount(0, SequenceService::fill($result));
  }

  /**
   * Tests the fill method when hour.
   *
   * @covers ::fill
   * @covers ::hours
   * @covers ::integer
   */
  public function testHour(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_hour' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_hour' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(24, $response);
  }

  /**
   * Tests the fill method when hour.
   *
   * @covers ::fill
   * @covers ::hours
   * @covers ::integer
   */
  public function testHourOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_hour' => 2,
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_hour' => 1,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(24, $response);
  }

  /**
   * Tests the fill method when hour.
   *
   * @covers ::fill
   * @covers ::hours
   * @covers ::integer
   */
  public function testDuplicateHour(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_hour' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_hour' => 2,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_hour' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(24, $response);
  }

  /**
   * Tests the fill method when local hour.
   *
   * @covers ::fill
   * @covers ::hours
   * @covers ::integer
   */
  public function testLocalHour(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_visitor_localtime' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_visitor_localtime' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(24, $response);
  }

  /**
   * Tests the fill method when local hour.
   *
   * @covers ::fill
   * @covers ::hours
   * @covers ::integer
   */
  public function testLocalHourOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_visitor_localtime' => 2,
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_visitor_localtime' => 1,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(24, $response);
  }

  /**
   * Tests the fill method when local hour.
   *
   * @covers ::fill
   * @covers ::hours
   * @covers ::integer
   */
  public function testLocalHourDuplicate(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_visitor_localtime' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_visitor_localtime' => 2,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_visitor_localtime' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(24, $response);
  }

  /**
   * Tests the fill method when month.
   *
   * @covers ::fill
   * @covers ::dayOfMonth
   * @covers ::integer
   */
  public function testDayOfMonth(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_day_of_month' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day_of_month' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(31, $response);
  }

  /**
   * Tests the fill method when month.
   *
   * @covers ::fill
   * @covers ::dayOfMonth
   * @covers ::integer
   */
  public function testDayOfMonthOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_day_of_month' => 2,
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_day_of_month' => 1,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(31, $response);
  }

  /**
   * Tests the fill method when month.
   *
   * @covers ::fill
   * @covers ::dayOfMonth
   * @covers ::integer
   */
  public function testDuplicateDayOfMonth(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_day_of_month' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day_of_month' => 2,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day_of_month' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(31, $response);
  }

  /**
   * Tests the fill method when day of week.
   *
   * @covers ::fill
   * @covers ::daysOfWeek
   * @covers ::integer
   */
  public function testDayOfWeek(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_day_of_week' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day_of_week' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(7, $response);
  }

  /**
   * Tests the fill method when day of week.
   *
   * @covers ::fill
   * @covers ::daysOfWeek
   * @covers ::integer
   */
  public function testDayOfWeekOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_day_of_week' => 2,
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_day_of_week' => 1,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(7, $response);
  }

  /**
   * Tests the fill method when day of week.
   *
   * @covers ::fill
   * @covers ::daysOfWeek
   * @covers ::integer
   */
  public function testDuplicateDayOfWeek(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_day_of_week' => 1,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day_of_week' => 2,
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day_of_week' => 2,
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(7, $response);
  }

  /**
   * Tests the fill method when days.
   *
   * @covers ::fill
   * @covers ::days
   */
  public function testDays(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_day' => '2025-03-01',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day' => '2025-03-03',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(3, $response);
  }

  /**
   * Tests the fill method when days.
   *
   * @covers ::fill
   * @covers ::days
   */
  public function testDaysOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_day' => '2025-03-03',
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_day' => '2025-03-01',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(2, $response);
  }

  /**
   * Tests the fill method when days.
   *
   * @covers ::fill
   * @covers ::days
   */
  public function testDuplicateDays(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_day' => '2025-03-01',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day' => '2025-03-03',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_day' => '2025-03-03',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(4, $response);
  }

  /**
   * Tests the fill method when weeks.
   *
   * @covers ::fill
   * @covers ::weeks
   * @covers ::date
   */
  public function testWeeks(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_week' => '202401',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_week' => '202503',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(55, $response);
  }

  /**
   * Tests the fill method when weeks.
   *
   * @covers ::fill
   * @covers ::weeks
   * @covers ::date
   */
  public function testWeeksOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_week' => '202503',
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_week' => '202401',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(2, $response);
  }

  /**
   * Tests the fill method when weeks.
   *
   * @covers ::fill
   * @covers ::weeks
   * @covers ::date
   */
  public function testDuplicateWeeks(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_week' => '202401',
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_week' => '202401',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_week' => '202503',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(55, $response);
  }

  /**
   * Tests the fill method when months.
   *
   * @covers ::fill
   * @covers ::months
   * @covers ::date
   */
  public function testMonths(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_month' => '202412',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_month' => '202512',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(13, $response);
  }

  /**
   * Tests the fill method when months.
   *
   * @covers ::fill
   * @covers ::months
   * @covers ::date
   */
  public function testMonthsOutOfOrder(): void {
    $result = [
      new ResultRow([
        'count' => 10,
        'visitors_month' => '202512',
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_month' => '202412',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(2, $response);
  }

  /**
   * Tests the fill method when months.
   *
   * @covers ::fill
   * @covers ::months
   * @covers ::date
   */
  public function testDuplicateMonths(): void {
    $result = [
      new ResultRow([
        'count' => 5,
        'visitors_month' => '202412',
      ]),
      new ResultRow([
        'count' => 5,
        'visitors_month' => '202412',
      ]),
      new ResultRow([
        'count' => 10,
        'visitors_month' => '202512',
      ]),
    ];
    $response = SequenceService::fill($result);
    $this->assertCount(13, $response);
  }

}
