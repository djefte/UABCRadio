<?php

namespace Drupal\visitors_geoip;

use GeoIp2\Database\Reader;

/**
 * Visitors Geo Location Interface.
 *
 * @package visitors_geoip
 */
interface VisitorsGeoIpInterface {

  /**
   * Get the GeoIP metadata.
   *
   * @return \MaxMind\Db\Reader\Metadata|null
   *   The metadata.
   */
  public function metadata();

  /**
   * Get the GeoIP country.
   *
   * @param string $ip_address
   *   The IP address.
   *
   * @return array|null
   *   The country.
   */
  public function city($ip_address);

  /**
   * Get the GeoIP reader.
   *
   * @return \GeoIp2\Database\Reader
   *   The reader.
   */
  public function getReader();

  /**
   * Set the GeoIP reader.
   *
   * @param \GeoIp2\Database\Reader $reader
   *   The reader.
   */
  public function setReader(Reader $reader);

  /**
   * Ensures the library is present.
   *
   * @param string $class_name
   *   The class name of the library.
   *
   * @return bool
   *   The device type.
   */
  public function hasLibrary($class_name = 'GeoIp2\Database\Reader'): bool;

  /**
   * Check if the extension is present.
   *
   * @param string $extension
   *   The extension.
   *
   * @return bool
   *   The device type.
   */
  public function hasExtension($extension = 'maxminddb'): bool;

}
