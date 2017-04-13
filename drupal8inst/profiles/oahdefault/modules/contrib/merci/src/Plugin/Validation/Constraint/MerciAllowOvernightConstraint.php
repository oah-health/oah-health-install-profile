<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\MerciAllowOvernightConstraint.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the node is assigned only a "leaf" term in the forum taxonomy.
 *
 * @Constraint(
 *   id = "MerciAllowOvernight",
 *   label = @Translation("MerciAllowOvernight", context = "Validation"),
 * )
 */
class MerciAllowOvernightConstraint extends Constraint {
  public $date_field;

  public $overnight_field;

  /**
    ** {@inheritdoc}
    */
  public function getRequiredOptions() {
    return array('date_field', 'overnight_field');
  }
}
