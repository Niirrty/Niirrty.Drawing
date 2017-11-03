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
use Niirrty\IO\File;
use Niirrty\IO\Path;


/**
 * Loads EXIF data from a image sidecar JSON file.
 *
 * <code>
 * # Image file   :  /foo/bar/bar/image.jpg
 * # => JSON file :  /foo/bar/bar/image.jpg.json
 * #    or        :  /foo/bar/bar/image.json
 * </code>
 *
 * @since v0.1.0
 */
class JSon implements ILoader
{

   
   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    */
   public function __construct() { }

   # </editor-fold>

   
   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * @inheritdoc
    */
   public function configure( array $configData ) { }

   /**
    * @inheritdoc
    */
   public function isConfigured()
   {

      return true;

   }

   /**
    * @inheritdoc
    */
   public function load( $imageFile ) : ?ImageInfo
   {

      if ( ! \file_exists( $imageFile ) )
      {
         // Do nothing if the defined image file does not exist.
         return null;
      }

      // Build the first case of a usable JSON file path (/origin/image.jpg.json)
      $jsonFile = Path::Combine( $imageFile, '.json' );

      if ( ! \file_exists( $jsonFile ) )
      {
         // The first case JSON file does not exist
         // Build the second case of a usable JSON file path (/origin/image.json)
         $jsonFile = File::ChangeExtension( $imageFile, 'json' );

         if ( ! \file_exists( $jsonFile ) )
         {
            // Do nothing because the required JSON file does not exist
            return null;
         }
      }

      // OK: Now we have a usable image + JSON file.

      try
      {
         // read + parse the JSON data from file
         $data = \json_decode( \file_get_contents( $jsonFile ), true );

         if ( ! \is_array( $data ) || \count( $data ) < 1 )
         {
            // Uuhhh, wrong JSON file format or content. So we are done here
            return null;
         }

         // Return the resulting ImageInfo instance
         return new ImageInfo( $data, $imageFile );

      }
      catch ( \Throwable $ex )
      {
         return null;
      }

   }

   # </editor-fold>

   
}

