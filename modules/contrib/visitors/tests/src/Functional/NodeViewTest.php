<?php

namespace Drupal\Tests\visitors\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the visitors/{report} page.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Controller\Report\ReportController
 */
class NodeViewTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'visitors'];

  /**
   * Tests the html head links.
   *
   * @covers ::nodeViews
   */
  public function testHasReportAccess() {
    $user = $this->drupalCreateUser([
      'access visitors',
      'access content',
    ]);
    $this->drupalLogin($user);

    $this->visitReports(200);
  }

  /**
   * Tests that we store and retrieve multi-byte UTF-8 characters correctly.
   */
  protected function visitReports(int $status) {
    $this->drupalCreateContentType(['type' => 'page']);
    $this->drupalCreateNode();

    $this->drupalGet('node/1/visitors');
    $this->assertSession()->statusCodeEquals($status);
  }

}
