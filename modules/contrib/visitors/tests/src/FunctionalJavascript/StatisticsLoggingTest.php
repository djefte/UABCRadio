<?php

namespace Drupal\Tests\visitors\FunctionalJavascript;

use Drupal\Core\Session\AccountInterface;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\user\Entity\Role;

/**
 * Tests that visitors works.
 *
 * @group visitors
 * @CoversNothing
 */
class StatisticsLoggingTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'visitors', 'language'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Node for tests.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * The counter service.
   *
   * @var \Drupal\visitors\VisitorsCounterInterface
   */
  protected $counterService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('visitors.config')
      ->set('counter.enabled', 1)
      ->save();

    Role::load(AccountInterface::ANONYMOUS_ROLE)
      ->grantPermission('view visitors counter')
      ->save();

    // Add another language to enable multilingual path processor.
    ConfigurableLanguage::create([
      'id' => 'xx',
      'label' => 'Test language',
    ])->save();
    $this->config('language.negotiation')->set('url.prefixes.en', 'en')->save();

    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
    $this->node = $this->drupalCreateNode();

    $this->counterService = $this->container->get('visitors.counter');
  }

  /**
   * Tests that statistics works with different addressing variants.
   *
   * @coversNothing
   */
  public function testLoggingPage() {
    // At the first request, the page does not contain statistics counter.
    $this->assertNull($this->getStatisticsCounter('node/1'));
    $this->assertSame(1, $this->getStatisticsCounter('node/1'));
    $this->assertSame(2, $this->getStatisticsCounter('en/node/1'));
    $this->assertSame(3, $this->getStatisticsCounter('en/node/1'));
    $this->assertSame(4, $this->getStatisticsCounter('index.php/node/1'));
    $this->assertSame(5, $this->getStatisticsCounter('index.php/node/1'));
    $this->assertSame(6, $this->getStatisticsCounter('index.php/en/node/1'));
    $this->assertSame(7, $this->getStatisticsCounter('index.php/en/node/1'));
  }

  /**
   * Gets counter of views by path.
   *
   * @param string $path
   *   A path to node.
   *
   * @return int|null
   *   A counter of views. Returns NULL if the page does not contain statistics.
   */
  protected function getStatisticsCounter($path) {
    $nid = $this->node->id();
    $count = $this->counterService->fetchView('node', $nid);
    $count = $count ? $count->getTotalCount() : 0;
    $this->drupalGet($path);

    // Wait while visitors send ajax request.
    $i = 0;
    do {
      sleep(1);
      $count_new = $this->counterService->fetchView('node', $nid);
      $count_new = $count_new ? $count_new->getTotalCount() : 0;
      $i += 1;
    } while ($count_new === $count && $i < 180);

    // Resaving the node to call the hook_node_links_alter(), which is used to
    // update information on the page. See visitors_node_links_alter().
    $this->node->save();

    $field_counter = $this->getSession()->getPage()->find('css', '.links li');
    return $field_counter ? (int) explode(' ', $field_counter->getText())[0] : NULL;
  }

}
