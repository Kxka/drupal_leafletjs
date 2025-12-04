## LeafletJS Drupal Module

Displays a leaflet map using a preprocessed file (.csv or .geojson) with all location data to prevent long query times for large data sets

## Usage

1. Go to Structure > Block layout
2. Click "Place block"
3. Search for "LeafletJS"

## Configuration
- Title
- Map Height
- Location Data File: `.csv`, `.json`, or `.geojson`
- Override autofit zoom and center to set default zoom and center

## File Formats

### CSV Format
```csv
Title,Coordinates,Link,Thumbnail
"Title1","67.913381, -48.204069",https://islandora.dev/node/10,https://islandora.dev/system/files/file1.jpg
"Title2","72.425063, 91.628126",https://islandora.dev/node/9,https://islandora.dev/system/files/file2.jpg
```

### GeoJSON Format
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": { "type": "Point", "coordinates": [18.692492, -51.061725] },
      "properties": {
        "name": "",
        "description": "",
        "title": "Contemporary Materials",
        "search_api_url": "https://islandora.dev/node/2",
        "islandora_object_thumbnail": "https://islandora.dev/system/files/styles/large/private/2025-12/facebook-1-logo-png-transparent.png?itok=rVxWQ-Rw"
      }
    },
    {
      "type": "Feature",
      "geometry": { "type": "Point", "coordinates": [120.583837, -67.782708] },
      "properties": {
        "name": "",
        "description": "",
        "title": "Research Manuscripts",
        "search_api_url": "https://islandora.dev/node/4",
        "islandora_object_thumbnail": "https://islandora.dev/system/files/styles/large/private/2025-12/facebook-1-logo-png-transparent.png?itok=rVxWQ-Rw"
      }
    }
  ]
}
```

**Note:** Files are converted to `.txt` format after upload. GeoJSON coordinates use `[longitude, latitude]` order.

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