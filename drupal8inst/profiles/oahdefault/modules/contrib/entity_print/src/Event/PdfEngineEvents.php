<?php

namespace Drupal\entity_print\Event;

/**
 * The events related to PDF Engines.
 */
final class PdfEngineEvents {

  /**
   * Name of the event fired when retrieving a PDF engine configuration.
   *
   * This event allows you to change the configuration of a PDF Engine
   * implementation right before the plugin manager creates the plugin instance.
   *
   * @Event
   *
   * @see \Symfony\Component\EventDispatcher\GenericEvent
   */
  const CONFIGURATION_ALTER = 'entity_print.pdf_engine.configuration_alter';

}
