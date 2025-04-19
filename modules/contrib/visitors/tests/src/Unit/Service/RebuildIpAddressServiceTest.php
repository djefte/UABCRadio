<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\RebuildIpAddressService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the RebuildIpAddressService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\RebuildIpAddressService
 *
 * @group visitors
 */
class RebuildIpAddressServiceTest extends UnitTestCase {

  /**
   * The report service.
   *
   * @var \Drupal\visitors\Service\RebuildIpAddressService
   */
  protected $service;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

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

    $this->logger = $this->createMock('\Psr\Log\LoggerInterface');
    $container->set('logger.factory', $this->logger);

    \Drupal::setContainer($container);

    $this->service = new RebuildIpAddressService(
      $this->database,
      $this->logger,
    );
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $service = new RebuildIpAddressService(
      $this->database,
      $this->logger,
    );
    $this->assertInstanceOf(RebuildIpAddressService::class, $service);
  }

  /**
   * Tests the getIpAddresses method.
   *
   * @covers ::getIpAddresses
   */
  public function testGetIpAddresses(): void {
    $statement = $this->createMock('\Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn([]);
    $select = $this->createMock('\Drupal\Core\Database\Query\SelectInterface');
    $this->database->expects($this->once())
      ->method('select')
      ->with('visitors', 'v')
      ->willReturn($select);
    $select->expects($this->once())
      ->method('fields')
      ->with('v', ['visitors_ip'])
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('distinct')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('orderBy')
      ->with('visitors_ip', 'ASC')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->service->getIpAddresses();
  }

  /**
   * Tests the rebuild method.
   *
   * @covers ::rebuild
   */
  public function testRebuildValid(): void {

    $count = $this->service->rebuild('127.0.0.1');

    $this->assertEquals(0, $count);
  }

  /**
   * Tests the rebuild method.
   *
   * @covers ::rebuild
   */
  public function testRebuildPackedAddress(): void {

    $address = inet_pton('127.0.0.1');

    $update = $this->createMock('\Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with(['visitors_ip' => '127.0.0.1'])
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('condition')
      ->with('visitors_ip', $address)
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('execute')
      ->willReturn(10);

    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors')
      ->willReturn($update);

    $count = $this->service->rebuild($address);

    $this->assertEquals(10, $count);
  }

  /**
   * Tests the rebuild method.
   *
   * @covers ::rebuild
   */
  public function testRebuildLong(): void {

    $address = (string) ip2long('127.0.0.1');

    $update = $this->createMock('\Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with(['visitors_ip' => '127.0.0.1'])
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('condition')
      ->with('visitors_ip', $address)
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('execute')
      ->willReturn(10);

    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors')
      ->willReturn($update);

    $count = $this->service->rebuild($address);

    $this->assertEquals(10, $count);
  }

  /**
   * Tests the rebuild method.
   *
   * @covers ::rebuild
   */
  public function testRebuildException(): void {

    $address = (string) ip2long('127.0.0.1');

    $update = $this->createMock('\Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with(['visitors_ip' => '127.0.0.1'])
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('condition')
      ->with('visitors_ip', $address)
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('execute')
      ->willThrowException(new \Exception('An error occurred'));

    $this->database->expects($this->once())
      ->method('update')
      ->with('visitors')
      ->willReturn($update);

    $count = $this->service->rebuild($address);

    $this->assertEquals(-1, $count);
  }

}
