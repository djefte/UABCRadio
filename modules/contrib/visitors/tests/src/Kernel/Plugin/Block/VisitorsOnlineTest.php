<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Kernel\Plugin\Block;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the Visitors Online block.
 *
 * @group visitors
 */
class VisitorsOnlineTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'user',
    'visitors',
  ];

  /**
   * Tests the settings schema.
   */
  public function testSchema(): void {
    $yaml = <<<YAML
uuid: 7e86dce7-f7a7-49fa-8318-b1ceffee427a
langcode: en
status: true
dependencies:
  module:
    - visitors
id: olivero_visitors_online
theme: olivero
region: content
weight: 0
provider: null
plugin: visitors_online
settings:
  id: visitors_online
  label: 'Visitors Online'
  label_display: visible
  provider: visitors
  now_30_minute: true
  now_24_hour: true
  yesterday_30_minute: true
  yesterday_24_hour: true
  last_week_30_minute: true
  last_week_24_hour: true
visibility: {  }
YAML;

    $block_array = Yaml::parse($yaml);
    $block = $this->config('block.block.olivero_visitors_online');
    $block->setData($block_array);
    $block->save();

    $this->assertTrue(TRUE);
  }

}
