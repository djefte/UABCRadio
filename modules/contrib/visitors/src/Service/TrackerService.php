<?php

namespace Drupal\visitors\Service;

use Drupal\Core\Database\Connection;
use Drupal\visitors\VisitorsTrackerInterface;

/**
 * Tracker for web analytics.
 */
class TrackerService implements VisitorsTrackerInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Tracks visits and actions.
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
  public function writeLog($fields): int {
    $id = $this->database->insert('visitors')
      ->fields($fields)
      ->execute();

    return $id;
  }

}
