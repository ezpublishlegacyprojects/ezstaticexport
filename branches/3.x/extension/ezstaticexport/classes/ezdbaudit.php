<?php
//
// Definition of eZAudit class
//
// Created on: <03-oct-2007 11:00:54 jr>
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

include_once( 'kernel/classes/ezaudit.php' );
include_once( 'extension/ezstaticexport/classes/ezstaticexportlog.php' );
include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );

 class eZDBAudit extends eZAudit
{
    /*!
      Creates a new audit object.
    */
    function eZDBAudit()
    {
        if( !eZAudit::isAuditEnabled() )
        {
            return false;
        }

        $this->StaticCacheLog = new eZStaticExportLog();
    }

    /*!
     \static
     Writes $auditName with $auditAttributes as content
    */
    function writeAudit( $auditName, $auditAttributes = array() )
    {

        $user =& eZUser::currentUser();
        $userID = $user->attribute( 'contentobject_id' );

        $this->StaticCacheLog->setAttribute( 'user_id', $userID );

        foreach( $auditAttributes as $key => $value )
        {
            $this->StaticCacheLog->setAttribute( $key, $value );
        }

        $this->StaticCacheLog->store();

        return true;
    }

    var $StaticCacheLog;
}
?>
