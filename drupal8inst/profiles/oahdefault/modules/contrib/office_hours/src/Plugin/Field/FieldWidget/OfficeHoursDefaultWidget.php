<?php

namespace Drupal\office_hours\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\office_hours\OfficeHoursDateHelper;

/**
 * Plugin implementation of the 'office_hours_default' widget.
 *
 * @FieldWidget(
 *   id = "office_hours_default",
 *   label = @Translation("Office hours (week)"),
 *   field_types = {
 *     "office_hours",
 *   },
 *   multiple_values = "FALSE",
 * )
 */
class OfficeHoursDefaultWidget extends OfficeHoursWidgetBase {

  /**
   * Special handling to create form elements for multiple values.
   * N.B. This is never called with Annotation: multiple_values = "FALSE",
   *
   * Removed the added generic features for multiple fields:
   * - number of widgets
   * - AHAH-'add more' button
   * - table display and drag-n-drop value reordering
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_cardinality = $this->fieldDefinition->getFieldStorageDefinition()
      ->getCardinality();
    if ($field_cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $this->fieldDefinition->getFieldStorageDefinition()
        ->setCardinality(7 * $this->getFieldSetting('cardinality_per_day'));
    }

    $elements = parent::formMultipleElements($items, $form, $form_state);

    // Remove the 'drag-n-drop reordering' element.
    $elements['#cardinality_multiple'] = FALSE;
    // Remove the little 'Weight for row n' box.
    unset($elements[0]['_weight']);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // In D8, we have an anomaly in the widget.
    // We prepare 1 widget for the whole week, but the field has unlimited
    // cardinality. So with $delta = 0, we show ALL values.
    // @todo D8: make the table widget correct again. See string 'link'.
    // @see https://www.drupal.org/node/2064123 "Field types are now plugins"
    // @see https://www.drupal.org/node/2112677 "Dynamic/Virtual field values using computed field property classes"
    if ($delta > 0) {
      return;
    }

    // Get field settings, to make it accessible for each element in other functions.
    $settings = $this->getFieldSettings();

    $cardinality = $this->getFieldSetting('cardinality_per_day');

// BEGIN D7 Code
    // Create an indexed two level array of time slots.
    // First level are day numbers. Second level contains field values arranged by daydelta.
    $indexed_items = array_fill_keys(range(0, 6), array());
    foreach ($items as $index => $item) {
      $value_of_item = $item->getValue();
      if ($item && !empty($value_of_item)) {
        $indexed_items[(int) $value_of_item['day']][] = $item;
      }
    }

    // Build elements, sorted by first_day_of_week.
    $elements = array();
    $days = OfficeHoursDateHelper::weekDaysOrdered(range(0, 6));
    $daynames = OfficeHoursDateHelper::weekDays(FALSE);
    $id = -1;
    foreach ($days as $index => $day) {
      // todo: theme_function clears values above cardinality. move it here.
      for ($daydelta = 0; $daydelta < $cardinality; $daydelta++) {
        $id++;
        $elements[$id]['#day'] = $day;
        $elements[$id]['#daydelta'] = $daydelta;
        $elements[$id]['#dayname'] = $daynames[$day];

        $elements[$id]['#type'] = 'office_hours_slot';
        $elements[$id]['#default_value'] = isset($indexed_items[$day][$daydelta]) ? $indexed_items[$day][$daydelta]->getValue() : NULL;
        $elements[$id]['#field_settings'] = $settings;
        $elements[$id]['#date_element_type'] = $this->getSetting('date_element_type');
      }
    }

    // Build multi element widget.
    $element['value'] = [
        /*
        // Begin D7-code.
        //      '#theme' => 'office_hours_week',
        '#field_name' => $items->getName(),
        '#title' => $this->fieldDefinition->getLabel(),
        //      '#required' => $this->fieldDefinition->isRequired(),
        '#description' => $this->fieldDefinition->getDescription(),
        // End   D7-code.
        */
        '#type' => 'office_hours_table',
        '#header' => array(
          $this->t($element['#title']),
          // $this->t('Day'),
          $this->t('From'),
          $this->t('To'),
          $this->t('Comment'),
        ),
        '#tableselect' => FALSE,
        '#tabledrag' => FALSE,
      ] + $elements;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // See also function formElement(), formMultipleElements().
    // Reformat the $values, before passing to database.
    $multiple_values = $this->getPluginDefinition()['multiple_values'];
    if ($multiple_values == 'FALSE') {
      // Below line works fine with Annotation: multiple_values = "FALSE",
      $values = $values['value'];
    }
    elseif ($multiple_values == 'TRUE') {
      // Below lines should work fine with Annotation: multiple_values = "TRUE",
      $values = reset($values)['value'];
    }
    else {
      // Below line will never work.
      // return parent::massageFormValues($values, $form, $form_state);
    }

    $values = parent::massageFormValues($values, $form, $form_state);

    return $values;
  }

}
