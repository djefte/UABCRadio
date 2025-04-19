<?php

namespace Drupal\visitors\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\Date;
use Drupal\visitors\VisitorsDateRangeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("visitors_date")
 */
class VisitorsDate extends Date {

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\VisitorsDateRangeInterface
   */
  protected $dateRange;

  /**
   * Constructs a new Date handler.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\visitors\VisitorsDateRangeInterface $date_range
   *   The date range service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisitorsDateRangeInterface $date_range) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->dateRange = $date_range;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('visitors.date_range'),
    );
  }

  /**
   * Add a type selector to the value form.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if (!$form_state->get('exposed')) {
      $form['value']['type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Value type'),
        '#options' => [
          'date' => $this->t('A date in any machine readable format. CCYY-MM-DD HH:MM:SS is preferred.'),
          'offset' => $this->t('An offset from the current time such as "@example1" or "@example2"', [
            '@example1' => '+1 day',
            '@example2' => '-2 hours -30 minutes',
          ]),
          'global' => $this->t('Use the global visitors date range.'),
        ],
        '#default_value' => !empty($this->value['type']) ? $this->value['type'] : 'global',
      ];
    }

  }

  /**
   * Validate that the time values convert to something usable.
   */
  public function validateValidTime(&$form, FormStateInterface $form_state, $operator, $value) {
    $operators = $this->operators();

    if ($operators[$operator]['values'] == 1) {
      $convert = strtotime($value['value']);
      if (!empty($form['value']) && ($convert == -1 || $convert === FALSE)) {
        $form_state->setError($form['value'], $this->t('Invalid date format.'));
      }
    }
    elseif ($operators[$operator]['values'] == 2 && $value['type'] != 'global') {
      $min = strtotime($value['min']);
      if ($min == -1 || $min === FALSE) {
        $form_state->setError($form['min'], $this->t('Invalid date format.'));
      }
      $max = strtotime($value['max']);
      if ($max == -1 || $max === FALSE) {
        $form_state->setError($form['max'], $this->t('Invalid date format.'));
      }
    }
  }

  /**
   * Custom operator handler for between.
   *
   * Uses the global date ranges from DateFilter form.
   */
  protected function opBetween($field) {

    if ($this->value['type'] == 'global') {

      $a = $this->dateRange->getStartTimestamp();
      $b = $this->dateRange->getEndTimestamp();
    }
    else {
      $a = intval(strtotime($this->value['min'], 0));
      $b = intval(strtotime($this->value['max'], 0));
    }

    if ($this->value['type'] == 'offset') {
      // Keep sign.
      $a = '***CURRENT_TIME***' . sprintf('%+d', $a);
      // Keep sign.
      $b = '***CURRENT_TIME***' . sprintf('%+d', $b);
    }
    // This is safe because we are manually scrubbing the values.
    // It is necessary because $a and $b are formulas when using an offset.
    $operator = strtoupper($this->operator);
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $query->addWhereExpression($this->options['group'], "$field $operator $a AND $b");
  }

}
