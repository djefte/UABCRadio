<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\argument_default\Path;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\argument_default\Path
 * @covers \Drupal\visitors\Plugin\views\argument_default\Path
 */
class PathTest extends UnitTestCase {

  /**
   * The plugin.
   *
   * @var \Drupal\visitors\Plugin\views\argument_default\Path
   */
  protected $plugin;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentPath;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $pathValidator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->currentPath = $this->createMock('Drupal\Core\Path\CurrentPathStack');
    $container->set('path.current', $this->currentPath);

    $this->pathValidator = $this->createMock('Drupal\Core\Path\PathValidatorInterface');
    $container->set('path.validator', $this->pathValidator);

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_path';
    $plugin_definition = [];
    $this->plugin = Path::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_path';
    $plugin_definition = [];
    $plugin = Path::create($container, $configuration, $plugin_id, $plugin_definition);

    $this->assertInstanceOf(Path::class, $plugin);
  }

  /**
   * Tests the constructor method.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $plugin = new Path([], 'visitors_path', [], $this->currentPath);
    $this->assertInstanceOf(Path::class, $plugin);
  }

  /**
   * Tests the defineOptions method.
   *
   * @covers ::defineOptions
   */
  public function testDefineOptions() {
    $reflection = new \ReflectionMethod($this->plugin, 'defineOptions');
    $reflection->setAccessible(TRUE);

    $options = $reflection->invoke($this->plugin);

    $this->assertCount(2, $options);
  }

  /**
   * Tests the buildOptionsForm method.
   *
   * @covers ::buildOptionsForm
   */
  public function testBuildOptionsForm(): void {
    $this->plugin->options['pop'] = 0;
    $this->plugin->options['route'] = FALSE;
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    $this->plugin->buildOptionsForm($form, $form_state);

    $this->assertArrayHasKey('pop', $form);
    $this->assertArrayHasKey('route', $form);
  }

  /**
   * Tests getArgument method.
   *
   * @covers ::getArgument
   */
  public function testGetArgumentPop(): void {
    $this->plugin->options['pop'] = 1;
    $this->plugin->options['route'] = FALSE;
    $this->currentPath->expects($this->once())
      ->method('getPath')
      ->willReturn('/node/1/edit');

    $this->assertEquals('/node/1', $this->plugin->getArgument());
  }

  /**
   * Tests getArgument method.
   *
   * @covers ::getArgument
   */
  public function testGetArgumentNoPath(): void {

    $plugin = new Path([], 'visitors_path', [], NULL);
    $plugin->options['pop'] = 0;
    $plugin->options['route'] = FALSE;
    $this->assertEquals('', $plugin->getArgument());
  }

  /**
   * Tests getArgument method.
   *
   * @covers ::getArgument
   */
  public function testGetArgumentRoute(): void {
    $url = $this->createMock('Drupal\Core\Url');
    $url->expects($this->once())
      ->method('getRouteName')
      ->willReturn('entity.node.canonical');
    $url->expects($this->once())
      ->method('getOptions')
      ->willReturn(['node' => 1]);
    $this->pathValidator->expects($this->once())
      ->method('getUrlIfValidWithoutAccessCheck')
      ->with('node/1')
      ->willReturn($url);
    $this->plugin->options['pop'] = 1;
    $this->plugin->options['route'] = TRUE;
    $this->currentPath->expects($this->once())
      ->method('getPath')
      ->willReturn('/node/1/edit');

    $this->assertEquals('entity.node.canonical', $this->plugin->getArgument());
  }

  /**
   * Tests the getCacheMaxAge method.
   *
   * @covers ::getCacheMaxAge
   */
  public function testGetCacheMaxAge() {
    $this->assertEquals(Cache::PERMANENT, $this->plugin->getCacheMaxAge());
  }

  /**
   * Tests the getCacheContexts method.
   *
   * @covers ::getCacheContexts
   */
  public function testGetCacheContexts() {
    $this->assertEquals(['url.path'], $this->plugin->getCacheContexts());
  }

}
