<?php

namespace Drupal\visitors;

/**
 * Interface VisitorsViewInterface.
 *
 * @package Drupal\visitors
 */
interface VisitorsCounterInterface {

  /**
   * Counts an entity view.
   *
   * @param string $type
   *   The type of the entity to count.
   * @param int $id
   *   The ID of the entity to count.
   *
   * @return bool
   *   TRUE if the entity view has been counted.
   */
  public function recordView(string $type, int $id);

  /**
   * Fetches the number of views for an entity.
   *
   * @param string $type
   *   The type of the entity to count.
   * @param int $id
   *   The ID of the entity to count.
   *
   * @return \Drupal\visitors\StatisticsViewsResult
   *   The number of views for the entity.
   */
  public function fetchView(string $type, int $id);

  /**
   * Returns the number of times entities have been viewed.
   *
   * @param string $type
   *   The type of the entity to count.
   * @param array $ids
   *   An array of IDs of entities to fetch the views for.
   *
   * @return \Drupal\visitors\StatisticsViewsResult[]
   *   An array of value objects representing the number of times each entity
   *   has been viewed. The array is keyed by entity ID. If an ID does not
   *   exist, it will not be present in the array.
   */
  public function fetchViews(string $type, array $ids);

  /**
   * Fetches the number of views for a list of entities.
   *
   * @param string $type
   *   The type of the entity to count.
   * @param string $order
   *   The type of the entity to count.
   * @param int $limit
   *   The IDs of the entities to count.
   *
   * @return array
   *   The number of views for the entities.
   */
  public function fetchAll(string $type, string $order, int $limit);

  /**
   * Delete counts for a specific entity.
   *
   * @param string $type
   *   The type of the entity to count.
   * @param int $id
   *   The ID of the entity which views to delete.
   *
   * @return bool
   *   TRUE if the entity views have been deleted.
   */
  public function deleteViews(string $type, int $id);

  /**
   * Reset the day counter for all entities once every day.
   */
  public function resetDayCount();

  /**
   * Returns the highest 'total' value.
   *
   * @return int
   *   The highest 'total' value.
   */
  public function maxTotalCount(string $type);

}
