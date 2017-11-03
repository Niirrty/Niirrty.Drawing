<?php
/**
 * This file defines the {@see \Niirrty\Drawing\Image\Exif\ImageInfoLocaleConverter} class.
 *
 * @author         Curt Durban <curt.durban@gmail.com>
 * @category       AURORA PHP-Framework
 * @copyright  (c) 2015-2016, Curt Durban
 * @package        Drawing
 * @subpackage     Image\Exif
 * @since          2015-11-05 19:02
 * @version        0.2
 */


declare( strict_types = 1 );


namespace Niirrty\Drawing\Image\Exif;


/**
 * Statische Klasse zum Konvertieren von Exif Bilddaten in andere Sprachen.
 * Bezieht sich nur auf die Keys. Die Werte werden nie konvertiert!
 *
 * @since v0.1
 */
class ImageInfoLocaleConverter
{


   # <editor-fold desc="= = =   P R I V A T E   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance. Is private because the class should only be used by the static way.
    */
   private function __construct() { }

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   S T A T I C   F I E L D S   = = = = = = = = = = = = = = = = = = =">

   /**
    * Alle Exiffeld Namenslokalsierungen.
    *
    * <code>
    * \Niirrty\Drawing\Image\Exif\ImageInfoLocalConverter::$Localizations['fr'] = array(
    *     'Image Width'  => '…', 'Image Height' => '…', # etc…
    * );
    * </code>
    *
    * Mögliche Keys sind:
    *
    * "Image Width", "Image Height", "MIME Type", "Format", "Image Description",
    * "Caption-Abstract", "Description", "Copyright", "Copyright Notice", "Rights",
    * "URL", "Usage Terms", "Copyright Flag", "Keywords", "Subject", 'Creator',
    * 'Artist', 'By-line', 'Authors Position', 'By-line Title', 'Creator Address',
    * 'Creator City', 'Creator Region', 'Creator Postal Code', 'Creator Country',
    * 'Creator Work Telephone', 'Creator Work Email', 'Creator Work URL',
    * 'Location', 'Sub-location', 'State', 'Province-State', 'Country Code',
    * 'Country-Primary Location Code', 'City', 'Country', 'Country-Primary Location Name',
    * 'Intellectual Genre', 'Scene', 'Modify Date', 'Date/Time Original',
    * 'Create Date', 'Date Created', 'Date/Time Created', 'Digital Creation Date/Time',
    * 'Digital Creation Date', 'Digital Creation Time', 'Instructions',
    * 'Special Instructions', 'Transmission Reference', 'Original Transmission Reference',
    * 'Credit', 'Source', 'GPS Latitude', 'GPS Longitude', 'GPS Position',
    * 'GPS Latitude Ref ', 'GPS Longitude Ref', 'Object Name', 'Label', 'Title',
    * "Headline", "Category", "Supplemental Categories", 'Make', 'Camera Model Name',
    * 'Exposure Time', 'Shutter Speed Value', 'Shutter Speed', 'F Number',
    * 'Aperture Value', 'ISO', 'Lens ID', 'Lens Info', 'Exposure Program',
    * 'Exposure Compensation', 'Metering Mode', 'Flash', 'Focal Length',
    * 'Exposure Mode', 'Caption Writer', 'Writer-Editor'
    *
    * @var array
    */
   public static $Localizations = [
      'de' => [
         'Image Width'                     => 'Breite',
         'Image Height'                    => 'Höhe',
         'MIME Type'                       => 'MIME-Type',
         'Format'                          => 'Bildformat',
         'Image Description'               => 'Bildbeschreibung',
         'Caption-Abstract'                => 'Abstrakte Beschreibung',
         'Description'                     => 'Beschreibung',
         'Copyright'                       => 'Copyright',
         'Copyright Notice'                => 'Copyright Hinweis',
         'Rights'                          => 'Copyright Rechte',
         'URL'                             => 'Copyright URL',
         'Usage Terms'                     => 'Nutzungsbedingungen',
         'Copyright Flag'                  => 'Copyright Flag',
         'Keywords'                        => 'Schlüsselworte',
         'Subject'                         => 'Tags',
         'Creator'                         => 'Ersteller',
         'Artist'                          => 'Künstler',
         'By-line'                         => 'Urheber',
         'Authors Position'                => 'Autor-Position',
         'By-line Title'                   => 'Autor-Anrede',
         'Creator Address'                 => 'Autor-Adresse',
         'Creator City'                    => 'Autor Stadt',
         'Creator Region'                  => 'Autor Region',
         'Creator Postal Code'             => 'Autor PLZ',
         'Creator Country'                 => 'Autor Stadt',
         'Creator Work Telephone'          => 'Autor Telefon',
         'Creator Work Email'              => 'Autor EMail',
         'Creator Work URL'                => 'Autor-Webadressen',
         'Location'                        => 'Bild-Standort',
         'Sub-location'                    => 'Bild-Substandort',
         'State'                           => 'Bild-Region',
         'Province-State'                  => 'Bild-Bundesland/Kanton',
         'Country Code'                    => 'Bild-Landeskennung',
         'Country-Primary Location Code'   => 'Bild-Ländercode',
         'City'                            => 'Bild-Stadt',
         'Country'                         => 'Bild-Land',
         'Country-Primary Location Name'   => 'Bild-Landesname',
         'Intellectual Genre'              => 'Bild-Genre',
         'Scene'                           => 'Bild-Szene',
         'Modify Date'                     => 'Änderungsdatum',
         'Date/Time Original'              => 'Datum/Zeit original',
         'Create Date'                     => 'Erstellungszeitpunkt',
         'Date Created'                    => 'Zeitpunkt Erstellung',
         'Date/Time Created'               => 'Erstellungs Datum/Zeit',
         'Digital Creation Date/Time'      => 'Dig. Erstellungs Datum/Zeit',
         'Digital Creation Date'           => 'Digit. Erstellungs Datum',
         'Digital Creation Time'           => 'Digit. Erstellungs Zeit',
         'Instructions'                    => 'Anweisungen',
         'Special Instructions'            => 'Spez. Anweisungen',
         'Transmission Reference'          => 'Jobkennung',
         'Original Transmission Reference' => 'Orig. Jobkennung',
         'Credit'                          => 'Anbieter',
         'Source'                          => 'Quelle',
         'GPS Latitude'                    => 'GPS-Breite',
         'GPS Longitude'                   => 'GPS-Länge',
         'GPS Position'                    => 'GPS-Position',
         'GPS Latitude Ref'                => 'GPS-Breiten Ref.',
         'GPS Longitude Ref'               => 'GPS-Längen Ref.',
         'Object Name'                     => 'Objektname',
         'Label'                           => 'Label',
         'Title'                           => 'Titel',
         'Headline'                        => 'Kopfzeile',
         'Category'                        => 'Kategorie',
         'Supplemental Categories'         => 'Zusätzliche Kategorien',
         'Make'                            => 'Hersteller',
         'Camera Model Name'               => 'Kamera-Modell',
         'Exposure Time'                   => 'Belichtungszeit',
         'Shutter Speed Value'             => 'Belichtungszeitwert',
         'Shutter Speed'                   => 'Zeit Belichtung',
         'F Number'                        => 'Blende',
         'Aperture Value'                  => 'Blendenwert',
         'ISO'                             => 'ISO',
         'Lens ID'                         => 'Objektiv',
         'Lens Info'                       => 'Objektivinfo',
         'Exposure Program'                => 'Belichtungsprogramm',
         'Exposure Compensation'           => 'Belichtungskompens.',
         'Metering Mode'                   => 'Messmodus',
         'Flash'                           => 'Blitz',
         'Focal Length'                    => 'Brennweite',
         'Exposure Mode'                   => 'Belichtungsmodus',
         'Caption Writer'                  => 'Autor-Beschreibung',
         'Writer-Editor'                   => 'Verfasser'
      ]
   ];

