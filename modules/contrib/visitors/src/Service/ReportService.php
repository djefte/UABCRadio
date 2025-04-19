<?php

namespace Drupal\visitors\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\visitors\VisitorsReportInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\visitors\VisitorsDateRangeInterface;

/**
 * Report data.
 *
 * @package visitors
 */
class ReportService implements VisitorsReportInterface {
  use StringTranslationTrait;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Items per page.
   *
   * @var int
   */
  protected $itemsPerPage;

  /**
   * The page number.
   *
   * @var int
   */
  protected $page;

  /**
   * The first day of week.
   *
   * @var int
   */
  protected $firstDay;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $date;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\VisitorsDateRangeInterface
   */
  protected $dateRange;

  /**
   * Database Service Object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $stack
   *   The request stack service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date
   *   The date service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\visitors\VisitorsDateRangeInterface $date_range
   *   The date range service.
   */
  public function __construct(
    Connection $database,
    ConfigFactoryInterface $config_factory,
    RequestStack $stack,
    RendererInterface $renderer,
    DateFormatterInterface $date,
    EntityTypeManagerInterface $entity_type_manager,
    ModuleHandlerInterface $module_handler,
    VisitorsDateRangeInterface $date_range,
  ) {

    $this->database = $database;
    $this->firstDay = $config_factory->get('system.date')->get('first_day') ?? 0;
    $this->itemsPerPage = $config_factory->get('visitors.config')->get('items_per_page') ?? 10;
    $this->page = $stack->getCurrentRequest()->query->get('page') ?? 0;
    $this->renderer = $renderer;
    $this->date = $date;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->dateRange = $date_range;
  }

  /**
   * {@inheritdoc}
   */
  public function referer(array $header) {
    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = $this->database->select('visitors', 'v')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('Drupal\Core\Database\Query\TableSortExtender');
    $query->condition('bot', 1, '<>');
    $query->addExpression('COUNT(*)', 'count');
    $query->fields('v', ['visitors_referer']);
    $this->addDateFilter($query);
    $query = $this->setReferrersCondition($query);
    $query->condition('visitors_referer', '', '<>');
    $query->groupBy('visitors_referer');
    $query->orderByHeader($header);
    $query->limit($this->itemsPerPage);

    $count_query = $this->database->select('visitors', 'v');
    $count_query->condition('bot', 1, '<>');
    $count_query->condition('visitors_referer', '', '<>');
    $count_query->addExpression('COUNT(DISTINCT visitors_referer)');
    $this->addDateFilter($count_query);
    $count_query = $this->setReferrersCondition($count_query);
    $query->setCountQuery($count_query);
    $results = $query->execute();

    $rows = [];
    $i = $this->page * $this->itemsPerPage;
    foreach ($results as $data) {

      $rows[] = [
        empty($data->visitors_referer) ? $this->t('No Referer') : $data->visitors_referer,
        $data->count,
      ];
    }
    return $rows;
  }

  /**
   * Build sql query from referer type value.
   */
  protected function setReferrersCondition($query) {
    switch ($_SESSION['referer_type']) {
      case VisitorsReportInterface::REFERER_TYPE_INTERNAL_PAGES:
        $query->condition(
          'visitors_referer',
          sprintf('%%%s%%', $_SERVER['HTTP_HOST']),
          'LIKE'
        );
        $query->condition('visitors_referer', '', '<>');
        break;

      case VisitorsReportInterface::REFERER_TYPE_EXTERNAL_PAGES:
        $query->condition(
          'visitors_referer',
          sprintf('%%%s%%', $_SERVER['HTTP_HOST']),
          'NOT LIKE'
        );
        break;

      default:
        break;
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function hitDetails($hit_id) {
    $query = $this->database->select('visitors', 'v');

    $query->fields('v');
    $query->condition('v.visitors_id', (int) $hit_id);
    /** @var object|false $hit_details */
    $hit_details = $query->execute()->fetch();

    $rows = [];

    if ($hit_details) {
      $url     = urldecode($hit_details->visitors_url);
      $referer = $hit_details->visitors_referer;
      $date    = $this->date->format($hit_details->visitors_date_time, 'large');
      $ip      = $hit_details->visitors_ip;

      $array = [
        $this->t('URL')->render()        => $url,
        $this->t('Title')->render()      => ($hit_details->visitors_title ?? ''),
        $this->t('Referer')->render()    => $referer,
        $this->t('Date')->render()       => $date,
        $this->t('Visitor')->render()    => $hit_details->visitor_id,
        $this->t('IP')->render()         => $ip,
        $this->t('User Agent')->render() => ($hit_details->visitors_user_agent ?? ''),
        $this->t('Country')->render()    => ($hit_details->location_country ?? ''),
      ];

      if ($this->moduleHandler->moduleExists('visitors_geoip')) {
        $geoip_data_array = [
          $this->t('Region')->render()          => ($hit_details->location_region ?? ''),
          $this->t('City')->render()            => ($hit_details->location_city ?? ''),
          $this->t('Latitude')->render()        => ($hit_details->location_latitude ?? ''),
          $this->t('Longitude')->render()       => ($hit_details->location_longitude ?? ''),
        ];
        $array = array_merge($array, $geoip_data_array);
      }

      foreach ($array as $key => $value) {
        $rows[] = [['data' => $key, 'header' => TRUE], $value];
      }
    }

    return $rows;
  }

  /**
   * Add date filter to the query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The query object.
   */
  protected function addDateFilter(&$query) {

    $from = $this->dateRange->getStartTimestamp();
    $to   = $this->dateRange->getEndTimestamp();

    $query->condition('visitors_date_time', [$from, $to], 'BETWEEN');
  }

}
