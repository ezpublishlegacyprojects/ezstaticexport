<?php
//
// Definition of eZStaticExportRsyncDriver class
//
// Created on: <16-Oct-2007 10:29:21 jr>
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

include_once( 'extension/ezstaticexport/classes/synchronizationdrivers/ezstaticexportabstractdriver.php');
include_once( 'lib/ezfile/classes/ezfile.php' );

class eZStaticExportRsyncDriver extends eZStaticExportSyncAbstractDriver
{
    function eZStaticExportRsyncDriver()
    {
        parent::eZStaticExportSyncAbstractDriver();
    }

    function startSync()
    {
        $command = $this->buildShellCommand();
        eZDebug::writeDebug( $command, 'eZStaticExportRsyncDriver: Shell command');
        $return = $output = '';
        exec( $command, $output, $return );

        if ( $return != 0 )
        {
            eZStaticExportLogger::log("An error occured syncing to ".$this->ConnectionInfos['host']." using " . $this->DriverName, EZSTATICEXPORT_LOG_WARNING );
            echo implode( "\n", $output );
            return false;
        }
        else
        {
            eZStaticExportLogger::log("Syncing to ".$this->ConnectionInfos['host']." using ".$this->DriverName." completed without errors");
            return true;
        }
    }

    /*
     !private
     */
    function buildShellCommand()
    {
        $argumentArgsString = $this->buildCommandArgs();

        $uri = '';
        // local transfer, no username required
        if ( $this->ConnectionInfos['host'] == 'localhost' or $this->ConnectionInfos['host'] == '127.0.0.1' )
        {
            if ( eZSys::osType() == 'win32' )
                $this->ConnectionInfos['path'] = str_replace('/', '\\', $this->ConnectionInfos['path'] );
            $uri .= $this->ConnectionInfos['path'];
        }
        // remote transfer, user@host:path
        else
        {
            $uri .= sprintf('%s@%s:%s', $this->ConnectionInfos['user'],
                                        $this->ConnectionInfos['host'],
                                        $this->ConnectionInfos['path'] );
        }
        $shellCommand = $this->DriverPath . ' ' . $argumentArgsString . ' ' . $this->ExportSource . ' ' . $uri;
        $shellCommand .= ' 2>&1';

        return $shellCommand;
    }

    /*!
    \desc builds the command line arguments list based on the options
    \return string the command line arguments
    */
    function buildCommandArgs()
    {
        $driverArgsKeys = array_keys( $this->DriverArgs );

        $finalArgumentsList = array();

        foreach( $this->InjectedDriverArgs as $driverArg )
        {
            if( isset( $this->DriverArgs[$driverArg]) )
            {
                if ( $this->DriverArgs[$driverArg] != '' )
                {
                    if ( substr( $this->DriverArgs[$driverArg], 0, 2 ) != '--' )
                    {
                        $argument = '--' . $this->DriverArgs[$driverArg];
                    }
                    else
                    {
                        $argument = $this->DriverArgs[$driverArg];
                    }
                    $finalArgumentsList[] = $argument;
                }
            }
            else
            {
                $errorMessage = 'Unable to load driver argument : DriverArgs[' . $driverArg . '] please, check your settings in staticexport.ini';
                eZDebug::writeError( $errorMessage );
                eZLog::write( $errorMessage, 'error.log' );
            }
        }

        return join( ' ', $finalArgumentsList );
    }

    var $DriverName = 'rsync';
}

?>
