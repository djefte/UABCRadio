<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_help.
 *
 * @group visitors
 */
class HookHelpTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $container->set('string_translation', $string_translation);

    \Drupal::setContainer($container);
  }

  /**
   * Tests visitors_help().
   */
  public function testVisitorsHelp() {
    $route_match = $this->createMock('Drupal\Core\Routing\RouteMatchInterface');

    $help = visitors_help('help.page.visitors', $route_match);

    $has_coverage = strpos($help, 'https://git.drupalcode.org/project/visitors/badges/8.x-2.x/coverage.svg');
    $this->assertNotFalse($has_coverage);
  }

}
