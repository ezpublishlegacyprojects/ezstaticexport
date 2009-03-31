<?php
//
// Definition of eZStaticExportToken class
//
// Created on: <02-Oct-2007 09:34:56 jr>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.3
// BUILD VERSION: 19751
// COPYRIGHT NOTICE: Copyright (C) 1999-2007 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

/*! \file ezstaticexportexport.php
*/

/*!
  \class eZStaticExportExport ezstaticexportexport.php
  \brief Handles exports in eZ static cache
*/

//include_once( "kernel/classes/ezpersistentobject.php" );
//include_once( "extension/ezstaticexport/classes/ezstaticexporttoken.php");
//include_once( "extension/ezstaticexport/classes/ezstaticexport.php");
//include_once( 'extension/ezstaticexport/classes/ezstaticexportsync.php');

class eZStaticExportExport extends eZStaticExportAbstractExport
{
	// Export types
	const TYPE_SUBTREE = 'subtree';
	const TYPE_NODE = 'node';
	
    /*!
     Initializes a new export.
    */
    function eZStaticExportExport( $row = array() )
    {
        parent::__construct( $row );
    }

    /*!
     \reimp
     \static
    */
    public static function definition()
    {
        return array( "fields" => array( "id"              => array( "name"     => "id",
                                                                     "datatype" => "integer",
                                                                     "default"  => 0,
                                                                     "required" => true ),

                                         // date the export started at
                                         "start_date"      => array( 'name'     => 'Start date',
                                                                     'datatype' => 'string',
                                                                     'default'  => time(),
                                                                     'required' => true ),

                                         // node / subtree
                                         "type"            => array( 'name'     => 'type',
                                                                     'datatype' => 'string',
                                                                     'default'  => 'node',
                                                                     'required' => true ),

                                         // node / subtree
                                         "static_resources" => array( 'name'     => 'static_resources',
                                                                      'datatype' => 'int',
                                                                      'default'  => 0,
                                                                      'required' => true ),

                                         // Path string to the root of the exported subtree
                                         "path_string"     => array( 'name'     => 'Path string',
                                                                     'datatype' => 'string',
                                                                     'default'  => '',
                                                                     'required' => true ),

                                         // export status, see EZ_STATIC_EXPORT_STATUS* constants
                                         "status"          => array( 'name'     => 'status',
                                                                     'datatype' => 'integer',
                                                                     'default'  => 1,
                                                                     'required' => true ),

                                         // export target, see statixexport.ini
                                         "target"          => array( 'name'     => 'target',
                                                                     'datatype' => 'string',
                                                                     'default'  => '',
                                                                     'required' => true ),

                                         // export type, either scheduled or immediate
                                         "schedule_type"   => array( 'name'     => 'Schedule type',
                                                                     'datatype' => 'string',
                                                                     'default'  => 'immediate',
                                                                     'required' => true ),

                                         // user id
                                         "user_id"         => array( 'name'     => 'User ID',
                                                                     'datatype' => 'integer',
                                                                     'default'  => '',
                                                                     'required' => true ),

                                         // total number of objects to exports
                                         "total"           => array( 'name'     => 'total',
                                                                     'datatype' => 'integer',
                                                                     'default'  => 0,
                                                                     'required' => true )

                                         // offset
                                         /*"offset"          => array( 'name'     => 'offset',
                                                                     'datatype' => 'integer',
                                                                     'default'  => 0,
                                                                     'required' => false ),*/
        
                                         ),

                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZStaticExportExport",
                      "name" => "ezstaticexport_export",
                      'function_attributes' => array( 'status_string' => 'getStatusString',
                                                      'node'          => 'getExportedNode',
                                                      'user'          => 'getUser',
        											  'offset'		  => 'getOffset',
        											  'removing_root_folder'	=> 'isRemovingRootFolder',
        											  'processes_count'			=> 'getProcessesCount',
        											  'archiving_current_folder'	=> 'getArchivingCurrentFolder',
        											  'exporting_static_ressources'	=> 'getExportingStaticRessources'),
        			  'set_functions' => array( 'offset' 						=> 'setOffset',
        										'removing_root_folder'			=> 'setIsRemovingRootFolder',
        										'archiving_current_folder'		=> 'setArchivingCurrentFolder',
        										'exporting_static_ressources'	=> 'setExportingStaticRessources') );
    }


