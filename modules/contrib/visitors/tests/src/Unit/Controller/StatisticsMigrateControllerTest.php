<?php

namespace Drupal\Tests\visitors\Unit\Controller;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Controller\StatisticsMigrateController;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit tests for the HitDetails controller.
 *
 * @coversDefaultClass \Drupal\visitors\Controller\StatisticsMigrateController
 * @uses \Drupal\visitors\Controller\StatisticsMigrateController
 * @group visitors
 */
class StatisticsMigrateControllerTest extends UnitTestCase {

  /**
   * The StatisticsMigrate controller under test.
   *
   * @var \Drupal\visitors\Controller\StatisticsMigrateController
   */
  protected $controller;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

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

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->formBuilder = $this->createMock('Drupal\Core\Form\FormBuilderInterface');
    $container->set('form_builder', $this->formBuilder);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->urlGenerator = $this->createMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $container->set('url_generator', $this->urlGenerator);

    \Drupal::setContainer($container);

    $this->controller = StatisticsMigrateController::create($container);
  }

  /**
   * Tests the migrate() method.
   *
   * @covers ::migrate
   */
  public function testMigrateStatisticsNotExist() {
    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('statistics')
      ->willReturn(FALSE);

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
    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('statistics')
      ->willReturn(TRUE);

    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with('Drupal\visitors\Form\StatisticsMigrateForm')
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
    $controller = StatisticsMigrateController::create($container);
    $this->assertInstanceOf(StatisticsMigrateController::class, $controller);
  }

  /**
   * Tests the construct() method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {

    $controller = new StatisticsMigrateController($this->moduleHandler, $this->formBuilder, $this->messenger);
    $this->assertInstanceOf(StatisticsMigrateController::class, $controller);
  }

}
