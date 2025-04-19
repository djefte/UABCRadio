<?php

namespace Drupal\visitors\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\visitors\VisitorsReportInterface;

/**
 * Referer type filter form.
 */
class Referer extends DateFilter {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visitors_referer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->setSessionRefererType();
    $form = parent::buildForm($form, $form_state);

    unset($form['submit']);

    $form['visitors_referer'] = [
      '#type'          => 'fieldset',
      '#title'         => $this->t('Referer type filter'),
      '#collapsible'   => FALSE,
      '#collapsed'     => FALSE,
      '#description'   => $this->t('Choose referer type'),
    ];

    $form['visitors_referer']['referer_type'] = [
      '#type' => 'select',
      '#title' => 'Referer type',
      '#default_value' => $_SESSION['referer_type'],
      '#options' => [
        $this->t('Internal pages'),
        $this->t('External pages'),
        $this->t('All pages'),
      ],
    ];

    $form['submit'] = [
      '#type'          => 'submit',
      '#value'         => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $_SESSION['referer_type'] = $form_state->getValues()['referer_type'];
  }

  /**
   * Set to session info default values for visitors referer type.
   */
  protected function setSessionRefererType() {
    if (!isset($_SESSION['referer_type'])) {
      $_SESSION['referer_type'] = VisitorsReportInterface::REFERER_TYPE_EXTERNAL_PAGES;
    }
  }

}
