<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../../visitors.install';

/**
 * Tests the hook_update_8223() function.
 *
 * @group visitors
 */
class HookUpdate8223Test extends KernelTestBase {

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
    $this->assertIsArray($config->get('status_codes_disabled'));
    $this->assertEquals('', $config->get('codesnippet.before'));
    $this->assertFalse($config->get('privacy.disablecookies'));

    visitors_update_8223();

    $config = $this->config('visitors.config');
    $this->assertNull($config->get('status_codes_disabled'));
    $this->assertNull($config->get('codesnippet'));
    $this->assertNull($config->get('privacy'));
  }

  /**
   * Set the legacy configuration.
   */
  protected function legacyConfig() {
    $yaml = <<<YAML
flush_log_timer: 0
bot_retention_log: 0
items_per_page: 10
theme: admin
disable_tracking: false
status_codes_disabled: {}
domain_mode: 0
track:
  userid: true
counter:
  enabled: true
  entity_types:
    - 'node'
  display_max_age: 3600
privacy:
  disablecookies: false
visibility:
  request_path_mode: 0
  request_path_pages: ''
  user_role_mode: 0
  user_role_roles: {}
  user_account_mode: 1
  exclude_user1: false
codesnippet:
  before: ''
  after: ''
script_type: minified
YAML;
    $view_array = Yaml::parse($yaml);
    $view = $this->config('visitors.config');
    $view->setData($view_array);
    $view->save();
  }

}
