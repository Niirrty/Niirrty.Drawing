<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Tags
 * @since      2017-11-02
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing\Image\Exif\Tags;


use Niirrty\Web\Url;


/**
 * @since v0.1
 */
class Copyright
{

   
   # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The copyright text.
    *
    * @var string
    */
   public $Notice; # <-- 'Copyright' oder 'Copyright Notice' oder 'Rights'

   /**
    * Optional copyright URL.
    *
    * @var \Niirrty\Web\Url oder NULL
    */
   public $InfoUrl; # <-- 'URL'

   /**
    * Optional usage terms of the image.
    *
    * @var string|null
    */
   public $UsageTerms; # <-- 'Usage Terms'

   /**
    * Copyright Flag
    *
    * @var bool
    */
   public $Flag; # <-- 'Copyright Flag'

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param  array $data Keys are 'Copyright', 'Copyright Notice', 'Rights', 'URL', 'Usage Terms', 'Copyright Flag'
    */
   public function __construct( array $data = [] )
   {

      $this->reinitFromArray( $data );

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Keys are 'Copyright' or 'Copyright Notice' or 'Rights') optional 'URL', 'Usage Terms'
    * and 'Copyright Flag'
    *
    * @param array $array
    */
   public final function reinitFromArray( array $array )
   {

      $this->Notice = isset( $array[ 'Copyright' ] )
         ? $array[ 'Copyright' ]
         : ( isset( $array[ 'Copyright Notice' ] )
            ? $array[ 'Copyright Notice' ]
            : ( isset( $array[ 'Rights' ] )
               ? $array[ 'Rights' ]
               : null
            )
         );

      $this->InfoUrl    = isset( $array[ 'URL' ] ) ? Url::Parse( $array[ 'URL' ] ) : null;
      $this->UsageTerms = isset( $array[ 'Usage Terms' ] ) ? $array[ 'Usage Terms' ] : null;
      $this->Flag       = isset( $array[ 'Copyright Flag' ] ) ? ( 'True' === $array[ 'Copyright Flag' ] ) : false;

   }

   /**
    * Keys: 'Creator', 'Artist', 'By-line'
    *
    * @param array $array
    */
   public final function addToArray( array &$array )
   {

      $array[ 'Copyright' ]        = $this->Notice;
      $array[ 'Copyright Notice' ] = $this->Notice;
      $array[ 'Rights' ]           = $this->Notice;
      
      if ( ! \is_null( $this->InfoUrl ) && ( $this->InfoUrl instanceof Url ) )
      {
         $array[ 'URL' ] = (string) $this->InfoUrl;
      }
      
      if ( ! \is_null( $this->UsageTerms ) && '' != $this->UsageTerms )
      {
         $array[ 'Usage Terms' ] = $this->UsageTerms;
      }
      
      if ( $this->Flag )
      {
         $array[ 'Copyright Flag' ] = 'True';
      }

   }

   # </editor-fold>


}

