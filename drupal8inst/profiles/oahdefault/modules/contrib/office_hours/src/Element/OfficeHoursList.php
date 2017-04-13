<?php

namespace Drupal\office_hours\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\office_hours\OfficeHoursDateHelper;

/**
 * Provides a one-line basic form element.
 *
 * @FormElement("office_hours_list")
 */
class OfficeHoursList extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $info = array(
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => array(
        array($class, 'processOfficeHoursSlot'),
      ),
      '#element_validate' => array(
        array($class, 'validateOfficeHoursSlot'),
      ),
      '#attached' => array(
        'library' => array(
          'office_hours/office_hours',
        ),
      ),
    );
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $input = parent::valueCallback($element, $input, $form_state);
    return $input;
  }

//  public static function preRender($element) {
//    // ...
//  }

  /**
   * Process an individual element.
   *
   * Build the form element. When creating a form using FAPI #process,
   * note that $element['#value'] is already set.
   */
  public static function processOfficeHoursSlot(&$element, FormStateInterface $form_state, &$complete_form) {
    // @todo D8: $form_state = ...
    // @todo D8: $form = ...
    // @todo D8: Repair the javascript and the links. They have vanished.

    $slot_class = 'office-hours-slot';
    $prefix = '';
    $suffix = '';
    $element['#attributes']['class'][] = 'form-item'; //D8
    $element['#attributes']['class'][] = 'container-inline';
    $element['#attributes']['class'][] = $slot_class;

    // Show a 'Clear this line' js-link to each element.
    // Use text 'Remove', which has lots of translations.
    $clear_link = \Drupal::l( t('Remove'), \Drupal\Core\Url::fromRoute('<front>'));
    $suffix .= "<div class='office-hours-clear-link'>" . $clear_link . '</div>';
    $element['#suffix'] = $suffix;

    $element['day'] = array(
//      '#type' => 'value',
//      '#prefix' => ' ' . $label,
//      '#value' => $element['#day'],
      '#type' => 'select',
//      '#title' => $this->t('Day'),
      '#options' => OfficeHoursDateHelper::weekDays(FALSE),
      '#default_value' => isset($element['#value']['day']) ? $element['#value']['day'] : NULL,
      '#description' => '',
    );
    $element['starthours'] = array(
      '#type' => 'office_hours_select', // datelist, datetime.
      '#default_value' => isset($element['#value']['starthours']) ? $element['#value']['starthours'] : NULL,
      '#field_settings' => $element['#field_settings'],
      // Attributes for element \Drupal\Core\Datetime\Element\Datelist.
      '#date_part_order' => (in_array($element['#field_settings']['time_format'], ['g', 'h']))
        ? array('hour', 'minute', 'ampm',)
        : array('hour', 'minute',),
      '#date_increment' => $element['#field_settings']['increment'],
    );
    $element['endhours'] = array(
      '#type' => 'office_hours_select', // datelist, datetime.
//      // Using capitals enables automatic translation. mb_strtolower also converts diacritical characters.
//      '#prefix' => mb_strtolower( $this->t('To', array('context' => 'office_hours')) ) . ' ',
//      '#suffix' => $suffix,
      '#default_value' => isset($element['#value']['endhours']) ? $element['#value']['endhours'] : NULL,
      '#field_settings' => $element['#field_settings'],
      // Attributes for element \Drupal\Core\Datetime\Element\Datelist.
      '#date_part_order' => (in_array($element['#field_settings']['time_format'], ['g', 'h']))
        ? array('hour', 'minute', 'ampm',)
        : array('hour', 'minute',),
      '#date_increment' => $element['#field_settings']['increment'],
//      '#theme_wrappers' => array('datetime_wrapper'),
//      '#date_date_format' => 'none', // $date_format,
//      '#date_date_element' => 'none', // 'date',
//      '#date_date_callbacks' => array(),
//      '#date_time_format' => $time_format,
//      '#date_time_element' => 'time',
//      '#date_time_callbacks' => array(),
//      '#date_year_range' => FALSE, // '2000:2000',
//      '#date_timezone' => '',
// End of attributes for datelist element.
    );
    $element['comment'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($element['#value']['comment']) ? $element['#value']['comment'] : NULL,
      '#size' => 20,
      '#maxlength' => 255,
      '#field_settings' => $element['#field_settings'],
    );

    return $element;
  }

  /**
   * Render API callback: Validates the element.
   *
   * Implements a callback for _office_hours_elements().
   *
   * For 'office_hours_slot' (day) and 'office_hours_select' (hour) elements.
   * You can find the value in $element['#value'], but better in $form_state['values'],
   * which is set in _office_hours_select_validate().
   */
  public static function validateOfficeHoursSlot(&$element, FormStateInterface $form_state, &$complete_form) {
    $error_text = '';

    $input_exists = TRUE;

    if ($input_exists) {

      $valhrs = $element['#field_settings']['valhrs'];
      $limit_start = $element['#field_settings']['limit_start'];
      $limit_end = $element['#field_settings']['limit_end'];

      // Numeric value is set in OfficeHoursSelect::validateOfficeHours()
      $start = (is_numeric($element['starthours']['#value'])) ? $element['starthours']['#value'] : '';
      $end = (is_numeric($element['endhours']['#value'])) ? $element['endhours']['#value'] : '';

      if (!empty($start) xor !empty($end)) {
        $error_text = 'Both Opening hours and Closing hours must be set.';
      }
      elseif ($valhrs && ($start > $end)) {
        $error_text = 'Closing hours are earlier than Opening hours.';
      }
      elseif (!empty($limit_start) || !empty($limit_end)) {
        if (($start && ($limit_start * 100) > $start) || ($end && ($limit_end * 100) < $end)) {
          $error_text = 'Hours are outside limits ( @start - @end ).';
        }
      }

//        if ($hour < 0 || $hour > 23) {
//          $error_text = $this->t('Hours should be between 0 and 23.', array(), array('office_hours'));
//          $form_state->setErrorByName('office_hours_select', $error_text);
//        }
//        if ($minute < 0 || $minute > 59) {
//          $error_text = $this->t('Minutes should be between 0 and 59.', array(), array('office_hours'));
//          $form_state->setErrorByName('office_hours_select', $error_text);
//        }

      if ($error_text) {
        $error_text = $element['#dayname']  // Day name is already translated.
          . ': '
          . t($error_text,
            array(
              '@start' => $limit_start . ':00',
              '@end' => $limit_end . ':00',
            ),
            array('context' => 'office_hours')
          );
        $form_state->setError($element, $error_text);
      }
    }
  }

}
