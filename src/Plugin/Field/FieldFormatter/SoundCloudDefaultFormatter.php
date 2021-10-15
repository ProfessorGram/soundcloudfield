<?php

namespace Drupal\soundcloudfield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'soundcloud_default' formatter.
 *
 * @FieldFormatter(
 *   id = "soundcloud_default",
 *   module = "soundcloudfield",
 *   label = @Translation("Default (HTML5 player)"),
 *   field_types = {
 *     "soundcloud"
 *   }
 * )
 */
class SoundCloudDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a SoundCloudDefaultFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ClientInterface $http_client) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @see \Drupal\Core\Field\FormatterPluginManager::createInstance().
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'soundcloud_player_type' => 'classic',
      'soundcloud_player_width' => SOUNDCLOUDFIELD_DEFAULT_WIDTH,
      'soundcloud_player_height' => SOUNDCLOUDFIELD_DEFAULT_HTML5_PLAYER_HEIGHT,
      'soundcloud_player_height_sets' => SOUNDCLOUDFIELD_DEFAULT_HTML5_PLAYER_HEIGHT_SETS,
      'soundcloud_player_visual_height' => SOUNDCLOUDFIELD_DEFAULT_VISUAL_PLAYER_HEIGHT,
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

    $elements['soundcloud_player_type'] = [
      '#title' => $this->t('HTML5 player type'),
      '#description' => $this->t('Select which HTML5 player to use.'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('soundcloud_player_type'),
      '#options' => [
        'classic' => t('Classic'),
        'visual' => t('Visual Player (new)'),
      ],
    ];

    $elements['soundcloud_player_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#size' => 4,
      '#default_value' => $this->getSetting('soundcloud_player_width'),
      '#description' => $this->t('Player width in percent. Default is @width.', ['@width' => SOUNDCLOUDFIELD_DEFAULT_WIDTH]),
    ];

    $elements['soundcloud_player_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#size' => 4,
      '#default_value' => $this->getSetting('soundcloud_player_height'),
      '#states' => [
        'visible' => [
          ':input[name*="soundcloud_player_type"]' => ['value' => 'classic'],
        ],
      ],
    ];

    $elements['soundcloud_player_height_sets'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height for sets'),
      '#size' => 4,
      '#default_value' => $this->getSetting('soundcloud_player_height_sets'),
      '#states' => [
        'visible' => [
          ':input[name*="soundcloud_player_type"]' => ['value' => 'classic'],
        ],
      ],
    ];

    $elements['soundcloud_player_visual_height'] = [
      '#type' => 'select',
      '#title' => $this->t('Height of the visual player'),
      '#size' => 4,
      '#default_value' => $this->getSetting('soundcloud_player_visual_height'),
      '#options' => [
        300 => t('300px'),
        450 => t('450px'),
        600 => t('600px'),
      ],
      '#states' => [
        'visible' => [
          ':input[name*="soundcloud_player_type"]' => ['value' => 'visual'],
        ],
      ],
    ];

    $elements['soundcloud_player_autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Play audio automatically when loaded (autoplay).'),
      '#default_value' => $this->getSetting('soundcloud_player_autoplay'),
    ];

    $elements['soundcloud_player_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Player color.'),
      '#default_value' => $this->getSetting('soundcloud_player_color'),
      '#description' => $this->t('Player color in hexadecimal format. Default is ff7700. Turn on the jQuery Colorpicker module if available.'),
    ];

    $elements['soundcloud_player_hiderelated'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide related tracks.'),
      '#default_value' => $this->getSetting('soundcloud_player_hiderelated'),
    ];

    $elements['soundcloud_player_showartwork'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show artwork.'),
      '#default_value' => $this->getSetting('soundcloud_player_showartwork'),
    ];

    $elements['soundcloud_player_showcomments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show comments.'),
      '#default_value' => $this->getSetting('soundcloud_player_showcomments'),
    ];

    $elements['soundcloud_player_showplaycount'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show play count.'),
      '#default_value' => $this->getSetting('soundcloud_player_showplaycount'),
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

    // Get the "common" settings.
    $width = $this->getSetting('soundcloud_player_width');
    $autoplay = $this->getSetting('soundcloud_player_autoplay') ? 'true' : 'false';
    $showcomments = $this->getSetting('soundcloud_player_showcomments') ? 'true' : 'false';
    $showplaycount = $this->getSetting('soundcloud_player_showplaycount') ? 'true' : 'false';
    $showartwork = $this->getSetting('soundcloud_player_showartwork') ? 'true' : 'false';
    $color = $this->getSetting('soundcloud_player_color') ? $this->getSetting('soundcloud_player_color') : 'ff7700';

    $oembed_endpoint = 'https://soundcloud.com/oembed';

    // Get 'HTML5 player'-specific settings.
    $html5_player_height = (empty($settings['html5_player']['html5_player_height']) ? SOUNDCLOUDFIELD_DEFAULT_HTML5_PLAYER_HEIGHT : $settings['html5_player']['html5_player_height']);
    $html5_player_height_sets = (empty($settings['html5_player']['html5_player_height_sets']) ? SOUNDCLOUDFIELD_DEFAULT_HTML5_PLAYER_HEIGHT_SETS : $settings['html5_player']['html5_player_height_sets']);
    $visual_player = ($this->getSetting('soundcloud_player_type') == 'visual') ? 'true' : 'false';

    foreach ($items as $delta => $item) {
      $output = '';
      $encoded_url = urlencode($item->url);

      // Set the proper height for this item.
      // - classic player: track default is 166px, set default is 450px.
      // - visual player: player height it's the same for tracks and sets.
      if ($visual_player == 'true') {
        $iframe_height = $settings['soundcloud_player_visual_height'];
      }
      else {
        $parsed_url = parse_url($item->url);
        $splitted_url = explode("/", $parsed_url['path']);
        // An artist page or a set or a track?
        $iframe_height = (!isset($splitted_url[2]) || $splitted_url[2] == 'sets') ? $html5_player_height_sets : $html5_player_height;
      }

      // Create the URL.
      $oembed_url = $oembed_endpoint . '?iframe=true&format=json&url=' . ($encoded_url);

      // Fetching data.
      if ($soundcloud_embed_data = $this->fetchSoundCloudData($oembed_url)) {
        // Load in the oEmbed JSON.
        $oembed = Json::decode($soundcloud_embed_data);

        // Replace player default settings with our settings,
        // set player width and height first.
        $final_iframe = preg_replace('/(width=)"([^"]+)"/', 'width="' . $width . '%"', $oembed['html']);
        $final_iframe = preg_replace('/(height=)"([^"]+)"/', 'height="' . $iframe_height . '"', $oembed['html']);
        // Set autoplay.
        if (preg_match('/auto_play=(true|false)/', $final_iframe)) {
          $final_iframe = preg_replace('/auto_play=(true|false)/', 'auto_play=' . $autoplay, $final_iframe);
        }
        else {
          $final_iframe = preg_replace('/">/', '&auto_play=' . $autoplay . '">', $final_iframe);
        }
        // Show comments?
        if (preg_match('/show_comments=(true|false)/', $final_iframe)) {
          $final_iframe = preg_replace('/show_comments=(true|false)/', 'show_comments=' . $showcomments, $final_iframe);
        }
        else {
          $final_iframe = preg_replace('/">/', '&show_comments=' . $showcomments . '">', $final_iframe);
        }
        // Show playcount?
        if (preg_match('/show_playcount=(true|false)/', $final_iframe)) {
          $final_iframe = preg_replace('/show_playcount=(true|false)/', 'show_playcount=' . $showplaycount, $final_iframe);
        }
        else {
          $final_iframe = preg_replace('/">/', '&show_playcount=' . $showplaycount . '">', $final_iframe);
        }
        // Show artwork?
        if (preg_match('/show_artwork=(true|false)/', $final_iframe)) {
          $final_iframe = preg_replace('/show_artwork=(true|false)/', 'show_artwork=' . $showartwork, $final_iframe);
        }
        else {
          $final_iframe = preg_replace('/">/', '&show_artwork=' . $showartwork . '">', $final_iframe);
        }
        // Set player color.
        if (preg_match('/color=([a-zA-Z0-9]{6})/', $final_iframe)) {
          $final_iframe = preg_replace('/color=([a-zA-Z0-9]{6})/', 'color=' . $color, $final_iframe);
        }
        else {
          $final_iframe = preg_replace('/">/', '&color=' . $color . '">', $final_iframe);
        }
        // Set HTML5 player type based on formatter: classic/visual player.
        if (preg_match('/visual=(true|false)/', $final_iframe)) {
          $final_iframe = preg_replace('/visual=(true|false)/', 'visual=' . $visual_player, $final_iframe);
        }
        else {
          $final_iframe = preg_replace('/">/', '&visual=' . $visual_player . '">', $final_iframe);
        }
        // Final output. Use '$oembed->html' for original embed code.
        $output = html_entity_decode($final_iframe);
      }
      else {
        $output = $this->t('The SoundCloud content at <a href=":url">:url</a> is not available, or it is set to private.', [':url' => $item->url]);
      }

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      $item_attributes = $item->_attributes;
      unset($item->_attributes);

      // Render each element as markup.
      $elements[$delta] = [
        '#markup' => $output,
        '#allowed_tags' => ['iframe'],
      ];
    }

    return $elements;
  }

  /**
   * Get data from url using httpClient.
   */
  public function fetchSoundCloudData($url) {
    try {
      $response = $this->httpClient->get($url);
      $data = (string) $response->getBody();
      if (empty($data)) {
        return FALSE;
      }
    }
    catch (RequestException $e) {
      return FALSE;
    }

    return $data;
  }
}
