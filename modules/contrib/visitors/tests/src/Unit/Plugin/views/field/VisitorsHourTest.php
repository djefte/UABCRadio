<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsHour;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsHour
 * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp
 */
class VisitorsHourTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsHour
   */
  protected $field;

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

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_hour';
    $plugin_definition = [];
    $this->field = VisitorsHour::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the getFormat method.
   *
   * @covers \Drupal\visitors\Plugin\views\field\VisitorsTimestamp::getFormat
   */
  public function testGetFormat() {
    $this->assertEquals('H', $this->field->getFormat());
  }

}
