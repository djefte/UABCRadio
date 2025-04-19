<?php

namespace Drupal\Tests\visitors\Functional\Views;

use Drupal\Tests\views\Functional\ViewTestBase;

/**
 * Tests basic integration of views data from the visitors module.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Service\CounterService
 * @uses \Drupal\visitors\Service\CounterService
 */
class IntegrationTest extends ViewTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['visitors', 'visitors_test_views', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Stores the user object that accesses the page.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * A test user with node viewing access only.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $deniedUser;

  /**
   * Stores the node object which is used by the test.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_statistics_integration'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['visitors_test_views']): void {
    parent::setUp($import_test_views, $modules);

    // Create a new user for viewing nodes and statistics.
    $this->webUser = $this->drupalCreateUser([
      'access content',
      'view visitors counter',
    ]);

    // Create a new user for viewing nodes only.
    $this->deniedUser = $this->drupalCreateUser(['access content']);

    $this->drupalCreateContentType(['type' => 'page']);
    $this->node = $this->drupalCreateNode(['type' => 'page']);
  }

  /**
   * Tests the integration of the {visitors_counter} table in views.
   *
   * @covers ::fetchViews
   */
  public function testNodeCounterIntegration() {
    $this->drupalLogin($this->webUser);

    $nid = $this->node->id();
    \Drupal::service('visitors.counter')->recordView('node', $nid);

    $this->drupalGet('node/' . $nid);
    $this->drupalGet('test_statistics_integration');

    /** @var \Drupal\visitors\StatisticsViewsResult $statistics */
    $statistics = \Drupal::service('visitors.counter')
      ->fetchView('node', $nid);

    $this->assertSession()->pageTextContains('Total views: 1');
    $this->assertSession()->pageTextContains('Views today: 1');
    $this->assertSession()->pageTextContains('Most recent view: ' . date('Y', $statistics->getTimestamp()));

    $this->drupalLogout();
    $this->drupalLogin($this->deniedUser);
    $this->drupalGet('test_statistics_integration');
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextNotContains('Total views:');
    $this->assertSession()->pageTextNotContains('Views today:');
    $this->assertSession()->pageTextNotContains('Most recent view:');
  }

}
