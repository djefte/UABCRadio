<?php

namespace Drupal\Tests\visitors\Unit\Controller;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Controller\PerformanceController;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit tests for the performance controller.
 *
 * @coversDefaultClass \Drupal\visitors\Controller\PerformanceController
 * @uses \Drupal\visitors\Controller\PerformanceController
 * @group visitors
 */
class PerformanceControllerTest extends UnitTestCase {

  /**
   * The StatisticsMigrate controller under test.
   *
   * @var \Drupal\visitors\Controller\PerformanceController
   */
  protected $controller;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $formBuilder;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->formBuilder = $this->createMock('Drupal\Core\Form\FormBuilderInterface');
    $container->set('form_builder', $this->formBuilder);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->urlGenerator = $this->createMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $container->set('url_generator', $this->urlGenerator);

    \Drupal::setContainer($container);

    $this->controller = PerformanceController::create($container);
  }

  /**
   * Tests the migrate() method.
   *
   * @covers ::migrate
   */
  public function testMigrateStatisticsNotExist() {

    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(FALSE);

    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->willReturn('admin/config/system/visitors');

    ob_start();
    $this->controller->migrate();
    $output = ob_get_clean();

    $this->assertNotEmpty($output);
  }

  /**
   * Tests the migrate() method.
   *
   * @covers ::migrate
   */
  public function testMigrateStatisticsExist() {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $schema->expects($this->once())
      ->method('tableExists')
      ->with('visitors_performance')
      ->willReturn(TRUE);

    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);

    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with('Drupal\visitors\Form\PerformanceForm')
      ->willReturn([]);

    $this->controller->migrate();
  }

  /**
   * Tests the create() method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $controller = PerformanceController::create($container);
    $this->assertInstanceOf(PerformanceController::class, $controller);
  }

  /**
   * Tests the construct() method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {

    $controller = new PerformanceController($this->database, $this->formBuilder, $this->messenger);
    $this->assertInstanceOf(PerformanceController::class, $controller);
  }

}
