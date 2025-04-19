<?php

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\RebuildIpAddressForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\InputBag;

require_once __DIR__ . '/../../../../web/core/includes/form.inc';

/**
 * Rebuild IP address form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\RebuildIpAddressForm
 * @uses \Drupal\visitors\Form\RebuildIpAddressForm
 */
class RebuildIpAddressFormTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors\Form\RebuildIpAddressForm
   */
  protected $form;

  /**
   * The visitors IP address rebuilder.
   *
   * @var \Drupal\visitors\VisitorsRebuildIpAddressInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $visitorsIpAddressRebuilder;

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

    $date_formatter = $this->createMock('Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $date_formatter);

    $database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $database);

    $this->visitorsIpAddressRebuilder = $this->createMock('Drupal\visitors\VisitorsRebuildIpAddressInterface');
    $container->set('visitors.rebuild.ip_address', $this->visitorsIpAddressRebuilder);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    \Drupal::setContainer($container);

    $this->form = RebuildIpAddressForm::create($container);
  }

  /**
   * Test the form id.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('confirm_visitor_rebuild_ip_address_form', $this->form->getFormId());
  }

  /**
   * Test getQuestion.
   *
   * @covers ::getQuestion
   */
  public function testGetQuestion() {
    $this->assertEquals('Do you want to rebuild the IP Address?', (string) $this->form->getQuestion());
  }

  /**
   * Test getCancelUrl.
   *
   * @covers ::getCancelUrl
   */
  public function testGetCancelUrl() {
    $this->assertEquals('visitors.settings', $this->form->getCancelUrl()->getRouteName());
  }

  /**
   * Test batch.
   *
   * @covers ::batch
   */
  public function testBatch() {
    $ip_address = '127.0.0.1';
    $context = [];

    $this->visitorsIpAddressRebuilder->expects($this->once())
      ->method('rebuild')
      ->with($ip_address)
      ->willReturn(1);

    RebuildIpAddressForm::batch($ip_address, $context);

    $this->assertEquals($ip_address, $context['results'][0]);
    $this->assertEquals(1, $context['finished']);
  }

  /**
   * Test batchFinished.
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedSuccess() {
    $success = TRUE;

    $result = RebuildIpAddressForm::batchFinished($success, [], []);
    $this->assertNull($result);
  }

  /**
   * Test batchFinished.
   *
   * @covers ::batchFinished
   */
  public function testBatchFinishedFailure() {
    $success = FALSE;
    $operation = ['operation', ['arguments']];
    $result = RebuildIpAddressForm::batchFinished($success, [], [$operation]);
    $this->assertNull($result);
  }

  /**
   * Test buildForm.
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
   * Test submitForm.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $record = new \stdClass();
    $record->visitors_ip = '127.0.0.1';
    $this->visitorsIpAddressRebuilder->expects($this->once())
      ->method('getIpAddresses')
      ->with()
      ->willReturn([$record]);
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    $this->form->submitForm($form, $form_state);

  }

  /**
   * Test create method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $form = RebuildIpAddressForm::create($container);
    $this->assertInstanceOf('Drupal\visitors\Form\RebuildIpAddressForm', $form);
  }

  /**
   * Test construct.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $form = new RebuildIpAddressForm(
      $this->visitorsIpAddressRebuilder,
    );
    $this->assertInstanceOf('Drupal\visitors\Form\RebuildIpAddressForm', $form);
  }

}