    /*!
     \static
      Fetches the export.
    */
    public static function fetch( $id )
    {
        return eZPersistentObject::fetchObject( eZStaticExportExport::definition(),
                                                null,
                                                array( 'id' => $id ),
                                                true );

    }

    /*!
     \static
     Fetches pending exports (status = pending)
    */
    public static function fetchPending()
    {
        return array();
    }


    /*!
     Sets the export as pending
    */
    function setPending()
    {
        $this->setAttribute( 'status', 0 );
    }


    /*!
     Runs the export
    */
    function run( $offset, $limit )
    {
        //include_once( 'lib/ezfile/classes/ezdir.php' );
        //include_once( 'lib/ezfile/classes/ezfile.php' );

        // logging engine
        //include_once( 'extension/ezstaticexport/classes/ezstaticexportlogger.php' );
        eZStaticExportLogger::init( $this );

        // status change: running
        $this->setAttribute( 'status', eZStaticExport::STATUS_RUNNING );
        $this->store();

        if ( !$this->checkFolders() )
        {
            return false;
        }

        // all folders are okay, we can start the static export process
        eZStaticExportLogger::log( "Beggining export to $this->workFolder" );
        $staticExport = new eZStaticExport( $this );
        $staticExport->setOutputHandler( 'ezcli' );
        $exportResult = $staticExport->run( $offset, $limit );

        switch( $exportResult )
        {
            // export was interrupted by another export
            case eZStaticExport::RESULT_INTERRUPTED:
                eZStaticExportLogger::log( 'Export was interrupted' );
                return;
                break;

            // export was completed correctly
            // the export can be a full export or
            // a partiel export
            case eZStaticExport::RESULT_DONE:
                if( $this->CanSync )
                {
                    eZStaticExportLogger::log( 'Export ended successfully, startying sync' );

                    // the syncing process can start since the export was successful
                    $this->setAttribute( 'status', eZStaticExport::STATUS_SYNCING );
                    $syncResult = $this->syncExport( $this->workFolder );

                    // current dir is archived (YYYYMMDD-HHii)
                    if (file_exists($this->currentFolder))
                    {
	                    $archiveFolder = $this->targetFolder . date( 'Ymd-Hi', filemtime( $this->currentFolder ) );
	                    if (file_exists($archiveFolder)) // Archive folder already exists. Add a suffix
	                    {
	                    	$counter = 1;
	                    	while(file_exists("$archiveFolder-$counter"))
	                    	{
	                    		$counter++;
	                    	}
	                    	$archiveFolder = "$archiveFolder-$counter";
	                    }
	                    $archiveFolder .= '/';
	                    eZStaticExportLogger::log( "Archiving current folder" );
	                    if ( !eZStaticExportExport::rename( $this->currentFolder, $archiveFolder ) )
	                    {
	                        eZStaticExportLogger::log( "An error occurred archiving current folder", eZStaticExportLogger::LOG_TYPE_ERROR );
	                    }
                    }

                    // work dir is renamed to current
                    eZStaticExportLogger::log( "Setting work folder as current" );
                    if ( !eZStaticExportExport::rename( $this->workFolder, $this->currentFolder ) )
                    {
                        eZStaticExportLogger::log( "An error occured renaming work folder to current", eZStaticExportLogger::LOG_TYPE_ERROR );
                    }

                    // status change: completed
                    $this->setAttribute( 'status', eZStaticExport::STATUS_COMPLETED );
                    $this->store();
                    eZStaticExportLogger::log( "Export completed successfully" );
                    //print( "Memory usage : " . xdebug_memory_usage() . "\n" );
                    //print( "Memory peak usage : " . xdebug_peak_memory_usage() . "\n" );
                }
                else
                {
                    // even if this is a recurrent or scheduled export it is now immediate and pending
                    $this->setAttribute( 'status', eZStaticExport::STATUS_PENDING );
                    $this->setAttribute( 'schedule_type', 'immediate' );
                    $this->store();
                }
                break;

            default:
                eZStaticExportLogger::log( "Unknown return status $exportResult" );
        }

    }

