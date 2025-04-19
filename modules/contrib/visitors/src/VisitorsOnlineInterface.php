<?php

declare(strict_types=1);

namespace Drupal\visitors;

/**
 * Provides an interface for the visitors online service.
 */
interface VisitorsOnlineInterface {

  /**
   * Thirty minutes in seconds.
   */
  const MINUTE_30 = 1800;

  /**
   * Twenty-four hours in seconds.
   */
  const HOUR_24 = 86400;

  /**
   * Seven days in seconds.
   */
  const DAY_7 = 604800;

  /**
   * Gets the current visitors online.
   *
   * @return int
   *   The current visitors online.
   */
  public function getLast30Minutes();

  /**
   * Gets the current visitors online.
   *
   * @return int
   *   The current visitors online.
   */
  public function getLast24Hours();

  /**
   * Gets the current visitors online.
   *
   * @return int
   *   The current visitors online.
   */
  public function getYesterday30Minutes();

  /**
   * Gets the current visitors online.
   *
   * @return int
   *   The current visitors online.
   */
  public function getYesterday24Hours();

  /**
   * Gets the current visitors online.
   *
   * @return int
   *   The current visitors online.
   */
  public function getLastWeek30Minutes();

  /**
   * Gets the current visitors online.
   *
   * @return int
   *   The current visitors online.
   */
  public function getLastWeek24Hours();

}
