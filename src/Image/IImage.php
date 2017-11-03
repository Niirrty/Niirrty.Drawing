<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Ni Irrty
 * @package    Niirrty\Drawing
 * @since      2017-11-02
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing\Image;


use Niirrty\Drawing\ContentAlign;
use Niirrty\Drawing\Point;
use Niirrty\Drawing\Rectangle;
use Niirrty\Drawing\Size;
use Niirrty\IDisposable;


/**
 * Each Image class must implement this interface.
 *
 * @since v0.1
 */
interface IImage extends IDisposable
{


   /**
    * Returns the image width.
    *
    * @return integer
    */
   public function getWidth() : int;

   /**
    * Returns the image height.
    *
    * @return integer
    */
   public function getHeight() : int;

   /**
    * Returns the image size.
    *
    * @return \Niirrty\Drawing\Size|null
    */
   public function getSize() : ?Size;

   /**
    * Returns the associated image mime type.
    *
    * @return string|null
    */
   public function getMimeType() : ?string;

   /**
    * Return if the current image is an true color image.
    *
    * @return boolean
    */
   public function isTrueColor() : bool;

   /**
    * Returns if the current image can use some transparency.
    *
    * @return boolean
    */
   public function canUseTransparency() : bool;

   public function __get( string $name );

   public function __clone();

   /**
    * Returns if the current image is an PNG image.
    *
    * @return boolean
    */
   public function isPng() : bool;

   /**
    * Returns if the current image is an GIF image.
    *
    * @return boolean
    */
   public function isGif() : bool;

   /**
    * Returns if the current image is an JPEG image.
    *
    * @return boolean
    */
   public function isJpeg() : bool;

   // IO STUFF

   /**
    * Returns the path of current open image file, if it is defined.
    *
    * @return string
    */
   public function getFile();

   /**
    * Saves the current image to defined image file. If no image file is defined the internally defined image file path
    * is used. If its also not defined a {@see \IO\Exception} is thrown.
    *
    * @param  string  $file    Path of image file to save the current image instance. (Must be only defined if the
    *                          instance does not define a path.
    * @param  integer $quality Image quality if it is saved as an JPEG image. (1-100)
    */
   public function save( string $file = null, int $quality = 75 );

   /**
    * Outputs the current image, including all required HTTP headers and exit the script.
    *
    * @param  integer $quality  Image quality if it is an JPEG image. (1-100)
    * @param  string  $filename Output image file name for HTTP headers.
    */
   public function output( int $quality = 60, string $filename = null );

   /**
    * Returns if currently a image file path is defined that can be used to store the image.
    *
    * @return boolean
    */
   public function hasAssociatedFile() : bool;

   // COLOR STUFF

   /**
    * Returns all user defined image colors as array of {@see \Drawing\ColorGD} objects.
    *
    * return array Array of {@see \Drawing\ColorGD}
    */
   public function getUserColors() : array;

   /**
    * Returns the user color, registered under user defined color name.
    *
    * @param  string $name
    * @return \Niirrty\Drawing\ColorGD|false
    */
   public function getUserColor( string $name );

   /**
    * Adds a new user defined, named color and returns the associated {@see \Drawing\Color} instance.
    *
    * @param  string $name The user defined color name
    * @param  string|RGB-Array|integer|\Drawing\ColorGD|\Drawing\Color $colorDefinition
    * @param  integer $opacity The opacity (0-100) in percent.
    * @return \Niirrty\Drawing\ColorGD or FALSE
    * @throws \Niirrty\ArgumentException
    */
   public function addUserColor( string $name, $colorDefinition, int $opacity = 100 );

   // MANIPULATION

   /**
    * Crops the image part ($width + $height) with defined gravity.
    *
    * @param  int            $width     The width of the required crop result.
    * @param  int            $height    The height of the required crop result.
    * @param  int|string     $gravity   The gravity of the crop rectangle (see GRAVITY_* constants)
    * @param  bool           $internal  Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    * @throws \Niirrty\ArgumentException     If $width or $height is lower than 1
    */
   public function cropByGravity( int $width, int $height, $gravity = \GRAVITY_TOPLEFT, bool $internal = true );

