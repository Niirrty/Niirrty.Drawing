<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Ni Irrty
 * @package    Niirrty\Drawing
 * @since      2017-11-02
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing;


use Niirrty\ArgumentException;
use Niirrty\ArrayHelper;
use Niirrty\IO\FileNotFoundException;
use Niirrty\IO\IOException;


/**
 *
 *
 * @since v0.1
 */
class Size
{


   # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The current width.
    *
    * @var integer
    */
   public $width;

   /**
    * The current height.
    *
    * @var integer
    */
   public $height;

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Inits a new instance.
    *
    * @param  integer $width  The current width.
    * @param  integer $height The current height.
    */
   public function __construct( int $width = 0, int $height = 0 )
   {

      $this->width  = $width;
      $this->height = $height;

      if ( $this->width < 0 )
      {
         $this->width = 0;
      }

      if ( $this->height < 0 )
      {
         $this->height = 0;
      }

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   # <editor-fold desc="Checking Methods">

   /**
    * Returns if one of the 2 class properties is 0 or lower.
    *
    * @return boolean
    */
   public function isEmpty() : bool
   {
      return $this->width <= 0 || $this->height <= 0;
   }

   /**
    * Returns if the current size can contain the size dimentions of the defined size.
    *
    * @param  \Niirrty\Drawing\Size $size
    * @return boolean
    */
   public final function contains( Size $size ) : bool
   {
      return (
         ( $size->width <= $this->width )
         &&
         ( $size->height <= $this->height )
      );
   }

   /**
    * Returns if the current size defines a quadratic size (width === height)
    *
    * @return boolean
    */
   public final function isQuadratic() : bool
   {
      return ( $this->width == $this->height );
   }

   /**
    * Returns if the current size uses a portrait format (width &lt; height)
    *
    * @return boolean
    */
   public final function isPortrait() : bool
   {
      return ( $this->width < $this->height );
   }

   /**
    * Returns if the current size uses a portrait format (width &gt; height)
    *
    * @return boolean
    */
   public final function isLandscape() : bool
   {
      return ( $this->width > $this->height );
   }

   /**
    * Returns if the current size uses a near quadratic format. It means, if width and height
    * difference is lower or equal to ?? percent.
    *
    * @param double $maxDifference If you want to change the allowed difference percent value you can do it here.
    *                              Valid values here are 0.01 (means 1%) to 0.3 (means 30%) default is 0.15
    * @return boolean
    */
   public final function isNearQuadratic( float $maxDifference = 0.15 ) : bool
   {

      if ( $this->isQuadratic() )
      {
         return true;
      }

      if ( $maxDifference >= 0.01 && $maxDifference <= 0.3 )
      {
         $diff = 1.0 + $maxDifference;
      }
      else
      {
         $diff = 1.15;
      }

      if ( $this->isPortrait() )
      {
         return ( ( 0.0 + $this->height ) / $this->width ) <= $diff;
      }

      return ( ( 0.0 + $this->width ) / $this->height ) <= $diff;

   }

   # </editor-fold>

   # <editor-fold desc="Contracting Methods">

   /**
    * Contracts both sides (width + height) by the defined value. (w=100, h=200) contracted by 50 is (w=50 h=150)
    *
    * @param  int $value
    * @return \Niirrty\Drawing\Size
    */
   public final function contract( int $value = 1 ) : Size
   {

      $this->width  = $this->width - $value;
      $this->height = $this->height - $value;

      return $this;

   }

   /**
    * Contracts both sides (width + height) by the defined percent value. (w=100, h=200) contracted by 10%
    * is (w=90 h=180).
    *
    * @param  int $percent The percent value for contraction. Must be lower than 100!
    * @return \Niirrty\Drawing\Size
    * @throws \Niirrty\ArgumentException if $percent is not lower than 100 or if its lower than 1
    */
   public final function contractByPercent( int $percent ) : Size
   {

      if ( $percent >= 100 || $percent < 1 )
      {
         throw new ArgumentException(
            'percent',
            $percent,
            'Contraction only works with percent values lower 100 and bigger than 0'
         );
      }

      $this->width  = \intval( \floor( $this->width  * ( ( 100 - $percent ) / 100 ) ) );
      $this->height = \intval( \floor( $this->height * ( ( 100 - $percent ) / 100 ) ) );

      return $this;

   }

   /**
    * Contracts the current size to fit the defined Size, by holding its proportions.
    *
    * @param  \Niirrty\Drawing\Size $maxSize
    * @return bool TRUE on success, or FALSE, if current size is already smaller than defined size.
    */
   public final function contractToMaxSize( Size $maxSize ) : bool
   {

      if ( ( $this->width  < $maxSize->width )
        && ( $this->height < $maxSize->height ) )
      {
         return false;
      }

      if ( ( $this->width  === $maxSize->width )
        && ( $this->height === $maxSize->height ) )
      {
         return true;
      }

      $dw = $this->width / $maxSize->width;
      $dh = $this->height / $maxSize->height;

      if ( $dw < $dh )
      {
         $this->width = \intval(
            \floor(
               ( $this->width * $maxSize->height ) / $this->height
            )
         );
         $this->height = $maxSize->height;
      }
      else if ( $dw > $dh )
      {
         $this->height = \intval(
            \floor(
               ( $maxSize->width * $this->height ) / $this->width
            )
         );
         $this->width = $maxSize->width;
      }
      else # ($dw == $dh)
      {
         $this->width  = $maxSize->width;
         $this->height = $maxSize->height;
      }

      return true;

   }

   /**
    * Contracts the longest side to the defined length and also contracts the shorter side to hold the proportion
    * of this size. If the current size defines a quadratic size, it is also contracted but with searching the
    * longer side.
    *
    * @param  int $newMaxSideLength
    * @return bool TRUE on success, or FALSE if longest side is already shorter or equal to $newMaxSideLength.
    */
   public final function contractMaxSideTo( int $newMaxSideLength ) : bool
   {

      if ( $this->isPortrait() )
      {

         if ( $newMaxSideLength >= $this->height )
         {
            return false;
         }
         $resultPercent = ( 100 * $newMaxSideLength ) / $this->height;
         $this->width = \intval(
            \floor(
               ( $resultPercent * $this->width ) / 100
            )
         );
         $this->height = $newMaxSideLength;

      }
      else
      {

         if ( $newMaxSideLength >= $this->width )
         {
            return false;
         }
         $resultPercent = ( 100 * $newMaxSideLength ) / $this->width;
         $this->height = \intval(
            \floor(
               ( $resultPercent * $this->height ) / 100
            )
         );
         $this->width = $newMaxSideLength;

      }

      return true;

   }

   /**
    * Contracts the longest side to the defined length and also contracts the shorter side to hold the proportion
    * of this size. The value, used as max side length is depending to the current size format.
    *
    * $newLandscapeMaxWidth is used if the current size uses a landscape ord quadratic format. Otherwise the
    * $newPortraitMaxHeight is used.
    *
    * @param  int $newLandscapeMaxWidth The max width, used if size has landscape or quadratic format.
    * @param  int $newPortraitMaxHeight The max height, used if size has portrait format.
    * @return bool
    */
   public final function contractMaxSideTo2( int $newLandscapeMaxWidth, int $newPortraitMaxHeight ) : bool
   {

      if ( $this->isPortrait() )
      {

         if ( $newPortraitMaxHeight >= $this->height )
         {
            return false;
         }
         $resultPercent = ( 100 * $newPortraitMaxHeight ) / $this->height;
         $this->width = \intval(
            \floor(
               ( $resultPercent * $this->width ) / 100
            )
         );
         $this->height = $newPortraitMaxHeight;

      }
      else
      {

         if ( $newLandscapeMaxWidth >= $this->width )
         {
            return false;
         }
         $resultPercent = ( 100 * $newLandscapeMaxWidth ) / $this->width;
         $this->height = \intval(
            \floor(
               ( $resultPercent * $this->height ) / 100
            )
         );
         $this->width = $newLandscapeMaxWidth;

      }

      return true;

   }

   # </editor-fold>

   # <editor-fold desc="Expanding Methods">

   /**
    * Expands both sides (width + height) by the defined value. (w=100, h=200) expanded by 50 is (w=150 h=250)
    *
    * @param  int $value
    * @return \Niirrty\Drawing\Size
    */
   public final function expand( int $value = 1 ) : Size
   {

      $this->width  = $this->width + $value;
      $this->height = $this->height + $value;

      return $this;

   }

   /**
    * Expands the longest side to the defined length and also expands the shorter side to hold the proportion
    * of this size. If the currennt size defines a quadratic size, its also expanded but without searching the
    * longer side.
    *
    * @param  integer $newMaxSideLength
    * @return boolean TRUE on success, or FALSE if longest side is already longer than $newMaxSideLength.
    */
   public final function expandMaxSideTo( int $newMaxSideLength ) : bool
   {

      if ( $this->isPortrait() )
      {

         if ( $newMaxSideLength < $this->height )
         {
            return false;
         }
         $resultPercent = ( 100 * $newMaxSideLength ) / $this->height;
         $this->width = \intval(
            \floor(
               ( $resultPercent * $this->width ) / 100
            )
         );
         $this->height = $newMaxSideLength;

      }
      else
      {

         if ( $newMaxSideLength < $this->width )
         {
            return false;
         }
         $resultPercent = ( 100 * $newMaxSideLength ) / $this->width;
         $this->height = \intval(
            \floor(
               ( $resultPercent * $this->height ) / 100
            )
         );
         $this->width = $newMaxSideLength;

      }

      return true;

   }

   /**
    * Expands the longest side to the defined length and also expands the shorter side to hold the proportion
    * of this size. The value, used as max side length is depending to the current size format.
    *
    * $newLandscapeMaxWidth is used if the current size uses a landscape ord quadratic format. Otherwise the
    * $newPortraitMaxHeight is used.
    *
    * @param  int $newLandscapeMaxWidth The max width, used if size has landscape or quadratic format.
    * @param  int $newPortraitMaxHeight The max height, used if size has portrait format.
    * @return bool
    */
   public final function expandMaxSideTo2( int $newLandscapeMaxWidth, int $newPortraitMaxHeight ) : bool
   {

      if ( $this->isPortrait() )
      {

         if ( $newPortraitMaxHeight <= $this->height )
         {
            return false;
         }
         $resultPercent = ( 100 * $newPortraitMaxHeight ) / $this->height;
         $this->width = \intval(
            \floor(
               ( $resultPercent * $this->width ) / 100
            )
         );
         $this->height = $newPortraitMaxHeight;

      }
      else
      {

         if ( $newLandscapeMaxWidth <= $this->width )
         {
            return false;
         }
         $resultPercent = ( 100 * $newLandscapeMaxWidth ) / $this->width;
         $this->height = \intval(
            \floor(
               ( $resultPercent * $this->height ) / 100
            )
         );
         $this->width = $newLandscapeMaxWidth;

      }

      return true;

   }

   # </editor-fold>

   # <editor-fold desc="Resizing Methods">

   /**
    * Changes both sides (width + height) by the defined value. (w=100, h=200) resized by -10 is (w=90 h=190)
    *
    * @param  int $value
    * @return \Niirrty\Drawing\Size
    */
   public final function resize( int $value = 1 ) : Size
   {

      $this->width  = $this->width + $value;
      $this->height = $this->height + $value;

      return $this;

   }

   /**
    * Resizes the longest side to the defined length and also resizes the shorter side to hold the proportion
    * of this size. If the currennt size defines a quadratic size, its also resized but without searching the
    * longer side.
    *
    * @param  integer $newSideLength
    * @return boolean
    */
   public final function resizeMaxSideTo( int $newSideLength ) : bool
   {

      if ( $this->isPortrait() )
      {

         if ( $newSideLength == $this->height )
         {
            return false;
         }
         if ( $newSideLength < $this->height )
         {
            return $this->contractMaxSideTo( $newSideLength );
         }
         return $this->expandMaxSideTo( $newSideLength );

      }

      if ( $newSideLength == $this->width )
      {
         return false;
      }

      if ( $newSideLength < $this->width )
      {
         return $this->contractMaxSideTo( $newSideLength );
      }

      return $this->expandMaxSideTo( $newSideLength );

   }

   /**
    * Resizes the longest side to the defined length and also resizes the shorter side to hold the proportion
    * of this size. The value, used as max side length is depending to the current size format.
    *
    * $newLandscapeWidth is used if the current size uses a landscape ord quadratic format. Otherwise the
    * $newPortraitHeight is used.
    *
    * @param  int $newLandscapeWidth Maximale Breite für Querformat und Quadratisch
    * @param  int $newPortraitHeight Maximale Höhe für Hochformat
    * @return bool
    */
   public final function resizeMaxSideTo2( int $newLandscapeWidth, int $newPortraitHeight ) : bool
   {
      if ( $this->isPortrait() )
      {
         if ( $newPortraitHeight == $this->height )
         {
            return false;
         }
         if ( $newPortraitHeight < $this->height )
         {
            return $this->contractMaxSideTo2( $newLandscapeWidth, $newPortraitHeight );
         }
         return $this->expandMaxSideTo2( $newLandscapeWidth, $newPortraitHeight );
      }
      if ( $newLandscapeWidth == $this->width )
      {
         return false;
      }
      if ( $newPortraitHeight < $this->width )
      {
         return $this->contractMaxSideTo2( $newLandscapeWidth, $newPortraitHeight );
      }
      return $this->expandMaxSideTo2( $newLandscapeWidth, $newPortraitHeight );
   }

   # </editor-fold>

   # <editor-fold desc="XML-Methods">

   /**
    * Write the current instance data as XML element attributes ('width' + 'height') to defined XmlWriter.
    *
    * @param \XMLWriter $w The XmlWriter.
    */
   public final function writeXmlAttributes( \XMLWriter $w )
   {

      $w->writeAttribute( 'width',  $this->width  );
      $w->writeAttribute( 'height', $this->height );

   }

   /**
    * Writes the current instance data as XML element to a XmlWriter.
    *
    * The resulting XML element looks like follow:
    *
    * <b>&lt;Size width="0" height="0"/&gt;</b>
    *
    * @param \XMLWriter $w           The XmlWriter.
    * @param string     $elementName The name of the resulting XML element.  (default='Size')
    *                                If no usable element name is defined, only the attributes are written!
    */
   public final function writeXml( \XMLWriter $w, string $elementName = 'Size' )
   {

      $writeElement = ! empty( $elementName );

      if ( $writeElement )
      {
         $w->startElement( $elementName );
      }

      $this->writeXmlAttributes( $w );

      if ( $writeElement )
      {
         $w->endElement();
      }

   }

   # </editor-fold>

   # <editor-fold desc="Rotation Methods">

   /**
    * Rotate the current size by 90°. (In other words: width and height are exchanged.)
    *
    * @return \Niirrty\Drawing\Size
    */
   public final function rotateSquare() : Size
   {

      $tmp          = $this->width;
      $this->width  = $this->height;
      $this->height = $tmp;

      return $this;

   }

   # </editor-fold>

   /**
    * Returns the size string. Format is: "width=?; height=?"
    *
    * @return string
    */
   public function __toString()
   {

      return \sprintf( 'width=%d; height=%d', $this->width, $this->height );

   }

   /**
    * Gibt Breite und Höhe als Array zurück Zuordnung ist:
    * 0=Breite, 1=Höhe, 'width' und 'height'
    *
    * @return array Array mit Keys 0=Breite, 1=Höhe, 'width' und 'height'
    */
   public function toArray() : array
   {
      return array(
         0        => $this->width,
         1        => $this->height,
         'width'  => $this->width,
         'height' => $this->height
      );
   }

   /**
    * @return \Niirrty\Drawing\Size
    */
   public function __clone()
   {

      return new Size( $this->width, $this->height );

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

   /**
    * Parses a array to a {@see \Drawing\Size} instance.
    *
    * @param array $objectData
    * @return \Niirrty\Drawing\Size
    */
   public static function FromArray( array $objectData ) : Size
   {

      $w = 0;
      if ( isset( $objectData[ 'width' ] ) )
      {
         $w = \intval( $objectData[ 'width' ] );
      }
      elseif ( isset( $objectData[ 'Width' ] ) )
      {
         $w = \intval( $objectData['Width'] );
      }
      elseif ( isset( $objectData[ 0 ] ) )
      {
         $w = \intval( $objectData[ 0 ] );
      }

      $h = 0;
      if ( isset( $objectData[ 'height' ] ) )
      {
         $h = \intval( $objectData[ 'height' ] );
      }
      elseif ( isset( $objectData[ 'Height' ] ) )
      {
         $h = \intval( $objectData[ 'Height' ] );
      }
      elseif ( isset( $objectData[ 1 ] ) )
      {
         $h = \intval( $objectData[ 1 ] );
      }

      return new Size( $w, $h );

   }

   /**
    * Parses a string to a {@see \Niirrty\Drawing\Size} instance.
    *
    * @param  string $objectString
    * @return \Niirrty\Drawing\Size|FALSE
    */
   public static function FromString( string $objectString )
   {

      if ( \preg_match( '~^width=(\d{1,4});\s*height=(\d{1,4})$~i', $objectString, $hits ) )
      {
         return new Size( \intval( $hits[ 1 ] ), \intval( $hits[ 2 ] ) );
      }

      if ( \preg_match( '~^(\d{1,4}),\s*(\d{1,4})$~i', $objectString, $hits ) )
      {
         return new Size( \intval( $hits[ 1 ] ), \intval( $hits[ 2 ] ) );
      }

      $res = ArrayHelper::ParseAttributes( $objectString, true, false );

      if ( empty( $res ) || ! isset( $res[ 'width' ] ) || ! isset( $res[ 'height' ] ) )
      {
         if ( ! \preg_match( '~^[A-Za-z0-9_.:,;/!$%~*+-]+$~', $objectString ) )
         {
            return false;
         }
         if ( ! \file_exists( $objectString ) )
         {
            return false;
         }
         try
         {
            $tmp = \getimagesize( $objectString );
         }
         catch ( \Throwable $ex )
         {
            $ex = null;
            return false;
         }
         return new Size( \intval( $tmp[ 0 ] ), \intval( $tmp[ 1 ] ) );
      }

      $w = \strval( \intval( $res[ 'width' ] ) );
      $h = \strval( \intval( $res[ 'height' ] ) );

      if ( $h != $res[ 'height' ] || $w < $res[ 'width' ] )
      {
         return false;
      }

      return new Size( \intval( $res[ 'width' ] ), \intval( $res[ 'height' ] ) );

   }

   /**
    * Parses a XML element to a {@see \Drawing\Size} instance.
    *
    * @param  \SimpleXMLElement $xmlElement
    * @params bool              $strict
    * @return \Niirrty\Drawing\Size|bool
    */
   public static function FromXml( \SimpleXMLElement $xmlElement )
   {

      // Getting Width
      $w = null;
      if ( isset( $xmlElement[ 'width' ] ) )
      {
         $w = \intval( $xmlElement[ 'width' ] );
      }
      else if ( isset( $xmlElement[ 'Width' ] ) )
      {
         $w = \intval( $xmlElement[ 'Width' ] );
      }
      else if ( isset( $xmlElement[ 'attributes' ][ 'width' ] ) )
      {
         $w = \intval( $xmlElement[ 'attributes' ][ 'width' ] );
      }
      else if ( isset( $xmlElement[ 'attributes' ][ 'Width' ] ) )
      {
         $w = \intval( $xmlElement[ 'attributes' ][ 'Width' ] );
      }
      else if ( isset( $xmlElement[ '@attributes' ][ 'width' ] ) )
      {
         $w = \intval( $xmlElement[ '@attributes' ][ 'width' ] );
      }
      else if ( isset( $xmlElement[ '@attributes' ][ 'Width' ] ) )
      {
         $w = \intval( $xmlElement[ '@attributes' ][ 'Width' ] );
      }

      // Getting Height
      $h = null;
      if ( isset( $xmlElement[ 'height' ] ) )
      {
         $h = \intval( $xmlElement[ 'height' ] );
      }
      else if ( isset( $xmlElement[ 'Height' ] ) )
      {
         $h = \intval( $xmlElement[ 'Height' ] );
      }
      else if ( isset( $xmlElement[ 'attributes' ][ 'height' ] ) )
      {
         $h = \intval( $xmlElement[ 'attributes' ][ 'height' ] );
      }
      else if ( isset( $xmlElement[ 'attributes' ][ 'Height' ] ) )
      {
         $h = \intval( $xmlElement[ 'attributes' ][ 'Height' ] );
      }
      else if ( isset( $xmlElement[ '@attributes' ][ 'height' ] ) )
      {
         $h = \intval( $xmlElement[ '@attributes' ][ 'height' ] );
      }
      else if ( isset( $xmlElement[ '@attributes' ][ 'Height' ] ) )
      {
         $h = \intval( $xmlElement[ '@attributes' ][ 'Height' ] );
      }

      if ( \is_null( $w ) || \is_null( $h ) )
      {
         return false;
      }

      return new Size( $w, $h );

   }

   /**
    * Extrahiert aus dem übergebenen Wert eine {@see \Niirrty\Drawing\Size}
    * Instanz, die im Parameter $size zurück gegeben wird, wenn die
    * Methode selbst (bool)TRUE zurück gibt.
    *
    * @param  mixed            $value int|double|array|string|\SimpleXMLElement|image-resource
    * @param  \Niirrty\Drawing\Size $size  Resultierende {@see \Niirrty\Drawing\Size} Instanz
    * @return boolean
    */
   public static function TryParse( $value, ?Size &$size = null ) : bool
   {

      $size = null;

      if ( \is_int( $value ) )
      {
         $size = new Size( $value, $value );
         return true;
      }

      if ( \is_double( $value ) )
      {
         $size = new Size( (int)$value, (int)$value );
         return true;
      }

      if ( \is_array( $value ) )
      {
         return ( false !== ( $size = self::FromArray( $value ) ) );
      }

      if ( \is_string( $value ) )
      {
         return ( false !== ( $size = self::FromString( $value ) ) );
      }

      if ( $value instanceof \SimpleXMLElement )
      {
         return ( false !== ( $size = self::FromXml( $value ) ) );
      }

      if ( \is_resource( $value ) && \Niirrty\strContains( \get_resource_type( $value ), 'image', true ) )
      {
         try
         {
            $size = new Size( \imagesx( $value ), \imagesy( $value ) );
            return true;
         }
         catch ( \Throwable $ex )
         {
            $ex = null;
            return false;
         }
      }

      return false;

   }

   /**
    * Parses a value to a {@see \Niirrty\Drawing\Size} instance. If parsing fails, it returns boolean FALSE.
    *
    * @param  mixed $value
    * @return \Niirrty\Drawing\Size|false Or boolean FALSE
    */
   public static function Parse( $value )
   {

      $res = null;

      if ( ! self::TryParse( $value, $res ) )
      {
         return false;
      }

      return $res;

   }

   /**
    * @param  string $imageFile
    * @return \Niirrty\Drawing\Size
    * @throws \Niirrty\IO\FileNotFoundException
    * @throws \Niirrty\IO\IOException
    */
   public static function FromImageFile( $imageFile ) : Size
   {

      if ( ! \file_exists( $imageFile ) )
      {
         throw new FileNotFoundException( $imageFile );
      }

      try
      {
         $tmp = \getimagesize( $imageFile );
         return new Size( \intval( $tmp[ 0 ] ), \intval( $tmp[ 1 ] ) );
      }
      catch ( \Throwable $ex )
      {
         throw new IOException( $imageFile, $ex->getMessage() );
      }

   }

   # </editor-fold>


}

