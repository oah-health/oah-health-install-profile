<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\MerciAllowOvernightConstraintValidator.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Checks for conflicts when validating a entity with reservable items.
 */
class MerciAllowOvernightConstraintValidator extends ConstraintValidator {

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


    if ($value->entity->{$constraint->overnight_field}->value != 1) {

      $datetime_start = $value->getEntity()->{$constraint->date_field}[0]->date;
      $datetime_end   = $value->getEntity()->{$constraint->date_field}[0]->date2;
      $datetime_start->setTimeZone(timezone_open(drupal_get_user_timezone()));
      $datetime_end->setTimeZone(timezone_open(drupal_get_user_timezone()));
      $date_format = DateFormat::load('html_date')->getPattern();
      if ($datetime_start->format($date_format) != $datetime_end->format($date_format)) {
        $this->context->addViolation('Reservation can not go overnight.');
      }
    }
  }
}
