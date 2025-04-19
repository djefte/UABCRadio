<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.module';

/**
 * Tests visitors_geoip_views_data_alter.
 *
 * @group visitors
 */
class HookViewsDataAlterTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_geoip_views_data_alter().
   */
  public function testViewsAlter() {
    $data = [];
    visitors_geoip_views_data_alter($data);

    $this->assertArrayHasKey('visitors', $data);
    $this->assertArrayHasKey('location_region', $data['visitors']);
    $this->assertArrayHasKey('location_city', $data['visitors']);

  }

}
