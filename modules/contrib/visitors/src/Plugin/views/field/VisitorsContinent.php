<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\visitors\VisitorsLocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the device brand of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_continent")
 */
final class VisitorsContinent extends FieldPluginBase {

  /**
   * The location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface
   */
  protected $location;

  /**
   * Continent field.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\visitors\VisitorsLocationInterface $location
   *   The location service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisitorsLocationInterface $location) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->location = $location;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('visitors.location'),
    );
  }

  /**
   * Define the available options.
   *
   * @return array
   *   The available options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['abbreviation'] = ['default' => FALSE];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['abbreviation'] = [
      '#title' => $this->t('Use abbreviation'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['abbreviation'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    if (!$this->options['abbreviation']) {
      $value = $this->location->getContinentLabel($value);
    }

    return $value;
  }

}
