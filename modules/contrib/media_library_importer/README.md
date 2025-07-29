# Media Library Importer module

A module to import media files from public directory into media library.

* For a full description of the module, visit the project page:
   https://www.drupal.org/project/media_library_importer

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/media_library_importer

## Requirements
The module requires the Drupal core Media and Media Library modules, and the
additional [drupal/queue_ui](https://www.drupal.org/project/queue_ui]) contrib module,
to properly process queue with batch imports.

## Installation
Require the module with:

        composer require drupal/media_library_importer
or download and unpack the code base in the *modules* folder,
currently in the root of your Drupal installation (along with its additional
[drupal/queue_ui](https://www.drupal.org/project/queue_ui]) contrib module dependency).

Enable the module in `/admin/modules`,
or using the following drush command:

    drush en media-library

## Configuration
Visit `/admin/config/media/media-library-importer-configuration` to
configure the Media Library Import settings.

## Import and Generate Media

### Manual Media Imports from the Drupal backend
Visit `/admin/config/media/media-library-importer/import` for the
actual import operation.

### Media Imports with Drush Command
It is possible to import Media (according to Media Library Import settings)
using the following drush command:

    drush media-library:import

or its short alias:

    drush mli

### Media Imports with Cron
It is possible to implement a cron job and run automatic Media Library Imports
(incrementally uploading new files in the same source folders locations)
implementing in your own custom module the following hook_cron, based on the
MediaLibraryImporterService (media_library_importer.service):

      /**
      * Implements hook_cron().
        */
        function [my_module]_cron() {
        /** @var Drupal\media_library_importer\MediaLibraryImporterService $media_library_importer_service */
        $media_library_importer_service = \Drupal::service('media_library_importer.service');

        // Generate Media Library Importer Queue.
        $media_library_importer_service->generateImportQueue();

        // Process Media Library Importer Queue.
        $media_library_importer_service->generateImportQueue();
      }

### Note
You can run import as many times as you like. Once created, media entities
will not be duplicated.


## Maintainers

Current maintainers:
 * salihcenap - https://www.drupal.org/u/salihcenap
 * itamair - https://www.drupal.org/u/itamair
