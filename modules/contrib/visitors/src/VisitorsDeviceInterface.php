<?php

namespace Drupal\visitors;

/**
 * Detects the device type.
 *
 * @package Drupal\visitors
 */
interface VisitorsDeviceInterface {

  /**
   * Bulk updates missing device information.
   *
   * @param string $user_agent
   *   The user agent string.
   *
   * @return int
   *   The number of records updated.
   */
  public function bulkUpdate(string $user_agent): int;

  /**
   * Gets the unique user agents.
   *
   * @return array
   *   The unique user agents.
   */
  public function getUniqueUserAgents(): array;

  /**
   * Sets the device fields.
   *
   * @param array $fields
   *   The fields array.
   * @param string $user_agent
   *   The user agent string.
   * @param array|null $server
   *   The server array.
   */
  public function doDeviceFields(array &$fields, string $user_agent, ?array $server = NULL): void;

  /**
   * Gets the device type.
   *
   * @param string $class_name
   *   The class name of the library.
   *
   * @return bool
   *   The device type.
   */
  public function hasLibrary($class_name = 'DeviceDetector\ClientHints'): bool;

}
