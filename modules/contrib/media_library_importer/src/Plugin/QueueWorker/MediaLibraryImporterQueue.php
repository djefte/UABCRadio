<?php

declare(strict_types = 1);

namespace Drupal\media_library_importer\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\media_library_importer\MediaLibraryImporterService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Consumes the 'media_library_importer' queue.
 *
 * @QueueWorker(
 *   id = "media_library_importer",
 *   title = @Translation("Media Library Importer Queue"),
 *   cron = {"time" = 10},
 * )
 */
class MediaLibraryImporterQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new worker instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\media_library_importer\MediaLibraryImporterService $mediaLibraryImporter
   *   The Media Library Importer Service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    protected MediaLibraryImporterService $mediaLibraryImporter,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('media_library_importer.service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    $this->mediaLibraryImporter->createMediaEntity($data['bundle'], $data['file'], $data['extra_fields']);
  }

}
