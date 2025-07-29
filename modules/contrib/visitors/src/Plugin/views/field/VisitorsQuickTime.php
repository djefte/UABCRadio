<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the quicktime plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_quicktime")
 */
final class VisitorsQuickTime extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'quicktime.png';

}
