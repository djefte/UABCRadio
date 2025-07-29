<?php

namespace Drupal\media_library_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_library_importer\MediaLibraryImporterService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class ImportForm extends FormBase {

  /**
   * Media Library Importer Settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $mediaLibraryImporterSettings;

  /**
   * Constructs a ImportForm form.
   *
   * @param \Drupal\media_library_importer\MediaLibraryImporterService $mediaLibraryImporter
   *   The Media Library Importer Service.
   */
  public function __construct(
    protected MediaLibraryImporterService $mediaLibraryImporter
  ) {
    $this->mediaLibraryImporterSettings = $this->config('media_library_importer.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('media_library_importer.service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_library_importer_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $import_folder = $this->mediaLibraryImporterSettings->get('import_folder');

    $folders = $this->mediaLibraryImporter->getMediaFolders($import_folder);
    $options = $this->mediaLibraryImporter->getMediaFoldersCheckboxOptions($folders);

    $form['media_folders'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media folders'),
      '#options' => $options,
      '#default_value' => array_keys($options),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->mediaLibraryImporter->generateImportQueue($form_state->getValue('media_folders'));
    // Immediately process Media Library Importer Queue.
    $this->mediaLibraryImporter->processImportQueue();
  }

}
