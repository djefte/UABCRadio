<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\visitors\VisitorsLanguageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the device brand of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_language")
 */
final class VisitorsLanguage extends FieldPluginBase {

  /**
   * The language service.
   *
   * @var \Drupal\visitors\VisitorsLanguageInterface
   */
  protected $language;

  /**
   * Continent field.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\visitors\VisitorsLanguageInterface $language
   *   The location service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisitorsLanguageInterface $language) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->language = $language;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('visitors.language'),
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
    $options['code'] = ['default' => FALSE];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['code'] = [
      '#title' => $this->t('Show language code'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['code'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $language_code = $this->getValue($values);

    $language = $this->language->getLanguageLabel($language_code);

    if ($this->options['code']) {
      $language = $language . ' (' . $language_code . ')';
    }

    return $language;
  }

}
