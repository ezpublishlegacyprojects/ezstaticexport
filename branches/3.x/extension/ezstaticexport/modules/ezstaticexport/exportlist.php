<?php
include_once( "kernel/common/template.php" );
include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );

$Module =& $Params["Module"];

$tpl =& templateInit();
$runningExports = eZStaticExportExport::fetchByStatus( array( EZ_STATIC_EXPORT_STATUS_PENDING,
                                                              EZ_STATIC_EXPORT_STATUS_RUNNING,
                                                              EZ_STATIC_EXPORT_STATUS_SYNCING,
                                                              EZ_STATIC_EXPORT_RESULT_INTERRUPTED,
                                                              EZ_STATIC_EXPORT_STATUS_INTERRUPT_REQUESTED ) );
$tpl->setVariable( 'exports', $runningExports );

$Result = array();
$Result['content']   =& $tpl->fetch( "design:ezstaticexport/exportlist.tpl" );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path']      = array( array( 'url' => false,
                                     'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ) );
?>
