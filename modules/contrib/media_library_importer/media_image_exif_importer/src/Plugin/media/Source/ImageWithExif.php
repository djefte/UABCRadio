<?php

namespace Drupal\media_image_exif_importer\Plugin\media\Source;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\Image;

/**
 * Image entity media source, with EXIF-handling capabilities.
 *
 * @see \Drupal\media\Plugin\media\Source\Image
 */
class ImageWithExif extends Image {

  /**
   * The exif data.
   *
   * @var array
   */
  protected array $exif;

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes(): array {
    $attributes = parent::getMetadataAttributes();

    $attributes += [
      static::METADATA_ATTRIBUTE_WIDTH => $this->t('Width'),
      static::METADATA_ATTRIBUTE_HEIGHT => $this->t('Height'),
    ];

    if (!empty($this->configuration['gather_exif'])) {
      $attributes += [
        'model' => $this->t('Camera model'),
        'created' => $this->t('Image creation datetime'),
        'iso' => $this->t('Iso'),
        'exposure' => $this->t('Exposure time'),
        'aperture' => $this->t('Aperture value'),
        'focal_length' => $this->t('Focal length'),
      ];
    }

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['gather_exif'] = [
      '#type' => 'select',
      '#title' => $this->t('Whether to gather exif data.'),
      '#description' => $this->t('Gather exif data using exif_read_data().'),
      '#default_value' => empty($this->configuration['gather_exif']) || !function_exists('exif_read_data') ? 0 : $this->configuration['gather_exif'],
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#disabled' => (function_exists('exif_read_data')) ? FALSE : TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $name) {
    // Get the file and image data.
    /** @var \Drupal\file\FileInterface $file */
    $file = $media->get($this->configuration['source_field'])->entity;
    // If the source field is not required, it may be empty.
    if (!$file) {
      return parent::getMetadata($media, $name);
    }

    $uri = $file->getFileUri();
    $image = $this->imageFactory->get($uri);
    switch ($name) {
      case static::METADATA_ATTRIBUTE_WIDTH:
        return $image->getWidth() ?: NULL;

      case static::METADATA_ATTRIBUTE_HEIGHT:
        return $image->getHeight() ?: NULL;

      case 'thumbnail_uri':
        return $uri;
    }

    if (!empty($this->configuration['gather_exif']) && function_exists('exif_read_data')) {
      switch ($name) {
        case 'model':
          return $this->getExifField($uri, 'Model');

        case 'created':
          $date = new DrupalDateTime($this->getExifField($uri, 'DateTimeOriginal'));
          return $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

        case 'iso':
          return $this->getExifField($uri, 'ISOSpeedRatings');

        case 'exposure':
          $value = $this->getExifField($uri, 'ExposureTime');
          if (strpos($value, '/') !== FALSE) {
            $value = $this->normaliseFraction($value);
          }
          return $value;

        case 'aperture':
          $value = $this->getExifField($uri, 'FNumber');
          if (strpos($value, '/') !== FALSE) {
            $value = $this->normaliseFraction($value);
          }
          return $value;

        case 'focal_length':
          $value = $this->getExifField($uri, 'FocalLength');
          if (strpos($value, '/') !== FALSE) {
            $value = $this->normaliseFraction($value);
          }
          return $value;
      }
    }

    return parent::getMetadata($media, $name);
  }

  /**
   * Get exif field value.
   *
   * @param string $uri
   *   The uri for the file that we are getting the Exif.
   * @param string $field
   *   The name of the exif field.
   *
   * @return string|bool
   *   The value for the requested field or FALSE if is not set.
   */
  protected function getExifField($uri, $field) {
    if (empty($this->exif)) {
      $this->exif = $this->getExif($uri);
    }
    return !empty($this->exif[$field]) ? $this->exif[$field] : FALSE;
  }

  /**
   * Read EXIF.
   *
   * @param string $uri
   *   The uri for the file that we are getting the Exif.
   *
   * @return array|bool
   *   An associative array where the array indexes are the header names and
   *   the array values are the values associated with those headers or FALSE
   *   if the data can't be read.
   */
  protected function getExif($uri) {
    $file = \Drupal::service('file_system')->realpath($uri);
    return exif_read_data($file, 'EXIF');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $parentConfiguration = parent::defaultConfiguration();
    $parentConfiguration['gather_exif'] = 0;
    return $parentConfiguration;
  }

  /**
   * Normalise fractions.
   */
  private function normaliseFraction($fraction) {
    $parts = explode('/', $fraction);
    $top = $parts[0];
    $bottom = $parts[1];

    if ($top > $bottom) {
      // Value > 1.
      if (($top % $bottom) == 0) {
        $value = ($top / $bottom);
      }
      else {
        $value = round(($top / $bottom), 2);
      }
    }
    else {
      if ($top == $bottom) {
        // Value = 1.
        $value = '1';
      }
      else {
        // Value < 1.
        if ($top == 1) {
          $value = '1/' . $bottom;
        }
        else {
          if ($top != 0) {
            $value = '1/' . round(($bottom / $top), 0);
          }
          else {
            $value = '0';
          }
        }
      }
    }
    return $value;
  }

}
