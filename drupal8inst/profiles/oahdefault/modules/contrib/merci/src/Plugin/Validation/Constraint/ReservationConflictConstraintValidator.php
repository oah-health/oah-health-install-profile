<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\Validation\Constraint\ReservationConflictConstraintValidator.
 */

namespace Drupal\merci\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\merci\ReservationConflicts;

/**
 * Checks for conflicts when validating a entity with reservable items.
 */
class ReservationConflictConstraintValidator extends ConstraintValidator {

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

    $conflicts = \Drupal::service('merci.reservation_conflicts');
    $conflicts->setEntity($value->getEntity());
    $conflicts->setDateField($constraint->date_field);
    $conflicts->setItemField($constraint->item_field);


    foreach ($conflicts->getErrors() as $delta => $errors) {
      $msg = array();

      /*
      if (array_key_exists(MERCI_ERROR_TOO_MANY, $errors)) {
        $msg[] = $errors[MERCI_ERROR_TOO_MANY];
      } elseif (array_key_exists(MERCI_ERROR_CONFLICT, $errors)) {
        foreach ($errors[MERCI_ERROR_CONFLICT] as $date_start => $message) {
          $msg[] = $message;
        }
      }
      $errors[$field['field_name']][$langcode][$delta][] = array(
        'error' => 'merci',
        'message' => implode('<br>', $msg),
      );
       */
      //$this->context->addViolation($constraint->message, array('%type' => $type, '%id' => $id));
      $this->context->addViolation('Conflict');

    }
  }
}
