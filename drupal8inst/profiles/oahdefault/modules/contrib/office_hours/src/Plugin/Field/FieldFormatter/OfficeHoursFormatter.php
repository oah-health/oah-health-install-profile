<?php

namespace Drupal\office_hours\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\office_hours\OfficeHoursDateHelper;

/**
 * Plugin implementation of the formatter.
 *
 * @FieldFormatter(
 *   id = "office_hours",
 *   label = @Translation("Office hours"),
 *   field_types = {
 *     "office_hours",
 *   }
 * )
 */
class OfficeHoursFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'day_format' => 'long',
      'time_format' => 'G',
      'compress' => FALSE,
      'grouped' => FALSE,
      'show_closed' => 'all',
      'closed_format' => 'Closed',
      // The html-string for closed/empty days.
      'separator' => array(
        'days' => '<br />',
        'grouped_days' => ' - ',
        'day_hours' => ': ',
        'hours_hours' => '-',
        'more_hours' => ', ',
      ),
      'current_status' => array(
        'position' => 'hide',
        'open_text' => 'Currently open!',
        'closed_text' => 'Currently closed',
      ),
      'timezone_field' => '',
      'office_hours_first_day' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = array();

    $settings = $this->getSettings();
    $day_names = OfficeHoursDateHelper::weekDays(FALSE);
    $day_names[''] = $this->t("- system's Regional settings -");

    // todo D8: get settings per view mode.
