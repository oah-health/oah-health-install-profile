<?php
/**
 * @file
 * Contains \Drupal\date_combo\Plugin\Field\FieldWidget\DateComboDefaultWidget.
 */

namespace Drupal\date_combo\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'datetime_default' widget.
 *
 * @FieldWidget(
 *   id = "date_combo_default",
 *   label = @Translation("Date and time"),
 *   field_types = {
 *     "date_combo"
 *   }
 * )
 */
class DateComboDefaultWidget extends DateTimeWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->dateStorage = $date_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Identify the type of date and time elements to use.
    switch ($this->getFieldSetting('datetime_type')) {
      case DateTimeItem::DATETIME_TYPE_DATE:
        $date_type = 'date';
        $time_type = 'none';
        $date_format = $this->dateStorage->load('html_date')->getPattern();
        $time_format = '';
        break;

      default:
        $date_type = 'date';
        $time_type = 'time';
        $date_format = $this->dateStorage->load('html_date')->getPattern();
        $time_format = $this->dateStorage->load('html_time')->getPattern();
        break;
    }
    $element['value2'] = $element['value'];

    $element['value'] += array(
      '#title' => t('Start'),
      '#date_date_format'=>  $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => array(),
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => array(),
    );

    $element['value2'] += array(
      '#title' => t('End'),
      '#date_date_format'=>  $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => array(),
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => array(),
    );

    if ($items[$delta]->date2) {
      $date = $items[$delta]->date2;
      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      if ($this->getFieldSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE) {
        // A date without time will pick up the current time, use the default
        // time.
        datetime_date_default_time($date);
      }
      $date->setTimezone(new \DateTimeZone($element['value2']['#date_timezone']));
      $element['value2']['#default_value'] = $date;
    }
    $element['#type'] = 'fieldset';
    unset($element['#theme_wrappers']);

    return $element;

  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timezone and format.
    foreach ($values as &$item) {
      if (!empty($item['value']) && !empty($item['value2']) && $item['value'] instanceof DrupalDateTime && $item['value2'] instanceof DrupalDateTime) {
        $date = $item['value'];
        $date2 = $item['value2'];
        switch ($this->getFieldSetting('datetime_type')) {
          case DateTimeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($date);
            datetime_date_default_time($date2);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $date2->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['value'] = $date->format($format);
        $item['value2'] = $date2->format($format);
      }
    }
    return $values;
  }

}
