<?php

namespace Drupal\Tests\entity_print\Kernel;

use Drupal\entity_print\Controller\EntityPrintController;
use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\simpletest\UserCreationTrait;

/**
 * @coversDefaultClass \Drupal\entity_print\Controller\EntityPrintController
 * @group entity_print
 */
class EntityPrintAccessTest extends KernelTestBase {

  use UserCreationTrait;
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  public static $modules = ['system', 'user', 'node', 'field', 'text', 'filter', 'entity_print'];

  /**
   * The node object to test against.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installSchema('system', ['router', 'sequences', 'key_value_expire']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['system', 'node', 'filter', 'field']);

    // Discard user 1 which causes havoc with access tests.
    $this->createUser();
    $this->createContentType(['type' => 'page', 'name' => 'Page']);
    $this->createContentType(['type' => 'article', 'name' => 'Article']);
  }

  /**
   * @covers ::checkAccess
   * @dataProvider accessPermissionsDataProvider
   */
  public function testAccessPermissions($permissions, $expected_access) {
    $id = $this->createNode()->id();
    $account = $this->createUser($permissions);
    $this->container->get('current_user')->setAccount($account);
    $controller = EntityPrintController::create($this->container);
    $this->assertSame($expected_access, $controller->checkAccess('pdf', 'node', $id)->isAllowed());
  }

  /**
   * Data provider to test access with different combinations of permissions.
   */
  public function accessPermissionsDataProvider() {
    return [
      'Permission "bypass entity print access" only cannot view PDF.' => [['bypass entity print access'], FALSE],
      'Permission "access content" only cannot view PDF.' => [['access content'], FALSE],
      'Permission "access content" and "bypass entity print access" can view PDF.' => [['bypass entity print access', 'access content'], TRUE],
      'Per entity type permissions allow access.' => [['entity print access type node', 'access content'], TRUE],
      'Per bundle permissions allow access.' => [['entity print access bundle page', 'access content'], TRUE],
      'Incorrect entity type permission cannot access' => [['entity print access type user', 'access content'], FALSE],
      'Incorrect bundle permissions cannot access' => [['entity print access bundle article', 'access content'], FALSE],
      'No permissions cannot access' => [[], FALSE],

    ];
  }

  /**
   * @covers ::checkAccess
   * @dataProvider invalidRouteParametersDataProvider
   */
  public function testInvalidRouteParameters($entity_type, $entity_id, $export_type) {
    $entity_id = $entity_id ?: $this->createNode()->id();
    $account = $this->createUser(['bypass entity print access', 'access content']);
    $this->assertSame(FALSE, $this->checkAccess($account, $entity_type, $entity_id, $export_type));
  }

  /**
   * Data provider for invalid route params.
   */
  public function invalidRouteParametersDataProvider() {
    return [
      'Invalid entity type triggers access denied.' => ['invalid', FALSE, 'pdf'],
      'Invalid entity id triggers access denied.' => ['node', 'invalid-entity-id', 'pdf'],
      'Invalid export type triggers access denied.' => ['node', FALSE, 'invalid-export-type'],
    ];
  }

  /**
   * Test access for a non-node entity type.
   *
   * @covers ::checkAccess
   */
  public function testSecondaryEntityTypeAccess() {
    // User with print entity type user permissions and entity view.
    $account = $this->createUser(array('entity print access type user', 'access content'));
    $this->assertTrue($this->checkAccess($account, 'user', $account->id()), 'User with "type user" permission and access content permission is allowed to see the content.');
  }

  /**
   * Checks access for the user to print the view.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account we're checking against.
   * @param string $entity_type
   *   The entity type string
   * @param string $entity_id
   *   The entity id.
   * @param string $export_type
   *   The export type.
   *
   * @return bool
   *   TRUE if the user has access otherwise FALSE.
   */
  protected function checkAccess($account, $entity_type, $entity_id, $export_type = 'pdf') {
    $this->container->get('current_user')->setAccount($account);
    $controller = EntityPrintController::create($this->container);
    return $controller->checkAccess($export_type, $entity_type, $entity_id)->isAllowed();
  }

}
