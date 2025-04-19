<?php

namespace Drupal\visitors\Plugin\views\field;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_hour")
 */
final class VisitorsHour extends VisitorsTimestamp {

  /**
   * {@inheritdoc}
   */
  protected $format = 'H';

}
