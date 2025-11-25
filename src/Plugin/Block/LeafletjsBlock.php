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
   *
   * Default block configuration values.
   */
  public function defaultConfiguration() {
    return [
      'map_height' => '600px',           
      'custom_location_file' => NULL,   // Processed location data file ID
      'override_autofit' => FALSE,      
      'default_lat' => '0',              
      'default_lon' => '0',              
      'default_zoom' => '18',            
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

    // Accepts CSV with format: Title, Coordinates ("lat, lon"), Link, Thumbnail
    $form['custom_location_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Location Data File'),
      '#description' => $this->t('Upload a .csv file with columns: Title, Coordinates ("lat, lon"), Link to node, Link to Thumbnail'),
      '#upload_location' => 'public://leafletjs/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#default_value' => $this->configuration['custom_location_file'] ? [$this->configuration['custom_location_file']] : NULL,
    ];

    // Override auto-fit behavior checkbox
    $form['override_autofit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override auto-fit zoom and center'),
      '#description' => $this->t('When checked, use custom settings instead of auto-fitting to markers'),
      '#default_value' => $this->configuration['override_autofit'],
    ];

    $form['default_lat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Latitude'),
      '#description' => $this->t('Default latitude on load (e.g., 51.505)'),
      '#default_value' => $this->configuration['default_lat'],
      '#states' => [
        'visible' => [
          ':input[name="settings[override_autofit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['default_lon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Longitude'),
      '#description' => $this->t('Default longitude on load (e.g., -0.09)'),
      '#default_value' => $this->configuration['default_lon'],
      '#states' => [
        'visible' => [
          ':input[name="settings[override_autofit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['default_zoom'] = [
      '#type' => 'number',
      '#title' => $this->t('Default Zoom Level'),
      '#description' => $this->t('Default zoom level on load (1-18)'),
      '#default_value' => $this->configuration['default_zoom'],
      '#min' => 1,
      '#max' => 18,
      '#states' => [
        'visible' => [
          ':input[name="settings[override_autofit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save basic configuration values
    $this->configuration['map_height'] = $form_state->getValue('map_height');
    $this->configuration['override_autofit'] = $form_state->getValue('override_autofit');
    $this->configuration['default_lat'] = $form_state->getValue('default_lat');
    $this->configuration['default_lon'] = $form_state->getValue('default_lon');
    $this->configuration['default_zoom'] = $form_state->getValue('default_zoom');

    // Handle file upload and processing
    $custom_file = $form_state->getValue('custom_location_file');
    if (!empty($custom_file[0])) {
      $file = File::load($custom_file[0]);
      if ($file) {
        $file_uri = $file->getFileUri();
        $file_contents = file_get_contents($file_uri);

        if ($file_contents) {
          // Check if file is already processed, prevents re-processing an already converted .txt file
          if (substr(trim($file_contents), 0, 3) === 'var') {
            $this->configuration['custom_location_file'] = $custom_file[0];
          }
          else {
            // Parse CSV file and convert to JavaScript array format
            $csv_data = [];
            $lines = str_getcsv($file_contents, "\n");

            if (empty($lines)) {
              \Drupal::messenger()->addError($this->t('Empty CSV file.'));
              return;
            }

            // Sotre header 
            $header = str_getcsv(array_shift($lines));

            // Parse each CSV line
            // Expected CSV format: Title, Coordinates ("lat, lon"), Link, Thumbnail
            // Output format: [[lat, lon, title, thumbnail, link], ...]
            $address_points = [];
            foreach ($lines as $line) {
              // Skip empty lines
              if (empty(trim($line))) {
                continue;
              }

              $row = str_getcsv($line);

              // Validate that title and coordinates exist
              if (count($row) >= 2 && !empty(trim($row[0])) && !empty(trim($row[1]))) {
                // Split coordinates string "lat, lon" 
                $coords = array_map('trim', explode(',', $row[1]));

                // Verify both latitude and longitude 
                if (count($coords) >= 2 && !empty($coords[0]) && !empty($coords[1])) {
                  $address_points[] = [
                    (float) $coords[0], // Latitude
                    (float) $coords[1], // Longitude
                    $row[0] ?? '',      // Title
                    $row[3] ?? '',      // Thumbnail URL 
                    $row[2] ?? '',      // Link URL 
                  ];
                }
              }
            }

            if (empty($address_points)) {
              \Drupal::messenger()->addError($this->t('No valid locations found in CSV file.'));
              return;
            }

            // Format: var addressPoints = [[lat,lon,title,thumb,link],...]
            $file_content = 'var addressPoints = ' . json_encode($address_points);

            // Save processed data to .txt file in public://leafletjs/ directory
            $directory = 'public://leafletjs';
            \Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

            // filename
            $filename = 'addressPoints_' . time() . '.txt';
            $file_uri = $directory . '/' . $filename;

            file_put_contents($file_uri, $file_content);

            // Create Drupal managed file entity for txt file 
            $txt_file = File::create([
              'uri' => $file_uri,
              'status' => 1,
            ]);
            $txt_file->setPermanent();
            $txt_file->save();

            // Delete previous location data file if one exists
            if (!empty($this->configuration['custom_location_file']) && $this->configuration['custom_location_file'] != $custom_file[0]) {
              $old_file = File::load($this->configuration['custom_location_file']);
              if ($old_file) {
                \Drupal::service('file.usage')->delete($old_file, 'leafletjs', 'block', $this->getPluginId());
                $old_file->delete();
              }
            }

            // Delete the uploaded CSV file 
            $file->delete();

            // Save the generated .txt file ID to configuration
            $this->configuration['custom_location_file'] = $txt_file->id();

            // Track file usage 
            \Drupal::service('file.usage')->add($txt_file, 'leafletjs', 'block', $this->getPluginId());

            \Drupal::messenger()->addStatus($this->t('Successfully processed @count locations', ['@count' => count($address_points)]));
          }
        }
      }
    }
    else {
      // File was removed and no new file
      if (!empty($this->configuration['custom_location_file'])) {
        $old_file = File::load($this->configuration['custom_location_file']);
        if ($old_file) {
          \Drupal::service('file.usage')->delete($old_file, 'leafletjs', 'block', $this->getPluginId());
          $old_file->delete();
          \Drupal::messenger()->addStatus($this->t('Location data file has been removed'));
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

    // Attach base Leaflet libraries (Leaflet.js, MarkerCluster, ResetView control)
    $build['#attached']['library'][] = 'leafletjs/map_base';

    // Pass configuration 
    $build['#attached']['drupalSettings']['leafletjs'] = [
      'override_autofit' => (bool) $this->configuration['override_autofit'],
      'default_lat' => (float) $this->configuration['default_lat'],
      'default_lon' => (float) $this->configuration['default_lon'],
      'default_zoom' => (int) $this->configuration['default_zoom'],
    ];

    // Load processed location data file if configured
    if (!empty($this->configuration['custom_location_file'])) {
      $file = File::load($this->configuration['custom_location_file']);
      if ($file) {
        $file_url = $file->createFileUrl();

        // Add the location data file as a script tag in the HTML head
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

    // Attach map initialization script (leafletjs.js)
    // This must load after map_base and location data
    $build['#attached']['library'][] = 'leafletjs/map_init';

    return $build;
  }

}
