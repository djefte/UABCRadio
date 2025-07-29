<?php

namespace Drupal\media_library_importer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Queue\QueueFactory;
use Drupal\queue_ui\QueueUIBatchInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\FieldConfigInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Provides a  MediaLibraryImporterService class.
 */
class MediaLibraryImporterService {

  use StringTranslationTrait;
  use MessengerTrait;
  use LoggerChannelTrait;
  use DependencySerializationTrait;

  /**
   * Media Library Importer Settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $mediaLibraryImporterSettings;

  /**
   * Real Path property..
   *
   * @var string
   */
  public $realPath;

  /**
   * Gets the logger for a specific channel.
   *
   * This method exists for backward-compatibility between FormBase and
   * LoggerChannelTrait. Use LoggerChannelTrait::getLogger() instead.
   *
   * @param string $channel
   *   The name of the channel. Can be any string, but the general practice is
   *   to use the name of the subsystem calling this.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger for the given channel.
   */
  protected function logger($channel) {
    return $this->getLogger($channel);
  }

  /**
   * MediaLibraryImporterService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager interface.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager interface.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   The current user.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The Queue factory service.
   * @param \Drupal\queue_ui\QueueUIBatchInterface $queueUiBatch
   *   The Queue batch service.
   */
  public function __construct(
    protected ConfigFactoryInterface $config,
    protected FileSystemInterface $fileSystem,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFieldManagerInterface $entityFieldManager,
    protected LanguageManagerInterface $languageManager,
    protected AccountProxy $currentUser,
    protected QueueFactory $queueFactory,
    protected QueueUIBatchInterface $queueUiBatch
  ) {
    $this->mediaLibraryImporterSettings = $this->config->get('media_library_importer.settings');
    $this->realPath = $this->fileSystem->realpath($this->config->get('system.file')->get('default_scheme') . "://");
  }

  /**
   * Load File, from uri and media type.
   *
   * @param string $uri
   *   The uri string.
   * @param array $media_type
   *   The media type string.
   *
   * @return \Drupal\file\FileInterface
   *   The File entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function loadFile(string $uri, array $media_type) {
    $newUri = NULL;
    $importToMediaDirectory = !empty($this->mediaLibraryImporterSettings->get('import_files_to_media_location'));
    if ($importToMediaDirectory) {
      $mediaDirectory = $media_type['dir'];
      $parts = explode('/', $uri);
      $filename = end($parts);
      $newUri = $mediaDirectory . '/' . $filename;
      $files = $this->entityTypeManager->getStorage('file')->loadByProperties(['uri' => $newUri]);
    }
    else {
      $files = $this->entityTypeManager->getStorage('file')->loadByProperties(['uri' => $uri]);
    }
    if (empty($files)) {
      $file = $this->createFileEntity($uri, $newUri);
    }
    else {
      $file = reset($files);
    }
    return $file;
  }

  /**
   * Check if a media file already exists.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   * @param string $bundle
   *   The media bundle.
   *
   * @return bool
   *   If a result is found or not.
   */
  protected function mediaEntityExists(FileInterface $file, $bundle) {
    $result = [];
    $media_types_fields = $this->mediaLibraryImporterSettings->get('media_types_fields');
    $bundle_fields = $this->entityFieldManager->getFieldDefinitions('media', $bundle);

    if (array_key_exists($bundle, $media_types_fields)) {
      $field_name = $media_types_fields[$bundle] ?? NULL;
      if (!empty($field_name) && (!empty($bundle_fields[$field_name]) && $bundle_fields[$field_name] instanceof FieldConfigInterface)) {
        try {
          $mediaQuery = $this->entityTypeManager->getStorage('media')
            ->getQuery();
          $mediaQuery->condition('bundle', $bundle);
          $mediaQuery->condition('name', $file->getFilename());
          $mediaQuery->accessCheck(FALSE);
          $result = $mediaQuery->execute();
          if ($result && empty($result)) {
            $this->logger('media_library_importer')
              ->warning("$bundle: " . $file->getFilename() . " has not been imported yet.");
          }
        }
        catch (\Exception $e) {
          $this->logger('media_library_importer')
            ->error("Exception while checking '$bundle' media type :" . $e->getMessage());
        }
      }
    }
    else {
      $this->logger('media_library_importer')
        ->warning("The '$bundle' media type is not among the selected ones for importing.");
    }
    return !empty($result);
  }

