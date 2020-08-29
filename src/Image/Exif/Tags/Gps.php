<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Tags
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing\Image\Exif\Tags;


use Niirrty\Gps\Coordinate;
use function is_null;


/**
 * @since v0.1
 */
class Gps
{


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * The GPS Coordinate, or NULL
     *
     * @var Coordinate|null
     */
    public $Coordinate; # <-- ("GPS Latitude" + "GPS Longitude") oder "GPS Position"

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     *
     * @param array $data usable Keys are 'GPS Latitude', 'GPS Longitude', 'GPS Position'
     */
    public function __construct( array $data = [] )
    {

        $this->reinitFromArray( $data );

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * known keys are 'GPS Latitude', 'GPS Longitude', 'GPS Position'
     *
     * @param array $data
     */
    public final function reinitFromArray( array $data )
    {

        $coord = null;

        if ( isset( $data[ 'GPS Latitude' ] ) && isset( $data[ 'GPS Longitude' ] ) )
        {
            if ( Coordinate::TryParse( $data[ 'GPS Latitude' ] . ', ' . $data[ 'GPS Longitude' ], $coord ) )
            {
                $this->Coordinate = $coord;

                return;
            }
        }

        if ( isset( $data[ 'GPS Position' ] ) )
        {
            if ( Coordinate::TryParse( $data[ 'GPS Position' ], $coord ) )
            {
                $this->Coordinate = $coord;

                return;
            }
        }

        $this->Coordinate = null;

    }

    /**
     * @param array $array Array zu dem die Werte hinzugefügt werden sollen.
     */
    public final function addToArray( array &$array )
    {

        if ( !is_null( $this->Coordinate )
             && ( $this->Coordinate instanceof Coordinate )
             && $this->Coordinate->isValid() )
        {

            $array[ 'GPS Latitude' ] = $this->Coordinate->Latitude->formatExifLike();
            $array[ 'GPS Longitude' ] = $this->Coordinate->Longitude->formatExifLike();
            $array[ 'GPS Position' ] = $this->Coordinate->formatExifLike();

            switch ( $this->Coordinate->Latitude->getDirection() )
            {
                case 'N':
                    $array[ 'GPS Latitude Ref' ] = 'North';
                    break;
                default:
                    $array[ 'GPS Latitude Ref' ] = 'South';
                    break;
            }
            switch ( $this->Coordinate->Longitude->getDirection() )
            {
                case 'E':
                    $array[ 'GPS Longitude Ref' ] = 'East';
                    break;
                default:
                    $array[ 'GPS Longitude Ref' ] = 'West';
                    break;
            }

        }

    }


    # </editor-fold>


}

