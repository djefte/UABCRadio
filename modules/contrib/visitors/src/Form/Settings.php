<?php

namespace Drupal\visitors\Form;

use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\visitors\VisitorsVisibilityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Visitors Settings Form.
 */
class Settings extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'visitors.config';

  /**
   * An extension discovery instance.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected $themeList;

  /**
   * An extension discovery instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Settings form.
   *
   * @param \Drupal\Core\Extension\ThemeExtensionList $theme_list
   *   The theme list.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ThemeExtensionList $theme_list, EntityTypeManagerInterface $entity_type_manager) {
    $this->themeList = $theme_list;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('extension.list.theme'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visitors_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('visitors.config');
    $system_config = $this->config('system.theme');
    $form = parent::buildForm($form, $form_state);

    $form['#attached']['library'][] = 'visitors/visitors.admin';

    $roles = [];
    foreach ($this->entityTypeManager->getStorage('user_role')->loadMultiple() as $name => $role) {
      $roles[$name] = $role->label();
    }

    $all_themes = $this->themeList->getList();
    $default_theme = $system_config->get('default');
    $admin_theme = $system_config->get('admin');

    $default_name = $all_themes[$default_theme]->info['name'];
    $themes_installed = [
      'default' => $this->t('Default (@default)', ['@default' => $default_name]),
    ];
    if ($admin_theme) {
      $admin_name = $all_themes[$admin_theme]->info['name'];
      $themes_installed['admin'] = $this->t('Admin (@admin)', ['@admin' => $admin_name]);
    }

    $list_themes = array_filter($all_themes, function ($obj) {
      $a = get_object_vars($obj);
      return $a['status'] ?? FALSE;
    });
    $themes_installed += array_map(function ($value) {
      return $value->info['name'];
    }, $list_themes);

    $form['visitors_disable_tracking'] = [
      '#type' => 'radios',
      '#title' => $this->t('Track visitors'),
      '#options' => [
        $this->t('Enabled'),
        $this->t('Disabled'),
      ],
      '#description' => $this->t('Enable or disable tracking of visitors.'),
      '#default_value' => (int) $config->get('disable_tracking'),
    ];

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Set a theme for reports'),
      '#options' => $themes_installed,
      '#default_value' => $config->get('theme') ?: 'admin',
      '#description' => $this->t('Select a theme for the Visitors reports.'),
    ];

    // Visibility settings.
    $form['tracking_scope'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Tracking scope'),
      '#title_display' => 'invisible',
      '#default_tab' => 'edit-tracking',
    ];

    // Page specific visibility configurations.
    $visibility_request_path_pages = $config->get('visibility.request_path_pages');

    $form['page_visibility_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Pages'),
      '#group' => 'tracking_scope',
    ];

    $description = $this->t(
        "Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.",
        [
          '%blog' => '/blog',
          '%blog-wildcard' => '/blog/*',
          '%front' => '<front>',
        ]
      );

    $form['page_visibility_settings']['visitors_visibility_request_path_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#title_display' => 'invisible',
      '#default_value' => !empty($visibility_request_path_pages) ? $visibility_request_path_pages : '',
      '#description' => $description,
      '#rows' => (int) 10,
    ];
    $form['page_visibility_settings']['visitors_visibility_request_path_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add tracking to specific pages'),
      '#title_display' => 'invisible',
      '#options' => [
        VisitorsVisibilityInterface::PATH_EXCLUDE => $this->t('All pages except those listed'),
        VisitorsVisibilityInterface::PATH_INCLUDE => $this->t('Only the listed pages'),
      ],
      '#default_value' => $config->get('visibility.request_path_mode'),
    ];

    // Render the role overview.
    $visibility_user_role_roles = $config->get('visibility.user_role_roles');

    $form['role_visibility_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Roles'),
      '#group' => 'tracking_scope',
    ];

    $form['role_visibility_settings']['visitors_visibility_user_role_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add tracking for specific roles'),
      '#options' => [
        $this->t('Add to the selected roles only'),
        $this->t('Add to every role except the selected ones'),
      ],
      '#default_value' => $config->get('visibility.user_role_mode'),
    ];
    $form['role_visibility_settings']['visitors_visibility_user_role_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#default_value' => !empty($visibility_user_role_roles) ? $visibility_user_role_roles : [],
      '#options' => $this->roleOptions(),
      '#description' => $this->t('If none of the roles are selected, all users will be tracked. If a user has any of the roles checked, that user will be tracked (or excluded, depending on the setting above).'),
    ];

    $form['user_visibility_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Users'),
      '#group' => 'tracking_scope',
    ];
    $t_permission = ['%permission' => $this->t('opt-out of visitors tracking')];
    $form['user_visibility_settings']['visitors_visibility_user_account_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Allow users to customize tracking on their account page'),
      '#options' => [
        VisitorsVisibilityInterface::USER_NO_PERSONALIZATION => $this->t('No customization allowed'),
        VisitorsVisibilityInterface::USER_OPT_OUT => $this->t('Tracking on by default, users with %permission permission can opt out', $t_permission),
        VisitorsVisibilityInterface::USER_OPT_IN => $this->t('Tracking off by default, users with %permission permission can opt in', $t_permission),
      ],
      '#default_value' => $config->get('visibility.user_account_mode') ?? 0,
    ];
    $form['user_visibility_settings']['visitors_trackuserid'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Track User ID'),
      '#default_value' => $config->get('track.userid'),
      '#description' => $this->t('User ID enables the analysis of groups of sessions, across devices, using a unique, persistent, and representing a user. <a href=":url">Learn more about the benefits of using User ID</a>.', [':url' => 'https://matomo.org/docs/user-id/']),
    ];

    $form['user_visibility_settings']['visibility_exclude_user1'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude user1 from statistics'),
      '#default_value' => $config->get('visibility.exclude_user1'),
      '#description' => $this->t('Exclude hits of user1 from statistics.'),
    ];

    $form['entity'] = [
      '#type' => 'details',
      '#title' => $this->t('Entity counter'),
      '#group' => 'tracking_scope',
    ];
    $form['entity']['counter_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $config->get('counter.enabled'),
      '#description' => $this->t('Count the number of times entities are viewed.'),
    ];
    $form['entity']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity Types'),
      '#options' => $this->entityTypes(),
      '#default_value' => $config->get('counter.entity_types') ?? [],
      '#description' => $this->t('Which entity types should be tracked.'),
    ];

    $form['retention'] = [
      '#type' => 'details',
      '#title' => $this->t('Retention'),
      '#group' => 'tracking_scope',
    ];
    $form['retention']['flush_log_timer'] = [
      '#type' => 'select',
      '#title' => $this->t('Discard visitors logs older than'),
      '#default_value'   => $config->get('flush_log_timer'),
      '#options' => [
        0 => $this->t('Never'),
        3600 => $this->t('1 hour'),
        10800 => $this->t('3 hours'),
        21600 => $this->t('6 hours'),
        32400 => $this->t('9 hours'),
        43200 => $this->t('12 hours'),
        86400 => $this->t('1 day'),
        172800 => $this->t('2 days'),
        259200 => $this->t('3 days'),
        604800 => $this->t('1 week'),
        1209600 => $this->t('2 weeks'),
        4838400 => $this->t('1 month 3 weeks'),
        9676800 => $this->t('3 months 3 weeks'),
        31536000 => $this->t('1 year'),
      ],
      '#description' =>
      $this->t('Older visitors log entries (including referrer statistics) will be automatically discarded. (Requires a correctly configured <a href="@cron">cron maintenance task</a>.)',
          ['@cron' => Url::fromRoute('system.status')->toString()]
      ),
    ];

    $form['retention']['bot_retention_log'] = [
      '#type' => 'select',
      '#title' => $this->t('Discard bot logs older than'),
      '#default_value'   => $config->get('bot_retention_log'),
      '#options' => [
        -1 => $this->t('Do not log'),
        0 => $this->t('Never'),
        3600 => $this->t('1 hour'),
        10800 => $this->t('3 hours'),
        21600 => $this->t('6 hours'),
        32400 => $this->t('9 hours'),
        43200 => $this->t('12 hours'),
        86400 => $this->t('1 day'),
        172800 => $this->t('2 days'),
        259200 => $this->t('3 days'),
        604800 => $this->t('1 week'),
        1209600 => $this->t('2 weeks'),
        4838400 => $this->t('1 month 3 weeks'),
        9676800 => $this->t('3 months 3 weeks'),
        31536000 => $this->t('1 year'),
      ],
      '#description' =>
      $this->t('Control how long or if visits by bots are logged.'),
    ];

    $form['miscellaneous'] = [
      '#type' => 'details',
      '#title' => $this->t('Miscellaneous'),
      '#group' => 'tracking_scope',
    ];

    $script_type = $config->get('script_type');
    $form['miscellaneous']['script_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Script type'),
      '#options' => [
        'minified' => $this->t('Minified'),
        'full' => $this->t('Full'),
      ],
      '#default_value' => $script_type == 'full' ? 'full' : 'minified',
      '#description' => $this->t('Full script is for debugging purposes. Minified script is for production.'),
    ];
    $form['miscellaneous']['items_per_page'] = [
      '#type' => 'select',
      '#title' => 'Items per page',
      '#default_value' => $config->get('items_per_page'),
      '#options' => [
        5 => 5,
        10 => 10,
        25 => 25,
        50 => 50,
        100 => 100,
        200 => 200,
        250 => 250,
        500 => 500,
        1000 => 1000,
      ],
      '#description' =>
      $this->t('This is only used for the referrer report.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::SETTINGS);
    $values = $form_state->getValues();

    $config
      ->set('theme', $values['theme'])
      ->set('items_per_page', $values['items_per_page'])
      ->set('flush_log_timer', $values['flush_log_timer'])
      ->set('bot_retention_log', $values['bot_retention_log'])
      ->set('track.userid', $values['visitors_trackuserid'])
      ->set('counter.enabled', $values['counter_enabled'])
      ->set('counter.entity_types', array_filter($values['entity_types'] ?? []))
      ->set('disable_tracking', $values['visitors_disable_tracking'])
      ->set('visibility.request_path_mode', $values['visitors_visibility_request_path_mode'])
      ->set('visibility.request_path_pages', $values['visitors_visibility_request_path_pages'])
      ->set('visibility.user_account_mode', $values['visitors_visibility_user_account_mode'])
      ->set('visibility.user_role_mode', $values['visitors_visibility_user_role_mode'])
      ->set('visibility.user_role_roles', array_filter($values['visitors_visibility_user_role_roles']))
      ->set('visibility.exclude_user1', $values['visibility_exclude_user1'])
      ->set('script_type', $values['script_type'] ?? 'minified')
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Returns a list of entity types.
   */
  protected function entityTypes() {
    $entity_types_list = [];
    $entity_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_definitions as $entity_name => $entity_definition) {
      if ($entity_definition instanceof ConfigEntityType) {
        continue;
      }
      $entity_types_list[$entity_name] = (string) $entity_definition->getLabel();
    }
    asort($entity_types_list);

    return $entity_types_list;
  }

  /**
   * Returns a list of roles.
   */
  protected function roleOptions() {
    $user_roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    $options = [];
    foreach ($user_roles as $role) {
      $options[$role->id()] = $role->label();
    }

    return \array_map('\Drupal\Component\Utility\Html::escape', $options);
  }

}
