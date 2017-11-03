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


/**
 * Each exif data loader must implement this interface.
 *
 * @since v0.1.0
 */
interface ILoader
{
   

   /**
    * Loads the exif data from defined image file.
    *
    * @param string|\Niirrty\Web\Url|\Niirrty\IO\File $imageFile
    * @return \Niirrty\Drawing\Image\Exif\ImageInfo|null
    */
   function load( $imageFile ) : ?ImageInfo;

   /**
    * Sets the config values of the ILoader implementation.
    *
    * @param array $configData
    * @throws \Niirrty\ArgumentException
    */
   function configure( array $configData );

   /**
    * Returns if the loader is configured right.
    *
    * @return bool
    */
   function isConfigured();
   

}

