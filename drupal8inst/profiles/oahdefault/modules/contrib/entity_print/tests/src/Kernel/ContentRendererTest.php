<?php

namespace Drupal\Tests\entity_print\Kernel;

use Drupal\entity_print\Renderer\RendererBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\NodeCreationTrait;

/**
 * @coversDefaultClass \Drupal\entity_print\Renderer\ContentEntityRenderer
 * @group entity_print
 */
class ContentRendererTest extends KernelTestBase {

  use NodeCreationTrait;

  /**
   * An array of modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'node', 'filter', 'entity_print'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(['system', 'filter']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
  }

  /**
   * Test filename generation for the content entities.
   *
   * @covers ::getFilename
   * @dataProvider generateFilenameDataProvider
   */
  public function testGenerateFilename($title, $expected_filename) {
    $node = $this->createNode(['title' => $title]);
    $renderer = $this->container->get('entity_print.renderer.content');
    $this->assertEquals($expected_filename, $renderer->getFilename([$node]));
  }

  /**
   * Get the data for testing filename generation.
   *
   * @return array
   *   An array of data rows for testing filename generation.
   */
  public function generateFilenameDataProvider() {
    return [
      // $node_title, $expected_filename.
      ['Random Node Title', 'Random Node Title'],
      ['Title -=with special chars&*#', 'Title with special chars'],
      ['Title 5 with Nums 2', 'Title 5 with Nums 2'],
      // Ensure invalid filenames get the default.
      [' ', RendererBase::DEFAULT_FILENAME],
    ];
  }

}
