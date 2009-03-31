<?php
//
// Definition of eZStaticExportLog class
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

/*! \file ezstaticexportlog.php
*/

/*!
  \class eZStaticExportLog ezstaticexportlog.php
  \brief Handles logs in eZ static cache
*/

//include_once( "kernel/classes/ezpersistentobject.php" );

define( 'EZSTATICEXPORT_LOG_ERROR',   1 );
define( 'EZSTATICEXPORT_LOG_WARNING', 2 );

class eZStaticExportLog extends eZPersistentObject
{
    /*!
     Initializes a new log.
    */
    function eZStaticExportLog( $row = array() )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \reimp
    */
    public static function definition()
    {
        return array( "fields" => array( "id"        => array( "name"     => "id",
                                                               "datatype" => "integer",
                                                               "default"  => 0,
                                                               "required" => true ),

                                         "export_id" => array( "name"     => "export_id",
                                                               "datatype" => "integer",
                                                               "default"  => 0,
                                                               "required" => true),

                                         "date"      => array( "name"     => "date",
                                                               "datatype" => "integer",
                                                               "default"  => time(),
                                                               "required" => true),

                                         "status"    => array( "name"     => "status",
                                                               "datatype" => "integer",
                                                               "default"  => 1,
                                                               "required" => true),

                                         "message"   => array( "name"     => "Message",
                                                               "datatype" => "string",
                                                               "default"  => "",
                                                               "required" => true) ),
                      "keys" => array( "id" ),
                      "class_name" => "eZStaticExportLog",
                      "increment_key" => "id",
                      "name" => "ezstaticexport_log" );
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
      Fetches one log.
    */
    public static function fetch()
    {
        return eZPersistentObject::fetchObject( eZStaticExportLog::definition(),
                                                null,
                                                null,
                                                true );
    }

    /*!
     \static
     */
    public static function fetchList()
    {
        return eZPersistentObject::fetchObjectList( eZStaticExportLog::definition(),
                                                    null,
                                                    null,
                                                    array( 'date' => 'asc', 'id' => 'asc' ) );
    }

    function fetchByExportID( $exportID )
    {
        return eZPersistentObject::fetchObjectList( eZStaticExportLog::definition(),
                                                    null,
                                                    array( 'export_id' => $exportID ),
                                                    array( 'date' => 'asc', 'id' => 'asc' ),
                                                    null,
                                                    false );
    }
}

?>
