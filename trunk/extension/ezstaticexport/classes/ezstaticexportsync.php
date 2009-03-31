<?php
//
// Definition of eZStaticExportSync class
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

//include_once( 'lib/ezutils/classes/ezini.php' );

// factory
class eZStaticExportSync
{
    /*!
     static
     */
    function loadDriver( $driverName )
    {
        if( eZStaticExportSync::driverExists( $driverName ) )
        {
            include_once( 'extension/ezstaticexport/classes/synchronizationdrivers/ezstaticexport' . strtolower( $driverName ) . 'driver.php' );
            $driverClassName = 'ezstaticexport' . $driverName . 'driver';
            return new $driverClassName();
        }
        else
        {
            eZDebug::writeError("No such driver '$driverName'");
        }

        return false;
    }

    /*!
     \static
     Returns the list of existing sync drivers
     */
    public static function getDrivers()
    {
        $ini = eZINI::instance( 'staticexport.ini' );
        $driverList = $ini->variable( 'SynchronizationSettings', 'DriverList' );

        return $driverList;
    }

    /*!
     \static
     Checks if the driver \a $driverName exists
    */
    public static function driverExists( $driverName )
    {
        $driverList = eZStaticExportSync::getDrivers();

        return in_array( $driverName, $driverList);
    }
}

?>
