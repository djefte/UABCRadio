<?php

namespace Drupal\Tests\visitors_geoip\Unit\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors_geoip\Form\RebuildLocationForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * Tests the Visitors Settings Form.
 *
 * @coversDefaultClass \Drupal\visitors_geoip\Form\RebuildLocationForm
 *
 * @group visitors_geoip
 */
class RebuildLocationFormTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors_geoip\Form\RebuildLocationForm
   */
  protected $form;

  /**
   * The rebuild location service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $rebuildLocationService;

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
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->rebuildLocationService = $this->createMock('Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface');
    $container->set('visitors_geoip.rebuild.location', $this->rebuildLocationService);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    \Drupal::setContainer($container);

    $this->form = RebuildLocationForm::create($container);

  }

  /**
   * Tests the create() method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $form = RebuildLocationForm::create($container);
    $this->assertInstanceOf(RebuildLocationForm::class, $form);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstruct(): void {
    $form = new RebuildLocationForm($this->rebuildLocationService);
    $this->assertInstanceOf(RebuildLocationForm::class, $form);
  }

  /**
   * Tests the buildForm() method.
   *
   * @covers ::buildForm
   */
  public function testBuildForm() {
    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $this->requestStack
      ->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $query = new InputBag();
    $request->query = $query;

    $form_state = $this->createMock(FormStateInterface::class);
    $form = $this->form->buildForm([], $form_state);

    $this->assertCount(8, $form);
    $this->assertArrayHasKey('slow', $form);
    $this->assertArrayHasKey('drush', $form);
  }

  /**
   * Tests the submitForm() method.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $this->rebuildLocationService
      ->expects($this->once())
      ->method('getLocations')
      ->willReturn([(object) []]);

    $form = [];
    $form_state = $this->createMock(FormStateInterface::class);
    $this->form->submitForm($form, $form_state);
  }

  /**
   * Tests the getFormId() method.
   *
   * @covers ::getFormId
   */
  public function testGetFormId(): void {
    $this->assertEquals('confirm_visitor_rebuild_location_form', $this->form->getFormId());
  }

  /**
   * Tests the getQuestion() method.
   *
   * @covers ::getQuestion
   */
  public function testGetQuestion() {
    $this->assertEquals('Do you want to rebuild missing locations?', (string) $this->form->getQuestion());
  }

  /**
   * Test getCancelUrl().
   *
   * @covers ::getCancelUrl
   */
  public function testGetCancelUrl() {
    $this->assertEquals('visitors_geoip.settings', $this->form->getCancelUrl()->getRouteName());
  }

  /**
   * Tests the batchFinished() method.
   *
   * @covers ::batchFinished
   */
  public function testBatchFinished() {
    $success = TRUE;
    $results = ['result1', 'result2'];
    $operations = [['operation1', ['arg1', 'arg2']]];

    $this->state->expects($this->once())
      ->method('delete')
      ->with('visitors_geoip.rebuild.location');
    $this->messenger->expects($this->once())
      ->method('addMessage');

    $this->form::batchFinished($success, $results, $operations);

  }

  /**
   * Tests the batchFinished() method.
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedFalse() {
    $success = FALSE;
    $results = ['result1', 'result2'];
    $operations = [['operation1', ['arg1', 'arg2']]];

    $this->messenger->expects($this->once())
      ->method('addMessage');

    $this->form::batchFinished($success, $results, $operations);

  }

  /**
   * Tests the batch() method.
   *
   * @covers ::batch
   */
  public function testBatch(): void {
    $ip_address = '127.0.0.1';
    $context = [];
    $this->rebuildLocationService
      ->expects($this->once())
      ->method('rebuild')
      ->with($ip_address)
      ->willReturn(TRUE);

    $this->form::batch($ip_address, $context);
    $this->assertArrayHasKey('results', $context);
    $this->assertEquals([$ip_address], $context['results']);
    $this->assertEquals(1, $context['finished']);
  }

}
