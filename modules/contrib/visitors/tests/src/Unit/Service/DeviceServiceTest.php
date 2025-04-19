<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\DeviceService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the CookieService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\DeviceService
 * @uses \Drupal\visitors\Service\DeviceService
 * @group visitors
 */
class DeviceServiceTest extends UnitTestCase {


  /**
   * The device service.
   *
   * @var \Drupal\visitors\Service\DeviceService|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $device;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->database = $this->createMock('Drupal\Core\Database\Connection');

    \Drupal::setContainer($container);

    $this->device = new DeviceService($this->database);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct():void {
    $device = new DeviceService($this->database);
    $this->assertInstanceOf(DeviceService::class, $device);
  }

  /**
   * Tests the getUniqueUserAgents method.
   *
   * @covers ::getUniqueUserAgents
   */
  public function testGetLanguageLabel():void {
    $result = [
      'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/106.0.5249.103 Safari/537.36',
    ];

    $statement = $this->createMock('Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn($result);

    $select = $this->createMock('Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('fields')
      ->with('v', ['visitors_user_agent'])
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('bot', NULL, 'IS NULL')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('distinct')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('orderBy')
      ->with('visitors_user_agent')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);

    $this->assertSame($result, $this->device->getUniqueUserAgents());

  }

  /**
   * Tests the getDeviceDetector method.
   *
   * @covers ::getDeviceDetector
   * @covers ::doDeviceFields
   * @covers ::setDeviceFields
   */
  public function testGetDeviceDetector():void {
    $fields = [];
    $server = [
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/106.0.5249.103 Safari/537.36',
    ];
    $user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/106.0.5249.103 Safari/537.36';
    $this->device->doDeviceFields($fields, $user_agent, $server);
    $this->assertCount(10, $fields);
  }

  /**
   * Tests the bulkUpdate method.
   *
   * @covers ::bulkUpdate
   */
  public function testBulkUpdate():void {
    $user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/106.0.5249.103 Safari/537.36';

    $update = $this->createMock('Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with([
        'config_os' => 'LIN',
        'config_browser_engine' => 'Blink',
        'config_browser_name' => 'HC',
        'config_browser_version' => '106.0',
        'config_client_type' => 'browser',
        'config_device_brand' => '',
        'config_device_model' => '',
        'config_os_version' => '',
        'bot' => 0,
        'config_device_type' => 'desktop',
      ])
      ->willReturnSelf();
    $update->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('execute')
      ->willReturn(15);

    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors')
      ->willReturn($update);

    $count = $this->device->bulkUpdate($user_agent);
    $this->assertSame(15, $count);
  }

  /**
   * Tests the hasLibrary method.
   *
   * @covers ::hasLibrary
   */
  public function testHasLibrary():void {
    $this->assertTrue($this->device->hasLibrary());
  }

  /**
   * Tests the hasLibrary method with a missing class name.
   *
   * @covers ::hasLibrary
   */
  public function testHasLibraryWithClassName():void {
    $this->assertFalse($this->device->hasLibrary('Missing\Class'));
  }

  /**
   * Tests setDeviceFields method.
   *
   * @covers ::setDeviceFields
   */
  public function testSetDeviceFields(): void {
    $dd = $this->createMock('DeviceDetector\DeviceDetector');
    $dd->expects($this->exactly(2))
      ->method('getOs')
      ->willReturnMap([
        ['short_name', 'unk'],
        ['version', 'unk'],
      ]);

    $dd->expects($this->exactly(4))
      ->method('getClient')
      ->willReturnMap([
        ['engine', 'Blink'],
        ['short_name', 'HC'],
        ['version', '106.0'],
        ['type', 'browser'],
      ]);

    $fields = [];
    // Make setDeviceFields method public.
    $method = new \ReflectionMethod($this->device, 'setDeviceFields');
    $method->setAccessible(TRUE);
    $method->invokeArgs($this->device, [&$fields, $dd]);

    $this->assertNull($fields['config_os']);
    $this->assertNull($fields['config_os_version']);

  }

}
