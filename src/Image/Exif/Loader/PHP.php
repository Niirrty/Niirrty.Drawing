<?php /** @noinspection PhpUndefinedFieldInspection */
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Loader
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing\Image\Exif\Loader;


use Exception;
use Niirrty\Drawing\Image\Exif\ImageInfo;
use Niirrty\IO\File;
use Niirrty\IO\FileAccessException;
use Niirrty\IO\IOException;
use Niirrty\Web\Url;
use Niirrty\XmlAttributeHelper;
use SimpleXMLElement;
use SimpleXMLIterator;
use function count;
use function doubleval;
use function exif_read_data;
use function explode;
use function fclose;
use function feof;
use function file_exists;
use function fopen;
use function fread;
use function intval;
use function is_resource;
use function join;
use function Niirrty\strEndswith;
use function preg_replace;
use function round;
use function simplexml_load_string;
use function str_replace;
use function strpos;
use function strval;
use function substr;
use function trim;


/**
 * Uses only PHP (no required extension or libraries) to getting the exif data.
 *
 * @since v0.1.0
 */
class PHP implements ILoader
{


    # <editor-fold desc="= = =   P R I V A T E   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * Extracts a string that defines internally XML, defining some XMP meta data from inside the image file.
     *
     * @param string  $imageFile The path of the image file, that defines maybe some XMP meta data.
     * @param integer $chunkSize The reading chunk size (default=2048)
     *
     * @return string|null Returns the resulting XML string, or null if nothing was found
     * @throws FileAccessException If reading fails
     */
    private function getXmpString( string $imageFile, int $chunkSize = 2048 ): ?string
    {

        // Define required variables
        $buffer = null;
        $fp = null;
        $start = false;

        // Open the image file for reading
        try
        {
            $fp = fopen( $imageFile, 'rb' );
        }
        catch ( Exception $ex )
        {
            // Open image file for reading fails
            throw FileAccessException::Read(
                $imageFile,
                'Could not read xmp metadata from image file. ' . $ex->getMessage()
            );
        }
        if ( !is_resource( $fp ) )
        {
            // Open image file for reading fails
            throw FileAccessException::Read(
                $imageFile,
                'Could not read xmp metadata from image file. '
            );
        }

        // read all content chunks
        while ( !feof( $fp ) )
        {

            // Read the next unread chunk
            $chunk = fread( $fp, $chunkSize );

            if ( !$start )
            {
                // XMP meta data start point was not found in the readed chunks before

                if ( false !== ( $pos = strpos( $chunk, '<x:xmpmeta' ) ) )
                {
                    // But this chunk defines the required start point
                    $start = true;
                    // Remember the current XMP meta data in a buffer
                    $buffer = substr( $chunk, $pos );
                    if ( false !== ( $pos = strpos( $buffer, '</x:xmpmeta>' ) ) )
                    {
                        // The required XMP meta data endpoint is a part of the current chunk. Extract it.
                        $buffer = substr( $buffer, 0, $pos + 12 );
                        // We are done with reading here
                        break;
                    }
                }

                // No XMP meta data inside this chunk, go to next chung
                continue;

            }

            // There was already a XMP meta data startpoint found. Finding the end of this data part.

            if ( false !== ( $pos = strpos( $chunk, '</x:xmpmeta>' ) ) )
            {
                // The required XMP meta data endpoint is a part of the current chunk. Extract it.
                $buffer .= substr( $chunk, 0, $pos + 12 );
                // We are done with reading here
                break;
            }

            // No XMP meta data end point inside this chunk, go to next chunk and ad the current chunk to buffer
            $buffer .= $chunk;

        }

        // Closing the file pointer
        fclose( $fp );

        if ( empty( $buffer ) )
        {
            // We could not found some XMP meta data inside the image file, so we are done here.
            return null;
        }

        // Normalize LINEBREAKS to LF (\n)
        $buffer = str_replace( [ "\r\n", "\r" ], [ "\n", "\n" ], $buffer );

        // Remove unwantet <x:xmpmeta… elements
        $buffer = trim(
            preg_replace(
                '~</?x:xmpmeta[^>]*>~', '',
                $buffer
            )
        );

        // remove all xmlns:* attributes
        $data = preg_replace( '~xmlns:[a-zA-Z0-9]+="[^"]+"~', '', $buffer );

        // Retun not required element + attribute prefixes and return it.
        return str_replace(
            [ 'x:', 'rdf:', 'dc:', 'xmp:', 'photoshop:', 'xmpMM:', 'xmpRights:',
              'crs:', 'xml:', 'stEvt:', 'stRef:', 'Iptc4xmpCore:' ],
            '',
            $data
        );

    }

