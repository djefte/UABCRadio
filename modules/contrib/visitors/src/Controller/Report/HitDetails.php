<?php

namespace Drupal\visitors\Controller\Report;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\visitors\VisitorsReportInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hit Detail Report controller.
 */
final class HitDetails extends ControllerBase {
  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $date;

  /**
   * The report service.
   *
   * @var \Drupal\visitors\VisitorsReportInterface
   */
  protected $report;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): HitDetails {
    return new self(
      $container->get('date.formatter'),
      $container->get('visitors.report')
    );
  }

  /**
   * Constructs a HitDetails object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date service.
   * @param \Drupal\visitors\VisitorsReportInterface $report_service
   *   The report service.
   */
  public function __construct(DateFormatterInterface $date_formatter, VisitorsReportInterface $report_service) {
    $this->date = $date_formatter;
    $this->report = $report_service;
  }

  /**
   * Returns a hit details page.
   *
   * @return array
   *   A render array representing the hit details page content.
   */
  public function display($hit_id): array {
    return [
      'visitors_table' => [
        '#type' => 'table',
        '#rows'  => $this->report->hitDetails($hit_id),
      ],
    ];
  }

}
