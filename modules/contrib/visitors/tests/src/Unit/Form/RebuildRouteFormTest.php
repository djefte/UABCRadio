<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\RebuildRouteForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;

require_once __DIR__ . '/../../../../web/core/includes/form.inc';

/**
 * Rebuild Route form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\RebuildRouteForm
 * @uses \Drupal\visitors\Form\RebuildRouteForm
 */
class RebuildRouteFormTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors\Form\RebuildRouteForm
   */
  protected $form;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The visitors route rebuilder.
   *
   * @var \Drupal\visitors\VisitorsRebuildRouteInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $visitorsRouteRebuilder;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $date_formatter = $this->createMock('Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $date_formatter);

    $database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $database);

    $this->languageManager = $this->createMock('Drupal\Core\Language\LanguageManagerInterface');
    $container->set('language_manager', $this->languageManager);

    $this->visitorsRouteRebuilder = $this->createMock('Drupal\visitors\VisitorsRebuildRouteInterface');
    $container->set('visitors.rebuild.route', $this->visitorsRouteRebuilder);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $state);

    \Drupal::setContainer($container);

    $this->form = RebuildRouteForm::create($container);
  }

  /**
   * Test the form id.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('confirm_visitor_rebuild_route_form', $this->form->getFormId());
  }

  /**
   * Test the form.
   *
   * @covers ::getCancelUrl
   */
  public function testGetCancelUrl() {
    $this->assertEquals('visitors.settings', $this->form->getCancelUrl()->getRouteName());
  }

  /**
   * Test the form.
   *
   * @covers ::getQuestion
   */
  public function testGetQuestion() {
    $this->assertEquals('Do you want to rebuild the routes?', (string) $this->form->getQuestion());
  }

  /**
   * Test the form.
   *
   * @covers ::batch
   */
  public function testBatch() {
    $path = '/node/1';
    $this->visitorsRouteRebuilder->expects($this->once())
      ->method('rebuild')
      ->with($path)
      ->willReturn(1);
    $context = [];
    $this->form->batch($path, $context);
    $this->assertEquals($path, $context['results'][0]);
    $this->assertEquals(1, $context['finished']);
  }

  /**
   * Test the form.
   *
   * @covers ::buildForm
   */
  public function testBuildForm() {
    $query = new InputBag([]);
    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request->query = $query;
    $this->requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($request);
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->form->buildForm([], $form_state);
    $this->assertIsArray($form);
    $this->assertCount(8, $form);
    $this->assertArrayHasKey('slow', $form);
    $this->assertArrayHasKey('drush', $form);
    $this->assertArrayHasKey('#title', $form);
    $this->assertArrayHasKey('#attributes', $form);
    $this->assertArrayHasKey('description', $form);
    $this->assertArrayHasKey('confirm', $form);
    $this->assertArrayHasKey('actions', $form);
    $this->assertArrayHasKey('#theme', $form);
  }

  /**
   * Test the form.
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedSuccess() {
    $result = $this->form->batchFinished(TRUE, [], []);
    $this->assertNull($result);
  }

  /**
   * Test the form.
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedFailure() {
    $error = [
      0 => 'error-operation',
      1 => ['arg1', 'arg2'],
    ];
    $result = $this->form->batchFinished(FALSE, [], [$error]);
    $this->assertNull($result);
  }

  /**
   * Tests submitForm.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $record = new \stdClass();
    $record->visitors_path = '/node/1';
    $this->visitorsRouteRebuilder->expects($this->once())
      ->method('getPaths')
      ->willReturn([$record]);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    $this->form->submitForm($form, $form_state);
  }

  /**
   * Tests create.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $form = RebuildRouteForm::create($container);
    $this->assertInstanceOf('Drupal\visitors\Form\RebuildRouteForm', $form);
  }

  /**
   * Tests construct.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $form = new RebuildRouteForm($this->visitorsRouteRebuilder);
    $this->assertInstanceOf(RebuildRouteForm::class, $form);
  }

}
