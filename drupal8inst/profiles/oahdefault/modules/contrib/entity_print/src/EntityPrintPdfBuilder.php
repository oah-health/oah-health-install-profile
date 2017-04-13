<?php

/**
 * @file
 * Contains \Drupal\entity_print\EntityPrintPdfBuilder
 */

namespace Drupal\entity_print;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDisplayBase;
use Drupal\entity_print\Plugin\PdfEngineInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\migrate\Plugin\migrate\destination\EntityViewMode;

class EntityPrintPdfBuilder implements PdfBuilderInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * Constructs a new EntityPrintPdfBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler..
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset resolver.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $css_renderer
   *   The CSS renderer.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The theme renderer.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ThemeHandlerInterface $theme_handler, ModuleHandlerInterface $module_handler, InfoParserInterface $info_parser, AssetResolverInterface $asset_resolver, AssetCollectionRendererInterface $css_renderer, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->themeHandler = $theme_handler;
    $this->moduleHandler = $module_handler;
    $this->infoParser = $info_parser;
    $this->assetResolver = $asset_resolver;
    $this->cssRenderer = $css_renderer;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityRenderedAsPdf(ContentEntityInterface $entity, PdfEngineInterface $pdf_engine, $force_download = FALSE, $use_default_css = TRUE) {
    $pdf_engine->addPage($this->getHtml($entity, $use_default_css, TRUE));

    // Allow other modules to alter the generated PDF object.
    $this->moduleHandler->alter('entity_print_pdf', $pdf_engine, $entity);

    // If we're forcing a download we need a filename otherwise it's just sent
    // straight to the browser.
    $filename = $force_download ? $this->generateFilename($entity) : NULL;

    return $pdf_engine->send($filename);
  }

  /**
   * {@inheritdoc}
   */
  public function getMultipleEntitiesRenderedAsPdf(array $entities, PdfEngineInterface $pdf_engine, $force_download = FALSE, $use_default_css = TRUE) {
    $pdf_engine->addPage($this->getHtmlMultiple($entities, $use_default_css, TRUE));

    // Allow other modules to alter the generated PDF object.
    $this->moduleHandler->alter('entity_print_pdf_multiple', $pdf_engine, $entities);

    // If we're forcing a download we need a filename otherwise it's just sent
    // straight to the browser.
    $filename = $force_download ? $this->generateMultiFilename($entities) : NULL;

    return $pdf_engine->send($filename);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityRenderedAsHtml(ContentEntityInterface $entity, $use_default_css = TRUE, $optimize_css = TRUE) {
    return $this->getHtml($entity, $use_default_css, $optimize_css);
  }

  /**
   * Generate the HTML for our entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity we're rendering.
   * @param bool $use_default_css
   *   TRUE if we should inject our default CSS otherwise FALSE.
   * @param bool $optimize_css
   *   TRUE if we should compress the CSS otherwise FALSE.
   *
   * @return string
   *   The generated HTML.
   *
   * @throws \Exception
   */
  protected function getHtml(ContentEntityInterface $entity, $use_default_css, $optimize_css) {
    $render_controller = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    $render = [
      '#theme' => 'entity_print__' . $entity->getEntityTypeId() . '__' . $entity->id(),
      '#entity' => $entity,
      '#entity_array' => $render_controller->view($entity, $this->getViewMode($entity)),
      '#attached' => [],
    ];

    return $this->generateHtml($render, [$entity], $use_default_css, $optimize_css);
  }

  /**
   * Generate the HTML for our entity.
   *
   * @param array $entities
   *   An array of entities to generate the HTML for.
   * @param bool $use_default_css
   *   TRUE if we should inject our default CSS otherwise FALSE.
   * @param bool $optimize_css
   *   TRUE if we should compress the CSS otherwise FALSE.
   *
   * @return string
   *   The generated HTML.
   *
   * @throws \Exception
   */
  protected function getHtmlMultiple($entities, $use_default_css, $optimize_css) {
    $first_entity = reset($entities);
    $render_controller = $this->entityTypeManager->getViewBuilder($first_entity->getEntityTypeId());

    // @TODO, maybe we should implement a different theme function?
    $render = [
      '#theme' => 'entity_print__' . $first_entity->getEntityTypeId(),
      '#entity' => $entities,
      '#entity_array' => $render_controller->viewMultiple($entities, $this->getViewMode($first_entity)),
      '#attached' => [],
    ];

    return $this->generateHtml($render, $entities, $use_default_css, $optimize_css);
  }

  /**
   * Generate the HTML for the PDF.
   *
   * @param array $render
   *   The renderable array for our Entity Print theme hook.
   * @param array $entities
   *   An array of entities that we're rendering.
   * @param bool $use_default_css
   *   TRUE if we're including the default CSS otherwise FALSE.
   * @param bool $optimize_css
   *   TRUE if we want to compress the CSS otherwise FALSE.
   *
   * @return string
   *   The HTML rendered string.
   */
  protected function generateHtml(array $render, array $entities, $use_default_css, $optimize_css) {
    // Inject some generic CSS across all templates.
    if ($use_default_css) {
      $render['#attached']['library'][] = 'entity_print/default';
    }

    foreach ($entities as $entity) {
      // Allow other modules to add their own CSS.
      $this->moduleHandler->alter('entity_print_css', $render, $entity);

      // Inject CSS from the theme info files and then render the CSS.
      $render = $this->addCss($render, $entity);
    }

    $css_assets = $this->assetResolver->getCssAssets(AttachedAssets::createFromRenderArray($render), $optimize_css);
    $rendered_css = $this->cssRenderer->render($css_assets);
    $render['#entity_print_css'] = $this->renderer->render($rendered_css);

    $html = (string) $this->renderer->render($render);

    // Allow other modules to alter the generated HTML.
    $this->moduleHandler->alter('entity_print_generate_html', $html);

    return $html;
  }

  /**
   * Gets the view mode to use for this entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity we're viewing.
   *
   * @return string
   *   The view mode machine name.
   */
  protected function getViewMode(ContentEntityInterface $entity) {
    // We check to see if the PDF view display have been configured, if not
    // then we simply fall back to the full display.
    $view_mode = 'pdf';
    if (!$this->entityTypeManager->getStorage('entity_view_display')->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode)) {
      $view_mode = 'full';
    }
    return $view_mode;
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
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity info from entity_get_info().
   *
   * @return array
   *   An array of stylesheets to be used for this template.
   */
  protected function addCss($render, ContentEntityInterface $entity) {

    $theme = $this->themeHandler->getDefault();
    $theme_path = $this->getThemePath($theme);

    /** @var \Drupal\Core\Extension\InfoParser $parser */
    $theme_info = $this->infoParser->parse("$theme_path/$theme.info.yml");

    // Parse out the CSS from the theme info.
    if (isset($theme_info['entity_print'])) {

      // See if we have the special "all" key which is added to every PDF.
      if (isset($theme_info['entity_print']['all'])) {
        $render['#attached']['library'][] = $theme_info['entity_print']['all'];
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
            $render['#attached']['library'][] = $css;
          }
        }
      }
    }

    return $render;
  }

  /**
   * Generate a filename from the entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to generate the filename.
   * @param bool $with_extension
   *   Allow us to exclude the PDF file extension when generating the filename.
   *
   * @return string
   *   The cleaned filename from the entity label.
   */
  protected function generateFilename(ContentEntityInterface $entity, $with_extension = TRUE) {
    $filename = preg_replace("/[^A-Za-z0-9 ]/", '', $entity->label());
    // If for some bizarre reason there isn't a valid character in the entity
    // title or the entity doesn't provide a label then we use the entity type.
    if (!$filename) {
      $filename = $entity->getEntityTypeId();
    }
    return $with_extension ? $filename . '.pdf' : $filename;
  }

  /**
   * @param array $entities
   *   An array of entities to derive the filename for.
   *
   * @return string
   *   The filename to use.
   */
  protected function generateMultiFilename(array $entities) {
    $filename = '';
    foreach ($entities as $entity) {
      $filename .= $this->generateFilename($entity, FALSE) . '-';
    }
    return rtrim($filename, '-');
  }

  /**
   * Get the path to a theme.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return string
   *   The Drupal path to the theme.
   */
  protected function getThemePath($theme) {
    return drupal_get_path('theme', $theme);
  }

}
