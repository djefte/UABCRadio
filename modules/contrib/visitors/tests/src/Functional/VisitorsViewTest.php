<?php

namespace Drupal\Tests\visitors\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the visitors/{report} page.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Controller\Report\ReportController
 * @uses \Drupal\visitors\Controller\Report\ReportController
 */
class VisitorsViewTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['visitors', 'node'];

  /**
   * Tests the html head links.
   *
   * @covers ::performance
   * @covers ::location
   * @covers ::device
   * @covers ::software
   * @covers ::time
   * @covers ::topHost
   * @covers ::topPages
   * @covers ::recentHost
   */
  public function testHasReportAccess() {
    $user = $this->drupalCreateUser([
      'access visitors',
    ]);
    $this->drupalLogin($user);

    $this->visitReports(200);
  }

  /**
   * Tests the Link header.
   *
   * @covers ::performance
   * @covers ::location
   * @covers ::device
   * @covers ::software
   * @covers ::time
   * @covers ::topHost
   * @covers ::topPages
   * @covers ::recentHost
   */
  public function testNoReportAccess() {
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($user);

    $this->visitReports(403);
  }

  /**
   * Tests Visitors Settings form access.
   *
   * @covers \Drupal\visitors\Form\Settings::buildForm
   */
  public function testVisitorsSettingsForm403() {
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/visitors');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests Visitors Settings form access.
   *
   * @covers \Drupal\visitors\Form\Settings::buildForm
   */
  public function testVisitorsSettingsForm200() {
    $user = $this->drupalCreateUser([
      'administer site configuration',
    ]);
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/visitors');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests that we store and retrieve multi-byte UTF-8 characters correctly.
   */
  protected function visitReports(int $status) {
    $this->drupalGet('/visitors');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/host');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/hits');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/referrers');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/pages');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/performance');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/location');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/device');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/software');
    $this->assertSession()->statusCodeEquals($status);

    $this->drupalGet('/visitors/times');
    $this->assertSession()->statusCodeEquals($status);
  }

}
