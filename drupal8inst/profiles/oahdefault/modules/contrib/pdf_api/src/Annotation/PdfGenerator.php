<?php

/**
 * @file
 * Contains \Drupal\pdf_api\Annotation\PdfGenerator.
 */

namespace Drupal\pdf_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an PDF generator annotation object.
 *
 * @Annotation
 */
class PdfGenerator extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the module providing the generator.
   *
   * @var string
   */
  public $module;

  /**
   * The human-readable name of the generator.
   *
   * This is used as an administrative summary of what the generator does.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * Additional administrative information about the generator's behavior.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

}