   /**
    * Crops the image part ($width + $height) with defined align
    *
    * @param  int                      $width    The width of the required crop result.
    * @param  int                      $height   The height of the required crop result.
    * @param  \Niirrty\Drawing\ContentAlign $align    The crop rectangle align
    * @param  bool                     $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    * @throws \Niirrty\ArgumentException              If $width or $height is lower than 1
    */
   public function cropByAlign( int $width, int $height, ContentAlign $align, bool $internal = true );

   /**
    * Crops the defined rectangle image part.
    *
    * @param  \Niirrty\Drawing\Rectangle $rect     The cropping rectangle
    * @param  bool                  $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function cropRect( Rectangle $rect, bool $internal = true );

   /**
    * Crops the quadratic part with biggest usable dimension from image, by a gravity.
    *
    * @param  int|string $gravity  The gravity of the crop rectangle (see GRAVITY_* constants)
    * @param  boolean    $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function cropQuadraticByGravity( $gravity = \GRAVITY_CENTER, bool $internal = true );

   /**
    * Crops the quadratic part with biggest usable dimension from image, by alignment.
    *
    * @param  \Niirrty\Drawing\ContentAlign $align    The crop rectangle align
    * @param  boolean                  $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function cropQuadraticByAlign( ContentAlign $align, bool $internal = true );

   /**
    * Rotate the image in 90° steps.
    *
    * @param int                                               $multiplier 1=90, 2=180, 3=270
    * @param string|\Niirrty\Drawing\Color|\Niirrty\Drawing\ColorGD|null $fillColor
    * @param bool                                              $internal  Use internal? (no new instance?)
    * @return \Niirrty\Drawing\Image\IImage
    * @throws \Niirrty\ArgumentException If $angle is not 90 anf not a multiple of 90 or outside accepted range
    */
   public function rotate90( int $multiplier = 1, $fillColor = null, bool $internal = true ) : IImage;

   /**
    * Places the defined $placement image at declared $point inside current image.
    *
    * @param  \Niirrty\Drawing\Image\IImage|string|resource $placement The image that should be placed. It can be a IImage,
    *                                                             a string pointing to the path of a image file, or
    *                                                             a PHP GD image resource handle.
    * @param \Niirrty\Drawing\Point        $point     The point where the image should be placed inside this image.
    * @param int                      $opacity   The opacity of the placed image in % (1-100)
    * @param bool                     $internal  Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    * @throws \Niirrty\ArgumentException if $placement is a unknown format
    */
   public function place( $placement, Point $point, int $opacity = 100, bool $internal = true );

   /**
    * Places the defined $placement image with defined gravity and padding inside this image.
    *
    * @param  \Niirrty\Drawing\Image\IImage|string|resource $placement The image that should be placed. It can be a IImage,
    *                                                             a string pointing to the path of a image file, or
    *                                                             a PHP GD image resource handle.
    * @param  int                      $padding   The padding. (0 means none)
    * @param  mixed                    $gravity   The placement gravity (see \GRAVITY_* constants)
    * @param  int                      $opacity   The opacity of the placed image in % (1-100)
    * @param  bool                     $internal  Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    * @throws \Niirrty\ArgumentException if $placement is a unknown format
    */
   public function placeByGravity(
      $placement, int $padding, $gravity = \GRAVITY_BOTTOMRIGHT, int $opacity = 100, bool $internal = true );

   /**
    * Places the defined $placement image with defined alignment and padding inside this image.
    *
    * @param  \Niirrty\Drawing\Image\IImage|string|resource $placement The image that should be placed. It can be a IImage,
    *                                                             a string pointing to the path of a image file, or
    *                                                             a PHP GD image resource handle.
    * @param  int                      $padding   The padding. (0 means none)
    * @param  \Niirrty\Drawing\ContentAlign $align     The content align of the image that should be placed.
    * @param  int                      $opacity   The opacity of the placed image in % (1-100)
    * @param  bool                     $internal  Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    * @throws \Niirrty\ArgumentException if $placement is a unknown format
    */
   public function placeByAlign(
      $placement, int $padding, ContentAlign $align, int $opacity = 100, bool $internal = true );