    /*!
     Authorize sync process
     */
    function authorizeSync()
    {
        $this->CanSync = true;
    }

    /*!
     Returns the string matching the export's status
    */
    function &getStatusString()
    {
        $statusStrings = array( 1 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Pending' ),
                                2 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Running' ),
                                3 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Failed' ),
                                4 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Deleted' ),
                                5 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Completed' ),
                                6 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Requested for interruption' ),
                                7 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Interrupted' ),
                                8 => ezi18n( 'extension/ezstaticexport/statusstrings', 'Transferring' ) );
        $status = (int)$this->attribute( 'status' );
        if ( isset( $statusStrings[$status] ) )
        {
            $return = $statusStrings[$status];
        }
        else
        {
            $return = null;
        }

        return $return;
    }
    
    /**
     * Returns the number of running processes for a currently running export
     *
     * @static
     * @return int
     */
    function fetchRunningProcessesCount()
    {
    	$db = eZDB::instance();
    	$res = $db->arrayQuery("SELECT COUNT(*) AS count FROM ezstaticexport_process");
    	
    	return $res[0]['count'];
    }
    
    /**
     * Alias of fetchRunningProcessesCount to map the result as an attribute of the persistent object
     *
     * @return unknown
     */
    function getProcessesCount()
    {
    	return eZStaticExportExport::fetchRunningProcessesCount();
    }

    /*!
     \static
     Fetches exports count by status
     \a $statusList can be an array of status
    */
    public static function fetchCountByStatus( $statusList )
    {
        $db = eZDB::instance();
        $statusString = implode( "','", $statusList );
        $res = $db->arrayQuery( "SELECT COUNT(*) AS count FROM ezstaticexport_export WHERE status IN ('$statusString')");
        return $res[0]['count'];
    }

    /*!
     \static
     Fetches exports by status
    */
    public static function fetchByStatus( $statusList )
    {
        if ( is_array( $statusList ) ) $statusList = array( $statusList );
        $aObjectList = eZPersistentObject::fetchObjectList( eZStaticExportExport::definition(), null, array( 'status' => $statusList ), array('id' => 'asc') ); 

        return $aObjectList;
    }

    /*!
     \static
     Fetches all exports
    */
    public static function fetchList()
    {
        return eZPersistentObject::fetchObjectList( eZStaticExportExport::definition() );
    }

    /*!
     Returns the eZContentObjectTreeNode of the export
    */
    function &getExportednode()
    {
        //include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
        if ( $node = eZContentObjectTreeNode::fetchByPath( $this->attribute( 'path_string' ) ) )
        {
            return $node;
        }
        else
        {
            return null;
        }
    }

    /*!
     Interrupts a running immediate export, only if export_schedule == immediate and
     status == eZStaticExport::STATUS_RUNNING

     \return true if transfer was correctly interrupted, false otherwise
    */
    function requestInterruption()
    {
        if ( ( $this->attribute( 'schedule_type' ) == 'immediate' ) &&
             ( $this->attribute( 'status' ) == eZStaticExport::STATUS_RUNNING ) )
        {
            $this->setAttribute( 'status', eZStaticExport::STATUS_INTERRUPT_REQUESTED );
            $this->store();
            eZStaticExportLogger::log( "Interruption requested: setting status to eZStaticExport::STATUS_INTERRUPT_REQUESTED" );
            return true;
        }
        else
        {
            return false;
        }
    }

