<?php

namespace Drupal\visitors_geoip\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\visitors_geoip\VisitorsGeoIpInterface;
use GeoIp2\Database\Reader;

/**
 * GeoIp lookup Service.
 *
 * @package visitors
 */
class GeoIpService implements VisitorsGeoIpInterface {

  /**
   * The GeoIP reader.
   *
   * @var \GeoIp2\Database\Reader|null
   */
  protected $reader;

  /**
   * Constructs a new GeoIpService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system) {
    $settings = $config_factory->get('visitors_geoip.settings');
    $path = $settings->get('geoip_path');
    $free_database = $path . '/GeoLite2-City.mmdb';
    $better_database = $path . '/GeoIP2-City.mmdb';
    $database = NULL;
    if ($file_system->realPath($better_database)) {
      $database = $better_database;
    }
    elseif ($file_system->realPath($free_database)) {
      $database = $free_database;
    }

    if ($database) {
      $this->reader = new Reader($database);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function metadata() {
    if (is_null($this->reader)) {
      return NULL;
    }
    $metadata = $this->reader->metadata();
    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function city($ip_address) {
    if (is_null($this->reader)) {
      return NULL;
    }
    $record = $this->reader->city($ip_address);
    return $record;
  }

  /**
   * {@inheritdoc}
   */
  public function getReader() {
    return $this->reader;
  }

  /**
   * {@inheritdoc}
   */
  public function setReader($reader) {
    $this->reader = $reader;
  }

  /**
   * {@inheritdoc}
   */
  public function hasLibrary($class_name = 'GeoIp2\Database\Reader'): bool {
    return class_exists($class_name);
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtension($extension = 'maxminddb'): bool {
    return extension_loaded($extension);
  }

}
