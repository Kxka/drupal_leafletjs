/**
 * @file
 * LeafletJS map initialization and interaction.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.leafletjs = {
    attach: function (context, settings) {
      once('leafletjs', '#leafletjs', context).forEach(function (element) {
        var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        var map = L.map('leafletjs', {
          layers: [tiles],
          minZoom: 1.49,  
          maxZoom: 18
        });

        var markers = L.markerClusterGroup({
          chunkedLoading: true,
        });

        // Check if location data is available
        if (typeof addressPoints !== 'undefined' && addressPoints.length > 0) {
          console.log('Loading ' + addressPoints.length + ' locations');

          // Loop through all address points
          for (var i = 0; i < addressPoints.length; i++) {
            var a = addressPoints[i];
            var lat = a[0];
            var lon = a[1];
            var title = a[2];
            var thumbnail = a[3];
            var url = a[4];

            var marker = L.marker(L.latLng(lat, lon), {title: title});

            var popupContent = '<div>' +
              '<img src="' + thumbnail + '" class="popup-thumbnail" alt="' + title + '" onerror="this.style.display=\'none\'">' +
              '<br>' +
              '<a href="' + url + '" target="_blank" class="popup-title">' + title + '</a>' +
              '</div>';

            marker.bindPopup(popupContent);
            markers.addLayer(marker);
          }

          map.addLayer(markers);

          // Auto-fit map with zoom limits
          setTimeout(function() {
            var bounds = markers.getBounds();
            if (bounds.isValid()) {
              map.fitBounds(bounds, {
                padding: [50, 50],
              });
            }
          }, 100);

          console.log('LeafletJS map loaded with ' + addressPoints.length + ' locations');
        } else {
          console.log('LeafletJS map loaded - no location data uploaded yet');
        }
      });
    }
  };

})(Drupal, once);
