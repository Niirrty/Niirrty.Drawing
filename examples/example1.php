<?php


include dirname( __DIR__ ) . '/vendor/autoload.php';


use Niirrty\Drawing\Image\Exif\Loader\PHP as PHPLoader;


$phpLoader = new PHPLoader();

try
{
    $exifData = $phpLoader->load( __DIR__ . '/images/dresden-from-pillnitz.jpg' );
    print_r( $exifData );
}
catch ( Exception $ex )
{
    echo $ex;
}


