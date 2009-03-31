<?php
include_once( "kernel/common/template.php" );
include_once( 'extension/ezstaticexport/classes/ezstaticexportlog.php' );

$tpl =& templateInit();
$tpl->setVariable( 'log', eZStaticExportLog::fetchByExportID( $Params['ExportID'] ) );
$tpl->setVariable( 'logDownloadUrl', 'ezstaticexport/downloadlog/' . $Params['ExportID'] );

$Result = array();
$Result['content'] =& $tpl->fetch( "design:ezstaticexport/logs.tpl" );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ) );

?>
