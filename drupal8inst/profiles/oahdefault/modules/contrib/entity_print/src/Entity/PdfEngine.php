<?php

/**
 * @file
 * Contains \Drupal\entity_print\Entity\PdfEngine.
 */

namespace Drupal\entity_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Defines the Pdf Engine specific configuration.
 *
 * @ConfigEntityType(
 *   id = "pdf_engine",
 *   label = @Translation("PDF Engine"),
 *   config_prefix = "pdf_engine",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   admin_permission = "administer entity print",
 *   config_export = {
 *     "id" = "id",
 *     "settings"
 *   }
 * )
 */
class PdfEngine extends ConfigEntityBase implements PdfEngineInterface {

  /**
   * The plugin collection for one PDF engine.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $pdfEnginePluginCollection;

  /**
   * An array of plugin settings for this specific PDF engine.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The id of the Pdf engine plugin.
   *
   * @var string
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings) {
    $this->settings = $settings;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPdfEnginePluginCollection() {
    if (!$this->pdfEnginePluginCollection) {
      $this->pdfEnginePluginCollection = new DefaultSingleLazyPluginCollection($this->getPdfEnginePluginManager(), $this->id, $this->settings);
    }
    return $this->pdfEnginePluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['settings' => $this->getPdfEnginePluginCollection()];
  }

  /**
   * Gets the plugin manager.
   *
   * @return \Drupal\entity_print\Plugin\EntityPrintPluginManager
   *   The plugin manager instance.
   */
  protected function getPdfEnginePluginManager() {
    return \Drupal::service('plugin.manager.entity_print.pdf_engine');
  }

}