    /**
     * @param string $imageFile The path of the image file, that defines maybe some XMP meta data.
     *
     * @return array Returns the resulting associative data array. (empty if no data was found)
     */
    private function extractXmpData( $imageFile )
    {

        // Init the required variables
        $result = [];
        $xmpString = null;
        $values = [];

        try
        {
            // Get the XMP meta data XML string from current defined image file
            $xmpString = $this->getXmpString( $imageFile );
        }
        catch ( Exception $ex )
        {
            // On error return a empty array
            return $result;
        }


        if ( empty( $xmpString ) )
        {
            // Could not found some XMP meta data. Return a empty array
            return $result;
        }

        try
        {

            // Loading the XML XMP string to a SimpleXMLElement instance.
            $xml = simplexml_load_string( $xmpString );

            if ( !isset( $xml->Description ) )
            {
                return $result;
            }

            $element = $xml->Description;
            if ( !( $element instanceof SimpleXMLElement ) )
            {
                $element = $element[ 0 ];
            }

            // format
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'format' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Format' ] = $values[ 0 ];
            }

            // Lens ID
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'auLens' );
            if ( false === $values[ 0 ] )
            {
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'auLensID' );
            }
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Lens ID' ] = $values[ 0 ];
            }

            // Lens Info
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'auLensInfo' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Lens Info' ] = $values[ 0 ];
            }

            // Modify Date
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'ModifyDate' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Modify Date' ] = $values[ 0 ];
            }

            // Create Date
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'CreateDate' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Create Date' ] = $values[ 0 ];
            }

            // Label
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Label' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Label' ] = $values[ 0 ];
            }

            // Date Created
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'DateCreated' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Date Created' ] = $values[ 0 ];
            }

            // Headline
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Headline' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Headline' ] = $values[ 0 ];
            }

            // Authors Position
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'AuthorsPosition' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Authors Position' ] = $values[ 0 ];
            }

            // Caption Writer
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'CaptionWriter' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Caption Writer' ] = $values[ 0 ];
            }

            // Category
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Category' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Category' ] = $values[ 0 ];
            }

            // City
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'City' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'City' ] = $values[ 0 ];
            }

            // State
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'State' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'State' ] = $values[ 0 ];
            }

            // Country
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Country' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Country' ] = $values[ 0 ];
            }

            // Country
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'TransmissionReference' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Transmission Reference' ] = $values[ 0 ];
            }

            // Instructions
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Instructions' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Instructions' ] = $values[ 0 ];
            }

            // Credit
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Credit' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Credit' ] = $values[ 0 ];
            }

            // Source
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Source' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Source' ] = $values[ 0 ];
            }

            // Intellectual Genre
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'IntellectualGenre' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Intellectual Genre' ] = $values[ 0 ];
            }

            // Location
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'Location' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Location' ] = $values[ 0 ];
            }

            // Country Code
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'CountryCode' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'Country Code' ] = $values[ 0 ];
            }

            // URL
            $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $element, 'WebStatement' );
            if ( false !== $values[ 0 ] )
            {
                $result[ 'URL' ] = $values[ 0 ];
            }

            if ( isset( $element->title->Alt->li ) )
            {
                $result[ 'Title' ] = (string) $element->title->Alt->li;
            }
            if ( isset( $element->creator->Seq->li ) )
            {
                $result[ 'Creator' ] = (string) $element->creator->Seq->li;
            }
            if ( isset( $element->rights->Alt->li ) )
            {
                $result[ 'Rights' ] = (string) $element->rights->Alt->li;
            }

            if ( isset( $element->description->Alt->li ) )
            {
                $result[ 'Description' ] = (string) $element->description->Alt->li;
            }

            if ( isset( $element->subject->Bag->li ) )
            {
                $result[ 'Subject' ] = [];
                foreach ( $element->subject->Bag->li as $li )
                {
                    $result[ 'Subject' ][] = (string) $li;
                }
                $result[ 'Subject' ] = join( ', ', $result[ 'Subject' ] );
            }

            if ( isset( $element->SupplementalCategories->Bag->li ) )
            {
                $result[ 'Supplemental Categories' ] = [];
                foreach ( $element->subject->Bag->li as $li )
                {
                    $result[ 'Supplemental Categories' ][] = (string) $li;
                }
                $result[ 'Supplemental Categories' ] = join( ', ', $result[ 'Supplemental Categories' ] );
            }

            if ( isset( $element->CreatorContactInfo ) )
            {
                $subElement = $element->CreatorContactInfo;
                if ( $subElement instanceof SimpleXMLIterator )
                {
                    $subElement = $subElement[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiAdrExtadr' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Address' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiAdrCity' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator City' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiAdrRegion' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Region' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiAdrPcode' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Postal Code' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiAdrCtry' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Country' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiTelWork' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Work Telephone' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiEmailWork' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Work Email' ] = $values[ 0 ];
                }
                $values[ 0 ] = XmlAttributeHelper::GetAttributeValue( $subElement, 'CiUrlWork' );
                if ( false !== $values[ 0 ] )
                {
                    $result[ 'Creator Work URL' ] = $values[ 0 ];
                }
            }

            // Usage Terms
            if ( isset( $element->UsageTerms->Alt->li ) )
            {
                $result[ 'Usage Terms' ] = (string) $element->UsageTerms->Alt->li;
            }

            return $result;

        }
        catch ( Exception $ex )
        {
            return $result;
        }

    }

    private function readLatitude( array $exif )
    {

        $lat_ref = $exif[ 'GPSLatitudeRef' ];
        [ $num, $dec ] = explode( '/', $exif[ 'GPSLatitude' ][ 0 ] );

        if ( $dec <= 0 )
        {
            $deg = doubleval( $num );
        }
        else
        {
            $deg = intval( $num ) / intval( $dec );
        }

        [ $num, $dec ] = explode( '/', $exif[ 'GPSLatitude' ][ 1 ] );
        if ( $dec <= 0 )
        {
            $min = doubleval( $num );
        }
        else
        {
            $min = intval( $num ) / intval( $dec );
        }

        [ $num, $dec ] = explode( '/', $exif[ 'GPSLatitude' ][ 2 ] );
        if ( $dec <= 0 )
        {
            $sec = doubleval( $num );
        }
        else
        {
            $sec = doubleval( $num ) / doubleval( $dec );
        }

        return "$deg deg $min\" $sec' $lat_ref";

    }

    private function readLongitude( array $exif )
    {

        if ( !isset( $exif[ 'GPSLongitude' ] ) )
        {
            return null;
        }

        $lat_ref = $exif[ 'GPSLongitudeRef' ];
        [ $num, $dec ] = explode( '/', $exif[ 'GPSLongitude' ][ 0 ] );
        if ( $dec <= 0 )
        {
            $deg = doubleval( $num );
        }
        else
        {
            $deg = intval( $num ) / intval( $dec );
        }

        [ $num, $dec ] = explode( '/', $exif[ 'GPSLongitude' ][ 1 ] );
        if ( $dec <= 0 )
        {
            $min = doubleval( $num );
        }
        else
        {
            $min = intval( $num ) / intval( $dec );
        }

        [ $num, $dec ] = explode( '/', $exif[ 'GPSLongitude' ][ 2 ] );
        if ( $dec <= 0 )
        {
            $sec = doubleval( $num );
        }
        else
        {
            $sec = doubleval( $num ) / doubleval( $dec );
        }

        return "$deg deg $min\" $sec' $lat_ref";

    }

    private function extractExifData( $imageFile )
    {

        $result = [];
        try
        {
            $exif = exif_read_data( $imageFile, 'ANY_TAG', false );
            # <editor-fold defaultstate="collapsed" desc="Make + Model">
            if ( isset( $exif[ 'Make' ] ) )
            {
                $result[ 'Make' ] = $exif[ 'Make' ];
            }
            if ( isset( $exif[ 'Model' ] ) )
            {
                $result[ 'Camera Model Name' ] = $exif[ 'Model' ];
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="Artist + Copyright">
            if ( isset( $exif[ 'Artist' ] ) )
            {
                $result[ 'Artist' ] = $exif[ 'Artist' ];
            }
            if ( isset( $exif[ 'Copyright' ] ) )
            {
                $result[ 'Copyright' ] = $exif[ 'Copyright' ];
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="ExposureTime">
            if ( isset( $exif[ 'ExposureTime' ] ) )
            {
                $result[ 'Exposure Time' ] = $exif[ 'ExposureTime' ];
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="FNumber">
            if ( isset( $exif[ 'FNumber' ] ) )
            {
                if ( strEndswith( $exif[ 'FNumber' ], '/1' ) )
                {
                    $result[ 'F Number' ] = substr( $exif[ 'FNumber' ], 0, -2 );
                }
                else
                {
                    $tmp = explode( '/', $exif[ 'FNumber' ], 2 );
                    if ( count( $tmp ) != 2 )
                    {
                        $result[ 'F Number' ] = $exif[ 'FNumber' ];
                    }
                    else
                    {
                        $result[ 'F Number' ] = strval( round( doubleval( $tmp[ 0 ] ) / doubleval( $tmp[ 1 ] ),
                                                                 2 ) );
                    }
                }
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="ISO">
            if ( isset( $exif[ 'ISOSpeedRatings' ] ) )
            {
                $result[ 'ISO' ] = $exif[ 'ISOSpeedRatings' ];
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="DateTimeOriginal + DateTimeDigitized">
            if ( isset( $exif[ 'DateTimeOriginal' ] ) )
            {
                $result[ 'Date/Time Original' ] = $exif[ 'DateTimeOriginal' ];
            }
            if ( isset( $exif[ 'DateTimeDigitized' ] ) )
            {
                $result[ 'Digital Creation Date/Time' ] = $exif[ 'DateTimeDigitized' ];
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="ApertureValue">
            if ( isset( $exif[ 'ApertureValue' ] ) )
            {
                if ( strEndswith( $exif[ 'ApertureValue' ], '/1' ) )
                {
                    $result[ 'Aperture Value' ] = substr( $exif[ 'ApertureValue' ], 0, -2 );
                }
                else
                {
                    $tmp = explode( '/', $exif[ 'ApertureValue' ], 2 );
                    if ( count( $tmp ) != 2 )
                    {
                        $result[ 'Aperture Value' ] = $exif[ 'ApertureValue' ];
                    }
                    else
                    {
                        $result[ 'Aperture Value' ] = strval(
                            round( doubleval( $tmp[ 0 ] ) / doubleval( $tmp[ 1 ] ), 2 ) );
                    }
                }
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="FocalLength + Lens ID">
            if ( isset( $exif[ 'FocalLength' ] ) )
            {
                $result[ 'Focal Length' ] = $exif[ 'FocalLength' ];
            }
            if ( isset( $exif[ 'UndefinedTag:0xA434' ] ) )
            {
                $result[ 'Lens ID' ] = $exif[ 'UndefinedTag:0xA434' ];
            }
            # </editor-fold>
            # <editor-fold defaultstate="collapsed" desc="+Copyright">
            if ( !isset( $result[ 'Copyright' ] )
                 && isset( $exif[ 'COMPUTED' ] )
                 && isset( $exif[ 'COMPUTED' ][ 'Copyright' ] ) )
            {
                $result[ 'Copyright' ] = $exif[ 'COMPUTED' ][ 'Copyright' ];
            }
            # </editor-fold>
            if ( isset( $exif[ 'ImageDescription' ] ) )
            {
                $result[ 'Image Description' ] = $exif[ 'ImageDescription' ];
            }
            # <editor-fold defaultstate="collapsed" desc="GPS">
            if ( isset( $exif[ 'GPSLatitudeRef' ] ) )
            {
                $result[ 'GPS Latitude Ref' ] = $exif[ 'GPSLatitudeRef' ];
            }
            if ( isset( $exif[ 'GPSLongitudeRef' ] ) )
            {
                $result[ 'GPS Longitude Ref' ] = $exif[ 'GPSLongitudeRef' ];
            }
            if ( isset( $exif[ 'GPSLatitude' ] ) )
            {
                $result[ 'GPS Latitude' ] = $this->readLatitude( $exif );
            }
            if ( isset( $exif[ 'GPSLongitude' ] ) )
            {
                $result[ 'GPS Longitude' ] = $this->readLongitude( $exif );
            }
            if ( isset( $exif[ 'GPSLatitude' ] ) && isset( $exif[ 'GPSLongitude' ] ) )
            {
                $result[ 'GPS Position' ] = $result[ 'GPS Latitude' ] . ', ' . $result[ 'GPS Longitude' ];
            }
            # </editor-fold>
        }
        catch ( Exception $ex )
        {
            $ex = null;
        }

        return $result;

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     */
    public function __construct() { }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Only a dummy to implement the interface.
     *
     * @param array $configData
     */
    public function configure( array $configData ) { }

    /**
     * Returns if the loader is configured correctly.
     *
     * @return bool
     */
    public function isConfigured() { return true; }

    /**
     * Loads the exif data from defined image file.
     *
     * @param string|Url|File $imageFile
     *
     * @return ImageInfo|null
     * @throws IOException
     */
    function load( $imageFile ): ?ImageInfo
    {

        if ( !file_exists( $imageFile ) )
        {
            // Missing image file => do nothing
            return null;
        }

        $xmpArray = $this->extractXmpData( $imageFile );
        $exifArray = $this->extractExifData( $imageFile );

        foreach ( $exifArray as $k => $v )
        {
            if ( !isset( $xmpArray[ $k ] ) )
            {
                $xmpArray[ $k ] = $v;
            }
        }

        if ( count( $xmpArray ) < 1 )
        {
            return null;
        }

        return new ImageInfo( $xmpArray, $imageFile );

    }


    # </editor-fold>


}

