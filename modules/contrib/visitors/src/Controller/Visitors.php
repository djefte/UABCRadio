<?php

namespace Drupal\visitors\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\visitors\VisitorsTrackerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\visitors_geoip\VisitorsGeoIpInterface;
use Drupal\visitors\VisitorsCounterInterface;
use Drupal\visitors\VisitorsCookieInterface;
use Drupal\visitors\VisitorsDeviceInterface;
use Drupal\visitors\VisitorsLocationInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;

/**
 * Visitors tracking controller.
 */
final class Visitors extends ControllerBase {

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The visitors settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $settings;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The counter service.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface
   */
  protected $counter;

  /**
   * The cookie service.
   *
   * @var \Drupal\visitors\VisitorsCookieInterface
   */
  protected $cookie;

  /**
   * The device service.
   *
   * @var \Drupal\visitors\VisitorsDeviceInterface
   */
  protected $device;

  /**
   * The location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface
   */
  protected $location;

  /**
   * The tracker service.
   *
   * @var \Drupal\visitors\VisitorsTrackerInterface
   */
  protected $tracker;

  /**
   * The geoip service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpInterface|null
   */
  protected $geoip;

  /**
   * Visitor tracker.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\visitors\VisitorsCounterInterface $counter
   *   The counter service.
   * @param \Drupal\visitors\VisitorsCookieInterface $cookie
   *   The cookie service.
   * @param \Drupal\visitors\VisitorsDeviceInterface $device
   *   The device service.
   * @param \Drupal\visitors\VisitorsLocationInterface $location
   *   The location service.
   * @param \Drupal\visitors\VisitorsTrackerInterface $tracker
   *   The date service.
   * @param \Drupal\visitors_geoip\VisitorsGeoIpInterface|null $geoip
   *   The geoip service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TimeInterface $time,
    LoggerInterface $logger,
    VisitorsCounterInterface $counter,
    VisitorsCookieInterface $cookie,
    VisitorsDeviceInterface $device,
    VisitorsLocationInterface $location,
    VisitorsTrackerInterface $tracker,
    ?VisitorsGeoIpInterface $geoip = NULL,
  ) {

    $this->settings = $config_factory->get('visitors.config');

    $this->time = $time;
    $this->logger = $logger;
    $this->counter = $counter;
    $this->cookie = $cookie;
    $this->device = $device;
    $this->location = $location;
    $this->tracker = $tracker;
    $this->geoip = $geoip;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): Visitors {
    return new self(
      $container->get('config.factory'),
      $container->get('datetime.time'),
      $container->get('logger.channel.visitors'),
      $container->get('visitors.counter'),
      $container->get('visitors.cookie'),
      $container->get('visitors.device'),
      $container->get('visitors.location'),
      $container->get('visitors.tracker'),
      $container->get('visitors_geoip.lookup', ContainerInterface::NULL_ON_INVALID_REFERENCE),
    );

  }

  /**
   * Tracks visits.
   */
  public function track(Request $request): Response {
    $response = new Response();
    $response->setStatusCode(Response::HTTP_NO_CONTENT);

    $server = $request->server;
    $query = $request->query->all();

    $fields = $this->getDefaultFields();

    $ip = $request->getClientIp();
    $fields['visitors_ip'] = $ip;
    $fields['visitors_uid'] = $query['uid'] ?? 0;
    $fields['visitors_title'] = $query['action_name'] ?? '';
    $fields['visitors_user_agent'] = $server->get('HTTP_USER_AGENT', '');

    $bot_retention_log = $this->settings->get('bot_retention_log');
    $discard_bot = ($bot_retention_log == -1);

    $this->doDeviceDetect($fields, $server);
    if ($discard_bot && $fields['bot']) {
      return $response;
    }

    $this->doVisitorId($fields, $query);
    $this->doUrl($fields, $query);
    $this->doReferrer($fields, $query);

    $cvar = $query['_cvar'] ?? NULL;
    $this->doCustom($fields, $cvar);

    $this->doCounter($fields, $cvar);

    $this->doConfig($fields, $query);
    $this->doPerformance($fields, $query);

    $this->doLocalTime($fields, $query);
    $this->doTime($fields);

    $languages = $request->getLanguages() ?? [];
    $this->doLanguage($fields, $languages);
    $this->doLocation($fields, $ip, $languages);

    // Write fields to database.
    $this->tracker->writeLog($fields);

    return $response;
  }

  /**
   * Get the default fields.
   *
   * @return array
   *   The default fields.
   */
  protected function getDefaultFields(): array {
    $fields = [
      'bot' => 0,
    ];

    return $fields;
  }

  /**
   * Detects the visitor url.
   *
   * @param string[] $fields
   *   The fields array.
   * @param string[] $query
   *   The query array.
   */
  protected function doUrl(array &$fields, array $query) {
    $url = $query['url'] ?? '';
    $fields['visitors_url'] = $url;
  }

  /**
   * Detects the visitor id.
   *
   * @param string[] $fields
   *   The fields array.
   * @param string[] $query
   *   The query array.
   */
  protected function doVisitorId(array &$fields, array $query) {
    $visitor_id = $query['_id'] ?? $this->cookie->getId();
    $fields['visitor_id'] = $visitor_id;
  }

  /**
   * Detects the referrer.
   *
   * @param string[] $fields
   *   The fields array.
   * @param string[] $query
   *   The query array.
   */
  protected function doReferrer(array &$fields, array $query) {
    $referrer = $query['urlref'] ?? '';
    $fields['visitors_referer'] = $referrer;
  }

