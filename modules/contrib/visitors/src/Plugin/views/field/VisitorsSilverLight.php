<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the silverlight plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_silverlight")
 */
final class VisitorsSilverLight extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'silverlight.png';

}
