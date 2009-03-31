#!/usr/bin/env php
<?php
//
// Created on: <17-Oct-2007 10:51:17 jr>
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

//TODO : try this : php4 extension/ezstaticexport/bin/php/synchronize.php --with-driver=rsync --with-driver-params=simulationmode --export-src=var/cache --export-target=target1

include_once( 'lib/ezutils/classes/ezcli.php' );
include_once( 'kernel/classes/ezscript.php' );
include_once( 'lib/ezutils/classes/ezini.php');
include_once( 'extension/ezstaticexport/classes/ezstaticexportsync.php');

$cli =& eZCLI::instance();
$script =& eZScript::instance( array( 'description' => ( "Static export synchronization system" ),
                                      'use-session' => false,
                                      'use-modules' => false,
                                      'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[with-driver:][with-driver-params:][list-drivers][export-src:][export-target:]",
                                "",
                                array( 'with-driver'        => 'Load driver according to driver name passed',
                                       'with-driver-params' => 'Load special parameter into driver, seaparate each param with a comma',
                                       'list-drivers'       => 'List available drivers',
                                       'export-src'         => 'path of the export you want to synchronize, based on the value of StaticStorageDir (in staticexport.ini) ',
                                       'export-target'      => 'name of the export target you want to synchronize with' ) );



$script->initialize();

if( empty( $options['list-drivers'] ) and empty( $options['with-driver'] ) )
{
    $script->shutdown( 1, 'Please specify a driver, use --help for more informations');
}

// displays a list of available drivers
if( $options['list-drivers'] )
{
    $driverList = eZStaticExportSyncFactory::getDrivers();

    $cli->output( 'List of available driver(s):' );

    foreach( $driverList as $driver )
    {
        $cli->output( '-' . $driver );
    }
}

if( $options['with-driver'] )
{
    $driverName = $options['with-driver'];

    if( !eZStaticExportSync::driverExists( $driverName ) )
    {
        $script->shutdown( 1, 'Please use an existing driver, see --help for more informations');
    }

    $syncEngine = eZStaticExportSync::loadDriver( $driverName );

    // driver arguments
    if( $options['with-driver-params'] )
    {
        $driverParams = explode( ',', $options['with-driver-params'] );
        $syncEngine->injectArgs( $driverParams );
    }

    // export source definition
    if( !$options['export-src'] or !is_dir( $options['export-src'] ) )
    {
        $script->shutdown( 1, 'Please choose a correct export source : path incorrect');
    }

    $syncEngine->exportSource = $options['export-src'];

    // export target definition
    if( !$options['export-target'] or !eZStaticExportSync::targetExists( $options['export-target'] ) )
    {
        $script->shutdown( 1, 'Please choose a correct export target : name incorrect');
    }

    // One exportTarget may contains multiple targetServers
    $syncEngine->setExportTarget( $options['export-target'] );

    $cli->output( 'Synchronizing with driver : ' . $driverName );

    $targetServerList = $syncEngine->TargetServers;

    // One exportTarget may contains multiple targetServers !!!
    foreach( $targetServerList as $targetServer )
    {
        $cli->output( 'Syncing to server : ' . $targetServer['ip'] );
        $syncEngine->CurrentTargetServer = $targetServerList[ $targetServer['ip'] ];
        $syncEngine->startSync();

        /*
        while( $syncEngine->StillSending )
        {
            $cli->output( "\r" . $syncEngine->percentageDone() . "%", false);
        }
        */
        $cli->output( "Done" );
    }
}

$script->shutdown();

?>

