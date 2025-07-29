<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Kernel;

use Drupal\KernelTests\KernelTestBase;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests the hook_update_8231() function.
 *
 * @group visitors
 */
class HookUpdate8231Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'visitors',
    'user',
  ];

  /**
   * Tests the hook_update_8231() function.
   */
  public function testHookUpdate8231(): void {

    // Set something in the render cache to confirm it's cleared.
    \Drupal::cache('render')->set('test_key', 'test_value');
    $this->assertNotFalse(\Drupal::cache('render')->get('test_key'));

    visitors_update_8231();

    // Check that the render cache has been flushed.
    $this->assertFalse(\Drupal::cache('render')->get('test_key'));
  }

}
