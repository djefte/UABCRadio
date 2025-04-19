<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\DateFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\DateFilter
 * @uses \Drupal\visitors\Form\DateFilter
 */
class DateFilterTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors\Form\DateFilter
   */
  protected $form;

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
   * The database.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The date time.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateTime;

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\Service\DateRangeService|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateRangeService;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->dateFormatter = $this->createMock('Drupal\Core\Datetime\DateFormatterInterface');
    $container->set('date.formatter', $this->dateFormatter);

    $this->dateTime = $this->createMock('Drupal\Component\Datetime\TimeInterface');
    $container->set('datetime.time', $this->dateTime);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->dateRangeService = $this->createMock('Drupal\visitors\Service\DateRangeService');
    $container->set('visitors.date_range', $this->dateRangeService);

    $language = $this->createMock('Drupal\Core\Language\LanguageInterface');
    $language->method('getId')->willReturn('en');
    $language_manager = $this->createMock('Drupal\Core\Language\LanguageManagerInterface');
    $language_manager->method('getCurrentLanguage')->willReturn($language);
    $container->set('language_manager', $language_manager);

    \Drupal::setContainer($container);

    $this->form = DateFilter::create($container);
  }

  /**
   * Test the form id.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('visitors_date_filter_form', $this->form->getFormId());
  }

  /**
   * Test the form build.
   *
   * @covers ::buildForm
   */
  public function testBuildForm() {
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->form->buildForm([], $form_state);
    $this->assertIsArray($form);
    $this->assertCount(1, $form);
    $this->assertIsArray($form['visitors_date_filter']);
    $this->assertCount(8, $form['visitors_date_filter']);

    $this->assertArrayHasKey('visitors_date_filter', $form);
    $this->assertArrayHasKey('submit', $form['visitors_date_filter']);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $form = DateFilter::create($container);
    $this->assertInstanceOf('Drupal\visitors\Form\DateFilter', $form);
  }

  /**
   * Test __construct.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $form = new DateFilter(
      $this->dateFormatter,
      $this->dateRangeService,
    );
    $this->assertInstanceOf('Drupal\visitors\Form\DateFilter', $form);
  }

  /**
   * Test submitForm.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->exactly(3))
      ->method('getValue')
      ->willReturnMap([
        ['from', NULL, '2020-01-31'],
        ['to', NULL, '2020-01-31'],
        ['period', NULL, 'day'],
      ]);

    $this->dateRangeService->expects($this->once())
      ->method('setPeriodAndDates')
      ->with('day', '2020-01-31', '2020-01-31');

    $this->form->submitForm($form, $form_state);
  }

}
