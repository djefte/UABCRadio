<?php

/**
 * @file
 * API documentation for Media Library Importer.
 */

use Drupal\file\FileInterface;

/**
 * Add additional fields to media being added.
 *
 * This hooks allows modules to add additional fields to media being added.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file being imported.
 * @param string $file_url
 *   The url of the file being imported.
 * @param string $uri
 *   The uri of the file being imported.
 * @param array $extra_fields
 *   Extra fields for the media to be created. Here, any other fields belonging
 *   to media bundle can be added.
 */
function hook_alter_media_library_importer_media_extra_fields(FileInterface $file, string $file_url, string $uri, array &$extra_fields) {
}
