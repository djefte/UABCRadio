<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsWeek;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\views\ResultRow;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsWeek
 * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp
 */
class VisitorsWeekTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsWeek
   */
  protected $field;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The query.
   *
   * @var \Drupal\views\Plugin\views\query\Sql|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $query;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->query = $this->createMock('Drupal\views\Plugin\views\query\Sql');

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_week';
    $plugin_definition = [];
    $this->field = VisitorsWeek::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->field->query = $this->query;
  }

  /**
   * Tests the getFormat method.
   *
   * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp::getFormat
   */
  public function testGetFormat() {
    $this->assertEquals('%X%V', $this->field->getFormat());
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRender() {

    $values = new ResultRow(['alias' => '20210104']);
    $this->field->field_alias = 'alias';

    $this->assertEquals('2021-01-04', $this->field->render($values)->__toString());
  }

  /**
   * Tests the query method.
   *
   * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp::query
   */
  public function testQuery() {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->once())
      ->method('get')
      ->with('timezone.default')
      ->willReturn('UTC');
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($settings);

    $this->query->expects($this->once())
      ->method('addField')
      ->with(NULL, NULL, 'visitors_week', [])
      ->willReturn('visitors_week');

    $this->field->options['group_type'] = 'group';
    $this->field->tableAlias = 'tableAlias';
    $this->field->realField = 'realField';
    $this->field->field = 'visitors_week';

    $this->field->query();
  }

  /**
   * Tests the getCacheTags method.
   *
   * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp::getCacheTags
   */
  public function testGetCacheTags(): void {
    $tags = ['config:system.date'];
    $this->assertEquals($tags, $this->field->getCacheTags());
  }

}
