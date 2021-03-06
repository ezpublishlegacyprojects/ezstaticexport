<?php
//
// Definition of eZStaticExportLogger class
//
// Created on: <31-Oct-2007 16:07:30 bd>
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

/*! \file ezstaticexportlogger.php
*/

/*!
  \class eZStaticExportLog ezstaticexportlogger.php
  \brief Helper function for loggin purposes
*/

//include_once( 'extension/ezstaticexport/classes/ezstaticexportlog.php' );
//include_once('lib/ezutils/classes/ezlog.php');
define('EZLOG', 'ezlog');
define('EZSTATICEXPORTLOG', 'ezstaticexportlog');

class eZStaticExportLogger
{
	const LOG_TYPE_ERROR = 1;
	const LOG_TYPE_WARNING = 2;
	const LOG_TYPE_NOTICE = 3;
	
    /*!
     Initializes the logging engine from an export instance
     Stores the current logged item globally so that log items can be created
     easily for that export
     \static
    */
    public static function init( $export )
    {
        if ( !is_a( $export, 'ezstaticexportexport' ) )
            return false;

        $GLOBALS['eZStaticExportLoggerRow'] = array(
            'export_id' => $export->attribute( 'id' ),
        );
    }

    /*!
     Adds a log entry about the current export
     Will fail if init() hasn't been called first !
     \static
    */
    public static function log( $message, $status = 0 )
    {
        $ini = eZINI::instance('staticexport.ini');
        $logHandler = $ini->variable('LogSettings', 'LogHandler');
        
        if ($logHandler == EZSTATICEXPORTLOG)
        {
	    	$row = $GLOBALS['eZStaticExportLoggerRow'];
	        $row['message'] = $message;
	        $row['status']  = $status;
	        $row['date']    = time();
	        $log = new eZStaticExportLog( $row );
	        $log->store();
        }
        else
        {
        	switch($status)
        	{
        		case self::LOG_TYPE_ERROR : $logFile = 'ezstaticexport-error.log'; break;
        		case self::LOG_TYPE_WARNING : $logFile = 'ezstaticexport-warning.log'; break;
        		
        		case self::LOG_TYPE_NOTICE :
        		default: $logFile = 'ezstaticexport-notice.log'; break;
        	}
        	
        	eZLog::write($message, $logFile);
        }
    }
}
?>