<?php

/**
 * @file
 * Definition of Drupal\date_combo\Plugin\Field\FieldType\DateCombo.
 */

namespace Drupal\date_combo\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
  *  * Defines the 'datecombo' entity field type.
  *
  * @FieldType(
  *   id = "date_combo",
  *   label = @Translation("Date Combo"),
  *   description = @Translation("An entity field containing a
      datetime value with start and end time."),
  *   default_widget = "date_combo_default",
  *   default_formatter = "date_combo_default",
  *   list_class = "\Drupal\date_combo\Plugin\Field\FieldType\DateComboFieldItemList"
  * )
  */
class DateCombo extends FieldItemBase {

  /**
    ** {@inheritdoc}
    */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Start Date value'))
      ->setRequired(TRUE);

    $properties['value2'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('End Date value'))
      ->setRequired(TRUE);


    $properties['date'] = DataDefinition::create('any')
      ->setLabel(t('Computed start date'))
      ->setDescription(t('The computed start DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'value');

    $properties['date2'] = DataDefinition::create('any')
      ->setLabel(t('Computed start date'))
      ->setDescription(t('The computed start DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'value2');

    return $properties; 
  }

  /**
    ** {@inheritdoc}
    */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'description' => 'The start date value.',
          'type' => 'varchar',
          'length' => 20,
        ),
        'value2' => array(
          'description' => 'The end date value.',
          'type' => 'varchar',
          'length' => 20,
        ),
      ),
      'indexes' => array(
        'value' => array('value'),
        'value2' => array('value2'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {

    // Just pick a date in the past year. No guidance is provided by this Field
    // type.
    $timestamp = REQUEST_TIME - mt_rand(0, 86400*365);
    $values['value'] = gmdate(DATETIME_DATETIME_STORAGE_FORMAT, $timestamp);
    $values['value2'] = gmdate(DATETIME_DATETIME_STORAGE_FORMAT, $timestamp + 3600);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    $value2 = $this->get('value2')->getValue();
    return $value === NULL || $value === '' || $value2 === NULL || $value2 === '';
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    // Enforce that the computed date is recalculated.
    if ($property_name == 'value') {
      $this->date = NULL;
    }
    if ($property_name == 'value2') {
      $this->date2 = NULL;
    }
    parent::onChange($property_name, $notify);
  }
}
