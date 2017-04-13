<?php

/**
 * @file
 * Contains \Drupal\entity_print\Tests\EntityPrintTest
 */

namespace Drupal\entity_print\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Entity Print tests.
 *
 * @group Entity Print
 */
class EntityPrintTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'entity_print_test'];

  /**
   * The node object to test against.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // We revoke the access content permission because we use that to test our
    // permissions around entity view.
    user_role_revoke_permissions(AccountInterface::ANONYMOUS_ROLE, ['access content']);
    user_role_revoke_permissions(AccountInterface::AUTHENTICATED_ROLE, ['access content']);

    // Create a content type and a dummy node.
    $this->drupalCreateContentType(array(
      'type' => 'page',
      'name' => 'Page',
    ));
    $this->node = $this->drupalCreateNode();

    // Install our custom theme.
    $theme = 'entity_print_test_theme';
    \Drupal::service('theme_handler')->install([$theme]);
    $this->config('system.theme')
      ->set('default', $theme)
      ->save();
  }

  /**
   * Test that CSS is parsed from our test theme correctly.
   */
  public function testEntityPrintThemeCss() {
    // Create a user and login.
    $account = $this->drupalCreateUser(['entity print access', 'access content'], $this->randomMachineName());
    $this->drupalLogin($account);

    $this->drupalGet('entityprint/node/' . $this->node->id() . '/debug');
    $config = \Drupal::configFactory()->getEditable('entity_print.settings');

    // Test the global CSS is there.
    $this->assertRaw('entity-print.css');
    // Disable the global CSS and test it is not there.
    $config->set('default_css', FALSE)->save();
    $this->drupalGet($this->getUrl());
    $this->assertNoRaw('entity-print.css');

    // Assert that the css files have been parsed out of our test theme.
    $this->assertRaw('entityprint-all.css');
    $this->assertRaw('entityprint-page.css');
    $this->assertRaw('entityprint-node.css');

    // Test that CSS was added from hook_entity_print_css(). See the
    // entity_print_test module for the implementation.
    $this->assertRaw('entityprint-module.css');
  }

  /**
   * Test the access works for viewing the PDF's.
   */
  public function testEntityPrintAccess() {
    // User with entity print access but not content access.
    $account = $this->drupalCreateUser(['entity print access'], $this->randomMachineName());
    $this->drupalLogin($account);
    $this->drupalGet('entityprint/node/' . $this->node->id() . '/debug');
    $this->assertResponse(403, 'User with only the entity print access permission cannot view PDF.');

    // User with access content but not entity print access.
    $account = $this->drupalCreateUser(['access content'], $this->randomMachineName());
    $this->drupalLogin($account);
    $this->drupalGet('entityprint/node/' . $this->node->id() . '/debug');
    $this->assertResponse(403, 'User with access content but no entity print permission cannot view PDF.');

    // User with both entity print and entity view.
    $account = $this->drupalCreateUser(['entity print access', 'access content'], $this->randomMachineName());
    $this->drupalLogin($account);
    $this->drupalGet('entityprint/node/' . $this->node->id() . '/debug');
    $this->assertResponse(200, 'User with both permissions can view the PDF.');

    // User with neither permissions.
    $account = $this->drupalCreateUser([], $this->randomMachineName());
    $this->drupalLogin($account);
    $this->drupalGet('entityprint/node/' . $this->node->id() . '/debug');
    $this->assertResponse(403, 'User with neither permission cannot view the PDF.');

    // Invalid entity type causes access denied.
    $this->drupalGet('entityprint/invalid/' . $this->node->id() . '/debug');
    $this->assertResponse(403, 'Invalid entity type triggers access denied.');

    // Invalid entity id also triggers access denied.
    $this->drupalGet('entityprint/node/invalid-entity-id/debug');
    $this->assertResponse(403, 'Invalid entity id triggers access denied.');
  }

}
