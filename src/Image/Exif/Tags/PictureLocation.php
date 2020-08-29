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


use function is_null;


/**
 * @since v0.1
 */
class PictureLocation
{


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * Region.
     *
     * @var string|null
     */
    public $Region; # <-- "Location" oder "Sub-location"

    /**
     * "State" or "Province-State"
     *
     * @var string|null
     */
    public $State; # <-- "State" oder "Province-State"

    /**
     * "Country Code" or "Country-Primary Location Code"
     *
     * @var string|null
     */
    public $CountryCode; # <-- "Country Code" oder "Country-Primary Location Code"

    /**
     * City
     *
     * @var string|null
     */
    public $City; # <-- "City"

    /**
     * "Country" or "Country-Primary Location Name"
     *
     * @var string|null
     */
    public $Country; # <-- "Country" oder "Country-Primary Location Name"

    /**
     * Genre.
     *
     * @var string|null
     */
    public $Genre; # <-- "Intellectual Genre"

    /**
     * Scene
     *
     * @var string|null
     */
    public $Scene; # <-- "Scene"

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     *
     * @param array $data  'Location', 'Sub-location',
     *                     'State', 'Province-State', 'Country Code', 'Country-Primary Location Code', 'City',
     *                     'Country', 'Country-Primary Location Name', 'Intellectual Genre', 'Scene'
     */
    public function __construct( array $data = [] )
    {

        $this->reinitFromArray( $data );

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * 'Location', 'Sub-location', 'State',
     * 'Province-State', 'Country Code', 'Country-Primary Location Code',
     * 'City', 'Country', 'Country-Primary Location Name',
     * 'Intellectual Genre', 'Scene'
     *
     * @param array $data
     */
    public final function reinitFromArray( array $data )
    {

        $this->Region = isset( $data[ 'Location' ] )
            ? $data[ 'Location' ]
            : ( isset( $data[ 'Sub-location' ] )
                ? $data[ 'Sub-location' ]
                : null
            );

        $this->State = isset( $data[ 'State' ] )
            ? $data[ 'State' ]
            : ( isset( $data[ 'Province-State' ] )
                ? $data[ 'Province-State' ]
                : null
            );

        $this->CountryCode = isset( $data[ 'Country Code' ] )
            ? $data[ 'Country Code' ]
            : ( isset( $data[ 'Country-Primary Location Code' ] )
                ? $data[ 'Country-Primary Location Code' ]
                : null
            );

        $this->City = isset( $data[ 'Creator City' ] ) ? $data[ 'Creator City' ] : null;

        $this->Country = isset( $data[ 'Country' ] )
            ? $data[ 'Country' ]
            : ( isset( $data[ 'Country-Primary Location Name' ] )
                ? $data[ 'Country-Primary Location Name' ]
                : null
            );

        $this->Genre = isset( $data[ 'Intellectual Genre' ] ) ? $data[ 'Intellectual Genre' ] : null;
        $this->Scene = isset( $data[ 'Scene' ] ) ? $data[ 'Scene' ] : null;

    }

    /**
     * 'Location',
     * 'Sub-location', 'State', 'Province-State', 'Country Code',
     * 'Country-Primary Location Code', 'City', 'Country',
     * 'Country-Primary Location Name', 'Intellectual Genre', 'Scene'
     *
     * @param array $array
     */
    public final function addToArray( array &$array )
    {

        if ( !is_null( $this->Region ) && '' != $this->Region )
        {
            $array[ 'Location' ] = $this->Region;
            $array[ 'Sub-location' ] = $this->Region;
        }

        if ( !is_null( $this->State ) && '' != $this->State )
        {
            $array[ 'State' ] = $this->State;
            $array[ 'Province-State' ] = $this->State;
        }

        if ( !is_null( $this->CountryCode ) && '' != $this->CountryCode )
        {
            $array[ 'Country Code' ] = $this->CountryCode;
            $array[ 'Country-Primary Location Code' ] = $this->CountryCode;
        }

        if ( !is_null( $this->City ) && '' != $this->City )
        {
            $array[ 'City' ] = $this->City;
        }

        if ( !is_null( $this->Country ) && '' != $this->Country )
        {
            $array[ 'Country' ] = $this->Country;
            $array[ 'Country-Primary Location Name' ] = $this->Country;
        }

        if ( !is_null( $this->Genre ) && '' != $this->Genre )
        {
            $array[ 'Intellectual Genre' ] = $this->Genre;
        }

        if ( !is_null( $this->Scene ) && '' != $this->Scene )
        {
            $array[ 'Scene' ] = $this->Scene;
        }

    }


    # </editor-fold>


}

