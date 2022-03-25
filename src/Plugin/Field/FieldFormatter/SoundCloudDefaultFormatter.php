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
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'soundcloud_default' formatter.
 *
 * @FieldFormatter(
 *   id = "soundcloud_default",
 *   module = "soundcloudfield",
 *   label = @Translation("Default (PHP-based)"),
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
      'soundcloud_player_showteaser' => TRUE,
      'soundcloud_player_showartwork' => '',
      'soundcloud_player_showcomments' => TRUE,
      'soundcloud_player_showplaycount' => '',
      'soundcloud_player_showuser' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();

    $elements['soundcloud_player_type'] = [
      '#title' => $this->t('HTML5 player type'),
      '#description' => $this->t('Select which HTML5 player to use.'),
      '#type' => 'select',
      '#default_value' => $settings['soundcloud_player_type'],
      '#options' => [
        'classic' => $this->t('Classic'),
        'visual' => $this->t('Visual Player'),
      ],
    ];

    $elements['soundcloud_player_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#size' => 4,
      '#default_value' => $settings['soundcloud_player_width'],
      '#description' => $this->t('Player width in percent. Default is @width.', ['@width' => SOUNDCLOUDFIELD_DEFAULT_WIDTH]),
    ];

    $elements['soundcloud_player_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#size' => 4,
      '#default_value' => $settings['soundcloud_player_height'],
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
      '#default_value' => $settings['soundcloud_player_height_sets'],
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

    $url_params = [
      'visual' => ($settings['soundcloud_player_type'] == 'visual') ? 'true' : 'false',
      'color' => ($settings['soundcloud_player_color']) ? $settings['soundcloud_player_color'] : 'ff7700',
      'autoplay' => ($settings['soundcloud_player_autoplay']) ? 'true' : 'false',
      'showcomments' => ($settings['soundcloud_player_showcomments']) ? 'true' : 'false',
      'hiderelated' => ($settings['soundcloud_player_hiderelated']) ? 'true' : 'false',
      'showteaser' => ($settings['soundcloud_player_showteaser']) ? 'true' : 'false',
      'showartwork' => ($settings['soundcloud_player_showartwork']) ? 'true' : 'false',
      'showuser' => ($settings['soundcloud_player_showuser']) ? 'true' : 'false',
      'showplaycount' => ($settings['soundcloud_player_showplaycount']) ? 'true' : 'false',
    ];

    $width = (empty($settings['soundcloud_player_width'])) ? 100 : $settings['soundcloud_player_width'];

    $oembed_endpoint = 'https://soundcloud.com/oembed';

    foreach ($items as $delta => $item) {
      $output = '';
      $encoded_url = urlencode($item->url);
      $url_params['url'] = $item->url;

      if ($settings['soundcloud_player_type'] == 'visual') {
        $height = $settings['soundcloud_player_visual_height'];
      }
      else {
        $parsed_url = parse_url($item->url);
        $split_path = explode('/', $parsed_url['path']);
        $height = (!isset($split_path[2]) || $split_path[2] == 'sets') ? $settings['soundcloud_player_height_sets'] : $settings['soundcloud_player_height'];
      }

      // Create the URL.
      $oembed_url = $oembed_endpoint . '?iframe=true&format=json&url=' . ($encoded_url);

      // Fetching data.
      if ($soundcloud_embed_data = $this->fetchSoundCloudData($oembed_url)) {
        // Load in the oEmbed JSON.
        $oembed = Json::decode($soundcloud_embed_data);
        $markup = $oembed['html'];

        // Set title for accessibility, if not already set.
        if (!preg_match('/(title=)"([^"]+)"/', $markup)) {
          $markup = preg_replace('/(<iframe\b[^><]*)>/i', '$1 title="' . Html::escape($oembed['title']) . '">', $markup);
       }

        // Replace player default player width and height.
        $markup = preg_replace('/(width=)"([^"]+)"/', 'width="' . $width . '%"', $markup);
        $markup = preg_replace('/(height=)"([^"]+)"/', 'height="' . $height . '"', $markup);

        // Parse src attribute and replace query params with our own.
        preg_match('/src="([^"]+)"/', $markup, $match);
        $iframe_src_parts = explode('?', $match[1]);
        $markup = str_replace($match[1], Url::fromUri($iframe_src_parts[0], ['query' => $url_params])->toString(), $markup);
        $output = $markup;
      }
      else {
        $soundcloud_url = Url::fromUri($item->url)->toString();
        $output = $this->t('The SoundCloud content at <a href=":url">:url</a> is not available, or it is set to private.', [':url' => $soundcloud_url]);
      }

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
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
