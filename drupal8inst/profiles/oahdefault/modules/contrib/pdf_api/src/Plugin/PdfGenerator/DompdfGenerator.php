<?php

/**
 * @file
 * Contains \Drupal\pdf_api\Plugin\DompdfGenerator.
 */

namespace Drupal\pdf_api\Plugin\PdfGenerator;

use Drupal\pdf_api\Plugin\PdfGeneratorBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pdf_api\Annotation\PdfGenerator;
use Drupal\Core\Annotation\Translation;
use \DOMPDF;
use Symfony\Component\DependencyInjection\ContainerInterface;

// Disable DOMPDF's internal autoloader if you are using Composer.
define('DOMPDF_ENABLE_AUTOLOAD', FALSE);
// Include the DOMPDF config file (required).
require __DIR__ . "../../../../vendor/dompdf/dompdf/dompdf_config.inc.php";

/**
 * A PDF generator plugin for the dompdf library.
 *
 * @PdfGenerator(
 *   id = "dompdf",
 *   module = "pdf_api",
 *   title = @Translation("DOMPDF"),
 *   description = @Translation("PDF generator using the DOMPDF generator.")
 * )
 */
class DompdfGenerator extends PdfGeneratorBase implements ContainerFactoryPluginInterface {

  /**
   * The global options for TCPDF.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Instance of the DOMPDF class library.
   *
   * @var \DOMPDF
   */
  protected $generator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, DOMPDF $generator) {
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
      $container->get('dompdf')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setter($pdf_content, $pdf_location, $save_pdf, $paper_orientation, $paper_size, $footer_content, $header_content, $path_to_binary = '') {
    $this->setPageOrientation($paper_orientation);
    $this->addPage($pdf_content);
    $this->setHeader($header_content);
    if ($save_pdf) {
      $filename = $pdf_location;
      if (empty($filename)) {
        // If no user's choice, PDF name should be made from its current path.
        $filename = str_replace('/', '_', \Drupal::service('path.current')->getPath());
        $filename = substr($filename, 1);
      }
      $this->stream($filename);
    }
    else {
      $this->send("");
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
    $canvas = $this->generator->get_canvas();
    $canvas->page_text(72, 18, "Header: {PAGE_COUNT}", "", 11, array(0, 0, 0));
  }

  /**
   * {@inheritdoc}
   */
  public function addPage($html) {
    $this->generator->load_html($html);
    $this->generator->render();
  }

  /**
   * {@inheritdoc}
   */
  public function setPageOrientation($orientation = PdfGeneratorInterface::PORTRAIT) {
    $this->generator->set_paper("", $orientation);
  }

  /**
   * {@inheritdoc}
   */
  public function setPageSize($page_size) {
    if ($this->isValidPageSize($page_size)) {
      $this->generator->set_paper($page_size);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setFooter($text) {
    // @todo see issue over here: https://github.com/dompdf/dompdf/issues/571
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
    $this->generator->stream("sample.pdf", array('Attachment' => 0));
  }

  /**
   * {@inheritdoc}
   */
  public function stream($filelocation) {
    $this->generator->Output($filelocation, "F");
  }

}
