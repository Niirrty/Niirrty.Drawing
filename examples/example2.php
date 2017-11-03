<?php


include dirname( __DIR__ ) . '/vendor/autoload.php';


$img = \Niirrty\Drawing\Image\GdImage::LoadFile( __DIR__ . '/images/dresden-from-pillnitz.jpg' );

$img->placeByGravity( __DIR__ . '/images/grashopper.gif', 10 );

$img->save( __DIR__ . '/images/montage.jpg' );

