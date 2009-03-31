<?php
include_once( "kernel/common/template.php" );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

$Module =& $Params["Module"];
$Result = array();
$tpl =& templateInit();

// prepare export: form displayed when (scheduled|immediate) export to (default|other target)
// is used from the contextual menu
if ( $Module->isCurrentAction( 'PrepareExport' ) )
{
    $possibleSchedule      = array( 'immediate', 'scheduled' );

    // schedule check
    if ( in_array( $Module->actionParameter( 'Schedule' ), $possibleSchedule ) )
    {
        $schedule = $Module->actionParameter( 'Schedule' );
    }
    else
    {
        $schedule = $possibleSchedule[0];
    }

    // targetServer check
    if ( $Module->actionParameter( 'TargetServer' ) == 'DefaultTargetServer' )
    {
        $targetServer = $Module->actionParameter( 'TargetServer' );
    }
    else
    {
        $targetServer = '';
    }

    // node ID check
    $nodeID = $Module->actionParameter( 'NodeID' );
    if ( !$node = eZContentObjectTreeNode::fetch( $nodeID, false, false ) )
    {
        $tpl->setVariable( 'error', 'node' );
    }

    $tpl->setVariable( 'export_schedule', $schedule );
    $tpl->setVariable( 'export_target',   $targetServer );
    $tpl->setVariable( 'export_node_id',  $nodeID );

    $viewTemplate = 'ezstaticexport/export.tpl';
    $secondLevelPath = array( 'url' => false,
                              'text' => ezi18n( 'extension/ezstaticexport', 'New immediate export of [%node_name]',
                                                '', array('%node_name' => $node['name'] ) ) );
}

