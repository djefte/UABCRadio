<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Kernel\Plugin\Block;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the Popular Content block.
 *
 * @group visitors
 */
class PopularContentTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'user',
    'visitors',
  ];

  /**
   * Tests the Blocks settings schema.
   */
  public function testSchema(): void {
    $yaml = <<<YAML
uuid: 5098c591-ba03-4ee1-99ff-28a4e151a5ca
langcode: en
status: true
dependencies:
  module:
    - visitors
id: olivero_popular_content
theme: olivero
region: content
weight: 0
provider: null
plugin: visitors_popular_block
settings:
  id: visitors_popular_block
  label: 'Popular content'
  label_display: visible
  provider: visitors
  top_day_num: '0'
  top_all_num: '0'
  top_last_num: '0'
  entity_type: 'node'
visibility: {  }
YAML;

    $block_array = Yaml::parse($yaml);
    $block = $this->config('block.block.olivero_popular_content');
    $block->setData($block_array);
    $result = $block->save();

    $this->assertTrue(TRUE);
  }

}
