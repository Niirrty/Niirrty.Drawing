<?php
/**
 * This file defines the {@see \Niirrty\Drawing\Image\Exif\Tags\Workflow} class.
 *
 * @author         Curt Durban <curt.durban@gmail.com>
 * @category       AURORA PHP-Framework
 * @copyright  (c) 2015-2016, Curt Durban
 * @package        Drawing
 * @subpackage     Image\Exif\Tags
 * @since          2015-11-05 18:40
 * @version        0.2
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing\Image\Exif\Tags;


/**
 * @since v0.1
 */
class Workflow
{


   # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Instructions
    *
    * @var string|null
    */
   public $Instructions; # <-- "Instructions" oder "Special Instructions"

   /**
    * Transmission Reference.
    *
    * @var string|null
    */
   public $TransmissionReference; # <-- "Transmission Reference" oder "Original Transmission Reference"

   /**
    * Credit
    *
    * @var string|null
    */
   public $Credit;  # <-- "Credit"

   /**
    * Source
    *
    * @var string|null
    */
   public $Source;  # <-- "Source"

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * @param  array $data 'Instructions',
    *                     'Special Instructions', 'Transmission Reference', 'Original Transmission Reference',
    *                     'Credit', 'Source'
    */
   public function __construct( array $data = array() )
   {

      $this->reinitFromArray( $data );

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * 'Instructions', 'Special Instructions',
    * 'Transmission Reference', 'Original Transmission Reference',
    * 'Credit', 'Source'
    *
    * @param array $data
    */
   public final function reinitFromArray( array $data )
   {

      $this->Instructions = isset( $data[ 'Instructions' ] )
         ? $data[ 'Instructions' ]
         : ( isset( $data[ 'Special Instructions' ] )
            ? $data[ 'Special Instructions' ]
            : null
         );

      $this->TransmissionReference = isset( $data[ 'Transmission Reference' ] )
         ? $data[ 'Transmission Reference' ]
         : ( isset( $data[ 'Original Transmission Reference' ] )
            ? $data[ 'Original Transmission Reference' ]
            : null
         );

      $this->Credit = isset( $data[ 'Credit' ] ) ? $data[ 'Credit' ] : null;
      $this->Source = isset( $data[ 'Source' ] ) ? $data[ 'Source' ] : null;

   }

   /**
    * 'Instructions',
    * 'Special Instructions', 'Transmission Reference', 'Original Transmission Reference',
    * 'Credit', 'Source'
    *
    * @param array $array
    */
   public final function addToArray( array &$array )
   {

      if ( ! \is_null( $this->Instructions ) && ( '' != $this->Instructions ) )
      {
         $array[ 'Instructions' ] = $this->Instructions;
         $array[ 'Special Instructions' ] = $this->Instructions;
      }

      if ( ! \is_null( $this->TransmissionReference ) && ( '' != $this->TransmissionReference ) )
      {
         $array[ 'Transmission Reference' ] = $this->TransmissionReference;
         $array[ 'Original Transmission Reference' ] = $this->TransmissionReference;
      }

      if ( ! \is_null( $this->Credit ) && ( '' != $this->Credit ) )
      {
         $array[ 'Credit' ] = $this->Credit;
      }

      if ( ! \is_null( $this->Source ) && ( '' != $this->Source ) )
      {
         $array[ 'Credit' ] = $this->Source;
      }

   }

   # </editor-fold>


}