   # </editor-fold>


   # <editor-fold defaultstate="collapsed" desc=" - - -   P U B L I C   S T A T I C   M E T H O D S   - - - - - - - - - - - - - - -">

   /**
    * Konvertiert die Keys des übergebenen Arrays mit Exifdaten, die im
    * originalen Englisch vorliegen müssen, in die angegebene Sprache
    * (Sprachkennung) und gibt das Array mit den neuen Keys zurück.
    *
    * @param array $data Daten deren Keys konvertiert werden sollen.
    * @param string $lang Sprache in die konvertiert werden soll.
    * @return array Resultierendes Datenarray mit neuen Keys, oder original Array wenn Sprache nicht verfügbar.
    */
   public static function ConvertTo( array $data, $lang = 'de' )
   {

      if ( ! isset( self::$Localizations[ $lang ] ) )
      {
         return $data;
      }

      $result = array();

      foreach ( $data as $key => $value )
      {
         if ( ! isset( self::$Localizations[ $lang ][ $key ] ) )
         {
            $result[ $key ] = $value;
         }
         else
         {
            $result[ self::$Localizations[ $lang ][ $key ] ] = $value;
         }
      }

      return $result;

   }

   /**
    * Konvertiert die Keys des übergebenen Arrays mit Exifdaten, die in
    * der anzugebenden Sprache vorliegen müssen, zurück ins Englische und
    * gibt das Array mit den neuen Keys zurück.
    *
    * @param array $data Daten deren Keys konvertiert werden sollen.
    * @param string $lang Sprache aus der ins engl. konvertiert werden soll.
    * @return array Resultierendes Datenarray mit neuen Keys, oder original Array wenn Sprache nicht verfügbar.
    */
   public static function ConvertFrom( array $data, $lang = 'de' )
   {

      if ( ! isset( self::$Localizations[ $lang ] ) )
      {
         return $data;
      }

      $result = array();

      foreach ( $data as $key => $value )
      {
         $okey = \array_search( $key, self::$Localizations[ $lang ] );
         if ( ! $okey )
         {
            $okey = $key;
         }
         $result[ $okey ] = $value;
      }

      return $result;

   }

   # </editor-fold>


}

