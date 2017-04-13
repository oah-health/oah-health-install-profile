<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\MerciOpenHoursConstraint.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the node is assigned only a "leaf" term in the forum taxonomy.
 *
 * @Constraint(
 *   id = "MerciOpenHours",
 *   label = @Translation("MerciOpenHours", context = "Validation"),
 * )
 */
class MerciOpenHoursConstraint extends Constraint {
  public $date_field;

  public $reservable_hours_field;

  public $office_hours_field;

  /**
    ** {@inheritdoc}
    */
  public function getRequiredOptions() {
    return array('date_field', 'reservable_hours_field', 'office_hours_field');
  }
}
