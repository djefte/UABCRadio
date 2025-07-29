<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the realplayer plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_realplayer")
 */
final class VisitorsRealPlayer extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'realplayer.png';

}