  /**
   * Create File entity.
   *
   * @param string $uri
   *   The source uri.
   * @param string $newUri
   *   The new uri.
   *
   * @return \Drupal\file\FileInterface|null
   *   The returned entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createFileEntity(string $uri, ?string $newUri = NULL) {
    $file = NULL;

    $importToMediaDirectory = is_null($this->mediaLibraryImporterSettings->get('import_files_to_media_location')) ? TRUE : $this->mediaLibraryImporterSettings->get('import_files_to_media_location');
    if (!$importToMediaDirectory) {
      if (file_exists($uri)) {
        $filename = mb_substr($uri, mb_strrpos($uri, '/') + 1);
        $file = File::create([
          'uid' => $this->currentUser->id(),
          'filename' => $filename,
          'uri' => $uri,
          'status' => 1,
        ]);

        $file->save();
      }
      else {
        $this->messenger()->addMessage(
          $this->t('An error occurred while attempting to import unmanaged file "@file_name." It will not be imported.', ['@file_name' => $uri]), MessengerInterface::TYPE_WARNING);
      }
    }
    else {
      if (file_exists($uri) && !file_exists($newUri)) {
        $filename = basename($newUri);
        $directory = str_replace($filename, "", $newUri);
        $this->fileSystem->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
        $this->fileSystem->copy($uri, $newUri, FileExists::Replace);
        $file = File::create([
          'uid' => $this->currentUser->id(),
          'filename' => $filename,
          'uri' => $newUri,
          'status' => 1,
        ]);
        $file->save();
      }
      elseif (file_exists($uri) && file_exists($newUri)) {
        $filename = basename($newUri);
        $file = File::create([
          'uid' => $this->currentUser->id(),
          'filename' => $filename,
          'uri' => $newUri,
          'status' => 1,
        ]);
        $file->save();
      }
      else {
        $this->messenger()->addMessage(
          $this->t("File at '@file_path' does not exist!", ['@file_path' => $uri]), MessengerInterface::TYPE_ERROR);
      }
    }
    return($file);
  }

  /**
   * Create Media Entity.
   *
   * @param string $bundle
   *   The Media bundle to create.
   * @param \Drupal\file\FileInterface $file
   *   The File to attach to the Media.
   * @param array $extra_fields
   *   Eventual extra fields.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createMediaEntity(string $bundle, FileInterface $file, array $extra_fields) {
    $media_types_fields = $this->mediaLibraryImporterSettings->get('media_types_fields');
    if (array_key_exists($bundle, $media_types_fields)) {
      $field_name = $media_types_fields[$bundle];
      $bundle_fields = $this->entityFieldManager->getFieldDefinitions('media', $bundle);
      if (!empty($field_name) && (!empty($bundle_fields[$field_name]) && $bundle_fields[$field_name] instanceof FieldConfigInterface)) {
        $media_data = [
          'bundle' => $bundle,
          'uid' => $this->currentUser->id(),
          'langcode' => $this->languageManager->getDefaultLanguage()->getId(),
          'name' => $file->getFilename(),
          'status' => 1,
          "$field_name" => $file,
        ];

        if (!empty($extra_fields)) {
          $media_data = array_merge($media_data, $extra_fields);
        }

        $media = Media::create($media_data);
        // (perhaps) It looks the following is not strictly needed as the media
        // is already being created with file name.
        // $media->setName($file->getFilename())->setPublished();
        $media->save();
        $this->logger('media_library_importer')->info($this->t("Created '@media_label' media of type '@bundle'.", [
          '@media_label' => $media->label(),
          '@bundle' => $bundle,
        ]));
      }
      else {
        $this->logger('media_library_importer')
          ->warning("Cannot find '$field_name' for '$bundle' media type. Not creating new Media for that." . $file->getFilename());
      }
    }
  }

  /**
   * Generates selected Media Types info.
   *
   * An indexed array of selected media types provoided with allowed extensions
   * and storage directory.
   *
   * @return array
   *   The Media Types info list.
   */
  protected function selectedMediaTypesInfo(): array {
    $media_types = $this->mediaLibraryImporterSettings->get('media_types_import');
    $type = [];
    if (is_array($media_types)) {
      foreach ($media_types as $key => $media_type) {
        if ($media_type != 0) {
          $type[$key]['ext'] = $this->getMediaExtensions($key);
          $type[$key]['dir'] = $this->getMediaDirectory($key);
        }
      }
    }
    return $type;
  }

