<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Base field handler to format timestamps.
 */
abstract class VisitorsTimestamp extends FieldPluginBase {

  /**
   * The format of the date.
   *
   * @var string
   */
  protected $format;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a VisitorsTimestamp object.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    $params = $this->options['group_type'] != 'group' ? ['function' => $this->options['group_type']] : [];
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;

    $timezone_location = $this->configFactory->get('system.date')->get('timezone.default');

    $timezone = new \DateTimeZone($timezone_location);
    $offset = $timezone->getOffset(new \DateTime());
    $field = $query->getDateField("$this->tableAlias.$this->realField", FALSE, FALSE);

    $query->setFieldTimezoneOffset($field, $offset);
    $formula = $query->getDateFormat($field, $this->getFormat(), FALSE);

    $this->field_alias = $query->addField(NULL, $formula, $this->field, $params);

    $this->addAdditionalFields();
  }

  /**
   * Returns the format of the date.
   *
   * @return string
   *   The format of the date.
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['config:system.date'];
  }

}
