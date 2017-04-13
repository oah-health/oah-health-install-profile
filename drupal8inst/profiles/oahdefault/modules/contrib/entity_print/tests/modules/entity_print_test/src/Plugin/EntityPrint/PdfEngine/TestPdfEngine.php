<?php

/**
 * @file
 * Contains \Drupal\entity_print_test\Plugin\EntityPrint\PdfEngine\TestPdfEngine
 */

namespace Drupal\entity_print_test\Plugin\EntityPrint\PdfEngine;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_print\PdfEngineException;
use Drupal\entity_print\Plugin\PdfEngineBase;

/**
 * @PdfEngine(
 *   id = "testpdfengine",
 *   label= @Translation("Test PDF Engine")
 * )
 */
class TestPdfEngine extends PdfEngineBase {

  /**
   * @var string
   */
  protected $html;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function send($filename = NULL) {
    // Echo the response and then flush, just like a PDF implementation would.
    echo 'Using testpdfengine - ' . $this->configuration['test_engine_suffix'];
    echo $this->html;
    flush();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['test_engine_setting'] = [
      '#title' => $this->t('Test setting'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['test_engine_setting'],
      '#description' => $this->t('Test setting'),
    ];
    $form['test_engine_suffix'] = [
      '#title' => $this->t('Suffix'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['test_engine_suffix'],
      '#description' => $this->t('Suffix'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['test_engine_setting'] = $form_state->getValue('test_engine_setting');
    $this->configuration['test_engine_suffix'] = $form_state->getValue('test_engine_suffix');
  }


  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('test_engine_setting') === 'rejected') {
      $form_state->setErrorByName('test_engine_setting', 'Setting has an invalid value');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'test_engine_setting' => '',
      'test_engine_suffix' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getError() {}

  /**
   * {@inheritdoc}
   */
  public function addPage($content) {
    $this->html = $content;
  }

  /**
   * {@inheritdoc}
   */
  public static function dependenciesAvailable() {
    return TRUE;
  }

}
