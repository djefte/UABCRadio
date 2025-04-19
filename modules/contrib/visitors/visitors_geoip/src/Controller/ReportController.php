<?php

namespace Drupal\visitors_geoip\Controller;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\visitors\Controller\Report\ReportBaseController;
use Drupal\visitors\VisitorsLocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Report controller.
 */
class ReportController extends ReportBaseController {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface
   */
  protected $location;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('form_builder'),
      $container->get('visitors.location')
    );
  }

  /**
   * ReportController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\visitors\VisitorsLocationInterface $location
   *   The location service.
   */
  public function __construct(FormBuilderInterface $form_builder, VisitorsLocationInterface $location) {
    $this->formBuilder = $form_builder;
    $this->location = $location;
  }

  /**
   * The region page title.
   */
  public function getRegionTitle($country, $region) {
    $title = 'Region';
    if ($country) {
      $title = $this->location->getCountryLabel($country);
    }
    if ($region) {
      if ($region == '_none') {
        $region = 'Unknown';
      }
      $title = "$region, $title";
    }
    return $title;
  }

  /**
   * Country report.
   *
   * @param string $country
   *   The country code.
   * @param string|null $region
   *   The region code.
   */
  public function region($country, $region): array {
    $args = [];
    $view_display = 'region_table';
    if ($country) {
      $args[] = $country;
    }
    if ($region) {
      if ($region != '_none') {
        $args[] = $region;
      }
      $view_display = 'city_table';
    }
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $first_row['region_table'] = [
      '#view_id'      => 'visitors_geoip',
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
   * The city page title.
   */
  public function getCityTitle($country, $region, $city) {
    $title = 'City';
    if ($country) {
      $title = $this->location->getCountryLabel($country);
    }
    if ($region && $region != '_none') {
      $title = "$region, $title";
    }
    if ($city && $city != '_none') {
      $title = "$city, $title";
    }

    return $title;
  }

  /**
   * City report.
   *
   * @param string|null $country
   *   The country code.
   * @param string|null $region
   *   The region code.
   * @param string|null $city
   *   The city name.
   */
  public function city($country, $region, $city): array {

    $args = [];
    $view_display = 'city_table';
    if ($country) {
      $args[] = $country;
    }
    if ($region) {
      if ($region = '_none') {
        $args[] = NULL;
      }
      else {
        $args[] = $region;
      }
    }
    if ($city) {
      if ($city = '_none') {
        $args[] = NULL;
      }
      else {
        $args[] = $city;
      }
      $view_display = 'recent_view_table';
    }

    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $first_row['region_table'] = [
      '#view_id'      => 'visitors_geoip',
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

}
