<?php


/**
 * @file
 * Contains \Drupal\merci\ReservationConflicts.
 * Abstraction of the selection logic of an entity reference field.
 *
 * Implementations that wish to provide an implementation of this should
 * register it using CTools' plugin system.
 */

namespace Drupal\merci;

use \Drupal\merci\ReservationConflictsInterface;
use \Drupal\Core\Link;
/**
 * A null implementation of EntityReference_SelectionHandler.
 */
class ReservationConflicts implements ReservationConflictsInterface {

  protected $entity;
  protected $date_field;
  protected $item_field;
  protected $validated;
  protected $conflicting_entities;
  protected $quantity_reserved;
  protected $buckets;
  protected $errors;

  protected $date_column, $date_column2;

  public function setEntity(\Drupal\Core\Entity\FieldableEntityInterface $entity) {
    $this->entity = $entity;
  }

  public function getEntity() {
    return $entity;
  }

  public function setDateField($date_field) {
    $this->date_field = $date_field;
    $date_storage = $this->entity->get($this->date_field)->getFieldDefinition()->getFieldStorageDefinition();
    $date_columns = $date_storage->getColumns();
    $this->date_column  = $this->date_field . '_' . key($date_columns);
    next($date_columns);
    $this->date_column2 = $this->date_field . '_' . key($date_columns);
  }

  public function getDateField() {
    return $date_field;
  }

  public function setItemField($item_field) {
    $this->item_field = $item_field;
  }

  public function getItemField() {
    return $item_field;
  }

  public function validate() {
    if (!$this->validated) {
      $this->buckets = $this->fillBuckets();
      $this->validated = TRUE;
      $conflicts = array();
      foreach ($this->buckets as $delta => $dates) {
        foreach ($dates as $date_value => $buckets){
          if (!isset($this->quantity_reserved[$delta])) {
            $this->quantity_reserved[$delta] = array();
          }
          $this->quantity_reserved[$delta][$date_value] = count($buckets);
          if (!isset($conflicts[$delta])) {
            $conflicts[$delta] = array();
          }
          $conflicts[$delta][$date_value] = array();
          foreach ($buckets as $bucket) {
            $conflicts[$delta][$date_value] = array_merge($conflicts[$delta][$date_value], $bucket);
          }
        }
      }
      $this->conflicting_entities = $conflicts;
    }
  }

