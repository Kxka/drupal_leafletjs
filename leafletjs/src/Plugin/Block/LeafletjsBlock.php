<?php

namespace Drupal\leafletjs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a 'LeafletJS' Block.
 *
 * @Block(
 *   id = "leafletjs_block",
 *   admin_label = @Translation("LeafletJS"),
 *   category = @Translation("Custom"),
 * )
 */
class LeafletjsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'map_height' => '600px',
      'custom_location_file' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['map_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Height'),
      '#description' => $this->t('Height of the map (e.g., 600px)'),
      '#default_value' => $this->configuration['map_height'],
    ];

    $form['custom_location_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Location Data File'),
      '#description' => $this->t('Upload a .txt file containing location data'),
      '#upload_location' => 'public://leafletjs/',
      '#upload_validators' => [
        'file_validate_extensions' => ['txt'],
      ],
      '#default_value' => $this->configuration['custom_location_file'] ? [$this->configuration['custom_location_file']] : NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['map_height'] = $form_state->getValue('map_height');

    // Handle file upload 
    $custom_file = $form_state->getValue('custom_location_file');
    if (!empty($custom_file[0])) {
      if (!empty($this->configuration['custom_location_file']) && $this->configuration['custom_location_file'] != $custom_file[0]) {
        $old_file = File::load($this->configuration['custom_location_file']);
        if ($old_file) {
          \Drupal::service('file.usage')->delete($old_file, 'leafletjs', 'block', $this->getPluginId());
          $old_file->delete();
        }
      }

      $file = File::load($custom_file[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();

        $this->configuration['custom_location_file'] = $custom_file[0];

        \Drupal::service('file.usage')->add($file, 'leafletjs', 'block', $this->getPluginId());
      }
    }
    else {
      if (!empty($this->configuration['custom_location_file'])) {
        $old_file = File::load($this->configuration['custom_location_file']);
        if ($old_file) {
          \Drupal::service('file.usage')->delete($old_file, 'leafletjs', 'block', $this->getPluginId());
          $old_file->delete();
          \Drupal::messenger()->addStatus($this->t('Location data file has been deleted.'));
        }
      }
      $this->configuration['custom_location_file'] = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'leafletjs',
      '#map_height' => $this->configuration['map_height'],
      '#cache' => [
        'max-age' => 3600,
      ],
    ];

    $build['#attached']['library'][] = 'leafletjs/map_base';

    if (!empty($this->configuration['custom_location_file'])) {
      $file = File::load($this->configuration['custom_location_file']);
      if ($file) {
        $file_url = $file->createFileUrl();

        $build['#attached']['html_head'][] = [
          [
            '#tag' => 'script',
            '#attributes' => [
              'src' => $file_url,
              'type' => 'text/javascript',
            ],
          ],
          'leafletjs_custom_location',
        ];
      }
    }

    $build['#attached']['library'][] = 'leafletjs/map_init';

    return $build;
  }

}
