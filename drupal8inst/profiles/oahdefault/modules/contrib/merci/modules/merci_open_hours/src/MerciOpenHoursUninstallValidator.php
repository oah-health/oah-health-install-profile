<?php

/**
 * @file
 * Contains \Drupal\merci_open_hours\MerciOpenHoursUninstallValidator.
 */

namespace Drupal\merci_open_hours;

use Drupal\merci\MerciBundleUninstallValidator; 

/**
 * Prevents forum module from being uninstalled whilst any forum nodes exist
 * or there are any terms in the forum vocabulary.
 */
class MerciOpenHoursUninstallValidator extends MerciBundleUninstallValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'merci_open_hours') {
      if ($this->hasContent('office_hours', 'node')) {
        $reasons[] = $this->t('To uninstall Merci OpenHours, first delete all <em>Merci OpenHours</em> content');
      }
    }
    return $reasons;
  }

}
