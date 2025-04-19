<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Plugin\views\field;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Plugin\views\field\VisitorsNumeric;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Date filter form test.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Plugin\views\field\VisitorsNumeric
 */
class VisitorsNumericTest extends UnitTestCase {

  /**
   * The field.
   *
   * @var \Drupal\visitors\Plugin\views\field\VisitorsNumeric
   */
  protected $field;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = 'visitors_numeric';
    $plugin_definition = [];
    $this->field = VisitorsNumeric::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Tests the access method.
   *
   * @covers ::access
   */
  public function testAccess() {
    $account = $this->createMock('Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('view visitors counter')
      ->willReturn(TRUE);

    $access = $this->field->access($account);
    $this->assertTrue($access);
  }

}
