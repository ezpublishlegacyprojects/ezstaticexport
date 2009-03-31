<?php
include_once( "kernel/common/template.php" );
//include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );

$tpl = templateInit();

$exports = eZStaticExportExport::fetchList();
$tpl->setVariable( 'exports', $exports );

$Result = array();
$Result['content'] = $tpl->fetch( "design:ezstaticexport/list.tpl" );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ) );

?>
