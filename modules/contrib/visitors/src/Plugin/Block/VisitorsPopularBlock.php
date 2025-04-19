<?php

namespace Drupal\visitors\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\visitors\VisitorsCounterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Popular content' block.
 *
 * @Block(
 *   id = "visitors_popular_block",
 *   admin_label = @Translation("Popular content")
 * )
 */
final class VisitorsPopularBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The storage for visitor counter.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface
   */
  protected $statisticsStorage;

  /**
   * The renderer interface.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a StatisticsPopularBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\visitors\VisitorsCounterInterface $statistics_storage
   *   The storage for statistics.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer configuration array.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    VisitorsCounterInterface $statistics_storage,
    RendererInterface $renderer,
    ConfigFactoryInterface $config_factory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->statisticsStorage = $statistics_storage;
    $this->renderer = $renderer;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('visitors.counter'),
      $container->get('renderer'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'top_day_num' => 0,
      'top_all_num' => 0,
      'top_last_num' => 0,
      'entity_type' => 'node',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $settings = $this->configFactory->get('visitors.config');
    $entity_type = $this->configuration['entity_type'] ?? '';
    $allowed_entity_types = $settings->get('counter.entity_types');
    $disabled_or_entity_type_not_allowed = !$settings->get('counter.enabled') || !in_array($entity_type, $allowed_entity_types);
    if ($disabled_or_entity_type_not_allowed) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Popular content block settings.
    $numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 30, 40];
    $numbers = ['0' => $this->t('Disabled')] + array_combine($numbers, $numbers);
    $form['statistics_block_top_day_num'] = [
      '#type' => 'select',
      '#title' => $this->t("Number of day's top views to display"),
      '#default_value' => $this->configuration['top_day_num'],
      '#options' => $numbers,
      '#description' => $this->t('How many content items to display in "day" list.'),
    ];
    $form['statistics_block_top_all_num'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of all time views to display'),
      '#default_value' => $this->configuration['top_all_num'],
      '#options' => $numbers,
      '#description' => $this->t('How many content items to display in "all time" list.'),
    ];
    $form['statistics_block_top_last_num'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of most recent views to display'),
      '#default_value' => $this->configuration['top_last_num'],
      '#options' => $numbers,
      '#description' => $this->t('How many content items to display in "recently viewed" list.'),
    ];
    $form['entity_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Entity types'),
      '#options' => $this->entityTypes(),
      '#default_value' => $this->configuration['entity_type'] ?? 'node',
      '#description' => $this->t('Select entity types to display popular content.'),
    ];

    return $form;
  }

  /**
   * Returns a list of entity types.
   */
  protected function entityTypes() {
    $allowed_entity_types = $this->configFactory->get('visitors.config')->get('counter.entity_types');
    $entity_types_list = [];
    $entity_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_definitions as $entity_name => $entity_definition) {
      if (!in_array($entity_name, $allowed_entity_types)) {
        continue;
      }
      $entity_types_list[$entity_name] = (string) $entity_definition->getLabel();
    }
    asort($entity_types_list);

    return $entity_types_list;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['top_day_num'] = $form_state->getValue('statistics_block_top_day_num');
    $this->configuration['top_all_num'] = $form_state->getValue('statistics_block_top_all_num');
    $this->configuration['top_last_num'] = $form_state->getValue('statistics_block_top_last_num');
    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = [];
    $entity_type = 'node';
    if ($this->configuration['top_day_num'] > 0) {
      $ids = $this->statisticsStorage->fetchAll($entity_type, 'today', $this->configuration['top_day_num']);
      if ($ids) {
        $content['top_day'] = $this->entityLabelList($ids, $this->t("Today's:"));
        $content['top_day']['#suffix'] = '<br />';
      }
    }

    if ($this->configuration['top_all_num'] > 0) {
      $ids = $this->statisticsStorage->fetchAll($entity_type, 'total', $this->configuration['top_all_num']);
      if ($ids) {
        $content['top_all'] = $this->entityLabelList($ids, $this->t('All time:'));
        $content['top_all']['#suffix'] = '<br />';
      }
    }

    if ($this->configuration['top_last_num'] > 0) {
      $ids = $this->statisticsStorage->fetchAll($entity_type, 'timestamp', $this->configuration['top_last_num']);
      $content['top_last'] = $this->entityLabelList($ids, $this->t('Last viewed:'));
      $content['top_last']['#suffix'] = '<br />';
    }

    return $content;
  }

  /**
   * Generates the ordered array of entity links for build().
   *
   * @param int[] $ids
   *   An ordered array of entity ids.
   * @param string $title
   *   The title for the list.
   *
   * @return array
   *   A render array for the list.
   */
  protected function entityLabelList(array $ids, $title) {
    $entity_type = $this->configuration['entity_type'] ?? 'node';
    $entities = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($ids);

    $items = [];
    foreach ($ids as $id) {
      $entity = $this->entityRepository->getTranslationFromContext($entities[$id]);
      $item = $entity->toLink()->toRenderable();
      $this->renderer->addCacheableDependency($item, $entity);
      $items[] = $item;
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $title,
      '#cache' => [
        'tags' => $this->entityTypeManager->getDefinition($entity_type)->getListCacheTags(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['config:visitors.config'];
  }

}
