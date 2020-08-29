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
     * @param array $data  'Instructions',
     *                     'Special Instructions', 'Transmission Reference', 'Original Transmission Reference',
     *                     'Credit', 'Source'
     */
    public function __construct( array $data = [] )
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

        if ( !is_null( $this->Instructions ) && ( '' != $this->Instructions ) )
        {
            $array[ 'Instructions' ] = $this->Instructions;
            $array[ 'Special Instructions' ] = $this->Instructions;
        }

        if ( !is_null( $this->TransmissionReference ) && ( '' != $this->TransmissionReference ) )
        {
            $array[ 'Transmission Reference' ] = $this->TransmissionReference;
            $array[ 'Original Transmission Reference' ] = $this->TransmissionReference;
        }

        if ( !is_null( $this->Credit ) && ( '' != $this->Credit ) )
        {
            $array[ 'Credit' ] = $this->Credit;
        }

        if ( !is_null( $this->Source ) && ( '' != $this->Source ) )
        {
            $array[ 'Credit' ] = $this->Source;
        }

    }


    # </editor-fold>


}

