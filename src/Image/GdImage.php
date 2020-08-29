<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing\Image
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing\Image;


use Exception;
use Niirrty\ArgumentException;
use Niirrty\Drawing\{Color, ColorGD, ColorTool, ContentAlign, Point, Rectangle, Size};
use Niirrty\IO\{File, FileAccessException, FileNotFoundException, IOException, MimeTypeTool};
use Niirrty\NiirrtyException;
use function basename;
use function cos;
use function deg2rad;
use function explode;
use function file_exists;
use function floor;
use function getimagesize;
use function header;
use function imagealphablending;
use function imagecolorallocate;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecolorsforindex;
use function imagecopy;
use function imagecopymerge;
use function imagecopyresampled;
use function imagecopyresized;
use function imagecreate;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefill;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagerectangle;
use function imagerotate;
use function imagesavealpha;
use function imagesetpixel;
use function imagestring;
use function imagesx;
use function imagesy;
use function imagettfbbox;
use function imagettftext;
use function intval;
use function is_file;
use function is_int;
use function is_null;
use function is_resource;
use function pow;
use function preg_match;
use function round;
use function sin;
use function sqrt;
use function strlen;
use function strtolower;
use const GRAVITY_BOTTOM;
use const GRAVITY_BOTTOMLEFT;
use const GRAVITY_BOTTOMRIGHT;
use const GRAVITY_CENTER;
use const GRAVITY_LEFT;
use const GRAVITY_RIGHT;
use const GRAVITY_TOP;
use const GRAVITY_TOPLEFT;
use const GRAVITY_TOPRIGHT;


/**
 * A PHP GD lib IImage implementation.
 *
 * @since v0.1.0
 */
class GdImage extends AbstractImage
{


    # <editor-fold desc="= = =   P R O T E C T E D   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * @var resource GD-Image Resource
     */
    protected $r;

    # </editor-fold>


    # <editor-fold desc="= = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Defines the 4 character size if a systemfont is used to draw some text in a image
     *
     * @var array
     */
    private $charSizes = [
        1 => [ 5, 11 ],
        2 => [ 6, 16 ],
        3 => [ 7, 18 ],
        4 => [ 8, 20 ],
    ];

    # </editor-fold>


    # <editor-fold desc="= = =   P R I V A T E   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = =">

    private function __construct( $resource, Size $size, string $mimeType, string $file = null )
    {

        $this->size = $size;
        $this->r = $resource;
        $this->mimeType = $mimeType;
        $this->_file = $file;
        $this->_colors = [];
        if ( !is_resource( $resource ) )
        {
            $this->dispose();
        }
    }

    # </editor-fold>


    # <editor-fold desc="= = =   D E S T U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =">

