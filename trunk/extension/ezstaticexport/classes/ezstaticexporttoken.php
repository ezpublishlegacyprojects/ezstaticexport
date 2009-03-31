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

/*! \file ezstaticexporttoken.php
*/

/*!
  \class eZStaticExportToken ezstaticexporttoken.php
  \brief Handles token in eZ static cache
  \desc abstracs eZStaticExportExport in order to return token status on exports
*/

//include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );

class eZStaticExportToken
{
    /*!
     \static
     Checks if an export token exists. True = an export is running or pending
    */
    public static function exists()
    {
        $count = eZStaticExportExport::fetchCountByStatus( array( eZStaticExport::STATUS_PENDING,
                                                                  eZStaticExport::STATUS_RUNNING,
                                                                  eZStaticExport::STATUS_INTERRUPTED ) );
        return ( $count > 0 );
    }

    /*!
     \static
     Checks if an export is pending
    */
    public static function isPending()
    {
        $count = eZStaticExportExport::fetchCountByStatus( array( eZStaticExport::STATUS_PENDING, eZStaticExport::STATUS_INTERRUPTED ) );
        return ( $count > 0 );
    }
    
    /**
     * Checks if an export can run
     *
     * @return bool
     */
    function canRunExport()
    {
    	$staticExportINI = eZINI::instance('staticexport.ini');
    	$maxAuthorizedProcesses = $staticExportINI->variable('ExportSettings', 'MaxAuthorizedRunningProcesses');
    	
    	if (eZStaticExportToken::isPending() || eZStaticExportExport::fetchRunningProcessesCount() < $maxAuthorizedProcesses)
    		return true;
    	
    	return false;
    }

    /*!
     \static
     Checks if an export is running
    */
    public static function isRunning()
    {
        $count = eZStaticExportExport::fetchCountByStatus( array( eZStaticExport::STATUS_RUNNING ) );
        return ( $count > 0 );
    }

    function isInterrupted()
    {
        $count = eZStaticExportExport::fetchCountByStatus( array( eZStaticExport::STATUS_INTERRUPTED ) );
        return ( $count > 0 );
    }
}

?>