  public function getErrors($delta = NULL) {
    // Determine if reserving too many of the same item.
    if ($this->errors === NULL) {
      $this->validate();
      $entity = $this->entity;
      $entity_type  = $this->entity->getEntityTypeId();
      $errors = array();

      foreach ($entity->get($this->item_field) as $delta => $resource) {
        $item_id = $resource->target_id;
        if (empty($item_id)) {
          continue;
        }
        if (empty($item_count[$item_id])) {
          $item_count[$item_id] = 0;
        }
        $item_count[$item_id]++;
          /*
            @FIXME
          try {
            $quantity_reservable = $resource->field_quantity->value();
          } catch (EntityMetadataWrapperException $e) {
           */
        $quantity_reservable = 1;
        //}
        if ($item_count[$item_id] > $quantity_reservable) {
          // Selected to many.
          if (!array_key_exists($delta, $errors)) {
            $errors[$delta] = array();
          }
          $parents_path = implode('][', array($this->item_field, 'und', $delta, 'target_id'));
          $errors[$delta][MERCI_ERROR_TOO_MANY] =  t('%name: You have selected too many of the same item.  We only have %quantity available but you reserved %reserved.',
            array(
              '%name' => $resource->entity->getLabel(),
              '%quantity' => $quantity_reservable,
              '%reserved' => $item_count[$item_id],
            ));
        }
      }

      $reserved = $this->getQuantityReserved();

      $reserved = $reserved ? $reserved : array();

      $reserved_so_far_by_me = array();

      foreach ($reserved as $delta => $start_dates) {

        $conflict_errors = array();

        // Load the resource being reserved.
        /*
        if ($this->items_is_list) {
          $resource  = $entity->{$context['item_field']}[$delta];
        } else {
          $resource  = $entity->{$context['item_field']};
        }
         */
        $resource = $entity->get($this->item_field)[$delta];

        // Determine if the quantity field exists.  If so use it.
        /* @FIXME
        try {
          $quantity_reservable = $resource->field_quantity->value();
        } catch (EntityMetadataWrapperException $e) {
         */
          $quantity_reservable = 1;
        //}

        $item_id = $resource->target_id;
        if (empty($reserved_so_far_by_me[$item_id])) {
          $reserved_so_far_by_me[$item_id] = 0;
        }
        $reserved_so_far_by_me[$item_id]++;

        foreach ($this->entity->get($this->date_field) as $dates) {

          $quantity_reserved = $this->getQuantityReserved($delta, $dates);


          // Determine if there are conflicts for this date and item.
          if ($quantity_reservable >= $quantity_reserved + $reserved_so_far_by_me[$item_id]) {
            continue;
          }
          // Load each conflicting entity so we can show information about it to
          // the user.
          $ids = array();
          foreach ($this->getConflicts($delta, $dates) as $conflict) {
            $ids[] = $conflict->parent_id;
          }

          // Load the entities which hold the conflicting item.
          $entities = \Drupal::entityManager()->getStorage($entity_type)->loadMultiple($ids);

          $line_items = array();

          foreach ($entities as $id => $line_item) {
            $entity_uri = $line_item->toUrl();//entity_uri($entity_type, $line_item);
            $entity_label = $line_item->label();//entity_label($entity_type, $line_item);
            $line_items[] = Link::fromTextAndUrl($entity_label, $entity_uri)->toString();
          }

          $date_start = $dates->get('value')->getValue();
          // Don't show the date repeat rule in the error message.

          // @FIXME
          //$render_dates = field_view_value($entity_type, $entity->value(), $this->date_field, $dates);
          $conflict_errors[$date_start] = t('%name is already reserved by: !items for selected dates !dates',
            array(
              '%name' => $resource->entity->label(),
              '!items' => implode(', ', $line_items),
              '!dates' => render($render_dates),
            ));
        }
        if ($conflict_errors) {
          if (!array_key_exists($delta, $errors)) {
            $errors[$delta] = array();
          }
          $errors[$delta][MERCI_ERROR_CONFLICT] = $conflict_errors;
        }
      }
      $this->errors = $errors;
    }
    return $this->errors;
  }

  public function getConflicts($delta = NULL, $dates = NULL) {

    $this->validate();
    $conflicts = $this->conflicting_entities;

    if ($delta === NULL) {
      return $conflicts;
    }

    if (empty($dates)) {
      return array_key_exists($delta, $conflicts) ?
        $conflicts[$delta] : FALSE;
    }

    $date_value = $dates->get('value')->getValue();
    return (array_key_exists($delta, $conflicts) and array_key_exists($date_value, $conflicts[$delta])) ?
      $conflicts[$delta][$date_value] : FALSE;
  }

  public function getQuantityReserved($delta = NULL, $dates = NULL) {

    $this->validate();
    $quantity_reserved = $this->quantity_reserved;

    if ($delta === NULL) {
      return $quantity_reserved;
    }

    if (empty($dates)) {
      return array_key_exists($delta, $quantity_reserved) ?
        $quantity_reserved[$delta] : 0;
    }

    $date_value = $dates->get('value')->getValue();
    return (array_key_exists($delta, $quantity_reserved) and array_key_exists($date_value, $quantity_reserved[$delta])) ?
      $quantity_reserved[$delta][$date_value] : 0;
  }

  /*
   * Determine if merci_line_item $entity conflicts with any other existing line_items.
   *
   * Returns array of conflicting line items.
   */

  public function conflicts($date) {
    $conflicts = array();

    $date_value = $date->get('value')->getValue();

    $query = $this->buildConflictQuery($date);

    $result = $query->execute();
    foreach ($result as $record){
      if (!isset($conflicts[$record->item_id])) {
        $conflicts[$record->item_id] = array();
      }
      if (!isset($conflicts[$record->item_id][$date_value])) {
        $conflicts[$record->item_id][$date_value] = array();
      }
      $conflicts[$record->item_id][$date_value][] = $record;
    }


    $return = array();

    $items = $this->entity->get($this->item_field);
    foreach ($items as $delta => $item) {
      if (isset($conflicts[$item->target_id])) {
        $return[$delta] = $conflicts[$item->target_id];
      }
    }
    return $return;
  }

