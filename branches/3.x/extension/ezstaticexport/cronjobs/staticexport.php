<?php
/*
 * 1 = find pending export(s) and start them
 * 2 = find scheduled exports and
 * 3 = if export found
 */
include_once( "extension/ezstaticexport/classes/ezstaticexportscheduler.php" );
include_once( "extension/ezstaticexport/classes/ezstaticexporttoken.php" );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

$cli =& eZCLI::instance();

if ( eZStaticExportToken::isRunning() )
{
    $cli->error("Uninterrupted exports are running, aborting");
}

// immediate exports
$cli->output();
$cli->output("Handling immediate/pending exports");

// an export is pending: run
if ( eZStaticExportToken::isPending() )
{
    $cli->warning("Found pending exports");
    $exports = eZStaticExportExport::fetchByStatus( array( EZ_STATIC_EXPORT_STATUS_PENDING, EZ_STATIC_EXPORT_STATUS_INTERRUPTED ) );

    foreach( $exports as $export )
    {
        launchExport( $export );
    }
}

// scheduled exports
$cli->output();
$cli->output("Handling scheduled exports");
$eZStaticExportScheduled = new eZStaticExportScheduler();
$scheduledExportList = $eZStaticExportScheduled->fetchDueList();

foreach( $scheduledExportList as $scheduledExport )
{
    // launching export
    $cli->output( "Export date : " . date( 'Y/m/d H:i:s' , $scheduledExport->attribute( 'date' ) ) . " :: " . $scheduledExport->attribute( 'recurrence' ) );
    $cli->output( "Launching scheduled export #" . $scheduledExport->attribute( 'id' ) );

    //$scheduledExport->run( $offset, $limit );
    launchExport( $scheduledExport );
}

function launchExport( $export )
{
    $cli =& eZCLI::instance();

    // does the number of objects needs to be update ?
    $topExportNode = eZContentObjectTreeNode::fetchByPath( $export->attribute( 'path_string' ) );
    $updatedTotal  = eZContentObjectTreeNode::subTreeCount( array(), $topExportNode->attribute( 'node_id' ) );

    $ini    = & eZINI::instance( 'staticexport.ini' );
    $limit  = (int)$ini->variable( 'ExportSettings', 'NumberOfObjectsToExportInTheSameTime' );
    $offset = 0;

    // no this is not an error $total is not available
    // for schedulded exports
    $total  = 0;

    $totalMustBeUpdated = false;

    // the scheduled export became an immediate export after
    // the first pass
    if( get_class( $export )  == "ezstaticexportexport")
    {
        // what is the offset for this export ?
        $offset = $export->attribute( 'offset' );
        $total  = $export->attribute( 'total' );
    }

    if( $updatedTotal != $total )
    {
        $total = $updatedTotal;
        $totalMustBeUpdated = true;
    }

    $remaining = $total - $offset;
    //$isLastExportProcess = false;

    if( $remaining < 0 )
    {
        //$isLastExportProcess = true;
        $remaining = 0;
    }

    if( $remaining < $limit )
    {
        $limit = $remaining;
    }

    $cli->output( "Running export #" . $export->attribute( 'id' ) . ' : ' );
    $cli->output( ' - total     : ' . $total . ' objects' );
    $cli->output( ' - remaining : ' . $remaining . ' objects' );

    $cli->output( "Exporting nodes with offset " . $offset . " and limit " . $limit );

    $currentOffset = $offset;
    $nextOffset    = $currentOffset + 1;
    $nextOffset   += $limit;

    if( $nextOffset >= $total )
    {
        $export->authorizeSync();
        $export->run( $currentOffset, $limit );
        $cli->output( 'Sending signal for syncing' );
        $cli->output();
    }
    else
    {
        // dirty hack need because of the extension architecture
        if( get_class( $export ) == 'ezstaticexportscheduler' )
        {
            $export = $export->run( $currentOffset, $limit );
        }
        else
        {
            $export->run( $currentOffset, $limit );
        }

        if( $totalMustBeUpdated )
        {
            $export->setAttribute( 'total', $updatedTotal );
        }

        $export->setAttribute( 'offset', $nextOffset );
        $export->store();
        //$cli->output( 'Saving new offset for this export : ' . $nextOffset );
    }
}
?>
