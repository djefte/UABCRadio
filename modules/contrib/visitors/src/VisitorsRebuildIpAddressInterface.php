<?php

namespace Drupal\visitors;

/**
 * Interface for rebuilding Ip Address.
 */
interface VisitorsRebuildIpAddressInterface {

  /**
   * Rebuilds Ip Address formats.
   *
   * The previous IP address only supported IPv4. This method will rebuild the
   * IP address to support IPv6.
   *
   * @return int
   *   The number of rows updated.
   */
  public function rebuild(string $ip_address): int;

  /**
   * Gets distinct IP addresses to be rebuilt.
   *
   * @return array
   *   The IP addresses.
   */
  public function getIpAddresses(): array;

}
