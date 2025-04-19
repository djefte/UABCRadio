<?php

namespace Drupal\visitors;

use Drupal\Component\Render\MarkupInterface;

/**
 * Visitors Language Interface.
 */
interface VisitorsLanguageInterface {

  /**
   * Get the language label.
   *
   * @param string $language_code
   *   The language code.
   */
  public function getLanguageLabel($language_code): MarkupInterface;

}
