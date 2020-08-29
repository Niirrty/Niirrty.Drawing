<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Loader
 * @since      2017-11-02
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Drawing\Image\Exif\Loader;


use Niirrty\ArgumentException;
use Niirrty\Drawing\Image\Exif\ImageInfo;
use Throwable;
use function chdir;
use function count;
use function explode;
use function file_exists;
use function is_dir;
use function is_executable;
use function is_readable;
use function Niirrty\strContains;
use function preg_match;
use function rtrim;
use function str_replace;
use function trim;
use const DIRECTORY_SEPARATOR;
use const IS_WIN;
use const PATH_SEPARATOR;


/**
 * EXIF data loader by using the exif-tool binary
 *
 * @since v0.1.0
 */
class ExifTool implements ILoader
{


    # <editor-fold desc="= = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * The path of the exiftool binary.
     *
     * @var string
     */
    private $path;

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     */
    public function __construct()
    {

        $this->path = $this->find();

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P R I V A T E   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

    private function find()
    {

        $file = 'exiftool';

        if ( is_executable( $file ) )
        {
            return $file;
        }

        if ( IS_WIN )
        {

            $file2 = 'exiftool.exe';

            $path = 'C:\\Windows\\' . $file;
            if ( is_executable( $path ) )
            {
                return $path;
            }

            $path = 'C:\\Windows\\' . $file2;
            if ( is_executable( $path ) )
            {
                return $path;
            }

            $path = 'C:\\Program Files\\exiftool\\' . $file;
            if ( is_executable( $path ) )
            {
                return $path;
            }

            $path = 'C:\\Program Files\\exiftool\\' . $file2;
            if ( is_executable( $path ) )
            {
                return $path;
            }

            // Get By Environment paths

            $envPath = isset( $_ENV[ 'PATH' ] )
                ? $_ENV[ 'PATH' ]
                : ( isset( $_ENV[ 'Path' ] )
                    ? $_ENV[ 'Path' ]
                    : null
                );

            if ( !empty( $envPath ) )
            {
                $pathElements = explode( PATH_SEPARATOR, $envPath );
                foreach ( $pathElements as $pathElement )
                {
                    if ( ( '.' === $pathElement ) || ( '..' === $pathElement ) )
                    {
                        continue;
                    }
                    if ( !is_dir( $pathElement ) )
                    {
                        continue;
                    }
                    $path = rtrim( $pathElement, '\\/' ) . DIRECTORY_SEPARATOR . $file;
                    if ( is_executable( $path ) )
                    {
                        return $path;
                    }
                    $path = rtrim( $pathElement, '\\/' ) . DIRECTORY_SEPARATOR . $file2;
                    if ( is_executable( $path ) )
                    {
                        return $path;
                    }
                }
            }

            $old = chdir( 'C:\\' );
            $lines = explode( "\n", str_replace( "\r\n", "\n", trim( `dir $file2 /s 2>&1` ) ) );
            if ( \is_string( $old ) ) { chdir( $old ); }
            $found = [ 'primary' => '', 'programs' => '' ];
            $m = null;
            for ( $i = 0; $i < count( $lines ); ++$i )
            {
                if ( !empty( $found[ 'primary' ] ) && !empty( $found[ 'programs' ] ) )
                {
                    break;
                }
                $line = trim( $lines[ $i ] );
                if ( '' === $line )
                {
                    continue;
                }
                if ( empty( $found[ 'primary' ] ) && preg_match( '~(c:\\\\.+?exiftool)$~i', $line, $m ) )
                {
                    $found[ 'primary' ] = rtrim( $m[ 1 ], '\\' ) . '\\' . $file;
                    continue;
                }
                if ( empty( $found[ 'programs' ] ) && preg_match( '~(c:\\\\program.+)$~i', $line, $m ) )
                {
                    $found[ 'programs' ] = rtrim( $m[ 1 ], '\\' ) . '\\' . $file;
                }
            }
            if ( !empty( $found[ 'primary' ] ) )
            {
                return $found[ 'primary' ];
            }
            if ( !empty( $found[ 'programs' ] ) )
            {
                return $found[ 'programs' ];
            }

            return null;
        }

        $path = '/usr/bin/' . $file;
        if ( is_executable( $path ) )
        {
            return $path;
        }

        $path = '/bin/' . $file;
        if ( is_executable( $path ) )
        {
            return $path;
        }

        return null;

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Sets the loader config values. The only accepted values is 'path' (the exif-tool path)
     *
     * @param array $configData 'path' = exif-tool binary path
     *
     * @throws ArgumentException
     */
    public function configure( array $configData )
    {

        if ( empty( $configData[ 'path' ] ) )
        {
            throw new ArgumentException(
                '$configData',
                $configData,
                "Missing 'path' for exiftool executable"
            );
        }

        if ( !file_exists( $configData[ 'path' ] ) )
        {
            if ( !is_executable( $configData[ 'path' ] ) )
            {
                throw new ArgumentException(
                    'configData[\'path\']',
                    $configData[ 'path' ],
                    "Defined 'path' dont points to a exiftool executable!"
                );
            }
            $this->path = $configData[ 'path' ];
        }
        else if ( !is_executable( $configData[ 'path' ] ) )
        {
            throw new ArgumentException(
                'configData[\'path\']',
                $configData[ 'path' ],
                "Defined 'path' dont points to a exiftool executable!"
            );
        }
        else
        {
            $this->path = $configData[ 'path' ];
        }

    }


    /**
     * @inheritdoc
     */
    public function isConfigured()
    {

        return !empty( $this->path );

    }

    /**
     * @inheritdoc
     */
    public function load( $imageFile ): ?ImageInfo
    {

        if ( !$this->isConfigured() )
        {
            return null;
        }

        if ( !file_exists( $imageFile ) )
        {
            return null;
        }

        if ( !is_readable( $imageFile ) )
        {
            return null;
        }

        $file = strContains( $imageFile, ' ' ) ? ( '"' . $imageFile . '"' ) : $imageFile;

        try
        {

            $output = `$this->path $file`;
            $lines = explode( "\n", str_replace( [ "\r\n", "\r" ], [ "\n", "\n" ], $output ) );
            $array = [];
            for ( $i = 0; $i < count( $lines ); ++$i )
            {
                $line = trim( $lines[ $i ] );
                if ( '' === $line )
                {
                    continue;
                }
                $tmp = explode( ':', $line, 2 );
                if ( count( $tmp ) != 2 )
                {
                    continue;
                }
                $tmp[ 0 ] = trim( $tmp[ 0 ] );
                if ( isset( $array[ $tmp[ 0 ] ] ) )
                {
                    continue;
                }
                $array[ $tmp[ 0 ] ] = trim( $tmp[ 1 ] );
            }

            return new ImageInfo( $array, $imageFile );

        }
        catch ( Throwable $ex )
        {

            return null;

        }

    }


    # </editor-fold>


}

