<?php

/**
 * @file
 * This file provides not working code and exists only to provide examples of
 * using the Entity Print API's.
 *
 * For further documentation see: https://www.drupal.org/node/2430561
 */

use Drupal\entity_print\Plugin\PdfEngineInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * This module is provided to allow modules to add their own CSS files.
 *
 * Note, you can also manage the CSS files from your theme.
 * @see https://www.drupal.org/node/2430561#from-your-theme
 *
 * @param array $render
 *   The renderable array for the PDF.
 * @param object $entity
 *   The entity we're rending.
 */
function hook_entity_print_css_alter(&$render, $entity) {
  // An example of adding two stylesheets for any commerce_order entity.
  if ($entity->bundle() === 'commerce_order') {
    $render['#attached']['library'][] = 'moudle/table';
    $render['#attached']['library'][] = 'moudle/commerce-order';
  }
}

/**
 * Allows other modules to get hold of the pdf object for making changes.
 *
 * Only use this function if you're not able to achieve the right outcome with
 * a custom template and CSS.
 *
 * @param \Drupal\entity_print\Plugin\PdfEngineInterface $pdf_engine
 *   The pdf engine plugin.
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *   The entity we're rending.
 */
function hook_entity_print_pdf_alter(PdfEngineInterface $pdf_engine, ContentEntityInterface $entity) {
  $terms = \Drupal::config('mymodule.settings')->get('terms_and_conditions');
  $pdf_engine->addPage($terms);
}

/**
 * Fired when rendering multiple entities onto one PDF. E.g. PdfDownload action.
 *
 * @param \Drupal\entity_print\Plugin\PdfEngineInterface $pdf_engine
 *   The PDF Engine that is being used.
 * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
 *   An array of content entities that are being rendered.
 */
function hook_entity_print_pdf_multiple_alter(PdfEngineInterface $pdf_engine, $entities) {

}
