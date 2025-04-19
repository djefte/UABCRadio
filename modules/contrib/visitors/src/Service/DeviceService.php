<?php

namespace Drupal\visitors\Service;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Drupal\Core\Database\Connection;
use Drupal\visitors\VisitorsDeviceInterface;

/**
 * Detects the device type.
 *
 * @package Drupal\visitors\Service
 */
class DeviceService implements VisitorsDeviceInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * DeviceService constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueUserAgents(): array {
    $results = $this->database->select('visitors', 'v')
      ->fields('v', ['visitors_user_agent'])
      ->condition('bot', NULL, 'IS NULL')
      ->distinct()
      ->orderBy('visitors_user_agent')
      ->execute()
      ->fetchAll(\PDO::FETCH_COLUMN, 0);

    return $results;
  }

  /**
   * Gets the device detector.
   *
   * @param string $user_agent
   *   The user agent string.
   * @param array $server
   *   The server array.
   *
   * @return \DeviceDetector\DeviceDetector
   *   The device detector.
   */
  protected function getDeviceDetector(string $user_agent, ?array $server = NULL): DeviceDetector {
    $client_hints = NULL;
    if ($server) {
      $client_hints = ClientHints::factory($server);
    }

    $dd = new DeviceDetector($user_agent, $client_hints);
    $dd->parse();

    return $dd;
  }

  /**
   * {@inheritdoc}
   */
  public function bulkUpdate(string $user_agent): int {
    $dd = new DeviceDetector($user_agent);
    $dd->parse();
    $fields = [];

    $this->setDeviceFields($fields, $dd);

    $count = $this->database->update('visitors')
      ->fields($fields)
      ->condition('visitors_user_agent', $user_agent)
      ->condition('bot', NULL, 'IS NULL')
      ->execute();

    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function doDeviceFields(array &$fields, string $user_agent, ?array $server = NULL): void {
    $dd = $this->getDeviceDetector($user_agent, $server);
    $this->setDeviceFields($fields, $dd);

  }

  /**
   * Assigns the device fields to the fields array.
   *
   * @param array $fields
   *   The fields array.
   * @param \DeviceDetector\DeviceDetector $dd
   *   The DeviceDetector object.
   */
  protected function setDeviceFields(&$fields, DeviceDetector $dd): void {
    $fields['config_browser_engine'] = $dd->getClient('engine');
    $fields['config_browser_name'] = $dd->getClient('short_name');
    $fields['config_browser_version'] = $dd->getClient('version');
    $fields['config_client_type'] = $dd->getClient('type');
    $fields['config_device_brand'] = $dd->getBrandName();
    $fields['config_device_model'] = $dd->getModel();
    $fields['config_device_type'] = $dd->getDeviceName();
    $fields['config_os'] = $dd->getOs('short_name');
    $fields['config_os_version'] = $dd->getOs('version');
    $fields['bot'] = (int) $dd->isBot();

    $nullable = [
      'config_browser_engine',
      'config_browser_name',
      'config_browser_version',
      'config_client_type',
      'config_os',
      'config_os_version',
    ];
    foreach ($nullable as $field) {
      $value = strtolower($fields[$field]);
      if ($value == 'unk') {
        $fields[$field] = NULL;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasLibrary($class_name = 'DeviceDetector\ClientHints'): bool {
    return class_exists($class_name);
  }

}
