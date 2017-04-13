<?php

namespace Drupal\office_hours\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\office_hours\OfficeHoursDateHelper;

/**
 * Plugin implementation of the 'office_hours_week' widget.
 *
 * @FieldWidget(
 *   id = "office_hours_list",
 *   label = @Translation("Office hours (list)"),
 *   field_types = {
 *     "office_hours",
 *   }
 * )
 */
class OfficeHoursListWidget extends OfficeHoursWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Get field settings, to make it accessible for each element in other functions.
    $settings = $this->getFieldSettings();

    $default_value = isset($items[$delta]) ? $items[$delta]->getValue() : NULL;
    $day = isset($default_value['day']) ? $default_value['day'] : '';
    $daynames = OfficeHoursDateHelper::weekDays(FALSE);
    $element['value'] = $element + array(
        '#type' => 'office_hours_list',
        '#default_value' => $default_value,
        '#day' => $day,
        // Make sure the value is shown in OfficeHoursSlot.
        '#daydelta' => 0, // $daydelta;
        '#dayname' => $daynames[$day],
        '#field_settings' => $settings,
        '#date_element_type' => $this->getSetting('date_element_type'),
      );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This repairs the anomaly we mentioned before.
    // See also function formElement().

    // Reformat the $values, before passing to database.
    // N.B. Resetting does not work: $values = reset($values)['value'];
    foreach ($values as $value) {
      $new_values[] = $value['value'];
    }
    $values = $new_values;

    $values = parent::massageFormValues($values, $form, $form_state);

    return $values;
  }
}
