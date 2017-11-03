<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Ni Irrty
 * @package    Niirrty\Drawing\Image\Exif\Loader
 * @since      2017-11-02
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing\Image\Exif\Loader;


use Niirrty\ArgumentException;
use Niirrty\Drawing\Image\Exif\ImageInfo;


/**
 * EXIF data loader by using the exif-tool binary
 *
 * @since v0.1.0
 */
class ExiV2 implements ILoader
{


   # <editor-fold desc="= = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">


   /**
    * The exiv2 binary path.
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


   # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Sets the loader config values. The only accepted values is 'path' (the exiV2 path)
    *
    * @param  array $configData 'path' = exiV2 binary path
    * @throws \Niirrty\ArgumentException
    */
   public function configure( array $configData )
   {

      if ( empty( $configData[ 'path' ] ) )
      {
         throw new ArgumentException(
            'configData',
            $configData,
            "Missing 'path' for exiv2 executable"
         );
      }
      if ( ! \file_exists( $configData[ 'path' ] ) )
      {
         if ( ! \is_executable( $configData[ 'path' ] ) )
         {
            throw new ArgumentException(
               'configData[\'path\']',
               $configData[ 'path' ],
               "Defined 'path' dont points to a exiv2 executable!"
            );
         }
         $this->path = $configData[ 'path' ];
      }
      else if ( ! \is_executable( $configData[ 'path' ] ) )
      {
         throw new ArgumentException(
            'configData[\'path\']',
            $configData[ 'path' ],
            "Defined 'path' dont points to a exiv2 executable!"
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

      return ! empty( $this->path );

   }

   /**
    * @inheritdoc
    */
   public function load( $imageFile ) : ?ImageInfo
   {

      if ( ! $this->isConfigured() )
      {
         return null;
      }

      if ( ! \file_exists( $imageFile ) )
      {
         return null;
      }

      if ( ! \is_readable( $imageFile ) )
      {
         return null;
      }

      $file = \Niirrty\strContains( $imageFile, ' ' )
         ? ( '"' . $imageFile . '"' )
         : $imageFile;
      $path = \Niirrty\strContains( $this->path, ' ' )
         ? ( '"' . $this->path . '"' )
         : $this->path;
      $ignoreKeyRegex = '~^(Xmp\.(crs|xmpMM)\.|Xmp\.iptc\.CreatorContactInfo|Xmp\.aux\.(FlashCompensation|MetadataDate|CreatorTool|FlashCompensation|Firmware|ApproximateFocusDistance|ImageNumber|SerialNumber)|Iptc\.Application2\.RecordVersion|Exif\.Thumbnail\.|Exif\.Photo\.(BodySerialNumber|CameraOwnerName|SceneCaptureType|WhiteBalance|CustomRendered|FocalPlaneResolutionUnit|FocalPlaneYResolution|FocalPlaneXResolution|SubSecTimeDigitized|SubSecTimeOriginal|UserComment|ExifVersion)|Exif\.Image\.(ExifTag|Software|ResolutionUnit|YResolution|XResolution))~';

      try
      {
         $output = `$path -p a $file`;
         $lines = \explode( "\n", \str_replace( [ "\r\n", "\r" ], [ "\n", "\n" ], $output ) );
         $array = [];
         for ( $i = 0; $i < \count( $lines ); ++$i )
         {
            $line = \trim( $lines[ $i ] );
            if ( '' === $line )
            {
               continue;
            }
            $tmp = \preg_split( '~\s+~', $line, 4, \PREG_SPLIT_NO_EMPTY );
            if ( \count( $tmp ) != 4 )
            {
               continue;
            }
            $tmp[ 0 ] = \trim( $tmp[ 0 ] );
            if ( \preg_match( $ignoreKeyRegex, $tmp[ 0 ] ) )
            {
               continue;
            }
            $tmp[ 1 ] = \trim( $tmp[ 1 ] );
            $tmp[ 3 ] = \trim( $tmp[ 3 ] );
            if ( isset( $array[ $tmp[ 0 ] ] ) )
            {
               if ( ! \is_array( $array[ $tmp[ 0 ] ] ) )
               {
                  $array[ $tmp[ 0 ] ] = [ $array[ $tmp[ 0 ] ] ];
               }
               $array[ $tmp[ 0 ] ][] = $this->normalizeValue( $tmp[ 1 ], $tmp[ 3 ] );
               continue;
            }
            $array[ $tmp[ 0 ] ] = $this->normalizeValue( $tmp[ 1 ], $tmp[ 3 ] );
         }
         $result = [];

         # <editor-fold defaultstate="collapsed" desc="Image Description">
         if ( isset( $array[ 'Exif.Image.ImageDescription' ] ) )
         {
            if ( \is_array( $array[ 'Exif.Image.ImageDescription' ] ) )
            {
               $result[ 'Image Description' ] = \join( "\n", $array[ 'Exif.Image.ImageDescription' ] );
            }
            else
            {
               $result[ 'Image Description' ] = $array[ 'Exif.Image.ImageDescription' ];
            }
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Make + Model">
         if ( isset( $array[ 'Exif.Image.Make' ] ) )
         {
            $result[ 'Make' ] = $array[ 'Exif.Image.Make' ];
         }
         if ( isset( $array[ 'Exif.Image.Model' ] ) )
         {
            $result[ 'Camera Model Name' ] = $array[ 'Exif.Image.Model' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Artist + Copyright + Exposure Time + F Number">
         if ( isset( $array[ 'Exif.Image.Artist' ] ) )
         {
            $result[ 'Artist' ] = $array[ 'Exif.Image.Artist' ];
         }
         if ( isset( $array[ 'Exif.Image.Copyright' ] ) )
         {
            $result[ 'Copyright' ] = $array[ 'Exif.Image.Copyright' ];
         }
         if ( isset( $array[ 'Exif.Photo.ExposureTime' ] ) )
         {
            $result[ 'Exposure Time' ] = \rtrim( $array[ 'Exif.Photo.ExposureTime' ], 's ' );
         }
         if ( isset( $array[ 'Exif.Photo.FNumber' ] ) )
         {
            $result[ 'F Number' ] = \ltrim( $array[ 'Exif.Photo.FNumber' ], 'Ff' );
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Exposure Program + ISO + Date/Time Original + Digital Creation Date/Time">
         if ( isset( $array[ 'Exif.Photo.ExposureProgram' ] ) )
         {
            $result[ 'Exposure Program' ] = $array[ 'Exif.Photo.ExposureProgram' ];
         }
         if ( isset( $array[ 'Exif.Photo.ISOSpeedRatings' ] ) )
         {
            $result[ 'ISO' ] = $array[ 'Exif.Photo.ISOSpeedRatings' ];
         }
         if ( isset( $array[ 'Exif.Photo.DateTimeOriginal' ] ) )
         {
            $result[ 'Date/Time Original' ] = $array[ 'Exif.Photo.DateTimeOriginal' ];
         }
         if ( isset( $array[ 'Exif.Photo.DateTimeDigitized' ] ) )
         {
            $result[ 'Digital Creation Date/Time' ] = $array[ 'Exif.Photo.DateTimeDigitized' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Shutter Speed Value + Aperture Value + Metering Mode + Flash + Focal Length">
         if ( isset( $array[ 'Exif.Photo.ShutterSpeedValue' ] ) )
         {
            $result[ 'Shutter Speed Value' ] = \rtrim( $array[ 'Exif.Photo.ShutterSpeedValue' ], 's ' );
         }
         if ( isset( $array[ 'Exif.Photo.ApertureValue' ] ) )
         {
            $result[ 'Aperture Value' ] = \ltrim( $array[ 'Exif.Photo.ApertureValue' ], 'Ff' );
         }
         if ( isset( $array[ 'Exif.Photo.MeteringMode' ] ) )
         {
            $result[ 'Metering Mode' ] = $array[ 'Exif.Photo.MeteringMode' ];
         }
         if ( isset( $array[ 'Exif.Photo.Flash' ] ) )
         {
            $result[ 'Flash' ] = $array[ 'Exif.Photo.Flash' ];
         }
         if ( isset( $array[ 'Exif.Photo.FocalLength' ] ) )
         {
            $result[ 'Focal Length' ] = $array[ 'Exif.Photo.FocalLength' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Exposure Mode + Lens ID">
         if ( isset( $array[ 'Exif.Photo.ExposureMode' ] ) )
         {
            $result[ 'Exposure Mode' ] = $array[ 'Exif.Photo.ExposureMode' ];
         }
         if ( isset( $array[ 'Exif.Photo.LensModel' ] ) )
         {
            $result[ 'Lens ID' ] = $array[ 'Exif.Photo.LensModel' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="GPS">
         if ( isset( $array[ 'Exif.GPSInfo.GPSLatitudeRef' ] ) )
         {
            $result[ 'GPS Latitude Ref' ] = ( 'south' === \strtolower( $array[ 'Exif.GPSInfo.GPSLatitudeRef' ] )
               ? 'S'
               : 'N' );
         }
         if ( isset( $array[ 'Exif.GPSInfo.GPSLongitudeRef' ] ) )
         {
            $result[ 'GPS Longitude Ref' ] = ( 'west' === \strtolower( $array[ 'Exif.GPSInfo.GPSLongitudeRef' ] )
               ? 'W'
               : 'E' );
         }
         if ( isset( $array[ 'Exif.GPSInfo.GPSLatitude' ] ) )
         {
            $result[ 'GPS Latitude' ] = \str_replace( 'deg', '°', $array[ 'Exif.GPSInfo.GPSLatitude' ] );
         }
         if ( isset( $array[ 'Exif.GPSInfo.GPSLongitude' ] ) )
         {
            $result[ 'GPS Longitude' ] = \str_replace( 'deg', '°', $array[ 'Exif.GPSInfo.GPSLongitude' ] );
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Object Name + Category + Supplemental Categories + Keywords">
         if ( isset( $array[ 'Iptc.Application2.ObjectName' ] ) )
         {
            $result[ 'Object Name' ] = $array[ 'Iptc.Application2.ObjectName' ];
         }
         if ( isset( $array[ 'Iptc.Application2.Category' ] ) )
         {
            $result[ 'Category' ] = $array[ 'Iptc.Application2.Category' ];
         }
         if ( isset( $array[ 'Iptc.Application2.SuppCategory' ] ) )
         {
            $result[ 'Supplemental Categories' ] = \is_array( $array[ 'Iptc.Application2.SuppCategory' ] )
               ? \join( ', ', $array[ 'Iptc.Application2.SuppCategory' ] )
               : $array[ 'Iptc.Application2.SuppCategory' ];
         }
         if ( isset( $array[ 'Iptc.Application2.Keywords' ] ) )
         {
            $result[ 'Keywords' ] = \is_array( $array[ 'Iptc.Application2.Keywords' ] )
               ? \join( ', ', $array[ 'Iptc.Application2.Keywords' ] )
               : $array[ 'Iptc.Application2.Keywords' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Special Instructions + Date/Time Created">
         if ( isset( $array[ 'Iptc.Application2.SpecialInstructions' ] ) )
         {
            $result[ 'Special Instructions' ] = \is_array( $array[ 'Iptc.Application2.SpecialInstructions' ] )
               ? \join( "\n", $array[ 'Iptc.Application2.SpecialInstructions' ] )
               : $array[ 'Iptc.Application2.SpecialInstructions' ];
         }
         if ( isset( $array[ 'Iptc.Application2.DateCreated' ] ) && isset( $array[ 'Iptc.Application2.TimeCreated' ] ) )
         {
            $result[ 'Date/Time Created' ] =
               $array[ 'Iptc.Application2.DateCreated' ]
               . ' '
               . $array[ 'Iptc.Application2.TimeCreated' ];
            if ( ! isset( $result[ 'Create Date' ] ) )
            {
               $result[ 'Create Date' ] = $result[ 'Date/Time Created' ];
            }
            if ( ! isset( $result[ 'Date Created' ] ) )
            {
               $result[ 'Date Created' ] = $result[ 'Date/Time Created' ];
            }
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="By-line + By-line Title + City + Sub-location + Province-State">
         if ( isset( $array[ 'Iptc.Application2.Byline' ] ) )
         {
            $result[ 'By-line' ] = $array[ 'Iptc.Application2.Byline' ];
         }
         if ( isset( $array[ 'Iptc.Application2.BylineTitle' ] ) )
         {
            $result[ 'By-line Title' ] = $array[ 'Iptc.Application2.BylineTitle' ];
         }
         if ( isset( $array[ 'Iptc.Application2.City' ] ) )
         {
            $result[ 'Creator City' ] = $array[ 'Iptc.Application2.City' ];
         }
         if ( isset( $array[ 'Iptc.Application2.SubLocation' ] ) )
         {
            $result[ 'Sub-location' ] = $array[ 'Iptc.Application2.SubLocation' ];
         }
         if ( isset( $array[ 'Iptc.Application2.ProvinceState' ] ) )
         {
            $result[ 'Province-State' ] = $array[ 'Iptc.Application2.ProvinceState' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Country Code + Country-Primary Location Code">
         if ( isset( $array[ 'Iptc.Application2.CountryCode' ] ) )
         {
            $result[ 'Country Code' ] = $array[ 'Iptc.Application2.CountryCode' ];
            $result[ 'Country-Primary Location Code' ] = $result[ 'Country Code' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Country + Country-Primary Location Name">
         if ( isset( $array[ 'Iptc.Application2.CountryName' ] ) )
         {
            $result[ 'Country' ] = $array[ 'Iptc.Application2.CountryName' ];
            $result[ 'Country-Primary Location Name' ] = $result[ 'Country' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Transmission Reference + Original Transmission Reference">
         if ( isset( $array[ 'Iptc.Application2.TransmissionReference' ] ) )
         {
            $result[ 'Transmission Reference' ] = $array[ 'Iptc.Application2.TransmissionReference' ];
            $result[ 'Original Transmission Reference' ] = $result[ 'Transmission Reference' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Headline + Credit + Source + Copyright Notice + ">
         if ( isset( $array[ 'Iptc.Application2.Headline' ] ) )
         {
            $result[ 'Headline' ] = $array[ 'Iptc.Application2.Headline' ];
         }
         if ( isset( $array[ 'Iptc.Application2.Credit' ] ) )
         {
            $result[ 'Credit' ] = $array[ 'Iptc.Application2.Credit' ];
         }
         if ( isset( $array[ 'Iptc.Application2.Source' ] ) )
         {
            $result[ 'Source' ] = $array[ 'Iptc.Application2.Source' ];
         }
         if ( isset( $array[ 'Iptc.Application2.Copyright' ] ) )
         {
            $result[ 'Copyright Notice' ] = $array[ 'Iptc.Application2.Copyright' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Caption-Abstract + Writer-Editor + Format + Title + Creator">
         if ( isset( $array[ 'Iptc.Application2.Caption' ] ) )
         {
            $result[ 'Caption-Abstract' ] = $array[ 'Iptc.Application2.Caption' ];
         }
         if ( isset( $array[ 'Iptc.Application2.Writer' ] ) )
         {
            $result[ 'Writer-Editor' ] = $array[ 'Iptc.Application2.Writer' ];
         }
         if ( isset( $array[ 'Xmp.dc.format' ] ) )
         {
            $result[ 'Format' ] = $array[ 'Xmp.dc.format' ];
         }
         if ( isset( $array[ 'Xmp.dc.title' ] ) )
         {
            $result[ 'Title' ] = $array[ 'Xmp.dc.title' ];
         }
         if ( isset( $array[ 'Xmp.dc.creator' ] ) )
         {
            $result[ 'Creator' ] = $array[ 'Xmp.dc.creator' ];
         }
         # </editor-fold>

         # <editor-fold defaultstate="collapsed" desc="Rights + Subject + Description + Lens Info + Lens ID">
         if ( isset( $array[ 'Xmp.dc.rights' ] ) )
         {
            $result[ 'Rights' ] = $array[ 'Xmp.dc.rights' ];
         }
         if ( isset( $array[ 'Xmp.dc.subject' ] ) )
         {
            $result[ 'Subject' ] = $array[ 'Xmp.dc.subject' ];
         }
         if ( isset( $array[ 'Xmp.dc.description' ] ) )
         {
            $result[ 'Description' ] = $array[ 'Xmp.dc.description' ];
         }
         if ( isset( $array[ 'Xmp.aux.LensInfo' ] ) )
         {
            $result[ 'Lens Info' ] = $array[ 'Xmp.aux.LensInfo' ];
         }
         if ( isset( $array[ 'Xmp.aux.Lens' ] ) )
         {
            $result[ 'Lens ID' ] = $array[ 'Xmp.aux.Lens' ];
         }
         # </editor-fold>

         if ( isset( $array[ 'Xmp.xmp.ModifyDate' ] ) )
         {
            $result[ 'Modify Date' ] = $array[ 'Xmp.xmp.ModifyDate' ];
         }
         if ( isset( $array[ 'Xmp.xmp.Label' ] ) )
         {
            $result[ 'Label' ] = $array[ 'Xmp.xmp.Label' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.AuthorsPosition' ] ) )
         {
            $result[ 'Authors Position' ] = $array[ 'Xmp.photoshop.AuthorsPosition' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.Headline' ] ) && ! isset( $result[ 'Headline' ] ) )
         {
            $result[ 'Headline' ] = $array[ 'Xmp.photoshop.Headline' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.CaptionWriter' ] ) && ! isset( $result[ 'Caption Writer' ] ) )
         {
            $result[ 'Caption Writer' ] = $array[ 'Xmp.photoshop.CaptionWriter' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.Category' ] ) )
         {
            $result[ 'Category' ] = $array[ 'Xmp.photoshop.Category' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.City' ] ) && empty( $result[ 'Creator City' ] ) )
         {
            $result[ 'Creator City' ] = $array[ 'Xmp.photoshop.City' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.State' ] ) && ! isset( $result[ 'State' ] ) )
         {
            $result[ 'State' ] = $array[ 'Xmp.photoshop.State' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.Country' ] ) && ! isset( $result[ 'Country' ] ) )
         {
            $result[ 'Country' ] = $array[ 'Xmp.photoshop.Country' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.TransmissionReference' ] )
              && ! isset( $result[ 'Transmission Reference' ] ) )
         {
            $result[ 'Transmission Reference' ] = $array[ 'Xmp.photoshop.TransmissionReference' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.Instructions' ] ) && ! isset( $result[ 'Instructions' ] ) )
         {
            $result[ 'Instructions' ] = $array[ 'Xmp.photoshop.Instructions' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.Credit' ] ) && ! isset( $result[ 'Credit' ] ) )
         {
            $result[ 'Credit' ] = $array[ 'Xmp.photoshop.Credit' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.Source' ] ) && ! isset( $result[ 'Source' ] ) )
         {
            $result[ 'Source' ] = $array[ 'Xmp.photoshop.Source' ];
         }
         if ( isset( $array[ 'Xmp.photoshop.SupplementalCategories' ] )
              && ! isset( $result[ 'Supplemental Categories' ] ) )
         {
            $result[ 'Supplemental Categories' ] = $array[ 'Xmp.photoshop.SupplementalCategories' ];
         }
         if ( isset( $array[ 'Xmp.iptc.IntellectualGenre' ] ) && ! isset( $result[ 'Intellectual Genre' ] ) )
         {
            $result[ 'Intellectual Genre' ] = $array[ 'Xmp.iptc.IntellectualGenre' ];
         }
         if ( isset( $array[ 'Xmp.iptc.Location' ] ) && ! isset( $result[ 'Location' ] ) )
         {
            $result[ 'Location' ] = $array[ 'Xmp.iptc.Location' ];
         }
         if ( isset( $array[ 'Xmp.iptc.CountryCode' ] ) && ! isset( $result[ 'Country Code' ] ) )
         {
            $result[ 'Country Code' ] = $array[ 'Xmp.iptc.CountryCode' ];
         }
         if ( isset( $array[ 'Xmp.iptc.Scene' ] ) && ! isset( $result[ 'Scene' ] ) )
         {
            $result[ 'Scene' ] = $array[ 'Xmp.iptc.Scene' ];
         }
         if ( isset( $array[ 'Xmp.xmpRights.Marked' ] ) )
         {
            $result[ 'Copyright Flag' ] = $array[ 'Xmp.xmpRights.Marked' ];
         }
         if ( isset( $array[ 'Xmp.xmpRights.WebStatement' ] ) && ! isset( $result[ 'URL' ] ) )
         {
            $result[ 'URL' ] = $array[ 'Xmp.xmpRights.WebStatement' ];
         }
         if ( isset( $array[ 'Xmp.xmpRights.UsageTerms' ] ) )
         {
            $result[ 'Usage Terms' ] = $array[ 'Xmp.xmpRights.UsageTerms' ];
         }

         return new ImageInfo( $result, $imageFile );
      }
      catch ( \Throwable $ex )
      {
         return null;
      }

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P R I V A T E   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   private function normalizeValue( $type, $value )
   {

      if ( 'LangAlt' != $type )
      {
         return $value;
      }

      if ( ! \preg_match( '~^lang="[^"]+"\s(.+)~', $value, $m ) )
      {
         return $value;
      }

      return $m[ 1 ];

   }

   private function find()
   {

      $file = 'exiv2';

      if ( \is_executable( $file ) )
      {
         return $file;
      }

      /** @noinspection PhpUndefinedConstantInspection */
      if ( \IS_WIN )
      {
         $file2 = 'exiv2.exe';
         $path = 'C:\\Windows\\' . $file;
         if ( \is_executable( $path ) )
         {
            return $path;
         }
         $path = 'C:\\Windows\\' . $file2;
         if ( \is_executable( $path ) )
         {
            return $path;
         }
         $path = 'C:\\Program Files\\exiv2\\' . $file;
         if ( \is_executable( $path ) )
         {
            return $path;
         }
         $path = 'C:\\Program Files\\exiv2\\' . $file2;
         if ( \is_executable( $path ) )
         {
            return $path;
         }
         $old = \chdir( 'C:\\' );
         $lines = \explode( "\n", \str_replace(
            "\r\n", "\n", \trim( `dir $file2 /s 2>&1` ) ) );
         \chdir( $old );
         $found = [ 'primary' => '', 'programs' => '' ];
         for ( $i = 0; $i < \count( $lines ); ++$i )
         {
            $line = \trim( $lines[ $i ] );
            if ( '' === $line )
            {
               continue;
            }
            if ( empty( $found[ 'primary' ] ) &&
                 \preg_match( '~(c:\\\\.+?exiv2)$~i', $line, $m ) )
            {
               $found[ 'primary' ] = \rtrim( $m[ 1 ], '\\' ) . '\\' . $file;
               continue;
            }
            if ( empty( $found[ 'programs' ] ) && \preg_match( '~(c:\\\\program.+)$~i', $line, $m ) )
            {
               $found[ 'programs' ] = \rtrim( $m[ 1 ], '\\' ) . '\\' . $file;
               continue;
            }
         }
         if ( ! empty( $found[ 'primary' ] ) )
         {
            return $found[ 'primary' ];
         }
         if ( ! empty( $found[ 'programs' ] ) )
         {
            return $found[ 'programs' ];
         }

         return null;

      }
      $path = '/usr/bin/' . $file;
      if ( \is_executable( $path ) )
      {
         return $path;
      }
      $path = '/bin/' . $file;
      if ( \is_executable( $path ) )
      {
         return $path;
      }

      return null;

   }

   # </editor-fold>


}

