<?php

namespace Drupal\visitors\Plugin\views\field;

use DeviceDetector\Parser\OperatingSystem;
use Drupal\Component\Utility\Xss as UtilityXss;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Render\ViewsRenderPipelineMarkup;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Field handler to display the OperatingSystem of the visitor.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_operating_system")
 */
final class VisitorsOperatingSystem extends FieldPluginBase {

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moduleHandler = $module_handler;
    $this->request = $request;

    $base_path = $this->request->getBasePath();
    $visitors_path = $this->moduleHandler->getModule('visitors')->getPath();
    $this->path = $base_path . '/' . $visitors_path;
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

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['icon'] = [
      '#title' => $this->t('Show icon'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['icon'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $output = '';
    $short_name = $this->getValue($values);
    $os_name = OperatingSystem::getOsFamily($short_name);

    $image = $this->path . '/icons/os/' . $short_name . '.png';
    if ($this->options['icon']) {
      $output .= '<img src="' . $image . '" width="16" height="16" /> ';
    }

    $output .= $os_name;

    return ViewsRenderPipelineMarkup::create(UtilityXss::filterAdmin($output));
  }

}
