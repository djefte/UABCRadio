<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests visitors_geoip_uninstall.
 *
 * @group visitors
 */
class HookUninstallTest extends UnitTestCase {

  /**
   * The database service.
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

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_geoip_uninstall().
   */
  public function testInstall() {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $schema->expects($this->exactly(4))
      ->method('dropField')
      ->willReturnMap([
        ['visitors', 'location_region'],
        ['visitors', 'location_city'],
        ['visitors', 'location_latitude'],
        ['visitors', 'location_longitude'],
      ]);

    visitors_geoip_uninstall();

  }

}
