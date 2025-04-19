<?php

namespace Drupal\visitors\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\visitors\Service\SequenceService;

/**
 * Sort handler for days of the week respecting system first day setting.
 *
 * @ViewsSort("visitors_timestamp")
 */
class VisitorsTimestamp extends SortPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field = $this->field;
    if ($field == 'visitor_localtime') {
      $field = 'visitors_visitor_localtime';
    }

    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $query->addOrderBy(NULL, NULL, $this->options['order'], $field);
  }

  /**
   * {@inheritdoc}
   */
  public function postExecute(&$values) {
    $values = SequenceService::fill($values);
  }

}
