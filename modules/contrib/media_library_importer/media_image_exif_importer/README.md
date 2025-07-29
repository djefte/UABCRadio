# Media Image EXIF importer

This module is a Drupal 11 enhanced / upgraded fork of its original:
[Media Entity Image EXIF](https://www.drupal.org/project/media_entity_image_exif)
which seems abandoned by its maintainers in its compatibility with Drupal 10.

Use this module if:
 - you need to be able to extract / import EXIF metadata into Media Images.

Media in core currently does not include the EXIF-related functionality that
Media Entity Image had, so this small module is needed to fill in that gap.

This module, when enabled, will override the "Image" source plugin from core
and all image media types will then have the EXIF extraction handling available.

## Requirements

This module requires the [exif_read_data()](http://php.net/exif_read_data)
function in PHP.

It also requires the Media system provided by core.
