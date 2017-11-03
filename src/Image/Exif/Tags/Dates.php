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


use Niirrty\Date\DateTime;


/**
 * @since v0.1
 */
class Dates
{


   # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Date time of last image manipulation.
    *
    * @var \DateTime
    */
   public $LastModified; # <-- "Modify Date"

   /**
    * Date time of Date time of image creation or shot.
    *
    * @var \DateTime
    */
   public $Created;      # <-- "Create Date" oder "Date Created" oder "Date/Time Created" oder "Date/Time Original"

   /**
    * Date time of image digitalization.
    *
    * @var \DateTime
    */
   public $Digitized;  # <-- "Digital Creation Date/Time" oder "Digital Creation Date" + "Digital Creation Time"

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param  array $data Keys are 'Modify Date', 'Create Date', 'Date Created', 'Date/Time Created',
    *                     'Date/Time Original', 'Digital Creation Date/Time', 'Digital Creation Date',
    *                     'Digital Creation Time'
    */
   public function __construct( array $data = [] )
   {

      $this->reinitFromArray( $data );

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Keys: 'Modify Date', 'Create Date', 'Date Created', 'Date/Time Created', 'Date/Time Original',
    * 'Digital Creation Date/Time', 'Digital Creation Date', 'Digital Creation Time'
    *
    * @param array $data
    */
   public final function reinitFromArray( array $data )
   {

      $this->LastModified = isset( $data[ 'Modify Date' ] ) ? self::ParseDateTime( $data[ 'Modify Date' ] ) : null;
      $dt     = [];
      $dt[]   = isset( $data[ 'Create Date' ] ) ? self::ParseDateTime( $data[ 'Create Date' ] ) : null;
      $dt[]   = isset( $data[ 'Date Created' ] ) ? self::ParseDateTime( $data[ 'Date Created' ] ) : null;
      $dt[]   = isset( $data[ 'Date/Time Created' ] ) ? self::ParseDateTime( $data[ 'Date/Time Created' ] ) : null;
      $dt[]   = isset( $data[ 'Date/Time Original' ] ) ? self::ParseDateTime( $data[ 'Date/Time Original' ] ) : null;
      $oldest = null;

      for ( $i = 0; $i < 4; ++$i )
      {
         if ( ! ( $dt[ $i ] instanceof DateTime ) )
         {
            continue;
         }
         if ( ! ( $oldest instanceof DateTime  ) )
         {
            $oldest = $dt[ $i ];
            continue;
         }
         if ( $oldest->getTimestamp() > $dt[ $i ]->getTimestamp() )
         {
            $oldest = $dt[ $i ];
         }
      }

      $this->Created = $oldest;
      $dt   = [];
      $dt[] = isset( $data[ 'Digital Creation Date/Time' ] )
         ? self::ParseDateTime( $data[ 'Digital Creation Date/Time' ] )
         : null;
      $dt[] = ( isset( $data[ 'Digital Creation Date' ] ) && isset( $data[ 'Digital Creation Time' ] ) )
         ? self::ParseDateTime( $data[ 'Digital Creation Date' ] . ' ' . $data[ 'Digital Creation Time' ] )
         : null;
      $dt[] = $this->Created;
      $oldest = null;

      for ( $i = 0; $i < 3; ++$i )
      {
         if ( ! ( $dt[ $i ] instanceof DateTime ) )
         {
            continue;
         }
         if ( ! ( $oldest instanceof DateTime ) )
         {
            $oldest = $dt[ $i ];
            continue;
         }
         if ( $oldest->getTimestamp() > $dt[ $i ]->getTimestamp() )
         {
            $oldest = $dt[ $i ];
         }
      }

      $this->Digitized = $oldest;

      if ( \is_null( $this->LastModified ) )
      {
         if ( \is_null( $this->Created ) )
         {
            if ( \is_null( $this->Digitized ) )
            {
               return;
            }
            $this->Created      = $this->Digitized;
            $this->LastModified = $this->Digitized;
            return;
         }
         if ( \is_null( $this->Digitized ) )
         {
            $this->Digitized = $this->Created;
         }
         $this->LastModified = $this->Created;
      }
      else if ( \is_null( $this->Created ) )
      {
         if ( \is_null( $this->Digitized ) )
         {
            $this->Created = $this->LastModified;
         }
         $this->Created = $this->Digitized;
      }
      else if ( \is_null( $this->Digitized ) )
      {
         $this->Digitized = $this->Created;
      }

   }

   /**
    * Fügt alle aktuell gesetzten Elemente unter den Keys 'Modify Date', 'Create Date', 'Date Created',
    * 'Date/Time Created', 'Date/Time Original', 'Digital Creation Date/Time', 'Digital Creation Date',
    * 'Digital Creation Time' zum angegebenen Array hinzu.
    *
    * @param array $array Array zu dem die Werte hinzugefügt werden sollen.
    */
   public final function addToArray( array &$array )
   {

      if ( ! \is_null( $this->LastModified ) && ( $this->LastModified instanceof \DateTimeInterface ) )
      {
         $array[ 'Modify Date' ] = $this->LastModified->format( 'Y:m:d H:i:s' );
      }

      if ( ! \is_null( $this->Created ) && ( $this->Created instanceof \DateTimeInterface ) )
      {
         $array[ 'Create Date' ]        = $this->Created->format( 'Y:m:d H:i:s' );
         $array[ 'Date Created' ]       = $array[ 'Create Date' ];
         $array[ 'Date/Time Created' ]  = $array[ 'Create Date' ];
         $array[ 'Date/Time Original' ] = $array[ 'Create Date' ];
      }

      if ( ! \is_null( $this->Digitized ) && ( $this->Digitized instanceof \DateTimeInterface ) )
      {
         $array[ 'Digital Creation Date/Time' ] = $this->Digitized->format( 'Y:m:d H:i:s' );
         $array[ 'Digital Creation Date' ]      = $this->Digitized->format( 'Y:m:d' );
         $array[ 'Digital Creation Time' ]      = $this->Digitized->format( 'Y:m:d' );
      }

   }

   /**
    * Gets the oldest defined date.
    *
    * @return \Niirrty\Date\DateTime oder NULL
    */
   public final function getOldest()
   {

      $isLM = ( ! \is_null( $this->LastModified ) && ( $this->LastModified instanceof \DateTimeInterface ) );
      $isCR = ( ! \is_null( $this->Created )      && ( $this->Created instanceof \DateTimeInterface ) );
      $isDI = ( ! \is_null( $this->Digitized )    && ( $this->Digitized instanceof \DateTimeInterface ) );

      $array = [];

      if ( $isLM )
      {
         $array[] = $this->LastModified;
      }

      if ( $isDI )
      {
         $array[] = $this->Digitized;
      }

      if ( $isCR )
      {
         $array[] = $this->Created;
      }

      $stamp = \PHP_INT_MAX;

      $idx   = -1;
      for ( $i = 0; $i < \count( $array ); ++$i )
      {
         if ( $array[ $i ]->Timestamp >= $stamp )
         {
            continue;
         }
         $idx = $i;
         $stamp = $array[ $i ]->Timestamp;
      }

      if ( $idx < 0 )
      {
         return null;
      }

      return $array[ $idx ];

   }

   /**
    * Returns if a usable datetime value is currently defined.
    *
    * @return boolean
    */
   public final function hasValue()
   {

      return ( ! \is_null( $this->LastModified ) && ( $this->LastModified instanceof \DateTimeInterface ) )
          || ( ! \is_null( $this->Created )      && ( $this->Created      instanceof \DateTimeInterface ) )
          || ( ! \is_null( $this->Digitized )    && ( $this->Digitized    instanceof \DateTimeInterface ) );

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

   /**
    * Extracts a DateTime instance from a string datetime.
    *
    * @param  string $str
    * @return \DateTime
    */
   public static function ParseDateTime( $str )
   {
      
      if ( \is_null( $str ) || ! \is_string( $str ) || '' === $str )
      {
         return null;
      }

      $tmp = \explode( '.', $str );
      if ( \count( $tmp ) == 2 )
      {
         $str = \trim( $tmp[ 0 ] );
      }

      $tmp = \explode( ' ', $str, 2 );
      if ( \count( $tmp ) != 2 )
      {
         return null;
      }

      $tmp[ 0 ] = \str_replace( ':', '-', $tmp[ 0 ] );

      return DateTime::Parse( \join( ' ', $tmp ) );

   }

   # </editor-fold>


}

