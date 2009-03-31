<?php
include_once( "kernel/common/template.php" );
//include_once( 'extension/ezstaticexport/classes/ezstaticexportcontenttype.php' );

$Module = $Params["Module"];

$tpl = templateInit();
$tpl->setVariable( 'flags', eZStaticExportContentType::fetchList() );

$Result = array();
$Result['content'] = $tpl->fetch( "design:ezstaticexport/flaglist.tpl" );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ),
                         array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Content type flag list' ) ) );
?>
