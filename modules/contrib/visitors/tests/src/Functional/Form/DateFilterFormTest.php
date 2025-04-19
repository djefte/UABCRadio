<?php

namespace Drupal\Tests\visitors\Functional\Form;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the DateFilter form.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\DateFilter
 */
class DateFilterFormTest extends BrowserTestBase {

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
   * Tests the DateFilter form.
   *
   * @covers ::buildForm
   */
  public function testDateFilterForm() {
    // Create a user with the necessary permissions.
    $user = $this->drupalCreateUser([
      'access visitors',
      'access content',
    ]);

    // Log in as the created user.
    $this->drupalLogin($user);

    // Navigate to the DateFilter form.
    $this->drupalGet('/visitors/software');

    // Assert that the form is displayed.
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('from');
    $this->assertSession()->fieldExists('to');

    $edit = [
      'from' => '11/01/2020',
      'to' => '12/01/2020',
      'period' => 'range',
    ];
    $this->submitForm($edit, 'Apply');

    // Assert that the form submission is successful.
    $this->assertSession()->fieldExists('from');
    $this->assertSession()->fieldExists('to');
  }

}
