<?php

/*
 * Methode used =
 * 1 - check target
 *      + if default     => fetch default target settings
 *      + if not default => fetch chosen target settings
 *
 * 2 - check export type
 *      + if immediate => launch immediate static export
 *      + if scheduled => insert row in scheduler
 *
 */

//var_dump($_POST);

include_once( "kernel/common/template.php" );
//include_once( "lib/ezutils/classes/ezhttptool.php" );
//include_once( "lib/ezutils/classes/ezini.php" );

function fetchDefaultSettings( $eZINI )
{
    $target = array() ;

    $target['ServerName']     = $eZINI->variable( 'DefaultTargetServerSettings', 'DefaultTargetServerName'     );
    $target['ServerURL']      = $eZINI->variable( 'DefaultTargetServerSettings', 'DefaultTargetServerURL'      );
    $target['ServerLogin']    = $eZINI->variable( 'DefaultTargetServerSettings', 'DefaultTargetServerLogin'    );
    $target['ServerPassword'] = $eZINI->variable( 'DefaultTargetServerSettings', 'DefaultTargetServerPassword' );

    return $target;
}

$Module = $Params["Module"];
$eZHTTP = eZHTTPTool::instance();

$tpl = templateInit();
// those 4 below variables may not be used in action.tpl but are necessary
// for export.tpl
$tpl->setVariable( 'NodeID'      , $eZHTTP->postVariable( 'NodeID' )       );
$tpl->setVariable( 'ObjectID'    , $eZHTTP->postVariable( 'ObjectID' )     );
$tpl->setVariable( 'ExportType'  , $eZHTTP->postVariable( 'ExportType' )   );
$tpl->setVariable( 'ExportTarget', $eZHTTP->postVariable( 'ExportTarget' ) );

$eZINI  = eZINI::instance( 'staticexport.ini' );
$templateToLoad = "design:ezstaticexport/action.tpl";

//--------------------------
// Begin ExportTarget stuff
// -------------------------

$target = array();

if( $eZHTTP->postVariable( 'ExportTarget' ) == 'default' and $eZHTTP->postVariable( 'TargetServer' ) == 'DefaultTargetServer' )
{
    // fetch default settings
    $target = fetchDefaultSettings( $eZINI );
}

if( $eZHTTP->postVariable( 'ExportTarget' ) == 'other' )
{
    // fetch specific settings
    $TargetServer = $eZHTTP->postVariable( 'TargetServer' );
    //var_dump($TargetServer );

    if( $TargetServer == 'DefaultTargetServer' )
    {
        $target = fetchDefaultSettings( $eZINI );
    }
    
    if( $eZINI->hasGroup( 'TargetServer-' . $TargetServer ) )
    {
        $target['ServerName']     = $eZINI->variable( 'TargetServer-' . $TargetServer, 'TargetServerName'     );
        $target['ServerURL']      = $eZINI->variable( 'TargetServer-' . $TargetServer, 'TargetServerURL'      );
        $target['ServerLogin']    = $eZINI->variable( 'TargetServer-' . $TargetServer, 'TargetServerLogin'    );
        $target['ServerPassword'] = $eZINI->variable( 'TargetServer-' . $TargetServer, 'TargetServerPassword' );
    }
}

// error no valid target
if( !is_array( $target ) or empty( $target ) )
{
    $tpl->setVariable( 'ErrorString', 'Please chose a correct target' );
    $templateToLoad = "design:ezstaticexport/export.tpl";
}

//var_dump($target );

//-----------------------
// End ExportTarget stuff
// ----------------------

//------------------------
// Begin ExportType stuff
//-------------------------

$export = '';

// immediate export
if( $eZHTTP->postVariable( 'ExportType' ) == 'immediate' )
{
    $export = 'immediate';

    //-------------------------
    // Begin static export stuff
    //-------------------------
    // Export must run in background
    // runs in foreground for instance : testing
    $staticExport = new eZStaticExport();
    if( $staticExport->buildCache( (int)$eZHTTP->postVariable( 'NodeID' ), $eZHTTP->postVariable( 'ExportSubtree' ) ) )
    {
        if( $staticExport->syncWithTarget( $target ) )
        {
            return true;
        }
    }

    //-------------------------
    // End static export stuff
    //-------------------------
}

// scheduled export => deferred to cron
if( $eZHTTP->postVariable( 'ExportType' ) == 'scheduled' )
{
    $export = 'scheduled';

    // checking dates + times
    $scheduledDate   = explode( '/', $eZHTTP->postVariable( 'ScheduledDate' ) );
    $scheduledHour   = (int)$eZHTTP->postVariable( 'ScheduledHour' );
    $scheduledMinute = (int)$eZHTTP->postVariable( 'ScheduledMinute' );

    $mktime = mktime( $scheduledHour , $scheduledMinute, 0, $scheduledDate[1], $scheduledDate[0], $scheduledDate[2] );

    if( $mktime == -1 )
    {
        $tpl->setVariable( 'ErrorString', 'Please chose a correct date' );
        $templateToLoad = "design:ezstaticexport/export.tpl";
    }
    else
    {
        //include_once( 'extension/ezstaticexport/classes/ezstaticexportscheduler.php' );
        $scheduledExport = new eZStaticExportScheduler();
        $scheduledExport->setAttribute( 'date'        , $mktime );
        $scheduledExport->setAttribute( 'node_id'     , (int)$eZHTTP->postVariable( 'NodeID' ) );
        $scheduledExport->setAttribute( 'repeat_every', $eZHTTP->postVariable( 'RepeatEvery' ) );
        
        $exportSubtree = $eZHTTP->postVariable( 'ExportSubtree' );
        if( isset( $exportSubtree ) and $exportSubtree == 1)
        {
            $scheduledExport->export_subtree = 1;
        }

        $scheduledExport->store();

        $Module->redirectToView( 'scheduledexports' );
    }
}

if( empty( $export ) )
{
    $tpl->setVariable( 'ErrorString', 'Please chose a correct export' );
    $templateToLoad = "design:ezstaticexport/export.tpl";
}

//var_dump( $export );

//------------------------
// End ExportType stuff
//-------------------------


$Result = array();
//$Result['content'] = $tpl->fetch( "design:ezstaticexport/action.tpl" );
$Result['content'] = $tpl->fetch( $templateToLoad );
$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ) );

?>
