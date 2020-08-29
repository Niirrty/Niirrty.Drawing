<?php /** @noinspection PhpUndefinedFieldInspection */
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing;


use Niirrty\NiirrtyException;
use Niirrty\Type;
use SimpleXMLElement;
use XMLWriter;
use function count;
use function intval;
use function is_array;
use function is_null;
use function is_string;
use function preg_match;
use function sprintf;


/**
 * This class defines a 2 dimensional point.
 *
 * @since      v1.0.0
 */
class Point
{


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * The X coordinate (horizontal)
     *
     * @var integer
     */
    public $x;

    /**
     * The Y coordinate (vertical)
     *
     * @var integer
     */
    public $y;

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     *
     * @param integer $x The X coordinate (horizontal)
     * @param integer $y The Y coordinate (vertical)
     */
    public function __construct( int $x = 0, int $y = 0 )
    {

        $this->x = $x;
        $this->y = $y;

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Returns if X and Y is 0.
     *
     * @return boolean
     */
    public final function isEmpty(): bool
    {

        return $this->x === 0 && $this->y === 0;

    }

    /**
     * Writes the current instance data as XML element to a XmlWriter.
     *
     * The resulting XML element looks like follow:
     *
     * <b>&lt;Point x="0" y="0"/&gt;</b>
     *
     * @param XMLWriter $w           The XmlWriter.
     * @param string     $elementName The name of the XML element to write (default='Point')
     *                                If no usable element name is defined, only the attributes are written!
     */
    public function writeXml( XMLWriter $w, string $elementName = 'Point' )
    {

        $writeElement = !empty( $elementName );

        if ( $writeElement )
        {
            $w->startElement( $elementName );
        }

        $this->writeXmlAttributes( $w );

        if ( $writeElement )
        {
            $w->endElement();
        }

    }

    /**
     * Write the current instance data as XML element attributes to defined XmlWriter.
     *
     * @param XMLWriter $w The XmlWriter.
     */
    public function writeXmlAttributes( XMLWriter $w )
    {

        $w->writeAttribute( 'x', $this->x );
        $w->writeAttribute( 'y', $this->y );

    }

    /**
     * Returns a string, representing the current point. (Format: x=%d; y=%d)
     *
     * @return string
     */
    public function __toString()
    {

        return sprintf( 'x=%d; y=%d', $this->x, $this->y );

    }

    /**
     * Return a associative array with the 2 elements 'x' and 'y'.
     *
     * @return array
     */
    public function toArray(): array
    {

        return [ 'x' => $this->x, 'y' => $this->y ];

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new {@see \Niirrty\Drawing\Point} instance from defined XML element and returns it.
     *
     * @param SimpleXMLElement $element The XML element, defining the point data by 2 attributes 'x' + 'y'.
     *
     * @return Point|false Returns the resulting point, or boolean FALSE if no usable data are defined.
     */
    public static function FromXml( SimpleXMLElement $element )
    {

        // Getting X
        $x = null;
        if ( isset( $element[ 'x' ] ) )
        {
            $x = intval( $element[ 'x' ] );
        }
        else if ( isset( $element[ 'X' ] ) )
        {
            $x = intval( $element[ 'X' ] );
        }
        else if ( isset( $element[ 'attributes' ][ 'x' ] ) )
        {
            $x = intval( $element[ 'attributes' ][ 'x' ] );
        }
        else if ( isset( $element[ 'attributes' ][ 'X' ] ) )
        {
            $x = intval( $element[ 'attributes' ][ 'X' ] );
        }
        else if ( isset( $element[ '@attributes' ][ 'x' ] ) )
        {
            $x = intval( $element[ '@attributes' ][ 'x' ] );
        }
        else if ( isset( $element[ '@attributes' ][ 'X' ] ) )
        {
            $x = intval( $element[ '@attributes' ][ 'X' ] );
        }

        // Getting Y
        $y = null;
        if ( isset( $element[ 'y' ] ) )
        {
            $y = intval( $element[ 'y' ] );
        }
        else if ( isset( $element[ 'Y' ] ) )
        {
            $y = intval( $element[ 'Y' ] );
        }
        else if ( isset( $element[ 'attributes' ][ 'y' ] ) )
        {
            $y = intval( $element[ 'attributes' ][ 'y' ] );
        }
        else if ( isset( $element[ 'attributes' ][ 'Y' ] ) )
        {
            $y = intval( $element[ 'attributes' ][ 'Y' ] );
        }
        else if ( isset( $element[ '@attributes' ][ 'y' ] ) )
        {
            $y = intval( $element[ '@attributes' ][ 'y' ] );
        }
        else if ( isset( $element[ '@attributes' ][ 'Y' ] ) )
        {
            $y = intval( $element[ '@attributes' ][ 'Y' ] );
        }

        if ( is_null( $x ) || is_null( $y ) )
        {
            return false;
        }

        return new Point( $x, $y );

    }

    /**
     * Parse a value to an \Drawing\Point instance. If the value uses an invalid format (boolean)FALSE is returned.
     *
     * @param Point|Rectangle|string|array|SimpleXMLElement                   $value
     * @param Point &                                                         $output
     *
     * @return boolean
     * @throws NiirrtyException
     */
    public static function TryParse( $value, ?Point &$output ): bool
    {

        if ( is_null( $value ) )
        {
            return false;
        }

        if ( $value instanceof Point )
        {
            $output = $value;

            return true;
        }

        if ( $value instanceof Rectangle )
        {
            $output = $value->getPoint();

            return true;
        }

        if ( is_string( $value ) )
        {
            $hits = null;
            if ( preg_match( '~^x=(\d{1,4});\s*y=(\d{1,4})$~', $value, $hits ) )
            {
                $output = new Point( intval( $hits[ 1 ] ), intval( $hits[ 2 ] ) );

                return true;
            }
            if ( preg_match( '~^(\d{1,4}),\s*(\d{1,4})$~', $value, $hits ) )
            {
                $output = new Point( intval( $hits[ 1 ] ), intval( $hits[ 2 ] ) );

                return true;
            }

            return false;
        }

        if ( is_array( $value ) )
        {
            if ( isset( $value[ 'x' ] ) && isset( $value[ 'y' ] ) )
            {
                $output = new Point( intval( $value[ 'x' ] ), intval( $value[ 'y' ] ) );

                return true;
            }
            if ( isset( $value[ 'X' ] ) && isset( $value[ 'Y' ] ) )
            {
                $output = new Point( intval( $value[ 'X' ] ), intval( $value[ 'Y' ] ) );

                return true;
            }
            if ( count( $value ) != 2 )
            {
                return false;
            }
            if ( isset( $value[ 0 ] ) && isset( $value[ 1 ] ) )
            {
                $output = new Point( intval( $value[ 0 ] ), intval( $value[ 1 ] ) );

                return true;
            }

            return false;
        }

        if ( $value instanceof SimpleXMLElement )
        {
            $output = self::FromXml( $value );

            return ( $output instanceof Point );
        }

        $type = new Type( $value );
        if ( $type->hasAssociatedString() )
        {
            $hits = null;
            if ( preg_match( '~^x=(\d{1,4});\s*y=(\d{1,4})$~', $type->getStringValue(), $hits ) )
            {
                $output = new Point( intval( $hits[ 1 ] ), intval( $hits[ 2 ] ) );

                return true;
            }
            if ( preg_match( '~^(\d{1,4}),\s*(\d{1,4})$~', $type->getStringValue(), $hits ) )
            {
                $output = new Point( intval( $hits[ 1 ] ), intval( $hits[ 2 ] ) );

                return true;
            }
        }

        return false;

    }


    # </editor-fold>


}

