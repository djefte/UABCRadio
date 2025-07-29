<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the flash plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_flash")
 */
class VisitorsFlash extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'flash.png';

}
