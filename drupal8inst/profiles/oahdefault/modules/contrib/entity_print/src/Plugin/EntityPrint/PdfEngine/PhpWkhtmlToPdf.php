<?php

/**
 * @file
 * Contains \Drupal\entity_print\Plugin\EntityPrint\PdfEngine\PhpWkhtmlToPdf
 */

namespace Drupal\entity_print\Plugin\EntityPrint\PdfEngine;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_print\PdfEngineException;
use Drupal\entity_print\Plugin\PdfEngineBase;
use mikehaertl\wkhtmlto\Pdf;

/**
 * @PdfEngine(
 *   id = "phpwkhtmltopdf",
 *   label = @Translation("Php Wkhtmltopdf")
 * )
 *
 * To use this implementation you will need the DomPDF library, simply run:
 *
 * @code
 *     composer require "mikehaertl/phpwkhtmltopdf ~2.1"
 * @endcode
 */
class PhpWkhtmlToPdf extends PdfEngineBase {

  /**
   * @var \mikehaertl\wkhtmlto\Pdf
   */
  protected $pdf;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pdf = new Pdf(['binary' => $this->configuration['binary_location']]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getInstallationInstructions() {
    return t('Please install with: @command', ['@command' => 'composer require "mikehaertl/phpwkhtmltopdf ~2.1"']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'binary_location' => '/usr/local/bin/wkhtmltopdf',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['binary_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Binary Location'),
      '#description' => $this->t('Set this to the system path where the PDF engine binary is located.'),
      '#default_value' => $this->configuration['binary_location'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $binary_location = $form_state->getValue('binary_location');
    if (!file_exists($binary_location)) {
      $form_state->setErrorByName('binary_location', sprintf('The wkhtmltopdf binary does not exist at %s', $binary_location));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function send($filename = NULL) {
    if (!$this->pdf->send($filename)) {
      throw new PdfEngineException(sprintf('Failed to generate PDF: %s', $this->pdf->getError()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addPage($content) {
    $this->pdf->addPage($content);
  }

  /**
   * {@inheritdoc}
   */
  public static function dependenciesAvailable() {
    return class_exists('mikehaertl\wkhtmlto\Pdf');
  }

}
