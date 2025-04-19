<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Component\Utility\Xss as UtilityXss;
use Drupal\views\Render\ViewsRenderPipelineMarkup;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_month")
 */
final class VisitorsMonth extends VisitorsTimestamp {

  /**
   * {@inheritdoc}
   */
  protected $format = 'Ym';

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $months = [
      1 => $this->t('January'),
      2 => $this->t('February'),
      3 => $this->t('March'),
      4 => $this->t('April'),
      5 => $this->t('May'),
      6 => $this->t('June'),
      7 => $this->t('July'),
      8 => $this->t('August'),
      9 => $this->t('September'),
      10 => $this->t('October'),
      11 => $this->t('November'),
      12 => $this->t('December'),
    ];

    $value = $this->getValue($values);
    $year = substr($value, 2, 2);
    $month = (int) substr($value, 4, 2);

    $output = $months[$month] . " '" . $year;

    return ViewsRenderPipelineMarkup::create(UtilityXss::filterAdmin($output));
  }

}
