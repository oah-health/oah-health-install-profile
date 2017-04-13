<?php

namespace Drupal\Tests\entity_print_views\Kernel;

use Drupal\entity_print\Renderer\RendererBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Views;

/**
 * @group entity_print_views
 */
class ViewsRendererTest extends KernelTestBase {

  /**
   * An array of modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'views',
    'node',
    'filter',
    'entity_print',
    'entity_print_views',
    'entity_print_views_test_views',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['system', 'entity_print_views_test_views']);
  }

  /**
   * Test filename generation for the views renderer.
   */
  public function testGenerateFilename() {
    $view = Views::getView('my_test_view');
    $view->setDisplay('page_1');
    $renderer = $this->container->get('entity_print.renderer.view');
    $this->assertSame('My Test view', $renderer->getFilename([$view->storage]));

    $view = Views::getView('my_test_view');
    $view->setDisplay('block_1');
    $renderer = $this->container->get('entity_print.renderer.view');
    $this->assertSame('My Test view block', $renderer->getFilename([$view->storage]));

    $view->setTitle(' ');
    $this->assertSame(RendererBase::DEFAULT_FILENAME, $renderer->getFilename([$view->storage]));
  }

}
