<?php

declare(strict_types=1);

namespace Drupal\visitors\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\visitors\VisitorsOnlineInterface;

/**
 * Provides a service for the visitors online.
 *
 * @package Drupal\visitors
 */
class OnlineService implements VisitorsOnlineInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a OnlineService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(Connection $database, TimeInterface $time) {
    $this->database = $database;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function getLast30Minutes() {
    $end = $this->time->getRequestTime();
    $start = $end - self::MINUTE_30;

    return $this->query($start, $end);
  }

  /**
   * {@inheritdoc}
   */
  public function getLast24Hours() {
    $end = $this->time->getRequestTime();
    $start = $end - self::HOUR_24;

    return $this->query($start, $end);
  }

  /**
   * {@inheritdoc}
   */
  public function getYesterday30Minutes() {
    $now = $this->time->getRequestTime();
    $end = $now - self::HOUR_24;
    $start = $end - self::MINUTE_30;

    return $this->query($start, $end);
  }

  /**
   * {@inheritdoc}
   */
  public function getYesterday24Hours() {
    $now = $this->time->getRequestTime();
    $end = $now - self::HOUR_24;
    $start = $end - self::HOUR_24;

    return $this->query($start, $end);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastWeek30Minutes() {
    $now = $this->time->getRequestTime();
    $end = $now - self::DAY_7;
    $start = $end - self::MINUTE_30;

    return $this->query($start, $end);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastWeek24Hours() {
    $now = $this->time->getRequestTime();
    $end = $now - self::DAY_7;
    $start = $end - self::HOUR_24;

    return $this->query($start, $end);
  }

  /**
   * Gets the current visitors online.
   *
   * @param int $start
   *   The start time.
   * @param int $end
   *   The end time.
   *
   * @return int
   *   The current visitors online.
   */
  protected function query(int $start, int $end): int {
    $query = $this->database->select('visitors', 'v');
    $query->addExpression('COUNT(DISTINCT v.visitor_id)', 'count');
    $query->condition('v.visitors_date_time', [$start, $end], 'BETWEEN');

    $count = $query->execute()->fetchField();

    return (int) $count;
  }

}
