<?php

namespace Drupal\visitors\Controller\Report;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\visitors\VisitorsLocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generic Report controller.
 */
class ReportController extends ReportBaseController {


  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $date;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $settings;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface
   */
  protected $locationService;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('form_builder'),
      $container->get('string_translation'),
      $container->get('config.factory'),
      $container->get('messenger'),
      $container->get('current_user'),
      $container->get('visitors.location'),
      $container->get('module_handler')
    );
  }

  /**
   * Constructs the report controller.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\visitors\VisitorsLocationInterface $location
   *   The location service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(FormBuilderInterface $form_builder, TranslationInterface $string_translation, ConfigFactoryInterface $config_factory, MessengerInterface $messenger, AccountProxyInterface $current_user, VisitorsLocationInterface $location, ModuleHandlerInterface $module_handler) {

    $this->formBuilder     = $form_builder;
    $this->account         = $current_user;
    $this->locationService = $location;
    $this->moduleHandler   = $module_handler;

    $this->settings = $config_factory->get('visitors.config');

    $this->setStringTranslation($string_translation);
    $this->setMessenger($messenger);
  }

  /**
   * Returns an Ajax response with the rendered view.
   */
  public function report(Request $request, string $view_id, string $display_id) {
    $blocks['path'] = [
      '#view_id'      => $view_id,
      '#view_display' => $display_id,
    ];

    $rendered = \views_embed_view($view_id, $display_id);

    $settings = NULL;
    $selector = $request->query->get('class');

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand($selector, $rendered, $settings));

    return $response;
  }

  /**
   * Returns software report.
   *
   * @return array
   *   A render array representing the days of month page content.
   */
  public function software(): array {

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $first_row = [];
    $second_row = [];

    $first_row['os_version_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'os_version_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $first_row['browser_version_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'browser_version_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $second_row['device_config_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'device_config_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $second_row['browser_engine_pie'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'browser_engine_pie',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $third_row['browser_plugin_list'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'browser_plugin_list',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($first_row, 'layout-row'),
        ],
        '2' => [
          $this->renderViews($second_row, 'layout-row'),
        ],
        '3' => [
          $this->renderViews($third_row, 'layout-row'),
        ],
      ],
      '#attached' => [
        'library' => [
          'visitors/visitors.report',
        ],
      ],
    ];
  }

  /**
   * Returns a time report.
   *
   * @return array
   *   A render array representing the hours page content.
   */
  public function time(): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');

    $first_row['daily_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'daily_column',
    ];

    $second_row['local_hour_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'local_hour_column',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $second_row['hour_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'hour_column',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $third_row['day_of_week_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'day_of_week_column',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $third_row['day_of_month_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'day_of_month_column',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $fourth_row['monthly_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'monthly_column',
    ];

    return [
      'visitors_date_filter_form' => $form,

      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($first_row),
        ],
        '2' => [
          $this->renderViews($second_row, 'layout-row'),
        ],
        '3' => [
          $this->renderViews($third_row, 'layout-row'),
        ],
        '4' => [
          $this->renderViews($fourth_row),
        ],
      ],
      '#attached' => [
        'library' => [
          'visitors/visitors.report',
        ],
      ],
    ];
  }

  /**
   * Returns a top pages report.
   *
   * @return array
   *   A render array representing the top pages page content.
   */
  public function topPages(): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['top_path_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'top_path_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks),
        ],
      ],
    ];
  }

  /**
   * Returns a hosts page.
   *
   * @return array
   *   A render array representing the hosts page content.
   */
  public function topHost(): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['path'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'top_host_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks),
        ],
      ],
    ];
  }

  /**
   * Returns a hosts report.
   *
   * @return array
   *   A render array representing the hosts page content.
   */
  public function recentHost($host): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['path'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'recent_view_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks, NULL, [NULL, $host]),
        ],
      ],
    ];
  }

  /**
   * Returns a title for the page.
   */
  public function getHostTitle(string $host) {
    $title = $this->stringTranslation
      ->translate('Visits from @host', ['@host' => $host]);

    return $title;
  }

  /**
   * Returns a top route page.
   *
   * @return array
   *   A render array representing the top pages page content.
   */
  public function topRoute(): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['route'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'top_route_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks),
        ],
      ],
    ];
  }

  /**
   * Returns recent visitors filtered by route.
   *
   * @return array
   *   A render array representing page views.
   */
  public function recentRoute(string $route) {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['route'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'recent_view_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks, NULL, [$route]),
        ],
      ],
    ];
  }

  /**
   * Returns a title for the page.
   */
  public function getRouteTitle(string $route) {
    $title = $this->stringTranslation
      ->translate('Route @route', ['@route' => $route]);

    return $title;
  }

  /**
   * Returns a recent hits page.
   *
   * @return array
   *   A render array representing the recent hits page content.
   */
  public function recentViews(): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['path'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'recent_view_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks),
        ],
      ],
    ];
  }

  /**
   * Returns referrer report.
   */
  public function nodeViews(int $node): array {

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $blocks['path'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'referrer_table',
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($blocks),
        ],
      ],
    ];
  }

  /**
   * Shows report related to devices.
   *
   * @return array
   *   A render array representing the days of month page content.
   */
  public function device(): array {

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $first_blocks = [];
    $second_blocks = [];

    $first_blocks['device_type_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'device_type_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $first_blocks['device_model_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'device_model_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $second_blocks['device_brand_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'device_brand_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $second_blocks['device_resolution_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'device_resolution_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    return [
      'visitors_date_filter_form' => $form,
      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($first_blocks, 'layout-row'),
        ],
        '2' => [
          $this->renderViews($second_blocks, 'layout-row'),
        ],
      ],
      '#attached' => [
        'library' => [
          'visitors/visitors.report',
        ],
      ],
    ];
  }

  /**
   * Returns a hours page.
   *
   * @return array
   *   A render array representing the hours page content.
   */
  public function location(): array {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');

    $first_row['continent_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'continent_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $first_row['country_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'country_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $second_row['distinct_countries_list'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'distinct_countries_list',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $second_row['region_table'] = [
      '#view_id'      => 'visitors_geoip',
      '#view_display' => 'region_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    $third_row['language_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => 'language_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];
    $third_row['city_table'] = [
      '#view_id'      => 'visitors_geoip',
      '#view_display' => 'city_table',
      '#attributes'   => [
        'class' => ['layout-column--half'],
      ],
    ];

    return [
      'visitors_date_filter_form' => $form,

      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($first_row, 'layout-row'),
        ],
        '2' => [
          $this->renderViews($second_row, 'layout-row'),
        ],
        '3' => [
          $this->renderViews($third_row, 'layout-row'),
        ],
      ],
      '#attached' => [
        'library' => [
          'visitors/visitors.report',
        ],
      ],
    ];
  }

  /**
   * Returns the Continent as the title for the page.
   */
  public function getContinentTitle($continent) {
    $title = $this->t('Continent');
    if ($continent) {
      $title = $this->locationService->getContinentLabel($continent);
    }

    return $title;
  }

  /**
   * Returns a continent page.
   *
   * @return array
   *   A render array representing the continent page content.
   */
  public function continent($continent): array {
    $args = [];
    $view_display = 'continent_table';
    if ($continent) {
      $args[] = $continent;
      $view_display = 'country_table';
    }
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');

    $first_row['continent_table'] = [
      '#view_id'      => 'visitors',
      '#view_display' => $view_display,
    ];

    return [
      'visitors_date_filter_form' => $form,

      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($first_row, NULL, $args),
        ],
      ],
      '#attached' => [
        'library' => [
          'visitors/visitors.report',
        ],
      ],
    ];
  }

  /**
   * Returns the Country as the title for the page.
   */
  public function getCountryTitle($country) {
    $title = $this->t('Country');
    if ($country) {
      $title = $this->locationService->getCountryLabel($country);
    }

    return $title;
  }

  /**
   * Returns a country page.
   *
   * @return array
   *   A render array representing the country page content.
   */
  public function country($country): array {
    $args = [];
    $view_id = 'visitors';
    $view_display = 'country_table';
    if ($country) {
      $args[] = NULL;
      $args[] = NULL;
      $args[] = $country;
      $view_display = 'recent_view_table';
    }
    if ($this->moduleHandler->moduleExists('visitors_geoip')) {
      $args = [$country];
      $view_id = 'visitors_geoip';
      $view_display = 'region_table';
    }
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $first_row['country_table'] = [
      '#view_id'      => $view_id,
      '#view_display' => $view_display,
    ];

    return [
      'visitors_date_filter_form' => $form,

      'main' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['visitors-main'],
        ],
        '1' => [
          $this->renderViews($first_row, NULL, $args),
        ],
      ],
      '#attached' => [
        'library' => [
          'visitors/visitors.report',
        ],
      ],
    ];
  }

  /**
   * Display the performance report.
   */
  public function performance($sequence = NULL): array {
    $view_display = NULL;
    switch ($sequence) {
      case 'hour':
        $view_display = 'performance_hourly_column';
        break;

      case 'day':
        $view_display = 'performance_daily_column';
        break;

      case 'week':
      default:
        $view_display = 'performance_weekly_column';
        break;
    }

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');

    $first_row['performance_daily_column'] = [
      '#view_id'      => 'visitors',
      '#view_display' => $view_display,
    ];

    return [
      'visitors_date_filter_form' => $form,
      'visitors_performance' => [
        '1' => $this->renderViews($first_row, 'layout-row'),
      ],
    ];
  }

}
