Audio Player
============

The **Audio Player** module for Drupal 10/11 provides a flexible and
customizable solution for displaying audio players within your content.
It supports both single audio files and playlists, providing different
player modes depending on the number of files. With over 15+ skins, 10+
color palettes, and 15+ equalizer effects, the module allows for extensive
customization, making it ideal for users who want a tailored audio
experience on their site.

This module integrates with Drupal’s **File** and **Media** fields,
allowing site administrators to easily configure audio players through
the content type’s display settings. You can override skins and customize
styles using Twig templates and CSS within your theme for full creative control.

For a full description of the module, visit the [project page](https://www.drupal.org/project/audio_player).

Submit bug reports and feature suggestions, or track changes in the [issue queue](https://www.drupal.org/project/issues/audio_player).

* * *

Table of Contents
-----------------

*   [Requirements](#requirements)
*   [Installation](#installation)
*   [Configuration](#configuration)
*   [Customization](#customization)
*   [Troubleshooting](#troubleshooting)
*   [Maintainers](#maintainers)

* * *

Requirements
------------

This module requires **Drupal 10/11** and works with the native **File** and
**Media** fields in Drupal.

*   No contributed modules are required outside of Drupal core.

* * *

Installation
------------

Install as you would normally install a contributed Drupal module. For more
information, see [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

If using Composer:

    composer require drupal/audio_player

Enable the module using Drush:

    drush en audio_player

* * *

Configuration
-------------

1.  Go to: `Structure → Content types → {content_type} → Manage display`
2.  Choose the **Audio Player** format for the **File** field type or the
**Media Audio Player** for the **Media field type**.
3.  If the field cardinality is set to 1, the audio file will be displayed as a
single track.
4.  If the field cardinality is greater than 1, choose to display the files
either as a looped single track or as an audio playlist.
5.  Configure the appearance of the audio player by selecting from over 15
skins, 10+ color palettes, and 15+ equalizers.
6.  Optionally, override the skins' Twig templates
(e.g., copy `templates/audio-playlist/skin-one.html.twig` to your theme).
7.  Save your display configuration.

* * *

Customization
-------------

To fully customize the appearance and behavior of the audio player:

*   Override the default Twig templates for specific skins (e.g.,
`templates/audio-playlist/skin-one.html.twig`) by copying them into your
theme’s folder structure.
*   Customize the player’s appearance with custom CSS (e.g., overriding colors,
fonts, and layout styles).
*   Use the **audio_player** CSS class for targeting and styling the audio
player and player controls.
*   You can define custom color palettes for your theme or module settings to
personalize the audio player’s visual design.

For more advanced customizations, you can modify the audio player’s HTML
structure and CSS within the Twig templates.

* * *

Troubleshooting
---------------

*   Ensure that your **File** or **Media - Audio** fields are properly
configured and that the correct file type (audio) is selected.
*   Check if the correct player mode (single player or playlist) is selected
based on the number of files in the field.
*   Verify that the selected skin, color palette, and equalizer effects are
applied properly.
*   Ensure that any custom CSS styles are being loaded and applied to
the audio player.

* * *

Maintainers
-----------

*   Sujan Shrestha - [sujan-shrestha](https://www.drupal.org/u/sujan-shrestha)
