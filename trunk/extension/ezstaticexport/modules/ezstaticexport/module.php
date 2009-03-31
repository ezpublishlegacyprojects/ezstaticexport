<?php

$Module = array( 'name' => 'ezstaticexport' ) ;

$ViewList['list'] = array(
   'script'                  => 'list.php',
   'default_navigation_part' => 'ezstaticexportnavigationpart',
   'params'                  => array( '' ) ) ;


// ExportType : immediate / scheduled
// ExportTarget : default target / chosen target
$ViewList['export'] = array(
    'script'                  => 'export.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array( 'ExportType',
                                        'ExportTarget' ),
    'single_post_actions' => array( 'DoExportButton'      => 'DoExport',
                                    'PrepareExportButton' => 'PrepareExport' ),
    'post_action_parameters' => array(

        'DoExport'      => array( 'NodeID'              => 'NodeID',
                                  'Schedule'            => 'Schedule',
                                  'TargetServer'        => 'TargetServer',
                                  'Type'                => 'Type',
                                  'StaticResources'     => 'StaticResources',
                                  'ScheduledDate'       => 'ScheduledDate',
                                  'ScheduledHour'       => 'ScheduledHour',
                                  'ScheduledMinute'     => 'ScheduledMinute',
                                  'ScheduledRecurrence' => 'ScheduledRecurrence' ),

        'PrepareExport' => array( 'NodeID'              => 'NodeID',
                                  'TargetServer'        => 'TargetServer',
                                  'Schedule'            => 'Schedule' ) ) );

$ViewList['exportlist'] = array(
    'script'                  => 'exportlist.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array( '' ) ) ;

$ViewList['action'] = array(
    'script'                  => 'action.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array() ) ;

// Action : list / delete
$ViewList['scheduledexports'] = array(
    'script'                  => 'scheduledexports.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array( 'Action', 'ScheduledExportID' ) ) ;

$ViewList['logs'] = array(
    'script'                  => 'logs.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array( 'ExportID' ) ) ;

$ViewList['downloadlog'] = array(
    'script'                  => 'downloadlog.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array( 'ExportID' ) ) ;

$ViewList['flagcontenttype'] = array(
    'script'                  => 'flagcontenttype.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array(),
    'single_post_actions'     => array( 'eZStaticExportFlagContentTypeButton'       => 'FlagContentType',
                                        'eZStaticExportRemoveContentTypeFlagButton' => 'RemoveFlag' ),
    'post_action_parameters'  => array(
        'FlagContentType' => array( 'NodeID'      => 'NodeID',
                                    'ContentType' => 'ContentType',
                                    'FlagType'    => 'FlagType' ),
        'RemoveFlag' => array( 'FlagIDArray' => 'FlagIDArray' ) ) ) ;

$ViewList['flaglist'] = array(
    'script'                  => 'flaglist.php',
    'default_navigation_part' => 'ezstaticexportnavigationpart',
    'params'                  => array() ) ;

?>
