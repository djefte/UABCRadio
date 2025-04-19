<?php

declare(strict_types=1);

namespace Drupal\visitors\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\visitors\VisitorsOnlineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Visitors Online' block.
 *
 * @Block(
 *   id = "visitors_online",
 *   admin_label = @Translation("Visitors Online")
 * )
 */
final class VisitorsOnlineBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Five minutes in seconds.
   */
  const MINUTE_5 = 300;

  /**
   * The visitors online service.
   *
   * @var \Drupal\visitors\VisitorsOnlineInterface
   */
  protected $online;

  /**
   * Constructs a StatisticsPopularBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\visitors\VisitorsOnlineInterface $visitors_online
   *   The visitors online service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisitorsOnlineInterface $visitors_online) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->online = $visitors_online;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('visitors.online'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'now_30_minute' => TRUE,
      'now_24_hour' => FALSE,
      'yesterday_30_minute' => FALSE,
      'yesterday_24_hour' => FALSE,
      'last_week_30_minute' => FALSE,
      'last_week_24_hour' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['now_30_minute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active visitors (last 30 min)'),
      '#description' => $this->t('Show the number of visitors in the last 30 minutes.'),
      '#default_value' => $this->configuration['now_30_minute'],
    ];
    $form['now_24_hour'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Visitors in the past 24 hours'),
      '#description' => $this->t('Show the number of visitors in the past 24 hours.'),
      '#default_value' => $this->configuration['now_24_hour'],
    ];
    $form['yesterday_30_minute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Visitors yesterday (same 30 min)'),
      '#description' => $this->t('Show the number of visitors in the same 30-minute period yesterday.'),
      '#default_value' => $this->configuration['yesterday_30_minute'],
    ];
    $form['yesterday_24_hour'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Visitors yesterday (total)'),
      '#description' => $this->t('Show the total number of visitors yesterday.'),
      '#default_value' => $this->configuration['yesterday_24_hour'],
    ];
    $form['last_week_30_minute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Visitors last week (same 30 min)'),
      '#description' => $this->t('Show the number of visitors in the same 30-minute period last week.'),
      '#default_value' => $this->configuration['last_week_30_minute'],
    ];
    $form['last_week_24_hour'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Visitors last week (same day)'),
      '#description' => $this->t('Show the total number of visitors on the same day last week.'),
      '#default_value' => $this->configuration['last_week_24_hour'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['now_30_minute'] = $form_state->getValue('now_30_minute');
    $this->configuration['now_24_hour'] = $form_state->getValue('now_24_hour');
    $this->configuration['yesterday_30_minute'] = $form_state->getValue('yesterday_30_minute');
    $this->configuration['yesterday_24_hour'] = $form_state->getValue('yesterday_24_hour');
    $this->configuration['last_week_30_minute'] = $form_state->getValue('last_week_30_minute');
    $this->configuration['last_week_24_hour'] = $form_state->getValue('last_week_24_hour');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $items = [];
    if ($this->configuration['now_30_minute']) {
      $items[] = $this->t('Last 30 min: @count', [
        '@count' => $this->online->getLast30Minutes(),
      ]);
    }
    if ($this->configuration['now_24_hour']) {
      $items[] = $this->t('Last 24 hours: @count', [
        '@count' => $this->online->getLast24Hours(),
      ]);
    }
    if ($this->configuration['yesterday_30_minute']) {
      $items[] = $this->t('Yesterday (same 30 min): @count', [
        '@count' => $this->online->getYesterday30Minutes(),
      ]);
    }
    if ($this->configuration['yesterday_24_hour']) {
      $items[] = $this->t('Yesterday (total): @count', [
        '@count' => $this->online->getYesterday24Hours(),
      ]);
    }
    if ($this->configuration['last_week_30_minute']) {
      $items[] = $this->t('Last week (same 30 min): @count', [
        '@count' => $this->online->getLastWeek30Minutes(),
      ]);
    }
    if ($this->configuration['last_week_24_hour']) {
      $items[] = $this->t('Last week (same day): @count', [
        '@count' => $this->online->getLastWeek24Hours(),
      ]);
    }

    $content = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return self::MINUTE_5;
  }

}