  public function buildConflictQuery($date) {

    $exclude_id   = $this->entity->id();
    $entity_type  = $this->entity->getEntityTypeId();

    $item_storage = $this->entity->get($this->item_field)->getFieldDefinition()->getFieldStorageDefinition();
    $item_table   = $entity_type . '__' . $this->item_field;//$this->item_table;
    $item_column  = $this->item_field . '_' . key($item_storage->getColumns());

    $date_storage = $this->entity->get($this->date_field)->getFieldDefinition()->getFieldStorageDefinition();
    $date_columns = $date_storage->getColumns();
    $date_table   = $entity_type . '__' . $this->date_field; //$this->date_table;
    $date_column  = $this->date_field . '_' . key($date_columns);
    next($date_columns);
    $date_column2 = $this->date_field . '_' . key($date_columns);

    $parent_table = $entity_type; //$this->parent_table;
    $parent_index = 'nid';//$this->parent_index;

    $items = array();

    foreach ($this->entity->get($this->item_field) as $delta => $item) {
      $items[] = $item->target_id;
    }


    // Build the query.
    $query = db_select($item_table, 'item_table');
    $query->addField('item_table', $item_column, 'item_id');
    $query->addField('item_table', 'entity_id', 'parent_id');

    if (count($items) == 1) {
      $query->condition($item_column, reset($items));
    } else {
      $query->condition($item_column, $items, 'IN');
    }
    // Ignore myself.
    if ($exclude_id) {
      $query->condition('item_table.entity_id', $exclude_id, '!=');
    }

    $query->join($parent_table, 'merci_line_item', 'item_table.entity_id = merci_line_item.' . $parent_index);

    /*
      @FIXME

    if ($this->parent_has_quantity) {
      $query->addField('merci_line_item', 'quantity', 'quantity');
    } else {
     */
      $query->addExpression('1', 'quantity');
/*
    }

    if ($this->parent_has_status) {
      $query->condition('merci_line_item.status', 1, '=');
    }
     */

    $query->join($date_table, 'date_table', 'item_table.entity_id = date_table.entity_id');
    $query->addField('date_table', $date_column);//, MERCI_DATE_FIELD_ALIAS);
    $query->addField('date_table', $date_column2);//, MERCI_DATE_FIELD_ALIAS2);
    //$query->condition('date_table.entity_type',$entity_type,'=');
    $query->condition('date_table.deleted' , 0, '=');

    $dates = array(
      'value' => $date->get('value')->getValue(),
      'end_value' => $date->get('end_value')->getValue()
    );

    // TODO handled multiple dates.
    $query->condition(
      db_or()
      //  start falls within another reservation.
      //                     |-------------this-------------|
      //            |-------------conflict-------------------------|
      //            OR
      //                     |-------------this-------------------------------|
      //            |-------------conflict-------------------------|
      ->condition(
        db_and()->condition($date_column, $dates['value'], '<=')->condition($date_column2, $dates['value'], '>=')
      )
      //  end falls within another reservation.
      //                     |-------------this-------------------------------|
      //                                   |-------------conflict-------------------------|
      ->condition(
        db_and()->condition($date_column, $dates['end_value'], '<=')->condition($date_column2, $dates['end_value'], '>=')
      )
      //  start before another reservation.
      //  end after another reservation.
      //                     |-------------------------this-------------------------------|
      //                            |----------------conflict------------------|
      ->condition(
        db_and()->condition($date_column, $dates['value'], '>')->condition($date_column2, $dates['end_value'], '<')
      )
    );

    $query->orderBy($date_column, 'ASC');

    // Add a generic entity access tag to the query.
    $query->addTag('merci_resource');
    $query->addMetaData('merci_reservable_handler', $this);

    return $query;
  }
  public function reservations($dates, $exclude_id) {
    $bestfit = $this->bestFit($dates);
    $reservations = array();
    foreach ($bestfit as $enity_id => $reservation) {
      $reservations[] = $entity_id;
    }
    return $reservations;
  }