    /*!
     Interupts the current export if interruption has been requested
     \a eZStaticExport::requestInterruption
     \return bool true if the export has been interrupted
    */
    function interrupt()
    {
        if ($this->attribute( 'status' ) == eZStaticExport::STATUS_INTERRUPT_REQUESTED )
        {
            eZStaticExportLogger::log( "Interruption requested" );
            $cli = eZCLI::instance();
            $ini = eZINI::instance( 'staticexport.ini' );
            $staticFolder = $ini->variable( 'ExportSettings', 'StaticStorageDir' );
            $workfFolder = $staticFolder . $this->attribute( 'target' ) . '/work/';

            eZDir::recursiveDelete( $workfFolder );

            $this->setAttribute( 'status', eZStaticExport::STATUS_INTERRUPTED );
            $this->store();

            eZStaticExportLogger::log( "Interruption completed" );

            return true;
        }
        else
        {
            return false;
        }
    }

    /*!
     Used to check after the \a interrupt method was called
     if the interruption was completed
    */
    function wasInterrupted()
    {
        return ( $this->attribute( 'status' ) == eZStaticExport::STATUS_INTERRUPTED );
    }

    /*!
     Starts the syncing process for the current export
    */
    function syncExport( $directory )
    {
        // the publication servers are fetched from the target's INI settings
        // each target server is defined by:
        //   - an URL (protocol://login:password@host:port/path
        //   - options, as defined for each driver, comma separated
        $target = $this->target();
        foreach( $target['publishing'] as $publishingTarget )
        {
            $syncEngine = eZStaticExportSync::loadDriver( $publishingTarget['scheme'] );
            if ( $syncEngine == false )
            {
                eZStaticExportLogger::log( "Unable to load sync driver [".$publishingTarget['scheme']."]" );
                break;
            }

            // TODO FIXME
            eZStaticExportLogger::log( "Syncing to " . $publishingTarget['host'] . " using " . $publishingTarget['scheme'] );
            $syncEngine->injectArgs( $publishingTarget['options'] );
            $syncEngine->ExportSource = $directory;
            $syncEngine->setTargetInfos( $publishingTarget );
            $syncEngine->preSyncHook();
            $syncEngine->startSync();
            $syncEngine->postSyncHook();
        }
    }

    /*!
     Returns the target server informations for this export
     \private
     \return array of target infos: name, url, publishing => array(url, options, scheme, host, port,
    */
    function target()
    {
        $ini = eZINI::instance( 'staticexport.ini' );

        if ( $this->attribute( 'target' ) == 'DefaultTargetServer' )
        {
            $INIBlockName = 'DefaultTargetServerSettings';
        }
        else
        {
            $INIBlockName = 'TargetServer-' . $this->attribute( 'target' );
        }

        // the INI file is first searched for the given block, either default or a specific one
        if ( !$ini->hasSection( $INIBlockName ) )
            return false;

        // target settings are read from the INI file
        $target['name']       = $ini->variable( $INIBlockName, 'TargetServerName' );
        $target['url']        = $ini->variable( $INIBlockName, 'TargetServerURL' );
        $target['publishing'] = array();

        $publishingTargets = $ini->variable( $INIBlockName, 'PublishingTargets' );
        foreach( $publishingTargets as $publishingTarget )
        {
            $publishingTargetInfos = explode( ';', $publishingTarget );
            $urlInfos = parse_url( $publishingTargetInfos[0] );
            $publishingTarget = array( 'url'          => $publishingTargetInfos[0],
                                       'scheme'       => $urlInfos['scheme'],
                                       'host'         => $urlInfos['host'],
                                       'port'         => isset( $urlInfos['port'] ) ? $urlInfos['port'] : false,
                                       'user'         => isset( $urlInfos['user'] ) ? $urlInfos['user'] : '',
                                       'pass'         => isset( $urlInfos['pass'] ) ? $urlInfos['pass'] : '',
                                       'path'         => $urlInfos['path'],
                                       'options'      => '' );

            // additionnal infos (options, content-type)
            if ( count( $publishingTargetInfos ) > 1 )
            {
                for( $i = 1; $i < count( $publishingTargetInfos ); $i++ )
                {
                    if ( strstr( $publishingTargetInfos[$i], ':' ) !== false )
                    {
                        list( $addInfoName, $addInfoValue ) = explode( ':', $publishingTargetInfos[$i] );
                        $publishingTarget[$addInfoName] =  $addInfoValue;
                    }
                    else
                    {
                        eZStaticExportLogger::log( $publishingTarget, "Invalid publishing target definition, options are missing" );
                    }
                }
            }
            $target['publishing'][] = $publishingTarget;
        }
        return $target;
    }

