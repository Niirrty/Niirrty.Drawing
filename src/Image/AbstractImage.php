<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing\Image;


use Niirrty\{ArgumentException, NiirrtyException, TDisposable};
use Niirrty\Drawing\{Color, ColorGD, Size};
use Exception;
use Niirrty\DynProp\ExplicitGetter;
use function array_keys;
use function count;
use function is_object;
use function is_string;


/**
 * This abstract class defined a object usable a a base image class.
 * It defines the core code of a {@see \Niirrty\Drawing\Image\IIMage} interface.
 *
 * @since         v0.1
 * @property-read integer   $width
 * @property-read integer   $height
 * @property-read ColorGD[] $userColors
 * @property-read string    $file
 */
abstract class AbstractImage extends ExplicitGetter implements IImage
{


    use TDisposable;


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * The image size
     *
     * @var Size
     */
    public $size;

    /**
     * @var string
     */
    public $mimeType;

    # </editor-fold>


    # <editor-fold desc="= = =   P R O T E C T E D   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * @var array
     */
    protected $_colors = [];

    /**
     * @var string
     */
    protected $_file = null;

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Returns the current image width.
     *
     * @return integer
     */
    public final function getWidth(): int
    {

        return $this->disposed() ? 0 : $this->size->width;

    }

    /**
     * Returns the current image height.
     *
     * @return integer
     */
    public final function getHeight(): int
    {

        return $this->disposed() ? 0 : $this->size->height;

    }

    /**
     * Returns the current image size.
     *
     * @return Size|null
     */
    public final function getSize(): ?Size
    {

        return $this->disposed() ? null : $this->size;

    }

    /**
     * Returns the current image mime type.
     *
     * @return string|null
     */
    public final function getMimeType(): ?string
    {

        return $this->disposed() ? null : $this->mimeType;

    }

    /**
     * Returns all currently user defined colors for this image as array with colornames (keys) + colors as
     * {@see \Niirrty\Drawing\ColorGD} objects.
     *
     * @return ColorGD[] Array of {@see \Niirrty\Drawing\ColorGD}
     */
    public final function getUserColors(): array
    {

        return $this->disposed() ? [] : $this->_colors;

    }

    /**
     * Returns the user defined color with defined name.
     *
     * @param string $name The user color name.
     *
     * @return ColorGD
     * @throws ArgumentException
     */
    public final function getUserColor( string $name )
    {

        if ( $this->disposed() )
        {
            return null;
        }

        if ( !isset( $this->_colors[ $name ] ) )
        {
            throw new ArgumentException(
                '$name',
                $name,
                'There is no user color with this name defined for this image!'
            );
        }

        return $this->_colors[ $name ];

    }

    /**
     * Returns the current image file path, if defined.
     *
     * @return string|null
     */
    public final function getFile(): ?string
    {

        return $this->disposed() ? null : $this->file;

    }

    /**
     * Returns, if the current image is a true color image.
     *
     * @return boolean
     */
    public final function isTrueColor(): bool
    {

        return $this->mimeType == 'image/jpeg' || $this->mimeType == 'image/png';

    }

    /**
     * Returns, if the current image can use some transparency.
     *
     * @return boolean
     */
    public final function canUseTransparency(): bool
    {

        return $this->mimeType == 'image/gif' || $this->mimeType == 'image/png';

    }

    /**
     * Adds a new user defined, named color and returns the associated {@see \Niirrty\Drawing\Color} instance.
     *
     * @param string  $name    The user defined color name
     * @param string|RGB-Array|integer|\Drawing\ColorGD|\Drawing\Color $colorDefinition
     * @param integer $opacity The opacity (0-100) in percent.
     *
     * @return ColorGD or FALSE
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    public final function addUserColor( string $name, $colorDefinition, int $opacity = 100 )
    {

        if ( $this->disposed() )
        {
            throw new NiirrtyException( 'This instance is not usable cause its already disposed (destroyed)!' );
        }

        if ( is_object( $colorDefinition ) )
        {

            if ( $colorDefinition instanceof ColorGD )
            {
                $this->_colors[ $name ] = $colorDefinition;
            }
            else if ( $colorDefinition instanceof Color )
            {
                $this->_colors[ $name ] = new ColorGD(
                    $colorDefinition->createGdValue(),
                    $colorDefinition->getOpacity()
                );
            }
            else
            {
                throw new ArgumentException(
                    '$colorDefinition',
                    $colorDefinition,
                    'Illegal color definition for usage inside a \Drawing\Image\GdImage!'
                );
            }

            return $this->_colors[ $name ];

        }

        $tmp = new ColorGD( $colorDefinition, $opacity );
        $this->_colors[ $name ] = $tmp;

        return $this->_colors[ $name ];

    }

    /**
     * Returns if the current image is an PNG image.
     *
     * @return boolean
     */
    public final function isPng(): bool
    {

        return $this->mimeType == 'image/png';

    }

    /**
     * Returns if the current image is an GIF image.
     *
     * @return boolean
     */
    public final function isGif(): bool
    {

        return $this->mimeType == 'image/gif';

    }

    /**
     * Returns if the current image is an JPEG image.
     *
     * @return boolean
     */
    public final function isJpeg(): bool
    {

        return $this->mimeType == 'image/jpeg';

    }

    /**
     * Returns if currently a image file path is defined that can be used to store the image.
     *
     * @return boolean
     */
    public final function hasAssociatedFile(): bool
    {

        return !empty( $this->file );
    }

    # </editor-fold>


    # <editor-fold desc="= = =   P R O T E C T E D   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * @param string|ColorGD|Color $color
     * @param string               $name
     *
     * @return ColorGD|false
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    protected function getGdColorObject( $color, string $name )
    {

        if ( $color instanceof ColorGD )
        {
            return $color;
        }

        if ( $color instanceof Color )
        {
            return $this->addUserColor( $name, $color );
        }

        if ( is_string( $color ) )
        {

            if ( isset( $this->_colors[ $color ] ) )
            {
                return $this->_colors[ $color ];
            }

            try
            {
                $color = $this->addUserColor( $name, $color );
                if ( !( $color instanceof ColorGD ) )
                {
                    throw new Exception();
                }

                return $color;
            }
            catch ( Exception $ex )
            {
                $ex = null;
                if ( isset( $this->_colors[ $name ] ) )
                {
                    return $this->_colors[ $name ];
                }
                if ( count( $this->_colors ) > 0 )
                {
                    $keys = array_keys( $this->_colors );

                    return $this->_colors[ $keys[ 0 ] ];
                }

                return $this->addUserColor( $name, 'black' );
            }

        }

        if ( isset( $this->_colors[ $name ] ) )
        {
            return $this->_colors[ $name ];
        }

        if ( count( $this->_colors ) > 0 )
        {
            $keys = array_keys( $this->_colors );

            return $this->_colors[ $keys[ 0 ] ];
        }

        return $this->addUserColor( $name, 'black' );

    }


    # </editor-fold>


}

