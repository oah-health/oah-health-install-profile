<?php

namespace Drupal\entity_print;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\Event\PreSendPrintEvent;
use Drupal\entity_print\Plugin\PrintEngineInterface;
use Drupal\entity_print\Renderer\RendererFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PrintBuilder implements PrintBuilderInterface {

  use StringTranslationTrait;

  /**
   * The Print Renderer factory.
   *
   * @var \Drupal\entity_print\Renderer\RendererFactoryInterface
   */
  protected $rendererFactory;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Constructs a new EntityPrintPrintBuilder.
   *
   * @param \Drupal\entity_print\Renderer\RendererFactoryInterface $renderer_factory
   *   The Renderer factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(RendererFactoryInterface $renderer_factory, EventDispatcherInterface $event_dispatcher, TranslationInterface $string_translation) {
    $this->rendererFactory = $renderer_factory;
    $this->dispatcher = $event_dispatcher;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function deliverPrintable(array $entities, PrintEngineInterface $print_engine, $force_download = FALSE, $use_default_css = TRUE) {
    if (empty($entities)) {
      throw new \InvalidArgumentException('You must pass at least 1 entity');
    }

    $renderer = $this->rendererFactory->create($entities);
    $content = array_map([$renderer, 'render'], $entities);

    $first_entity = reset($entities);
    $render = [
      '#theme' => 'entity_print__' . $first_entity->getEntityTypeId() . '__' . $first_entity->bundle(),
      '#title' => $this->t('View @type', ['@type' => $print_engine->getExportType()->label()]),
      '#content' => $content,
      '#attached' => [],
    ];

    $print_engine->addPage($renderer->generateHtml($entities, $render, $use_default_css, TRUE));

    // Allow other modules to alter the generated Print object.
    $this->dispatcher->dispatch(PrintEvents::PRE_SEND, new PreSendPrintEvent($print_engine, $entities));

    // If we're forcing a download we need a filename otherwise it's just sent
    // straight to the browser.
    $filename = $force_download ? $renderer->getFilename($entities) . '.' . $print_engine->getExportType()->getFileExtension() : NULL;

    return $print_engine->send($filename);
  }

  /**
   * {@inheritdoc}
   */
  public function printHtml(EntityInterface $entity, $use_default_css = TRUE, $optimize_css = TRUE) {
    $renderer = $this->rendererFactory->create([$entity]);
    $content[] = $renderer->render($entity);

    $render = [
      '#theme' => 'entity_print__' . $entity->getEntityTypeId() . '__' . $entity->bundle(),
      '#title' => $this->t('View'),
      '#content' => $content,
      '#attached' => [],
    ];
    return $renderer->generateHtml([$entity], $render, $use_default_css, $optimize_css);
  }

}
