<?php

namespace Drupal\entity_print\Plugin\EntityPrint\PrintEngine;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_print\Plugin\ExportTypeInterface;
use Drupal\entity_print\PrintEngineException;
use mikehaertl\wkhtmlto\Pdf;

/**
 * @PrintEngine(
 *   id = "phpwkhtmltopdf",
 *   label = @Translation("Php Wkhtmltopdf"),
 *   export_type = "pdf"
 * )
 *
 * To use this implementation you will need the DomPDF library, simply run:
 *
 * @code
 *     composer require "mikehaertl/phpwkhtmltopdf ~2.1"
 * @endcode
 */
class PhpWkhtmlToPdf extends PdfEngineBase implements AlignableHeaderFooterInterface {

  /**
   * @var \mikehaertl\wkhtmlto\Pdf
   */
  protected $pdf;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExportTypeInterface $export_type) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $export_type);
    $this->pdf = new Pdf([
      'binary' => $this->configuration['binary_location'],
      'orientation' => $this->configuration['orientation'],
      'username' => $this->configuration['username'],
      'password' => $this->configuration['password'],
    ]);
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
    return parent::defaultConfiguration() + [
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
    // If the filename received here is NULL, force open in the browser
    // otherwise attempt to have it downloaded.
    if (!$this->pdf->send($filename, !(bool) $filename)) {
      throw new PrintEngineException(sprintf('Failed to generate PDF: %s', $this->pdf->getError()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBlob() {
    $this->pdf->toString();
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
    return class_exists('mikehaertl\wkhtmlto\Pdf') && !drupal_valid_test_ua();
  }

  /**
   * {@inheritdoc}
   */
  protected function getPaperSizes() {
    return [
      'a0' => 'A0',
      'a1' => 'A1',
      'a2' => 'A2',
      'a3' => 'A3',
      'a4' => 'A4',
      'a5' => 'A5',
      'a6' => 'A6',
      'a7' => 'A7',
      'a8' => 'A8',
      'a9' => 'A9',
      'b0' => 'B0',
      'b1' => 'B1',
      'b10' => 'B10',
      'b2' => 'B2',
      'b3' => 'B3',
      'b4' => 'B4',
      'b5' => 'B5',
      'b6' => 'B6',
      'b7' => 'B7',
      'b8' => 'B8',
      'b9' => 'B9',
      'ce5' => 'CE5',
      'comm10e' => 'Comm10E',
      'dle' => 'DLE',
      'executive' => 'Executive',
      'folio' => 'Folio',
      'ledger' => 'Ledger',
      'legal' => 'Legal',
      'letter' => 'Letter',
      'tabloid' => 'Tabloid',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setHeaderText($text, $alignment) {
    $this->pdf->setOptions(['header-' . $alignment => $text]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setFooterText($text, $alignment) {
    $this->pdf->setOptions(['footer-' . $alignment => $text]);
    return $this;
  }

}
