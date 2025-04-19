<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\TrackerService;

/**
 * Tests the TrackerService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\TrackerService
 * @uses \Drupal\visitors\Service\TrackerService
 * @group visitors
 */
class TrackerServiceTest extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The tracker service.
   *
   * @var \Drupal\visitors\Service\TrackerService
   */
  protected $trackerService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->database = $this->createMock('\Drupal\Core\Database\Connection');
    $this->trackerService = new TrackerService($this->database);

  }

  /**
   * Tests the getId method.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $tracker_service = new TrackerService($this->database);
    $this->assertInstanceOf('\Drupal\visitors\Service\TrackerService', $tracker_service);
  }

  /**
   * Tests the writeLog method.
   *
   * @covers ::writeLog
   */
  public function testWriteLog() {
    $fields = [
      'uid' => 1,
      'ip' => '',
    ];
    $insert = $this->createMock('\Drupal\Core\Database\Query\Insert');
    $insert->expects($this->once())
      ->method('fields')
      ->with($fields)
      ->willReturnSelf();
    $insert->expects($this->once())
      ->method('execute')
      ->willReturn(123);
    $this->database->expects($this->once())
      ->method('insert')
      ->with('visitors')
      ->willReturn($insert);

    $id = $this->trackerService->writeLog($fields);

    $this->assertEquals(123, $id);
  }

}
