<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\StatisticsViewsResult;

/**
 * @coversDefaultClass \Drupal\visitors\StatisticsViewsResult
 * @group visitors
 */
class StatisticsViewsResultTest extends UnitTestCase {

  /**
   * Tests migration of node counter.
   *
   * @covers ::__construct
   * @covers ::getTotalCount
   * @covers ::getDayCount
   * @covers ::getTimestamp
   *
   * @dataProvider providerTestStatisticsCount
   */
  public function testStatisticsCount($total_count, $day_count, $timestamp) {
    $statistics = new StatisticsViewsResult($total_count, $day_count, $timestamp);
    $this->assertSame((int) $total_count, $statistics->getTotalCount());
    $this->assertSame((int) $day_count, $statistics->getDayCount());
    $this->assertSame((int) $timestamp, $statistics->getTimestamp());
  }

  /**
   * Data provider for testStatisticsCount().
   */
  public static function providerTestStatisticsCount() {
    return [
      [2, 0, 1421727536],
      [1, 0, 1471428059],
      [1, 1, 1478755275],
      ['1', '1', '1478755275'],
    ];
  }

}
