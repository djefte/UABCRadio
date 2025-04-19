<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsCountry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\views\ResultRow;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsCountry
 * @uses \Drupal\visitors\Plugin\views\field\VisitorsCountry
 */
class VisitorsCountryTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsCountry
   */
  protected $field;

  /**
   * The location service.
   *
   * @var \Drupal\visitors\VisitorsLocationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $location;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $request;

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

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->location = $this->createMock('Drupal\visitors\VisitorsLocationInterface');
    $container->set('visitors.location', $this->location);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    \Drupal::setContainer($container);

    $this->request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $this->requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($this->request);

    $this->request->expects($this->any())
      ->method('getBasePath')
      ->willReturn('/base/path');

    $extension = $this->createMock('\Drupal\Core\Extension\Extension');
    $extension->expects($this->any())
      ->method('getPath')
      ->willReturn('modules/contrib/visitors');
    $this->moduleHandler->expects($this->any())
      ->method('getModule')
      ->with('visitors')
      ->willReturn($extension);

    $configuration = [];
    $plugin_id = 'visitors_country';
    $plugin_definition = [];
    $this->field = VisitorsCountry::create($container, $configuration, $plugin_id, $plugin_definition);
    $options = [];
    $options['id'] = 'id';
    $options['text'] = TRUE;
    $options['icon'] = TRUE;
    $options['abbreviation'] = FALSE;
    $options['admin_label'] = 'test';
    $options['element_label_colon'] = FALSE;
    $options['exclude'] = FALSE;
    $options['element_type'] = FALSE;
    $options['element_class'] = FALSE;
    $options['element_label_type'] = FALSE;
    $options['element_label_class'] = FALSE;
    $options['element_wrapper_type'] = FALSE;
    $options['element_wrapper_class'] = FALSE;
    $options['empty'] = FALSE;
    $options['hide_empty'] = FALSE;
    $options['empty_zero'] = FALSE;
    $options['hide_alter_empty'] = FALSE;
    $options['element_default_classes'] = FALSE;

    $options['alter'] = [];
    $options['alter']['alter_text'] = FALSE;
    $options['alter']['text'] = '';
    $options['alter']['make_link'] = FALSE;
    $options['alter']['preserve_tags'] = FALSE;
    $options['alter']['strip_tags'] = FALSE;
    $options['alter']['trim_whitespace'] = FALSE;
    $options['alter']['path'] = '';
    $options['alter']['absolute'] = FALSE;
    $options['alter']['replace_spaces'] = FALSE;
    $options['alter']['external'] = FALSE;
    $options['alter']['path_case'] = FALSE;
    $options['alter']['link_class'] = '';
    $options['alter']['alt'] = '';
    $options['alter']['rel'] = '';
    $options['alter']['prefix'] = '';
    $options['alter']['suffix'] = '';
    $options['alter']['target'] = '';
    $options['alter']['max_length'] = FALSE;
    $options['alter']['word_boundary'] = FALSE;
    $options['alter']['ellipsis'] = FALSE;
    $options['alter']['trim'] = FALSE;
    $options['alter']['html'] = FALSE;
    $options['alter']['nl2br'] = FALSE;
    $options['alter']['more_link'] = FALSE;
    $options['alter']['more_link_text'] = '';
    $options['alter']['more_link_path'] = '';

    $this->field->options = $options;
  }

  /**
   * Tests the buildOptionsForm method.
   *
   * @covers ::buildOptionsForm
   */
  public function testBuildOptionsForm() {
    $this->field->view = $this->createMock('Drupal\views\ViewExecutable');
    $this->field->view->display_handler = $this->createMock('Drupal\views\Plugin\views\display\DisplayPluginBase');
    $this->field->view->display_handler->expects($this->once())
      ->method('getFieldLabels')
      ->willReturn([]);
    $this->field->view->display_handler->expects($this->once())
      ->method('getHandlers')
      ->with('argument')
      ->willReturn([]);
    $views_settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $views_settings->expects($this->any())
      ->method('get')
      ->with('field_rewrite_elements')
      ->willReturn([]);
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('views.settings')
      ->willReturn($views_settings);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    $this->field->buildOptionsForm($form, $form_state);

    $this->assertArrayHasKey('icon', $form);
    $this->assertArrayHasKey('text', $form);
    $this->assertArrayHasKey('abbreviation', $form);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_country';
    $plugin_definition = [];
    $field = VisitorsCountry::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('Drupal\visitors\Plugin\views\field\VisitorsCountry', $field);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $field = new VisitorsCountry([], 'visitors_country', [], $this->moduleHandler, $this->request, $this->location);
    $this->assertInstanceOf('Drupal\visitors\Plugin\views\field\VisitorsCountry', $field);
  }

  /**
   * Tests the defineOptions method.
   *
   * @covers ::defineOptions
   */
  public function testDefineOptions() {
    $this->field->view = $this->createMock('Drupal\views\ViewExecutable');

    $method = new \ReflectionMethod($this->field, 'defineOptions');
    $method->setAccessible(TRUE);
    $options = $method->invoke($this->field);

    $this->assertArrayHasKey('icon', $options);
    $this->assertTrue($options['icon']['default']);
    $this->assertTrue($options['text']['default']);
    $this->assertFalse($options['abbreviation']['default']);
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRender() {

    $values = new ResultRow(['alias' => 'US']);
    $this->field->field_alias = 'alias';
    $label = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $label->expects($this->once())
      ->method('__toString')
      ->willReturn('United States');
    $this->location->expects($this->once())
      ->method('getCountryLabel')
      ->with('US')
      ->willReturn($label);

    $this->assertEquals('<img src="/base/path/modules/contrib/visitors/icons/flags/us.png" width="16" height="16" /> United States', (string) $this->field->render($values));
  }

}
