<?php

namespace Drupal\visitors\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Drupal\visitors\StatisticsViewsResult;
use Drupal\visitors\VisitorsCounterInterface;

/**
 * Counts entity views.
 */
class CounterService implements VisitorsCounterInterface {

  /**
   * The number of seconds in one day.
   */
  const ONE_DAY = 86400;

  /**
   * The entities viewed.
   *
   * @var array
   */
  protected static $entities = [];

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The date service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs the counter service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection for the node view storage.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The date service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(Connection $connection, TimeInterface $time, StateInterface $state) {
    $this->database = $connection;
    $this->time = $time;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function recordView(string $type, int $id) {
    return (bool) $this->database
      ->merge('visitors_counter')
      ->key('entity_type', $type)
      ->key('entity_id', $id)
      ->fields([
        'today' => 1,
        'total' => 1,
        'timestamp' => $this->time->getRequestTime(),
      ])
      ->expression('today', '[today] + 1')
      ->expression('total', '[total] + 1')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function fetchViews(string $type, array $ids): array {
    $views = $this->database
      ->select('visitors_counter', 'vc')
      ->fields('vc', ['total', 'today', 'timestamp'])
      ->condition('entity_type', $type)
      ->condition('entity_id', $ids, 'IN')
      ->execute()
      ->fetchAll();
    foreach ($views as $id => $view) {
      $views[$id] = new StatisticsViewsResult($view->total, $view->today, $view->timestamp);
    }
    return $views;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchView(string $type, int $id) {
    $views = $this->fetchViews($type, [$id]);
    return reset($views);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAll(string $type, string $order = 'total', int $limit = 5) {
    assert(in_array($order, ['total', 'today', 'timestamp']), "Invalid order argument.");

    return $this->database
      ->select('visitors_counter', 'vc')
      ->fields('vc', ['entity_id'])
      ->condition('entity_type', $type)
      ->orderBy($order, 'DESC')
      ->range(0, $limit)
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function resetDayCount() {
    $counter_timestamp = $this->state->get('visitors.count_timestamp', 0);
    $now = $this->time->getRequestTime();
    if (($now - $counter_timestamp) >= self::ONE_DAY) {
      $this->state->set('visitors.count_timestamp', $now);
      $this->database->update('visitors_counter')
        ->fields(['today' => 0])
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteViews(string $type, int $id) {
    return (bool) $this->database
      ->delete('visitors_counter')
      ->condition('entity_type', $type)
      ->condition('entity_id', $id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function maxTotalCount(string $type) {
    $query = $this->database->select('visitors_counter', 'vc');
    $query->addExpression('MAX([total])');
    $query->condition('entity_type', $type);
    $max_total_count = (int) $query->execute()->fetchField();
    return $max_total_count;
  }

}
