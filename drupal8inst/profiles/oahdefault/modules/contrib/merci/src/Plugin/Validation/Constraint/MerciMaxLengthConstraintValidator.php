<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\MerciMaxLengthConstraintValidator.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Checks for conflicts when validating a entity with reservable items.
 */
class MerciMaxLengthConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /* @var \Drupal\Core\Field\FieldItemInterface $value */
    if (!isset($value)) {
      return;
    }
    $id = $value->target_id;
    // '0' or NULL are considered valid empty references.
    if (empty($id)) {
      return;
    }

    $datetime_start = $value->getEntity()->{$constraint->date_field}[0]->date;
    $datetime_end   = $value->getEntity()->{$constraint->date_field}[0]->date2;

    $interval = $value->entity->{$constraint->interval_field};

    $interval_spec = "P";
    
    if (in_array($interval->period, array('hour', 'minute', 'second'))) {
      $interval_spec .= 'T';
    }

    $interval_spec .= $interval->interval;

    $interval_map = array(
      'second' => 'S',
      'minute' => 'M',
      'hour' => 'H',
      'week' => 'W',
      'day' => 'D',
      'month' => 'M',
      'year' => 'Y',
    );

    $interval_spec .= $interval_map[$interval->period];

    $datetime_start->add(new \DateInterval($interval_spec));

    if ($datetime_start < $datetime_end) {
      $this->context->addViolation('Reservation length is too long.');
    }

  }
}
