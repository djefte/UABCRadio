<?php

namespace Drupal\visitors;

/**
 * Value object for passing statistic results.
 */
class StatisticsViewsResult {

  /**
   * Total number of times the entity has been viewed.
   *
   * @var int
   */
  protected $total;

  /**
   * Total number of times the entity has been viewed "today".
   *
   * @var int
   */
  protected $today;

  /**
   * Timestamp of when the entity was last viewed.
   *
   * @var int
   */
  protected $timestamp;

  /**
   * StatisticsViewsResult constructor.
   *
   * @param int $total_count
   *   Total number of times the entity has been viewed.
   * @param int $day_count
   *   Total number of times the entity has been viewed "today".
   * @param int $timestamp
   *   Timestamp of when the entity was last viewed.
   */
  public function __construct($total_count, $day_count, $timestamp) {
    $this->total = (int) $total_count;
    $this->today = (int) $day_count;
    $this->timestamp = (int) $timestamp;
  }

  /**
   * Total number of times the entity has been viewed.
   *
   * @return int
   *   The total number of times the entity has been viewed.
   */
  public function getTotalCount() {
    return $this->total;
  }

  /**
   * Total number of times the entity has been viewed "today".
   *
   * @return int
   *   The total number of times the entity has been viewed "today".
   */
  public function getDayCount() {
    return $this->today;
  }

  /**
   * Timestamp of when the entity was last viewed.
   *
   * @return int
   *   The timestamp of when the entity was last viewed.
   */
  public function getTimestamp() {
    return $this->timestamp;
  }

}
