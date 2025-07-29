<?php

namespace Drupal\media_library_importer\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media_library_importer\MediaLibraryImporterService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\field\FieldConfigInterface;

/**
 * {@inheritdoc}
 */
class ConfigurationForm extends ConfigFormBase {

  /**
   * Constructs configuration form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   * @param \Drupal\media_library_importer\MediaLibraryImporterService $mediaLibraryImporter
   *   The Media Library Importer Service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    TypedConfigManagerInterface $typedConfigManager,
    protected EntityFieldManagerInterface $entityFieldManager,
    protected FileSystemInterface $fileSystem,
    protected MediaLibraryImporterService $mediaLibraryImporter,
  ) {
    parent::__construct($configFactory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('entity_field.manager'),
      $container->get('file_system'),
      $container->get('media_library_importer.service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_library_importer_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $files_default_path = $this->fileSystem->realpath($this->config('system.file')->get('default_scheme') . "://");
    $config = $this->config('media_library_importer.settings');
    $config_schema = $this->typedConfigManager->getDefinition('media_library_importer.settings') + ['mapping' => []]['mapping'];
    $config_schema = $config_schema['mapping'];

    $form['#tree'] = TRUE;

    $form['exclude_styles'] = [
      '#type' => 'checkbox',
      '#title' => $config_schema['exclude_styles']['label'],
      '#default_value' => $config->get('exclude_styles'),
      '#description' => $config_schema['exclude_styles']['description'],
    ];

    $form['import_files_to_media_location'] = [
      '#type' => 'checkbox',
      '#title' => $config_schema['import_files_to_media_location']['label'],
      '#default_value' => $config->get('import_files_to_media_location'),
      '#description' => $config_schema['import_files_to_media_location']['description'],
    ];

    $form['import_folder'] = [
      '#type' => 'textfield',
      '#title' => $config_schema['import_folder']['label'],
      '#default_value' => !empty($config->get('import_folder')) ? $config->get('import_folder') : $files_default_path,
      '#placeholder' => $this->mediaLibraryImporter->realPath,
      '#description' => $this->t("Path to the folder that stores the Media files to import.<br><strong>Note: </strong><u>Always prefix this with the following Public files server real path</u>: <strong>@real_path</strong>", [
        '@real_path' => $this->mediaLibraryImporter->realPath,
      ]),
    ];

    $form['media_types_import'] = [
      '#type' => 'checkboxes',
      '#title' => $config_schema['media_types_import']['label'],
      '#description' => $config_schema['media_types_import']['description'],
      '#options' => $this->mediaLibraryImporter->getMediaTypes(),
      '#multiple' => TRUE,
      '#default_value' => $config->get('media_types_import') ?? [],
      '#ajax' => [
        'callback' => '::mediaTypesFieldsAjaxCallback',
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'edit-output',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ],
    ];

    $media_types_import = $form_state->getValue('media_types_import') ?? ($config->get('media_types_import') ?? []);

    $form['media_types_fields'] = [
      '#type' => 'fieldset',
      '#title' => $config_schema['media_types_fields']['label'],
      '#description' => $config_schema['media_types_fields']['description'],
      '#prefix' => '<div id="edit-output">',
      '#suffix' => '</div>',
    ];

    if (!empty($media_types_import)) {
      foreach ($media_types_import as $k => $bundle) {
        if ($k === $bundle) {
          $label = $this->mediaLibraryImporter->getMediaTypes()[$k];
          $options = [];
          foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle) as $fieldDefinition) {
            if ($fieldDefinition instanceof FieldConfigInterface) {
              $options[$fieldDefinition->getName()] = $fieldDefinition->getName();
            }
          }
          $form['media_types_fields'][$k] = [
            '#type' => 'select',
            '#title' => $label,
            '#options' => $options,
            '#default_value' => $config->get('media_types_fields') && !empty($config->get('media_types_fields')[$k]) ? $config->get('media_types_fields')[$k] : '',
          ];
        }
      }
    }
    else {
      $form['media_types_fields']['#description'] = $this->t('None Media Types to import selected yet. Please select at least one!');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax Callback.
   *
   * @return array
   *   The media type file field machine name.
   */
  public function mediaTypesFieldsAjaxCallback(array &$form, FormStateInterface $form_state): array {
    // Return the prepared textfield.
    return $form['media_types_fields'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get all the form state values, in an array structure.
    $form_state_values = $form_state->getValues();

    $config = $this->config('media_library_importer.settings');

    $config->set('exclude_styles', $form_state_values['exclude_styles']);
    $config->set('import_files_to_media_location', $form_state_values['import_files_to_media_location']);
    $config->set('import_folder', $form_state_values['import_folder']);
    $config->set('media_types_import', $form_state_values['media_types_import']);
    if (isset($form_state_values['media_types_fields'])) {
      $config->set('media_types_fields', $form_state_values['media_types_fields']);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_library_importer.settings',
    ];
  }

}
