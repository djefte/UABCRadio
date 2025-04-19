<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsLocalHour;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsLocalHour
 */
class VisitorsLocalHourTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsLocalHour
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

    $configuration = [
      'field' => 'visitor_localtime',
    ];
    $plugin_id = 'visitors_local_hour';
    $plugin_definition = [];
    $this->field = VisitorsLocalHour::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->field->query = $this->query;
  }

  /**
   * Tests the query method.
   *
   * @covers ::query
   */
  public function testQuery() {
    $this->query->expects($this->once())
      ->method('addField')
      ->with(NULL, 'FLOOR(visitor_localtime/3600)', 'visitors_visitor_localtime', [])
      ->willReturn('visitors_visitor_localtime');

    $this->field->options['group_type'] = 'group';
    $this->field->field = 'visitors_visitor_localtime';
    $this->field->tableAlias = 'visitors';

    $this->field->query();
  }

}
