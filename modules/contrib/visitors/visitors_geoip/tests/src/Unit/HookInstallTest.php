<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors_geoip\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests visitors_geoip_install.
 *
 * @group visitors
 */
class HookInstallTest extends UnitTestCase {

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
   * Tests visitors_geoip_install().
   */
  public function testInstall() {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $schema->expects($this->exactly(4))
      ->method('fieldExists')
      ->willReturn(FALSE);

    $numeric = [
      'type' => 'numeric',
      'precision' => 13,
      'scale' => 10,
      'default' => NULL,
    ];
    $varchar = [
      'type' => 'varchar',
      'length' => 128,
      'not null' => TRUE,
      'default' => '',
    ];
    $schema->expects($this->exactly(4))
      ->method('addField')
      ->willReturnMap([
        ['visitors', 'location_region', $varchar],
        ['visitors', 'location_city', $varchar],
        ['visitors', 'location_latitude', $numeric],
        ['visitors', 'location_longitude', $numeric],
      ]);

    visitors_geoip_install();

  }

}