  public function fillBuckets() {
    $conflicts = array();

    $dates = $this->entity->get($this->date_field);
    foreach ($dates as $date) {
      $date_value = $date->get('value')->getValue();
      $result = $this->bestFit($date);
      // Result is array indexed by $delta of filled buckets.
      foreach ($result as $delta => $buckets) {
        if (!isset($conflicts[$delta])) {
          $conflicts[$delta] = array();
        }
        $conflicts[$delta][$date_value] = $buckets;

      }
    }
    return $conflicts;
  }

  /*
   * Perform first-fit algorhtym on reservations into buckets.
   *
   * Return array indexed by item delta of array of filled buckets.
   */
  public function bestFit($dates) {

    $entity = $this->entity;
    $best_fit = array();


    $parent_conflicts = $this->conflicts($dates);

    $date_value = $dates->get('value')->getValue();

    foreach ($entity->get($this->item_field) as $delta => $item) {

      // No need to sort into buckets if there is nothing to sort into buckets.
      if (!array_key_exists($delta, $parent_conflicts) or !array_key_exists($date_value, $parent_conflicts[$delta])) {
        continue;
      }

      // Determine if the quantity field exists.  If so use it.
      /* @FIXME
      try {
        $quantity = $item->{$context['quantity_field']}->value();
      } catch (EntityMetadataWrapperException $e) {
       */
        $quantity = 1;
      //}

      // Split reservations based on quantity.
      $reservations = array();

      foreach($parent_conflicts[$delta][$date_value] as $reservation) {
        for ($i = 0; $i < $reservation->quantity; $i++) {
          $reservations[] = $reservation;
        }
      }

      // Determine how many bucket items are needed for this time period.
      // Need to sort like this:
      //            .... time ....
      // item1  x x a a a x x x x x f x e e e x x x x x
      // item2  x x x d d d d d d x x x x c c c x x x x
      // item3  x x b b b b b b b b b b b b b x x x x x
      // etc ......
      //
      //      // Order by lenght of reservation descending.
      //      // Do first-fit algorythm.

      // Sort by length of reservation.
      uasort($reservations, array($this, "merci_bucket_cmp_length"));

      $buckets = array();
      // First-fit algorythm.
      foreach ($reservations as $test_reservation) {

        // Go through each bucket item to look for a available slot for this reservation.
        //
        // Find a bucket to use for this reservation.
        for ($i = 0; $i < $quantity; $i++) {

          $fits = TRUE;
          // Bucket already has other reservations we need to check against for a fit.
          if (array_key_exists($i, $buckets)) {
            foreach ($buckets[$i] as $reservation) {
              if ($this->merci_bucket_intersects($reservation, $test_reservation)) {
                //Conflict so skip saving the reservation to this slot and try to use the next bucket item.
                $fits = FALSE;
                break;
              }
            }
          }

          // We've found a slot so test the next reservation.
          if ($fits) {
            if (array_key_exists($i, $buckets)) {
              $buckets[$i] = array();
            }
            $buckets[$i][] = $test_reservation;
            break;
          }

        }
      }
      if (count($buckets)) {
        $best_fit[$delta] = $buckets;
      }
    }
    return $best_fit;
  }

/*
 * |----------------------|        range 1
 * |--->                           range 2 overlap
 *  |--->                          range 2 overlap
 *                        |--->    range 2 overlap
 *                         |--->   range 2 no overlap
 */
  private function merci_bucket_intersects($r1, $r2) {
    $value = $this->date_column;
    $end_value = $this->date_column2;
    /*
     * Make sure r1 start date is before r2 start date.
     */
    if (date_create($r1->{$value}) > date_create($r2->{$value})) {
      $temp = $r1;
      $r1 = $r2;
      $r2 = $temp;
    }

    if (date_create($r2->{$value}) <= date_create($r1->{$end_value})) {
      return true;
    }
    return false;

  }

  private function merci_bucket_cmp_length($a, $b) {
    $value = $this->date_column;
    $end_value = $this->date_column2;
    $len_a = date_format(date_create($a->{$end_value}),'U') - date_format(date_create($a->{$value}), 'U');
    $len_b = date_format(date_create($b->{$end_value}),'U') - date_format(date_create($b->{$value}), 'U');
    if ($len_a == $len_b) {
      return 0;
    }
    return ($len_a < $len_b) ? 1 : -1;
  }

}