    /*!
     Returns the user who requested the export
    */
    function &getUser()
    {
        $user = eZUser::fetch( $this->attribute('user_id' ) );
        return $user;
    }

    /*!
     Renames a file/folder to another name
     Removes trailing / from folder names so that it works...
     \return bool true if the operation was successful
     \static
    */
    public static function rename( $source, $target )
    {
        return eZFile::rename( rtrim( $source, '/' ), rtrim( $target, '/' ) );
    }

    /*!
     Runs all the folders check prior to running an export
     \return bool
    */
    function checkFolders()
    {
        $ini = eZINI::instance( 'staticexport.ini' );
        $staticFolder = $ini->variable( 'ExportSettings', 'StaticStorageDir' );

        // check for static export storage dir existence
        if ( !file_exists( $staticFolder ) )
        {
            if ( !eZDir::mkdir( $staticFolder, false, true ) )
            {
                $this->setAttribute( 'status', eZStaticExport::STATUS_FAILED );
                $this->store();
                eZStaticExportLogger::log( 'Static export folder could not be created', eZStaticExportLogger::LOG_TYPE_ERROR );
                return false;
            }
            eZStaticExportLogger::log( 'Static export folder created successfully');
        }

        // check for static export storage dir existence
        if ( !is_writeable( $staticFolder ) )
        {
            $this->setAttribute( 'status', eZStaticExport::STATUS_FAILED );
            $this->store();
            eZStaticExportLogger::log( 'Static export folder is not writeable', eZStaticExportLogger::LOG_TYPE_ERROR );
            return false;
        }

        // check target folder, and create if necessary
        $this->targetFolder = $staticFolder . $this->attribute( 'target' ) . '/';
        if (!file_exists( $this->targetFolder ) )
        {
            if ( !eZDir::mkdir( $this->targetFolder, false, true ) )
            {
                $this->setAttribute( 'status', eZStaticExport::STATUS_FAILED );
                $this->store();
                eZStaticExportLogger::log( 'Static export storage folder could not be created', eZStaticExportLogger::LOG_TYPE_ERROR );
                return false;
            }
        }

        // work directory init
        $this->workFolder    = $this->targetFolder . 'work/';
        $this->currentFolder = $this->targetFolder . 'current/';
        if ( !eZDir::mkdir( $this->workFolder, false, true ) )
        {
            $this->setAttribute( 'status', eZStaticExport::STATUS_FAILED );
            $this->store();
            eZStaticExportLogger::log( 'Static export work folder could not be created', eZStaticExportLogger::LOG_TYPE_ERROR );
            return false;
        }

        // if <exportdir>/<target>/current exists, its content is copied to <exportdir>/<target>/work,
        // and <exportdir>/<target>/current will be renamed to archive once the export is finished
        if ( file_exists( $this->currentFolder ) )
        {
            $this->setAttribute('archiving_current_folder', true);
        	$tmpFolder = $this->targetFolder . 'tmp/';
            eZDir::mkdir( $tmpFolder );

            // current/design/
            if ( file_exists( $this->currentFolder . "design/" ) )
            {
                eZStaticExportExport::rename( $this->currentFolder . "design/", $tmpFolder . "design/" );
            }
            if ( file_exists( $this->currentFolder . "extension/" ) )
            {
                eZStaticExportExport::rename( $this->currentFolder . "extension/", $tmpFolder . "extension/" );
            }

            $itemsCount = count( eZDir::findSubitems( $this->currentFolder ) );
            $copiedItemsCount = eZDIR::copy( $this->currentFolder, $this->workFolder, false );

            if ( $copiedItemsCount < $itemsCount )
            {
                $this->setAttribute( 'status', eZStaticExport::STATUS_FAILED );
                $this->store();
                eZStaticExportLogger::log( "Not all files were copied from $this->currentFolder to $this->workFolder", eZStaticExportLogger::LOG_TYPE_ERROR );
                return false;
            }

            // tmp/design/ is moved back to current/
            if ( file_exists( $tmpFolder . "design/" ) )
            {
                eZStaticExportExport::rename( $tmpFolder . "design/",    $this->currentFolder . "design/" );
            }
            // tmp/extension/ is moved back to current/
            if ( file_exists( $tmpFolder . "extension/" ) )
            {
                eZStaticExportExport::rename( $tmpFolder . "extension/", $this->currentFolder . "extension/" );
            }
            // and we finally remove the temporary folder
            rmdir( $tmpFolder );
            $this->setAttribute('archiving_current_folder', false);
        }

        // copy of static resources
        if ( $this->attribute( 'static_resources' ) == 1 )
        {
            $this->setAttribute('exporting_static_ressources', true);
        	eZDebug::writeDebug( "Copying static resources" );
            $staticResourcesSiteAccess = $ini->variable( 'ExportSettings', 'Siteacess' );

            $siteINI = eZINI::instance( 'site.ini' );
            $siteINI->prependOverrideDir( "siteaccess/$staticResourcesSiteAccess", false, 'siteaccess' );
            $designs = array_merge( array( 'standard', $siteINI->variable( 'DesignSettings', 'SiteDesign') ),
                                    $siteINI->variable( 'DesignSettings', 'AdditionalSiteDesignList' ) );

            // we loop over the active design folders and copy relevant items
            $designDir = 'design/';
            eZDir::mkdir( $this->workFolder . $designDir );
            foreach( $designs as $design )
            {
                $thisDesignDir = $designDir . $design . '/';
                if ( file_exists( $thisDesignDir ) )
                {
                    $subdirs = eZDir::findSubdirs( $thisDesignDir, false, '/^(templates|override)$/' );
                    if ( count( $subdirs ) > 0 )
                    {
                        eZDir::mkdir( $this->workFolder . $thisDesignDir );
                    }
                    foreach( $subdirs as $subdir )
                    {
                        $sourceDir = $thisDesignDir . $subdir;
                        $targetDir = $this->workFolder . $thisDesignDir;
                        eZDebug::writeDebug( "$sourceDir => $targetDir", "Copying");
                        $copiedItemsCount = eZDir::copy( $sourceDir, $targetDir, true );
                    }
                }
            }
            if ( count( eZDir::findSubItems( $this->workFolder . $designDir ) ) == 0 )
            {
                rmdir( $this->workFolder . $designDir );
            }

            // we need the list of active design extensions
            $designINI = eZINI::instance( 'design.ini' );
            $designINI->prependOverrideDir( "siteaccess/$staticResourcesSiteAccess", false, 'siteaccess' );
            $designExtensions = $designINI->variable( 'ExtensionSettings', 'DesignExtensions' );

            // and we do the same for extension designs
            $extensionDir = 'extension/';
            eZDir::mkdir( $this->workFolder . $extensionDir );
            foreach( $designExtensions as $designExtension )
            {
                $extensionDesignDir = $extensionDir . $designExtension . '/design/';
                foreach( $designs as $design )
                {
                    $thisExtensionDesignDir = $extensionDesignDir . $design . '/';
                    if ( file_exists( $thisExtensionDesignDir ) )
                    {
                        $subdirs = eZDir::findSubdirs( $thisExtensionDesignDir, false, '/^(templates|override)$/' );
                        if ( count( $subdirs ) > 0 )
                        {
                            eZDir::mkdir( $this->workFolder . $thisExtensionDesignDir, false, true );
                        }
                        foreach( $subdirs as $subdir )
                        {
                            $sourceDir = $thisExtensionDesignDir . $subdir;
                            $targetDir = $this->workFolder . $thisExtensionDesignDir;
                            eZDebug::writeDebug( "$sourceDir => $targetDir", "Copying");
                            eZDir::copy( $sourceDir, $targetDir, true );
                        }
                    }
                }
            }
            // we remove the extension folder if it is empty
            if ( count( eZDir::findSubItems( $this->workFolder . $extensionDir ) ) == 0 )
            {
                rmdir( $this->workFolder . $extensionDir );
            }
            
            $this->setAttribute('exporting_static_ressources', false);
        }

        return true;
    }
    
