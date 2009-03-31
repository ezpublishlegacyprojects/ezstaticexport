<?php
//
// Definition of eZStaticExportContentType class
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

class eZStaticExportContentType extends eZPersistentObject
{
    /*!
     Initializes a new log.
    */
    function eZStaticExportContentType( $row = array() )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \reimp
    */
    public static function definition()
    {
        return array( "fields" => array( "id"               => array( "name"     => "ID",
                                                                      "datatype" => "integer",
                                                                      "default"  => 0,
                                                                      "required" => true ),

                                         "node_path_string" => array( "name"     => "Node path string",
                                                                      "datatype" => "string",
                                                                      "default"  => 0,
                                                                      "required" => true),

                                         "content_type"     => array( "name"     => "Content type",
                                                                      "datatype" => "string",
                                                                      "default"  => "",
                                                                      "required" => true),

                                         "flag_type"        => array( 'name'     => 'Flag type',
                                                                      'datatype' => 'string',
                                                                      'default'  => 'node',
                                                                      'required' => false ) ),
                      "keys" => array( "id", "node_path_string" ),
                      "class_name" => "eZStaticExportContentType",
                      "name" => "ezstaticexport_contenttype",
                      'increment_key' => 'id',
                      'function_attributes' => array( 'node' => 'getNode',
                                                      'nodes_count' => 'getNodesCount' ) );
    }


    /*!
     \static
     Fetches an eZStaticExportContentType with path string \a $pathString
     If \a $recurse is true, will go up to eZ publish's root until either
     a subtree flag is found, or will return default.
    */
    public static function fetchByNodePathString( $pathString, $recurse = true )
    {
        $exportContentType = eZPersistentObject::fetchObject(
            eZStaticExportContentType::definition(),
            false,
            array( 'node_path_string' => $pathString ) );
        if ( is_object( $exportContentType ) )
        {
            return $exportContentType;
        }
        elseif ( $recurse )
        {
            $node = eZContentObjectTreeNode::fetchByPath( $pathString );
            $path_items = explode('/', trim( $node->attribute( 'path_string' ), '/' ) );
            for ( $i = count( $path_items ) - 1; $i > 0; $i-- )
            {
                $path_item = '/' . implode( '/', array_slice( $path_items, 0, $i + 1 ) ) . '/';
                if ( $analyzedNodeFlag = eZStaticExportContentType::fetchByNodePathString( $path_item, false ) )
                    if ( $analyzedNodeFlag->attribute( 'flag_type' ) == 'subtree' )
                        return new eZStaticExportContentType( array(
                            'content_type'     => $analyzedNodeFlag->attribute( 'content_type' ),
                            'flag_type'        => 'inherited',
                            'node_path_string' => $pathString ) );
            }
            return new eZStaticExportContentType( array(
                'content_type'     => eZStaticExportContentType::defaultContentType(),
                'flag_type'        => 'default',
                'node_path_string' => $pathString ) );
        }
        else
        {
            return null;
        }
    }


