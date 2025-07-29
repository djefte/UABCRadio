<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the java plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_java")
 */
final class VisitorsJava extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'java.png';

}
