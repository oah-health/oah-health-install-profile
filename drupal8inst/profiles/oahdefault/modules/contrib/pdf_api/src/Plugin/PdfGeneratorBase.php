<?php

/**
 * @file
 * Contains \Drupal\printable\Plugin\PrintableFormatBase.
 */

namespace Drupal\pdf_api\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a base class for PDF generator plugins.
 */
abstract class PdfGeneratorBase extends PluginBase implements PdfGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * Get the dimensions of a given page size.
   *
   * @param string $page_size
   *   The page size to get the dimensions for (e.g. A4).
   *
   * @return array|FALSE
   *   An array with the keys "width" and "height" that contain the width and
   *   height dimensions respectively. False if the page size is unknown.
   */
  protected function getPageDimensions($page_size) {
    if ($this->isValidPageSize($page_size)) {
      $page_sizes = $this->pageSizes();
      return $page_sizes[$page_size];
    }
  }

  /**
   * Checks if a given page size is valid.
   *
   * @param string $page_size
   *   The page size to check.
   *
   * @return bool
   *   TRUE if the page size is valid, FALSE if not.
   */
  protected function isValidPageSize($page_size) {
    return array_key_exists($page_size, $this->pageSizes());
  }

  /**
   * Get an array of all valid page sizes, keyed by the page size name.
   *
   * @return array
   *   An array of page sizes with the values an array of width and height and
   *   keys the page size name (e.g. A4).
   */
  protected function pageSizes() {
    return array(
      'A0' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A1' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A2' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A3' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A4' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A5' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A6' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A7' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A8' => array(
        'width' => 0,
        'height' => 0,
      ),
      'A9' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B0' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B1' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B10' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B2' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B3' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B4' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B5' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B6' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B7' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B8' => array(
        'width' => 0,
        'height' => 0,
      ),
      'B9' => array(
        'width' => 0,
        'height' => 0,
      ),
      'C5E' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Comm10E' => array(
        'width' => 0,
        'height' => 0,
      ),
      'DLE' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Executive' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Folio' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Ledger' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Legal' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Letter' => array(
        'width' => 0,
        'height' => 0,
      ),
      'Tabloid' => array(
        'width' => 0,
        'height' => 0,
      ),
    );
  }

}
