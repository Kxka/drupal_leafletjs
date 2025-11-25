# LeafletJS Drupal Module

Allows users to upload a preprocessed file (.csv) with all location data to prevent long query times for large data set

# Usage

1. Go to Structure > Block layout
2. Click "Place block"
3. Search for "LeafletJS"

# Configuration
 - Title
 - Map Height
 - Location Data File in .csv
   Note: The file will be converted into a .txt format with new file name after saving

   CSV Format:
   Title,Coordinates,Link,Thumbnail
   "Title1","67.913381, -48.204069",https://islandora.dev/node/10,https://islandora.dev/system/files/styles/large/private/file1
   "Title1","72.425063, 91.628126",https://islandora.dev/node/9,https://islandora.dev/system/files/styles/large/private/file2
    ...

 - Override autofit zoom and center to set default zoom and center