	/**
     * Get the current export offset in the table ezstaticexport_offset
     * If no record can be found in the table (first pass for the current export), an entry is inserted with offset 0
     *
     * @return int
     */
    public function getOffset()
    {
    	$exportID = $this->attribute('id');
    	$res = $this->db->arrayQuery("SELECT * FROM ezstaticexport_offset WHERE export_id = $exportID");
    	
    	if (!$res)
    	{
    		$offset = 0;
    		$this->db->begin();
    		$this->db->query("INSERT INTO ezstaticexport_offset (export_id, offset) VALUES($exportID, $offset)");
    		$this->db->commit();
    		
    	}
    	else
    	{
    		$offset = $res[0]['offset'];
    	}
    	
    	return $offset;
    }
    
    public function setOffset($offset)
    {
    	$exportID = $this->attribute('id');
    	
    	$currentOffset = $this->getOffset();
    	$this->db->begin();
    	$this->db->query("UPDATE ezstaticexport_offset SET offset = $offset WHERE export_id = $exportID");
    	$this->db->commit();
    	$newOffset = $this->getOffset();
    	
    	if ($newOffset < $currentOffset)
    	{
    		eZDebug::writeError('New offset is smaller than old offset !');
    		return $currentOffset; // force data dirty
    	}
    	
    	return $newOffset;
    }
    
