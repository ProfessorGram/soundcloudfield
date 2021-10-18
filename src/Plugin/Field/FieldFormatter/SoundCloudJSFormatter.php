<?php

namespace Drupal\soundcloudfield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'soundcloud_js' formatter.
 *
 * @FieldFormatter(
 *   id = "soundcloud_js",
 *   module = "soundcloudfield",
 *   label = @Translation("Visual Player loaded via Javascript"),
 *   field_types = {
 *     "soundcloud"
 *   }
 * )
 */
class SoundCloudJSFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'soundcloud_player_width' => SOUNDCLOUDFIELD_DEFAULT_WIDTH,
      'soundcloud_player_height' => SOUNDCLOUDFIELD_DEFAULT_VISUAL_PLAYER_HEIGHT,
      'soundcloud_player_height_sets' => SOUNDCLOUDFIELD_DEFAULT_VISUAL_PLAYER_HEIGHT,
      'soundcloud_player_autoplay' => '',
      'soundcloud_player_color' => 'ff7700',
      'soundcloud_player_hiderelated' => '',
      'soundcloud_player_showartwork' => '',
      'soundcloud_player_showcomments' => TRUE,
      'soundcloud_player_showplaycount' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();

    $elements['soundcloud_player_height'] = [
      '#type' => 'select',
      '#title' => $this->t('Height of the visual player'),
      '#size' => 4,
      '#default_value' => $settings['soundcloud_player_height'],
      '#options' => [
        300 => $this->t('300px'),
        400 => $this->t('400px'),
        450 => $this->t('450px'),
        500 => $this->t('500px'),
        600 => $this->t('600px'),
      ],
    ];

    $elements['soundcloud_player_height_sets'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height for sets'),
      '#size' => 4,
      '#default_value' => $settings['soundcloud_player_height_sets'],
    ];

    $elements['soundcloud_player_autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Play audio automatically when loaded (autoplay).'),
      '#default_value' => $settings['soundcloud_player_autoplay'],
    ];

    $elements['soundcloud_player_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Player color.'),
      '#default_value' => $settings['soundcloud_player_color'],
      '#description' => $this->t('Player color in hexadecimal format. Default is ff7700. Turn on the jQuery Colorpicker module if available.'),
    ];

    $elements['soundcloud_player_hiderelated'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide related tracks.'),
      '#default_value' => $settings['soundcloud_player_hiderelated'],
    ];

    $elements['soundcloud_player_showartwork'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show artwork.'),
      '#default_value' => $settings['soundcloud_player_showartwork'],
    ];

    $elements['soundcloud_player_showplaycount'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show play count.'),
      '#default_value' => $settings['soundcloud_player_showplaycount'],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the SoundCloud player.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();

    $autoplay = $settings['soundcloud_player_autoplay'] ? 'true' : 'false';
    $showplaycount = $settings['soundcloud_player_showplaycount'] ? 'true' : 'false';
    $showartwork = $settings['soundcloud_player_showartwork'] ? 'true' : 'false';
    $color = $settings['soundcloud_player_color'] ? $settings['soundcloud_player_color'] : 'ff7700';

    $elements['#attached']['library'][] = 'soundcloudfield/soundcloud_sdk';
    $elements['#attached']['library'][] = 'soundcloudfield/soundcloudfield_init';

    foreach ($items as $delta => $item) {
      $parsed_url = parse_url($item->url);
      $split_path = explode('/', $parsed_url['path']);
      $height = (!isset($split_path[2]) || $split_path[2] == 'sets') ? $settings['soundcloud_player_height_sets'] : $settings['soundcloud_player_height'];

      $id = Html::cleanCssIdentifier($item->url);

      $elements['#attached']['drupalSettings']['soundcloudfield'][] = [
        'id' => $id,
        'url' => $item->url,
        'autoplay' => $autoplay,
        'maxheight' => $height,
        'showartwork' => $showartwork,
        'showplaycount' => $showplaycount,
        'color' => $color,
      ];

      $elements[$delta] = [
        '#theme' => 'soundcloudfield_js_embed',
        '#id' => $id,
      ];
    }

    return $elements;
  }
}
