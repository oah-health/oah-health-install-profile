<?php

namespace Drupal\entity_print\Renderer;

use Drupal\Core\Entity\EntityInterface;

interface RendererInterface {

  /**
   * Gets the renderable for this entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we're rendering.
   *
   * @return array
   *   The renderable array for the entity.
   */
  public function render(EntityInterface $entity);

  /**
   * Generates the HTML from the renderable array of entities.
   *
   * @param array $entities
   *   An array of entities we're rendering.
   * @param array $render
   *   A renderable array.
   * @param bool $use_default_css
   *   TRUE if we should inject our default CSS otherwise FALSE.
   * @param bool $optimize_css
   *   TRUE if we should compress the CSS otherwise FALSE.
   * @return mixed
   */
  public function generateHtml(array $entities, array $render, $use_default_css, $optimize_css);

  /**
   * Get the filename for the entity we're printing *without* the extension.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   The entities for which to generate the filename from.
   * @return string
   *   The generate file name for this entity.
   */
  public function getFilename(array $entities);

}
