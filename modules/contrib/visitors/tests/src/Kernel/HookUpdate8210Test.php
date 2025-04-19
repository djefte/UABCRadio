<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests the hook_update_8210() function.
 *
 * @group visitors
 */
class HookUpdate8210Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'visitors',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->legacyConfig();
  }

  /**
   * Tests the hook_update_8223() function.
   */
  public function testHookUpdate8223(): void {
    $config = $this->config('visitors.config');
    $this->assertEquals(1, $config->get('exclude_administer_users'));
    $this->assertNull($config->get('exclude_user1'));
    $this->assertNull($config->get('excluded_roles'));

    visitors_update_8210();

    $config = $this->config('visitors.config');

    $this->assertNull($config->get('exclude_administer_users'));
    $this->assertEquals(['administer' => 'administer'], $config->get('excluded_roles'));
    $this->assertFalse($config->get('exclude_user1'));
  }

  /**
   * Set the legacy configuration.
   */
  protected function legacyConfig() {
    $yaml = <<<YAML
chart_height: 430
chart_width: 700
exclude_administer_users: 1
flush_log_timer: 0
items_per_page: 10
show_last_registered_user: 1
show_published_nodes: 1
show_registered_users_count: 1
show_since_date: 1
show_total_visitors: 1
show_unique_visitor: 1
show_user_ip: 1
start_count_total_visitors: 0
theme: admin
YAML;
    $view_array = Yaml::parse($yaml);
    $view = $this->config('visitors.config');
    $view->setData($view_array);
    $view->save();
  }

}
