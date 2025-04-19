<?php

namespace Drupal\visitors\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default argument plugin for current or relative path.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "visitors_path",
 *   title = @Translation("Current Path or Route")
 * )
 */
class Path extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|null
   */
  protected $currentPath;

  /**
   * Constructs a new Node instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\CurrentPathStack|null $current_path
   *   The current_path.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ?CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.current')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['pop'] = ['default' => 0];
    $options['route'] = ['default' => FALSE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['pop'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Items to pop off the path'),
      '#description' => $this->t('0 = Current path. 1 = node/1/visitors becomes node/1.'),
      '#default_value' => $this->options['pop'],
    ];
    $form['route'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use route instead of path'),
      '#description' => $this->t('Convert the path to a route.'),
      '#default_value' => $this->options['route'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    if (!$this->currentPath) {
      return '';
    }

    $path = $this->currentPath->getPath();

    $pop = ($this->options['pop']);
    if ($pop > 0) {
      $path = explode('/', $path);
      $path = array_slice($path, 0, -$pop);
      $path = implode('/', $path);
    }

    if ($this->options['route']) {
      $route = Url::fromUserInput($path)->getRouteName();
      return $route;
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url.path'];
  }

}
