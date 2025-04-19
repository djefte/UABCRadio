<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\CounterService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the CookieService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\CounterService
 * @uses \Drupal\visitors\Service\CounterService
 * @uses \Drupal\visitors\StatisticsViewsResult
 * @group visitors
 */
class CounterServiceTest extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The date service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $time;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $state;

  /**
   * The counter service.
   *
   * @var \Drupal\visitors\Service\CounterService
   */
  protected $counter;

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

    $this->state = $this->createMock('\Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    \Drupal::setContainer($container);

    $this->counter = new CounterService($this->database, $this->time, $this->state);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $counter = new CounterService($this->database, $this->time, $this->state);
    $this->assertInstanceOf(CounterService::class, $counter);
  }

  /**
   * Tests the recordView method.
   *
   * @covers ::recordView
   */
  public function testRecordView() {
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(1234567890);

    $merge = $this->createMock('\Drupal\Core\Database\Query\Merge');
    $merge->expects($this->exactly(2))
      ->method('key')
      ->willReturnSelf();
    $merge->expects($this->once())
      ->method('fields')
      ->with([
        'today' => 1,
        'total' => 1,
        'timestamp' => 1234567890,
      ])
      ->willReturnSelf();
    $merge->expects($this->exactly(2))
      ->method('expression')
      ->willReturnSelf();
    $merge->expects($this->once())
      ->method('execute')
      ->willReturn(1);

    $this->database->expects($this->once())
      ->method('merge')
      ->with('visitors_counter')
      ->willReturn($merge);

    $this->assertTrue($this->counter->recordView('node', 1));
  }

  /**
   * Tests the fetchViews method.
   *
   * @covers ::fetchViews
   */
  public function testFetchViews() {
    $object = new \stdClass();
    $object->today = 1;
    $object->total = 1;
    $object->timestamp = 1234567890;
    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn([1 => $object]);

    $select = $this->createMock('\Drupal\Core\Database\Query\Select');

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);
    $select->expects($this->once())
      ->method('fields')
      ->willReturnSelf();
    $select->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $views = $this->counter->fetchViews('node', [1]);
    $this->assertCount(1, $views);
    $view = reset($views);

    $this->assertEquals(1, $view->getTotalCount());
    $this->assertEquals(1, $view->getDayCount());
    $this->assertEquals(1234567890, $view->getTimestamp());
  }

  /**
   * Tests the fetchViews method.
   *
   * @covers ::fetchView
   */
  public function testFetchView() {
    $object = new \stdClass();
    $object->today = 1;
    $object->total = 1;
    $object->timestamp = 1234567890;
    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn([1 => $object]);

    $select = $this->createMock('\Drupal\Core\Database\Query\Select');

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);
    $select->expects($this->once())
      ->method('fields')
      ->willReturnSelf();
    $select->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $view = $this->counter->fetchView('node', 1);

    $this->assertEquals(1, $view->getTotalCount());
    $this->assertEquals(1, $view->getDayCount());
    $this->assertEquals(1234567890, $view->getTimestamp());
  }

  /**
   * Tests the fetchAll method.
   *
   * @covers ::fetchAll
   */
  public function testFetchAll() {
    $object = new \stdClass();
    $object->today = 1;
    $object->total = 1;
    $object->timestamp = 1234567890;
    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchCol')
      ->willReturn(10);

    $select = $this->createMock('\Drupal\Core\Database\Query\Select');

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);
    $select->expects($this->once())
      ->method('fields')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('entity_type', 'node')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('orderBy')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('range')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $views = $this->counter->fetchAll('node');

    $this->assertEquals(10, $views);

  }

  /**
   * Tests the fetchAll method with invalid order.
   *
   * @covers ::fetchAll
   */
  public function testFetchAllInvalidOrder() {
    $this->expectException(\AssertionError::class);
    $this->expectExceptionMessage('Invalid order argument.');
    $this->counter->fetchAll('node', 'invalid');
  }

  /**
   * Tests the resetDayCount method.
   *
   * @covers ::resetDayCount
   */
  public function testResetDayCount() {
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(1234567890);

    $this->state->expects($this->once())
      ->method('get')
      ->with('visitors.count_timestamp', 0)
      ->willReturn(0);

    $this->state->expects($this->once())
      ->method('set')
      ->with('visitors.count_timestamp', 1234567890)
      ->willReturn(NULL);

    $update = $this->createMock('\Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with(['today' => 0])
      ->willReturnSelf();

    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors_counter')
      ->willReturn($update);

    $this->assertNull($this->counter->resetDayCount());
  }

  /**
   * Tests the resetDayCount method.
   *
   * @covers ::resetDayCount
   */
  public function testResetDayCountLessThanDay() {
    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(1234567890);

    $this->state->expects($this->once())
      ->method('get')
      ->with('visitors.count_timestamp', 0)
      ->willReturn(1234567890);

    $this->state->expects($this->never())
      ->method('set');

    $this->database->expects($this->never())
      ->method('update');

    $this->assertNull($this->counter->resetDayCount());
  }

  /**
   * Tests the deleteViews method.
   *
   * @covers ::deleteViews
   */
  public function testDeleteViews() {
    $delete = $this->createMock('\Drupal\Core\Database\Query\Delete');
    $delete->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $delete->expects($this->once())
      ->method('execute')
      ->willReturn(1);
    $this->database->expects($this->once())
      ->method('delete')
      ->with('visitors_counter')
      ->willReturn($delete);

    $this->assertTrue($this->counter->deleteViews('node', 1));
  }

  /**
   * Tests the maxTotalCount method.
   *
   * @covers ::maxTotalCount
   */
  public function testMaxTotalCount() {
    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(10);

    $select = $this->createMock('\Drupal\Core\Database\Query\Select');
    $select->expects($this->once())
      ->method('addExpression');

    $select->expects($this->once())
      ->method('condition')
      ->with('entity_type', 'node')
      ->willReturnSelf();

    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);

    $count = $this->counter->maxTotalCount('node');
    $this->assertEquals(10, $count);
  }

}
