<?php

namespace Drupal\audio_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'audio_player_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "audio_player_mfield_formatter",
 *   label = @Translation("Media Audio Player"),
 *   description = @Translation("Display media audio gallery"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class AudioPlayerMediaFieldFormatter extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {
  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param object $current_user
   *   The current user.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file URL generator.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    object $current_user,
    Renderer $renderer,
    FileUrlGeneratorInterface $fileUrlGenerator,
    ThemeManagerInterface $theme_manager,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currentUser = $current_user;
    $this->renderer = $renderer;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->themeManager = $theme_manager;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('file_url_generator'),
      $container->get('theme.manager'),
      $container->get('module_handler'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'skin' => 'skin-one',
      'audio_display' => 'single-audio',
      'equalizer_effect' => 'waveform',
      'palette' => 'default-palette',
    ] + parent::defaultSettings();
  }

  /**
   * Settings form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state array.
   *
   * @return mixed
   *   Returns mixed data.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $field_storage_definition = $this->fieldDefinition->getFieldStorageDefinition();
    $cardinality = $field_storage_definition->getCardinality();

    $palette_options = audio_player_palette_options();

    if ($cardinality != 1) {
      $skin_options = audio_player_audio_playlist_skins();
      $element['audio_display'] = [
        '#title' => $this->t('Audio Display'),
        '#type' => 'select',
        '#default_value' => $this->getSetting('audio_display'),
        '#options' => [
          '' => $this->t('Single'),
          'audio-playlist' => $this->t('Playlist'),
        ],
        '#description' => $this->t('Select whether the audios should be presented individually or grouped together in a playlist.'),
        '#access' => $this->currentUser->hasPermission('administer image styles'),
      ];

      $player_equalizer_options = audio_player_equalizer_options();

      // Select dropdown field for equalizer options (Initially hidden)
      $element['equalizer_effect'] = [
        '#type' => 'select',
        '#title' => $this->t('Equalizer Options'),
        '#options' => $player_equalizer_options,
        '#default_value' => $this->getSetting('equalizer_effect'),
        '#states' => [
          'visible' => [
            'select[name="fields[field_mp3_files][settings_edit_form][settings][audio_display]' => ['value' => 'audio-playlist'],
          ],
        ],
      ];
    }
    else {
      $skin_options = audio_player_single_audio_skins();
    }

    $element['skin'] = [
      '#title' => $this->t('Skin'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('skin'),
      '#options' => $skin_options,
      '#description' => $this->t('Choose the border shape for thumbnails. Common options include square or round.'),
      '#access' => $this->currentUser->hasPermission('administer image styles'),
    ];

    $element['palette'] = [
      '#title' => $this->t('Color Palette'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('palette'),
      '#options' => $palette_options,
      '#description' => $this->t('Choose the border shape for thumbnails. Common options include square or round.'),
      '#access' => $this->currentUser->hasPermission('administer image styles'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */

  /**
   * Settings summary.
   *
   * @return array
   *   Summary of settings.
   */
  public function settingsSummary() {
    $summary = [];

    $field_storage_definition = $this->fieldDefinition->getFieldStorageDefinition();
    $cardinality = $field_storage_definition->getCardinality();
    $audio_display = $this->getSetting('audio_display') ?? '';

    if ($cardinality != 1) {
      $summary[] = $this->t('Audio Display: @value', ['@value' => $audio_display]);
    }

    // Equalizer effect.
    $equalizer_effect = $this->getSetting('equalizer_effect') ?? '';
    if ($equalizer_effect && $audio_display) {
      $summary[] = $this->t('Equalizer effect: @value', ['@value' => $equalizer_effect]);
    }

    // Thumbnail Border Style.
    $skin = $this->getSetting('skin') ?? '';
    $summary[] = $this->t('Skin: @value', ['@value' => $skin]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $field_name = $items->getFieldDefinition()->getName();
    $elements = [];
    $media_entities = $this->getEntitiesToView($items, $langcode);
    $field_storage_definition = $this->fieldDefinition->getFieldStorageDefinition();
    $cardinality = $field_storage_definition->getCardinality();

    if (empty($media_entities)) {
      return $elements;
    }

    $audio_display = NULL;
    $equalizer = NULL;
    $palette = NULL;
    if ($cardinality != 1) {
      $audio_display = $this->getSetting('audio_display');
      $equalizer = $this->getSetting('equalizer_effect');
    }
    $skin = $this->getSetting('skin');
    $palette = $this->getSetting('palette');

    $audios = [];
    $cache_tags = [];

    $fileStorage = $this->entityTypeManager->getStorage('file');
    if ($media_entities) {
      foreach ($media_entities as $delta => $media) {
        $getEntityTypeId = $media->bundle();
        if ($getEntityTypeId == 'audio') {
          $audio_name = $media->label();

          $fid = $media->getSource()->getSourceFieldValue($media);
          if ($fid) {
            $file = $fileStorage->load($fid);
            if ($file) {
              $video_uri = $file->getFileUri();

              $video_uri_absolute = $this->fileUrlGenerator->generateAbsoluteString($video_uri);
              $video_uri_parsed = parse_url($video_uri_absolute);
              $video_uri_path = $video_uri_parsed['path'] ?? '';

              $cache_tags = Cache::mergeTags($cache_tags, $file->getCacheTags());

              if (!$audio_name) {
                $audio_name = basename($video_uri_path);
                $audio_name = urldecode($audio_name);
              }
              $audio_name = audio_player_generate_name($audio_name);

              $audios[$delta] = [
                'original_url' => $video_uri_path,
                'audio_title' => $audio_name,
                'audio_subtitle' => '',
              ];
            }
          }
        }
      }

      $data_attributes = [
        'equalizer' => $equalizer,
      ];

      $layout = $audio_display && $skin ? "{$audio_display}/{$skin}" : 'single-audio/' . $skin;

      // Get the current active theme's path.
      $theme = $this->themeManager->getActiveTheme();
      $theme_path = $theme->getPath() . '/templates/' . $layout . '.html.twig';

      $template_part = '';

      // Define the module template path.
      $module_path = $this->moduleHandler->getModule('audio_player')->getPath() . '/templates/' . $layout . '.html.twig';

      // Check for theme and module template files.
      if (file_exists($theme_path)) {
        $template_part = '@' . $theme->getName() . '/' . $layout . '.html.twig';
      }
      elseif (file_exists($module_path)) {
        $template_part = '@audio_player/' . $layout . '.html.twig';
      }

      $elements[] = [
        '#theme' => 'audio_player',
        '#audios' => $audios,
        '#data_attributes' => $data_attributes,
        '#cache' => [
          'tags' => $cache_tags,
        ],
        '#skin' => $skin,
        '#equalizer' => $equalizer,
        '#palette' => $palette,
        '#audio_display' => $audio_display,
        '#template_part' => $template_part,
        'field_name' => [$field_name],
        '#attached' => [
          'drupalSettings' => [],
        ],
      ];
    }
    return $elements;
  }

}
