<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\CronService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the CronService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\CronService
 *
 * @group visitors
 */
class CronServiceTest extends UnitTestCase {

  const ONE_DAY = 86400;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $state;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $time;

  /**
   * The counter.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $counter;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The service.
   *
   * @var \Drupal\visitors\Service\CronService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $this->time = $this->createMock('Drupal\Component\Datetime\TimeInterface');
    $container->set('datetime.time', $this->time);

    $this->counter = $this->createMock('Drupal\visitors\VisitorsCounterInterface');
    $container->set('visitors.counter', $this->counter);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $this->service = new CronService($this->configFactory, $this->database, $this->state, $this->time, $this->counter);

  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $service = new CronService($this->configFactory, $this->database, $this->state, $this->time, $this->counter);
    $this->assertInstanceOf(CronService::class, $service);
  }

  /**
   * Test deleteExpiredLogs() method.
   *
   * @covers ::deleteExpiredLogs
   */
  public function testDeleteExpiredLogsRetainAll(): void {
    $this->settings->expects($this->once())
      ->method('get')
      ->with('flush_log_timer')
      ->willReturn(0);

    // Make deleteExpiredLogs() public for testing.
    $method = new \ReflectionMethod($this->service, 'deleteExpiredLogs');
    $method->setAccessible(TRUE);
    $method->invoke($this->service);
  }

  /**
   * Tests the execute method.
   *
   * @covers ::execute
   */
  public function testExecute(): void {
    $this->settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['flush_log_timer', self::ONE_DAY],
        ['bot_retention_log', self::ONE_DAY],
      ]);

    $this->time->expects($this->exactly(2))
      ->method('getRequestTime')
      ->willReturn(1000);

    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $delete->expects($this->exactly(2))
      ->method('execute');

    $this->database->expects($this->exactly(2))
      ->method('delete')
      ->willReturnMap([
        ['visitors', [], $delete],
      ]);

    $this->counter->expects($this->once())
      ->method('resetDayCount');

    $this->counter->expects($this->once())
      ->method('maxTotalCount')
      ->with('node')
      ->willReturn(100);

    $this->state->expects($this->once())
      ->method('set')
      ->with('visitors.node_counter_scale', 0.01);

    $this->service->execute();
  }

  /**
   * Test deleteExpiredLogs() method.
   *
   * @covers ::deleteExpiredLogs
   */
  public function testDeleteExpiredLogs(): void {
    $this->settings->expects($this->once())
      ->method('get')
      ->with('flush_log_timer')
      ->willReturn(self::ONE_DAY);

    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(1000);

    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->once())
      ->method('condition')
      ->with('visitors_date_time', 1000 - self::ONE_DAY, '<')
      ->willReturnSelf();
    $delete->expects($this->once())
      ->method('execute');

    $this->database->expects($this->once())
      ->method('delete')
      ->willReturnMap([
        ['visitors', [], $delete],
      ]);

    // Make deleteExpiredLogs() public for testing.
    $method = new \ReflectionMethod($this->service, 'deleteExpiredLogs');
    $method->setAccessible(TRUE);
    $method->invoke($this->service);
  }

  /**
   * Test deleteBotLogs() method.
   *
   * @covers ::deleteBotLogs
   */
  public function testDeleteBotLogsRetainAll(): void {
    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(0);

    // Make deleteBotLogs() public for testing.
    $method = new \ReflectionMethod($this->service, 'deleteBotLogs');
    $method->setAccessible(TRUE);
    $method->invoke($this->service);
  }

  /**
   * Test deleteBotLogs() method.
   *
   * @covers ::deleteBotLogs
   */
  public function testDeleteBotLogs(): void {
    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(1);

    $this->time->expects($this->once())
      ->method('getRequestTime')
      ->willReturn(1000);

    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->exactly(2))
      ->method('condition')
      ->willReturnMap([
        ['bot', 1, '=', $delete],
        ['visitors_date_time', '0', '<', $delete],
      ]);
    $delete->expects($this->once())
      ->method('execute');

    $this->database->expects($this->once())
      ->method('delete')
      ->willReturnMap([
        ['visitors', [], $delete],
      ]);

    // Make deleteBotLogs() public for testing.
    $method = new \ReflectionMethod($this->service, 'deleteBotLogs');
    $method->setAccessible(TRUE);
    $method->invoke($this->service);
  }

  /**
   * Tests reset entity day counter.
   *
   * @covers ::dayCounter
   */
  public function testDayCounter(): void {
    $this->counter->expects($this->once())
      ->method('resetDayCount');

    $this->counter->expects($this->once())
      ->method('maxTotalCount')
      ->with('node')
      ->willReturn(100);

    $this->state->expects($this->once())
      ->method('set')
      ->with('visitors.node_counter_scale', 0.01);

    // Make dayCounter() public for testing.
    $method = new \ReflectionMethod($this->service, 'dayCounter');
    $method->setAccessible(TRUE);
    $method->invoke($this->service);
  }

}
