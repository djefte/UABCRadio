<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\sort;

use Drupal\Tests\UnitTestCase;
use Drupal\views\ResultRow;
use Drupal\visitors\Plugin\views\sort\VisitorsTimestamp;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\sort\VisitorsTimestamp
 * @covers \Drupal\visitors\Plugin\views\sort\VisitorsTimestamp
 */
class VisitorsTimestampTest extends UnitTestCase {

  /**
   * The sort plugin.
   *
   * @var \Drupal\visitors\Plugin\views\sort\VisitorsTimestamp
   */
  protected $sort;

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

    $this->query = $this->createMock('Drupal\views\Plugin\views\query\Sql');

    \Drupal::setContainer($container);

    $configuration = [
      'table' => 'visitors_view_visitors_visit',
      'field' => 'created',
    ];
    $plugin_id = 'visitors_timestamp';
    $plugin_definition = [];
    $this->sort = VisitorsTimestamp::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->sort->query = $this->query;
  }

  /**
   * Tests the query method.
   *
   * @covers \Drupal\visitors\Plugin\views\sort\VisitorsTimestamp::query
   */
  public function testQuery() {

    $this->sort->options['order'] = 'ASC';
    $this->sort->field = 'visitors_week';

    $this->query->expects($this->once())
      ->method('addOrderBy')
      ->with(NULL, NULL, 'ASC', 'visitors_week');

    $this->sort->query();
  }

  /**
   * Tests the query method.
   *
   * @covers \Drupal\visitors\Plugin\views\sort\VisitorsTimestamp::query
   */
  public function testQueryLocalHour() {

    $this->sort->options['order'] = 'ASC';
    $this->sort->field = 'visitor_localtime';

    $this->query->expects($this->once())
      ->method('addOrderBy')
      ->with(NULL, NULL, 'ASC', 'visitors_visitor_localtime');

    $this->sort->query();
  }

  /**
   * Tests the postExecute method.
   *
   * @covers ::postExecute
   */
  public function testPostExecute() {
    $values = [
      new ResultRow(['visitors_week' => 1]),
      new ResultRow(['visitors_week' => 2]),
    ];

    $this->sort->postExecute($values);

    $this->assertEquals(1, $values[0]->visitors_week);
    $this->assertEquals(0, $values[0]->index);
    $this->assertEquals(2, $values[1]->visitors_week);
    $this->assertEquals(1, $values[1]->index);
  }

}
