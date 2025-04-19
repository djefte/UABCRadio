<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\user\RoleInterface;
use Drupal\visitors\Form\Settings;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests settings form.
 *
 * @group visitors
 * @coversDefaultClass \Drupal\visitors\Form\Settings
 * @uses \Drupal\visitors\Form\Settings
 */
class SettingsTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * The form under test.
   *
   * @var \Drupal\visitors\Form\Settings
   */
  protected $form;

  /**
   * The extension theme list.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $extensionThemeList;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

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

    $this->extensionThemeList = $this->createMock('Drupal\Core\Extension\ThemeExtensionList');
    $container->set('extension.list.theme', $this->extensionThemeList);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $unrouted_url_assembler = $this->createMock('Drupal\Core\Utility\UnroutedUrlAssemblerInterface');
    $container->set('unrouted_url_assembler', $unrouted_url_assembler);

    $url_generator = $this->createMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $container->set('url_generator', $url_generator);

    $messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $messenger);

    \Drupal::setContainer($container);

    $this->form = Settings::create($container);
  }

  /**
   * Test the getFormId method.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('visitors_admin_settings', $this->form->getFormId());
  }

  /**
   * Test the buildForm method.
   *
   * @covers ::buildForm
   * @covers ::roleOptions
   * @covers ::entityTypes
   */
  public function testBuildForm() {
    $user_role_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->with('user_role')
      ->willReturn($user_role_storage);
    $anonymous_role = $this->createMock('Drupal\user\RoleInterface');
    $anonymous_role->expects($this->exactly(1))
      ->method('id')
      ->willReturn(RoleInterface::ANONYMOUS_ID);
    $anonymous_role->expects($this->exactly(2))
      ->method('label')
      ->willReturn('Anonymous');
    $authenticate_role = $this->createMock('Drupal\user\RoleInterface');
    $authenticate_role->expects($this->exactly(1))
      ->method('id')
      ->willReturn(RoleInterface::AUTHENTICATED_ID);
    $authenticate_role->expects($this->exactly(2))
      ->method('label')
      ->willReturn('Authenticated');
    $administrator_role = $this->createMock('Drupal\user\RoleInterface');
    $administrator_role->expects($this->exactly(1))
      ->method('id')
      ->willReturn('administrator');
    $administrator_role->expects($this->exactly(2))
      ->method('label')
      ->willReturn('Administrator');
    $user_roles = [$anonymous_role, $authenticate_role, $administrator_role];
    $user_role_storage->expects($this->exactly(2))
      ->method('loadMultiple')
      ->willReturn($user_roles);

    $config = $this->createMock('Drupal\Core\Config\Config');
    $config
      ->expects($this->exactly(15))
      ->method('get');
    $system_config = $this->createMock('Drupal\Core\Config\Config');
    $system_config->expects($this->exactly(2))
      ->method('get')
      ->willReturn('bartik', 'claro');
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.theme')
      ->willReturn($system_config);

    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($config);

    $bartik_theme = $this->createMock('\Drupal\Core\Extension\Extension');
    $bartik_theme->info = ['name' => 'bartik'];
    // @phpstan-ignore-next-line
    $bartik_theme->status = 1;

    $claro_theme = $this->createMock('\Drupal\Core\Extension\Extension');
    $claro_theme->info = ['name' => 'claro'];
    // @phpstan-ignore-next-line
    $claro_theme->status = 1;
    $this->extensionThemeList->expects($this->once())
      ->method('getList')
      ->willReturn(['bartik' => $bartik_theme, 'claro' => $claro_theme]);

    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request_stack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');

    $node_definition = $this->createMock('Drupal\Core\Entity\ContentEntityType');
    $node_type_definition = $this->createMock('Drupal\Core\Config\Entity\ConfigEntityType');

    $this->entityTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn([$node_definition, $node_type_definition]);

    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->form->buildForm([], $form_state);

    $this->assertIsArray($form);
    $this->assertArrayHasKey('actions', $form);
    $this->assertArrayHasKey('#theme', $form);
    $this->assertArrayHasKey('theme', $form);
    $this->assertArrayHasKey('tracking_scope', $form);
    $this->assertArrayHasKey('miscellaneous', $form);
    $this->assertArrayHasKey('entity', $form);
  }

  /**
   * Test the buildForm method with different options.
   *
   * @covers ::buildForm
   */
  public function testBuildFormOtherSettings() {
    $user_role_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->with('user_role')
      ->willReturn($user_role_storage);
    $anonymous_role = $this->createMock('Drupal\user\RoleInterface');
    $anonymous_role->expects($this->exactly(1))
      ->method('id')
      ->willReturn(RoleInterface::ANONYMOUS_ID);
    $anonymous_role->expects($this->exactly(2))
      ->method('label')
      ->willReturn('Anonymous');
    $authenticate_role = $this->createMock('Drupal\user\RoleInterface');
    $authenticate_role->expects($this->exactly(1))
      ->method('id')
      ->willReturn(RoleInterface::AUTHENTICATED_ID);
    $authenticate_role->expects($this->exactly(2))
      ->method('label')
      ->willReturn('Authenticated');
    $administrator_role = $this->createMock('Drupal\user\RoleInterface');
    $administrator_role->expects($this->exactly(1))
      ->method('id')
      ->willReturn('administrator');
    $administrator_role->expects($this->exactly(2))
      ->method('label')
      ->willReturn('Administrator');
    $user_roles = [$anonymous_role, $authenticate_role, $administrator_role];
    $user_role_storage->expects($this->exactly(2))
      ->method('loadMultiple')
      ->willReturn($user_roles);

    $config = $this->createMock('Drupal\Core\Config\Config');
    $config
      ->expects($this->exactly(15))
      ->method('get')
      ->willReturnMap([
        ['flush_log_timer', 0],
        ['bot_retention_log', 0],
        ['items_per_page', 10],
        ['theme', 'admin'],
        ['disable_tracking', FALSE],
        ['visibility.request_path_mode', 1],
        ['script_type', 'minified'],
      ]);
    $system_config = $this->createMock('Drupal\Core\Config\Config');
    $system_config->expects($this->exactly(2))
      ->method('get')
      ->willReturn('bartik', 'claro');
    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('system.theme')
      ->willReturn($system_config);

    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($config);

    $bartik_theme = $this->createMock('\Drupal\Core\Extension\Extension');
    $bartik_theme->info = ['name' => 'bartik'];
    // @phpstan-ignore-next-line
    $bartik_theme->status = 1;

    $claro_theme = $this->createMock('\Drupal\Core\Extension\Extension');
    $claro_theme->info = ['name' => 'claro'];
    // @phpstan-ignore-next-line
    $claro_theme->status = 1;
    $this->extensionThemeList->expects($this->once())
      ->method('getList')
      ->willReturn(['bartik' => $bartik_theme, 'claro' => $claro_theme]);

    $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request_stack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');

    $node_definition = $this->createMock('Drupal\Core\Entity\ContentEntityType');
    $node_type_definition = $this->createMock('Drupal\Core\Config\Entity\ConfigEntityType');

    $this->entityTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn([$node_definition, $node_type_definition]);

    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->form->buildForm([], $form_state);

    $this->assertIsArray($form);
    $this->assertArrayHasKey('actions', $form);
    $this->assertArrayHasKey('#theme', $form);
    $this->assertArrayHasKey('theme', $form);
    $this->assertArrayHasKey('tracking_scope', $form);
    $this->assertArrayHasKey('miscellaneous', $form);
    $this->assertArrayHasKey('entity', $form);
  }

  /**
   * Test the submitForm method.
   *
   * @covers ::submitForm
   * @covers ::getEditableConfigNames
   */
  public function testSubmitForm() {
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('getValues')
      ->willReturn([
        'theme' => 'theme',
        'items_per_page' => 'items_per_page',
        'flush_log_timer' => 'flush_log_timer',
        'bot_retention_log' => 'bot_retention_log',
        'visitors_trackuserid' => 'visitors_trackuserid',
        'counter_enabled' => 'counter_enabled',
        'disable_cookies' => '0',
        'visitors_disable_tracking' => 'visitors_disable_tracking',
        'visitors_visibility_request_path_mode' => 'visitors_visibility_request_path_mode',
        'visitors_visibility_request_path_pages' => 'visitors_visibility_request_path_pages',
        'visitors_visibility_user_account_mode' => 'visitors_visibility_user_account_mode',
        'visitors_visibility_user_role_mode' => 'visitors_visibility_user_role_mode',
        'visitors_visibility_user_role_roles' => ['visitors_visibility_user_role_roles'],
        'visibility_exclude_user1' => 'visibility_exclude_user1',
      ]);

    $config = $this->createMock('Drupal\Core\Config\Config');
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($config);

    $config->method('set')
      ->willReturnSelf();
    $config->expects($this->once())
      ->method('save');

    $this->form->submitForm($form, $form_state);
  }

  /**
   * Test the create method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $form = Settings::create($container);
    $this->assertInstanceOf('Drupal\visitors\Form\Settings', $form);
  }

  /**
   * Test the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $form = new Settings(
      $this->extensionThemeList,
      $this->entityTypeManager,
    );
    $this->assertInstanceOf(Settings::class, $form);
  }

}
