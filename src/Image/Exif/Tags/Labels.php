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


use function count;
use function is_array;
use function is_null;
use function strtolower;


/**
 * 3 labels/titles
 *
 * @since v0.1
 */
class Labels
{


    # <editor-fold desc="= = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">


    /**
     * Optional "Object Name" label
     *
     * @var string
     */
    public $ObjectName;

    /**
     * The EXIF "Label" Element
     *
     * @var string
     */
    public $Label;

    /**
     * The IPTC "Title" Element Label
     *
     * @var string
     */
    public $Title;

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Init a new instance.
     *
     * @param array $data Keys 'Label', 'Title' and 'Object-Name'
     */
    public function __construct( array $data = [] )
    {

        $this->reinitFromArray( $data );

    }

    # </editor-fold>


    # <editor-fold desc="= = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

    /**
     * Gets the first label depending to the order of values, defined by $preferes.
     *
     * If $preferes is empty/undefined the following order is used: 'Label', 'Title', 'ObjectName'
     *
     * @param array $preferes
     *
     * @return string
     */
    public final function getPreferedLabel( array $preferes = null )
    {

        if ( !is_array( $preferes ) || count( $preferes ) < 1 )
        {
            $preferes = [ 'label', 'title', 'object' ];
        }

        foreach ( $preferes as $pref )
        {
            switch ( strtolower( $pref ) )
            {
                case 'object':
                case 'objekt':
                case 'name':
                case 'objectname':
                case 'objektname':
                case 'object-name':
                case 'objekt-name':
                case 'object.name':
                case 'objekt.name':
                case 'object name':
                case 'objekt name':
                    if ( !is_null( $this->ObjectName ) && '' != $this->ObjectName )
                    {
                        return $this->ObjectName;
                    }
                    break;
                case 'label':
                case 'etikett':
                case 'beschriftung':
                case 'aufschrift':
                    if ( !is_null( $this->Label ) && '' != $this->Label )
                    {
                        return $this->Label;
                    }
                    break;
                case 'title':
                case 'titel':
                case 'überschrift':
                case 'ueberschrift':
                case 'bezeichnung':
                    if ( !is_null( $this->Title ) && '' != $this->Title )
                    {
                        return $this->Title;
                    }
                    break;
                default:
                    break;
            }
        }

        if ( !is_null( $this->Label ) && '' != $this->Label )
        {
            return $this->Label;
        }

        if ( !is_null( $this->Title ) && '' != $this->Title )
        {
            return $this->Title;
        }

        if ( !is_null( $this->ObjectName ) )
        {
            return $this->ObjectName;
        }

        return '';

    }

    /**
     * @return string
     */
    public final function __toString()
    {

        return $this->getPreferedLabel();

    }

    /**
     * Keys are: 'Label', 'Title' and 'Object-Name'.
     *
     * @param array $data
     */
    public final function reinitFromArray( array $data )
    {

        $this->Label = isset( $data[ 'Label' ] ) ? $data[ 'Label' ] : null;
        $this->Title = isset( $data[ 'Title' ] ) ? $data[ 'Title' ] : null;
        $this->ObjectName = isset( $data[ 'Object-Name' ] )
            ? $data[ 'Object-Name' ]
            : ( isset( $data[ 'Object Name' ] )
                ? $data[ 'Object Name' ]
                : null );

    }

    /**
     * @param array $array
     */
    public final function addToArray( array &$array )
    {

        if ( !is_null( $this->Label ) && '' != $this->Label )
        {
            $array[ 'Label' ] = $this->Label;
        }

        if ( !is_null( $this->Title ) && '' != $this->Title )
        {
            $array[ 'Title' ] = $this->Title;
        }

        if ( !is_null( $this->ObjectName ) && '' != $this->ObjectName )
        {
            $array[ 'Object-Name' ] = $this->ObjectName;
        }

    }


    # </editor-fold>


}

