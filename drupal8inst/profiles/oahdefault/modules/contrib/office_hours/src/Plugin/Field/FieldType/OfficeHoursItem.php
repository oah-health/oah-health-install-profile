<?php

namespace Drupal\office_hours\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\office_hours\OfficeHoursDateHelper;

/**
 * Plugin implementation of the 'office_hours' field type.
 *
 * @FieldType(
 *   id = "office_hours",
 *   label = @Translation("Office hours"),
 *   description = @Translation("This field stores weekly 'office hours' or 'opening hours' in the database."),
 *   default_widget = "office_hours_default",
 *   default_formatter = "office_hours",
 *   list_class = "\Drupal\office_hours\Plugin\Field\FieldType\OfficeHoursItemList",
 * )
 */
class OfficeHoursItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    // todo D8: waar komt dit vandaan?  $maxlenght = $field_definition->getSetting('maxlength');

    return array(
      'columns' => array(
        'day' => array(
          'type' => 'int',
          'not null' => FALSE,
        ),
        'starthours' => array(
          'type' => 'int',
          'not null' => FALSE,
        ),
        'endhours' => array(
          'type' => 'int',
          'not null' => FALSE,
        ),
        'comment' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    //    $properties['value'] = DataDefinition::create('string')
    //      ->setLabel( $this->t('Office hours'));
    $properties['day'] = DataDefinition::create('integer')
      ->setLabel(t('Day'))
      ->setDescription("Stores the day of the week's numeric representation (0-6)");
    $properties['starthours'] = DataDefinition::create('integer')
      ->setLabel(t('Start hours'))
      ->setDescription("Stores the start hours value");
    $properties['endhours'] = DataDefinition::create('integer')
      ->setLabel(t('End hours'))
      ->setDescription("Stores the end hours value");
    $properties['comment'] = DataDefinition::create('string')
      ->setLabel(t('Comment'))
      ->addConstraint('Length', array('max' => 255))
      ->setDescription("Stores the comment");

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $defaultStorageSettings = array(
        'time_format' => 'G',
        'increment' => 30,
        'limit_start' => '',
        'limit_end' => '',
        'valhrs' => 0,
        'cardinality_per_day' => 2,
      ) + parent::defaultStorageSettings();

    return $defaultStorageSettings;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();

    $settings = $this->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getSettings();

    // Get a formatted list of hours.
    $hours = OfficeHoursDateHelper::hours('H', FALSE);
    foreach ($hours as $key => &$hour) {
      if (!empty($hour)) {
        $hrs = OfficeHoursDateHelper::format($hour . '00', 'H:i');
        $ampm = OfficeHoursDateHelper::format($hour . '00', 'g:i a');
        $hour = "$hrs ($ampm)";
      }
    }

    $element['#element_validate'] = array(array($this, 'officeHoursValidate'));
    $description = $this->t(
      'The maximum number of slots, that are allowed per day.
      <br/><strong> Warning! Lowering this setting after data has been created
      could result in the loss of data! </strong><br/> Be careful when using
      more then 2 slots per day, since not all external services (like Google
      Places) support this.');
    $element['cardinality_per_day'] = array(
      '#type' => 'select',
      '#title' => $this->t('Number of slots'),
      // @todo for 'multiple slots per day': add support for FIELD_CARDINALITY_UNLIMITED.
      // '#options' => array(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED => $this->t('unlimited')) + drupal_map_assoc(range(1, 10)),
      '#options' => array_combine(range(1, 12), range(1, 12)),
      '#default_value' => $settings['cardinality_per_day'],
      '#description' => $description,
      // '#disabled' => $has_data,
    );

    // @todo D8: aligen with DateTimeDatelistWidget.
    $element['time_format'] = array(
      '#type' => 'select',
      '#title' => $this->t('Time notation'),
      '#options' => array(
        'G' => $this->t('24 hour time') . ' (9:00)', // D7: key = 0
        'H' => $this->t('24 hour time') . ' (09:00)', // D7: key = 2
        'g' => $this->t('12 hour time') . ' (9:00 am)', // D7: key = 1
        'h' => $this->t('12 hour time') . ' (09:00 am)', // D7: key = 1
      ),
      '#default_value' => $settings['time_format'],
      '#required' => FALSE,
      '#description' => $this->t('Format of the time in the widget.'),
    );
    // @todo D8: align with DateTimeDatelistWidget.
    $element['increment'] = array(
      '#type' => 'select',
      '#title' => $this->t('Time increments'),
      '#default_value' => $settings['increment'],
      '#options' => array(
        1 => $this->t('1 minute'),
        5 => $this->t('5 minute'),
        15 => $this->t('15 minute'),
        30 => $this->t('30 minute'),
        60 => $this->t('60 minute')
      ),
      '#required' => FALSE,
      '#description' => $this->t('Restrict the input to fixed fractions of an hour.'),
    );

    $element['valhrs'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Validate hours'),
      '#required' => FALSE,
      '#default_value' => $settings['valhrs'],
      '#description' => $this->t('Assure that endhours are later then starthours. Please note that this will work as long as the opening hours are not through midnight.'),
    );
    $element['limit_start'] = array(
      '#type' => 'select',
      '#title' => $this->t('Limit widget hours - from'),
      '#description' => $this->t('Restrict the hours available - select options will start from this hour.'),
      '#default_value' => $settings['limit_start'],
      '#options' => $hours,
    );
    $element['limit_end'] = array(
      '#type' => 'select',
      '#title' => $this->t('Limit widget hours - until'),
      '#description' => $this->t('Restrict the hours available - select options will end at this hour.'),
      '#default_value' => $settings['limit_end'],
      '#options' => $hours,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // @TODO : for Week-widget, day is always <> ''
    //         for list-widget, day can be ''.
    // N.B. Test every change with both widgets!
    if ( // !$this->get('day')->getValue() == '' &&
      $this->get('starthours')->getValue() == '' &&
      $this->get('endhours')->getValue() == '' &&
      $this->get('comment')->getValue() == ''
    ) {
      return TRUE;
    }
    return FALSE;
  }

  public function getConstraints() {
    $constraints = array();
// @todo: when adding parent::getConstraints(), only English is allowed...
//    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraint_manager = \Drupal::typedDataManager()
        ->getValidationConstraintManager();
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'value' => array(
          'Length' => array(
            'max' => $max_length,
            'maxMessage' => $this->t('%name: may not be longer than @max characters.', array(
              '%name' => $this->getFieldDefinition()
                ->getLabel(),
              '@max' => $max_length
            )),
          ),
        ),
      ));
    }
    return $constraints;
  }

  /**
   * Implements the #element_validate callback for storageSettingsForm().
   *
   * Verifies the office hours limits.
   * "Please note that this will work as long as the opening hours are not through midnight."
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function officeHoursValidate(array $element, FormStateInterface &$form_state) {
    if ($element['limit_start']['#value'] > $element['limit_end']['#value']) {
      $form_state->setError($element['limit_start'], $this->t('%start is later then %end.', array(
        '%start' => $element['limit_start']['#title'],
        '%end' => $element['limit_end']['#title'],
      )));
    }
  }

}
