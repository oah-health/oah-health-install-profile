<?php

/**
 * @file
 * Contains \Drupal\entity_print\Annotation\PdfEngine.
 */

namespace Drupal\entity_print\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * The PdfEngine annotation.
 *
 * @Annotation
 */
class PdfEngine extends Plugin {

  /**
   * The unique Id of the Pdf engine implementation.
   *
   * @var string
   */
  public $id;

  /**
   * The human readable name of the Pdf engine implementation.
   *
   * @var string
   */
  public $label;

}
