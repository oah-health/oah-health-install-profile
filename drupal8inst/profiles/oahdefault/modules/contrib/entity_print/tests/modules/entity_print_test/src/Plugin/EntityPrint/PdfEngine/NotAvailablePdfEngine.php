<?php

/**
 * @file
 * Contains \Drupal\entity_print_test\Plugin\EntityPrint\PdfEngine\NotAvailablePdfEngine
 */

namespace Drupal\entity_print_test\Plugin\EntityPrint\PdfEngine;

use Drupal\entity_print\Plugin\PdfEngineBase;

class NotAvailablePdfEngine extends PdfEngineBase {

  /**
   * {@inheritdoc}
   */
  public function send($filename = NULL) {}

  /**
   * {@inheritdoc}
   */
  public function getError() {}

  /**
   * {@inheritdoc}
   */
  public function addPage($content) {}

  /**
   * {@inheritdoc}
   */
  public static function dependenciesAvailable() {
    return FALSE;
  }

}
