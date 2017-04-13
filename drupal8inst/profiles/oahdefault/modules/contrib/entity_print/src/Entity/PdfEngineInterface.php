<?php
/**
 * @file
 * Contains \Drupal\entity_print\Entity\PdfEngineInterface.
 */

namespace Drupal\entity_print\Entity;

use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * An interface for our config entity storage for PDF engines.
 */
interface PdfEngineInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Gets a single lazy plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The plugin collection for our PDF Engine plugin.
   */
  public function getPdfEnginePluginCollection();

  /**
   * Gets the PDF engine settings.
   *
   * @return array
   *   The PDF Engine settings.
   */
  public function getSettings();

  /**
   * Sets the PDF engine settings.
   *
   * @return $this
   *   The config entity.
   */
  public function setSettings(array $settings);

}
