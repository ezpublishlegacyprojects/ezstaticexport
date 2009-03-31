<?php
//
// Definition of eZStaticExportScheduler class
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

/*! \file ezstaticexportscheduler.php
*/

/*!
  \class eZStaticExportScheduler ezstaticexportscheduler.php
  \brief Handles export scheduled
*/

//include_once( "kernel/classes/ezpersistentobject.php" );

class eZStaticExportScheduler extends eZStaticExportAbstractExport
{
    /**
     * eZStaticExportExport object launched by the scheduled export
     *
     * @var eZStaticExportExport
     */
	private $export;
	
	/*!
     Initializes a new log.
    */
    function eZStaticExportScheduler( $row = array() )
    {
        parent::__construct( $row );
    }
    
    /**
     * Creates the export with the schedule data
     *
     */
    public function createExportFromScheduleData()
    {
    	// create the export based on the schedule data
        //include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );
        
        // Total number of nodes to export
        $total = $this->attribute('total');
        
    	$row = array( 'type'          => $this->attribute( 'type' ),
                      'path_string'   => $this->attribute( 'path_string' ),
                      'target'        => $this->attribute( 'target' ),
                      'schedule_type' => 'scheduled',
                      'status'        => eZStaticExport::STATUS_PENDING,
                      'total'         => $total,
    				  'static_resources' => $this->attribute('static_resources'));

        $this->export = new eZStaticExportExport( $row );
        $this->export->store();
    }

    /*!
     \reimp
    */
    public static function definition()
    {
        return array( "fields" => array( "id"          => array( "name"     => "id",
                                                                 "datatype" => "integer",
                                                                 "default"  => 0,
                                                                 "required" => true ),

                                         "date"        => array( "name"     => "date",
                                                                 "datatype" => "integer",
                                                                 "default"  => time(),
                                                                 "required" => true),

                                         "type"        => array( "name"     => "Type",
                                                                 "datatype" => "string",
                                                                 "default"  => 0,
                                                                 "required" => true),

                                         "path_string" => array( "name"     => "Path string",
                                                                 "datatype" => "string",
                                                                 "default"  => 0,
                                                                 "required" => true),

                                         "recurrence"  => array( "name"     => "Recurrence",
                                                                 "datatype" => "string",
                                                                 "default"  => null,
                                                                 "required" => false ),

                                         "target"      => array( "name"     => "target",
                                                                 "datatype" => "string",
                                                                 "default"  => '',
                                                                 "required" => false ),
        
        								 "static_resources"	=> array( "name"		=> "static_resources",
        														 	  "datatype"	=> "integer",
        															  "default"		=> 0,
        															  "required"	=> false ) ),

                      "keys" => array( "id" ),
                      "set_functions" => array( "repeat_every" => "setRecurrence" ),
                      "class_name" => "eZStaticExportScheduler",
                      "name" => "ezstaticexport_scheduler",
                      'function_attributes' => array( 'node' => 'getExportedNode',
        											  'status_string' => 'getStatusString',
                                                      'node'          => 'getExportedNode',
                                                      'user'          => 'getUser',
        											  'offset'		  => 'getOffset',
        											  'removing_root_folder'	=> 'isRemovingRootFolder',
        											  'processes_count'			=> 'getProcessesCount',
        											  'archiving_current_folder'	=> 'getArchivingCurrentFolder',
        											  'exporting_static_ressources'	=> 'getExportingStaticRessources',
        											  'total'	=> 'getTotal',
        											  'export_subtree'	=> 'isSubtreeExported' ),
        			  'set_functions' => array( 'offset' 						=> 'setOffset',
        										'removing_root_folder'			=> 'setIsRemovingRootFolder',
        										'archiving_current_folder'		=> 'setArchivingCurrentFolder',
        										'exporting_static_ressources'	=> 'setExportingStaticRessources') );
    }

    /*!
      \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function store($fieldFilters = null)
    {
        eZPersistentObject::store();
    }

    /*!
     \static
      Fetches the token.
    */
    public static function fetch()
    {
        return eZPersistentObject::fetchObject( eZStaticExportScheduler::definition(),
                                                null,
                                                null,
                                                true );
    }

    function fetchList()
    {
        return eZPersistentObject::fetchObjectList( eZStaticExportScheduler::definition(),
                                                    null,
                                                    null,
                                                    array( 'date' => 'desc' ) );
    }

    function setRecurrence( $recurrence )
    {
        $recurrenceValue = null;

        // maps form values with db value fields
        $recurrenceFormDbMapping = array( 'hour'  => 'hourly',
                                          'day'   => 'daily',
                                          'week'  => 'weekly',
                                          'month' => 'monthly' );

        if( isset( $recurrenceFormDbMapping[$recurrence]  ) )
        {
            $recurrenceValue = $recurrenceFormDbMapping[$recurrence];
        }

        $this->setAttribute( 'recurrence', $recurrenceValue );
    }


