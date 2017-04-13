<?php

/**
  * @file
  * Contains \Drupal\merci\ReservationConflictsInterface.
  */

namespace Drupal\merci;

/**
 * A null implementation of EntityReference_SelectionHandler.
 */
interface ReservationConflictsInterface {

  public function setEntity(\Drupal\Core\Entity\FieldableEntityInterface $entity);

  public function getEntity();

  public function setDateField($date_field);

  public function getDateField();

  public function setItemField($item_field);

  public function getItemField();

  public function validate();

  public function getErrors($delta = NULL);

  public function getConflicts($delta = NULL, $dates = NULL);

  public function getQuantityReserved($delta = NULL, $dates = NULL);

  public function conflicts($date);

  public function buildConflictQuery($date);
}