  /**
   * Returns a list of all the media types currently defined.
   *
   * @return array
   *   An array of media types currently defined.
   */
  public function getMediaTypes(): array {
    $media_entity_storage = $this->entityTypeManager->getStorage('media_type')->loadMultiple();
    $types = [];
    foreach ($media_entity_storage as $media_type) {
      $types[$media_type->id()] = $media_type->label() . " ({$media_type->id()})";
    }
    return $types;
  }

  /**
   * Generate a single comma separated list of unique allowed media extensions.
   */
  protected function getAllMediaExtensions(): string {
    $extensions = [];
    foreach ($this->selectedMediaTypesInfo() as $media_type) {
      // Generate a merged list of unique.
      $extensions = array_unique(array_merge(explode(" ", $media_type['ext']), $extensions));
    }
    if (empty($extensions)) {
      return "";
    }
    else {
      return implode(",", $extensions);
    }
  }

  /**
   * Get Media Bundle allowed Extensions.
   *
   * Takes a media type bundle id and returns the allowed extensions of
   * that bundle.
   *
   * @param string $bundle
   *   The bundle id.
   *
   * @return string
   *   The extensions list.
   */
  protected function getMediaExtensions($bundle): string {
    /** @var \Drupal\media\MediaTypeInterface $type */
    $type = $this->entityTypeManager->getStorage('media_type')->load($bundle);
    $extensions = $type->getSource()->getSourceFieldDefinition($type)->getSetting('file_extensions');
    if (is_null($extensions)) {
      $extensions = "";
    }
    return $extensions;
  }

  /**
   * Get the Media Bundle storage directory.
   *
   * Takes a media type bundle id and returns the storing file directory of
   * that bundle.
   *
   * @param string $bundle
   *   The bundle id.
   *
   * @return string
   *   The storage path.
   */
  protected function getMediaDirectory($bundle): string {
    try {
      /** @var \Drupal\media\MediaTypeInterface $type */
      $type = $this->entityTypeManager->getStorage('media_type')->load($bundle);
      $token_service = \Drupal::token();
      $scheme = $type->getSource()
        ->getSourceFieldDefinition($type)
        ->getFieldStorageDefinition()
        ->getSetting('uri_scheme');
      $dir = $token_service->replace($type->getSource()
        ->getSourceFieldDefinition($type)
        ->getSetting('file_directory'));
      return $scheme . "://" . $dir;
    }
    catch (\Exception $e) {
      return '';
    }
  }