    /*!
     \static
     Flags the subtree/node (\a $flagType) \a $nodeID as content type \a $contentType
    */
    public static function flag( $flagType, $nodeID, $contentType )
    {
        eZDebug::writeDebug("Requested operation: flag $nodeID on $flagType as $contentType", "eZStaticExport::flag" );
        if ( !eZStaticExportContentType::checkContentType( $contentType ) )
        {
            eZDebug::writeError( "$contentType is not a valid content type", "eZStaticExport::flag" );
            return false;
        }

        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( !is_object( $node ) )
        {
            eZDebug::writeError( "$nodeID is not a valid node iD" );
            return false;
        }
        $pathString = $node->attribute( 'path_string' );

        // this will return the content-type for the node
        $flag = eZStaticExportContentType::fetchByNodePathString( $pathString );

        eZDebug::writeDebug( $flag, "eZStaticExportContentType::flagNode : Current flag for $pathString" );

        // flag_type == default: we flag if content type != default
        if ( $flag->attribute( 'flag_type' ) == 'default' )
        {
            if ( !eZStaticExportContentType::isDefaultContentType( $contentType ) )
            {
                $flag->setAttribute( 'flag_type', $flagType );
                $flag->setAttribute( 'content_type', $contentType );
                $flag->store();
                eZDebug::writeDebug( "Flag type is default and target content type != default, flagging $pathString on $flagType as $contentType", "eZStaticExportContentType::flagNode" );
            }
            else
            {
                eZDebug::writeDebug( "Flag type is default and target content type is default, skipping", "eZStaticExportContentType::flagNode" );
            }
        }
        // flag_type == inherited: depends on content type
        elseif ( $flag->attribute( 'flag_type' ) == 'inherited' )
        {
            // flag_type == inherited, and different content type, flagging
            if ( $flag->attribute( 'content_type' ) != $contentType )
            {
                $flag->setAttribute( 'content_type', $contentType );
                $flag->setAttribute( 'flag_type', $flagType);
                $flag->store();
                eZDebug::writeDebug( "Inherited flag with different content type, flagging $pathString on $flagType as $contentType", "eZStaticExportContentType::flagNode" );
            }
            // flag_type == inherited, and identical content type, skipping
            else
            {
                eZDebug::writeDebug( "Inherited flag with identical content type, skipping", "eZStaticExportContentType::flagNode" );
            }
        }
        // attempt to flag with the same content type: we don't do anything
        elseif ( $flag->attribute( 'content_type' ) == $contentType  )
        {
            if ( $flag->attribute('flag_type') == $flagType )
            {
                eZDebug::writeDebug( "New and current content type and flag types are identical, skipping", "eZStaticExportContentType::flagNode" );
            }
            else
            {
                $flag->setAttribute( 'flag_type', $flagType );
                $flag->store();
                eZDebug::writeDebug( "Content types are identical, but flag types are different, flagging $pathString on $flagType", "eZStaticExportContentType::flagNode" );
            }
        }
        // flag_type == node|subtree: depends on the content type
        else
        {
            // default content type: we also need to check the parent's flag !
            // if the parent has a different flag, we need to explicitely flag
            if ( eZStaticExportContentType::isDefaultContentType( $contentType ) )
            {
                // we remove the last item from the path string to get the parent's
                // not very readable but so neat :)
                $parentPathString = '/' . implode( '/', array_slice( explode('/', trim( $pathString, '/' ) ), 0, -1 ) ) . '/';

                $parentFlag = eZStaticExportContentType::fetchByNodePathString( $parentPathString );

                // parent's content type is default, we can safely remove the flag
                if ( eZStaticExportContentType::isDefaultContentType( $parentFlag->attribute( 'content_type' ) ) or
                     $parentFlag->attribute( 'flag_type' ) == 'node' )
                {
                    eZDebug::writeDebug( "A flag is explicitely set, and either the parent's content type is default, or it has a node based content type, we can remove the flag", "eZStaticExportContentType::flagNode" );
                    $flag->remove();
                }
                else
                {
                    eZDebug::writeDebug( "A flag is explicitely set, and the parent's content type is NOT default, flagging $pathString on $flagType as $contentType", "eZStaticExportContentType::flagNode" );
                    $flag->setAttribute( 'content_type', $contentType );
                    $flag->setAttribute( 'flag_type',    $flagType );
                    $flag->store();
                }
            }
            else
            {
                $flag->setAttribute( 'content_type', $contentType );
                $flag->setAttribute( 'flag_type',    $flagType );
                $flag->store();
                eZDebug::writeDebug( "A flag is explicitely set, updating content type to $contentType and flag type to $flagType", "eZStaticExportContentType::flagNode" );
            }
        }
    }


    /*!
     \private
     \static
     Checks if \a $contentType is a valid content type
    */
    private static function checkContentType( $contentType)
    {
        $ini = eZINI::instance( 'staticexport.ini' );
        return in_array( $contentType, $ini->variable( 'ExportSettings', 'AllowedContentTypes' ) );
    }

    /*!
     \private
     \static
     Checks if \a $contentType is the default export type
    */
    private static function isDefaultContentType( $contentType)
    {
        return $contentType == eZStaticExportContentType::defaultContentType();
    }

    function defaultContentType()
    {
        $ini = eZINI::instance( 'staticexport.ini' );
        return $ini->variable( 'ExportSettings', 'DefaultContentType' );
    }

    /*!
     \static
     Returns the static export content infos about a node
     Will inherit properties from the parent to get the flags, and will return
     default content type if no flag exists
    */
    public static function fetchNodeInfo( $nodeID )
    {
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( is_object( $node ) )
            return eZStaticExportContentType::fetchByNodePathString( $node->attribute( 'path_string' ) );
        else
            return false;
    }

    /*!
     Returns a simple list of ezstaticexportcontenttype objects
     \static
    */
    public static function fetchList()
    {
        return eZPersistentObject::fetchObjectList( eZStaticExportContentType::definition() );
    }


    /*!
     Returns the node the object references in node_path_string
     Used by the node attribute
    */
    function &getNode()
    {
        //include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
        $node = eZContentObjectTreeNode::fetchByPath( $this->attribute( 'node_path_string' ) );
        return $node;
    }

    /*!
     Returns the number of nodes affected by the flag
    */
    function &getNodesCount()
    {
        if ( $this->attribute( 'flag_type' ) == 'node' )
        {
            $count = 1;
        }
        elseif ( $this->attribute( 'flag_type' ) == 'subtree' )
        {
            $node = $this->getNode();
            $array = array();
            $count = eZContentObjectTreeNode::subtreeCountByNodeID( $array, $node->attribute( 'node_id' ) );

            // +1 since we need to add the node itself
            $count++;
        }
        else
        {
            eZDebug::writeWarning( "Flag on node " . $this->attribute( 'path_string' ) . " is either inherited or default.", "eZStaticExportContentType::getNodesCount" );
            $count = 0;
        }
        return $count;
    }

    /*!
     Fetches a flag based on its ID
     \static
    */
    public static function fetch( $id )
    {
        return eZPersistentObject::fetchObject( eZStaticExportContentType::definition(),
                                                false,
                                                array( 'id' => $id ) );
    }
}

?>
