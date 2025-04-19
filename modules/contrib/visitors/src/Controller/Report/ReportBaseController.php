<?php

namespace Drupal\visitors\Controller\Report;

use Drupal\Core\Controller\ControllerBase;

/**
 * Report base controller.
 */
class ReportBaseController extends ControllerBase {

  /**
   * Render the registered blocks as output.
   *
   * @param array $blocks
   *   An array of block items formatted for rendering a view.
   * @param string|null $class
   *   An optional class to add to the wrapper div.
   * @param array|null $args
   *   An optional array of arguments to pass to the view.
   */
  public function renderViews(array $blocks, $class = NULL, $args = NULL) {
    $output = [];
    // Render each block element.
    foreach ($blocks as $block) {
      if (empty($block['#view_id'])) {

        $build = $block;
      }
      else {
        $view_id = $block['#view_id'];
        $display_id = $block['#view_display'];

        if (is_null($args)) {
          // Create a view embed for this content.
          $build = \views_embed_view($view_id, $display_id);
        }
        else {
          if (!is_array($args)) {
            $args = [$args];
          }
          // Create a view embed for this content.
          $build = \views_embed_view($view_id, $display_id, ...$args);
        }

      }
      if (!isset($build['#attributes']) && isset($block['#attributes'])) {
        $build['#attributes'] = $block['#attributes'];
      }

      $output[] = $build;
    }
    $prefix = '<div>';
    if (!empty($class)) {
      $prefix = '<div class="' . $class . '">';
    }
    return [
      '#prefix'   => $prefix,
      'blocks'    => $output,
      '#suffix'   => '</div>',
    ];
  }

}
