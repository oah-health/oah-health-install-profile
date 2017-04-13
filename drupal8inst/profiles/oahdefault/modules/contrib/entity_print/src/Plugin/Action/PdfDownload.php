<?php

/**
 * @file
 * Contains \Drupal\entity_print\Plugin\Action\PdfDownload
 */

namespace Drupal\entity_print\Plugin\Action;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_print\PdfBuilderInterface;
use Drupal\entity_print\PdfEngineException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads the PDF for an entity.
 *
 * @Action(
 *   id = "entity_print_download_action",
 *   label = @Translation("Download PDF"),
 *   type = "node"
 * )
 *
 * @TODO, support multiple entity types once core is fixed.
 * @see https://www.drupal.org/node/2011038
 */
class PdfDownload extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The PDF builder service.
   *
   * @var \Drupal\entity_print\PdfBuilderInterface
   */
  protected $pdfBuilder;

  /**
   * The Entity Print plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Our custom configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $entityPrintConfig;

  /**
   * The PDF engine implementation.
   *
   * @var \Drupal\entity_print\Plugin\PdfEngineInterface
   */
  protected $pdfEngine;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccessManagerInterface $access_manager, PdfBuilderInterface $pdf_builder, PluginManagerInterface $plugin_manager, ImmutableConfig $entity_print_config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->accessManager = $access_manager;
    $this->pdfBuilder = $pdf_builder;
    $this->pluginManager = $plugin_manager;
    $this->entityPrintConfig = $entity_print_config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('access_manager'),
      $container->get('entity_print.pdf_manager'),
      $container->get('plugin.manager.entity_print.pdf_engine'),
      $container->get('config.factory')->get('entity_print.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $route_params = [
      'entity_id' => $object->id(),
      'entity_type' => $object->getEntityTypeId(),
    ];
    return $this->accessManager->checkNamedRoute('entity_print.view', $route_params, $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->sendResponse((function() use ($entity) {
      $this->pdfBuilder->getEntityRenderedAsPdf($entity, $this->getPdfEngine(), TRUE);
    }));
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $this->sendResponse((function() use ($entities) {
      $this->pdfBuilder->getMultipleEntitiesRenderedAsPdf($entities, $this->getPdfEngine(), TRUE);
    }));
  }

  /**
   * Sends the response using a stream and catches any errors.
   *
   * @param callable $callback
   *   The callable responding for rendering the content.
   */
  protected function sendResponse(callable $callback) {
    try {
      (new StreamedResponse($callback))->send();
    }
    catch (PdfEngineException $e) {
      drupal_set_message(new FormattableMarkup(Xss::filter($e->getMessage()), []), 'error');
    }
  }

  /**
   * Gets the PDF engine implementation.
   *
   * @return \Drupal\entity_print\Plugin\PdfEngineInterface
   *   The PDF Engine implementation.
   */
  protected function getPdfEngine() {
    if (!isset($this->pdfEngine)) {
      $this->pdfEngine = $this->pluginManager->createInstance($this->entityPrintConfig->get('pdf_engine'));
    }
    return $this->pdfEngine;
  }

}
