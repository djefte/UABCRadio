<?php

declare(strict_types=1);

namespace Drupal\visitors;

/**
 * Visitors Cron Interface.
 */
interface VisitorsCronInterface {

  /**
   * Execute cron.
   */
  public function execute();

}