    public function setIsRemovingRootFolder($bStatus)
    {
    	$iStatus = $bStatus ? 1 : 0;
    	
    	$this->db->begin();
    	$this->db->query('UPDATE ezstaticexport_offset SET removing_root_folder = '.$iStatus.' WHERE export_id= '.$this->attribute('id'));
    	$this->db->commit();
    	
    	return $bStatus;
    }
    
    public function isRemovingRootFolder()
    {
    	$res = $this->db->arrayQuery('SELECT removing_root_folder FROM ezstaticexport_offset WHERE export_id='.$this->attribute('id'));
    	
    	if ($res && $res[0]['removing_root_folder'])
    		return true;
    	else
    		return false;
    	
    }
    
    public function addRunningProcess($pid)
    {
    	$exportID = $this->attribute('id');
    	
    	$this->db->begin();
	    $this->db->query("INSERT INTO ezstaticexport_process VALUES('', $exportID, $pid)");
	    $this->db->commit();
    }
    
    public function setArchivingCurrentFolder($bStatus)
    {
    	$exportID = $this->attribute('id');
    	$iStatus = $bStatus ? 1 : 0;
    	
    	$this->db->begin();
    	$this->db->query("UPDATE ezstaticexport_offset SET archiving_current_folder = $iStatus WHERE export_id = $exportID");
    	$this->db->commit();
    	
    	return $bStatus;
    }
    
    public function getArchivingCurrentFolder()
    {
    	$res = $this->db->arrayQuery('SELECT archiving_current_folder FROM ezstaticexport_offset WHERE export_id='.$this->attribute('id'));
    	
    	if ($res && $res[0]['archiving_current_folder'])
    		return true;
    	else
    		return false;
    }
    
    public function setExportingStaticRessources($bStatus)
    {
    	$exportID = $this->attribute('id');
    	$iStatus = $bStatus ? 1 : 0;
    	
    	$this->db->begin();
    	$this->db->query("UPDATE ezstaticexport_offset SET exporting_static_ressources = $iStatus WHERE export_id = $exportID");
    	$this->db->commit();
    	
    	return $bStatus;
    }
    
	public function getExportingStaticRessources()
    {
    	$res = $this->db->arrayQuery('SELECT exporting_static_ressources FROM ezstaticexport_offset WHERE export_id='.$this->attribute('id'));
    	
    	if ($res && $res[0]['exporting_static_ressources'])
    		return true;
    	else
    		return false;
    }
	
    var $currentFolder, $workFolder, $targetFolder;
    var $CanSync = false;
    
}

?>
