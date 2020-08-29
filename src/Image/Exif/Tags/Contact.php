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


use Exception;
use Niirrty\Web\MailAddress;
use function count;
use function explode;
use function is_null;
use function join;
use function trim;


/**
 * @since v0.1
 */
class Contact
{


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * The Author/Artist/Creator of the image (Creator|Artist|By-line)
     *
     * @var string|null
     */
    public $Author; # <-- "Creator" or "Artist" or "By-line"

    /**
     * "By-line Title" or "Authors Position"
     *
     * @var string|null
     */
    public $JobTitle; # <-- "By-line Title" or "Authors Position"

    /**
     * Address line of author address (typically street + number) (Creator Address)
     *
     * @var string|null
     */
    public $Address; # <-- "Creator Address"

    /**
     * The creator city.
     *
     * @var string|null
     */
    public $City; # <-- "Creator City"

    /**
     * The creator region.
     *
     * @var string|null
     */
    public $Region; # <-- "Creator Region"

    /**
     * The creator Postal Code
     *
     * @var string|null
     */
    public $PostalCode; # <-- "Creator Postal Code"

    /**
     * The creator Country.
     *
     * @var string|null
     */
    public $Country; # <-- "Creator Country"

    /**
     * The creator Work Telephone.
     *
     * @var string|null
     */
    public $Telephone; # <-- "Creator Work Telephone"

    /**
     * The creator Work Email
     *
     * @var MailAddress|null
     */
    public $Email; # <-- "Creator Work Email"

    /**
     * The creator Work URLs. 0-n Urls
     *
     * @var array
     */
    public $Urls; # <-- "Creator Work URL" 0-n Urls

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     *
     * @param array $data  Initial Data. Usable keys are: 'Creator', 'Artist', 'By-line', 'Authors Position',
     *                     'By-line Title', 'Creator Address', 'Creator City', 'Creator Region', 'Creator Postal Code',
     *                     'Creator Country', 'Creator Work Telephone', 'Creator Work Email', 'Creator Work URL'
     */
    public function __construct( array $data = [] )
    {

        $this->reinitFromArray( $data );

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Re-init from defined array.
     *
     * Keys: 'Creator', 'Artist', 'By-line', 'Authors Position', 'By-line Title', 'Creator Address', 'Creator City',
     *       'Creator Region', 'Creator Postal Code', 'Creator Country', 'Creator Work Telephone', 'Creator Work Email',
     *       'Creator Work URL'
     *
     * @param array $data
     */
    public final function reinitFromArray( array $data )
    {

        $this->Author = isset( $data[ 'Creator' ] )
            ? $data[ 'Creator' ]
            : ( isset( $data[ 'Artist' ] )
                ? $data[ 'Artist' ]
                : ( isset( $data[ 'By-line' ] )
                    ? $data[ 'By-line' ]
                    : null
                )
            );

        if ( is_null( $this->Author ) )
        {
            $this->Author = isset( $data[ 'Owner Name' ] )
                ? $data[ 'Owner Name' ]
                : null;
        }

        $this->JobTitle = isset( $data[ 'Authors Position' ] )
            ? $data[ 'Authors Position' ]
            : ( isset( $data[ 'By-line Title' ] )
                ? $data[ 'By-line Title' ]
                : null
            );

        $this->Address = isset( $data[ 'Creator Address' ] ) ? $data[ 'Creator Address' ] : null;
        $this->City = isset( $data[ 'Creator City' ] ) ? $data[ 'Creator City' ] : null;
        $this->Region = isset( $data[ 'Creator Region' ] ) ? $data[ 'Creator Region' ] : null;
        $this->PostalCode = isset( $data[ 'Creator Postal Code' ] ) ? $data[ 'Creator Postal Code' ] : null;
        $this->Country = isset( $data[ 'Creator Country' ] ) ? $data[ 'Creator Country' ] : null;
        $this->Telephone = isset( $data[ 'Creator Work Telephone' ] ) ? $data[ 'Creator Work Telephone' ] : null;

        try
        {
            $this->Email = isset( $data[ 'Creator Work Email' ] )
                ? MailAddress::Parse( $data[ 'Creator Work Email' ] )
                : null;
        }
        catch ( Exception $ex )
        {
            $ex = null;
            $this->Email = null;
        }

        $this->Urls = isset( $data[ 'Creator Work URL' ] )
            ? explode( ' ', $data[ 'Creator Work URL' ] )
            : [];

        for ( $i = 0; $i < count( $this->Urls ); ++$i )
        {
            $this->Urls[ $i ] = trim( $this->Urls[ $i ], ' \t;.:' );
        }

    }

    /**
     * Adds all values, defined by keys 'Creator', 'Artist', 'By-line', 'Authors Position', 'By-line Title',
     * 'Creator Address', 'Creator City', 'Creator Region', 'Creator Postal Code', 'Creator Country',
     * 'Creator Work Telephone', 'Creator Work Email', 'Creator Work URL'.
     *
     * @param array $array
     */
    public final function addToArray( array &$array )
    {

        if ( !is_null( $this->Author ) && '' != $this->Author )
        {
            $array[ 'Creator' ] = $this->Author;
            $array[ 'Artist' ] = $this->Author;
            $array[ 'By-line' ] = $this->Author;
        }

        if ( !is_null( $this->JobTitle ) && '' != $this->JobTitle )
        {
            $array[ 'Authors Position' ] = $this->JobTitle;
            $array[ 'By-line Title' ] = $this->JobTitle;
        }

        if ( !is_null( $this->Address ) && '' != $this->Address )
        {
            $array[ 'Creator Address' ] = $this->Address;
        }

        if ( !is_null( $this->City ) && '' != $this->City )
        {
            $array[ 'Creator City' ] = $this->City;
        }

        if ( !is_null( $this->Region ) && '' != $this->Region )
        {
            $array[ 'Creator Region' ] = $this->Region;
        }

        if ( !is_null( $this->PostalCode ) && '' != $this->PostalCode )
        {
            $array[ 'Creator Postal Code' ] = $this->PostalCode;
        }

        if ( !is_null( $this->Country ) && '' != $this->Country )
        {
            $array[ 'Creator Country' ] = $this->Country;
        }

        if ( !is_null( $this->Telephone ) && '' != $this->Telephone )
        {
            $array[ 'Creator Work Telephone' ] = $this->Telephone;
        }

        if ( !is_null( $this->Email ) && ( $this->Email instanceof MailAddress ) )
        {
            $array[ 'Creator Work Email' ] = (string) $this->Email;
        }

        if ( count( $this->Urls ) > 0 )
        {
            $array[ 'Creator Work URL' ] = join( ' ', $this->Urls );
        }

    }


    # </editor-fold>


}

