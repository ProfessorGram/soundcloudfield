CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration


INTRODUCTION
------------

The SoundCloud Field module adds a new SoundCloud field type to Drupal. This
allows you to enter the URL for an audio file or playlist (set) from SoundCloud
and have the embedded player appear on the page.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/soundcloudfield

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/soundcloudfield


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

Use "Manage fields" to add a new field to a content type or other entity. The
SoundCloud field type will be listed under the General section.

Once you have added the field, go to "Manage display" to select the view modes
it should appear on. Under "Format" select the type of output. The choices are

 * Default (PHP-Based): Uses a server-side request to load the embed code
   from SoundCloud.
 * Javascript: Uses a client-side request to load the embed code from
   SoundCloud.
 * Link to SoundCloud URI: Generates a basic HTML link to SoundCloud.
 * Raw output of SoundCloud URI: Returns only the field value in plain text.