   /**
    * Draw a single border arround the image, with defined color (width=1px).
    *
    * @param  string|array|\Niirrty\Drawing\ColorGD|\Niirrty\Drawing\Color $borderColor
    * @param  bool $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function drawSingleBorder( $borderColor, bool $internal = true );

   /**
    * Draw a double border arround the image, with defined colors (width=1px).
    *
    * @param  string|array|\Niirrty\Drawing\ColorGD|\Niirrty\Drawing\Color $innerBorderColor
    * @param  string|array|\Niirrty\Drawing\ColorGD|\Niirrty\Drawing\Color $outerBorderColor
    * @param  bool $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function drawDoubleBorder( $innerBorderColor, $outerBorderColor, bool $internal = true );

   /**
    * Draws a text at a specific point (top left corner)
    *
    * @param  string $text     The text that should be drawn
    * @param  string $font     The font that should be used. For example with a GdImage, here the path of a *.ttf font
    *                          file should be defined. If null here is used so the font will be defined depending to
    *                          font size 1-4. If a *.ttf path here is used the font size should be declared in points.
    *                          If Imagick is used here you only have to define the font name like 'Arial'
    * @param  int    $fontSize The font size
    * @param  string|array|\Niirrty\Drawing\Color|\Niirrty\Drawing\ColorGD $color The text color
    * @param  \Niirrty\Drawing\Point $point The top left corner of the bounding text box.
    * @param  bool   $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function drawText( string $text, $font, $fontSize, $color, Point $point, bool $internal = true );

   /**
    * Draws a text with gravity and padding.
    *
    * @param  string $text     The text that should be drawn
    * @param  string $font     The font that should be used. For example with a GdImage, here the path of a *.ttf font
    *                          file should be defined. If null here is used so the font will be defined depending to
    *                          font size 1-4. If a *.ttf path here is used the font size should be declared in points.
    *                          If Imagick is used here you only have to define the font name like 'Arial'
    * @param  int    $fontSize The font size
    * @param  string|array|\Niirrty\Drawing\Color|\Niirrty\Drawing\ColorGD $color The text color
    * @param  int    $padding  Padding of the text inside the text bounds.
    * @param  mixed  $gravity  The gravity of the text inside this image.
    * @param  bool   $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function drawTextWithGravity(
      string $text, $font, $fontSize, $color, int $padding, $gravity, bool $internal = true );

   /**
    * Draws a text with alignment and padding.
    *
    * @param  string $text     The text that should be drawn
    * @param  string $font     The font that should be used. For example with a GdImage, here the path of a *.ttf font
    *                          file should be defined. If null here is used so the font will be defined depending to
    *                          font size 1-4. If a *.ttf path here is used the font size should be declared in points.
    *                          If Imagick is used here you only have to define the font name like 'Arial'
    * @param  int    $fontSize The font size
    * @param  string|array|\Niirrty\Drawing\Color|\Niirrty\Drawing\ColorGD $color The text color
    * @param  int    $padding  Padding of the text inside the text bounds.
    * @param  \Niirrty\Drawing\ContentAlign $align The align of the text inside this image
    * @param  bool   $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function drawTextWithAlign(
      string $text, $font, $fontSize, $color, int $padding, ContentAlign $align, bool $internal = true );

   /**
    * Shrinks this image by defined percent value, with holding the image proportion.
    *
    * @param int  $percent  Resize value in percent.
    * @param bool $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function shrink( int $percent, bool $internal = true );

   /**
    * Shrinks this image so it fits the defined size, with holding the image proportion.
    *
    * @param \Niirrty\Drawing\Size $maxSize  This is the maximum size of this image after resizing.
    * @param bool             $internal Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function shrinkInto( Size $maxSize, bool $internal = true );

   /**
    * Shrinks this image so it fits the defined size, with holding the image proportion.
    *
    * @param \Niirrty\Drawing\Size $landscapeMaxSize This is the maximum size of this image after resizing if this image
    *                                           is in landscape format.
    * @param \Niirrty\Drawing\Size $portraitMaxSize  This is the maximum size of this image after resizing if this image
    *                                           is in portrait format or quadratic.
    * @param bool             $internal         Handle the action internally? Otherwise a new instance is returned.
    * @return \Niirrty\Drawing\Image\IImage
    */
   public function shrinkIntoByFormat( Size $landscapeMaxSize, Size $portraitMaxSize, bool $internal = true );


}