  /**
   * Detects the device.
   *
   * @param string[] $fields
   *   The fields array.
   * @param \Symfony\Component\HttpFoundation\ServerBag $server
   *   The server array.
   */
  protected function doDeviceDetect(array &$fields, ServerBag $server) {
    if (!$this->device->hasLibrary()) {
      return NULL;
    }

    $user_agent = $server->get('HTTP_USER_AGENT', '');
    $this->device->doDeviceFields($fields, $user_agent, $server->all());

  }

  /**
   * Set the fields with data in the custom variable.
   */
  protected function doCustom(array &$fields, $cvar = NULL) {
    $path = '';
    $route = '';
    $server = NULL;

    if (!is_null($cvar)) {
      $custom = json_decode($cvar);
      foreach ($custom as $c) {
        if ($c[0] == 'path') {
          $path = $c[1];
        }
        if ($c[0] == 'route') {
          $route = $c[1];
        }
        if ($c[0] == 'server') {
          $server = $c[1];
        }
      }
    }

    $fields['visitors_path'] = $path;
    $fields['route'] = $route;
    $fields['server'] = $server;

  }

  /**
   * Record the view of the entity.
   */
  protected function doCounter(array &$fields, $cvar = NULL) {

    $viewed = NULL;
    if (!is_null($cvar)) {
      $custom = json_decode($cvar);
      foreach ($custom as $c) {
        if ($c[0] == 'viewed') {
          $viewed = $c[1];
        }
      }
    }

    if (!is_null($viewed)) {
      [$type, $id] = explode(':', $viewed);
      $this->counter->recordView($type, $id);
    }
  }

  /**
   * Set the configuration fields.
   */
  protected function doConfig(array &$fields, array $query) {

    $fields['config_resolution']   = $query['res'] ?? NULL;
    $fields['config_pdf']          = $query['pdf'] ?? NULL;
    $fields['config_flash']        = $query['fla'] ?? NULL;
    $fields['config_java']         = $query['java'] ?? NULL;
    $fields['config_quicktime']    = $query['qt'] ?? NULL;
    $fields['config_realplayer']   = $query['realp'] ?? NULL;
    $fields['config_windowsmedia'] = $query['wma'] ?? NULL;
    $fields['config_silverlight']  = $query['ag'] ?? NULL;
    $fields['config_cookie']       = $query['cookie'] ?? NULL;
  }

  /**
   * Set the performance fields.
   */
  protected function doPerformance(array &$fields, array $query) {
    $fields['pf_network']        = $query['pf_net'] ?? NULL;
    $fields['pf_server']         = $query['pf_srv'] ?? NULL;
    $fields['pf_transfer']       = $query['pf_tfr'] ?? NULL;
    $fields['pf_dom_processing'] = $query['pf_dm1'] ?? NULL;
    $fields['pf_dom_complete']   = $query['pf_dm2'] ?? NULL;
    $fields['pf_on_load']        = $query['pf_onl'] ?? NULL;

    $fields['pf_total'] = ($fields['pf_network'] ?? 0)
    + ($fields['pf_server'] ?? 0)
    + ($fields['pf_transfer'] ?? 0)
    + ($fields['pf_dom_processing'] ?? 0)
    + ($fields['pf_dom_complete'] ?? 0)
    + ($fields['pf_on_load'] ?? 0);
  }

  /**
   * Set the visitor's local time field.
   */
  protected function doLocalTime(array &$fields, array $query) {
    $hours = $query['h'] ?? NULL;
    $minutes = $query['m'] ?? NULL;
    $seconds = $query['s'] ?? NULL;

    $has_null = is_null($hours) || is_null($minutes) || is_null($seconds);
    if ($has_null) {
      return NULL;
    }

    $time = $hours * 3600 + $minutes * 60 + $seconds;

    $fields['visitor_localtime'] = $time;
  }

  /**
   * Set the server time field.
   */
  protected function doTime(array &$fields) {
    $fields['visitors_date_time'] = $this->time->getRequestTime();
  }

  /**
   * Set the language fields.
   */
  protected function doLanguage(array &$fields, array $languages) {
    if (empty($languages)) {
      return NULL;
    }

    $language = $languages[0] ?? '';
    $lang = explode('_', $language);
    $fields['language'] = $lang[0];
  }

  /**
   * Set the location fields.
   */
  protected function doLocation(array &$fields, $ip_address, $languages) {
    if (!empty($languages)) {
      $language = $languages[0] ?? '';
      $lang = explode('_', $language);
      $country_code = strtoupper($lang[1] ?? '');
      if ($this->location->isValidCountryCode($country_code)) {
        $fields['location_country'] = $country_code;
        $fields['location_continent'] = $this->location->getContinent($country_code);
      }
    }

    if (!$this->geoip) {
      return NULL;
    }

    /** @var \GeoIp2\Model\City|null $location */
    $location = $this->geoip->city($ip_address);
    if (!$location) {
      return NULL;
    }

    $fields['location_continent'] = $location->continent->code;
    $fields['location_country']   = $location->country->isoCode;
    $fields['location_region']    = $location->subdivisions[0]->isoCode;
    $fields['location_city']      = $location->city->names['en'];
    $fields['location_latitude']  = $location->location->latitude;
    $fields['location_longitude'] = $location->location->longitude;

  }

}
