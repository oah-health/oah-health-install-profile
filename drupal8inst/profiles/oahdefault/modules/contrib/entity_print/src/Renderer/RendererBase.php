<?php

namespace Drupal\entity_print\Renderer;

use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\entity_print\Event\PrintCssAlterEvent;
use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\Event\PrintHtmlAlterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Render\RendererInterface as CoreRendererInterface;

/**
 * The RendererBase class.
 */
abstract class RendererBase implements RendererInterface {

  /**
   * The filename used when we're unable to calculate a filename.
   *
   * @var string
   */
  const DEFAULT_FILENAME = 'document';

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The info parser for yml files.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The asset resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * The css asset renderer.
   *
   * @var \Drupal\Core\Asset\CssCollectionRenderer
   */
  protected $cssRenderer;

  /**
   * The renderer for renderable arrays.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  public function __construct(ThemeHandlerInterface $theme_handler, InfoParserInterface $info_parser, AssetResolverInterface $asset_resolver, AssetCollectionRendererInterface $css_renderer, CoreRendererInterface $renderer, EventDispatcherInterface $event_dispatcher) {
    $this->themeHandler = $theme_handler;
    $this->infoParser = $info_parser;
    $this->assetResolver = $asset_resolver;
    $this->cssRenderer = $css_renderer;
    $this->renderer = $renderer;
    $this->dispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function generateHtml(array $entities, array $render, $use_default_css, $optimize_css) {
    // Inject some generic CSS across all templates.
    if ($use_default_css) {
      $render['#attached']['library'][] = 'entity_print/default';
    }

    foreach ($entities as $entity) {
      // Inject CSS from the theme info files and then render the CSS.
      $render = $this->addCss($render, $entity);
    }

    $this->dispatcher->dispatch(PrintEvents::CSS_ALTER, new PrintCssAlterEvent($render, $entities));
    $css_assets = $this->assetResolver->getCssAssets(AttachedAssets::createFromRenderArray($render), $optimize_css);
    $rendered_css = $this->cssRenderer->render($css_assets);

    $render['#entity_print_css'] = $this->renderer->executeInRenderContext(new RenderContext(), function () use (&$rendered_css) {
      return $this->renderer->render($rendered_css);
    });

    $html = (string) $this->renderer->executeInRenderContext(new RenderContext(), function () use (&$render) {
      return $this->renderer->render($render);
    });

    // Allow other modules to alter the generated HTML.
    $this->dispatcher->dispatch(PrintEvents::POST_RENDER, new PrintHtmlAlterEvent($html, $entities));

    return $html;
  }

  /**
   * Gets a safe filename.
   *
   * @param string $filename
   *   The un-processed filename.
   *
   * @return string
   *   The filename stripped to only safe characters.
   */
  protected function sanitizeFilename($filename) {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
  }

  /**
   * Inject the relevant css for the template.
   *
   * You can specify CSS files to be included per entity type and bundle in your
   * themes css file. This code uses your current theme which is likely to be the
   * front end theme.
   *
   * Examples:
   *
   * entity_print:
   *   all: 'yourtheme/all-pdfs',
   *   commerce_order:
   *     all: 'yourtheme/orders'
   *   node:
   *     article: 'yourtheme/article-pdf'
   *
   * @param array $render
   *   The renderable array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity info from entity_get_info().
   *
   * @return array
   *   An array of stylesheets to be used for this template.
   */
  protected function addCss($render, EntityInterface $entity) {
    $theme = $this->themeHandler->getTheme($this->themeHandler->getDefault());
    $theme_info = $this->infoParser->parse($theme->getPathname());

    if (!isset($theme_info['entity_print'])) {
      return $render;
    }

    // See if we have the special "all" key which is added to every PDF.
    if (isset($theme_info['entity_print']['all'])) {
      $render['#attached']['library'] = array_merge(isset($render['#attached']['library']) ? $render['#attached']['library'] : [], (array) $theme_info['entity_print']['all']);
      unset($theme_info['entity_print']['all']);
    }

    foreach ($theme_info['entity_print'] as $key => $value) {
      // If the entity type doesn't match just skip.
      if ($key !== $entity->getEntityTypeId()) {
        continue;
      }

      // Parse our css files per entity type and bundle.
      foreach ($value as $css_bundle => $css) {
        // If it's magic key "all" add it otherwise check the bundle.
        if ($css_bundle === 'all' || $entity->bundle() === $css_bundle) {
          $render['#attached']['library'] = array_merge(isset($render['#attached']['library']) ? $render['#attached']['library'] : [], (array) $css);
        }
      }
    }

    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilename(array $entities) {
    $filenames = [];
    foreach ($entities as $entity) {
      if ($label = trim($this->sanitizeFilename($this->getLabel($entity)))) {
        $filenames[] = $label;
      }
    }
    return $filenames ? implode('-', $filenames) : static::DEFAULT_FILENAME;
  }

  /**
   * Gets the entity label.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we want to generate a label for.
   *
   * @return string
   *   The label for this entity.
   */
  abstract protected function getLabel(EntityInterface $entity);

}
