<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\OnlineService;
use Drupal\visitors\VisitorsOnlineInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the OnlineService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\OnlineService
 *
 * @group visitors
 */
class OnlineServiceTest extends UnitTestCase {

  /**
   * The current time.
   */
  const NOW = 1741560323;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $time;

  /**
   * The online service.
   *
   * @var \Drupal\visitors\Service\OnlineService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->database = $this->createMock('\Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->time = $this->createMock('\Drupal\Component\Datetime\TimeInterface');
    $container->set('datetime.time', $this->time);

    \Drupal::setContainer($container);

    $this->service = new OnlineService($this->database, $this->time);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $service = new OnlineService($this->database, $this->time);
    $this->assertInstanceOf(OnlineService::class, $service);
  }

  /**
   * Tests the getLast30Minutes method.
   *
   * @covers ::getLast30Minutes
   * @covers ::query
   */
  public function testGetLast30Minutes(): void {
    $end = self::NOW;
    $start = $end - VisitorsOnlineInterface::MINUTE_30;
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(self::NOW);

    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(1);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('addExpression')
      ->with('COUNT(DISTINCT v.visitor_id)', 'count')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.visitors_date_time', [$start, $end], 'BETWEEN')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $count = $this->service->getLast30Minutes();

    $this->assertEquals(1, $count);
    $this->assertEquals(VisitorsOnlineInterface::MINUTE_30, ($end - $start));
  }

  /**
   * Tests the getLast24Hours method.
   *
   * @covers ::getLast24Hours
   * @covers ::query
   */
  public function testGetLast24Hours(): void {
    $end = self::NOW;
    $start = $end - VisitorsOnlineInterface::HOUR_24;
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(self::NOW);

    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(1);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('addExpression')
      ->with('COUNT(DISTINCT v.visitor_id)', 'count')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.visitors_date_time', [$start, $end], 'BETWEEN')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $count = $this->service->getLast24Hours();

    $this->assertEquals(1, $count);
    $this->assertEquals(VisitorsOnlineInterface::HOUR_24, ($end - $start));
  }

  /**
   * Tests the getYesterday30Minutes method.
   *
   * @covers ::getYesterday30Minutes
   * @covers ::query
   */
  public function testGetYesterday30Minutes(): void {
    $end = self::NOW - VisitorsOnlineInterface::HOUR_24;
    $start = $end - VisitorsOnlineInterface::MINUTE_30;
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(self::NOW);

    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(1);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('addExpression')
      ->with('COUNT(DISTINCT v.visitor_id)', 'count')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.visitors_date_time', [$start, $end], 'BETWEEN')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $count = $this->service->getYesterday30Minutes();

    $this->assertEquals(1, $count);
    $this->assertEquals(VisitorsOnlineInterface::MINUTE_30, ($end - $start));
  }

  /**
   * Tests the getYesterday24Hours method.
   *
   * @covers ::getYesterday24Hours
   * @covers ::query
   */
  public function testGetYesterday24Hours(): void {
    $end = self::NOW - VisitorsOnlineInterface::HOUR_24;
    $start = $end - VisitorsOnlineInterface::HOUR_24;
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(self::NOW);

    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(1);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('addExpression')
      ->with('COUNT(DISTINCT v.visitor_id)', 'count')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.visitors_date_time', [$start, $end], 'BETWEEN')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $count = $this->service->getYesterday24Hours();

    $this->assertEquals(1, $count);
    $this->assertEquals(VisitorsOnlineInterface::HOUR_24, ($end - $start));
  }

  /**
   * Tests the getLastWeek30Minutes method.
   *
   * @covers ::getLastWeek30Minutes
   * @covers ::query
   */
  public function testGetLastWeek30Minutes(): void {
    $end = self::NOW - VisitorsOnlineInterface::DAY_7;
    $start = $end - VisitorsOnlineInterface::MINUTE_30;
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(self::NOW);

    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(1);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('addExpression')
      ->with('COUNT(DISTINCT v.visitor_id)', 'count')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.visitors_date_time', [$start, $end], 'BETWEEN')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $count = $this->service->getLastWeek30Minutes();

    $this->assertEquals(1, $count);
    $this->assertEquals(VisitorsOnlineInterface::MINUTE_30, ($end - $start));
  }

  /**
   * Tests the getLastWeek24Hours method.
   *
   * @covers ::getLastWeek24Hours
   * @covers ::query
   */
  public function testGetLastWeek24Hours(): void {
    $end = self::NOW - VisitorsOnlineInterface::DAY_7;
    $start = $end - VisitorsOnlineInterface::HOUR_24;
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(self::NOW);

    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(1);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('addExpression')
      ->with('COUNT(DISTINCT v.visitor_id)', 'count')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.visitors_date_time', [$start, $end], 'BETWEEN')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);

    $count = $this->service->getLastWeek24Hours();

    $this->assertEquals(1, $count);
    $this->assertEquals(VisitorsOnlineInterface::HOUR_24, ($end - $start));
  }

}