//    $display = $instance['display'][$view_mode];
//    $settings = _office_hours_field_formatter_defaults($instance['display'][$view_mode]['settings']);

    /*
      // Find timezone fields, to be used in 'Current status'-option.
      $fields = field_info_instances( (isset($form['#entity_type']) ? $form['#entity_type'] : NULL), (isset($form['#bundle']) ? $form['#bundle'] : NULL));
      $timezone_fields = array();
      foreach ($fields as $field_name => $timezone_instance) {
        if ($field_name == $field['field_name']) {
          continue;
        }
        $timezone_field = field_read_field($field_name);

        if (in_array($timezone_field['type'], array('tzfield'))) {
          $timezone_fields[$timezone_instance['field_name']] = $timezone_instance['label'] . ' (' . $timezone_instance['field_name'] . ')';
        }
      }
      if ($timezone_fields) {
        $timezone_fields = array('' => '<None>') + $timezone_fields;
      }
     */

    // @TODO: The settings could go under the several 'core' settings,
    // as above in the implemented hook_FORMID_form_alter functions.
    /*
    $element = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Office hours formatter settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#weight' => 5,
    );
    */

    $element['show_closed'] = array(
      '#type' => 'select',
      '#title' => $this->t('Number of days to show'),
      '#options' => array(
        'all' => $this->t('Show all days'),
        'open' => $this->t('Show only open days'),
        'next' => $this->t('Show next open day'),
        'none' => $this->t('Hide all days'),
        'current' => $this->t('Show only current day'),
      ),
      '#default_value' => $settings['show_closed'],
      '#description' => $this->t('The days to show in the formatter. Useful in combination with the Current Status block.'),
    );
    // First day of week, copied from system.variable.inc.
    $element['office_hours_first_day'] = array(
      '#type' => 'select',
      '#options' => $day_names,
      '#title' => $this->t('First day of week'),
      '#default_value' => $this->getSetting('office_hours_first_day'),
    );
    $element['day_format'] = array(
      '#type' => 'select',
      '#title' => $this->t('Day notation'),
      '#options' => array(
        'long' => $this->t('long'),
        'short' => $this->t('short'),
        'number' => $this->t('number'),
        'none' => $this->t('none'),
      ),
      '#default_value' => $settings['day_format'],
    );
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
      '#description' => $this->t('Format of the clock in the formatter.'),
    );
    $element['compress'] = array(
      '#title' => $this->t('Compress all hours of a day into one set'),
      '#type' => 'checkbox',
      '#default_value' => $settings['compress'],
      '#description' => $this->t('Even if more hours is allowed, you might want to show a compressed form. E.g.,  7:00-12:00, 13:30-19:00 becomes 7:00-19:00.'),
      '#required' => FALSE,
    );
    $element['grouped'] = array(
      '#title' => $this->t('Group consecutive days with same hours into one set'),
      '#type' => 'checkbox',
      '#default_value' => $settings['grouped'],
      '#description' => $this->t('E.g., Mon: 7:00-19:00; Tue: 7:00-19:00 becomes Mon-Tue: 7:00-19:00.'),
      '#required' => FALSE,
    );
    $element['closed_format'] = array(
      '#type' => 'textfield',
      '#size' => 30,
      '#title' => $this->t('Empty days notation'),
      '#default_value' => $settings['closed_format'],
      '#required' => FALSE,
      '#description' => $this->t('Format of empty (closed) days. You can use translatable text and HTML in this field.'),
    );

    // Taken from views_plugin_row_fields.inc.
    // Show a 'Current status' option.
    $element['separator'] = array(
      '#title' => $this->t('Separators'),
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $element['separator']['days'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#default_value' => $settings['separator']['days'],
      '#description' => $this->t('This separator will be placed between the days. Use &#39&ltbr&gt&#39 to show each day on a new line.'),
    );
    $element['separator']['grouped_days'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#default_value' => $settings['separator']['grouped_days'],
      '#description' => $this->t('This separator will be placed between the labels of grouped days.'),
    );
    $element['separator']['day_hours'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#default_value' => $settings['separator']['day_hours'],
      '#description' => $this->t('This separator will be placed between the day and the hours.'),
    );
    $element['separator']['hours_hours'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#default_value' => $settings['separator']['hours_hours'],
      '#description' => $this->t('This separator will be placed between the hours of a day.'),
    );
    $element['separator']['more_hours'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#default_value' => $settings['separator']['more_hours'],
      '#description' => $this->t('This separator will be placed between the hours and more_hours of a day.'),
    );

    // Show a 'Current status' option.
    $element['current_status'] = array(
      '#title' => $this->t('Current status'),
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $element['current_status']['position'] = array(
      '#type' => 'select',
      '#title' => $this->t('Current status position'),
      '#options' => array(
        'hide' => $this->t('Hidden'),
        'before' => $this->t('Before hours'),
        'after' => $this->t('After hours'),
      ),
      '#default_value' => $settings['current_status']['position'],
      '#description' => $this->t('Where should the current status be located?'),
    );
    $element['current_status']['open_text'] = array(
      '#title' => $this->t('Formatting'),
      '#type' => 'textfield',
      '#size' => 40,
      '#default_value' => $settings['current_status']['open_text'],
      '#description' => $this->t('Format of the message displayed when currently open. You can use translatable text and HTML in this field.'),
    );
    $element['current_status']['closed_text'] = array(
      '#type' => 'textfield',
      '#size' => 40,
      '#default_value' => $settings['current_status']['closed_text'],
      '#description' => $this->t('Format of message displayed when currently closed. You can use translatable text and HTML in this field.'),
    );

    /*
      if ($timezone_fields) {
        $element['timezone_field'] = array(
          '#type' => 'select',
          '#title' => $this->t('Timezone') . ' ' . $this->t('Field'),
          '#options' => $timezone_fields,
          '#default_value' => $settings['timezone_field'],
          '#description' => $this->t('Should we use another field to set the timezone for these hours?'),
        );
      }
      else {
        $element['timezone_field'] = array(
          '#type' => 'hidden',
          '#value' => $settings['timezone_field'],
        );
      }
     */

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // @todo: Return more info, like the Date module does.
    $summary[] = $this->t('Display Office hours in different formats.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
//  public function prepareView(array $entities_items) {
//    return parent::prepareView($entities_items);
//  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // @TODO: check if ($items->implementsInterface('OfficeHoursListInterface')) {
    //              if ($items instanceof OfficeHoursListInterface) {
    //              if ($items instanceof Drupal\Core\Field\FieldItemListInterface) {
    //              if (is_subclass_of($items, 'OfficeHoursListInterface')) {
    $office_hours = $items->getOfficeHoursArray(REQUEST_TIME);
    $open = $items->isOpen(REQUEST_TIME);

    if (empty($office_hours)) {
      return $elements;
    }

    $settings = $this->getSettings();
    // Get formatted weekday names.
    $day_names = OfficeHoursDateHelper::weekDaysByFormat($settings['day_format']);
    // Reorder weekdays to match the first day of the week, using formatter settings;
    $office_hours = OfficeHoursDateHelper::weekDaysOrdered($office_hours, $settings['office_hours_first_day']);

    // Check if we're compressing times. If so, combine lines of the same day into one.
    if ($settings['compress']) {
      $office_hours = $this->compressSlots($office_hours);
    }

    // Check if we're grouping days.
    if ($settings['grouped']) {
      $office_hours = $this->groupDays($office_hours);
    }

    // Check if we're showing not all days.
    switch ($settings['show_closed']) {
      case 'all':
        break;

      case 'open':
        $office_hours = $this->keepOpenDays($office_hours);
        break;

      case 'next':
        $office_hours = $this->keepNextDay($office_hours);
        break;

      case 'none':
        $office_hours = [];
        break;

      case 'current':
        $office_hours = $this->keepCurrentDay($office_hours);
        break;
    }

    $build[] = [
      '#theme' => 'office_hours',
      '#office_hours' => $office_hours,
      '#day_format' => $settings['day_format'],
      '#days_suffix' => $settings['separator']['day_hours'],
      '#item_separator' => $settings['separator']['days'],
      '#group_separator' => $settings['separator']['grouped_days'],
      '#slot_separator' => $settings['separator']['more_hours'],
      '#time_format' => OfficeHoursDateHelper::getTimeFormat($settings['time_format']),
      '#time_separator' => $settings['separator']['hours_hours'],
      '#closed_text' => $settings['closed_format'],
    ];

    $status_position = isset($settings['current_status']['position']) && in_array($settings['current_status']['position'], ['before', 'after'])
      ? $settings['current_status']['position']
      : NULL;

    if ($status_position) {
      $status = [
        '#theme' => 'office_hours_status',
        '#open' => $open,
        '#open_text' => $settings['current_status']['open_text'],
        '#closed_text' => $settings['current_status']['closed_text'],
      ];

      if ($status_position == 'before') {
        array_unshift($build, $status);
      }
      elseif ($status_position == 'after') {
        array_push($build, $status);
      }
    }
    return $build;

  }

  protected function compressSlots(array $office_hours) {
    foreach ($office_hours as &$info) {
      if (is_array($info['times'])) {
        // Initialize first slot of the day.
        $day_times = $info['times'][0];
        // Compress other slot in first slot.
        foreach ($info['times'] as $index => $slot_times) {
          $day_times['start'] = min($day_times['start'], $slot_times['start']);
          $day_times['end'] = max($day_times['end'], $slot_times['end']);
        }
        $info['times'] = [0 => $day_times];
      }
    }
    return $office_hours;
  }

  protected function groupDays(array $office_hours) {
    $times = [];
    for ($i = 0; $i < 7; $i++) {
      if ($i == 0) {
        $times = $office_hours[$i]['times'];
      }
      elseif ($times != $office_hours[$i]['times']) {
        $times = $office_hours[$i]['times'];
      }
      else {
        // N.B. for 0=Sundays, we need to (int) the indices.
        $office_hours[$i]['endday'] = $office_hours[(int) $i]['startday'];
        $office_hours[$i]['startday'] = $office_hours[(int) $i - 1]['startday'];
        $office_hours[$i]['current'] = $office_hours[(int) $i]['current'] || $office_hours[(int) $i - 1]['current'];
        $office_hours[$i]['next'] = $office_hours[(int) $i]['next'] || $office_hours[(int) $i - 1]['next'];
        unset($office_hours[(int) $i - 1]);
      }
    }
    return $office_hours;
  }

  protected function keepOpenDays(array $office_hours) {
    $new_office_hours = [];
    foreach ($office_hours as $day => $info) {
      if (!empty($info['times'])) {
        $new_office_hours[] = $info;
      }
    }
    return $new_office_hours;
  }

  protected function keepNextDay(array $office_hours) {
    $new_office_hours = [];
    foreach ($office_hours as $day => $info) {
      if ($info['current'] || $info['next']) {
        $new_office_hours[$day] = $info;
      }
    }
    return $new_office_hours;
  }

  protected function keepCurrentDay(array $office_hours) {
    $new_office_hours = [];

    $today = (int) idate('w', $_SERVER['REQUEST_TIME']); // Get daynumber sun=0 - sat=6.

    foreach ($office_hours as $day => $info) {
      if ($day == $today) {
        $new_office_hours[$day] = $info;
      }
    }
    return $new_office_hours;
  }

}
