<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\ReservationConflictConstraint.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the node is assigned only a "leaf" term in the forum taxonomy.
 *
 * @Constraint(
 *   id = "ReservationConflict",
 *   label = @Translation("ReservationConflict", context = "Validation"),
 * )
 */
class ReservationConflictConstraint extends Constraint {

  public $date_field;

  public $item_field;

  public $quantity_field;

}
