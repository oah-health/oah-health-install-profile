<?php

/**
 * @file
 * Contains \Drupal\entity_print\Plugin\PdfEngineInterface
 */

namespace Drupal\entity_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface PdfEngineInterface extends PluginInspectionInterface, PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Add a string of HTML to a new page.
   *
   * @param string $content
   *   The string of HTML to add to a new page.
   *
   * @return $this
   */
  public function addPage($content);

  /**
   * Send the PDF contents to the browser.
   *
   * @param $filename
   *   (optional) The filename if we want to force the browser to download.
   *
   * @throws \Drupal\entity_print\PdfEngineException
   *   Thrown when PDF generation fails.
   */
  public function send($filename = NULL);

  /**
   * Checks if the PDF engine dependencies are available.
   *
   * @return bool
   *   TRUE if this implementation has its dependencies met otherwise FALSE.
   */
  public static function dependenciesAvailable();

  /**
   * Gets the installation instructions for this PDF engine.
   *
   * @return string
   *   A description of how the user can meet the dependencies for this engine.
   */
  public static function getInstallationInstructions();

}
