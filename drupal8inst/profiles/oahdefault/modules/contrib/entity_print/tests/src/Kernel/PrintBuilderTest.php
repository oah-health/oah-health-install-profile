<?php

namespace Drupal\Tests\entity_print\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\NodeCreationTrait;

/**
 * @coversDefaultClass \Drupal\entity_print\PrintBuilder
 * @group entity_print
 */
class PrintBuilderTest extends KernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'user', 'node', 'filter', 'entity_print', 'entity_print_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['system', 'filter']);
    $this->container->get('theme_handler')->install(['stark']);
    $node_type = NodeType::create(['name' => 'Page', 'type' => 'page']);
    $node_type->setDisplaySubmitted(FALSE);
    $node_type->save();
  }

  /**
   * @covers ::deliverPrintable
   * @dataProvider outputtedFileDataProvider
   */
  public function testOutputtedFilename($print_engine_id, $file_name) {
    $print_engine = $this->container->get('plugin.manager.entity_print.print_engine')->createInstance($print_engine_id);
    $node = $this->createNode(['title' => 'myfile']);

    ob_start();
    $this->container->get('entity_print.print_builder')->deliverPrintable([$node], $print_engine, TRUE);
    $contents = ob_get_contents();
    ob_end_clean();
    $this->assertTrue(strpos($contents, $file_name) !== FALSE, "The $file_name file was found in $contents");
  }

  /**
   * Provides a data provider for testOutputtedFilename().
   */
  public function outputtedFileDataProvider() {
    return [
      'PDF file' => ['testprintengine', 'myfile.pdf'],
      'Word doc file' => ['test_word_print_engine', 'myfile.docx'],
    ];
  }

  /**
   * Ensure when not using force download we do not get a filename.
   */
  public function testForceDownload() {
    $print_engine = $this->getMock('Drupal\entity_print\Plugin\PrintEngineInterface');
    $export_type = $this->getMock('Drupal\entity_print\Plugin\ExportTypeInterface');
    $print_engine
      ->expects($this->once())
      ->method('send')
      ->with(NULL);
    $print_engine
      ->expects($this->any())
      ->method('getExportType')
      ->willReturn($export_type);
    $node = $this->createNode(['title' => 'myfile']);
    $this->container->get('entity_print.print_builder')->deliverPrintable([$node], $print_engine, FALSE);
  }

  /**
   * @covers ::deliverPrintable
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage You must pass at least 1 entity
   */
  public function testNoEntities() {
    $print_engine = $this->container->get('plugin.manager.entity_print.print_engine')->createInstance('testprintengine');
    $this->container->get('entity_print.print_builder')->deliverPrintable([], $print_engine, TRUE);
  }

  /**
   * Test that CSS is parsed from our test theme correctly.
   */
  public function testEntityPrintThemeCss() {
    $theme = 'entity_print_test_theme';
    $this->container->get('theme_handler')->install([$theme]);
    $this->config('system.theme')
      ->set('default', $theme)
      ->save();
    $node = $this->createNode();

    // Test the global CSS is there.
    $html = $this->container->get('entity_print.print_builder')->printHtml($node, TRUE, FALSE);
    $this->assertContains('entity-print.css', $html);

    // Disable the global CSS and test it is not there.
    $html = $this->container->get('entity_print.print_builder')->printHtml($node, FALSE, FALSE);
    $this->assertNotContains('entity-print.css', $html);

    // Assert that the css files have been parsed out of our test theme.
    $this->assertContains('entityprint-all.css', $html);
    $this->assertContains('entityprint-page.css', $html);
    $this->assertContains('entityprint-node.css', $html);

    // Test that CSS was added from hook_entity_print_css(). See the
    // entity_print_test module for the implementation.
    $this->assertContains('entityprint-module.css', $html);
  }

}
