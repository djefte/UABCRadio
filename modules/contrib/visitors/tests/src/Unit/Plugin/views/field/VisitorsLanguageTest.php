<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsLanguage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\views\ResultRow;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsLanguage
 * @uses \Drupal\visitors\Plugin\views\field\VisitorsLanguage
 */
class VisitorsLanguageTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsLanguage
   */
  protected $field;

  /**
   * The language service.
   *
   * @var \Drupal\visitors\VisitorsLanguageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $language;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

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

    $this->language = $this->createMock('Drupal\visitors\VisitorsLanguageInterface');
    $container->set('visitors.language', $this->language);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_continent';
    $plugin_definition = [];
    $this->field = VisitorsLanguage::create($container, $configuration, $plugin_id, $plugin_definition);
    $options = [];
    $options['id'] = 'id';
    $options['code'] = TRUE;
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

    $this->assertArrayHasKey('code', $form);

  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_language';
    $plugin_definition = [];
    $field = VisitorsLanguage::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('Drupal\visitors\Plugin\views\field\VisitorsLanguage', $field);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $field = new VisitorsLanguage([], 'visitors_language', [], $this->language);
    $this->assertInstanceOf('Drupal\visitors\Plugin\views\field\VisitorsLanguage', $field);
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

    $this->assertArrayHasKey('code', $options);
    $this->assertEquals(FALSE, $options['code']['default']);
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRenderWithCode() {

    $values = new ResultRow(['alias' => 'en']);
    $this->field->field_alias = 'alias';
    $label = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $label->expects($this->once())
      ->method('__toString')
      ->willReturn('English');
    $this->language->expects($this->once())
      ->method('getLanguageLabel')
      ->with('en')
      ->willReturn($label);

    $this->assertEquals('English (en)', (string) $this->field->render($values));
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRender() {
    $this->field->options['code'] = FALSE;

    $values = new ResultRow(['alias' => 'en']);
    $this->field->field_alias = 'alias';
    $label = $this->createMock('Drupal\Component\Render\MarkupInterface');
    $label->expects($this->once())
      ->method('__toString')
      ->willReturn('English');
    $this->language->expects($this->once())
      ->method('getLanguageLabel')
      ->with('en')
      ->willReturn($label);

    $this->assertEquals('English', (string) $this->field->render($values));
  }

}
