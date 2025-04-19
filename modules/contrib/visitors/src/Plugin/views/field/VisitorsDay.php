<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_day")
 */
final class VisitorsDay extends VisitorsTimestamp {

  /**
   * {@inheritdoc}
   */
  protected $format = 'Y-m-d';

}
