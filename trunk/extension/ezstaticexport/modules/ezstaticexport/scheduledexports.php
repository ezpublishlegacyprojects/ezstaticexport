<?php

include_once( "kernel/common/template.php" );
//include_once( 'extension/ezstaticexport/classes/ezstaticexportscheduler.php' );

$eZStaticExportScheduler = new eZStaticExportScheduler();

$Module = $Params["Module"];
$tpl = templateInit();

$possibleActions = array( 'list', 'delete' );
$Action = $possibleActions[0];

if( isset( $Module->NamedParameters['Action'] ) and in_array( $Module->NamedParameters['Action'], $possibleActions ))
{
    $Action = $Module->NamedParameters['Action'];
}

//list
if( $Action == $possibleActions[0] )
{
    $scheduledExports = $eZStaticExportScheduler->fetchList();
    $tpl->setVariable( 'scheduledExports', $scheduledExports );
}

//delete
if( $Action == $possibleActions[1] and isset( $Module->NamedParameters['ScheduledExportID'] ) )
{
    $ScheduledExportID = $Module->NamedParameters['ScheduledExportID'];
    $eZStaticExportScheduler->remove( $ScheduledExportID );
    $Module->redirectToView( 'scheduledexports' );
}

$Result = array();
$Result['content'] = $tpl->fetch( "design:ezstaticexport/scheduledexports.tpl" );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ) );

?>
