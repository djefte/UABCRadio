<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the pdf plugin of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_pdf")
 */
final class VisitorsPdf extends VisitorsBrowserPlugin {

  /**
   * {@inheritdoc}
   */
  const ICON = 'pdf.png';

}
