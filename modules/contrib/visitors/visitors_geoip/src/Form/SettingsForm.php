<?php

namespace Drupal\visitors_geoip\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Visitors Settings Form.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'visitors_geoip.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visitors_geoip_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->configFactory->get('visitors_geoip.settings');
    $form = parent::buildForm($form, $form_state);

    $form['geoip_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GeoIP Database path'),
      '#description' => $this->t('Enter the path to the MindMax database(s). Just the directory, relative to the Drupal root.'),
      '#default_value' => $settings->get('geoip_path'),
    ];
    $form['license'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MaxMind License Key'),
      '#description' => $this->t('Enter your MaxMind license key. If you do not have one, you can get one <a href="https://www.maxmind.com/en/geolite2/signup">here</a>.'),
      '#default_value' => $settings->get('license'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $path = $form_state->getValue('geoip_path');
    if (!is_dir($path)) {
      $form_state->setErrorByName('geoip_path', $this->t('Directory does not exists, or is not accessible.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $values = $form_state->getValues();
    $config
      ->set('geoip_path', $values['geoip_path'])
      ->set('license', $values['license'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
