<?php

namespace Drupal\soundcloudfield\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'soundcloud' field type.
 *
 * @FieldType(
 *   id = "soundcloud",
 *   label = @Translation("SoundCloud"),
 *   module = "soundcloud",
 *   description = @Translation("Stores a SoundCloud URL string."),
 *   default_widget = "soundcloud_url",
 *   default_formatter = "soundcloud_default",
 * )
 */
class SoundCloudItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['url'] = DataDefinition::create('uri')
      ->setLabel(t('URL'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'url' => [
          'description' => 'The URL of the SoundCloud link.',
          'type' => 'varchar',
          'length' => 2048,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    $element = [];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('url')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $max_length = 512;
    $constraints[] = $constraint_manager->create('ComplexData', [
      'url' => [
        'Length' => [
          'max' => $max_length,
          'maxMessage' => $this->t('%name: the SoundCloud URL may not be longer than @max characters.', ['%name' => $this->getFieldDefinition()->getLabel(), '@max' => $max_length]),
        ],
      ],
    ]);

    return $constraints;
  }
}
