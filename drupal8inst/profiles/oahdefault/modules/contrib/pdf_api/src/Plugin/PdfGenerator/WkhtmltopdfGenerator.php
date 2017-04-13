<?php

/**
 * @file
 * Contains \Drupal\pdf_api\Plugin\WkhtmltopdfGenerator.
 */

namespace Drupal\pdf_api\Plugin\PdfGenerator;

use Drupal\pdf_api\Plugin\PdfGeneratorBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pdf_api\Annotation\PdfGenerator;
use Drupal\Core\Annotation\Translation;
use mikehaertl\wkhtmlto\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A PDF generator plugin for the WKHTMLTOPDF library.
 *
 * @PdfGenerator(
 *   id = "wkhtmltopdf",
 *   module = "pdf_api",
 *   title = @Translation("WKHTMLTOPDF"),
 *   description = @Translation("PDF generator using the WKHTMLTOPDF binary.")
 * )
 */
class WkhtmltopdfGenerator extends PdfGeneratorBase implements ContainerFactoryPluginInterface {

  /**
   * The global options for WKHTMLTOPDF.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Instance of the WKHtmlToPdf class library.
   *
   * @var \WkHtmlToPdf
   */
  protected $generator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, Pdf $generator) {
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
      $container->get('wkhtmltopdf')
    );
  }

  /**
   * Set the path of binary file.
   *
   * @param string $path_to_binary
   *   Path to binary file.
   */
  public function configBinary($path_to_binary) {
    $this->setOptions(array('binary' => $path_to_binary));
  }

  /**
   * {@inheritdoc}
   */
  public function setter($pdf_content, $pdf_location, $save_pdf, $paper_orientation, $paper_size, $footer_content, $header_content, $path_to_binary = '') {
    $this->configBinary($path_to_binary);
    $this->addPage($pdf_content);
    $this->setPageSize($paper_size);
    $this->setPageOrientation($paper_orientation);
    // Uncomment below line when need to add header and footer to page,
    // also make changes in the templates too.
    // $this->setHeader($header_content);
    // $this->setFooter($footer_content);
    if ($save_pdf) {
      $filename = $pdf_location;
      if (empty($filename)) {
        $filename = str_replace("/", "_", \Drupal::service('path.current')->getPath());
        $filename = substr($filename, 1);
      }
      $this->stream($filename . '.pdf');
    }
    else {
      $this->send();
    }
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
    $this->setOptions(array('header-right' => $text));
  }

  /**
   * {@inheritdoc}
   */
  public function setPageOrientation($orientation = PdfGeneratorInterface::PORTRAIT) {
    $this->setOptions(array('orientation' => $orientation));
  }

  /**
   * {@inheritdoc}
   */
  public function setPageSize($page_size) {
    if ($this->isValidPageSize($page_size)) {
      $this->setOptions(array('page-size' => $page_size));
    }
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
  public function setFooter($text) {
    $this->setOptions(array('footer-center' => $text));
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
    $this->preGenerate();
    $this->generator->send();
  }

  /**
   * {@inheritdoc}
   */
  public function stream($filelocation) {
    $this->preGenerate();
    $this->generator->saveAs($filelocation);
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
   * Set the global options from plugin into the WKHTMLTOPDF generator class.
   */
  protected function preGenerate() {
    $this->generator->setOptions($this->options);
  }

}
