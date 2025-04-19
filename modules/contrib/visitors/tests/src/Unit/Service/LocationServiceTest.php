<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\LocationService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the CookieService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\LocationService
 * @uses \Drupal\visitors\Service\LocationService
 * @group visitors
 */
class LocationServiceTest extends UnitTestCase {


  /**
   * The location service.
   *
   * @var \Drupal\visitors\Service\LocationService
   */
  protected $location;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();

    \Drupal::setContainer($container);

    $this->location = new LocationService($string_translation);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $counter = new LocationService($this->getStringTranslationStub());
    $this->assertInstanceOf(LocationService::class, $counter);
  }

  /**
   * Tests the getCountryLabel method.
   *
   * @covers ::getCountryLabel
   */
  public function testGetCountryLabel() {
    $this->assertSame('United States', (string) $this->location->getCountryLabel('US'));
    $this->assertSame('United States', (string) $this->location->getCountryLabel('us'));
    $this->assertSame('United Kingdom', (string) $this->location->getCountryLabel('GB'));
    $this->assertSame('Unknown', (string) $this->location->getCountryLabel('ZZ'));
  }

  /**
   * Tests the getContinent method.
   *
   * @covers ::getContinent
   */
  public function testGetContinent() {
    $this->assertSame('NA', $this->location->getContinent('US'));
    $this->assertSame('NA', $this->location->getContinent('us'));
    $this->assertSame('EU', $this->location->getContinent('GB'));
    $this->assertSame('', $this->location->getContinent('ZZ'));
  }

  /**
   * Tests the getContinentLabel method.
   *
   * @covers ::getContinentLabel
   */
  public function testGetContinentLabel() {
    $this->assertSame('North America', (string) $this->location->getContinentLabel('NA'));
    $this->assertSame('Europe', (string) $this->location->getContinentLabel('EU'));
    $this->assertSame('Unknown', (string) $this->location->getContinentLabel('ZZ'));
  }

  /**
   * Tests the isValidCountryCode method.
   *
   * @covers ::isValidCountryCode
   */
  public function testIsValidCountryCode() {
    $this->assertTrue($this->location->isValidCountryCode('US'));
    $this->assertTrue($this->location->isValidCountryCode('us'));
    $this->assertTrue($this->location->isValidCountryCode('GB'));
    $this->assertFalse($this->location->isValidCountryCode('ZZ'));
    $this->assertFalse($this->location->isValidCountryCode('419'));
  }

}
