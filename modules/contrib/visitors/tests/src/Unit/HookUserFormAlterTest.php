<?php

namespace Drupal\Tests\visitors\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\VisitorsVisibilityInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../visitors.module';

/**
 * Tests visitors_form_user_form_alter.
 *
 * @group visitors
 */
class HookUserFormAlterTest extends UnitTestCase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $settings;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $userData;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->userData = $this->createMock('Drupal\user\UserDataInterface');
    $container->set('user.data', $this->userData);

    $this->settings = $this->createMock('Drupal\Core\Config\ImmutableConfig');

    \Drupal::setContainer($container);
  }

  /**
   * Tests when personalization is not allowed.
   *
   * @covers visitors_form_user_form_alter
   */
  public function testNoPersonalization(): void {

    $this->settings->expects($this->once())
      ->method('get')
      ->with('visibility.user_account_mode')
      ->willReturn(VisitorsVisibilityInterface::USER_NO_PERSONALIZATION);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');

    visitors_form_user_form_alter($form, $form_state);

    $this->assertCount(0, $form);
  }

  /**
   * Tests when user does not have permission is personalize.
   *
   * @covers visitors_form_user_form_alter
   */
  public function testNoPermission(): void {

    $this->settings->expects($this->once())
      ->method('get')
      ->with('visibility.user_account_mode')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_OUT);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('hasPermission')
      ->with('opt-out of visitors tracking')
      ->willReturn(FALSE);
    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_form_user_form_alter($form, $form_state);

    $this->assertCount(0, $form);
  }

  /**
   * Tests when user has permission, opt out default.
   *
   * @covers visitors_form_user_form_alter
   */
  public function testOptOut(): void {

    $this->settings->expects($this->once())
      ->method('get')
      ->with('visibility.user_account_mode')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_OUT);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);
    $user->expects($this->once())
      ->method('hasPermission')
      ->with('opt-out of visitors tracking')
      ->willReturn(TRUE);
    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_form_user_form_alter($form, $form_state);

    $this->assertCount(2, $form);
    $this->assertArrayHasKey('visitors', $form);
    $this->assertContains('visitors_user_profile_form_submit', $form['actions']['submit']['#submit']);
    $this->assertEquals(VisitorsVisibilityInterface::USER_OPT_OUT, $form['visitors']['user_account_users']['#default_value']);
  }

  /**
   * Tests when user has permission, opt out default with data.
   *
   * @covers visitors_form_user_form_alter
   */
  public function testOptOutWithData(): void {

    $this->settings->expects($this->once())
      ->method('get')
      ->with('visibility.user_account_mode')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_OUT);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);
    $user->expects($this->once())
      ->method('hasPermission')
      ->with('opt-out of visitors tracking')
      ->willReturn(TRUE);
    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $this->userData->expects($this->once())
      ->method('get')
      ->with('visitors', 2)
      ->willReturn([
        'user_account_users' => VisitorsVisibilityInterface::USER_OPT_IN,
      ]);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_form_user_form_alter($form, $form_state);

    $this->assertCount(2, $form);
    $this->assertArrayHasKey('visitors', $form);
    $this->assertContains('visitors_user_profile_form_submit', $form['actions']['submit']['#submit']);
    $this->assertEquals(VisitorsVisibilityInterface::USER_OPT_IN, $form['visitors']['user_account_users']['#default_value']);
  }

  /**
   * Tests when user has permission, opt in default.
   *
   * @covers visitors_form_user_form_alter
   */
  public function testOptIn(): void {

    $this->settings->expects($this->once())
      ->method('get')
      ->with('visibility.user_account_mode')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_IN);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);
    $user->expects($this->once())
      ->method('hasPermission')
      ->with('opt-out of visitors tracking')
      ->willReturn(TRUE);
    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_form_user_form_alter($form, $form_state);

    $this->assertCount(2, $form);
    $this->assertArrayHasKey('visitors', $form);
    $this->assertContains('visitors_user_profile_form_submit', $form['actions']['submit']['#submit']);
    $this->assertEquals(VisitorsVisibilityInterface::USER_OPT_IN, $form['visitors']['user_account_users']['#default_value']);
  }

  /**
   * Tests when user has permission, opt in default with data.
   *
   * @covers visitors_form_user_form_alter
   */
  public function testOptInWithData(): void {

    $this->settings->expects($this->once())
      ->method('get')
      ->with('visibility.user_account_mode')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_IN);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('visitors.config')
      ->willReturn($this->settings);

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);
    $user->expects($this->once())
      ->method('hasPermission')
      ->with('opt-out of visitors tracking')
      ->willReturn(TRUE);
    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $this->userData->expects($this->once())
      ->method('get')
      ->with('visitors', 2)
      ->willReturn([
        'user_account_users' => VisitorsVisibilityInterface::USER_OPT_OUT,
      ]);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_form_user_form_alter($form, $form_state);

    $this->assertCount(2, $form);
    $this->assertArrayHasKey('visitors', $form);
    $this->assertContains('visitors_user_profile_form_submit', $form['actions']['submit']['#submit']);
    $this->assertEquals(VisitorsVisibilityInterface::USER_OPT_OUT, $form['visitors']['user_account_users']['#default_value']);
  }

  /**
   * Tests submit opt out.
   *
   * @covers visitors_user_profile_form_submit
   */
  public function testSubmitOptIn(): void {

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);

    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $this->userData->expects($this->once())
      ->method('set')
      ->with('visitors', 2, 'user_account_users', VisitorsVisibilityInterface::USER_OPT_IN);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('hasValue')
      ->with('user_account_users')
      ->willReturn(TRUE);
    $form_state->expects($this->once())
      ->method('getValue')
      ->with('user_account_users')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_IN);
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_user_profile_form_submit($form, $form_state);

  }

  /**
   * Tests submit opt out.
   *
   * @covers visitors_user_profile_form_submit
   */
  public function testSubmitOptOut(): void {

    $user = $this->createMock('Drupal\user\UserInterface');
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);

    $user_form = $this->createMock('Drupal\user\AccountForm');
    $user_form->expects($this->once())
      ->method('getEntity')
      ->willReturn($user);

    $this->userData->expects($this->once())
      ->method('set')
      ->with('visitors', 2, 'user_account_users', VisitorsVisibilityInterface::USER_OPT_OUT);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('hasValue')
      ->with('user_account_users')
      ->willReturn(TRUE);
    $form_state->expects($this->once())
      ->method('getValue')
      ->with('user_account_users')
      ->willReturn(VisitorsVisibilityInterface::USER_OPT_OUT);
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($user_form);

    visitors_user_profile_form_submit($form, $form_state);

  }

  /**
   * Tests submit missing value.
   *
   * @covers visitors_user_profile_form_submit
   */
  public function testSubmitMissingValue(): void {

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('hasValue')
      ->with('user_account_users')
      ->willReturn(FALSE);

    visitors_user_profile_form_submit($form, $form_state);

  }

}
