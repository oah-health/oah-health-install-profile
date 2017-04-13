<?php

namespace Drupal\entity_print\Renderer;

interface RendererFactoryInterface {

  /**
   * @param mixed $item
   *   The item we require a renderer for.
   * @param string $context
   *   The type, currently supports entities but could change in the future.
   *
   * @return \Drupal\entity_print\Renderer\RendererInterface
   *   The constructed renderer.
   */
  public function create($item, $context = 'entity');

}
