<?php

namespace Drupal\visitors_geoip\Service;

use Drupal\Core\Database\Connection;
use Drupal\visitors_geoip\VisitorsGeoIpInterface;
use Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface;

/**
 * Rebuild the location from the IP address.
 */
class RebuildLocationService implements VisitorsGeoIpRebuildLocationInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The GeoIP reader.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpInterface
   */
  protected $geoip;

  /**
   * Constructs a new Rebuild Location Service.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\visitors_geoip\VisitorsGeoIpInterface $geoip
   *   The GeoIP reader.
   */
  public function __construct(Connection $database, VisitorsGeoIpInterface $geoip) {
    $this->database = $database;
    $this->geoip = $geoip;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild(string $ip_address) {
    /** @var \GeoIp2\Model\City|null $location */
    $location = $this->geoip->city($ip_address);
    if (!$location) {
      return NULL;
    }

    $geoip_data                   = [];
    $geoip_data['continent_code'] = $location->continent->code;
    $geoip_data['country_code']   = $location->country->isoCode;
    $geoip_data['region']         = $location->subdivisions[0]->isoCode;
    $geoip_data['city']           = $location->city->names['en'];
    $geoip_data['latitude']       = $location->location->latitude;
    $geoip_data['longitude']      = $location->location->longitude;

    $fields['location_continent'] = $geoip_data['continent_code'];
    $fields['location_country']   = $geoip_data['country_code'];
    $fields['location_region']    = $geoip_data['region'];
    $fields['location_city']      = $geoip_data['city'];
    $fields['location_latitude']  = $geoip_data['latitude'];
    $fields['location_longitude'] = $geoip_data['longitude'];

    $this->database->update('visitors')
      ->fields($fields)
      ->condition('visitors_ip', $ip_address)
      ->condition('location_latitude', NULL, 'IS NULL')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getLocations() {

    $missing_location = $this->database->select('visitors', 'v')
      ->fields('v', ['visitors_ip'])
      ->condition('location_latitude', NULL, 'IS NULL')
      ->distinct()
      ->execute()
      ->fetchAll(\PDO::FETCH_COLUMN, 0);

    return $missing_location;
  }

}
