<?php

namespace Drupal\entity_print\Renderer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_print\PrintEngineException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * The RendererFactory class.
 */
class RendererFactory implements RendererFactoryInterface {

  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function create($item, $context = 'entity') {
    // If we get an array or something, just look at the first one.
    if (is_array($item)) {
      $item = array_pop($item);
    }

    if ($item instanceof EntityInterface) {
      // Support specific renderers for each entity type.
      $id = $item->getEntityType()->id();
      if ($this->container->has("entity_print.renderer.$id")) {
        return $this->container->get("entity_print.renderer.$id");
      }

      // Returns the generic service for content/config entities.
      $group = $item->getEntityType()->getGroup();
      if ($this->container->has("entity_print.renderer.$group")) {
        return $this->container->get("entity_print.renderer.$group");
      }
    }

    throw new PrintEngineException(sprintf('Rendering not yet supported for "%s". Entity Print context "%s"', is_object($item) ? get_class($item) : $item, $context));
  }

}
