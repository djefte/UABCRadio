<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Component\Utility\Xss as UtilityXss;
use Drupal\views\Render\ViewsRenderPipelineMarkup;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_week")
 */
final class VisitorsWeek extends VisitorsTimestamp {

  /**
   * {@inheritdoc}
   */
  protected $format = '%X%V';

  /**
   * {@inheritdoc}
   */
  public function render($values) {

    $value = $this->getValue($values);
    $year = (int) substr($value, 0, 4);
    $week = (int) substr($value, 4, 2);

    // Converts week of year to date.
    $date = new \DateTime();
    $date->setISODate($year, $week);

    return ViewsRenderPipelineMarkup::create(UtilityXss::filterAdmin($date->format('Y-m-d')));
  }

}
