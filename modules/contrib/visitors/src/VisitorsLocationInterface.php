<?php

namespace Drupal\visitors;

use Drupal\Component\Render\MarkupInterface;

/**
 * Visitors Location Interface.
 */
interface VisitorsLocationInterface {

  /**
   * Get the country label.
   *
   * @param string $country_code
   *   The country code.
   */
  public function getCountryLabel($country_code): MarkupInterface;

  /**
   * Get the continent code.
   *
   * @param string $country_code
   *   The country code.
   */
  public function getContinent($country_code): string;

  /**
   * Get the continent label.
   *
   * @param string $continent_code
   *   The continent code.
   */
  public function getContinentLabel($continent_code): MarkupInterface;

  /**
   * Check if the country code is valid.
   *
   * @param string $country_code
   *   The country code.
   *
   * @return bool
   *   Return true if the country code is valid.
   */
  public function isValidCountryCode($country_code): bool;

}
