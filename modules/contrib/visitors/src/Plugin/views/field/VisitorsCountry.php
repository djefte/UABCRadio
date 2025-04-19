<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Component\Utility\Xss as UtilityXss;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Render\ViewsRenderPipelineMarkup;
use Drupal\views\ResultRow;
use Drupal\visitors\VisitorsLocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Field handler to display the device brand of the visitors.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_country")
 */
final class VisitorsCountry extends FieldPluginBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The path.
   *
   * @var string
   */
  protected $path;

  /**
   * The location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface
   */
  protected $location;

  /**
   * Browser version field.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\visitors\VisitorsLocationInterface $location
   *   The location service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, Request $request, VisitorsLocationInterface $location) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moduleHandler = $module_handler;
    $this->request = $request;

    $base_path = $this->request->getBasePath();
    $visitors_path = $this->moduleHandler->getModule('visitors')->getPath();
    $this->path = $base_path . '/' . $visitors_path;

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
      $container->get('module_handler'),
      $container->get('request_stack')->getCurrentRequest(),
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
    $options['icon'] = ['default' => TRUE];
    $options['text'] = ['default' => TRUE];
    $options['abbreviation'] = ['default' => FALSE];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['icon'] = [
      '#title' => $this->t('Show flag'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['icon'],
    ];
    $form['text'] = [
      '#title' => $this->t('Show name'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['text'],
    ];

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

    $output = '';
    $code = $this->getValue($values);
    $value = $code;
    if (!$this->options['abbreviation']) {
      $value = $this->location->getCountryLabel($code);
    }

    if ($this->options['icon']) {
      $flag = $code ? strtolower($code) : 'xx';
      $image = $this->path . "/icons/flags/$flag.png";

      $output .= '<img src="' . $image . '" width="16" height="16" /> ';
    }
    if ($this->options['text'] || $this->options['abbreviation']) {
      $output .= $value;
    }

    return ViewsRenderPipelineMarkup::create(UtilityXss::filterAdmin($output));
  }

}
