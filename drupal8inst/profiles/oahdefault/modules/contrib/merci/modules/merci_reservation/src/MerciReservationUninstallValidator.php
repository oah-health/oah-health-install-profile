<?php

/**
 * @file
 * Contains \Drupal\merci_reservation\MerciReservationUninstallValidator.
 */

namespace Drupal\merci_reservation;

use Drupal\merci\MerciBundleUninstallValidator; 

/**
 * Prevents forum module from being uninstalled whilst any forum nodes exist
 * or there are any terms in the forum vocabulary.
 */
class MerciReservationUninstallValidator extends MerciBundleUninstallValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'merci_reservation') {
      if ($this->hasContent('merci_reservation', 'node')) {
        $reasons[] = $this->t('To uninstall Merci Reservation, first delete all <em>Merci Reservation</em> content');
      }
    }
    return $reasons;
  }

}
