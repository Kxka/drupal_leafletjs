/**
 * @file
 * LeafletJS map initialization and interaction.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.leafletjs = {
    attach: function (context, settings) {
      once('leafletjs', '#leafletjs', context).forEach(function (element) {

        // Get default settings from Drupal
        var overrideAutofit = (settings.leafletjs && settings.leafletjs.override_autofit) ? settings.leafletjs.override_autofit : false;
        var defaultLat = (settings.leafletjs && settings.leafletjs.default_lat) ? settings.leafletjs.default_lat : 51.505;
        var defaultLon = (settings.leafletjs && settings.leafletjs.default_lon) ? settings.leafletjs.default_lon : -0.09;
        var defaultZoom = (settings.leafletjs && settings.leafletjs.default_zoom) ? settings.leafletjs.default_zoom : 13;

        var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        var map = L.map('leafletjs', {
          layers: [tiles],
          minZoom: 1,
          maxZoom: 18  // Known issue: Map does not work well with non-integer zoom levels
        }).setView([defaultLat, defaultLon], defaultZoom);

        console.log('Map initialized with center:', map.getCenter(), 'zoom:', map.getZoom());

        var markers = L.markerClusterGroup({
          chunkedLoading: true,
        });

        // Initialize reset control with configured default values
        var resetControl = L.control.resetView({
          position: "topleft",
          title: "Reset view",
          latlng: L.latLng([defaultLat, defaultLon]),
          zoom: defaultZoom,
        });
        resetControl.addTo(map);
        console.log('Reset control initialized with latlng:', resetControl.options.latlng, 'zoom:', resetControl.options.zoom);

        // Check if location data is available
        if (typeof geoJsonData !== 'undefined') {
          // GeoJSON format
          L.geoJSON(geoJsonData, {
            onEachFeature: function(feature, layer) {
              var p = feature.properties;
              var thumbnail = p.islandora_object_thumbnail || '';
              var url = p.search_api_url || '';
              var title = p.title || '';

              var popup = '<div>' +
                '<img src="' + thumbnail + '" class="popup-thumbnail" alt="' + title + '" onerror="this.style.display=\'none\'">' +
                '<br>' +
                '<a href="' + url + '" target="_blank" class="popup-title">' + title + '</a>' +
                '</div>';

              layer.bindPopup(popup);
              markers.addLayer(layer);
            }
          });
        }
        else if (typeof addressPoints !== 'undefined' && addressPoints.length > 0) {
          // CSV array format
          for (var i = 0; i < addressPoints.length; i++) {
            var a = addressPoints[i];
            var marker = L.marker([a[0], a[1]], {title: a[2]});
            marker.bindPopup('<div><img src="' + a[3] + '" class="popup-thumbnail" alt="' + a[2] + '" onerror="this.style.display=\'none\'"><br><a href="' + a[4] + '" target="_blank" class="popup-title">' + a[2] + '</a></div>');
            markers.addLayer(marker);
          }
        }

        map.addLayer(markers);

        // Auto-fit map with zoom limits (only if override is not enabled)
        if (!overrideAutofit) {
          setTimeout(function() {
            var bounds = markers.getBounds();
            if (bounds.isValid()) {
              console.log('Auto-fitting map to marker bounds');
              map.fitBounds(bounds, {
                padding: [50, 50],
              });

              console.log('Map after fitBounds - center:', map.getCenter(), 'zoom:', map.getZoom());

              // Update reset control to use the fitted view
              resetControl.options.latlng = map.getCenter();
              resetControl.options.zoom = map.getZoom();
              console.log('Reset control updated to fitted view - latlng:', resetControl.options.latlng, 'zoom:', resetControl.options.zoom);
            }
          }, 100);
        } else {
          console.log('Override autofit enabled - keeping configured defaults');
        }
      });
    }
  };

})(Drupal, once);
