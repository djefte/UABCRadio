<?php

namespace Drupal\audio_player\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item in a audio_player.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "audio_player",
 *   title = @Translation("Audio Player"),
 *   help = @Translation("Display the results as a audio player."),
 *   theme = "views_audio_player",
 *   display_types = {"normal"}
 * )
 */
class ViewsAudioPlayer extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Does the style plugin support grouping of rows.
   *
   * @var bool
   */
  protected $usesGrouping = FALSE;

  /**
   * Does the style plugin for itself support to add fields to it's output.
   *
   * This option only makes sense on style plugins without row plugins, like
   * for example table.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['equalizer_effect'] = ['default' => 'waveform'];
    $options['skin'] = ['default' => 'skin-two'];
    $options['palette'] = ['default' => 'palette-1'];
    $options['audio_player_title'] = ['default' => ''];
    $options['audio_player_subtitle'] = ['default' => ''];
    $options['audio_player_video'] = ['default' => ''];
    $options['audio_player_thumbnail'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $player_equalizer_options = audio_player_equalizer_options();
    $skin_options = audio_player_audio_playlist_skins();
    $palette_options = audio_player_palette_options();
    $fields = ['' => $this->t('None')] + $this->getNonExcludedFields();

    // Select dropdown field for equalizer options (Initially hidden)
    $form['equalizer_effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Equalizer Options'),
      '#options' => $player_equalizer_options,
      '#default_value' => $this->options['equalizer_effect'],
    ];

    $form['skin'] = [
      '#title' => $this->t('Skin'),
      '#type' => 'select',
      '#default_value' => $this->options['skin'],
      '#options' => $skin_options,
      '#description' => $this->t('Choose the border shape for thumbnails. Common options include square or round.'),
    ];

    $form['palette'] = [
      '#title' => $this->t('Color Palette'),
      '#type' => 'select',
      '#default_value' => $this->options['palette'],
      '#options' => $palette_options,
      '#description' => $this->t('Choose the border shape for thumbnails. Common options include square or round.'),
    ];

    $form['audio_player_title'] = [
      '#type' => 'select',
      '#title' => $this->t('Video title'),
      '#options' => $fields,
      '#default_value' => $this->options['audio_player_title'],
      '#description' => $this->t('Select a field from the available fields in the view to be used as the video title for the playlist. This field will be mapped to the playlist title, helping to provide context and identification for the video.'),
    ];

    $form['audio_player_subtitle'] = [
      '#type' => 'select',
      '#title' => $this->t('Video sub title'),
      '#options' => $fields,
      '#default_value' => $this->options['audio_player_subtitle'],
      '#description' => $this->t('Select a field from the available fields in the view to be used as the video sub title for the playlist. This field will be mapped to the playlist sub title, helping to provide context and identification for the video.'),
    ];

    $form['audio_player_video'] = [
      '#type' => 'select',
      '#title' => $this->t('Video source'),
      '#options' => $fields,
      '#default_value' => $this->options['audio_player_video'],
      '#description' => $this->t('Select a field from the available fields in the view to be used as the video source. This field will be mapped to the video player source, determining the content displayed in the player.'),
    ];

    $form['audio_player_thumbnail'] = [
      '#type' => 'select',
      '#title' => $this->t('Video thumbnail'),
      '#options' => $fields,
      '#default_value' => $this->options['audio_player_thumbnail'],
      '#description' => $this->t('Select a field from the available fields in the view to be used as the thumbnail for the video. This field will be mapped to the video thumbnail image, providing a visual preview of the content.'),
      '#states' => [
        'visible' => [
          'select[name="style_options[skin]"]' => ['value' => 'skin-two'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    // Validate all audio_player type plugins values.
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    // Submit all audio_player type plugins values.
  }

  /**
   * Get an array of non-excluded fields for the current view.
   *
   * @return array
   *   An array of field names.
   */
  protected function getNonExcludedFields() {
    $fields = [];

    // Get the list of all fields.
    $all_fields = $this->view->display_handler->getHandlers('field');

    // Loop through all fields.
    foreach ($all_fields as $field_name => $field_handler) {
      // Check if the field is excluded.
      if (empty($field_handler->options['exclude'])) {
        // Include the field in the result.
        $fields[$field_name] = $field_handler->adminLabel();
      }
    }

    return $fields;
  }

}