  /**
   * Generate Media Library Importer Queue.
   *
   * @param array $media_folders
   *   The media folders structure.
   */
  public function generateImportQueue(array $media_folders = []) {

    $queue = $this->queueFactory->get('media_library_importer');
    $queue->createQueue();
    // Eventually empty existing 'media_library_importer' queue (still not
    // processed) to avoid creation of duplicates.
    $queue->deleteQueue();

    $media_extensions = $this->getAllMediaExtensions();

    if (empty($media_folders)) {
      $import_folder = $this->mediaLibraryImporterSettings->get('import_folder');
      $folders = $this->getMediaFolders($import_folder);
      $media_folders = array_keys($this->getMediaFoldersCheckboxOptions($folders));
    }

    foreach ($media_folders as $media_folder) {

      if ($media_folder) {

        $media_files = glob("$media_folder/*.{" . $media_extensions . "}", GLOB_BRACE);

        foreach ($media_files as $file_url) {
          $uri = str_replace($this->realPath, 'public:/', $file_url);
          $ext = pathinfo($file_url, PATHINFO_EXTENSION);
          $media_types = $this->selectedMediaTypesInfo();
          $file = NULL;
          foreach ($media_types as $key => $media_type) {
            if (in_array($ext, explode(" ", $media_type['ext']), TRUE)) {
              try {
                $bundle = $key;
                $file = $this->loadFile($uri, $media_type);
                if ($file && !is_null($bundle)) {
                  if ($this->mediaEntityExists($file, $bundle)) {
                    $this->logger('media_library_importer')->notice('Media entity already exists. Not creating a new queue item for "@file_name"', ['@file_name' => $file->getFilename()]);
                  }
                  else {
                    $extra_fields = [];

                    $module_handler = \Drupal::moduleHandler();
                    $module_handler->invokeAll('alter_media_library_importer_media_extra_fields', [
                      $file,
                      $file_url,
                      $uri,
                      &$extra_fields,
                    ]);

                    $queue->createItem([
                      'bundle' => $bundle,
                      'file' => $file,
                      'extra_fields' => $extra_fields,
                    ]);
                  }
                }
              }
              catch (\Exception $e) {
                $this->logger('media_library_importer')->error("Exception while importing and generating files :" . $e->getMessage());
              }
            }
          }
        }
      }
    }
  }

  /**
   * Process Media Library Importer Queue.
   */
  public function processImportQueue() {
    $this->queueUiBatch->batch(['media_library_importer']);
  }

  /**
   * Get Media Folders.
   *
   * @param string $files_path
   *   The root path.
   *
   * @return array|array[]
   *   The Media folders nested array structure.
   */
  public function getMediaFolders($files_path = NULL) {
    $media_extensions = $this->getAllMediaExtensions();

    $stylesShouldBeExcluded = $this->mediaLibraryImporterSettings->get('exclude_styles') ?? TRUE;

    if ($files_path) {
      // Eventually remove slash character at the end.
      if (mb_substr($files_path, -1) == '/') {
        $files_path = mb_substr($files_path, 0, -1);
      }

      $name = mb_substr($files_path, mb_strrpos($files_path, '/') + 1);
      $media_files = glob("$files_path/*.{" . $media_extensions . "}", GLOB_BRACE);

      $tree = [
        $name =>
          [
            'name' => $name,
            'path' => $files_path,
            'uri' => str_replace($this->realPath, 'public:/', $files_path),
            'media_count' => count($media_files),
          ],
      ];

      $directories = glob($files_path . '/*', GLOB_ONLYDIR);
      foreach ($directories as $folder) {
        if ($name == 'styles' && $stylesShouldBeExcluded) {
          continue;
        }
        $tree[$name]['subdir'][] = $this->getMediaFolders($folder);
      }
      return $tree;
    }
    else {
      return $this->getMediaFolders($this->realPath);
    }
  }

  /**
   * Generate Media Folders Checkbox Options.
   *
   * @param array $folders
   *   The folders array.
   * @param int $level
   *   The level.
   *
   * @return array
   *   The options array.
   */
  public function getMediaFoldersCheckboxOptions(array $folders, int $level = 0) {

    $options = [];

    $indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    $level++;

    foreach ($folders as $folder) {
      $media_count = $folder['media_count'];
      $hasSubdir = array_key_exists('subdir', $folder);

      $k = $folder['path'];
      if ($media_count > 0) {
        $v = $indentation . $folder['name'] . ' (' . $media_count . ')';
      }
      else {
        $v = $indentation . $folder['name'];
      }

      if ($media_count > 0 || $hasSubdir) {
        $options[$k] = $v;
      }

      if ($hasSubdir) {
        $sub_folders = $folder['subdir'];
        foreach ($sub_folders as $sub_folder) {
          $options = array_merge($options, $this->getMediaFoldersCheckboxOptions($sub_folder, $level));
        }
      }
    }
    return $options;
  }

}
