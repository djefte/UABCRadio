<?php

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\RebuildDeviceForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;

require_once __DIR__ . '/../../../../web/core/includes/form.inc';

/**
 * Rebuild Device form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\RebuildDeviceForm
 * @uses \Drupal\visitors\Form\RebuildDeviceForm
 */
class RebuildDeviceFormTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors\Form\RebuildDeviceForm
   */
  protected $form;

  /**
   * The visitors device rebuilder.
   *
   * @var \Drupal\visitors\VisitorsDeviceInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $visitorsDeviceRebuilder;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->visitorsDeviceRebuilder = $this->createMock('Drupal\visitors\VisitorsDeviceInterface');
    $container->set('visitors.device', $this->visitorsDeviceRebuilder);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    \Drupal::setContainer($container);

    $this->form = RebuildDeviceForm::create($container);
  }

  /**
   * Test the form id.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('confirm_visitor_rebuild_device_form', $this->form->getFormId());
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
    $this->assertArrayHasKey('#attributes', $form);
    $this->assertArrayHasKey('#theme', $form);
    $this->assertArrayHasKey('#title', $form);
    $this->assertArrayHasKey('actions', $form);
    $this->assertArrayHasKey('confirm', $form);
    $this->assertArrayHasKey('description', $form);
    $this->assertArrayHasKey('drush', $form);
    $this->assertArrayHasKey('slow', $form);
  }

  /**
   * Test the submit form.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {

    $record = new \stdClass();

    $this->visitorsDeviceRebuilder->expects($this->once())
      ->method('getUniqueUserAgents')
      ->willReturn([$record]);

    $query = new InputBag([]);
    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request->query = $query;
    $this->requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    $this->form->submitForm($form, $form_state);
  }

  /**
   * Test the form batch().
   *
   * @covers ::batch
   */
  public function testBatch() {
    $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';
    $this->visitorsDeviceRebuilder->expects($this->once())
      ->method('bulkUpdate')
      ->with($user_agent)
      ->willReturn(1);
    $context = [];
    RebuildDeviceForm::batch($user_agent, $context);
    $this->assertEquals($user_agent, $context['results'][0]);
    $this->assertEquals(1, $context['finished']);
  }

  /**
   * Test the form batchFinished().
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedSuccess() {
    $result = RebuildDeviceForm::batchFinished(TRUE, [], []);
    $this->assertNull($result);
  }

  /**
   * Test the form batchFinished().
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedFailure() {
    $operation = ['operation', ['arguments']];
    $result = RebuildDeviceForm::batchFinished(FALSE, [], [$operation]);
    $this->assertNull($result);
  }

  /**
   * Test the form create().
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $form = RebuildDeviceForm::create($container);
    $this->assertInstanceOf('Drupal\visitors\Form\RebuildDeviceForm', $form);
  }

  /**
   * Test the form construct().
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $form = new RebuildDeviceForm($this->visitorsDeviceRebuilder);
    $this->assertInstanceOf('Drupal\visitors\Form\RebuildDeviceForm', $form);
  }

  /**
   * Test the form getCancelUrl().
   *
   * @covers ::getCancelUrl
   */
  public function testGetCancelUrl() {
    $url = $this->form->getCancelUrl();
    $this->assertEquals('route:visitors.settings', $url->toUriString());
  }

  /**
   * Test the form getQuestion().
   *
   * @covers ::getQuestion
   */
  public function testGetQuestion() {
    $this->assertEquals('Do you want to rebuild the devices?', (string) $this->form->getQuestion());
  }

}
