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


use Niirrty\TypeTool;


/**
 * @since      v0.1
 */
class ColorGD extends Color
{


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param string|int|array $colorDefinition
    * @param int              $opacity
    */
   public function __construct( $colorDefinition, int $opacity = 100 )
   {

      parent::__construct( '#000000', $opacity );

      if ( TypeTool::IsInteger( $colorDefinition ) )
      {
         $this->setGdValue( $colorDefinition );
      }
      else if ( \is_array( $colorDefinition ) )
      {
         $this->setARGB( $colorDefinition );
         $this->data[ 'gd' ] = $this->createGdValue();
      }
      else
      {
         $this->setWebColor( $colorDefinition );
         $this->data[ 'gd' ] = $this->createGdValue();
      }

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * @return mixed
    */
   public final function getGdValue()
   {

      return $this->data[ 'gd' ];

   }

   /**
    * @param $gdValue
    */
   public final function setGdValue( $gdValue )
   {

      $this->data[ 'gd' ] = $gdValue;

      if ( $gdValue > 16777215 )
      {
         $this->data[ 'alpha' ] = ($gdValue >> 24) & 0xFF;
         $this->data[ 'red' ] = ($gdValue >> 16) & 0xFF;
         $this->data[ 'green' ] = ($gdValue >> 8) & 0xFF;
         $this->data[ 'blue' ] = $gdValue & 0xFF;
         if ( $this->data[ 'alpha' ] == 0 )
         {
            $this->data['opacity'] = 0;
         }
         else if ( $this->data[ 'alpha' ] == 127 )
         {
            $this->data['opacity'] = 100;
         }
         else
         {
            $this->data['opacity'] = \intval( \floor( ( 100 * $this->data[ 'alpha' ] ) / 127 ) );
         }
         return;
      }

      $this->data[ 'alpha' ]   = 0;
      $this->data[ 'red' ]     = ( $gdValue >> 16 ) & 0xFF;
      $this->data[ 'green' ]   = ( $gdValue >> 8 ) & 0xFF;
      $this->data[ 'blue' ]    = $gdValue & 0xFF;
      $this->data[ 'opacity' ] = 100;

   }

   /**
    * @param $name
    * @return array|bool|int|mixed|string
    */
   public function __get( $name )
   {

      if ( false !== ( $value = $this->get( $name ) ) )
      {
         return $value;
      }

      switch ( \strtolower( $name ) )
      {

         case 'gdvalue':
         case 'gdcolor':
            return $this->data[ 'gd' ];

         default:
            return false;

      }

   }

   /**
    * @param $name
    * @param $value
    */
   public function __set( $name, $value )
   {

      $resetGD = true;

      switch ( \strtolower( $name ) )
      {

         case 'gdvalue':
         case 'gdcolor':
            $this->setGdValue( $value );
            $resetGD = false;
            break;

         case 'rgb':
            $this->setRGB($value);
            break;

         case 'argb':
            $this->setARGB($value);
            break;

         case 'rgba':
            $this->setRGBA($value);
            break;

         case 'r':
         case 'red':
            $this->setR($value);
            break;

         case 'g':
         case 'green':
            $this->setG($value);
            break;

         case 'b':
         case 'blue':
            $this->setB($value);
            break;

         case 'hex':
         case 'hexadecimal':
         case 'value':
         case 'webcolor':
            $this->setWebColor($value);
            break;

         case 'alpha':
         case 'a':
            $this->setAlpha($value);
            break;

         case 'o':
         case 'opacity':
            $this->setOpacity($value);
            break;

      }

      if ( $resetGD )
      {

         if ( \is_null( $this->data[ 'alpha' ] ) )
         {
            $this->data[ 'gd' ] = \hexdec(
               \str_pad( \dechex( $this->data[ 'red' ] ), 2, 0, \STR_PAD_LEFT )
               . \str_pad( \dechex( $this->data[ 'green' ] ), 2, 0, \STR_PAD_LEFT )
               . \str_pad( \dechex( $this->data[ 'blue' ] ), 2, 0, \STR_PAD_LEFT )
            );
         }
         else
         {
            $this->data[ 'gd' ] = \bindec(
               \decbin(  $this->data[ 'alpha' ]  )
               . \decbin(  \hexdec( \substr( $this->data[ 'hex' ], 1 ) ) )
            );
         }

      }

   }

   /**
    * @return number
    */
   public function createGdValue()
   {
      return $this->data[ 'gd' ];
   }

   # </editor-fold>


}

