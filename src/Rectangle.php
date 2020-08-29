<?php /** @noinspection PhpUnused */
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing;


use Niirrty\DynProp\ExplicitGetterSetter;
use Niirrty\NiirrtyException;
use Niirrty\TypeTool;
use SimpleXMLElement;
use XMLWriter;
use function array_change_key_case;
use function imagesx;
use function imagesy;
use function intval;
use function is_array;
use function is_int;
use function is_integer;
use function is_string;
use function max;
use function min;
use function preg_match;


/**
 * Define a 2 dimensional rectangle in relation to a location.
 *
 * @property-read int $right  The location of the right corner of the rectangle.
 * @property-read int $bottom The location of the bottom corner of the rectangle.
 * @property      int $left   Alias of 'x' property.
 * @property      int $x      The rectangle X coordinate.
 * @property      int $top    Alias of 'y' property.
 * @property      int $y      The rectangle Y coordinate.
 * @property      int $width  The rectangle width.
 * @property      int $height The rectangle height.
 */
class Rectangle extends ExplicitGetterSetter
{


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * The rectangle location point of the top left corner.
     *
     * @var Point
     */
    public $point;

    /**
     * The rectangle size.
     *
     * @var Size
     */
    public $size;

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     *
     * @param Point|null $point
     * @param Size|null  $size
     */
    public function __construct( ?Point $point = null, ?Size $size = null )
    {

        $this->point = ( null !== $point ) ? $point : new Point();

        $this->size = ( null !== $size ) ? $size : new Size();

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Returns the X position of the right rectangle corner.
     *
     * @return integer
     */
    public final function getRight(): int
    {

        return $this->point->x + $this->size->width;

    }

    /**
     * Returns the Y position of the bottom rectangle corner.
     *
     * @return integer
     */
    public final function getBottom(): int
    {

        return $this->point->y + $this->size->height;

    }

    /**
     * Returns, if the current rectangle contains the defined rectangle.
     *
     * @param Rectangle $rect
     *
     * @return boolean
     */
    public final function contains( Rectangle $rect ): bool
    {

        return (
            (
                (
                    (
                        $this->point->x <= $rect->point->x
                    )
                    && (
                        ( $rect->point->x + $rect->size->width ) <= ( $this->point->x + $this->size->width )
                    )
                )
                &&
                (
                    $this->point->y <= $rect->point->y
                )
            )
            &&
            (
                ( $rect->point->y + $rect->size->height ) <= ( $this->point->y + $this->size->height )
            )
        );

    }

    /**
     * Returns, if the current rectangle contains the defined location.
     *
     * @param int|Point $xOrPoint
     * @param int|null  $y
     *
     * @return boolean
     */
    public final function containsPoint( $xOrPoint, ?int $y = null ): bool
    {

        $x = $xOrPoint;

        if ( $x instanceof Point )
        {
            $y = $x->y;
            $x = $x->x;
        }
        else
        {
            if ( !is_int( $y ) )
            {
                $y = intval( $y );
            }
            if ( is_integer( $xOrPoint ) )
            {
                $x = $xOrPoint;
            }
            else if ( TypeTool::IsInteger( $xOrPoint ) )
            {
                $x = (int) $xOrPoint;
            }
            else
            {
                $x = 0;
            }
        }

        return (
            (
                (
                    ( $this->point->x <= $x )
                    &&
                    ( $x < ( $this->point->x + $this->size->width ) )
                )
                &&
                ( $this->point->y <= $y )
            )
            &&
            ( $y < ( $this->point->y + $this->size->height ) )
        );

    }

    /**
     * Returns, if the current rectangle contains the defined Size.
     *
     * @param Size|integer $widthOrSize
     * @param integer      $height
     *
     * @return boolean
     */
    public final function containsSize( $widthOrSize, ?int $height = null ): bool
    {

        $width = $widthOrSize;

        if ( $width instanceof Size )
        {
            $height = $width->height;
            $width = $width->width;
        }
        else
        {
            if ( !is_int( $height ) )
            {
                $height = intval( $height );
            }
            if ( is_integer( $widthOrSize ) )
            {
                $width = $widthOrSize;
            }
            else if ( TypeTool::IsInteger( $widthOrSize ) )
            {
                $width = intval( $widthOrSize );
            }
            else
            {
                $width = 0;
            }
        }

        return
            $this->size->width >= $width
            &&
            $this->size->height >= $height;

    }

    /**
     * Gets a clone of the current instance.
     *
     * @return Rectangle
     */
    public function getClone(): Rectangle
    {

        return new Rectangle(
            new Point(
                $this->point->x,
                $this->point->y
            ),
            new Size(
                $this->size->width,
                $this->size->height
            )
        );

    }

    public function __clone()
    {

        return $this->getClone();
    }

    /**
     * Builds the intersection (Schnittmenge) between the current and the defined rectangle.
     *
     * @param Rectangle $rect
     *
     * @return Rectangle|false Returns the resulting rectangle or boolean FALSE if no intersection
     *                                          exists.
     */
    public final function intersect( Rectangle $rect )
    {

        $x = max( $this->point->x, $rect->point->x );

        $num2 = min(
            ( $this->point->x + $this->size->width ),
            ( $rect->point->x + $rect->size->width )
        );

        $y = max( $this->point->y, $rect->point->y );

        $num4 = min(
            ( $this->point->y + $this->size->height ),
            ( $rect->point->y + $rect->size->height )
        );

        if ( ( $num2 >= $x ) && ( $num4 >= $y ) )
        {
            return Rectangle::Create(
                $x,
                $y,
                $num2 - $x,
                $num4 - $y
            );
        }
        else
        {
            return false;
        }

    }

    /**
     * Returns if the current rectangle is a empty rectangle (width, height, x and y must be lower than 1)
     *
     * @return boolean
     */
    public final function isEmpty(): bool
    {

        return $this->point->isEmpty()
               && $this->size->width < 1
               && $this->size->height < 1;

    }

    /**
     * Builds a new rectangle that must contain the current rectangle and the defined rectangle. (a union)
     *
     * @param Rectangle $rect
     *
     * @return Rectangle Returns the resulting rectangle.
     */
    public final function union( Rectangle $rect )
    {

        $x = max( $this->point->x, $rect->point->x );
        $num2 = max(
            $this->point->x + $this->size->width,
            $rect->point->x + $rect->size->width
        );

        $y = min( $this->point->y, $rect->point->y );
        $num4 = max(
            $this->point->y + $this->size->height,
            $rect->point->y + $rect->size->height
        );

        return Rectangle::Create(
            $x,
            $y,
            $num2 - $x,
            $num4 - $y
        );

    }

    /**
     * Gets the rectangle point.
     *
     * @return Point
     */
    public final function getPoint(): Point
    {

        return $this->point;

    }

    /**
     * Gets the rectangle X coordinate.
     *
     * @return int
     */
    public final function getLeft(): int
    {

        return $this->point->x;

    }

    /**
     * Gets the rectangle X coordinate.
     *
     * @return int
     */
    public final function getX(): int
    {

        return $this->point->x;

    }

    /**
     * Gets the rectangle Y coordinate.
     *
     * @return int
     */
    public final function getTop(): int
    {

        return $this->point->y;

    }

    /**
     * Gets the rectangle Y coordinate.
     *
     * @return int
     */
    public final function getY(): int
    {

        return $this->point->y;

    }

    /**
     * Gets the rectangle width.
     *
     * @return int
     */
    public final function getWidth(): int
    {

        return $this->size->width;

    }

    /**
     * Gets the rectangle height.
     *
     * @return int
     */
    public final function getHeight(): int
    {

        return $this->size->height;

    }

    /**
     * Sets the rectangle X coordinate.
     *
     * @param int $left
     *
     * @return Rectangle
     */
    public final function setLeft( int $left ): Rectangle
    {

        $this->point->x = $left;

        return $this;

    }

    /**
     * Sets the rectangle X coordinate.
     *
     * @param int $x
     *
     * @return Rectangle
     */
    public final function setX( int $x ): Rectangle
    {

        $this->point->x = $x;

        return $this;

    }

    /**
     * Sets the rectangle Y coordinate.
     *
     * @param int $top
     *
     * @return Rectangle
     */
    public final function setTop( int $top ): Rectangle
    {

        $this->point->y = $top;

        return $this;

    }

    /**
     * Sets the rectangle Y coordinate.
     *
     * @param int $y
     *
     * @return Rectangle
     */
    public final function setY( int $y ): Rectangle
    {

        $this->point->y = $y;

        return $this;

    }

    /**
     * Sets the rectangle width.
     *
     * @param int $width
     *
     * @return Rectangle
     */
    public final function setWidth( int $width ): Rectangle
    {

        $this->size->width = $width;

        return $this;

    }

    /**
     * Sets the rectangle height.
     *
     * @param int $height
     *
     * @return Rectangle
     */
    public final function setHeight( int $height ): Rectangle
    {

        $this->size->height = $height;

        return $this;

    }

    /**
     * Writes the current instance data as XML element to a XmlWriter.
     *
     * The resulting XML element looks like follow:
     *
     * <b>&lt;Rectangle x="0" y="0" width="0" height="0"/&gt;</b>
     *
     * @param XMLWriter $w           The XMLWRiter.
     * @param string     $elementName The name of the resulting XML element.  (default='Rectangle')
     *                                If no usable element name is defined, only the attributes are written!
     */
    public function writeXml( XMLWriter $w, string $elementName = 'Rectangle' )
    {

        $writeElement = !empty( $elementName );

        if ( $writeElement )
        {
            $w->startElement( $elementName );
        }

        $this->writeXMLAttributes( $w );

        if ( $writeElement )
        {
            $w->endElement();
        }

    }

    /**
     * Write the current instance data as XML element attributes (width, height, x, y) to defined XmlWriter.
     *
     * @param XMLWriter $w The XMLWriter.
     */
    public function writeXmlAttributes( XMLWriter $w )
    {

        $this->point->writeXmlAttributes( $w );
        $this->size->writeXmlAttributes( $w );

    }

    /**
     * Returns the rectangle string. Format is: "width=?; height=?; x=?; y=?"
     *
     * @return string
     */
    public function __toString()
    {

        return (string) $this->point . '; ' . (string) $this->size;

    }

    /**
     * Returns a array with all instance data. Used array keys are 'x', 'y', 'width' and 'height'.
     *
     * @return array
     */
    public function toArray(): array
    {

        return [
            'x'      => $this->point->x,
            'y'      => $this->point->y,
            'width'  => $this->size->width,
            'height' => $this->size->height,
        ];

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

    /**
     * …
     *
     * @param integer $x
     * @param integer $y
     * @param integer $width
     * @param integer $height
     *
     * @return Rectangle
     */
    public static function Create( int $x = 0, int $y = 0, int $width = 0, int $height = 0 ): Rectangle
    {

        return new Rectangle(
            new Point( $x, $y ),
            new Size( $width, $height )
        );

    }

    /**
     * …
     *
     * @param resource $imageResource
     *
     * @return Rectangle
     */
    public static function FromImageResource( $imageResource )
    {

        return new Rectangle(
            new Point(),
            new Size( imagesx( $imageResource ), imagesy( $imageResource ) )
        );

    }

    /**
     * Parses a XML element to a {@see \Niirrty\Drawing\Rectangle} instance.
     *
     * @param SimpleXMLElement $element Das XML-Element mit den Instanzdaten
     *
     * @return Rectangle|false
     * @throws NiirrtyException
     */
    public static function FromXml( SimpleXMLElement $element )
    {

        $point = null;
        if ( !Point::TryParse( $element, $point ) )
        {
            return false;
        }

        $size = null;
        if ( !Size::TryParse( $element, $size ) )
        {
            return false;
        }

        return new Rectangle(
            $point,
            $size
        );

    }

    /**
     * Parses a string to a {@see \Niirrty\Drawing\Rectangle} instance.
     *
     * Accepted string must use the following format:
     *
     * <code>x=0; y=0; width=800; height=600</code>
     *
     * @param string $objectString Zeichenkette die die Daten einer
     *                             Instanz des implementierenden Objekts entspricht.
     *
     * @return Rectangle|false Or boolean FALSE
     */
    public static function FromString( string $objectString )
    {

        $hits = null;

        if ( !preg_match( '~^x=(\d{1,4});\s*y=(\d{1,4});\s*width=(\d{1,4});\s*height=(\d{1,4})$~',
                          $objectString, $hits ) )
        {
            return false;
        }

        return self::Create(
            intval( $hits[ 1 ] ),
            intval( $hits[ 2 ] ),
            intval( $hits[ 3 ] ),
            intval( $hits[ 4 ] )
        );

    }

    /**
     * Parses a array to a {@see \Niirrty\Drawing\Rectangle} instance.
     *
     * Required array keys are: x, y, width, height
     *
     * @param array $objectData
     *
     * @return Rectangle|false Or boolean FALSE
     */
    public static function FromArray( array $objectData )
    {

        $objectData = array_change_key_case( $objectData );

        if ( isset( $objectData[ 'x' ] )
             && isset( $objectData[ 'y' ] )
             && isset( $objectData[ 'width' ] )
             && isset( $objectData[ 'height' ] ) )
        {
            return self::Create(
                intval( $objectData[ 'x' ] ),
                intval( $objectData[ 'y' ] ),
                intval( $objectData[ 'width' ] ),
                intval( $objectData[ 'height' ] )
            );
        }

        return false;

    }

    /**
     * Parses a array to a {@see \Niirrty\Drawing\Rectangle} instance. Required format "x=?; y=?; width=?; height=?"
     *
     * @param string     $value
     * @param Rectangle &$output
     *
     * @return boolean
     * @throws NiirrtyException
     */
    public static function TryParse( $value, &$output ): bool
    {

        if ( $value instanceof Rectangle )
        {
            $output = $value;

            return true;
        }

        if ( is_string( $value ) )
        {
            return ( false !== ( $output = self::FromString( $value ) ) );
        }

        if ( is_array( $value ) )
        {
            return ( false !== ( $output = self::FromArray( $value ) ) );
        }

        if ( $value instanceof SimpleXMLElement )
        {
            return ( false !== ( $output = self::FromXml( $value ) ) );
        }

        return false;

    }


    # </editor-fold>


}

