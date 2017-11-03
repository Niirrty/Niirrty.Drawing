<?php


if ( class_exists( '\Imagick' ) )
{
   define( 'GRAVITY_TOP', \Imagick::GRAVITY_NORTH );
   define( 'GRAVITY_TOPLEFT', \Imagick::GRAVITY_NORTHWEST );
   define( 'GRAVITY_TOPRIGHT', \Imagick::GRAVITY_NORTHEAST );
   define( 'GRAVITY_LEFT', \Imagick::GRAVITY_WEST );
   define( 'GRAVITY_RIGHT', \Imagick::GRAVITY_EAST );
   define( 'GRAVITY_BOTTOMLEFT', \Imagick::GRAVITY_SOUTHWEST );
   define( 'GRAVITY_BOTTOMRIGHT', \Imagick::GRAVITY_SOUTHEAST );
   define( 'GRAVITY_BOTTOM', \Imagick::GRAVITY_SOUTH );
   define( 'GRAVITY_CENTER', \Imagick::GRAVITY_CENTER );
}
else
{
   define( 'GRAVITY_TOP', 'north' );
   define( 'GRAVITY_TOPLEFT', 'northwest' );
   define( 'GRAVITY_TOPRIGHT', 'northeast' );
   define( 'GRAVITY_LEFT', 'west' );
   define( 'GRAVITY_RIGHT', 'east' );
   define( 'GRAVITY_BOTTOMLEFT', 'southwest' );
   define( 'GRAVITY_BOTTOMRIGHT', 'southeast' );
   define( 'GRAVITY_BOTTOM', 'south' );
   define( 'GRAVITY_CENTER', 'center' );
}

