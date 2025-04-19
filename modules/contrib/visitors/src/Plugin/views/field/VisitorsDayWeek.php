<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Field handler to display the hour (server) of the visit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_day_of_week")
 */
final class VisitorsDayWeek extends VisitorsTimestamp {

  /**
   * {@inheritdoc}
   */
  protected $format = '%w';

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    // Default to Sunday (0)
    $first_day = $this->configFactory->get('system.date')->get('first_day') ?? 0;

    $weekdays = [];
    $weekdays[((0 + 7 - $first_day) % 7)] = $this->t('Sunday');
    $weekdays[((1 + 7 - $first_day) % 7)] = $this->t('Monday');
    $weekdays[((2 + 7 - $first_day) % 7)] = $this->t('Tuesday');
    $weekdays[((3 + 7 - $first_day) % 7)] = $this->t('Wednesday');
    $weekdays[((4 + 7 - $first_day) % 7)] = $this->t('Thursday');
    $weekdays[((5 + 7 - $first_day) % 7)] = $this->t('Friday');
    $weekdays[((6 + 7 - $first_day) % 7)] = $this->t('Saturday');

    $value = (int) $this->getValue($values);

    // $output = $weekdays[$value];
    $output = [
      '#markup' => $weekdays[$value],
    ];
    // Add cache metadata.
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->addCacheTags(['config:system.date']);
    $cache_metadata->applyTo($output);

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    $params = $this->options['group_type'] != 'group' ? ['function' => $this->options['group_type']] : [];
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;

    // Default to Sunday (0)
    $system_date = $this->configFactory->get('system.date');
    $first_day = $system_date->get('first_day') ?? 0;

    $timezone_location = $system_date->get('timezone.default');

    $timezone = new \DateTimeZone($timezone_location);
    $offset = $timezone->getOffset(new \DateTime());

    $field = $query->getDateField("$this->tableAlias.$this->realField", FALSE, FALSE);
    $query->setFieldTimezoneOffset($field, $offset);
    $formula = $query->getDateFormat($field, $this->getFormat(), FALSE);
    $sorted_expression = $formula;
    if ($first_day != 0) {
      $sorted_expression = "(($formula + 7 - $first_day) % 7)";
    }

    $this->field_alias = $query->addField(NULL, $sorted_expression, $this->field, $params);

    $this->addAdditionalFields();
  }

}
