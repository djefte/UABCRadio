<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the windows media plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_windowsmedia")
 */
final class VisitorsWindowsMedia extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'windowsmedia.png';

}
