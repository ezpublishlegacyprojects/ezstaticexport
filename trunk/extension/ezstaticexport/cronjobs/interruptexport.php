<?php
/*
 * 1 = find pending export(s) and start them
 * 2 = find scheduled exports and
 * 3 = if export found
 */
//include_once( "extension/ezstaticexport/classes/ezstaticexportscheduler.php" );
//include_once( "extension/ezstaticexport/classes/ezstaticexporttoken.php" );

$cli = eZCLI::instance();

// scheduled exports
$cli->output();
$cli->output("Checking scheduled exports");
$eZStaticExportScheduled = new eZStaticExportScheduler();
$scheduledExportList = $eZStaticExportScheduled->fetchDueList();
$canExecute = true;

// if scheduled exports are to be executed, we have to interrupt any running immediate transfer
if ( ( count( $scheduledExportList ) > 0 ) and eZStaticExportToken::isRunning() )
{
    $cli->notice("A scheduled export is due, and an export seems to be running");
    $canExecute = false;

    $runningExport = eZStaticExportExport::fetchByStatus( eZStaticExport::STATUS_RUNNING );
    if ( count( $runningExport ) )
    {
        $runningExport = $runningExport[0];

        if ( $runningExport->attribute( 'schedule_type' ) == 'immediate' )
        {
            $cli->output( "The running export is immediate, requesting interruption" );
            $runningExport->requestInterruption();
            $exportID = $runningExport->attribute( 'id' );
            unset( $runningExport );

            // interruption has been requested. We try X times every X seconds
            // until the export IS interrupted
            for ( $i = 1; $i <= 10; $i++ )
            {
                $cli->output("Check #$i : has export #$exportID been interrupted");
                // the export is refetched from the database everytime to get its updated status
                $runningExport = eZStaticExportExport::fetch( $exportID );
                if ( $runningExport->wasInterrupted() )
                {
                    $cli->notice("Export #$exportID was interrupted, we can proceed");
                    $canExecute = true;
                    break;
                }
                sleep( 1 );
            }
        }
        else
        {
            $cli->notice("The running export is not immediate, we can not interrupt it");
        }
    }
}
?>
