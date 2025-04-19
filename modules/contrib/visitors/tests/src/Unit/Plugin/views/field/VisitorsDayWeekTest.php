<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsDayWeek;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\views\ResultRow;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsDayWeek
 * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp
 * @covers \Drupal\visitors\Plugin\views\field\VisitorsDayWeek
 */
class VisitorsDayWeekTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsDayWeek
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

    \Drupal::setContainer($container);

    $this->query = $this->createMock('Drupal\views\Plugin\views\query\Sql');

    $configuration = [];
    $plugin_id = 'visitors_day_of_week';
    $plugin_definition = [];
    $this->field = VisitorsDayWeek::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->field->query = $this->query;
  }

  /**
   * Tests the getFormat method.
   *
   * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp::getFormat
   */
  public function testGetFormat() {
    $this->assertEquals('%w', $this->field->getFormat());
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRender() {
    $system_date = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $system_date->expects($this->once())
      ->method('get')
      ->with('first_day')
      ->willReturn(0);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($system_date);

    $values = new ResultRow(['alias' => '4']);
    $this->field->field_alias = 'alias';

    $this->assertEquals('Thursday', $this->field->render($values)['#markup']->__toString());
  }

  /**
   * Tests the render method.
   *
   * @covers ::render
   */
  public function testRenderFirstDay() {
    $system_date = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $system_date->expects($this->once())
      ->method('get')
      ->with('first_day')
      ->willReturn(4);
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($system_date);

    $values = new ResultRow(['alias' => '4']);
    $this->field->field_alias = 'alias';

    $this->assertEquals('Monday', $this->field->render($values)['#markup']->__toString());
  }

  /**
   * Tests the query method.
   *
   * @covers ::query
   */
  public function testQuery() {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['timezone.default', 'UTC'],
        ['first_day', 0],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($settings);

    $this->query->expects($this->once())
      ->method('getDateField')
      ->with('visitors_view_visitors_visit.created', FALSE, FALSE)
      ->willReturn("DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND)");

    $this->query->expects($this->once())
      ->method('setFieldTimezoneOffset')
      ->with("DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND)", 0);

    $this->query->expects($this->once())
      ->method('getDateFormat')
      ->with("DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND)", '%w', FALSE)
      ->willReturn("DATE_FORMAT((DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND) + INTERVAL -21600 SECOND), '%X%V')");

    $this->query->expects($this->once())
      ->method('addField')
      ->with(NULL, "DATE_FORMAT((DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND) + INTERVAL -21600 SECOND), '%X%V')", 'visitors_week', [])
      ->willReturn('visitors_week');

    $this->field->options['group_type'] = 'group';
    $this->field->tableAlias = 'visitors_view_visitors_visit';
    $this->field->realField = 'created';
    $this->field->field = 'visitors_week';

    $this->field->query();
  }

  /**
   * Tests the query method.
   *
   * @covers ::query
   */
  public function testQueryFirstDay() {
    $settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');
    $settings->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['timezone.default', 'UTC'],
        ['first_day', 4],
      ]);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.date')
      ->willReturn($settings);

    $this->query->expects($this->once())
      ->method('getDateField')
      ->with('visitors_view_visitors_visit.created', FALSE, FALSE)
      ->willReturn("DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND)");

    $this->query->expects($this->once())
      ->method('setFieldTimezoneOffset')
      ->with("DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND)", 0);

    $this->query->expects($this->once())
      ->method('getDateFormat')
      ->with("DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND)", '%w', FALSE)
      ->willReturn("DATE_FORMAT((DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND) + INTERVAL -21600 SECOND), '%w')");

    $this->query->expects($this->once())
      ->method('addField')
      ->with(NULL, "((DATE_FORMAT((DATE_ADD('19700101', INTERVAL visitors_view_visitors_visit.created SECOND) + INTERVAL -21600 SECOND), '%w') + 7 - 4) % 7)", 'visitors_week', [])
      ->willReturn('visitors_week');

    $this->field->options['group_type'] = 'group';
    $this->field->tableAlias = 'visitors_view_visitors_visit';
    $this->field->realField = 'created';
    $this->field->field = 'visitors_week';

    $this->field->query();
  }

}
