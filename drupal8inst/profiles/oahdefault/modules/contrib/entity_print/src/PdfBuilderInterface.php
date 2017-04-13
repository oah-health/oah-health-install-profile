<?php

/**
 * @file
 * Contains \Drupal\entity_print\PdfBuilderInterface
 */

namespace Drupal\entity_print;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\entity_print\Plugin\PdfEngineInterface;

/**
 * Interface for the PDF builder service.
 */
interface PdfBuilderInterface {

  /**
   * Render any content entity as a PDF.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to render.
   * @param \Drupal\entity_print\Plugin\PdfEngineInterface $pdf_engine
   *   The plugin id of the PDF engine to use.
   * @param bool $force_download
   *   (optional) TRUE to try and force the PDF to be downloaded rather than opened.
   * @param bool $use_default_css
   *   (optional) TRUE if you want the default CSS included, otherwise FALSE.
   *
   * @return string
   *   FALSE or the PDF content will be sent to the browser.
   */
  public function getEntityRenderedAsPdf(ContentEntityInterface $entity, PdfEngineInterface $pdf_engine, $force_download = FALSE, $use_default_css = TRUE);

  /**
   * Render any content entity as a PDF.
   *
   * @param array $entities
   *   An array of content entities to render, 1 per page.
   * @param \Drupal\entity_print\Plugin\PdfEngineInterface $pdf_engine
   *   The plugin id of the PDF engine to use.
   * @param bool $force_download
   *   (optional) TRUE to try and force the PDF to be downloaded rather than opened.
   * @param bool $use_default_css
   *   (optional) TRUE if you want the default CSS included, otherwise FALSE.
   *
   * @return string
   *   FALSE or the PDF content will be sent to the browser.
   */
  public function getMultipleEntitiesRenderedAsPdf(array $entities, PdfEngineInterface $pdf_engine, $force_download = FALSE, $use_default_css = TRUE);

  /**
   * Get a HTML version of the entity as used for the PDF rendering.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to render.
   * @param bool $use_default_css
   *   TRUE if you want the default CSS included, otherwise FALSE.
   * @param bool $optimize_css
   *   TRUE if you the CSS should be compressed otherwise FALSE.
   *
   * @return string
   *   The rendered HTML for this entity, the same as what is used for the PDF.
   */
  public function getEntityRenderedAsHtml(ContentEntityInterface $entity, $use_default_css = TRUE, $optimize_css = TRUE);
}
