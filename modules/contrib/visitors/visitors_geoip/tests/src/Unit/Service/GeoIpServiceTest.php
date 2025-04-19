<?php

namespace Drupal\Tests\visitors_geoip\Unit\Service;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors_geoip\Service\GeoIpService;
use GeoIp2\Database\Reader;
use GeoIp2\Model\City;

/**
 * Tests the GeoIpService.
 *
 * @coversDefaultClass \Drupal\visitors_geoip\Service\GeoIpService
 *
 * @group visitors_geoip
 */
class GeoIpServiceTest extends UnitTestCase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The GeoIP reader service.
   *
   * @var \GeoIp2\Database\Reader|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $reader;

  /**
   * The GeoIpService instance being tested.
   *
   * @var \Drupal\visitors_geoip\Service\GeoIpService
   */
  protected $geoIpService;

  /**
   * The settings config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->fileSystem = $this->createMock('\Drupal\Core\File\FileSystemInterface');
    $container->set('file_system', $this->fileSystem);

    $this->settings = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $this->settings->expects($this->any())
      ->method('get')
      ->with('geoip_path')
      ->willReturn('test_path');
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $container->set('config.factory', $this->configFactory);
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('visitors_geoip.settings')
      ->willReturn($this->settings);
    $this->reader = $this->createMock(Reader::class);

    \Drupal::setContainer($container);

    $this->geoIpService = new GeoIpService($this->configFactory, $this->fileSystem);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructorBetterDatabase() {
    $this->fileSystem->expects($this->once())
      ->method('realPath')
      ->with('test_path/GeoIP2-City.mmdb')
      ->willReturn(TRUE);

    $this->expectException(\InvalidArgumentException::class);
    $geoIpService = new GeoIpService($this->configFactory, $this->fileSystem);
    $this->assertInstanceOf(GeoIpService::class, $geoIpService);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructorFreeDatabase() {
    $this->fileSystem->expects($this->exactly(2))
      ->method('realPath')
      ->willReturnMap([
        ['test_path/GeoIP2-City.mmdb', FALSE],
        ['test_path/GeoLite2-City.mmdb', TRUE],
      ]);

    $this->expectException(\InvalidArgumentException::class);
    $geoIpService = new GeoIpService($this->configFactory, $this->fileSystem);
    $this->assertInstanceOf(GeoIpService::class, $geoIpService);
  }

  /**
   * Tests the metadata() method.
   *
   * @covers ::metadata
   */
  public function testMetadata() {
    // Mock the reader service.
    $metadata = $this->createMock('\MaxMind\Db\Reader\Metadata');
    $this->reader->expects($this->once())
      ->method('metadata')
      ->willReturn($metadata);
    $this->geoIpService->setReader($this->reader);
    // Call the metadata() method.
    $result = $this->geoIpService->metadata();

    // Assert the result.
    $this->assertEquals($metadata, $result);
  }

  /**
   * Tests the metadata() method with a NULL reader.
   *
   * @covers ::metadata
   */
  public function testMetadataNull() {
    $this->geoIpService->setReader(NULL);
    // Call the metadata() method.
    $result = $this->geoIpService->metadata();

    // Assert the result.
    $this->assertNull($result, 'Metadata should be NULL when there is no reader');
  }

  /**
   * Tests the city() method.
   *
   * @covers ::city
   * @covers ::setReader
   */
  public function testCity() {
    $ipAddress = '127.0.0.1';

    // Mock the reader service.
    $city = $this->createMock(City::class);
    $this->reader->expects($this->once())
      ->method('city')
      ->with($ipAddress)
      ->willReturn($city);
    $this->geoIpService->setReader($this->reader);
    // Call the city() method.
    $result = $this->geoIpService->city($ipAddress);

    // Assert the result.
    $this->assertSame($city, $result);
  }

  /**
   * Tests the city() method with a NULL reader.
   *
   * @covers ::city
   */
  public function testCityNull() {
    $ipAddress = '127.0.0.1';

    $this->geoIpService->setReader(NULL);
    // Call the city() method.
    $result = $this->geoIpService->city($ipAddress);

    // Assert the result.
    $this->assertNull($result, 'City should be NULL when there is no reader');
  }

  /**
   * Tests the getReader() method.
   *
   * @covers ::getReader
   */
  public function testGetReader() {
    $this->geoIpService->setReader($this->reader);
    $this->assertSame($this->reader, $this->geoIpService->getReader());
  }

  /**
   * Tests the setReader() method.
   *
   * @covers ::setReader
   */
  public function testSetReader() {
    $this->geoIpService->setReader($this->reader);
    $this->assertSame($this->reader, $this->geoIpService->getReader());
  }

  /**
   * Tests the hasLibrary method.
   *
   * @covers ::hasLibrary
   */
  public function testHasLibrary():void {
    $this->assertTrue($this->geoIpService->hasLibrary());
  }

  /**
   * Tests the hasLibrary method with a missing class name.
   *
   * @covers ::hasLibrary
   */
  public function testHasLibraryWithClassName():void {
    $this->assertFalse($this->geoIpService->hasLibrary('Missing\Class'));
  }

  /**
   * Tests the hasExtension method.
   *
   * We can not guarantee that the maxminddb extension is installed on the
   * testing environment, so we will test the method with a fake extension.
   *
   * @covers ::hasExtension
   */
  public function testHasExtension(): void {
    $this->assertFalse($this->geoIpService->hasExtension('fake_extension'));
  }

}
