<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Loader
 * @since      2017-11-02
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing\Image\Exif\Loader;


use Niirrty\Drawing\Image\Exif\ImageInfo;
use Niirrty\Drawing\Image\Exif\Loader\PHP as PHPLoader;


/**
 * A static helper class for loading exif data by the best available way.
 *
 * @since v0.1.0
 */
class AutoLoader
{


   /**
    * Loads exif data by exiftool, exiv2, php-internal or from a specific JSON file
    *
    * @param string $imageFile
    * @param string $exifToolPath
    * @param string $exiV2Path
    * @return \Niirrty\Drawing\Image\Exif\ImageInfo|null
    */
   public static function Load( $imageFile, $exifToolPath = null, $exiV2Path = null ) : ?ImageInfo
   {

      $exifToolLoader = new ExifTool();
      if ( ! $exifToolLoader->isConfigured() )
      {
         if ( ! empty( $exifToolPath ) )
         {
            try { $exifToolLoader->configure( [ 'path' => $exifToolPath ] ); }
            catch ( \Throwable $ex ) { $ex = null; }
         }
      }
      if ( $exifToolLoader->isConfigured() )
      {
         try { return $exifToolLoader->load( $imageFile ); }
         catch ( \Throwable $ex ) { $ex = null; }
      }

      $exiV2Loader = new ExiV2();
      if ( ! $exiV2Loader->isConfigured() && ! empty( $exiV2Path ) )
      {
         try { $exiV2Loader->configure( [ 'path' => $exiV2Path ] ); }
         catch ( \Exception $ex ) { $ex = null; }
      }
      if ( $exiV2Loader->isConfigured() )
      {
         try { return $exiV2Loader->load( $imageFile ); }
         catch ( \Exception $ex ) { $ex = null; }
      }

      $phpLoader = new PHPLoader();
      try { return $phpLoader->load( $imageFile ); }
      catch ( \Exception $ex ) { $ex = null; }

      $jsonLoader = new JSon();
      try { return $jsonLoader->load( $imageFile ); }
      catch ( \Exception $ex ) { $ex = null; }

      return null;

   }

}

