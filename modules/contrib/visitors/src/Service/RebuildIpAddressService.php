<?php

namespace Drupal\visitors\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Utility\Error;
use Drupal\visitors\VisitorsRebuildIpAddressInterface;
use Psr\Log\LoggerInterface;

/**
 * Convert legacy IP address to new format.
 */
class RebuildIpAddressService implements VisitorsRebuildIpAddressInterface {

  const ERROR = -1;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new Rebuild Route Service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(Connection $connection, LoggerInterface $logger) {
    $this->database = $connection;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild(string $ip_address): int {
    if (inet_pton($ip_address) !== FALSE) {
      return 0;
    }
    $new_address = NULL;
    if (inet_ntop($ip_address) !== FALSE) {
      $new_address = inet_ntop($ip_address);
    }
    elseif (long2ip((int) $ip_address) !== FALSE) {
      $new_address = long2ip((int) $ip_address);
    }

    $count = self::ERROR;
    try {
      $count = $this->database->update('visitors')
        ->fields(['visitors_ip' => $new_address])
        ->condition('visitors_ip', $ip_address)
        ->execute();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }

    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpAddresses(): array {
    $ip_addresses = $this->database->select('visitors', 'v')
      ->fields('v', [
        'visitors_ip',
      ])
      ->distinct()
      ->orderBy('visitors_ip', 'ASC')
      ->execute()->fetchAll();

    return $ip_addresses;
  }

}
