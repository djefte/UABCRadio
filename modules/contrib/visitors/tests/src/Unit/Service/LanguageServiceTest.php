<?php

namespace Drupal\Tests\visitors\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Service\LanguageService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the CookieService class.
 *
 * @coversDefaultClass \Drupal\visitors\Service\LanguageService
 * @uses \Drupal\visitors\Service\LanguageService
 * @group visitors
 */
class LanguageServiceTest extends UnitTestCase {


  /**
   * The language service.
   *
   * @var \Drupal\visitors\Service\LanguageService
   */
  protected $language;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();

    \Drupal::setContainer($container);

    $this->language = new LanguageService($string_translation);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $language = new LanguageService($this->getStringTranslationStub());
    $this->assertInstanceOf(LanguageService::class, $language);
  }

  /**
   * Tests the getLanguageLabel method.
   *
   * @covers ::getLanguageLabel
   */
  public function testGetLanguageLabel() {
    $this->assertSame('Yoruba', (string) $this->language->getLanguageLabel('yo'));
    $this->assertSame('English', (string) $this->language->getLanguageLabel('en'));
    $this->assertSame('Croatian', (string) $this->language->getLanguageLabel('hr'));
    $this->assertSame('Unknown', (string) $this->language->getLanguageLabel('zz'));
  }

}
