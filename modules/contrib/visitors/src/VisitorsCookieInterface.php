<?php

namespace Drupal\visitors;

/**
 * Interface for tacking cookies.
 *
 * @package Drupal\visitors
 */
interface VisitorsCookieInterface {

  /**
   * Returns the visitor id from the cookie.
   *
   * @return string|null
   *   The visitor id.
   */
  public function getId(): ?string;

}