    function getExportedNode()
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

    function authorizeSync()
    {
        $this->ExportCanSync = true;
    }

    /*!
     Executes the scheduled export:
      - starts the export
      - schedules the next occurence if applicable
    */
    function run( $offset, $limit )
    {
        if( $this->ExportCanSync )
        {
            $this->export->authorizeSync();
        }

        $this->export->store();

        // We'll run the scheduled export after the scheduler process to avoid conflicts in multi-thread mode

        // depending on the return status, create the next occurence or delete the entry
        if ( $this->attribute( 'recurrence' ) == 'none' )
        {
            $this->remove();
        }
        else
        {
            $scheduledDate = $this->attribute( 'date' );
            $recurrence    = $this->attribute( 'recurrence' );

            // checking for recurrence
            if( isset( $recurrence ) )
            {
                // set to next date according to reccurence
                switch( $recurrence )
                {
                    case 'hourly'  :
                        $nextTime = '+1 hour';
                    break;

                    case 'daily'   :
                        $nextTime = '+1 day';
                    break;

                    case 'weekly'  :
                        $nextTime = '+1 week';
                    break;

                    case 'monthly' :
                        $nextTime = '+1 month';
                    break;

                    default :
                        return false ;
                    break;
                }

                $cli = eZCLI::instance();
                $cli->output("Scheduling recuring export #" . $this->attribute('id') . " to $nextTime " );
                $this->setAttribute('date', strtotime( $nextTime, $scheduledDate ) );
                $this->store();
            }
        }

        // run the export
        $this->export->run( $offset, $limit );
        
        // needed for the cronjob
        return $this->export;
    }

    /*!
     Fetches exports that are due to be executed (date < currentdate)
    */
    public static function fetchDueList()
    {
        $aDueList = eZPersistentObject::fetchObjectList( eZStaticExportScheduler::definition(),
                   	                                     null,
                       	                                 array( 'date' => array( '<=', time() ) ),
                           	                             array( 'date' => 'desc' ) );

		
		return $aDueList;
    }
    
    public function getTotal()
    {
    	$topExportNode = eZContentObjectTreeNode::fetchByPath( $this->attribute( 'path_string' ) );
    	$total = 0;
    	
    	if ($this->attribute('type') == eZStaticExportExport::TYPE_NODE)
        	$total = 1;
        else if ($this->attribute('type') == eZStaticExportExport::TYPE_SUBTREE)
        	$total = eZContentObjectTreeNode::subtreeCountByNodeID( array(), $topExportNode->attribute( 'node_id' ) );
        	
        return $total;
    }
    
    /**
     * Calling non-existent methods. Many "function_attributes" and "set_functions" must be called from the eZStaticExportExport $export property
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
    	try
    	{
	    	$def = self::definition();
	    	$aFunctionAttributes = $def['function_attributes'];
	    	$aSetFunction = $def['set_functions'];
	    	$return = null;
	    	
	    	if (!$this->export)
	    		throw new eZStaticExportSchedulerException('$export not defined for scheduler', eZStaticExportSchedulerException::EXPORT_NOT_DEFINED);
	    	
	    	// Check function attributes
	    	if (in_array($name, $aFunctionAttributes))
	    	{
	    		$return = $this->export->$name(); 
	    	}
	    	// Check Set functions
	    	else if(in_array($name, $aSetFunction))
	    	{
	    		$return = $this->export->$name($arguments[0]);
	    	}
	    	else
	    	{
		    	switch($name)
		    	{
		    		case 'addRunningProcess' :
		    			$return = $this->export->addRunningProcess(getmypid());
		    			break;
		    		default :
			    		throw new eZStaticExportSchedulerException('Unsupported method', eZStaticExportSchedulerException::UNSUPPORTED_METHOD);
		    			break;
		    	}
	    	}
	    	
	    	return $return;
    	}
    	catch (eZStaticExportSchedulerException $e)
    	{
    		eZStaticExportLogger::log((string) $e, eZStaticExportLogger::LOG_TYPE_ERROR);
    	}
    }
    
    /**
     * Check if scheduled export is a subtree or a single node export
     * Return 'yes' or 'no'
     *
     * @return string
     */
    public function isSubtreeExported()
    {
    	$exportType = $this->attribute('type');
    	
    	if ($exportType == eZStaticExportExport::TYPE_SUBTREE)
    		return 'yes';
    	else
    		return 'no';
    }
    
    /**
     * Return the next due scheduled export
     *
     * @return array
     */
    public static function fetchNextScheduledExport()
    {
    	$aDueList = self::fetchDueList();
    	if ($aDueList)
    		return $aDueList[0];
    		
    	return false;
    }

    var $ExportCanSync = false;
}

?>
