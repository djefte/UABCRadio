<?php

namespace Drupal\media_library_importer\Commands;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\queue_ui\QueueUIBatchInterface;
use Drupal\media_library_importer\MediaLibraryImporterService;
use Drush\Commands\DrushCommands;

/**
 * Drush commands.
 *
 * Provide Drush commands for media_library_importer.
 */
class MediaLibraryImporterCommands extends DrushCommands {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * Constructs a new Drush commands class instance.
   *
   * @param \Drupal\media_library_importer\MediaLibraryImporterService $mediaLibraryImporter
   *   The Media Library Importer Service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The Queue factory service.
   * @param \Drupal\queue_ui\QueueUIBatchInterface $queueUiBatch
   *   Queue UI Batch service.
   */
  public function __construct(
    protected MediaLibraryImporterService $mediaLibraryImporter,
    protected QueueFactory $queueFactory,
    protected QueueUIBatchInterface $queueUiBatch,
  ) {
    parent::__construct();
  }

  /**
   * Import Media entities according to Media Library Importer settings.
   *
   * @command media-library:import
   * @aliases mli
   */
  public function import(): void {
    $this->mediaLibraryImporter->generateImportQueue();
    $queue = $this->queueFactory->get('media_library_importer');
    if ($queue->numberOfItems() == 0) {
      $this->messenger()->addWarning($this->t('No Media being processed by the Media Library Importer: either there no Media to import or they are all already imported.'));
    }
    else {
      $this->startBatch(
        (new BatchBuilder())
          ->addOperation([$this->queueUiBatch, 'step'], ['media_library_importer'])
      );
    }

  }

  /**
   * Helper function to start a batch process.
   *
   * @param \Drupal\Core\Batch\BatchBuilder $batch_definition
   *   The batch that needs to be processed.
   */
  protected function startBatch(BatchBuilder $batch_definition): void {
    // Set and configure the batch.
    batch_set($batch_definition->toArray());
    $batch = & batch_get();
    $batch['progressive'] = FALSE;

    // Process the batch.
    drush_backend_batch_process();
  }

}
