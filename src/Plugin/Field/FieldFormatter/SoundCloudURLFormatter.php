<?php

namespace Drupal\soundcloudfield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'soundcloud_url' formatter.
 *
 * @FieldFormatter(
 *   id = "soundcloud_url",
 *   module = "soundcloudfield",
 *   label = @Translation("Raw output of SoundCloud URI"),
 *   field_types = {
 *     "soundcloud"
 *   }
 * )
 */
class SoundCloudURLFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if (!$item->isEmpty()) {
        $elements[$delta]['#markup'] = Url::fromUri($item->url)->toString();
      }
    }

    return $elements;
  }

}
