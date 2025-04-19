<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_day_of_month")
 */
final class VisitorsDayMonth extends VisitorsTimestamp {

  /**
   * {@inheritdoc}
   */
  protected $format = 'd';

}
