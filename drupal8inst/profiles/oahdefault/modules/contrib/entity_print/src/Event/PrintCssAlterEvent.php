<?php

namespace Drupal\entity_print\Event;

use Symfony\Component\EventDispatcher\Event;

class PrintCssAlterEvent extends Event {

  /**
   * The renderable array.
   *
   * @var array
   */
  protected $build;

  /**
   * An array of entities we're rendering.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities;

  /**
   * PrintCssAlterEvent constructor.
   *
   * @param array $build
   *   The renderable array.
   * @param array $entities
   *   An array of entities we're rendering.
   */
  public function __construct(array &$build, $entities) {
    $this->build = &$build;
    $this->entities = $entities;
  }

  /**
   * Gets the renderable array by reference if you want to change it.
   *
   * @return array
   *   The renderable array.
   */
  public function &getBuild() {
    return $this->build;
  }

  /**
   * Gets the entities being rendered.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entities.
   */
  public function getEntities() {
    return $this->entities;
  }

}
