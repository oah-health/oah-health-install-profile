<?php

/**
 * @file
 * Contains \Drupal\pdf_api\Plugin\MpdfGenerator.
 */

namespace Drupal\pdf_api\Plugin\PdfGenerator;

use Drupal\pdf_api\Plugin\PdfGeneratorBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pdf_api\Annotation\PdfGenerator;
use Drupal\Core\Annotation\Translation;
use Drupal\pdf_api\Plugin\PdfGeneratorInterface;
use \mPDF;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A PDF generator plugin for the mPDF library.
 *
 * @PdfGenerator(
 *   id = "mpdf",
 *   module = "pdf_api",
 *   title = @Translation("mPDF"),
 *   description = @Translation("PDF generator using the mPDF generator.")
 * )
 */
class MpdfGenerator extends PdfGeneratorBase implements ContainerFactoryPluginInterface {

  /**
   * The global options for mPDF.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Instance of the mPdf class library.
   *
   * @var \mPdf
   */
  protected $generator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, mPDF $generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mpdf')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setter($pdf_content, $pdf_location, $save_pdf, $paper_orientation, $paper_size, $footer_content, $header_content, $path_to_binary = '') {
    $this->setPageSize($paper_size);
    $this->setPageOrientation($paper_orientation);
    $this->setHeader($header_content);
    $this->setFooter($footer_content);
    $filename = $pdf_location;
    $this->preGenerate();
    $stylesheet = '.node_view  { display: none; }';
    $this->generator->WriteHTML($stylesheet, 1);
    $this->generator->WriteHTML(utf8_encode($pdf_content), 0);
    if ($save_pdf) {
      if (empty($filename)) {
        $filename = str_replace("/", "_", \Drupal::service('path.current')->getPath());
        $filename = substr($filename, 1);
      }
      $this->stream($filename . '.pdf');
    }
    else {
      $this->send(utf8_encode($pdf_content));
    }
    $this->addPage($pdf_content);
  }

  /**
   * {@inheritdoc}
   */
  public function getObject() {
    return $this->generator;
  }

  /**
   * {@inheritdoc}
   */
  public function setHeader($text) {
    $this->generator->SetHeader($text);
  }

  /**
   * {@inheritdoc}
   */
  public function addPage($html) {
    $this->generator->addPage($html);
  }

  /**
   * {@inheritdoc}
   */
  public function setPageOrientation($orientation = PdfGeneratorInterface::PORTRAIT) {
    if ($orientation == PdfGeneratorInterface::PORTRAIT) {
      $orientation = 'P';
    }
    else {
      $orientation = 'L';
    }
    $this->setOptions(array('orientation' => $orientation));
  }

  /**
   * {@inheritdoc}
   */
  public function setPageSize($page_size) {
    if ($this->isValidPageSize($page_size)) {
      $this->setOptions(array('sheet-size' => $page_size));
    }
  }

  /**
   * Sets the password in PDF.
   *
   * @param string $password
   *   The password which will be used in PDF.
   */
  public function setPassword($password) {
    if (isset($password) && $password != NULL) {
      // Print and Copy is allowed.
      $this->generator->SetProtection(array('print', 'copy'), $password, $password);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setFooter($text) {
    // $this->generator->SetFooter($text);
  }

  /**
   * {@inheritdoc}
   */
  public function save($location) {
    $this->preGenerate();
    $this->generator->send($location);
  }

  /**
   * {@inheritdoc}
   */
  public function send() {
    $this->generator->Output("", "I");
  }

  /**
   * {@inheritdoc}
   */
  public function stream($filelocation) {
    $this->generator->Output($filelocation, 'F');
  }

  /**
   * Set global options.
   *
   * @param array $options
   *   The array of options to merge into the currently set options.
   */
  protected function setOptions(array $options) {
    $this->options += $options;
  }

  /**
   * Set the global options from the plugin into the mPDF generator class.
   */
  protected function preGenerate() {
    $this->generator->AddPageByArray($this->options);
  }

}
