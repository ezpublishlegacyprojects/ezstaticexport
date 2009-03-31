<?php
/*
 * 1 = find pending export(s) and start them
 * 2 = find scheduled exports and
 * 3 = if export found
 */
//include_once( "extension/ezstaticexport/classes/ezstaticexportscheduler.php" );
//include_once( "extension/ezstaticexport/classes/ezstaticexporttoken.php" );
//include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

$cli = eZCLI::instance();
if ( eZStaticExportToken::isRunning() && !eZStaticExportToken::canRunExport() )
{
    $cli->error("Uninterrupted exports are running, aborting");
}

// immediate exports
$cli->output();
$cli->output("Handling immediate/pending exports");

// an export is pending: run
//if ( eZStaticExportToken::isPending() )
if ( eZStaticExportToken::canRunExport() )
{
    $cli->warning("Found pending exports");
    $exports = eZStaticExportExport::fetchByStatus( array( eZStaticExport::STATUS_PENDING, eZStaticExport::STATUS_INTERRUPTED, eZStaticExport::STATUS_RUNNING ) );

    foreach( $exports as $export )
    {
        launchExport( $export );
    }
}

// scheduled exports
$cli->output();
$cli->output("Handling scheduled exports");
//$scheduledExportList = eZStaticExportScheduler::fetchDueList();
$nextScheduleExport = eZStaticExportScheduler::fetchNextScheduledExport();

// One export at a time
if ($nextScheduleExport && !eZStaticExportToken::isRunning())
{	
    // launching export
    $cli->output( "Export date : " . date( 'Y/m/d H:i:s' , $nextScheduleExport->attribute( 'date' ) ) . " :: " . $nextScheduleExport->attribute( 'recurrence' ) );
    $cli->output( "Launching scheduled export #" . $nextScheduleExport->attribute( 'id' ) );

    //$scheduledExport->run( $offset, $limit );
    $nextScheduleExport->createExportFromScheduleData(); 
    
    launchExport( $nextScheduleExport );
}


#####
##### Functions
#####

function launchExport( $export )
{
    $cli = eZCLI::instance();
    $db = eZDB::instance();
    $exportID = $export->attribute('id');
    $ini    = & eZINI::instance( 'staticexport.ini' );
    $limit  = (int)$ini->variable( 'ExportSettings', 'NumberOfObjectsToExportInTheSameTime' );
    $aExcludeContentClasses = $ini->variable('ExportSettings', 'ExcludeContentClasses');
    $offset = 0;

    // does the number of objects needs to be update ?
    $topExportNode = eZContentObjectTreeNode::fetchByPath( $export->attribute( 'path_string' ) );
    $aSubtreeParams = array( 'ClassFilterType' => 'exclude', 
                			 'ClassFilterArray' => $aExcludeContentClasses );
    
    // Export type check
    if ($export->attribute('type') == eZStaticExportExport::TYPE_SUBTREE)
    	$updatedTotal = eZContentObjectTreeNode::subtreeCountByNodeID( $aSubtreeParams, $topExportNode->attribute( 'node_id' ) );
    else if ($export->attribute('type') == eZStaticExportExport::TYPE_NODE)
    	$updatedTotal = $export->attribute( 'total' );


    // no this is not an error $total is not available
    // for schedulded exports
    $total  = 0;

    $totalMustBeUpdated = false;

    // the scheduled export became an immediate export after
    // the first pass
    if( $export instanceof eZStaticExportExport )
    {
        // what is the offset for this export ?
        $offset = $export->attribute( 'offset' );
        $total  = $export->attribute( 'total' );
        $cli->error("Total : $total - UpdatedTotal : $updatedTotal");
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

    $currentOffset = $offset;
    $nextOffset    = $currentOffset + 1;
    $nextOffset   += $limit;
    $runningProcessesCount = $export->attribute('processes_count');
    $pid = getmypid();

    //$export->setAttribute('running_processes', $runningProcessesCount + 1);
    $export->setAttribute( 'offset', $nextOffset );
    $export->store();
    
    // On ne peut synchroniser que si l'on est dans le dernier processus. Aucun autre ne peut etre en cours
    if( $nextOffset >= $total && $runningProcessesCount == 0 )
    {
    	$export->addRunningProcess($pid);
        $export->authorizeSync();
        
        $cli->output( "Exporting nodes with offset " . $offset . " and limit " . $limit );
        $cli->output( 'Sending signal for syncing' );
        // dirty hack need because of the extension architecture
        if( $export instanceof eZStaticExportScheduler )
        {
        	$export = $export->run( $currentOffset, $limit );
        	$exportID = $export->attribute('id');
        }
        else
        {
        	$export->run( $currentOffset, $limit );
        }
        $cli->output();
        
        $db->query("DELETE FROM ezstaticexport_process WHERE export_id=$exportID LIMIT 1");
        //$export->setAttribute('running_processes', $runningProcessesCountUpdate);
        $export->store();
    }
    else if ($currentOffset < $total)
    {
	    if ($export->attribute('removing_root_folder'))
	    {
	    	$cli->warning('A process is currently removing export root folder. Aborting');
	    	return;
	    }
	    else if ($export->attribute('archiving_current_folder'))
	    {
	    	$cli->warning('A process is currently archiving "current" folder. Aborting');
	    	return;
	    }
    	else if ($export->attribute('exporting_static_ressources'))
	    {
	    	$cli->warning('A process is currently exporting static ressources. Aborting');
	    	return;
	    }
    	
    	$export->addRunningProcess($pid);
    	$cli->output( "Exporting nodes with offset " . $offset . " and limit " . $limit );
        
        // dirty hack need because of the extension architecture
        if( $export instanceof eZStaticExportScheduler )
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

        $db->query("DELETE FROM ezstaticexport_process WHERE pid=$pid LIMIT 1");
        //$export->setAttribute('running_processes', $runningProcessesCountUpdate);
        $export->store();
        //$cli->output( 'Saving new offset for this export : ' . $nextOffset );
    }
}

?>
