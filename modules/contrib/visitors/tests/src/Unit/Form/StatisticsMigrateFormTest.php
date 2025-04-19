<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\visitors\Form\StatisticsMigrateForm;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests settings form.
 *
 * @coversDefaultClass \Drupal\visitors\Form\StatisticsMigrateForm
 *
 * @group visitors
 */
class StatisticsMigrateFormTest extends UnitTestCase {


  /**
   * The form under test.
   *
   * @var \Drupal\visitors\Form\StatisticsMigrateForm
   */
  protected $form;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $httpClient;

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
   * The request stack.
   *
   * @var \Drupal\Core\Session\SessionConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $sessionConfiguration;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleInstaller;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->currentUser = $this->createMock('Drupal\Core\Session\AccountInterface');
    $container->set('current_user', $this->currentUser);

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $container->set('module_handler', $this->moduleHandler);

    $this->httpClient = $this->createMock('GuzzleHttp\Client');
    $container->set('http_client', $this->httpClient);

    $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
    $container->set('session', $session);

    $this->sessionConfiguration = $this->createMock('Drupal\Core\Session\SessionConfigurationInterface');
    $container->set('session_configuration', $this->sessionConfiguration);

    $this->extensionThemeList = $this->createMock('Drupal\Core\Extension\ThemeExtensionList');
    $container->set('extension.list.theme', $this->extensionThemeList);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->configFactory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $container->set('config.factory', $this->configFactory);

    $this->requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    $container->set('request_stack', $this->requestStack);

    $unrouted_url_assembler = $this->createMock('Drupal\Core\Utility\UnroutedUrlAssemblerInterface');
    $container->set('unrouted_url_assembler', $unrouted_url_assembler);

    $url_generator = $this->createMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $container->set('url_generator', $url_generator);

    $this->messenger = $this->createMock('Drupal\Core\Messenger\MessengerInterface');
    $container->set('messenger', $this->messenger);

    $this->database = $this->createMock('Drupal\Core\Database\Connection');
    $container->set('database', $this->database);

    $this->moduleInstaller = $this->createMock('Drupal\Core\Extension\ModuleInstallerInterface');
    $container->set('module_installer', $this->moduleInstaller);

    \Drupal::setContainer($container);

    $this->form = StatisticsMigrateForm::create($container);
  }

  /**
   * Test the getFormId method.
   *
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('visitors_statistics_migrate', $this->form->getFormId());
  }

  /**
   * Test the getQuestion method.
   *
   * @covers ::getQuestion
   */
  public function testGetQuestion() {
    $this->assertEquals('Migrate statistics node view count?', $this->form->getQuestion());
  }

  /**
   * Test the getDescription method.
   *
   * @covers ::getDescription
   */
  public function testGetDescription() {
    $this->assertEquals('This will migrate the Statistics view count to Visitors, and uninstall Statistics.', $this->form->getDescription());
  }

  /**
   * Test the getCancelUrl method.
   *
   * @covers ::getCancelUrl
   */
  public function testGetCancelUrl() {
    $this->assertEquals('visitors.settings', $this->form->getCancelUrl()->getRouteName());
  }

  /**
   * Test the submitForm method.
   *
   * @covers ::delete
   */
  public function testDelete() {

    $delete = $this->createMock('Drupal\Core\Database\Query\Delete');
    $delete->expects($this->once())
      ->method('condition')
      ->with('entity_type', 'node')
      ->willReturnSelf();

    $delete->expects($this->once())
      ->method('execute')
      ->willReturn(10);

    $this->database->expects($this->once())
      ->method('delete')
      ->with('visitors_counter')
      ->willReturn($delete);

    $this->form::delete();
  }

  /**
   * Test the insert method.
   *
   * @covers ::insert
   */
  public function testInsert() {

    $select = $this->createMock('Drupal\Core\Database\Query\Select');
    $select->expects($this->once())
      ->method('addExpression')
      ->with("'node'", 'entity_type');

    $select->expects($this->exactly(4))
      ->method('addField')
      ->willReturnMap([
        ['s', 'nid', 'entity_id', NULL],
        ['s', 'totalcount', 'total', NULL],
        ['s', 'daycount', 'today', NULL],
        ['s', 'timestamp', 'timestamp', NULL],
      ]);

    $insert = $this->createMock('Drupal\Core\Database\Query\Insert');
    $insert->expects($this->once())
      ->method('fields')
      ->with([
        'entity_id',
        'total',
        'today',
        'timestamp',
        'entity_type',
      ])
      ->willReturnSelf();

    $insert->expects($this->once())
      ->method('from')
      ->with($select)
      ->willReturnSelf();

    $insert->expects($this->once())
      ->method('execute')
      ->willReturn(10);

    $this->database->expects($this->once())
      ->method('insert')
      ->with('visitors_counter')
      ->willReturn($insert);

    $this->database->expects($this->once())
      ->method('select')
      ->with('node_counter', 's')
      ->willReturn($select);

    $this->form::insert();
  }

  /**
   * Test the uninstallStatistics method.
   *
   * @covers ::uninstallStatistics
   */
  public function testUninstallStatistics() {

    $this->messenger->expects($this->once())
      ->method('addStatus')
      ->with('The Statistics module has been uninstalled.');

    $this->moduleInstaller->expects($this->once())
      ->method('uninstall')
      ->with(['statistics'])
      ->willReturn(TRUE);

    $this->form::uninstallStatistics();
  }

  /**
   * Test the uninstallStatistics method.
   *
   * @covers ::uninstallStatistics
   */
  public function testUninstallStatisticsFails() {

    $this->messenger->expects($this->once())
      ->method('addError')
      ->with('The Statistics module could not be uninstalled.');

    $this->moduleInstaller->expects($this->once())
      ->method('uninstall')
      ->with(['statistics'])
      ->willReturn(FALSE);

    $this->form::uninstallStatistics();
  }

  /**
   * Test the enableVisitorsLogging method.
   *
   * @covers ::enableVisitorsLogging
   */
  public function testEnableVisitorsLogging() {
    $config = $this->createMock('Drupal\Core\Config\Config');
    $this->configFactory->expects($this->once())
      ->method('getEditable')
      ->with('visitors.config')
      ->willReturn($config);

    $config->expects($this->once())
      ->method('set')
      ->with('counter.enabled', TRUE)
      ->willReturnSelf();

    $config->expects($this->once())
      ->method('save');

    $this->form::enableVisitorsLogging();
  }

  /**
   * Test the submitForm method.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('setRedirectUrl');

    $this->form->submitForm($form, $form_state);
  }

}
