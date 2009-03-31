<?php
//
// Definition of eZStaticExport class
//
// Created on: <09-Oct-2007 11:08:14 jr>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.3
// BUILD VERSION: 19751
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
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

include_once( "kernel/classes/ezworkflowtype.php" );
include_once( 'extension/ezstaticexport/classes/ezstaticexporttoken.php' );

define( "EZ_WORKFLOW_TYPE_STATICEXPORT_ID", "ezstaticexport" );

class eZStaticExportType extends eZWorkflowEventType
{
    function eZStaticExportType()
    {
        $this->eZWorkflowEventType( EZ_WORKFLOW_TYPE_STATICEXPORT_ID,
            ezi18n( 'extension/ezstaticexport/workflow', "Static export token check", 'Workflow event name' ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ) ) );
    }

    function execute( &$process, &$event)
    {
        include_once( 'extension/ezstaticexport/classes/ezstaticexporttoken.php' );

        // could be improved by showing a template when publishing is delayed
        // that says that publishing will be finished when the current static
        // export is over
        if ( eZStaticExportToken::exists() )
        {
            return EZ_WORKFLOW_TYPE_STATUS_DEFERRED_TO_CRON_REPEAT;
        }
        else
        {
            return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
        }
    }
}

eZWorkflowEventType::registerType( EZ_WORKFLOW_TYPE_STATICEXPORT_ID, "ezstaticexporttype" );

?>
