<?php

/**
 * @file
 * Contains \Drupal\merci_resource\MerciResourceUninstallValidator.
 */

namespace Drupal\merci_resource;

use Drupal\merci\MerciBundleUninstallValidator; 

/**
 * Prevents forum module from being uninstalled whilst any forum nodes exist
 * or there are any terms in the forum vocabulary.
 */
class MerciResourceUninstallValidator extends MerciBundleUninstallValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'merci_resource') {
      if ($this->hasContent('merci_resource', 'node')) {
        $reasons[] = $this->t('To uninstall Merci Resource, first delete all <em>Merci Resource</em> content');
      }
    }
    return $reasons;
  }

}
