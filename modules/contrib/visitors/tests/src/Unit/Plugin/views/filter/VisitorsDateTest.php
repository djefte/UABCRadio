<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\filter;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\filter\VisitorsDate;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\filter\VisitorsDate
 * @covers \Drupal\visitors\Plugin\views\filter\VisitorsDate
 */
class VisitorsDateTest extends UnitTestCase {

  /**
   * The plugin.
   *
   * @var \Drupal\visitors\Plugin\views\filter\VisitorsDate
   */
  protected $plugin;

  /**
   * The date range.
   *
   * @var \Drupal\visitors\VisitorsDateRangeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $dateRange;

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheContextsManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->dateRange = $this->createMock('Drupal\visitors\VisitorsDateRangeInterface');
    $container->set('visitors.date_range', $this->dateRange);

    $this->cacheContextsManager = $this->createMock('Drupal\Core\Cache\Context\CacheContextsManager');
    $container->set('cache_contexts_manager', $this->cacheContextsManager);

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_date';
    $plugin_definition = [];
    $this->plugin = VisitorsDate::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    $container = \Drupal::getContainer();
    $configuration = [];
    $plugin_id = 'visitors_date';
    $plugin_definition = [];
    $plugin = VisitorsDate::create($container, $configuration, $plugin_id, $plugin_definition);

    $this->assertInstanceOf(VisitorsDate::class, $plugin);
  }

  /**
   * Tests the constructor method.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $plugin = new VisitorsDate([], 'visitors_date', [], $this->dateRange);
    $this->assertInstanceOf(VisitorsDate::class, $plugin);
  }

  /**
   * Tests the valueForm method.
   *
   * @covers ::valueForm
   */
  public function testValueForm() {
    $this->plugin->value['value'] = 'value';
    $this->plugin->value['min'] = 'min';
    $this->plugin->value['max'] = 'max';
    $form = [
      'operator' => [
        '#type' => 'select',
        '#options' => [
          'global' => 'global',
          'between' => 'between',
          'not between' => 'not between',
          'empty' => 'empty',
          'not empty' => 'not empty',
        ],
      ],
    ];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $reflection = new \ReflectionMethod($this->plugin, 'valueForm');
    $reflection->setAccessible(TRUE);

    $reflection->invokeArgs($this->plugin, [&$form, $form_state]);

    $this->assertEquals('global', $form['value']['type']['#default_value']);
  }

  /**
   * Tests the validateValidTime method.
   *
   * @covers ::validateValidTime
   */
  public function testValidateValidTime(): void {

    $form = [
      'value' => [
        '#type' => 'textfield',
        '#title' => 'Value',
      ],
    ];
    $markup = $this->createMock('Drupal\Core\StringTranslation\TranslatableMarkup');
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('setError');
    $operator = '=';
    $value = [
      'value' => 'Not Good',
    ];
    $this->plugin->validateValidTime($form, $form_state, $operator, $value);

    $this->assertCount(1, $form);
  }

  /**
   * Tests the validateValidTime method.
   *
   * @covers ::validateValidTime
   */
  public function testValidateValidTimeBetween(): void {

    $form = [
      'min' => [
        '#type' => 'textfield',
        '#title' => 'Value',
      ],
      'max' => [
        '#type' => 'textfield',
        '#title' => 'Value',
      ],
    ];
    $markup = $this->createMock('Drupal\Core\StringTranslation\TranslatableMarkup');
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->exactly(2))
      ->method('setError');
    $operator = 'between';
    $value = [
      'type' => 'offset',
      'max' => 'Not Good',
      'min' => 'Not Good',
    ];
    $this->plugin->validateValidTime($form, $form_state, $operator, $value);

    $this->assertCount(2, $form);
  }

  /**
   * Tests the opBetween method.
   *
   * @covers ::opBetween
   */
  public function testOpBetween(): void {
    $this->plugin->value['value'] = 'value';
    $this->plugin->value['min'] = '1';
    $this->plugin->value['max'] = '3600';
    $this->plugin->value['type'] = 'offset';
    $this->plugin->operator = 'between';
    $this->plugin->options['group'] = NULL;

    $field = 'visitors_date_time';
    $query = $this->createMock('Drupal\views\Plugin\views\query\Sql');
    $query->expects($this->once())
      ->method('addWhereExpression')
      ->with(NULL, "$field BETWEEN ***CURRENT_TIME***+0 AND ***CURRENT_TIME***+51437804400", []);

    $this->plugin->query = $query;
    $reflection = new \ReflectionMethod($this->plugin, 'opBetween');
    $reflection->setAccessible(TRUE);

    $reflection->invoke($this->plugin, $field);
  }

  /**
   * Tests the opBetween method.
   *
   * @covers ::opBetween
   */
  public function testOpBetweenGlobal(): void {
    $this->dateRange->expects($this->once())
      ->method('getStartTimestamp')
      ->willReturn(1);
    $this->dateRange->expects($this->once())
      ->method('getEndTimestamp')
      ->willReturn(3600);

    $this->plugin->value['value'] = 'value';
    $this->plugin->value['min'] = '1';
    $this->plugin->value['max'] = '3600';
    $this->plugin->value['type'] = 'global';
    $this->plugin->operator = 'between';
    $this->plugin->options['group'] = NULL;

    $field = 'visitors_date_time';
    $query = $this->createMock('Drupal\views\Plugin\views\query\Sql');
    $query->expects($this->once())
      ->method('addWhereExpression')
      ->with(NULL, "$field BETWEEN 1 AND 3600", []);

    $this->plugin->query = $query;
    $reflection = new \ReflectionMethod($this->plugin, 'opBetween');
    $reflection->setAccessible(TRUE);

    $reflection->invoke($this->plugin, $field);
  }

  /**
   * Test the getCacheContexts method.
   *
   * @covers ::getCacheContexts
   */
  public function testGetCacheContext() {
    $this->plugin->value['type'] = 'global';
    $this->cacheContextsManager->expects($this->once())
      ->method('assertValidTokens')
      ->with(['visitors_date_range'])
      ->willReturn(TRUE);
    $this->assertEquals(['visitors_date_range'], $this->plugin->getCacheContexts());
  }

}
