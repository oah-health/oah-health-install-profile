<?php

/**
 * @file
 * Contains \Drupal\merci\MerciBundleUninstallValidator.
 */

namespace Drupal\merci;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\taxonomy\VocabularyInterface;

/**
 * Prevents forum module from being uninstalled whilst any forum nodes exist
 * or there are any terms in the forum vocabulary.
 */
class MerciBundleUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * The field storage config storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $vocabularyStorage;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ForumUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *  The config factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityManagerInterface $entity_manager, QueryFactory $query_factory, ConfigFactoryInterface $config_factory, TranslationInterface $string_translation) {
    $this->vocabularyStorage = $entity_manager->getStorage('taxonomy_vocabulary');
    $this->queryFactory = $query_factory;
    $this->configFactory = $config_factory;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    return $reasons;
  }

  /**
   * Determines if there are any forum nodes or not.
   *
   * @return bool
   *   TRUE if there are forum nodes, FALSE otherwise.
   */
  protected function hasContent($bundle, $entity_type = 'node') {
    $nodes = $this->queryFactory->get($entity_type)
      ->condition('type', $bundle)
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($nodes);
  }

  /**
   * Determines if there are any taxonomy terms for a specified vocabulary.
   *
   * @param \Drupal\taxonomy\VocabularyInterface $vocabulary
   *   The vocabulary to check for terms.
   *
   * @return bool
   *   TRUE if there are terms for this vocabulary, FALSE otherwise.
   */
  protected function hasTermsForVocabulary(VocabularyInterface $vocabulary) {
    $terms = $this->queryFactory->get('taxonomy_term')
      ->condition('vid', $vocabulary->id())
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($terms);
  }

}