    public function __destruct()
    {

        $this->dispose();

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Dispose the image instance and all allocated resources.
     */
    public final function dispose()
    {

        if ( $this->disposed() )
        {
            return;
        }

        if ( is_resource( $this->r ) )
        {
            imagedestroy( $this->r );
        }

        $this->r = null;
        $this->size = null;
        $this->_colors = [];
        $this->mimeType = null;
        $this->_file = null;

        parent::dispose();

    }

    /**
     * Return the current image resource.
     *
     * @return resource|null
     */
    public final function getResource()
    {

        return $this->r;

    }

    public function __get( string $name )
    {

        switch ( strtolower( $name ) )
        {
            case 'resource':
            case 'img':
            case 'image':
            case 'imgage':
                return $this->r;
            default:
                return parent::__get( $name );
        }

    }

    public function __clone()
    {

        if ( $this->disposed() )
        {
            return null;
        }

        if ( $this->isTruecolor() )
        {
            $dst = imagecreatetruecolor( $this->getWidth(), $this->getHeight() );
        }
        else
        {
            $dst = imagecreate( $this->getWidth(), $this->getHeight() );
        }

        if ( $this->canUseTransparency() )
        {
            imagealphablending( $dst, false );
            imagesavealpha( $dst, true );
        }

        imagecopyresampled(
            $dst,
            $this->r,
            0,
            0,
            0,
            0,
            $this->getWidth(),
            $this->getHeight(),
            $this->getWidth(),
            $this->getHeight()
        );

        return new GdImage(
            $dst,
            new Size( $this->size->width, $this->size->height ),
            $this->mimeType,
            $this->getFile()
        );

    }

    /**
     * Saves the current image to defined image file. If no image file is defined the internally defined image file path
     * is used. If its also not defined a {@see \Niirrty\IO\IOException} is thrown.
     *
     * @param string  $file     Path of image file to save the current image instance. (Must be only defined if the
     *                          instance does not define a path.
     * @param integer $quality  Image quality if it is saved as an JPEG image. (1-100)
     *
     * @throws IOException
     */
    public final function save( string $file = null, int $quality = 75 )
    {

        if ( !empty( $file ) )
        {
            $this->file = $file;
        }

        if ( empty( $this->file ) )
        {
            throw new IOException(
                $file,
                'Saving a GdImage instance fails! No file is defined.'
            );
        }

        $fMime = MimeTypeTool::GetByFileName( $this->file );
        if ( $fMime != $this->mimeType )
        {
            $tmp = explode( '/', $fMime );
            $ext = $tmp[ 1 ];
            if ( $ext == 'jpeg' )
            {
                $ext = 'jpg';
            }
            $this->file = File::ChangeExtension( $this->file, $ext );
            $this->mimeType = $fMime;
        }

        switch ( $this->mimeType )
        {
            case 'image/png':
                imagepng( $this->r, $this->file );
                break;
            case 'image/gif':
                imagegif( $this->r, $this->file );
                break;
            default:
                imagejpeg( $this->r, $this->file, $quality );
                break;
        }

    }

    /**
     * Outputs the current image, including all required HTTP headers and exit the script.
     *
     * @param integer $quality  Image quality if it is an JPEG image. (1-100)
     * @param string  $filename Output image file name for HTTP headers.
     */
    public final function output( int $quality = 60, string $filename = null )
    {

        header( 'Expires: 0' );
        header( 'Cache-Control: private' );
        header( 'Pragma: cache' );

        if ( empty( $filename ) )
        {
            $filename = basename( $this->file );
        }
        else
        {
            $filename = basename( $filename );
        }

        if ( !empty( $filename ) )
        {
            header( "Content-Disposition: inline; filename=\"{$filename}\"" );
            $mime = MimetypeTool::GetByFileName( $filename );
            header( "Content-Type: {$mime}" );
            switch ( $mime )
            {
                case 'image/png':
                    imagepng( $this->r );
                    break;
                case 'image/gif':
                    imagegif( $this->r );
                    break;
                default:
                    imagejpeg( $this->r, null, $quality );
                    break;
            }
            exit;
        }

        header( "Content-Type: {$this->mimeType}" );

        switch ( $this->mimeType )
        {
            case 'image/png':
                imagepng( $this->r );
                break;
            case 'image/gif':
                imagegif( $this->r );
                break;
            default:
                imagejpeg( $this->r, null, $quality );
                break;
        }

        exit;

    }

    # </editor-fold>


    # <editor-fold desc=" - - -   P U B L I C   A C T I O N   M E T H O D S   - - - - - - - - - - - - - - -">

    # <editor-fold desc=" O T H E R">

    /**
     * Rotate the image in 90° steps.
     *
     * @param int                       $multiplier 1=90, 2=180, 3=270
     * @param string|Color|ColorGD|null $fillColor
     * @param bool                      $internal   Use internal? (no new instance?)
     *
     * @return IImage
     * @throws ArgumentException|NiirrtyException If $angle is not 90 anf not a multiple of 90 or outside accepted range
     */
    public function rotate90( int $multiplier = 1, $fillColor = null, bool $internal = true ): IImage
    {

        if ( 0 > $multiplier || 4 === $multiplier )
        {
            $multiplier = 0;
        }
        else if ( 4 < $multiplier )
        {
            $multiplier = ( $multiplier - ( $multiplier % 4 ) ) / 4;
            if ( 3 < $multiplier )
            {
                $multiplier = 0;
            }
        }

        $angle = 90 * $multiplier;

        $fillColor = $this->getGdColorObject( $fillColor, 'rotate_fillcolor' );
        $tmpr = imagerotate( $this->r, $angle, $fillColor->getGdValue() );

        if ( $internal )
        {
            imagedestroy( $this->r );
            $this->r = $tmpr;
            $this->size = new Size(
                imagesx( $this->r ),
                imagesy( $this->r )
            );

            return $this;
        }

        $res = new GdImage(
            $tmpr,
            new Size(
                imagesx( $tmpr ),
                imagesy( $tmpr )
            ),
            $this->mimeType,
            $this->file
        );

        $res->_colors = $this->_colors;

        return $res;

    }

    /**
     * Negates all image colors.
     *
     * @param bool $internal Handle the action internally? Otherwise a new instance is returned.
     *
     * @return GdImage
     */
    public final function negate( bool $internal = false )
    {

        if ( !$internal )
        {
            $cln = clone $this;

            return $cln->negate( true );
        }

        $w = $this->getWidth();
        $h = $this->getHeight();

        $im = imagecreatetruecolor( $w, $h );
        for ( $y = 0; $y < $h; ++$y )
        {
            for ( $x = 0; $x < $w; $x++ )
            {
                $colors = imagecolorsforindex(
                    $this->r,
                    imagecolorat( $this->r, $x, $y )
                );
                $r = 255 - $colors[ 'red' ];
                $g = 255 - $colors[ 'green' ];
                $b = 255 - $colors[ 'blue' ];
                $newColor = imagecolorallocate( $im, $r, $g, $b );
                imagesetpixel( $im, $x, $y, $newColor );
            }
        }

        imagedestroy( $this->r );
        $this->r = null;

        $this->r = $im;

        return $this;

    }

    # </editor-fold>

    # <editor-fold desc=" C R O P P I N G ">

    /**
     * Crops the image part ($width + $height) with defined gravity.
     *
     * @param int        $width    The width of the required crop result.
     * @param int        $height   The height of the required crop result.
     * @param int|string $gravity  The gravity of the crop rectangle (see GRAVITY_* constants)
     * @param bool       $internal Handle the action internally? Otherwise a new instance is returned.
     *
     * @return GdImage
     * @throws ArgumentException     If $width or $height is lower than 1
     */
    public function cropByGravity( int $width, int $height, $gravity = GRAVITY_TOPLEFT, bool $internal = true )
    {

        // Check if width and height is valid, and regulate the finally used values
        $this->cropCheck( $width, $height );

        if ( $this->hasSameSize( $width, $height ) )
        {
            // No size change needed
            return $internal ? $this : clone $this;
        }

        $rect = null;

        switch ( $gravity )
        {

            case GRAVITY_TOPLEFT:
                $rect = Rectangle::Create( 0, 0, $width, $height );
                break;

            case GRAVITY_TOP:
                $rect = Rectangle::Create(
                    (int) floor( ( $this->getWidth() / 2.0 ) - ( $width / 2.0 ) ),
                    0,
                    $width,
                    $height
                );
                break;

            case GRAVITY_TOPRIGHT:
                $rect = Rectangle::Create( $this->getWidth() - $width, 0, $width, $height );
                break;
            case GRAVITY_LEFT:
                $rect = Rectangle::Create(
                    0,
                    (int) floor( ( $this->getHeight() / 2.0 ) - ( $height / 2.0 ) ),
                    $width,
                    $height
                );
                break;

            case GRAVITY_RIGHT:
                $rect = Rectangle::Create(
                    $this->getWidth() - $width,
                    (int) floor( ( $this->getHeight() / 2.0 ) - ( $height / 2.0 ) ),
                    $width,
                    $height
                );
                break;

            case GRAVITY_BOTTOMLEFT:
                $rect = Rectangle::Create(
                    0,
                    $this->getHeight() - $height,
                    $width,
                    $height
                );
                break;

            case GRAVITY_BOTTOM:
                $rect = Rectangle::Create(
                    (int) floor( ( $this->getWidth() / 2.0 ) - ( $width / 2.0 ) ),
                    $this->getHeight() - $height,
                    $width,
                    $height
                );
                break;
            case GRAVITY_BOTTOMRIGHT:
                $rect = Rectangle::Create(
                    $this->getWidth() - $width,
                    $this->getHeight() - $height,
                    $width,
                    $height
                );
                break;

            default:
                #case \GRAVITY_CENTER:
                $rect = Rectangle::Create(
                    (int) floor( ( $this->getWidth() / 2.0 ) - ( $width / 2.0 ) ),
                    (int) floor( ( $this->getHeight() / 2.0 ) - ( $height / 2.0 ) ),
                    $width,
                    $height
                );
                break;

        }

        return $this->cropRect( $rect, $internal );

    }

    /**
     * Crops the image part ($width + $height) with defined align
     *
     * @param int          $width                     The width of the required crop result.
     * @param int          $height                    The height of the required crop result.
     * @param ContentAlign $align                     The crop rectangle align
     * @param bool         $internal                  Handle the action internally? Otherwise a new instance is
     *                                                returned.
     *
     * @return GdImage
     * @throws ArgumentException              If $width or $height is lower than 1
     */
    public function cropByAlign( int $width, int $height, ContentAlign $align, bool $internal = true )
    {

        $this->cropCheck( $width, $height );

        if ( $this->hasSameSize( $width, $height ) )
        {
            return $internal ? $this : clone $this;
        }
        $rect = null;

        switch ( $align->Value )
        {
            case ContentAlign::TOP_LEFT:
                $rect = Rectangle::Create( 0, 0, $width, $height );
                break;
            case ContentAlign::TOP:
                $rect = Rectangle::Create(
                    (int) floor( ( $this->getWidth() / 2.0 ) - ( $width / 2.0 ) ),
                    0,
                    $width,
                    $height
                );
                break;
            case ContentAlign::TOP_RIGHT:
                $rect = Rectangle::Create(
                    $this->getWidth() - $width,
                    0,
                    $width,
                    $height
                );
                break;
            case ContentAlign::MIDDLE_LEFT:
                $rect = Rectangle::Create(
                    0,
                    (int) floor( ( $this->getHeight() / 2.0 ) - ( $height / 2.0 ) ),
                    $width,
                    $height
                );
                break;
            case ContentAlign::MIDDLE_RIGHT:
                $rect = Rectangle::Create(
                    $this->getWidth() - $width,
                    (int) floor( ( $this->getHeight() / 2.0 ) - ( $height / 2.0 ) ),
                    $width,
                    $height
                );
                break;
            case ContentAlign::BOTTOM_LEFT:
                $rect = Rectangle::Create( 0, $this->getHeight() - $height, $width, $height );
                break;
            case ContentAlign::BOTTOM:
                $rect = Rectangle::Create(
                    (int) floor( ( $this->getWidth() / 2.0 ) - ( $width / 2.0 ) ),
                    $this->getHeight() - $height,
                    $width,
                    $height
                );
                break;
            case ContentAlign::BOTTOM_RIGHT:
                $rect = Rectangle::Create(
                    $this->getWidth() - $width,
                    $this->getHeight() - $height,
                    $width,
                    $height
                );
                break;
            default:
                #case \GRAVITY_CENTER:
                $rect = Rectangle::Create(
                    (int) floor( ( $this->getWidth() / 2.0 ) - ( $width / 2.0 ) ),
                    (int) floor( ( $this->getHeight() / 2.0 ) - ( $height / 2.0 ) ),
                    $width,
                    $height
                );
                break;
        }

        return $this->cropRect( $rect, $internal );

    }

    /**
     * Crops the defined rectangle image part.
     *
     * @param Rectangle $rect     The cropping rectangle
     * @param bool      $internal Handle the action internally? Otherwise a new instance is returned.
     *
     * @return GdImage
     */
    public function cropRect( Rectangle $rect, bool $internal = true )
    {

        $thumb = imagecreatetruecolor( $rect->size->width, $rect->size->height );

        if ( $this->mimeType == 'image/gif' || $this->mimeType == 'image/png' )
        {
            imagealphablending( $thumb, false );
            imagesavealpha( $thumb, true );
        }

        imagecopyresampled(
            $thumb,              # $dst_image
            $this->r,            # $src_image
            0,                   # $dst_x
            0,                   # $dst_y
            $rect->point->x,     # $src_x
            $rect->point->y,     # $src_y
            $rect->size->width,  # $dst_w
            $rect->size->height, # $dst_h
            $rect->size->width,  # $src_w
            $rect->size->height  # $src_h
        );

        if ( $internal )
        {
            $this->size->height = $rect->size->height;
            $this->size->width = $rect->size->width;
            if ( is_resource( $this->r ) )
            {
                imagedestroy( $this->r );
                $this->r = null;
            }
            $this->r = $thumb;

            return $this;
        }

        return new GdImage( $thumb, $rect->size, $this->mimeType, $this->file );

    }

    /**
     * Crops the quadratic part with biggest usable dimension from image, by a gravity.
     *
     * @param int|string $gravity  The gravity of the crop rectangle (see GRAVITY_* constants)
     * @param boolean    $internal Handle the action internally? Otherwise a new instance is returned.
     *
     * @return IImage
     * @throws ArgumentException
     */
    public function cropQuadraticByGravity( $gravity = GRAVITY_CENTER, bool $internal = true )
    {

        if ( $this->size->isLandscape() )
        {
            return $this->cropByGravity( $this->getHeight(), $this->getHeight(), $gravity, $internal );
        }

        return $this->cropByGravity( $this->getWidth(), $this->getWidth(), $gravity, $internal );

    }

    /**
     * Crops the quadratic part with biggest usable dimension from image, by alignment.
     *
     * @param ContentAlign $align                     The crop rectangle align
     * @param boolean      $internal                  Handle the action internally? Otherwise a new instance is
     *                                                returned.
     *
     * @return IImage
     * @throws ArgumentException
     */
    public function cropQuadraticByAlign( ContentAlign $align, bool $internal = true )
    {

        if ( $this->size->isLandscape() )
        {
            return $this->cropByAlign( $this->getHeight(), $this->getHeight(), $align, $internal );
        }

        return $this->cropByAlign( $this->getWidth(), $this->getWidth(), $align, $internal );

    }

    # </editor-fold>

    # <editor-fold desc=" S H R I N K I N G ">

    /**
     * Shrinks this image by defined percent value, with holding the image proportion.
     *
     * @param int  $percent  Resize value in percent.
     * @param bool $internal Handle the action internally? Otherwise a new instance is returned.
     *
     * @return GdImage
     * @throws ArgumentException
     */
    public function shrink( int $percent, bool $internal = true )
    {

        if ( $percent < 1 || $percent >= 100 )
        {
            throw new ArgumentException(
                'percent',
                $percent,
                'Image dimension shrinking must produce a smaller, non zero sized image!'
            );
        }

        $newWidth = intval( floor( $this->getWidth() * $percent / 100 ) );
        $newHeight = intval( floor( $this->getHeight() * $percent / 100 ) );

        if ( $this->isTruecolor() )
        {
            $dst = imagecreatetruecolor( $newWidth, $newHeight );
        }
        else
        {
            $dst = imagecreate( $newWidth, $newHeight );
        }

        if ( $this->canUseTransparency() )
        {
            imagealphablending( $dst, false );
            imagesavealpha( $dst, true );
        }

        imagecopyresized( $dst, $this->r, 0, 0, 0, 0, $newWidth, $newHeight, $this->getWidth(), $this->getHeight() );

        if ( $internal )
        {
            if ( !is_null( $this->r ) )
            {
                imagedestroy( $this->r );
                $this->r = null;
            }
            $this->r = $dst;
            $this->size->width = $newWidth;
            $this->size->height = $newHeight;

            return $this;
        }

        return new GdImage(
            $dst,
            new Size( $newWidth, $newHeight ),
            $this->mimeType,
            $this->file
        );

    }

    /**
     * Shrinks this image so it fits the defined size, with holding the image proportion.
     *
     * @param Size $maxSize  This is the maximum size of this image after resizing.
     * @param bool $internal Handle the action internally? Otherwise a new instance is returned.
     *
     * @return IImage
     */
    public function shrinkInto( Size $maxSize, bool $internal = true )
    {

        if ( $maxSize->width >= $this->size->width && $maxSize->height >= $this->size->height )
        {
            return $this->returnSelf( $internal );
        }

        $newSize = new Size( $this->size->width, $this->size->height );
        $newSize->contractToMaxSize( $maxSize );

        return $this->createImageAfterResize( $newSize, $internal );

    }

    /**
     * Shrinks this image so it fits the defined size, with holding the image proportion.
     *
     * @param Size $landscapeMaxSize                  This is the maximum size of this image after resizing if this
     *                                                image is in landscape format.
     * @param Size $portraitMaxSize                   This is the maximum size of this image after resizing if this
     *                                                image is in portrait format or quadratic.
     * @param bool $internal                          Handle the action internally? Otherwise a new instance is
     *                                                returned.
     *
     * @return IImage
     */
    public function shrinkIntoByFormat( Size $landscapeMaxSize, Size $portraitMaxSize, bool $internal = true )
    {

        // Is current format a portrait format? (or quadratic)
        if ( $this->size->isPortrait() )
        {
            return $this->shrinkInto( $portraitMaxSize, $internal );
        }

        return $this->shrinkInto( $landscapeMaxSize, $internal );

    }

    # </editor-fold>

    # <editor-fold desc=" P L A C I N G ">

    /**
     * Places the defined $placement image at declared $point inside current image.
     *
     * @param IImage|string|resource $placement                        The image that should be placed. It can be a
     *                                                                 IImage, a string pointing to the path of a image
     *                                                                 file, or a PHP GD image resource handle.
     * @param Point                  $point                            The point where the image should be placed
     *                                                                 inside this image.
     * @param int                    $opacity                          The opacity of the placed image in % (1-100)
     * @param bool                   $internal                         Handle the action internally? Otherwise a new
     *                                                                 instance is returned.
     *
     * @return GdImage
     * @throws ArgumentException if $placement is a unknown format
     * @throws FileAccessException
     * @throws FileNotFoundException
     */
    public function place( $placement, Point $point, int $opacity = 100, bool $internal = true )
    {

        $gdPlacement = $this->placementToGdImage( $placement );

        if ( null === $gdPlacement )
        {
            throw new ArgumentException(
                'placement',
                $placement,
                'Placement image must be a GdImage instance if base image is a GdImage instance!'
            );
        }

        if ( !$internal )
        {
            $res = clone $this;
            if ( $gdPlacement->canUseTransparency() && $opacity < 100 )
            {
                imagecopymerge(
                    $res->r, $gdPlacement->r, $point->x, $point->y, 0, 0,
                    $gdPlacement->getWidth(), $gdPlacement->getHeight(), $opacity
                );
            }
            else
            {
                imagecopy(
                    $res->r,
                    $gdPlacement->r,
                    $point->x,
                    $point->y,
                    0,
                    0,
                    $gdPlacement->getWidth(),
                    $gdPlacement->getHeight()
                );
            }

            return $res;
        }

        if ( $gdPlacement->canUseTransparency() && $opacity < 100 )
        {
            imagecopymerge(
                $this->r,
                $gdPlacement->r,
                $point->x,
                $point->y,
                0,
                0,
                $gdPlacement->getWidth(),
                $gdPlacement->getHeight(),
                $opacity
            );
        }
        else
        {
            imagecopy(
                $this->r,
                $gdPlacement->r,
                $point->x,
                $point->y,
                0,
                0,
                $gdPlacement->getWidth(),
                $gdPlacement->getHeight()
            );
        }

        return $this;

    }

    /**
     * Places the defined $placement image with defined gravity and padding inside this image.
     *
     * @param IImage|string|resource $placement                        The image that should be placed. It can be a
     *                                                                 IImage, a string pointing to the path of a image
     *                                                                 file, or a PHP GD image resource handle.
     * @param int                    $padding                          The padding. (0 means none)
     * @param mixed                  $gravity                          The placement gravity (see \GRAVITY_* constants)
     * @param int                    $opacity                          The opacity of the placed image in % (1-100)
     * @param bool                   $internal                         Handle the action internally? Otherwise a new
     *                                                                 instance is returned.
     *
     * @return IImage
     * @throws ArgumentException if $placement is a unknown format
     * @throws FileAccessException
     * @throws FileNotFoundException
     */
    public function placeByGravity(
        $placement, int $padding, $gravity = GRAVITY_BOTTOMRIGHT, int $opacity = 100, bool $internal = true )
    {

        $gdPlacement = $this->placementToGdImage( $placement );

        if ( null === $gdPlacement )
        {
            throw new ArgumentException(
                'placement',
                $placement,
                'Placement image must be a GdImage instance if base image is a GdImage instance!'
            );
        }

        switch ( $gravity )
        {
            case GRAVITY_TOPLEFT:
                $x = $padding;
                $y = $padding;
                break;
            case GRAVITY_TOP:
                $x = intval( floor( ( $this->getWidth() / 2 ) - ( $gdPlacement->getWidth() / 2 ) ) );
                $y = $padding;
                break;
            case GRAVITY_TOPRIGHT:
                $x = $this->getWidth() - $gdPlacement->getWidth() - $padding;
                $y = $padding;
                break;
            case GRAVITY_LEFT:
                $x = $padding;
                $y = intval( floor( ( $this->getHeight() / 2 ) - ( $gdPlacement->getHeight() / 2 ) ) );
                break;
            case GRAVITY_RIGHT:
                $x = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                $y = intval( floor( ( $this->getHeight() / 2 ) - ( $gdPlacement->getHeight() / 2 ) ) );
                break;
            case GRAVITY_BOTTOMLEFT:
                $x = $padding;
                $y = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                break;
            case GRAVITY_BOTTOM:
                $x = intval( floor( ( $this->getWidth() / 2 ) - ( $gdPlacement->getWidth() / 2 ) ) );
                $y = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                break;
            case GRAVITY_BOTTOMRIGHT:
                $x = $this->getWidth() - $gdPlacement->getWidth() - $padding;
                $y = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                break;
            default:
                # case \GRAVITY_CENTER:
                $x = intval( floor( ( $this->getWidth() / 2 ) - ( $gdPlacement->getWidth() / 2 ) ) );
                $y = intval( floor( ( $this->getHeight() / 2 ) - ( $gdPlacement->getHeight() / 2 ) ) );
                break;
        }

        return $this->place( $gdPlacement, new Point( $x, $y ), $opacity, $internal );

    }

    /**
     * Places the defined $placement image with defined alignment and padding inside this image.
     *
     * @param IImage|string|resource $placement                        The image that should be placed. It can be a
     *                                                                 IImage, a string pointing to the path of a image
     *                                                                 file, or a PHP GD image resource handle.
     * @param int                    $padding                          The padding. (0 means none)
     * @param ContentAlign           $align                            The content align of the image that should be
     *                                                                 placed.
     * @param int                    $opacity                          The opacity of the placed image in % (1-100)
     * @param bool                   $internal                         Handle the action internally? Otherwise a new
     *                                                                 instance is returned.
     *
     * @return GdImage
     * @throws ArgumentException if $placement is a unknown format
     * @throws FileAccessException
     * @throws FileNotFoundException
     */
    public function placeByAlign(
        $placement, int $padding, ContentAlign $align, int $opacity = 100, bool $internal = true )
    {

        $gdPlacement = $this->placementToGdImage( $placement );

        if ( null === $gdPlacement )
        {
            throw new ArgumentException(
                'placement',
                $placement,
                'Placement image must be a GdImage instance if base image is a GdImage instance!'
            );
        }

        # <editor-fold defaultstate="collapsed" desc="X + Y zuweisen">
        switch ( $align->Value )
        {
            case ContentAlign::TOP_LEFT:
                $x = $padding;
                $y = $padding;
                break;
            case ContentAlign::TOP:
                $x = intval( floor( ( $this->getWidth() / 2 ) - ( $gdPlacement->getWidth() / 2 ) ) );
                $y = $padding;
                break;
            case ContentAlign::TOP_RIGHT:
                $x = $this->getWidth() - $gdPlacement->getWidth() - $padding;
                $y = $padding;
                break;
            case ContentAlign::MIDDLE_LEFT:
                $x = $padding;
                $y = intval( floor( ( $this->getHeight() / 2 ) - ( $gdPlacement->getHeight() / 2 ) ) );
                break;
            case ContentAlign::MIDDLE_RIGHT:
                $x = $this->getWidth() - $gdPlacement->getWidth() - $padding;
                $y = intval( floor( ( $this->getHeight() / 2 ) - ( $gdPlacement->getHeight() / 2 ) ) );
                break;
            case ContentAlign::BOTTOM_LEFT:
                $x = $padding;
                $y = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                break;
            case ContentAlign::BOTTOM:
                $x = intval( floor( ( $this->getWidth() / 2 ) - ( $gdPlacement->getWidth() / 2 ) ) );
                $y = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                break;
            case ContentAlign::BOTTOM_RIGHT:
                $x = $this->getWidth() - $gdPlacement->getWidth() - $padding;
                $y = $this->getHeight() - $gdPlacement->getHeight() - $padding;
                break;
            default:
                # case \GRAVITY_CENTER:
                $x = intval( floor( ( $this->getWidth() / 2 ) - ( $gdPlacement->getWidth() / 2 ) ) );
                $y = intval( floor( ( $this->getHeight() / 2 ) - ( $gdPlacement->getHeight() / 2 ) ) );
                break;
        }

        # </editor-fold>

        return $this->place( $gdPlacement, new Point( $x, $y ), $opacity, $internal );

    }

    # </editor-fold>

    # <editor-fold desc=" B O R D E R I N G ">

    /**
     * Draw a single border around the image, with defined color (width=1px).
     *
     * @param string|array|ColorGD|Color $borderColor
     * @param bool                       $internal                                   Handle the action internally?
     *                                                                               Otherwise a new instance is
     *                                                                               returned.
     *
     * @return IImage
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    public function drawSingleBorder( $borderColor, bool $internal = true )
    {

        $borderColor = $this->getGdColorObject( $borderColor, 'bordercolor' );
        if ( $internal )
        {
            imagerectangle( $this->r, 1, 1, $this->getWidth() - 2, $this->getHeight() - 2,
                             $borderColor->getGdValue() );

            return $this;
        }
        $res = clone $this;
        imagerectangle( $res->r, 1, 1, $this->getWidth() - 2, $this->getHeight() - 2, $borderColor->getGdValue() );

        return $res;
    }

    /**
     * Draw a double border around the image, with defined colors (width=1px).
     *
     * @param string|array|ColorGD|Color $innerBorderColor
     * @param string|array|ColorGD|Color $outerBorderColor
     * @param bool                       $internal                                   Handle the action internally?
     *                                                                               Otherwise a new instance is
     *                                                                               returned.
     *
     * @return IImage
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    public function drawDoubleBorder( $innerBorderColor, $outerBorderColor, bool $internal = true )
    {

        $innerBorderColor = $this->getGdColorObject( $innerBorderColor, 'bordercolor' );
        $outerBorderColor = $this->getGdColorObject( $outerBorderColor, 'outerbordercolor' );
        if ( $internal )
        {
            imagerectangle( $this->r, 0, 0, $this->getWidth() - 1, $this->getHeight() - 1,
                             $outerBorderColor->getGdValue() );
            imagerectangle( $this->r, 1, 1, $this->getWidth() - 2, $this->getHeight() - 2,
                             $innerBorderColor->getGdValue() );

            return $this;
        }
        $res = clone $this;
        imagerectangle( $res->r, 0, 0, $this->getWidth() - 1, $this->getHeight() - 1,
                         $outerBorderColor->getGdValue() );
        imagerectangle( $res->r, 1, 1, $this->getWidth() - 2, $this->getHeight() - 2,
                         $innerBorderColor->getGdValue() );

        return $res;
    }

    # </editor-fold>

    # <editor-fold desc=" TEXT DRAWING">

    /**
     * Draws a text at a specific point (top left corner)
     *
     * @param string                     $text                                       The text that should be drawn
     * @param string                     $font                                       The font that should be used. For
     *                                                                               example with a GdImage, here the
     *                                                                               path of a *.ttf font file should
     *                                                                               be defined. If null here is used
     *                                                                               so the font will be defined
     *                                                                               depending to font size 1-4. If a
     *                                                                               *.ttf path here is used the font
     *                                                                               size should be declared in points.
     *                                                                               If Imagick is used here you only
     *                                                                               have to define the font name like
     *                                                                               'Arial'
     * @param int                        $fontSize                                   The font size
     * @param string|array|Color|ColorGD $color                                      The text color
     * @param Point                      $point                                      The top left corner of the
     *                                                                               bounding text box.
     * @param bool                       $internal                                   Handle the action internally?
     *                                                                               Otherwise a new instance is
     *                                                                               returned.
     *
     * @return IImage
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    public function drawText( string $text, $font, $fontSize, $color, Point $point, bool $internal = true )
    {

        $color = $this->getGdColorObject( $color, 'textcolor' );

        if ( !empty( $font ) && file_exists( $font ) )
        {
            if ( $internal )
            {
                imagettftext( $this->r, $fontSize, 0, $point->x, $point->y, $color->getGdValue(), $font, $text );

                return $this;
            }
            $res = clone $this;
            imagettftext( $res->r, $fontSize, 0, $point->x, $point->y, $color->getGdValue(), $font, $text );

            return $res;
        }

        if ( $internal )
        {
            imagestring( $this->r, $fontSize, $point->x, $point->y, $text, $color->getGdValue() );

            return $this;
        }

        $res = clone $this;
        imagestring( $res->r, $fontSize, $point->x, $point->y, $text, $color->getGdValue() );

        return $res;

    }

    /**
     * Draws a text with gravity and padding.
     *
     * @param string                     $text                                       The text that should be drawn
     * @param string                     $font                                       The font that should be used. For
     *                                                                               example with a GdImage, here the
     *                                                                               path of a *.ttf font file should
     *                                                                               be defined. If null here is used
     *                                                                               so the font will be defined
     *                                                                               depending to font size 1-4. If a
     *                                                                               *.ttf path here is used the font
     *                                                                               size should be declared in points.
     *                                                                               If Imagick is used here you only
     *                                                                               have to define the font name like
     *                                                                               'Arial'
     * @param int                        $fontSize                                   The font size
     * @param string|array|Color|ColorGD $color                                      The text color
     * @param int                        $padding                                    Padding of the text inside the
     *                                                                               text bounds.
     * @param mixed                      $gravity                                    The gravity of the text inside
     *                                                                               this image.
     * @param bool                       $internal                                   Handle the action internally?
     *                                                                               Otherwise a new instance is
     *                                                                               returned.
     *
     * @return IImage
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    public function drawTextWithGravity(
        string $text, $font, $fontSize, $color, int $padding, $gravity, bool $internal = true )
    {

        if ( !empty( $font ) && file_exists( $font ) )
        {
            return $this->drawText(
                $text,
                $font,
                $fontSize,
                $color,
                $this->imageTtfPoint( $fontSize, 0, $font, $text, $gravity, $this->size, $padding ),
                $internal
            );
        }

        if ( !is_int( $fontSize ) )
        {
            $fontSize = 2;
        }

        if ( $fontSize < 1 )
        {
            $fontSize = 1;
        }
        else if ( $fontSize > 4 )
        {
            $fontSize = 4;
        }

        $textSize = $this->imageMeasureString( $fontSize, $text );
        $textSize->height -= 2;
        $textSize->width += 1;
        $point = null;

        switch ( $gravity )
        {
            case GRAVITY_BOTTOMLEFT:
                $point = new Point( $padding, $this->getHeight() - $textSize->height - $padding );
                break;
            case GRAVITY_BOTTOM:
                $point = new Point(
                    intval( floor( ( $this->getWidth() / 2 ) - ( $textSize->width / 2 ) ) ),
                    $this->getHeight() - $textSize->height - $padding
                );
                break;
            case GRAVITY_BOTTOMRIGHT:
                $point = new Point(
                    $this->getWidth() - $textSize->width - $padding,
                    $this->getHeight() - $textSize->height - $padding
                );
                break;
            case GRAVITY_LEFT:
                $point = new Point(
                    $padding,
                    intval( floor( ( $this->getHeight() / 2 ) - ( $textSize->height / 2 ) ) )
                );
                break;
            case GRAVITY_RIGHT:
                $point = new Point(
                    $this->getWidth() - $textSize->width - $padding,
                    intval( floor( ( $this->getHeight() / 2 ) - ( $textSize->height / 2 ) ) )
                );
                break;
            case GRAVITY_TOPLEFT:
                $point = new Point( $padding, $padding );
                break;
            case GRAVITY_TOP:
                $point = new Point(
                    intval( floor( ( $this->getWidth() / 2 ) - ( $textSize->width / 2 ) ) ),
                    $padding );
                break;
            case GRAVITY_TOPRIGHT:
                $point = new Point( $this->getWidth() - $textSize->width - $padding, $padding );
                break;
            default:
                #case \GRAVITY_CENTER:
                $point = new Point(
                    intval( floor( ( $this->getWidth() / 2 ) - ( $textSize->width / 2 ) ) ),
                    intval( floor( ( $this->getHeight() / 2 ) - ( $textSize->height / 2 ) ) )
                );
                break;
        }

        return $this->drawText( $text, $font, $fontSize, $color, $point, $internal );

    }

    /**
     * Draws a text with alignment and padding.
     *
     * @param string                     $text                                       The text that should be drawn
     * @param string                     $font                                       The font that should be used. For
     *                                                                               example with a GdImage, here the
     *                                                                               path of a *.ttf font file should
     *                                                                               be defined. If null here is used
     *                                                                               so the font will be defined
     *                                                                               depending to font size 1-4. If a
     *                                                                               *.ttf path here is used the font
     *                                                                               size should be declared in points.
     *                                                                               If Imagick is used here you only
     *                                                                               have to define the font name like
     *                                                                               'Arial'
     * @param int                        $fontSize                                   The font size
     * @param string|array|Color|ColorGD $color                                      The text color
     * @param int                        $padding                                    Padding of the text inside the
     *                                                                               text bounds.
     * @param ContentAlign               $align                                      The align of the text inside this
     *                                                                               image
     * @param bool                       $internal                                   Handle the action internally?
     *                                                                               Otherwise a new instance is
     *                                                                               returned.
     *
     * @return IImage
     * @throws ArgumentException
     * @throws NiirrtyException
     */
    public function drawTextWithAlign(
        string $text, $font, $fontSize, $color, int $padding, ContentAlign $align, bool $internal = true )
    {

        if ( !empty( $font ) && file_exists( $font ) )
        {
            return $this->drawText(
                $text,
                $font,
                $fontSize,
                $color,
                $this->imageTtfPoint2( $fontSize, 0, $font, $text, $align, $this->size, $padding ),
                $internal
            );
        }

        if ( !is_int( $fontSize ) )
        {
            $fontSize = 2;
        }

        if ( $fontSize < 1 )
        {
            $fontSize = 1;
        }
        else if ( $fontSize > 4 )
        {
            $fontSize = 4;
        }

        $textSize = $this->imageMeasureString( $fontSize, $text );
        $textSize->height -= 2;
        $textSize->width += 1;
        $point = null;

        switch ( $align->Value )
        {
            case ContentAlign::BOTTOM_LEFT:
                $point = new Point( $padding, $this->getHeight() - $textSize->height - $padding );
                break;
            case ContentAlign::BOTTOM:
                $point = new Point(
                    intval( floor( ( $this->getWidth() / 2 ) - ( $textSize->width / 2 ) ) ),
                    $this->getHeight() - $textSize->height - $padding
                );
                break;
            case ContentAlign::BOTTOM_RIGHT:
                $point = new Point(
                    $this->getWidth() - $textSize->width - $padding,
                    $this->getHeight() - $textSize->height - $padding
                );
                break;
            case ContentAlign::MIDDLE_LEFT:
                $point = new Point(
                    $padding,
                    intval( floor( ( $this->getHeight() / 2 ) - ( $textSize->height / 2 ) ) )
                );
                break;
            case ContentAlign::MIDDLE_RIGHT:
                $point = new Point(
                    $this->getWidth() - $textSize->width - $padding,
                    intval( floor( ( $this->getHeight() / 2 ) - ( $textSize->height / 2 ) ) )
                );
                break;
            case ContentAlign::TOP_LEFT:
                $point = new Point( $padding, $padding );
                break;
            case ContentAlign::TOP:
                $point = new Point(
                    intval( floor( ( $this->getWidth() / 2 ) - ( $textSize->width / 2 ) ) ),
                    $padding
                );
                break;
            case ContentAlign::TOP_RIGHT:
                $point = new Point(
                    $this->getWidth() - $textSize->width - $padding,
                    $padding
                );
                break;
            default:
                $point = new Point(
                    intval( floor( ( $this->getWidth() / 2 ) - ( $textSize->width / 2 ) ) ),
                    intval( floor( ( $this->getHeight() / 2 ) - ( $textSize->height / 2 ) ) )
                );
                break;
        }

        return $this->drawText( $text, $font, $fontSize, $color, $point, $internal );

    }

    # </editor-fold>

    # </editor-fold>


    # <editor-fold desc="= = =   P R I V A T E   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * @param integer|float $size Schriftgroesse
     * @param string        $text Zu vermessende Zeichenkette
     *
     * @return Size
     */
    private function imageMeasureString( $size, string $text ): Size
    {

        return new Size(
            $this->charSizes[ intval( $size ) ][ 0 ] * strlen( $text ),
            $this->charSizes[ intval( $size ) ][ 1 ]
        );

    }

    /**
     * Ermittelt den Punkt, an dem ein Text, formatiert mit einem
     * anzugebenden TTF-Font, plaziert werden muss, damit er im Bild in dem
     * er eigefügt wird die angegebene Ausrichtung $gravity hat.
     *
     * @param int    $size                      Schriftgröße
     * @param int    $angle                     Winkel (0 ist normal von links nach rechts)
     * @param string $fontfile                  Pfad zur zu nutzenden TTF Fontdatei
     * @param string $text                      Text der vermessen werden soll.
     * @param mixed  $gravity                   Ausrichtung im Bild in das der Text plaziert
     *                                          werden soll (Siehe Konstanten \GRAVITY_*).
     * @param Size   $targetSize                Größe des Bildes in dem der Text
     *                                          platziert werden soll.
     * @param int    $padding
     *
     * @return Point Punkt an dem der Text mit der definierten
     *               Ausrichtung platziert werden soll.
     */
    private function imageTtfPoint(
        int $size, int $angle, string $fontfile, string $text, $gravity, Size $targetSize, int $padding ): Point
    {

        $coords = imagettfbbox( $size, 0, $fontfile, $text );

        $a = deg2rad( $angle );
        $ca = cos( $a );
        $sa = sin( $a );
        $b = [];

        for ( $i = 0; $i < 7; $i += 2 )
        {
            $b[ $i ] = round( $coords[ $i ] * $ca + $coords[ $i + 1 ] * $sa );
            $b[ $i + 1 ] = round( $coords[ $i + 1 ] * $ca - $coords[ $i ] * $sa );
        }

        $x = $b[ 4 ] - $b[ 6 ];
        $y = $b[ 5 ] - $b[ 7 ];
        $width = sqrt( pow( $x, 2 ) + pow( $y, 2 ) );
        $x = $b[ 0 ] - $b[ 6 ];
        $y = $b[ 1 ] - $b[ 7 ];
        $height = sqrt( pow( $x, 2 ) + pow( $y, 2 ) );

        switch ( $gravity )
        {

            case GRAVITY_BOTTOMRIGHT:
                #echo "\GRAVITY_BOTTOMRIGHT $gravity\n";
                return new Point(
                    $targetSize->width - $width - $b[ 0 ] - $padding,
                    $targetSize->height - $height - $b[ 5 ] - 1 - $padding
                );

            case GRAVITY_BOTTOM:
                #echo "\GRAVITY_BOTTOM $gravity\n";
                return new Point(
                    (int) ( ( $targetSize->width / 2 ) - ( $width / 2 ) ),
                    ( $targetSize->height - $height - $b[ 5 ] ) - 1 - $padding
                );

            case GRAVITY_BOTTOMLEFT:
                #echo "\GRAVITY_BOTTOMLEFT $gravity\n";
                return new Point( $padding, $targetSize->height - $height - $b[ 5 ] - 1 - $padding );

            case GRAVITY_LEFT:
                #echo "\GRAVITY_LEFT: $gravity\n";
                return new Point( $padding, (int) ( $targetSize->height / 2 ) - (int) ( $height / 2 ) - $b[ 5 ] - 1 );

            case GRAVITY_RIGHT:
                #echo "\GRAVITY_RIGHT: $gravity\n";
                return new Point(
                    $targetSize->width - $width + $b[ 0 ] - $padding,
                    (int) ( $targetSize->height / 2 ) - (int) ( $height / 2 ) - $b[ 5 ] - 1
                );

            case GRAVITY_TOPLEFT:
                #echo "\GRAVITY_TOPLEFT: $gravity\n";
                return new Point( $padding, 0 - $b[ 5 ] + $padding );

            case GRAVITY_TOP:
                #echo "\GRAVITY_TOP: $gravity\n";
                return new Point(
                    (int) ( $targetSize->width / 2 ) - (int) ( $width / 2 ),
                    0 - $b[ 5 ] + $padding
                );

            case GRAVITY_TOPRIGHT:
                #echo "\GRAVITY_TOPRIGHT: $gravity\n";
                return new Point(
                    $targetSize->width - $width + $b[ 0 ] - $padding,
                    0 - $b[ 5 ] + $padding
                );

            default:
                #case \GRAVITY_CENTER:
                return new Point(
                    (int) ( $targetSize->width / 2 ) - (int) ( $width / 2 ),
                    (int) ( $targetSize->height / 2 ) - (int) ( $height / 2 ) - $b[ 5 ] - 1
                );

        }

    }

    private function imageTtfPoint2(
        int $size, int $angle, string $fontfile, string $text, ContentAlign $align, Size $targetSize,
        int $padding ): Point
    {

        $coords = imagettfbbox( $size, 0, $fontfile, $text );
        $a = deg2rad( $angle );
        $ca = cos( $a );
        $sa = sin( $a );
        $b = [];

        for ( $i = 0; $i < 7; $i += 2 )
        {
            $b[ $i ] = round( $coords[ $i ] * $ca + $coords[ $i + 1 ] * $sa );
            $b[ $i + 1 ] = round( $coords[ $i + 1 ] * $ca - $coords[ $i ] * $sa );
        }

        $x = $b[ 4 ] - $b[ 6 ];
        $y = $b[ 5 ] - $b[ 7 ];
        $width = sqrt( pow( $x, 2 ) + pow( $y, 2 ) );
        $x = $b[ 0 ] - $b[ 6 ];
        $y = $b[ 1 ] - $b[ 7 ];
        $height = sqrt( pow( $x, 2 ) + pow( $y, 2 ) );

        switch ( $align->Value )
        {

            case ContentAlign::BOTTOM_RIGHT:
                return new Point(
                    $targetSize->width - $width - $b[ 0 ] - $padding,
                    $targetSize->height - $height - $b[ 5 ] - 1 - $padding
                );

            case ContentAlign::BOTTOM:
                return new Point(
                    (int) ( ( $targetSize->width / 2 ) - ( $width / 2 ) ),
                    ( $targetSize->height - $height - $b[ 5 ] ) - 1 - $padding
                );

            case ContentAlign::BOTTOM_LEFT:
                return new Point( $padding, $targetSize->height - $height - $b[ 5 ] - 1 - $padding );

            case ContentAlign::MIDDLE_LEFT:
                return new Point(
                    $padding,
                    (int) ( $targetSize->height / 2 ) - (int) ( $height / 2 ) - $b[ 5 ] - 1
                );

            case ContentAlign::MIDDLE_RIGHT:
                return new Point(
                    $targetSize->width - $width + $b[ 0 ] - $padding,
                    (int) ( $targetSize->height / 2 ) - (int) ( $height / 2 ) - $b[ 5 ] - 1
                );

            case ContentAlign::TOP_LEFT:
                return new Point( $padding, 0 - $b[ 5 ] + $padding );

            case ContentAlign::TOP:
                return new Point(
                    (int) ( $targetSize->width / 2 ) - (int) ( $width / 2 ),
                    0 - $b[ 5 ] + $padding
                );

            case ContentAlign::TOP_RIGHT:
                return new Point(
                    $targetSize->width - $width + $b[ 0 ] - $padding,
                    0 - $b[ 5 ] + $padding
                );

            default:
                return new Point(
                    (int) ( $targetSize->width / 2 ) - (int) ( $width / 2 ),
                    (int) ( $targetSize->height / 2 ) - (int) ( $height / 2 ) - $b[ 5 ] - 1
                );

        }

    }

    /**
     * @param int $width
     * @param int $height
     *
     * @throws ArgumentException
     */
    private function cropCheck( int &$width, int &$height )
    {

        if ( $width <= 0 )
        {
            throw new ArgumentException(
                'width',
                $width,
                'Croping of a image fails.'
            );
        }

        if ( $height <= 0 )
        {
            throw new ArgumentException(
                'height',
                $height,
                'Croping of a image fails.'
            );
        }

        if ( $width > $this->size->width )
        {
            $width = $this->size->width;
        }

        if ( $height > $this->size->height )
        {
            $height = $this->size->height;
        }

    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    private function hasSameSize( int $width, int $height )
    {

        return ( $width == $this->size->width && $height == $this->size->height );

    }

    /**
     * @param Size $newSize
     * @param bool $internal
     *
     * @return $this|GdImage
     */
    private function createImageAfterResize( Size $newSize, bool $internal )
    {

        if ( $this->isTruecolor() )
        {
            $dst = imagecreatetruecolor( $newSize->width, $newSize->height );
        }
        else
        {
            $dst = imagecreate( $newSize->width, $newSize->height );
        }

        if ( $this->canUseTransparency() )
        {
            imagealphablending( $dst, false );
            imagesavealpha( $dst, true );
        }

        imagecopyresampled( $dst, $this->r, 0, 0, 0, 0, $newSize->width, $newSize->height, $this->getWidth(),
                             $this->getHeight() );

        if ( $internal )
        {
            imagedestroy( $this->r );
            $this->r = $dst;
            $this->size->width = $newSize->width;
            $this->size->height = $newSize->height;

            return $this;
        }

        return new GdImage(
            $dst,
            $newSize,
            $this->mimeType,
            $this->file
        );

    }

    /**
     * @param bool $internal
     *
     * @return $this|GdImage
     */
    private function returnSelf( bool $internal )
    {

        if ( $internal )
        {
            return $this;
        }

        return clone $this;

    }

    /**
     * @param $placement
     *
     * @return GdImage|null
     * @throws FileAccessException
     * @throws FileNotFoundException
     */
    private function placementToGdImage( $placement ): ?GdImage
    {

        if ( $placement instanceof GdImage )
        {
            return $placement;
        }

        if ( is_string( $placement ) )
        {
            if ( !preg_match( '~^[A-Za-z0-9_.' . ( IS_WIN ? ':\\\\/' : '/' ) . ',!$\~+ -]+$~', $placement ) ||
                 !is_file( $placement ) )
            {
                return null;
            }

            return GdImage::LoadFile( $placement );
        }

        if ( is_resource( $placement ) && Size::TryParse( $placement, $size ) )
        {
            return new GdImage( $placement, $size, 'image/png' );
        }

        if ( $placement instanceof IImage )
        {
            $tmpFile = tempnam( sys_get_temp_dir(), 'gdimage-placement' );
            if ( $placement->isPng() )
            {
                $tmpFile .= '.png';
            }
            else if ( $placement->isGif() )
            {
                $tmpFile .= '.gif';
            }
            else
            {
                $tmpFile .= '.jpg';
            }
            $placement->save( $tmpFile );

            return GdImage::LoadFile( $tmpFile );
        }

        return null;

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

    /**
     * Creates a new image with defined dimensions and declared type.
     *
     * @param int          $width       The image width.
     * @param int          $height      The image height.
     * @param string|array $backColor   Image background color
     * @param string       $type        The image mime type. e.g.:: 'image/jpeg'
     * @param bool         $transparent Should a GIF or PNG image become a transparent background?
     *
     * @return GdImage
     * @throws ArgumentException|NiirrtyException If the image type is invalid/unknown.
     */
    public static function Create(
        int $width, int $height, $backColor, string $type = 'image/gif', bool $transparent = false ): GdImage
    {

        $img = imagecreatetruecolor( $width, $height );

        if ( false === ( $rgb = ColorTool::Color2Rgb( $backColor ) ) )
        {
            $rgb = [ 0, 0, 0 ];
        }

        $bgColor = null;
        $mime = null;

        switch ( $type )
        {

            case 'image/gif':
                imagealphablending( $img, false );
                if ( $transparent )
                {
                    $bgColor = imagecolorallocatealpha( $img, $rgb[ 0 ], $rgb[ 1 ], $rgb[ 2 ], 127 );
                }
                else
                {
                    $bgColor = imagecolorallocate( $img, $rgb[ 0 ], $rgb[ 1 ], $rgb[ 2 ] );
                }
                imagefill( $img, 0, 0, $bgColor );
                $mime = 'image/gif';
                break;

            case 'image/png':
                imagealphablending( $img, false );
                if ( $transparent )
                {
                    $bgColor = imagecolorallocatealpha( $img, $rgb[ 0 ], $rgb[ 1 ], $rgb[ 2 ], 127 );
                }
                else
                {
                    $bgColor = imagecolorallocate( $img, $rgb[ 0 ], $rgb[ 1 ], $rgb[ 2 ] );
                }
                imagesavealpha( $img, true );
                imagefill( $img, 0, 0, $bgColor );
                $mime = 'image/png';
                break;

            default:
                $mime = 'image/jgep';
                $bgColor = imagecolorallocate( $img, $rgb[ 0 ], $rgb[ 1 ], $rgb[ 2 ] );
                imagefill( $img, 0, 0, $bgColor );
                break;

        }

        $result = new GdImage( $img, new Size( $width, $height ), $mime );
        $result->addUserColor( 'background', $bgColor );

        return $result;

    }

    /**
     * Loads a GdImage from a image file.
     *
     * @param string $imageFile
     *
     * @return GdImage
     * @throws FileNotFoundException
     * @throws FileAccessException
     */
    public static function LoadFile( string $imageFile ): GdImage
    {

        if ( !file_exists( $imageFile ) )
        {
            throw new FileNotFoundException(
                $imageFile, 'Loading a \Niirrty\Drawing\Image\GdImage Resource from this file fails.'
            );
        }

        $imageInfo = null;

        try
        {
            if ( false === ( $imageInfo = getimagesize( $imageFile ) ) )
            {
                throw new Exception( 'Defined imagefile uses a unknown file format!' );
            }
        }
        catch ( Exception $ex )
        {
            throw new FileAccessException(
                $imageFile,
                FileAccessException::ACCESS_READ,
                $ex->getMessage()
            );
        }

        $img = null;
        $mime = null;

        switch ( $imageInfo[ 'mime' ] )
        {

            case 'image/png':
                $img = imagecreatefrompng( $imageFile );
                $mime = 'image/png';
                break;

            case 'image/gif':
                $img = imagecreatefromgif( $imageFile );
                $mime = 'image/gif';
                break;

            default:
                $img = imagecreatefromjpeg( $imageFile );
                $mime = 'image/jpeg';
                break;

        }

        return new GdImage(
            $img,
            new Size( $imageInfo[ 0 ], $imageInfo[ 1 ] ),
            $mime,
            $imageFile
        );

    }


    # </editor-fold>


}

