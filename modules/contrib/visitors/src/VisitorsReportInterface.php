<?php

namespace Drupal\visitors;

/**
 * Visitors report data.
 */
interface VisitorsReportInterface {

  /**
   * The number of hits for each day of the month.
   */
  const REFERER_TYPE_INTERNAL_PAGES = 0;

  /**
   * The number of hits for each day of the month.
   */
  const REFERER_TYPE_EXTERNAL_PAGES = 1;

  /**
   * Get the number of hits for each referer.
   *
   * @param array $header
   *   Table header configuration.
   *
   * @return array
   *   The number of hits for each referer.
   */
  public function referer(array $header);

  /**
   * Details about the visit.
   *
   * @param int $hit_id
   *   The hit id.
   *
   * @return array
   *   The details about the visit.
   */
  public function hitDetails($hit_id);

}
