<?php
//
// Definition of eZStaticExportSyncAbstractDriver class
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

//include_once( 'lib/ezutils/classes/ezlog.php');
//include_once( 'lib/ezutils/classes/ezdebug.php');

class eZStaticExportSyncAbstractDriver
{
    function eZStaticExportSyncAbstractDriver()
    {
        $this->DriverPath = $this->iniGet( 'DriverPath' );
        $this->DriverArgs = $this->iniGet( 'DriverArgs' );
    }

    function setTargetInfos( $infos )
    {
        $this->ConnectionInfos = $infos;
    }

    /*!
     \desc injects an options string : verbose,ssh
    */
    function injectArgs( $driverArgs )
    {
        $this->InjectedDriverArgs = explode( ',', $driverArgs );
    }

    /*
     \private
    */
    function iniGet( $configurationDirective, $configurationGroup = null )
    {
        $ini = eZINI::instance( 'staticexport.ini' );

        if( is_null( $configurationGroup ) )
             $configurationGroup = 'DriverSettings-' . $this->DriverName;

        if( $ini->hasVariable( $configurationGroup, $configurationDirective ) )
        {
            return $ini->variable( $configurationGroup , $configurationDirective );
        }
        else
        {
            $errorMessage = 'Unable to find configuration group ' . $configurationGroup . ' or configuration directive ' . $configurationDirective . ', please check your settings';
            eZDebug::writeError( $errorMessage );
            eZLog::write( $errorMessage , 'error.log' );
            return false;
        }
    }
    
    /**
     * Begins synchronisation using chosen driver
     * 
     * @abstract 
     */
    function startSync()
    {
    	
    }
    
    /**
     * Allows to trig one or several scripts once the synchronization has finished
     * Scripts can be configured in staticexport.ini
     *
     */
    function postSyncHook()
    {
    	$aPostScripts = $this->iniGet('PostSyncScripts', 'SynchronizationSettings');
    	foreach ($aPostScripts as $script)
    	{
	    	
    		eZStaticExportLogger::log("Executing POST sync script '$script'");
    		$return = null;
	    	$output = array();
	        exec( $script, $output, $return );
	
	        if ( $return != 0 )
	        {
	            eZStaticExportLogger::log("An error occured executing the POST sync script '$script'", eZStaticExportLogger::LOG_TYPE_WARNING );
	            echo implode( "\n", $output );
	        }
	        else
	        {
	            eZLog::write(implode("\n", $output), 'staticexport-post-sync.log');
	        }
    	}
    }
    
    /**
     * Allows to trig one or several scripts BEFORE the synchronization
     * Scripts can be configured in staticexport.ini
     *
     */
    function preSyncHook()
    {
    	$aPreScripts = $this->iniGet('PreSyncScripts', 'SynchronizationSettings');
	    foreach ($aPreScripts as $script)
    	{
	    	
    		eZStaticExportLogger::log("Excuting POST sync script '$script'");
    		$return = null;
	    	$output = array();
	        exec( $script, $output, $return );	        
	
	        if ( $return != 0 )
	        {
	            eZStaticExportLogger::log("An error occured executing the POST sync script '$script'", eZStaticExportLogger::LOG_TYPE_WARNING );
	            echo implode( "\n", $output );
	        }
	        else
	        {
	            eZLog::write(implode("\n", $output), 'staticexport-pre-sync.log');
	        }
    	}
    }

    var $DriverPath          = '';
    var $DriverArgs          = array();
    var $InjectedDriverArgs  = array();
    var $TargetServers       = array();
    var $ExportSource        = '';
    var $CurrentTargetServer = '';
    var $ConnectionInfos     = array();
}

?>
