<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

if (!defined('REQUIREMENT_ERROR')) {
  define('REQUIREMENT_ERROR', 'REQUIREMENT_ERROR');
}
if (!defined('REQUIREMENT_WARNING')) {
  define('REQUIREMENT_WARNING', 'REQUIREMENT_WARNING');
}
if (!defined('REQUIREMENT_OK')) {
  define('REQUIREMENT_OK', 'REQUIREMENT_OK');
}

/**
 * Tests visitors_geoip_requirements.
 *
 * @group visitors
 */
class HookRequirementsTest extends UnitTestCase {

  /**
   * The geo location service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $geoLocation;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->geoLocation = $this->createMock('Drupal\visitors_geoip\VisitorsGeoIpInterface');
    $container->set('visitors_geoip.lookup', $this->geoLocation);

    $this->languageManager = $this->createMock('Drupal\Core\Language\LanguageManagerInterface');
    $container->set('language_manager', $this->languageManager);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_geoip_requirements().
   */
  public function testPhaseUpdate() {
    $phase = 'update';
    $requirements = visitors_geoip_requirements($phase);

    $this->assertIsArray($requirements);
    $this->assertEmpty($requirements);
  }

  /**
   * Tests visitors_geoip_requirements().
   */
  public function testPhaseFake() {
    $phase = 'fake';
    $requirements = visitors_geoip_requirements($phase);

    $this->assertIsArray($requirements);
    $this->assertEmpty($requirements);
  }

  /**
   * Tests visitors_geoip_requirements().
   */
  public function testPhaseRuntimeMissingExtension() {
    $language = $this->createMock('Drupal\Core\Language\LanguageInterface');
    $language->expects($this->once())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($language);

    $metadata = (object) [
      'databaseType' => 'Enterprise',
      'buildEpoch' => 1743351627,
    ];
    $this->geoLocation->expects($this->once())
      ->method('metadata')
      ->willReturn($metadata);

    $this->geoLocation->expects($this->once())
      ->method('hasExtension')
      ->willReturn(FALSE);

    $phase = 'runtime';
    $requirements = visitors_geoip_requirements($phase);

    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
    $this->assertArrayHasKey('visitors_geoip', $requirements);
    $this->assertEquals(REQUIREMENT_WARNING, $requirements['visitors_geoip']['severity']);
  }

  /**
   * Tests visitors_geoip_requirements().
   */
  public function testPhaseRuntime() {
    $language = $this->createMock('Drupal\Core\Language\LanguageInterface');
    $language->expects($this->once())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($language);

    $metadata = (object) [
      'databaseType' => 'Enterprise',
      'buildEpoch' => 1743351627,
    ];
    $this->geoLocation->expects($this->once())
      ->method('metadata')
      ->willReturn($metadata);

    $this->geoLocation->expects($this->once())
      ->method('hasExtension')
      ->willReturn(TRUE);

    $phase = 'runtime';
    $requirements = visitors_geoip_requirements($phase);

    $this->assertIsArray($requirements);
    $this->assertCount(1, $requirements);
    $this->assertArrayHasKey('visitors_geoip', $requirements);
    $this->assertEquals(REQUIREMENT_OK, $requirements['visitors_geoip']['severity']);
  }

}
