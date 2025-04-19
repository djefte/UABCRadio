<?php

namespace Drupal\visitors;

/**
 * The Date Range interface.
 */
interface VisitorsDateRangeInterface {

  /**
   * One day in seconds.
   */
  const ONE_DAY = 86400;

  /**
   * Get the period. day, week, month, year, range.
   *
   * @return string
   *   The period. day is the default.
   */
  public function getPeriod();

  /**
   * Get the start timestamp.
   *
   * @return int
   *   The start timestamp. Default is yesterday.
   */
  public function getStartTimestamp();

  /**
   * Get the start date. Y-m-d.
   *
   * @return string
   *   The start date. Default is yesterday.
   */
  public function getStartDate();

  /**
   * Get the end timestamp.
   *
   * @return int
   *   The end timestamp. Default is today.
   */
  public function getEndTimestamp();

  /**
   * Get the end date. Y-m-d.
   *
   * @return string
   *   The end date. Default is today.
   */
  public function getEndDate();

  /**
   * A human readable summary of the date range.
   *
   * @return string
   *   The summary.
   */
  public function getSummary();

  /**
   * Set the period and dates.
   *
   * @param string $period
   *   The period.
   * @param string $start_date
   *   The start date.
   * @param string $end_date
   *   The end date.
   */
  public function setPeriodAndDates($period, $start_date, $end_date);

}
