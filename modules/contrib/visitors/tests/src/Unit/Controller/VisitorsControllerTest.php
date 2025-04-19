<?php

namespace Drupal\Tests\visitors\Unit\Controller;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Controller\Visitors;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ServerBag;

/**
 * Unit tests for the HitDetails controller.
 *
 * @coversDefaultClass \Drupal\visitors\Controller\Visitors
 * @uses \Drupal\visitors\Controller\Visitors
 * @group visitors
 */
class VisitorsControllerTest extends UnitTestCase {

  /**
   * The Visitors controller under test.
   *
   * @var \Drupal\visitors\Controller\Visitors
   */
  protected $controller;

  /**
   * The visitors tracker.
   *
   * @var \Drupal\visitors\VisitorsTrackerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $visitorsTracker;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateTime;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * The counter.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $counter;

  /**
   * The cookie.
   *
   * @var \Drupal\visitors\VisitorsCookieInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cookie;

  /**
   * The device.
   *
   * @var \Drupal\visitors\VisitorsDeviceInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $device;

  /**
   * The location.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $location;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->visitorsTracker = $this->createMock('\Drupal\visitors\VisitorsTrackerInterface');
    $container->set('visitors.tracker', $this->visitorsTracker);

    $this->configFactory = $this->createMock('\Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->dateTime = $this->createMock('\Drupal\Component\Datetime\TimeInterface');
    $container->set('datetime.time', $this->dateTime);

    $this->logger = $this->createMock('\Psr\Log\LoggerInterface');
    $container->set('logger.channel.visitors', $this->logger);

    $this->counter = $this->createMock('\Drupal\visitors\VisitorsCounterInterface');
    $container->set('visitors.counter', $this->counter);

    $this->cookie = $this->createMock('\Drupal\visitors\VisitorsCookieInterface');
    $container->set('visitors.cookie', $this->cookie);

    $this->device = $this->createMock('\Drupal\visitors\VisitorsDeviceInterface');
    $container->set('visitors.device', $this->device);

    $this->location = $this->createMock('\Drupal\visitors\VisitorsLocationInterface');
    $container->set('visitors.location', $this->location);

    $this->settings = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    $this->configFactory->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    \Drupal::setContainer($container);

    $this->controller = Visitors::create($container);
  }

  /**
   * Tests the create() method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $controller = Visitors::create($container);
    $this->assertInstanceOf(Visitors::class, $controller);
  }

  /**
   * Tests the construct() method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {

    $controller = new Visitors(
      $this->configFactory,
      $this->dateTime,
      $this->logger,
      $this->counter,
      $this->cookie,
      $this->device,
      $this->location,
      $this->visitorsTracker,
      NULL,
    );
    $this->assertInstanceOf(Visitors::class, $controller);
  }

  /**
   * Tests the track() method.
   *
   * @covers ::doConfig
   * @covers ::doCounter
   * @covers ::doCustom
   * @covers ::doDeviceDetect
   * @covers ::doLanguage
   * @covers ::doLocalTime
   * @covers ::doLocation
   * @covers ::doPerformance
   * @covers ::doReferrer
   * @covers ::doTime
   * @covers ::doUrl
   * @covers ::doVisitorId
   * @covers ::getDefaultFields
   * @covers ::track
   */
  public function testTrack() {
    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);
    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getClientIp')
      ->willReturn('127.0.0.1');
    $query = new InputBag();
    $query->set('h', '1');
    $query->set('m', '2');
    $query->set('s', '3');
    $_cvar = [
      ['route', 'entity.node.canonical'],
      ['path', '/node/1'],
      ['server', 'localhost'],
      ['viewed', 'node:1'],
    ];
    $query->set('_cvar', json_encode($_cvar));
    $request->server = new ServerBag();
    $request->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GoogleBot/2.1; +http://www.google.com/bot.html) Chrome/W.X.Y.Z Safari/537.36');
    $request->method('getLanguages')->willReturn([]);
    $request->query = $query;

    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(0);

