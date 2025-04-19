<?php

namespace Drupal\Tests\visitors\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\visitors\VisitorsVisibilityInterface;

/**
 * Tests basic integration of views data from the visitors module.
 *
 * @group visitors
 */
class UserFormTest extends BrowserTestBase {


  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['visitors'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Stores the user object that accesses the page.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $grantedUser;

  /**
   * A test user with node viewing access only.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $deniedUser;


  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_statistics_integration'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a new user for viewing nodes and statistics.
    $this->grantedUser = $this->drupalCreateUser([
      'opt-out of visitors tracking',
    ]);

    // Create a new user for viewing nodes only.
    $this->deniedUser = $this->drupalCreateUser([]);

  }

  /**
   * Tests user form with no personalization.
   */
  public function testNoPersonalization() {
    \Drupal::configFactory()
      ->getEditable('visitors.config')
      ->set('visibility.user_account_mode', VisitorsVisibilityInterface::USER_NO_PERSONALIZATION)
      ->save();

    $this->drupalLogin($this->grantedUser);
    $this->drupalGet('user/' . $this->grantedUser->id() . '/edit');
    $this->assertSession()->responseNotContains('Visitors settings');

    $this->drupalLogin($this->deniedUser);
    $this->drupalGet('user/' . $this->deniedUser->id() . '/edit');
    $this->assertSession()->responseNotContains('Visitors settings');
  }

  /**
   * Tests user form with opt out.
   */
  public function testOptOut() {
    \Drupal::configFactory()
      ->getEditable('visitors.config')
      ->set('visibility.user_account_mode', VisitorsVisibilityInterface::USER_OPT_OUT)
      ->save();

    $this->drupalLogin($this->grantedUser);
    $this->drupalGet('user/' . $this->grantedUser->id() . '/edit');
    $this->assertSession()->responseContains('Visitors settings');
    $this->assertSession()->checkboxChecked('edit-user-account-users');
    // Change value and submit form.
    $edit = [
      'user_account_users' => FALSE,
      'mail' => $this->grantedUser->get('mail')->value,
      'timezone' => $this->grantedUser->get('timezone')->value,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseContains('The changes have been saved.');
    $this->assertSession()->checkboxNotChecked('edit-user-account-users');

    $this->drupalLogin($this->deniedUser);
    $this->drupalGet('user/' . $this->deniedUser->id() . '/edit');
    $this->assertSession()->responseNotContains('Visitors settings');
  }

  /**
   * Tests user form with opt in.
   */
  public function testOptIn() {
    \Drupal::configFactory()
      ->getEditable('visitors.config')
      ->set('visibility.user_account_mode', VisitorsVisibilityInterface::USER_OPT_IN)
      ->save();

    $this->drupalLogin($this->grantedUser);
    $this->drupalGet('user/' . $this->grantedUser->id() . '/edit');
    $this->assertSession()->responseContains('Visitors settings');
    $this->assertSession()->checkboxNotChecked('edit-user-account-users');
    // Change value and submit form.
    $edit = [
      'user_account_users' => TRUE,
      'mail' => $this->grantedUser->get('mail')->value,
      'timezone' => $this->grantedUser->get('timezone')->value,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseContains('The changes have been saved.');
    $this->assertSession()->checkboxChecked('edit-user-account-users');

    $this->drupalLogin($this->deniedUser);
    $this->drupalGet('user/' . $this->deniedUser->id() . '/edit');
    $this->assertSession()->responseNotContains('Visitors settings');
  }

}
