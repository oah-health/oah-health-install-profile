<?php

namespace Drupal\Tests\entity_print\Kernel;

use Drupal\entity_print\Event\PrintHtmlAlterEvent;
use Drupal\entity_print\PrintEngineException;

class PrintHtmlAlterTestEvent extends PrintHtmlAlterEvent {

  // Null the constructor.
  public function __construct() {}

  /**
   * Throws an exception so we can test PostRenderSubscriber.
   */
  public function &getHtml() {
    throw new PrintEngineException('getHtml should never be called');
  }

}
