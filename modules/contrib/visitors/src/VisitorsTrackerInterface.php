<?php

namespace Drupal\visitors;

/**
 * Interface for tracker visitors.
 */
interface VisitorsTrackerInterface {

  /**
   * Writes the log.
   *
   * @param string[] $fields
   *   The fields array.
   *
   * @return int
   *   The id of row created.
   */
  public function writeLog($fields): int;

}
