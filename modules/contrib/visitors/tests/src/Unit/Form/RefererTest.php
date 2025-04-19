<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\Referer;
use Drupal\visitors\VisitorsReportInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Referer form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\Referer
 * @uses \Drupal\visitors\Form\Referer
 * @uses \Drupal\visitors\Form\DateFilter
 */
class RefererTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors\Form\Referer
   */
  protected $form;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\Service\DateRangeService|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateRangeService;

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

    $this->dateRangeService = $this->createMock('Drupal\visitors\Service\DateRangeService');
    $container->set('visitors.date_range', $this->dateRangeService);

    \Drupal::setContainer($container);

    $this->form = Referer::create($container);
  }

  /**
   * Test the form id.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('visitors_referer_form', $this->form->getFormId());
  }

  /**
   * Test the form.
   *
   * @covers ::buildForm
   * @covers ::setSessionRefererType
   */
  public function testBuildForm() {

    $current_language = $this->createMock('Drupal\Core\Language\LanguageInterface');
    $current_language->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->willReturn($current_language);

    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->form->buildForm([], $form_state);

    $this->assertIsArray($form);
    $this->assertCount(3, $form);
    $this->assertArrayHasKey('visitors_date_filter', $form);
    $this->assertArrayHasKey('visitors_referer', $form);
    $this->assertArrayHasKey('submit', $form);
  }

  /**
   * Test the default referer.
   *
   * @covers ::buildForm
   * @covers ::setSessionRefererType
   */
  public function testBuildFormReferer() {
    unset($_SESSION['referer_type']);
    $current_language = $this->createMock('Drupal\Core\Language\LanguageInterface');
    $current_language->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->willReturn($current_language);

    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->form->buildForm([], $form_state);

    $this->assertIsArray($form);
    $this->assertCount(3, $form);
    $this->assertArrayHasKey('visitors_date_filter', $form);
    $this->assertArrayHasKey('visitors_referer', $form);
    $this->assertArrayHasKey('submit', $form);

    $this->assertEquals($_SESSION['referer_type'], VisitorsReportInterface::REFERER_TYPE_EXTERNAL_PAGES);
  }

  /**
   * Tests submitForm.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $date_to = $this->createMock('Drupal\Core\Datetime\DrupalDateTime');

    $date_from = $this->createMock('Drupal\Core\Datetime\DrupalDateTime');

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getValues')
      ->willReturn([
        'referer_type' => 'internal',
      ]);
    $form_state->expects($this->exactly(3))
      ->method('getValue')
      ->willReturn($date_to, $date_from, 'period');

    $this->form->submitForm($form, $form_state);
  }

}
