<?php

declare(strict_types=1);

namespace Drupal\visitors\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Drupal\visitors\VisitorsCounterInterface;
use Drupal\visitors\VisitorsCronInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Visitors Cron Service.
 */
class CronService implements VisitorsCronInterface {

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The counter.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface
   */
  protected $counter;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * CronService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\visitors\VisitorsCounterInterface $counter
   *   The counter.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database, StateInterface $state, TimeInterface $time, VisitorsCounterInterface $counter) {
    $this->database = $database;
    $this->state = $state;
    $this->time = $time;
    $this->counter = $counter;

    $this->settings = $config_factory->get('visitors.config');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {

    $this->deleteExpiredLogs();
    $this->deleteBotLogs();
    $this->dayCounter();

  }

  /**
   * Delete expired logs.
   */
  protected function deleteExpiredLogs() {
    $flush_log_timer = $this->settings->get('flush_log_timer') ?? 0;
    if ($flush_log_timer == 0) {
      return;
    }
    $now = $this->time->getRequestTime();
    $delete_since = (string) $now - $flush_log_timer;
    // Clean up expired access logs.
    $this->database->delete('visitors')
      ->condition('visitors_date_time', $delete_since, '<')
      ->execute();
  }

  /**
   * Delete bot logs.
   */
  protected function deleteBotLogs() {
    $bot_retention_log = $this->settings->get('bot_retention_log') ?? 0;
    $bot_retention_log = abs($bot_retention_log);
    if ($bot_retention_log == 0) {
      return;
    }

    $now = $this->time->getRequestTime();
    $delete_since = (string) $now - $bot_retention_log;
    if ($bot_retention_log == 1) {
      $delete_since = '0';
    }

    $this->database->delete('visitors')
      ->condition('bot', 1)
      ->condition('visitors_date_time', $delete_since, '<')
      ->execute();
  }

  /**
   * Reset the day count.
   */
  protected function dayCounter() {
    $this->counter->resetDayCount();
    $max_total_count = $this->counter->maxTotalCount('node');
    $this->state->set('visitors.node_counter_scale', 1.0 / max(1.0, $max_total_count));
  }

}
