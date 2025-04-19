<?php

namespace Drupal\Tests\visitors_geoip\Unit\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors_geoip\Form\SettingsForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the Visitors Settings Form.
 *
 * @coversDefaultClass \Drupal\visitors_geoip\Form\SettingsForm
 *
 * @group visitors_geoip
 */
class SettingsFormTest extends UnitTestCase {

  /**
   * The form.
   *
   * @var \Drupal\visitors_geoip\Form\SettingsForm
   */
  protected $form;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('\Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $typed_config_manager = NULL;
    if (version_compare(\Drupal::VERSION, '10.2.0', '>=')) {
      $typed_config_manager = $this->createMock('\Drupal\Core\Config\TypedConfigManagerInterface');
      $container->set('config.typed', $typed_config_manager);
    }

    $this->messenger = $this->createMock('\Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    \Drupal::setContainer($container);

    $this->settings = $this->createMock('\Drupal\Core\Config\Config');

    $this->form = SettingsForm::create($container);
    $this->form->setStringTranslation($this->getStringTranslationStub());

  }

  /**
   * Tests the buildForm() method.
   *
   * @covers ::buildForm
   */
  public function testBuildForm() {

    $this->settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['geoip_path', 'path/to/geoip/database'],
        ['license', 'secret-key-provided-by-maxmind'],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors_geoip.settings')
      ->willReturn($this->settings);

    // Create a mock form state object.
    $form_state = $this->createMock(FormStateInterface::class);

    // Build the form.
    $form = $this->form->buildForm([], $form_state);

    // Assert that the form array contains the expected elements.
    $this->assertArrayHasKey('geoip_path', $form);
    $this->assertEquals('textfield', $form['geoip_path']['#type']);
    $this->assertEquals('GeoIP Database path', $form['geoip_path']['#title']);
    $this->assertEquals('path/to/geoip/database', $form['geoip_path']['#default_value']);

    $this->assertArrayHasKey('license', $form);
    $this->assertEquals('secret-key-provided-by-maxmind', $form['license']['#default_value']);
  }

  /**
   * Tests the submitForm() method.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {

    $this->settings->expects($this->exactly(2))
      ->method('set')
      ->willReturnMap([
        ['geoip_path', 'new/path/to/geoip/database', $this->settings],
        ['license', 'secret-key-provided-by-maxmind', $this->settings],
      ]);
    $this->settings->expects($this->once())
      ->method('save');

    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors_geoip.settings')
      ->willReturn($this->settings);

    // Create a mock form state object.
    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->once())
      ->method('getValues')
      ->willReturn([
        'geoip_path' => 'new/path/to/geoip/database',
        'license' => 'secret-key-provided-by-maxmind',
      ]);

    // Submit the form.
    $form = [
      'geoip_path' => [
        '#value' => 'new/path/to/geoip/database',
      ],
      'license' => [
        '#value' => 'secret-key-provided-by-maxmind',
      ],
    ];
    $this->form->submitForm($form, $form_state);

    // Assert that the config was updated with the new value.
    $this->assertEquals('new/path/to/geoip/database', $form['geoip_path']['#value']);
    $this->assertEquals('secret-key-provided-by-maxmind', $form['license']['#value']);
  }

  /**
   * Tests the validateForm() method.
   *
   * @covers ::validateForm
   */
  public function testValidateForm() {

    $form = [];
    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->once())
      ->method('getValue')
      ->with('geoip_path')
      ->willReturn('path/to/geoip/database');

    $this->form->validateForm($form, $form_state);

    // Assert that the form state was not set an error.
    $this->assertEmpty($form_state->getErrors());
  }

  /**
   * Tests the getFormId() method.
   *
   * @covers ::getFormId
   */
  public function testGetFormId(): void {
    $this->assertEquals('visitors_geoip_admin_form', $this->form->getFormId());
  }

  /**
   * Tests the getEditableConfigNames() method.
   *
   * @covers ::getEditableConfigNames
   */
  public function testGetEditableConfigNames(): void {
    // Make the method accessible.
    $method = new \ReflectionMethod($this->form, 'getEditableConfigNames');
    $method->setAccessible(TRUE);
    // Invoke the method.
    $result = $method->invoke($this->form);
    $this->assertEquals(['visitors_geoip.settings'], $result);
  }

}
