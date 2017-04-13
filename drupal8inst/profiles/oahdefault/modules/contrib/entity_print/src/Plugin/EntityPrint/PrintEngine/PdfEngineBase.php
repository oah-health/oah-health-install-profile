<?php

namespace Drupal\entity_print\Plugin\EntityPrint\PrintEngine;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_print\Plugin\PrintEngineBase;

/**
 * Base class for all PDF print engines.
 */
abstract class PdfEngineBase extends PrintEngineBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['default_paper_size'] = [
      '#title' => $this->t('Paper Size'),
      '#type' => 'select',
      '#options' => $this->getPaperSizes(),
      '#default_value' => $this->configuration['default_paper_size'],
      '#description' => $this->t('The page size to print the PDF to.'),
    ];
    $form['orientation'] = [
      '#type' => 'select',
      '#title' => $this->t('Paper Orientation'),
      '#options' => [
        static::PORTRAIT => $this->t('Portrait'),
        static::LANDSCAPE => $this->t('Landscape'),
      ],
      '#description' => $this->t('The paper orientation one of Landscape or Portrait'),
      '#default_value' => $this->configuration['orientation'],
    ];
    $form['credentials'] = [
      '#type' => 'details',
      '#title' => $this->t('HTTP Authentication'),
      '#open' => !empty($this->configuration['username']) || !empty($this->configuration['password']),
    ];
    $form['credentials']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('If your website is behind HTTP Authentication you can set the username'),
      '#default_value' => $this->configuration['username'],
    ];
    $form['credentials']['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#description' => $this->t('If your website is behind HTTP Authentication you can set the password'),
      '#default_value' => $this->configuration['password'],
    ];

    return $form;
  }

  /**
   * Gets the paper sizes supported.
   *
   * @return array
   *   An array of paper sizes keyed by their machine name.
   */
  protected abstract function getPaperSizes();

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'orientation' => 'portrait',
      'default_paper_size' => 'letter',
      'username' => '',
      'password' => '',
    ];
  }

}
