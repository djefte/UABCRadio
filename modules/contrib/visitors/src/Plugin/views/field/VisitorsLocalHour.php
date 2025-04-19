<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_local_hour")
 */
final class VisitorsLocalHour extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    // Add the field.
    $params = $this->options['group_type'] != 'group' ? ['function' => $this->options['group_type']] : [];
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $field = $this->configuration['field'];
    $alias = 'visitors_visitor_localtime';
    $this->field_alias = $query->addField(NULL, "FLOOR($field/3600)", $alias, $params);

    $this->addAdditionalFields();
  }

}
