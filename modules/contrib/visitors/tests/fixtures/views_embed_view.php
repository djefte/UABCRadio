<?php

/**
 * @file
 * Fixture to override global function.
 */

/**
 * Overrides global function if not exists.
 *
 * @return string
 *   Base path mocked.
 */
function views_embed_view() {
  if (!function_exists('views_embed_view')) {
    return '/';
  }
}
