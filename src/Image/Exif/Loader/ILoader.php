<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Loader
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing\Image\Exif\Loader;


use Niirrty\ArgumentException;
use Niirrty\Drawing\Image\Exif\ImageInfo;
use Niirrty\IO\File;
use Niirrty\Web\Url;


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
     * @param string|Url|File $imageFile
     *
     * @return ImageInfo|null
     */
    function load( $imageFile ): ?ImageInfo;

    /**
     * Sets the config values of the ILoader implementation.
     *
     * @param array $configData
     *
     * @throws ArgumentException
     */
    function configure( array $configData );

    /**
     * Returns if the loader is configured right.
     *
     * @return bool
     */
    function isConfigured();


}

