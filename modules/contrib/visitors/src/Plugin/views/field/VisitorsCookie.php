<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the cookie plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_cookie")
 */
class VisitorsCookie extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'cookie.png';

}