// DoExport: creates the export entry in the database and shows a confirmation message
if ( $Module->isCurrentAction( 'DoExport' ) )
{
    // scheduled export
    if ( $Module->actionParameter( 'Schedule' ) == 'scheduled' )
    {
        $valid = true;

        // checking dates + times
        $scheduledDate   = explode( '/', $Module->actionParameter( 'ScheduledDate' ) );
        $scheduledHour   = (int)$Module->actionParameter( 'ScheduledHour' );
        $scheduledMinute = (int)$Module->actionParameter( 'ScheduledMinute' );

        $exportDate = mktime( $scheduledHour , $scheduledMinute, 0, $scheduledDate[1], $scheduledDate[0], $scheduledDate[2] );

        if( $exportDate == -1 )
        {
            $errors[] = ezi18n( 'extension/ezstaticexport', 'Please chose a correct date' );
            $valid = false;
        }
        else
        {
            $row['date'] = $exportDate;
        }


        // export node: if the node is invalid, we redirect since this is a fatal error
        $exportNodeID = $Module->actionParameter( 'NodeID' );
        if( !$exportedNode = eZContentObjectTreeNode::fetch( $exportNodeID ) )
        {
            return $Module->redirectTo( '/' );
        }
        else
        {
            $row['path_string'] = $exportedNode->attribute( 'path_string' );
            $topExportNode = eZContentObjectTreeNode::fetchByPath( $row['path_string'] );
            $row['total']  = eZContentObjectTreeNode::subTreeCount( array(), $topExportNode->attribute( 'node_id' ) );
        }

        // target server check
        $ini =& eZINI::instance( 'staticexport.ini' );
        if ( !$Module->hasActionParameter('TargetServer' ) or
             ( ( $Module->actionParameter('TargetServer' ) != 'DefaultTargetServer' ) && !$ini->hasGroup( 'TargetServer-' . $Module->actionParameter('TargetServer' ) ) ) )
        {
            $errors[] = ezi18n( 'extension/ezstaticexport',
                                "You have to select a valid target server" );
            $valid = false;
            $row['target'] = false;
        }
        else
        {
            $row['target'] = $Module->actionParameter( 'TargetServer' );
        }

        // recurrence
        $possibleRecurences = array( 'none', 'monthly', 'weekly', 'hourly', 'daily' );
        if ( !$Module->hasActionParameter( 'ScheduledRecurrence') or
             !in_array( $Module->actionParameter( 'ScheduledRecurrence' ), $possibleRecurences ) )
        {
            $errors[] = ezi18n( 'extension/ezstaticexport', 'The specified recurrence is not valid' );
            $row['recurrence'] = false;
            $valid = false;
        }
        else
        {
            $row['recurrence'] = $Module->actionParameter( 'ScheduledRecurrence' );
        }

        // export type (subtree or node)
        $possibleExportTypes = array( 'node', 'subtree' );
        if ( !$Module->hasActionParameter( 'Type') or !in_array( $Module->actionParameter( 'Type' ), $possibleExportTypes ) )
        {
            $errors[] = ezi18n( 'extension/ezstaticexport', 'The export type is not valid, select either node or subtree' );
            $row['type'] = false;
            $valid = false;
        }
        else
        {
            $row['type'] = $Module->actionParameter( 'Type' );
        }

        if ( $Module->hasActionParameter( 'StaticResources' ) )
        {
            $row['static_resources'] = 1;
        }
        else
        {
            $row['static_resources'] = 0;
        }

        // valid data, we can create the scheduled export
        if ( $valid )
        {
            $user = eZUser::currentUser();
            $row['user_id'] = $user->attribute( 'contentobject_id' );

            include_once( 'extension/ezstaticexport/classes/ezstaticexportscheduler.php' );
            $schedule = new eZStaticExportScheduler( $row );
            $schedule->store();

            $tpl->setVariable( 'export_schedule', 'scheduled' );
            $tpl->setVariable( 'schedule',        $schedule );

            $viewTemplate = 'ezstaticexport/export_added.tpl';
            $secondLevelPath = array( 'url' => false, 'text' => ezi18n( 'extension/ezstaticexport', 'Done' ) );
        }
        // an error has occured, we show the form again
        else
        {
            $tpl->setVariable( 'errors', $errors );

            $tpl->setVariable( 'export_schedule',   'scheduled' );
            $tpl->setVariable( 'export_recurrence', $row['recurrence'] );
            $tpl->setVariable( 'export_target',     $row['target'] );
            $tpl->setVariable( 'export_node_id',    $exportNodeID );
            $tpl->setVariable( 'export_type',       $row['type'] );
            $tpl->setVariable( 'export_date',       $Module->actionParameter( 'ScheduledDate' ) );
            $tpl->setVariable( 'export_hour',       $Module->actionParameter( 'ScheduledHour' ) );
            $tpl->setVariable( 'export_minute',     $Module->actionParameter( 'ScheduledMinute' ) );
            eZDebug::writeDebug($row['static_resources'], "Setting staticresources to");

            $tpl->setVariable( 'export_staticresources', $row['static_resources'] );

            $viewTemplate = 'ezstaticexport/export.tpl';
            $secondLevelPath = array( 'url' => false,
                                      'text' => ezi18n( 'extension/ezstaticexport', 'New scheduled export of [%node_name]',
                                                        '', array('%node_name' => $exportedNode->getName() ) ) );
        }
    }
    // immediate export
    else
    {
        $valid = true;
        include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );

        // node check: if !ok, we redirect since this is a non recoverable error
        $exportedNodeID = $Module->hasActionParameter( 'NodeID' ) ? $Module->actionParameter( 'NodeID' ) : false;
        if ( !$exportedNodeID or !$exportedNode = eZContentObjectTreeNode::fetch( $exportedNodeID ) )
        {
            return $Module->redirectTo('/');
        }
        else
        {
            $row['path_string'] = $exportedNode->attribute( 'path_string' );
            $topExportNode = eZContentObjectTreeNode::fetchByPath( $row['path_string'] );
            $row['total']  = eZContentObjectTreeNode::subTreeCount( array(), $topExportNode->attribute( 'node_id' ) );
        }

        // target server check
        $ini =& eZINI::instance( 'staticexport.ini' );
        if ( !$Module->hasActionParameter( 'TargetServer' ) or
             ( ( $Module->actionParameter( 'TargetServer' ) != 'DefaultTargetServer' ) && !$ini->hasGroup( 'TargetServer-' . $Module->actionParameter( 'TargetServer' ) ) ) )
        {
            $errors[] = ezi18n( 'extension/ezstaticexport',
                                "You have to select a valid target server" );
            $valid = false;
            $row['target'] = false;
        }
        else
        {
            $row['target'] = $Module->actionParameter( 'TargetServer' );
        }

        // type
        if ( !$Module->hasActionParameter( 'Type' ) or !in_array( $Module->actionParameter( 'Type' ), array( 'subtree', 'node' ) ) )
        {
            $errors[] = ezi18n( 'extension/ezstaticexport', 'You have to select a valid export type' );
            $valid = false;
            $row['type'] = false;
        }
        else
        {
            $row['type'] = $Module->actionParameter( 'Type' );
        }


        if ( $Module->hasActionParameter( 'StaticResources' ) )
        {
            $row['static_resources'] = 1;
        }
        else
        {
            $row['static_resources'] = 0;
        }

        if ( $valid )
        {
            $user = eZUser::currentUser();
            $row['user_id'] = $user->attribute( 'contentobject_id' );
            $row['status']          = EZ_STATIC_EXPORT_STATUS_PENDING;
            $row['export_schedule'] = 'immediate';

            
            $export = new eZStaticExportExport( $row );
            $export->store();

            $tpl->setVariable( 'export',          $export );
            $tpl->setVariable( 'export_schedule', 'immediate' );

            $viewTemplate = 'ezstaticexport/export_added.tpl';
            $secondLevelPath = array( 'url' => false, 'text' => ezi18n( 'extension/ezstaticexport', 'Done' ) ) ;
        }
        else
        {
            $tpl->setVariable( 'export_schedule',   'immediate' );
            $tpl->setVariable( 'export_target',     $row['target'] );
            $tpl->setVariable( 'export_node_id',    $exportedNodeID );
            $tpl->setVariable( 'export_type',       $row['type'] );
            $tpl->setVariable( 'errors',            $errors );
            $tpl->setVariable( 'static_resources',  $row['static_resources'] );

            $viewTemplate = 'ezstaticexport/export.tpl';
            $secondLevelPath = array( 'url' => false,
                                      'text' => ezi18n( 'extension/ezstaticexport', 'New immediate export of [%node_name]',
                                                        '', array('%node_name' => $exportedNode->getName() ) ) );
        }
    }

}

$Result['left_menu'] = 'design:parts/ezstaticexport/menu.tpl';
$Result['content'] =& $tpl->fetch( "design:$viewTemplate" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'extension/ezstaticexport', 'Static export' ) ),
                         $secondLevelPath );
?>
