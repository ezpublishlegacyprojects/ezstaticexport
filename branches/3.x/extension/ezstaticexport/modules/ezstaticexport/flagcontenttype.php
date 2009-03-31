<?php
/**
 * Flags a node as JSP export
 * Input data (POST)
 *  - NodeID
 *  - ObjectID
 *  - eZStaticExportFlasgAsJSPButton
 */

include_once( 'extension/ezstaticexport/classes/ezstaticexportcontenttype.php' );
include_once( 'extension/ezstaticexport/classes/ezstaticexporttoken.php' );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
include_once( 'kernel/common/template.php' );

$Module =& $Params["Module"];
$tpl =& templateInit();

if ( eZStaticExportToken::exists() )
{
    $Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
    $Result['content'] = $tpl->fetch( 'design:ezstaticexport/flagcontenttype_exportrunning.tpl' );
    $Result['path']    = array( array( 'url' => false,
                                       'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ),
                                array( 'url' => false,
                                       'text' => ezi18n( 'extension/ezstaticexport', 'An export is running' ) ) );
}
elseif ( $Module->isCurrentAction( 'FlagContentType' ) )
{
    $NodeID      = $Module->actionParameter( 'NodeID' );
    $FlagType    = $Module->actionParameter( 'FlagType' );
    $ContentType = $Module->actionParameter( 'ContentType' );

    eZStaticExportContentType::flag( $FlagType, $NodeID, $ContentType );

    return $Module->redirectTo( 'content/view/full/' . $NodeID );
}
elseif( $Module->isCurrentAction( 'RemoveFlag' ) )
{
    foreach( $Module->actionParameter( 'FlagIDArray' ) as $flagID )
    {
        $flag = eZStaticExportContentType::fetch( $flagID );
        if ( in_array( $flag->attribute('flag_type'), array( 'node', 'subtree' ) ) )
            $flag->remove();
    }
    return $Module->redirectToView( 'flaglist', array() );
}
?>