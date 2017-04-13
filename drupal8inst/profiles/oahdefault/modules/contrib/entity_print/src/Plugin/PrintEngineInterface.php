<?php

namespace Drupal\entity_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface PrintEngineInterface extends PluginInspectionInterface, PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Gets the export type.
   *
   * @return \Drupal\entity_print\Plugin\ExportTypeInterface
   *   The export type interface.
   */
  public function getExportType();

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
   * Send the Print contents to the browser.
   *
   * @param $filename
   *   (optional) The filename if we want to force the browser to download.
   *
   * @throws \Drupal\entity_print\PrintEngineException
   *   Thrown when Print generation fails.
   */
  public function send($filename = NULL);

  /**
   * Gets the binary data for the printed document.
   *
   * @return mixed
   *   The binary data.
   */
  public function getBlob();

  /**
   * Checks if the Print engine dependencies are available.
   *
   * @return bool
   *   TRUE if this implementation has its dependencies met otherwise FALSE.
   */
  public static function dependenciesAvailable();

  /**
   * Gets the installation instructions for this Print engine.
   *
   * @return string
   *   A description of how the user can meet the dependencies for this engine.
   */
  public static function getInstallationInstructions();

}
