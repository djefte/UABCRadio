<?php

namespace Drupal\visitors\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\visitors\VisitorsDateRangeInterface;

/**
 * The Date Range service.
 */
class DateRangeService implements VisitorsDateRangeInterface {

  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The session object.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Valid periods values.
   *
   * @var array
   */
  protected $validPeriods = [
    'day',
    'week',
    'month',
    'year',
    'range',
  ];

  /**
   * DateService constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct($date_formatter, $request_stack) {
    $this->dateFormatter = $date_formatter;
    $request = $request_stack->getCurrentRequest();
    $this->session = $request->getSession();
  }

  /**
   * {@inheritdoc}
   */
  public function getPeriod() {
    $period = $this->session->get('visitors_period');
    if (!in_array($period, $this->validPeriods)) {
      $period = 'day';
    }

    return $period;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartTimestamp() {
    $start_timestamp = $this->session->get('visitors_from');
    $start_timestamp = intval($start_timestamp);
    if ($start_timestamp == 0) {
      // $timezone = date_default_timezone_get();
      $date = new DrupalDateTime('yesterday');
      $start_timestamp = $date->getTimestamp();
    }

    return $start_timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate() {
    $start_timestamp = $this->getStartTimestamp();
    $start_date = DrupalDateTime::createFromTimestamp($start_timestamp);

    return $start_date->format('Y-m-d');
  }

  /**
   * {@inheritdoc}
   */
  public function getEndTimestamp() {
    $end_timestamp = $this->session->get('visitors_to');
    $end_timestamp = intval($end_timestamp);
    if ($end_timestamp == 0) {
      // $timezone = date_default_timezone_get();
      $date = new DrupalDateTime('today');
      $end_timestamp = $date->getTimestamp();
    }

    return $end_timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate() {
    $end_timestamp = $this->getEndTimestamp();

    $end_date = DrupalDateTime::createFromTimestamp($end_timestamp - self::ONE_DAY);

    return $end_date->format('Y-m-d');
  }

  /**
   * {@inheritdoc}
   */
  public function setPeriodAndDates($period, $start_date, $end_date) {
    $period = $this->setPeriod($period);

    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    if (!$start_timestamp) {
      $start_timestamp = 0;
    }
    if (!$end_timestamp) {
      $end_timestamp = 0;
    }
    $start_date = DrupalDateTime::createFromTimestamp($start_timestamp);
    $end_date = DrupalDateTime::createFromTimestamp($end_timestamp);

    $visitors_from = 0;
    $visitors_to = 0;
    if ($period == 'range') {
      $visitors_from = $start_date->getTimestamp();
      $visitors_to = $end_date->getTimestamp();
      $visitors_to += self::ONE_DAY;
    }
    else {
      $selected_date = $start_date->getTimestamp();
      switch ($period) {
        case 'year':
          $start = date('Y-01-01', $selected_date);
          $end = strtotime("$start +1 year");
          $start = strtotime($start);

          $start = DrupalDateTime::createFromTimestamp($start);
          $end = DrupalDateTime::createFromTimestamp($end);

          $visitors_from = $start->getTimestamp();
          $visitors_to = $end->getTimestamp();
          break;

        case 'month':
          $start = date('Y-m-01', $selected_date);
          $end = strtotime("$start +1 month");
          $start = strtotime($start);

          $start = DrupalDateTime::createFromTimestamp($start);
          $end = DrupalDateTime::createFromTimestamp($end);

          $visitors_from = $start->getTimestamp();
          $visitors_to = $end->getTimestamp();
          break;

        case 'week':
          $start = strtotime('Last Sunday', $selected_date);
          $end = strtotime('Next Sunday', $start);

          $start = DrupalDateTime::createFromTimestamp($start);
          $end = DrupalDateTime::createFromTimestamp($end);

          $visitors_from = $start->getTimestamp();
          $visitors_to = $end->getTimestamp();
          break;

        case 'day':
        default:
          $visitors_from = $start_date->getTimestamp();
          $visitors_to = $visitors_from + self::ONE_DAY;
          break;
      }

    }

    $this->session->set('visitors_from', (int) $visitors_from);
    $this->session->set('visitors_to', (int) $visitors_to);
  }

  /**
   * Set the period.
   *
   * @param string $period
   *   The period.
   *
   * @return string
   *   The period.
   */
  protected function setPeriod($period) {
    if (!in_array($period, $this->validPeriods)) {
      $period = 'day';
    }
    $this->session->set('visitors_period', $period);

    return $period;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $period = $this->getPeriod();
    $start_timestamp = $this->getStartTimestamp();
    $end_timestamp = $this->getEndTimestamp();

    $summary = '';
    switch ($period) {
      case 'day':
        $summary = $this->dateFormatter->format($start_timestamp, 'custom', 'l, F j, Y');
        break;

      case 'month':
        $summary = $this->dateFormatter->format($start_timestamp, 'custom', 'F Y');
        break;

      case 'year':
        $summary = $this->dateFormatter->format($start_timestamp, 'custom', 'Y');
        break;

      case 'week':
      case 'range':
      default:
        $formatted_start = $this->dateFormatter->format($start_timestamp, 'custom', 'F j, Y');
        $summary = $formatted_start . ' - ' . $this->dateFormatter->format($end_timestamp - self::ONE_DAY, 'custom', 'F j, Y');
        break;
    }

    return $summary;
  }

}
