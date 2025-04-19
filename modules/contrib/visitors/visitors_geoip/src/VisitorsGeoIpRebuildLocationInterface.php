<?php

namespace Drupal\visitors_geoip;

/**
 * Rebuild the location from the IP address.
 *
 * @package Drupal\visitors_geoip
 */
interface VisitorsGeoIpRebuildLocationInterface {

  /**
   * Rebuild the location.
   */
  public function rebuild(string $ip_address);

  /**
   * Get the locations that need to be rebuilt.
   *
   * @return array
   *   The locations that need to be rebuilt.
   */
  public function getLocations();

}
