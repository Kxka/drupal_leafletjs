## LeafletJS Drupal Module

Displays a leaflet map using a preprocessed file (.csv) with all location data to prevent long query times for large data set

## Usage

1. Go to Structure > Block layout
2. Click "Place block"
3. Search for "LeafletJS"

## Configuration
 - Title
 - Map Height
 - Location Data File in .csv
   
   Note: The file will be converted into a .txt format with new file name after saving
         Title and Coordinates row is required for valid marker

   Example CSV:
   ```
   Title,Coordinates,Link,Thumbnail
   "Title1","67.913381, -48.204069",https://islandora.dev/node/10,https://islandora.dev/system/files/styles/large/private/file1
   "Title1","72.425063, 91.628126",https://islandora.dev/node/9,https://islandora.dev/system/files/styles/large/private/file2
    ...
   ```

 - Override autofit zoom and center to set default zoom and center

## Folder Structure
```
drupal_leafletjs/
├── css/
│   ├── style.css                          # Custom map styles
│   └── L.Control.ResetView.min.css       # Reset button styles
├── images/
│   └── redo-solid.svg                     # Reset button icon
├── js/
│   ├── leafletjs.js                       # Map initialization script
│   ├── markercluster-src.js              # Marker clustering library
│   └── L.Control.ResetView.min.js        # Reset view control
├── libraries/
│   ├── MarkerCluster.css                  # Cluster styling
│   └── MarkerCluster.Default.css         # Default cluster theme
├── src/
│   └── Plugin/
│       └── Block/
│           └── LeafletjsBlock.php        # Main block plugin
├── templates/
│   └── leafletjs.html.twig               # Map container template
├── leafletjs.info.yml                     # Module definition
├── leafletjs.libraries.yml                # Library definitions
├── leafletjs.module                       # Module hooks
└── README.md                              
```

## Credits

This module uses the following open-source libraries:
- **[Leaflet](https://leafletjs.com/)** 

- **[Leaflet.markercluster](https://github.com/Leaflet/Leaflet.markercluster)** 

- **[L.Control.ResetView](https://github.com/drustack/Leaflet.ResetView)** 
