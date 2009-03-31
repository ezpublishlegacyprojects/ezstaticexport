<?php
include_once( "kernel/common/template.php" );
//include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );

$Module = $Params["Module"];

$tpl = templateInit();
$runningExports = eZStaticExportExport::fetchByStatus( array( eZStaticExport::STATUS_PENDING,
                                                              eZStaticExport::STATUS_RUNNING,
                                                              eZStaticExport::STATUS_SYNCING,
                                                              eZStaticExport::RESULT_INTERRUPTED,
                                                              eZStaticExport::STATUS_INTERRUPT_REQUESTED ) );
$tpl->setVariable( 'exports', $runningExports );

$Result = array();
$Result['content']   = $tpl->fetch( "design:ezstaticexport/exportlist.tpl" );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path']      = array( array( 'url' => false,
                                     'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ) );
?>
