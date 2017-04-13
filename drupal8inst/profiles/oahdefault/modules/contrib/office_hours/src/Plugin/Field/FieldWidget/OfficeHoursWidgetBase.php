<?php

namespace Drupal\office_hours\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for the 'office_hours_*' widgets.
 */
class OfficeHoursWidgetBase extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = array(
      'date_element_type' => 'datelist',
    ) + parent::defaultSettings();

    return $settings;
  }

  /**
   * Returns the array of field settings, added with hours data.
   *
   * @return array
   *   The array of settings.
   */
  public function getFieldSettings() {
    $settings = parent::getFieldSettings();
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // N.B. The $values are already reformatted in the subWidgets.

    foreach ($values as $key => &$item) {
      if ($item['starthours'] == '' && $item['endhours'] == '' && $item['comment'] == '') {
        unset($values[$key]);
      }
      elseif ($item['starthours'] == '' && $item['endhours'] == '' && $item['comment'] != '') {
        // @todo: allow closed days with comment. However, this is prohibited
        //        by the database: value '' is not allowed. The format is
        //        int(11). Would changing the format to 'string' help?
        unset($values[$key]);
      }
      else {
        // Avoid core's error "This value should be of the correct primitive type."
        // by casting the times to integer.
        // This is needed for e.g., 0000 and 0030.
        $item['starthours'] = (int) $item['starthours'];
        $item['endhours'] = (int) $item['endhours'];
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['date_element_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Time element type'),
      '#description' => $this->t('Select the widget type for inputing time.'),
      '#options' => array(
        'datelist' => 'Select list',
        'datetime' => 'HTML5 time input',
      ),
      '#default_value' => $this->getSetting('date_element_type'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Time element type: @date_element_type', array('@date_element_type' => $this->getSetting('date_element_type')));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
//  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
//    return $element;
//  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    return $element;
  }

}
