<?php

/**
 * @file
 * Contains \Drupal\merci_grouping\MerciGroupingUninstallValidator.
 */

namespace Drupal\merci_grouping;

use Drupal\merci\MerciBundleUninstallValidator;
use Drupal\taxonomy\VocabularyInterface;


/**
 * Prevents forum module from being uninstalled whilst any forum nodes exist
 * or there are any terms in the forum vocabulary.
 */
class MerciGroupingUninstallValidator extends MerciBundleUninstallValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'merci_grouping') {
      $vocabulary = $this->vocabularyStorage->load('resource_tree');
      if ($this->hasTermsForVocabulary($vocabulary)) {
        if ($vocabulary->access('view')) {
          $reasons[] = $this->t('To uninstall Merci Grouping, first delete all <a href=":url">%vocabulary</a> terms', [
            '%vocabulary' => $vocabulary->label(),
            ':url' => $vocabulary->url('overview-form'),
          ]);
        }
        else {
          $reasons[] = $this->t('To uninstall Merci Grouping, first delete all %vocabulary terms', [
            '%vocabulary' => $vocabulary->label()
          ]);
        }
      }
    }
    return $reasons;
  }

}
