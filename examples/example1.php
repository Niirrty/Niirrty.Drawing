<?php


include dirname( __DIR__ ) . '/vendor/autoload.php';


$exifData = \Niirrty\Drawing\Image\Exif\Loader\AutoLoader::Load( __DIR__ . '/images/dresden-from-pillnitz.jpg' );


print_r( $exifData );