    $response = $this->controller->track($request);
    $this->assertSame('', $response->getContent());
  }

  /**
   * Tests the track() method.
   *
   * @covers ::doConfig
   * @covers ::doCounter
   * @covers ::doCustom
   * @covers ::doDeviceDetect
   * @covers ::doLanguage
   * @covers ::doLocalTime
   * @covers ::doLocation
   * @covers ::doPerformance
   * @covers ::doReferrer
   * @covers ::doTime
   * @covers ::doUrl
   * @covers ::doVisitorId
   * @covers ::getDefaultFields
   * @covers ::track
   */
  public function testTrackNoDeviceLibrary() {
    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(FALSE);
    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getClientIp')
      ->willReturn('127.0.0.1');
    $query = new InputBag();
    $query->set('h', '1');
    $query->set('m', '2');
    $query->set('s', '3');
    $_cvar = [
      ['route', 'entity.node.canonical'],
      ['path', '/node/1'],
      ['server', 'localhost'],
      ['viewed', 'node:1'],
    ];
    $query->set('_cvar', json_encode($_cvar));
    $request->server = new ServerBag();
    $request->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GoogleBot/2.1; +http://www.google.com/bot.html) Chrome/W.X.Y.Z Safari/537.36');
    $request->method('getLanguages')->willReturn(['en_US']);
    $request->query = $query;

    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(0);

    $this->location->expects($this->once())
      ->method('isValidCountryCode')
      ->with('US')
      ->willReturn(TRUE);
    $this->location->expects($this->once())
      ->method('getContinent')
      ->with('US')
      ->willReturn('NA');

    $response = $this->controller->track($request);
    $this->assertSame('', $response->getContent());
  }

  /**
   * Tests the track() method.
   *
   * @covers ::doConfig
   * @covers ::doCounter
   * @covers ::doCustom
   * @covers ::doDeviceDetect
   * @covers ::doLanguage
   * @covers ::doLocalTime
   * @covers ::doLocation
   * @covers ::doPerformance
   * @covers ::doReferrer
   * @covers ::doTime
   * @covers ::doUrl
   * @covers ::doVisitorId
   * @covers ::getDefaultFields
   * @covers ::track
   */
  public function testTrackBot() {
    $this->device->expects($this->once())
      ->method('hasLibrary')
      ->willReturn(TRUE);
    $this->device->expects($this->once())
      ->method('doDeviceFields')
      ->willReturnCallback(function (array &$fields, string $user_agent, ?array $server = NULL): void {
        $fields['bot'] = TRUE;
      });
    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getClientIp')
      ->willReturn('127.0.0.1');
    $query = new InputBag();
    $query->set('h', '1');
    $query->set('m', '2');
    $query->set('s', '3');
    $_cvar = [
      ['route', 'entity.node.canonical'],
      ['path', '/node/1'],
      ['server', 'localhost'],
      ['viewed', 'node:1'],
    ];
    $query->set('_cvar', json_encode($_cvar));
    $request->server = new ServerBag();
    $request->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GoogleBot/2.1; +http://www.google.com/bot.html) Chrome/W.X.Y.Z Safari/537.36');
    $request->method('getLanguages')->willReturn([]);
    $request->query = $query;

    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(-1);

    $response = $this->controller->track($request);
    $this->assertSame('', $response->getContent());
  }

  /**
   * Tests the track() method.
   *
   * @covers ::doLocalTime
   */
  public function testTrackNoLocal() {
    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getClientIp')
      ->willReturn('127.0.0.1');
    $query = new InputBag();
    $_cvar = [
      ['route', 'entity.node.canonical'],
      ['path', '/node/1'],
      ['server', 'localhost'],
      ['viewed', 'node:1'],
    ];
    $query->set('_cvar', json_encode($_cvar));
    $request->server = new ServerBag();

    $request->method('getLanguages')->willReturn([]);
    $request->query = $query;

    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(0);

    $response = $this->controller->track($request);
    $this->assertSame('', $response->getContent());
  }

  /**
   * Tests location with geoip; location null.
   *
   * @covers ::doLocation
   */
  public function testTrackGeoIp() {
    $container = \Drupal::getContainer();

    $continent = new \stdClass();
    $continent->code = 'NA';
    $country = new \stdClass();
    $country->isoCode = 'US';
    $location = new \stdClass();
    $location->latitude = 37.751;
    $location->longitude = -97.822;
    $location->metroCode = '815';
    $subdivision = new \stdClass();
    $subdivision->isoCode = 'CA';
    $postal = new \stdClass();
    $postal->code = '94043';
    $c = new \stdClass();
    $c->names = ['en' => 'Chicago'];

    $city = new \stdClass();
    $city->continent = $continent;
    $city->country = $country;
    $city->subdivisions = [$subdivision];
    $city->city = $c;
    $city->postal = $postal;
    $city->location = $location;

    $geoip = $this->createMock('\Drupal\visitors_geoip\VisitorsGeoIpInterface');
    $geoip->expects($this->once())
      ->method('city')
      ->with('127.0.0.1')
      ->willReturn($city);
    $container->set('visitors_geoip.lookup', $geoip);

    $controller = Visitors::create($container);

    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getClientIp')
      ->willReturn('127.0.0.1');

    $query = new InputBag();
    $query->set('h', '1');
    $query->set('m', '2');
    $query->set('s', '3');
    $_cvar = [
      ['route', 'entity.node.canonical'],
      ['path', '/node/1'],
      ['server', 'localhost'],
      ['viewed', 'node:1'],
    ];
    $query->set('_cvar', json_encode($_cvar));
    $request->server = new ServerBag();

    $request->method('getLanguages')->willReturn([]);
    $request->query = $query;

    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(0);

    $response = $controller->track($request);
    $this->assertSame('', $response->getContent());
  }

  /**
   * Tests location with geoip; location null.
   *
   * @covers ::doLocation
   */
  public function testTrackGeoIpNull() {
    $container = \Drupal::getContainer();

    $geoip = $this->createMock('\Drupal\visitors_geoip\VisitorsGeoIpInterface');
    $container->set('visitors_geoip.lookup', $geoip);

    $controller = Visitors::create($container);

    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->expects($this->once())
      ->method('getClientIp')
      ->willReturn('127.0.0.1');

    $query = new InputBag();
    $query->set('h', '1');
    $query->set('m', '2');
    $query->set('s', '3');
    $_cvar = [
      ['route', 'entity.node.canonical'],
      ['path', '/node/1'],
      ['server', 'localhost'],
      ['viewed', 'node:1'],
    ];
    $query->set('_cvar', json_encode($_cvar));
    $request->server = new ServerBag();

    $request->method('getLanguages')->willReturn([]);
    $request->query = $query;

    $this->settings->expects($this->once())
      ->method('get')
      ->with('bot_retention_log')
      ->willReturn(0);

    $response = $controller->track($request);
    $this->assertSame('', $response->getContent());
  }

}
