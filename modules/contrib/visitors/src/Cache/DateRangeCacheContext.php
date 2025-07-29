<?php

declare(strict_types=1);

namespace Drupal\visitors\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\visitors\VisitorsDateRangeInterface;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Defines a custom cache context for Views, depending on TempStore date range.
 */
class DateRangeCacheContext implements CacheContextInterface {

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\VisitorsDateRangeInterface
   */
  protected $dateRange;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a DateRangeCacheContext object.
   *
   * @param \Drupal\visitors\VisitorsDateRangeInterface $visitors_date_range
   *   The date range service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(VisitorsDateRangeInterface $visitors_date_range, TimeInterface $time) {
    $this->dateRange = $visitors_date_range;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Visitors Date Range Filter');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $start = $this->dateRange->getStartTimestamp();
    $end = $this->dateRange->getEndTimestamp();
    $now = $this->time->getCurrentTime();

    if ($end > $now) {
      $end = $now;
    }

    return "$start:$end";
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    $metadata = new CacheableMetadata();

    $end = $this->dateRange->getEndTimestamp();
    $now = $this->time->getCurrentTime();

    if ($end > $now) {
      // Disable caching by setting max-age to 0.
      $metadata->setCacheMaxAge(0);
    }

    return $metadata;
  }

}
