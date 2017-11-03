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


/**
 * Defines the content align of a element.
 *
 * @since      v0.1
 */
class ContentAlign
{

   
   # <editor-fold desc="= = =   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Bottom right aligned
    */
   const BOTTOM_RIGHT = 0;

   /**
    * Bottom center aligned
    */
   const BOTTOM = 1;

   /**
    * Bottom left aligned
    */
   const BOTTOM_LEFT = 2;

   /**
    * Middle right aligned
    */
   const MIDDLE_RIGHT = 3;

   /**
    * Middle center aligned
    */
   const MIDDLE = 4;

   /**
    * Middle left aligned
    */
   const MIDDLE_LEFT = 5;

   /**
    * Top right aligned
    */
   const TOP_RIGHT = 6;

   /**
    * Top center aligned
    */
   const TOP = 7;

   /**
    * Top left aligned
    */
   const TOP_LEFT = 8;

   # </editor-fold>

   
   # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The alignment value (one of the {@see \Drawing\ContentAlign} class constants).
    *
    * @var integer
    */
   public $Value;

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Inits a new instance.
    *
    * @param integer $value One of the {@see \Drawing\ContentAlign}::* class constants
    */
   public function __construct( int $value = self::BOTTOM_RIGHT )
   {
      if ( ! static::isValidValue( $value ) )
      {
         if ( ! \is_string( $value ) )
         {
            $this->Value = static::BOTTOM_RIGHT;
         }
         elseif ( \preg_match( '~^[0-8]$~', $value ) )
         {
            $this->Value = \intval( $value );
         }
         else
         {
            $this->Value = static::BOTTOM_RIGHT;
         }
      }
      else
      {
         $this->Value = $value;
      }
   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   public function __toString()
   {
      return \strval( $this->Value );
   }

   /**
    * Returns the associated \Imagick::GRAVITY_* constant value.
    *
    * @return integer
    */
   public function toGravity()
   {

      if ( class_exists( '\\Imagick' ) )
      {

         switch ( $this->Value )
         {

            case self::TOP_LEFT:
               return \Imagick::GRAVITY_NORTHWEST;

            case self::TOP:
               return \Imagick::GRAVITY_NORTH;

            case self::TOP_RIGHT:
               return \Imagick::GRAVITY_NORTHEAST;

            case self::MIDDLE_LEFT:
               return \Imagick::GRAVITY_WEST;

            case self::MIDDLE:
               return \Imagick::GRAVITY_CENTER;

            case self::MIDDLE_RIGHT:
               return \Imagick::GRAVITY_EAST;

            case self::BOTTOM_LEFT:
               return \Imagick::GRAVITY_SOUTHWEST;

            case self::BOTTOM:
               return \Imagick::GRAVITY_SOUTH;

            default:
               return \Imagick::GRAVITY_SOUTHEAST;

         }

      }

      return 5; // Is the same than \Imagick::GRAVITY_CENTER

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P R I V A T E   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   private static function isValidValue( $value ) : bool
   {

      if ( ! \is_int( $value ) )
      {
         return false;
      }

      return $value > -1 && $value < 9;

   }

   # </editor-fold>


}

