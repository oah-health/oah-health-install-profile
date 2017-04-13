<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\MerciOpenHoursConstraintValidator.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Checks for conflicts when validating a entity with reservable items.
 */
class MerciOpenHoursConstraintValidator extends ConstraintValidator {

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
    /* @var \Drupal\Core\Entity\FieldableEntityInterface $referenced_entity */
    $referenced_entity = $value->entity;

    $context = array(
      'quantity_field' => 'field_quantity',
      'date_field' => 'merci_reservation_date', //$value->getFieldDefinition()->getSetting('merci_date_field'),
      'item_field' => $value->getFieldDefinition()->getName(),
      'reservable_hours' => 'field_reservable_hours',
    );

    $datetime_start = $value->getEntity()->{$constraint->date_field}[0]->date;
    $datetime_end = $value->getEntity()->{$constraint->date_field}[0]->date2;
    $datetime_start->setTimeZone(timezone_open(drupal_get_user_timezone()));
    $datetime_end->setTimeZone(timezone_open(drupal_get_user_timezone()));
    $date_format = DateFormat::load('html_date')->getPattern();
    $time_format = DateFormat::load('html_time')->getPattern();
    $date_time_format = trim($date_format . ' ' . $time_format);
    $timezone = $datetime_start->getTimezone();

    $date_start = $datetime_start->format($date_format);
    $date_end   = $datetime_end->format($date_format);

    $start_error = FALSE;
    $end_error = FALSE;

    foreach ($referenced_entity->{$constraint->reservable_hours_field}[0]->entity->{$constraint->office_hours_field} as $open_hours) {

      $open_hours->startdate->setTimeZone(timezone_open(drupal_get_user_timezone()));
      $open_hours->enddate->setTimeZone(timezone_open(drupal_get_user_timezone()));
      $date_time_input = trim($date_start . ' ' . $open_hours->startdate->format($time_format));
      $open = DrupalDateTime::createFromFormat($date_time_format, $date_time_input, $timezone);
      $date_time_input = trim($date_start . ' ' . $open_hours->enddate->format($time_format));
      $close = DrupalDateTime::createFromFormat($date_time_format, $date_time_input, $timezone);
      if ($open >= $datetime_start && $datetime_start <= $close) {
        $start_error = TRUE;
      }
      if ($open >= $datetime_end && $datetime_end <= $close) {
        $end_error = TRUE;
      }
    }
    if ($start_error) {
      $this->context->addViolation('Start is outside open hours.');
    }
    if ($end_error) {
      $this->context->addViolation('End is outside open hours.');
    }

  }
}
