<?php

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\PerformanceForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../../web/core/includes/form.inc';

/**
 * Rebuild IP address form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\PerformanceForm
 * @uses \Drupal\visitors\Form\PerformanceForm
 */
class PerformanceFormTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors\Form\PerformanceForm
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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

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

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $date_time = $this->createMock('Drupal\Component\Datetime\Time');
    $container->set('datetime.time', $date_time);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->state = $this->createMock('Drupal\Core\State\StateInterface');
    $container->set('state', $this->state);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    \Drupal::setContainer($container);

    $this->form = PerformanceForm::create($container);
  }

  /**
   * Test the form id.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('visitors_performance_form', $this->form->getFormId());
  }

  /**
   * Test getQuestion.
   *
   * @covers ::getQuestion
   */
  public function testGetQuestion() {
    $this->assertEquals('This will migrate the performance data from the old table. Are you sure?', (string) $this->form->getQuestion());
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
  public function testBatchFirst() {
    $context = [];

    $statement = $this->createMock('Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchField')
      ->willReturn(0);
    $select = $this->createMock('Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('fields')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('join')
      ->with('visitors', 'v', 'vp.visitors_id = v.visitors_id')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.pf_total', NULL, 'IS NOT NULL')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('countQuery')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);

    PerformanceForm::batch($context);
    $this->assertEquals(0, $context['sandbox']['total']);
  }

  /**
   * Test batch.
   *
   * @covers ::batch
   */
  public function testBatch() {
    $context = [
      'sandbox' => [
        'total' => 10,
        'current' => 9,
      ],
    ];
    $row = new \stdClass();
    $row->id = 1;
    $row->visitors_id = 1;
    $row->network = 1;
    $row->server = 1;
    $row->transfer = 1;
    $row->dom_processing = 1;
    $row->dom_complete = 1;
    $row->on_load = 1;
    $row->total = 6;
    $statement = $this->createMock('Drupal\Core\Database\StatementInterface');
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn([$row]);
    $select = $this->createMock('Drupal\Core\Database\Query\SelectInterface');
    $select->expects($this->once())
      ->method('fields')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('join')
      ->with('visitors', 'v', 'vp.visitors_id = v.visitors_id')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('condition')
      ->with('v.pf_total', NULL, 'IS NOT NULL')
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('range')
      ->with(0, 1000)
      ->willReturnSelf();
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    $update_statement = $this->createMock('Drupal\Core\Database\StatementInterface');
    $update = $this->createMock('Drupal\Core\Database\Query\Update');
    $update->expects($this->once())
      ->method('fields')
      ->with([
        'pf_total' => 6,
        'pf_network' => 1,
        'pf_server' => 1,
        'pf_transfer' => 1,
        'pf_dom_processing' => 1,
        'pf_dom_complete' => 1,
        'pf_on_load' => 1,
      ])
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('condition')
      ->with('visitors_id', 1)
      ->willReturnSelf();
    $update->expects($this->once())
      ->method('execute')
      ->willReturn($update_statement);

    $delete_statement = $this->createMock('Drupal\Core\Database\StatementInterface');
    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->once())
      ->method('condition')
      ->with('visitors_id', 1)
      ->willReturnSelf();
    $delete->expects($this->once())
      ->method('execute')
      ->willReturn($delete_statement);

    $this->database->expects($this->once())
      ->method('select')
      ->willReturn($select);

    $this->database->expects($this->once())
      ->method('delete')
      ->willReturn($delete);

    $this->database->expects($this->once())
      ->method('update')
      ->willReturn($update);

    PerformanceForm::batch($context);
    $this->assertEquals(10, $context['sandbox']['total']);
  }

  /**
   * Test dropTable.
   *
   * @covers ::dropTable
   */
  public function testDropTable() {
    $schema = $this->createMock('Drupal\Core\Database\Schema');
    $this->database->expects($this->once())
      ->method('schema')
      ->willReturn($schema);
    $schema->expects($this->once())
      ->method('dropTable')
      ->with('visitors_performance');
    PerformanceForm::dropTable();
  }

  /**
   * Test submitForm.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    $this->form->submitForm($form, $form_state);
    $this->assertEmpty($form);
  }

}
