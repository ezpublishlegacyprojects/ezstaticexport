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

include_once( "kernel/classes/ezpersistentobject.php" );

class eZStaticExportScheduler extends eZPersistentObject
{
    /*!
     Initializes a new log.
    */
    function eZStaticExportScheduler( $row = array() )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \reimp
    */
    function definition()
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
                                                                 "required" => false ) ),

                      "keys" => array( "id" ),
                      "set_functions" => array( "repeat_every" => "setRecurrence" ),
                      "class_name" => "eZStaticExportScheduler",
                      "name" => "ezstaticexport_scheduler",
                      'function_attributes' => array( 'node' => 'getExportedNode' ) );
    }

    /*!
      \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function store()
    {
        eZPersistentObject::store();
    }

    /*!
     \static
      Fetches the token.
    */
    function fetch()
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
        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
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
      - creates the export with the schedule data
      - starts the export
      - schedules the next occurence if applicable
    */
    function run( $offset, $limit )
    {
        $topExportNode = eZContentObjectTreeNode::fetchByPath( $this->attribute( 'path_string' ) );

        // create the export based on the schedule data
        include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );
        $row = array( 'type'          => $this->attribute( 'type' ),
                      'path_string'   => $this->attribute( 'path_string' ),
                      'target'        => $this->attribute( 'target' ),
                      'schedule_type' => 'scheduled',
                      'status'        => EZ_STATIC_EXPORT_STATUS_PENDING,
                      'total'         => eZContentObjectTreeNode::subTreeCount( array(), $topExportNode->attribute( 'node_id' ) ));

        $export = new eZStaticExportExport( $row );

        if( $this->ExportCanSync )
        {
            $export->authorizeSync();
        }

        $export->store();

        // run the export
        $export->run( $offset, $limit );

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

                $cli =& eZCLI::instance();
                $cli->output("Scheduling recuring export #" . $this->attribute('id') . " to $nextTime " );
                $this->setAttribute('date', strtotime( $nextTime, $scheduledDate ) );
                $this->store();
            }
        }

        // needed for the cronjob
        return $export;
    }

    /*!
     Fetches exports that are due to be executed (date < currentdate)
    */
    function fetchDueList()
    {
        return eZPersistentObject::fetchObjectList( eZStaticExportScheduler::definition(),
                                                    null,
                                                    array( 'date' => array( '<=', time() ) ),
                                                    array( 'date' => 'desc' ) );
    }

    var $ExportCanSync = false;
}

?>
