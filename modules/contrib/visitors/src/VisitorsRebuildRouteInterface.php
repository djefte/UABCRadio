<?php

namespace Drupal\visitors;

/**
 * Interface for rebuilding routes.
 */
interface VisitorsRebuildRouteInterface {

  /**
   * Rebuilds routes from path.
   *
   * @return int
   *   The number of routes that were rebuilt.
   */
  public function rebuild(string $path): int;

  /**
   * Gets the paths missing a route.
   *
   * @return array
   *   The paths.
   */
  public function getPaths(): array;

}
