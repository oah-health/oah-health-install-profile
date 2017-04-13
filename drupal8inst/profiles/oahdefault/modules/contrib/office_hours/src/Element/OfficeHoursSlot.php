<?php

namespace Drupal\office_hours\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a one-line text field form element.
 *
 * @FormElement("office_hours_slot")
 */
class OfficeHoursSlot extends OfficeHoursList {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo();
  }

  /**
   * {@inheritdoc}
   */
//  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
//    if ($input !== FALSE) {
//      $input = parent::valueCallback($element, $input, $form_state);
//    }
//    else {
//      $input = parent::valueCallback($element, $input, $form_state);
//    }
//    return $input;
//  }

//  public static function preRenderOfficeHoursSlot($element) {
//    // ...
//  }

  /**
   * Process an individual element.
   *
   * Build the form element. When creating a form using FAPI #process,
   * note that $element['#value'] is already set.
   */
  public static function processOfficeHoursSlot(&$element, FormStateInterface $form_state, &$complete_form) {
    // Fill with default data from a List element.
    $element = parent::processOfficeHoursSlot($element, $form_state, $complete_form);

    // @todo D8: $form_state = ...
    // @todo D8: $form = ...
    // @todo D8: Repair the javascript and the links. They have vanished.
    //           I did see them at some point in D8, though.
    //           IMO the repair is in this function.

    $prefix = '';
    $slots_per_day = $element['#field_settings']['cardinality_per_day'];
    $daydelta = $element['#daydelta'];
    if ($daydelta == 0) {
      // This is the first slot: show the dayname.
      $label = $element['#dayname']; // Day name is already translated.
      $label_style = '';
      $slot_style = '';
      $slot_class = 'office-hours-slot';
    }
    elseif ($daydelta >= $slots_per_day) {
      // In case the number of slots per day was lowered by admin, this element
      // may have a value. Better clear it (in case a value was entered before).
      // The value will be removed upon the next 'Save' action.
      $label = '';
      $label_style = '';
      // The following style is only needed if js isn't working.
      $slot_style = 'style = "display:none;"';
      // The following class is the trigger for js to hide the row.
      $slot_class = 'office-hours-hide'; // @todo: Test

      $element['#value']['starthours'] = '';
      $element['#value']['endhours'] = '';
      $element['#value']['comment'] = NULL;
    }
    elseif (!empty($element['#value']['starthours'])) {
      // This is a following block with contents, so we show the times.
      $label = t('and');
      $label_style = 'style = "text-align:right;"';
      $slot_style = '';
      $slot_class = 'office-hours-slot';
    }
    else {
      // This is an empty following slot: show the 'add hours link'.
      $label = t('and');
      $label_style = 'style = "text-align:right;"';
      $slot_style = 'style = "display:none;"';
      $slot_class = 'office-hours-hide';

      $link = \Drupal::l(t('Add new @node_type', array('@node_type' => t('Time'), '%type' => 'Time')), \Drupal\Core\Url::fromRoute('<front>'));
      $prefix .= $link;
    }
    $prefix .= '<label ' . $label_style . '>' . $label . '</label>';
    $element['#prefix'] = $prefix;

    $element['#attributes']['class'][] = $slot_class;

    // Overwrite the 'day' select-field.
    $element['day'] = array(
      '#type' => 'hidden',
      '#prefix' => ' ' . $label,
//      '#prefix' => $prefix,
      '#default_value' => $element['#day'],
    );
    // Starthours and Endhours are already OK.
    // $element['starthours'] = array();
    // $element['endhours'] = array();

    return $element;
  }

  /**
   * Render API callback: Validates the office_hours_slot element.
   *
   * Implements a callback for _office_hours_elements().
   *
   * For 'office_hours_slot' (day) and 'office_hours_select' (hour) elements.
   * You can find the value in $element['#value'], but better in $form_state['values'],
   * which is set in _office_hours_select_validate().
   */
//  public static function validateOfficeHoursSlot(&$element, FormStateInterface $form_state, &$complete_form) {
//     return parent::validateOfficeHoursSlot($element, $form_state, $complete_form);
//  }

}
