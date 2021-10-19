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
 *   label = @Translation("Javascript"),
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
      'soundcloud_player_type' => 'visual',
      'soundcloud_player_width' => SOUNDCLOUDFIELD_DEFAULT_WIDTH,
      'soundcloud_player_visual_height' => SOUNDCLOUDFIELD_DEFAULT_VISUAL_PLAYER_HEIGHT,
      'soundcloud_player_classic_height' => SOUNDCLOUDFIELD_DEFAULT_HTML5_PLAYER_HEIGHT,
      'soundcloud_player_height_sets' => SOUNDCLOUDFIELD_DEFAULT_HTML5_PLAYER_HEIGHT_SETS,
      'soundcloud_player_color' => 'ff7700',
      'soundcloud_player_autoplay' => TRUE,
      'soundcloud_player_showcomments' => TRUE,
      'soundcloud_player_hiderelated' => FALSE,
      'soundcloud_player_showteaser' => TRUE,
      'soundcloud_player_showartwork' => TRUE,
      'soundcloud_player_showuser' => TRUE,
      'soundcloud_player_showplaycount' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();

    $elements['soundcloud_player_type'] = [
      '#title' => $this->t('Player type'),
      '#description' => $this->t('Select either Visual or Classic player.'),
      '#type' => 'select',
      '#default_value' => $settings['soundcloud_visual_player'],
      '#options' => [
        'visual' => $this->t('Visual Player'),
        'classic' => $this->t('Classic'),
      ],
    ];

    $elements['soundcloud_player_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#size' => 4,
      '#default_value' => $settings['soundcloud_player_width'],
      '#description' => $this->t('Player width in percent. Default is @width.', ['@width' => SOUNDCLOUDFIELD_DEFAULT_WIDTH]),
    ];

    $elements['soundcloud_player_visual_height'] = [
      '#type' => 'select',
      '#title' => $this->t('Height of the player'),
      '#size' => 3,
      '#default_value' => $settings['soundcloud_player_visual_height'],
      '#options' => [
        300 => $this->t('300px'),
        450 => $this->t('450px'),
        600 => $this->t('600px'),
      ],
      '#states' => [
        'visible' => [
          ':input[name*="soundcloud_player_type"]' => ['value' => 'visual'],
        ],
      ],
    ];

    $elements['soundcloud_player_classic_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height of the player'),
      '#size' => 6,
      '#default_value' => $settings['soundcloud_player_classic_height'],
      '#states' => [
        'visible' => [
          ':input[name*="soundcloud_player_type"]' => ['value' => 'classic'],
        ],
      ],
    ];

    $elements['soundcloud_player_height_sets'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height for sets'),
      '#size' => 6,
      '#default_value' => $settings['soundcloud_player_height_sets'],
      '#states' => [
        'visible' => [
          ':input[name*="soundcloud_player_type"]' => ['value' => 'classic'],
        ],
      ],
    ];

    $elements['soundcloud_player_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Player color.'),
      '#default_value' => $settings['soundcloud_player_color'],
      '#description' => $this->t('Player color in hexadecimal format. Default is ff7700. Turn on the jQuery Colorpicker module if available.'),
    ];

    $elements['soundcloud_player_autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Play audio automatically when loaded (autoplay)'),
      '#default_value' => $settings['soundcloud_player_autoplay'],
    ];

    $elements['soundcloud_player_showcomments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show comments'),
      '#default_value' => $settings['soundcloud_player_showplaycount'],
    ];

    $elements['soundcloud_player_hiderelated'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide related tracks'),
      '#default_value' => $settings['soundcloud_player_hiderelated'],
    ];

    $elements['soundcloud_player_showteaser'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show SoundCloud overlays'),
      '#default_value' => $settings['soundcloud_player_showteaser'],
    ];

    $elements['soundcloud_player_showartwork'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show artwork'),
      '#default_value' => $settings['soundcloud_player_showartwork'],
    ];

    $elements['soundcloud_player_showuser'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show user information'),
      '#default_value' => $settings['soundcloud_player_showuser'],
    ];

    $elements['soundcloud_player_showplaycount'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show play count'),
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

    $js_settings = [
      'visual' => ($settings['soundcloud_player_type'] == 'visual') ? 'true' : 'false',
      'width' => (empty($settings['soundcloud_player_width'])) ? 100 : $settings['soundcloud_player_width'],
      'color' => ($settings['soundcloud_player_color']) ? $settings['soundcloud_player_color'] : 'ff7700',
      'autoplay' => ($settings['soundcloud_player_autoplay']) ? 'true' : 'false',
      'showcomments' => ($settings['soundcloud_player_showcomments']) ? 'true' : 'false',
      'hiderelated' => ($settings['soundcloud_player_hiderelated']) ? 'true' : 'false',
      'showteaser' => ($settings['soundcloud_player_showteaser']) ? 'true' : 'false',
      'showartwork' => ($settings['soundcloud_player_showartwork']) ? 'true' : 'false',
      'showuser' => ($settings['soundcloud_player_showuser']) ? 'true' : 'false',
      'showplaycount' => ($settings['soundcloud_player_showplaycount']) ? 'true' : 'false',
    ];

    $elements['#attached']['library'][] = 'soundcloudfield/soundcloud_sdk';
    $elements['#attached']['library'][] = 'soundcloudfield/soundcloudfield_init';

    foreach ($items as $delta => $item) {
      if ($settings['soundcloud_player_type'] == 'visual') {
        $height = $settings['soundcloud_player_visual_height'];
      }
      else {
        $parsed_url = parse_url($item->url);
        $split_path = explode('/', $parsed_url['path']);
        $height = (!isset($split_path[2]) || $split_path[2] == 'sets') ? $settings['soundcloud_player_height_sets'] : $settings['soundcloud_player_classic_height'];
      }

      $js_settings['height'] = $height;
      $js_settings['url'] = $item->url;
      $id = Html::cleanCssIdentifier($item->url);
      $js_settings['id'] = $id;

      $elements['#attached']['drupalSettings']['soundcloudfield'][$id] = $js_settings;

      $elements[$delta] = [
        '#theme' => 'soundcloudfield_js_embed',
        '#id' => $id,
      ];
    }

    return $elements;
  }
}
