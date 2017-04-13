<?php

namespace Drupal\entity_print\Renderer;

use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Render\RendererInterface as CoreRendererInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentEntityRenderer extends RendererBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(ThemeHandlerInterface $theme_handler, InfoParserInterface $info_parser, AssetResolverInterface $asset_resolver, AssetCollectionRendererInterface $css_renderer, CoreRendererInterface $renderer, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($theme_handler, $info_parser, $asset_resolver, $css_renderer, $renderer, $event_dispatcher);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function render(EntityInterface $entity) {
    $render_controller = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    return $render_controller->view($entity, $this->getViewMode($entity));
  }

  /**
   * {@inheritdoc}
   */
  protected function getLabel(EntityInterface $entity) {
    return $entity->label();
  }

  /**
   * Gets the view mode to use for this entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity we're viewing.
   *
   * @return string
   *   The view mode machine name.
   */
  protected function getViewMode(EntityInterface $entity) {
    // We check to see if the PDF view display have been configured, if not
    // then we simply fall back to the full display.
    $view_mode = 'pdf';
    if (!$this->entityTypeManager->getStorage('entity_view_display')->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode)) {
      $view_mode = 'full';
    }
    return $view_mode;
  }

}
