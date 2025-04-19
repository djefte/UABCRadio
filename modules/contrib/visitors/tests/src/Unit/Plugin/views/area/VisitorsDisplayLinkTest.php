<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\area;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\area\VisitorsDisplayLink;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\area\VisitorsDisplayLink
 * @covers \Drupal\visitors\Plugin\views\area\VisitorsDisplayLink
 */
class VisitorsDisplayLinkTest extends UnitTestCase {

  /**
   * The plugin.
   *
   * @var \Drupal\visitors\Plugin\views\area\VisitorsDisplayLink
   */
  protected $plugin;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The view settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewSettings;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $pathValidator;

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

    $this->viewSettings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('views.settings')
      ->willReturn($this->viewSettings);

    $container->set('config.factory', $this->configFactory);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->pathValidator = $this->createMock('Drupal\Core\Path\PathValidatorInterface');
    $container->set('path.validator', $this->pathValidator);

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_display_link';
    $plugin_definition = [];
    $this->plugin = VisitorsDisplayLink::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_display_link';
    $plugin_definition = [];
    $plugin = VisitorsDisplayLink::create($container, $configuration, $plugin_id, $plugin_definition);

    $this->assertInstanceOf(VisitorsDisplayLink::class, $plugin);
  }

  /**
   * Tests the constructor method.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $plugin = new VisitorsDisplayLink([], 'visitors_display_link', [], $this->viewSettings);
    $this->assertInstanceOf(VisitorsDisplayLink::class, $plugin);
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRenderEmptyDisplay() {

    $render_array = $this->plugin->render();

    $this->assertIsArray($render_array);
    $this->assertCount(0, $render_array);
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRenderPath() {
    $this->plugin->options['display_id'] = 'page_1';
    $this->plugin->options['empty'] = 'not empty';

    $display = $this->createMock('Drupal\views\Plugin\views\display\PathPluginBase');
    $view = $this->createMock('Drupal\views\ViewExecutable');
    $display_handler = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handler->expects($this->once())
      ->method('get')
      ->with('page_1')
      ->willReturn($display);
    $view->displayHandlers = $display_handler;
    $this->plugin->view = $view;

    $render_array = $this->plugin->render();

    $this->assertIsArray($render_array);
    $this->assertCount(0, $render_array);
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRender() {
    $this->plugin->options['display_id'] = 'embed_1';
    $this->plugin->options['empty'] = 'not empty';
    $this->plugin->options['label'] = 'Visitors';

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $view->current_display = 'embed_1';
    $view->expects($this->once())
      ->method('getCurrentPage')
      ->willReturn(1);
    $storage = $this->createMock('Drupal\views\Entity\View');
    $storage->expects($this->once())
      ->method('id')
      ->willReturn('visitors');
    $view->storage = $storage;
    $display = $this->createMock('Drupal\views\Plugin\views\display\Embed');
    $display_handler = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handler->expects($this->once())
      ->method('get')
      ->with('embed_1')
      ->willReturn($display);
    $view->displayHandlers = $display_handler;
    $this->plugin->view = $view;

    $render_array = $this->plugin->render();

    $this->assertIsArray($render_array);
    $this->assertCount(4, $render_array);
  }

  /**
   * Tests the validate method.
   *
   * @covers ::validate
   */
  public function testValidateDefaultDisplay(): void {
    $this->plugin->options['label'] = 'Visitors';

    $this->viewSettings->expects($this->once())
      ->method('get')
      ->with('ui.show.default_display')
      ->willReturn(FALSE);
    $view = $this->createMock('Drupal\views\ViewExecutable');
    $display_handler = $this->createMock('Drupal\views\Plugin\views\display\DisplayPluginBase');
    $display_handler->expects($this->once())
      ->method('isDefaultDisplay')
      ->willReturn(TRUE);
    $this->plugin->displayHandler = $display_handler;

    $errors = $this->plugin->validate();
    $this->assertCount(0, $errors);
  }

  /**
   * Tests the validate method.
   *
   * @covers ::validate
   */
  public function testValidateNoDisplaySet(): void {

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $display_handler = $this->createMock('Drupal\views\Plugin\views\display\DisplayPluginBase');
    $display_handler->expects($this->once())
      ->method('isDefaultDisplay')
      ->willReturn(FALSE);

    $display_handler->display['display_title'] = 'Visitors';

    $this->plugin->displayHandler = $display_handler;

    $errors = $this->plugin->validate();
    $this->assertCount(1, $errors);
  }

  /**
   * Tests the validate method.
   *
   * @covers ::validate
   */
  public function testValidateDisplayRemoved(): void {
    $this->plugin->options['display_id'] = 'embed_1';

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $display_handler = $this->createMock('Drupal\views\Plugin\views\display\DisplayPluginBase');
    $display_handler->expects($this->once())
      ->method('isDefaultDisplay')
      ->willReturn(FALSE);

    $display_handler->display['display_title'] = 'Visitors';

    $this->plugin->displayHandler = $display_handler;

    $display_handlers = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handlers->expects($this->once())
      ->method('get')
      ->with('embed_1')
      ->willReturn(FALSE);
    $view->displayHandlers = $display_handlers;
    $this->plugin->view = $view;

    $errors = $this->plugin->validate();
    $this->assertCount(1, $errors);
  }

  /**
   * Tests the validate method.
   *
   * @covers ::validate
   */
  public function testValidateHasPath(): void {
    $this->plugin->options['display_id'] = 'page_1';

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $display_handler = $this->createMock('Drupal\views\Plugin\views\display\DisplayPluginBase');
    $display_handler->expects($this->once())
      ->method('isDefaultDisplay')
      ->willReturn(FALSE);

    $display_handler->display['display_title'] = 'Visitors';

    $this->plugin->displayHandler = $display_handler;

    $display = $this->createMock('Drupal\views\Plugin\views\display\PathPluginBase');
    $display->display['display_title'] = 'Visitors';
    $display_handlers = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handlers->expects($this->exactly(3))
      ->method('get')
      ->with('page_1')
      ->willReturn($display);
    $view->displayHandlers = $display_handlers;
    $this->plugin->view = $view;

    $errors = $this->plugin->validate();
    $this->assertCount(1, $errors);
  }

  /**
   * Tests the validate method.
   *
   * @covers ::validate
   */
  public function testValidateDifferentOptions(): void {
    $this->plugin->options['display_id'] = 'page_1';

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $display_handler = $this->createMock('Drupal\views\Plugin\views\display\DisplayPluginBase');
    $display_handler->expects($this->once())
      ->method('isDefaultDisplay')
      ->willReturn(FALSE);
    $display_handler->expects($this->exactly(3))
      ->method('getOption')
      ->willReturnMap([
        ['filters', 'filter option'],
        ['pager', 'pager option'],
        ['arguments', 'b1 option'],
      ]);

    $display_handler->display['display_title'] = 'Visitors';

    $this->plugin->displayHandler = $display_handler;

    $display = $this->createMock('Drupal\views\Plugin\views\display\Embed');
    $display->expects($this->exactly(3))
      ->method('getOption')
      ->willReturnMap([
        ['filters', 'filter option'],
        ['pager', 'pager option'],
        ['arguments', 'a1 option'],
      ]);
    $display->display['display_title'] = 'Visitors';
    $display_handlers = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handlers->expects($this->exactly(6))
      ->method('get')
      ->with('page_1')
      ->willReturn($display);
    $view->displayHandlers = $display_handlers;
    $this->plugin->view = $view;

    $errors = $this->plugin->validate();
    $this->assertCount(0, $errors);
  }

  /**
   * Tests the buildOptionsForm method.
   *
   * @covers ::buildOptionsForm
   */
  public function testBuildOptionsForm(): void {
    $this->plugin->options['admin_label'] = 'embed display';
    $this->plugin->options['display_id'] = 'embed_1';
    $this->plugin->options['label'] = 'not empty';

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $page_display = $this->createMock('Drupal\views\Plugin\views\display\PathPluginBase');
    $display = [
      'display_title' => 'embed display',
    ];
    $storage = $this->createMock('Drupal\views\Entity\View');
    $storage->expects($this->exactly(2))
      ->method('get')
      ->with('display')
      ->willReturn([
        'embed_1' => $display,
        'page_1' => $display,
      ]);
    $view->storage = $storage;

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $display_handlers = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handlers->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap([
        ['embed_1', $display],
        ['page_1', $page_display],
      ]);
    $view->displayHandlers = $display_handlers;
    $this->plugin->view = $view;
    $this->plugin->buildOptionsForm($form, $form_state);

    $this->assertCount(8, $form);
  }

  /**
   * Tests the buildOptionsForm method.
   *
   * @covers ::buildOptionsForm
   */
  public function testBuildOptionsFormNotAllowed(): void {
    $this->plugin->options['admin_label'] = 'embed display';
    $this->plugin->options['display_id'] = 'embed_1';
    $this->plugin->options['label'] = 'not empty';

    $view = $this->createMock('Drupal\views\ViewExecutable');
    $page_display = $this->createMock('Drupal\views\Plugin\views\display\PathPluginBase');
    $display = [
      'display_title' => 'embed display',
    ];
    $storage = $this->createMock('Drupal\views\Entity\View');
    $storage->expects($this->exactly(2))
      ->method('get')
      ->with('display')
      ->willReturn([
        // 'embed_1' => $display,
        'page_1' => $display,
      ]);
    $view->storage = $storage;

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $display_handlers = $this->createMock('Drupal\views\DisplayPluginCollection');
    $display_handlers->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        // ['embed_1', $display],
        ['page_1', $page_display],
      ]);
    $view->displayHandlers = $display_handlers;
    $this->plugin->view = $view;
    $this->plugin->buildOptionsForm($form, $form_state);

    $this->assertCount(9, $form);
  }

}
