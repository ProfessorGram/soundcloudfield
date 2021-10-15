<?php
namespace Drupal\soundcloudfield\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Display\EntityDisplayRepositoryInterface;

/**
 * Test case for custom SoundCloud field.
 *
 * @group soundcloudfield
 */
class SoundCloudWidgetValidationTest extends WebTestBase {
  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['entity_test', 'node', 'soundcloudfield'];


  /**
   * Random fieldName.
   */
  protected $fieldName;

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The instance used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->drupalCreateUser([
      'view test entity',
      'administer entity_test content',
      'link to any page',
    ]));
  }

  /**
   * Test for valid SoundCloud URL.
   */
  public function testSoundCloudUrlValid() {
    $field = $this->getField();

    // Display creation form.
    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$this->fieldName}[0][url]",
                             '',
                             'SoundCloud URL');
    $this->assertRaw('value=""');

    // Create a path alias.
    \Drupal::service('path.alias_storage')->save('/admin', '/a/path/alias');
    // Create a node to test the link widget.
    $node = $this->drupalCreateNode();

    $validEntries = ['https://soundcloud.com/dioxidmusic/soncha-gona-the-ground-20-tribute-to-the-100'];
    $this->assertValidEntries($validEntries);
  }

  /**
   * Test for invalid URLs or strings.
   */
  public function testSoundCloudUrlInvalid() {
    $field = $this->getField();

    // Display creation form.
    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$this->fieldName}[0][url]",
                             '',
                             'SoundCloud URL');
    $this->assertRaw('value=""');

    // Create a path alias.
    \Drupal::service('path.alias_storage')->save('/admin', '/a/path/alias');
    // Create a node to test the link widget.
    $node = $this->drupalCreateNode();

    $validationError = 'Please insert a valid SoundCloud url.';
    $invalidEntries = [
      'only a string'       => $validationError,
      'https://lmgtfy.com/' => $validationError,
    ];

    $this->assertInvalidEntries($invalidEntries);
  }

  /**
   * Asserts that valid URLs can be submitted.
   *
   * @param array $validEntries
   *   An array of valid URL entries.
   */
  protected function assertValidEntries(array $validEntries) {
    foreach ($validEntries as $key => $validEntry) {
      $edit = [
        "{$this->fieldName}[0][url]" => $validEntry,
      ];
      $this->drupalPostForm(NULL, $edit, t('Save'));
      preg_match('|entity_test/manage/(\d+)|', $this->url, $match);
      $id = $match[1];
      $this->assertText(t('entity_test @id has been created.', ['@id' => $id]));
      $this->assertRaw($validEntry);
    }
  }

  /**
   * Asserts that invalid URLs cannot be submitted.
   *
   * @param array $invalidEntries
   *   An array of invalid URL entries.
   */
  protected function assertInvalidEntries(array $invalidEntries) {
    foreach ($invalidEntries as $invalidValue => $errorMessage) {
      $edit = [
        "{$this->fieldName}[0][url]" => $invalidValue,
      ];
      $this->drupalPostForm('entity_test/add', $edit, t('Save'));
      $this->assertText(t($errorMessage, ['@link_path' => $invalidValue]));
    }
  }

  /**
   * Get the field for the test with default settings.
   */
  private function getField() {
    $this->fieldName = mb_strtolower($this->randomMachineName());
    // DB field.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name'  => $this->fieldName,
      'entity_type' => 'entity_test',
      'type'        => 'soundcloud',
      'cardinality' => 1,
    ]);
    $this->fieldStorage->save();
    // Field type.
    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle'        => 'entity_test',
      'settings'      => [
        'title' => DRUPAL_DISABLED,
      ],
    ]);
    $this->field->save();

    // Form display.
    EntityStorageInterface::load('entity_test.entity_test.default')
      ->setComponent($this->fieldName,
        [
          'type' => 'soundcloud_url',
        ])
      ->save();

    // Display.
    EntityDisplayRepositoryInterface::getViewDisplay('entity_test', 'entity_test', 'full')
      ->setComponent($this->fieldName,
        [
          'type' => 'soundcloud_default',
          'settings' => [
            'soundcloud_player_type' => 'classic',
            'soundcloud_player_width' => 100,
            'soundcloud_player_height' => 166,
            'soundcloud_player_height_sets' => 450,
            'soundcloud_player_visual_height' => 450,
            'soundcloud_player_autoplay' => '',
            'soundcloud_player_color' => 'ff7700',
            'soundcloud_player_hiderelated' => '',
            'soundcloud_player_showartwork' => '',
            'soundcloud_player_showcomments' => TRUE,
            'soundcloud_player_showplaycount' => '',
          ],
        ])
      ->save();
  }
}
