<?php
//
// Definition of eZStaticClass class
//
// Created on: <12-Jan-2005 10:29:21 dr>
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

/*! \file ezstaticexport.php
*/

/*!
  \class eZStaticExport ezstaticexport.php
  \brief Manages the static cache system.

  This class can be used to generate static cache files usable
  by the static cache system.

  Generating static cache is done by instatiating the class and then
  calling generateCache(). For example:
  \code
  $staticCache = new eZStaticExport();
  $staticCache->generateCache();
  \endcode

  To generate the URLs that must always be updated call generateAlwaysUpdatedCache()

*/

//include_once( 'lib/ezutils/classes/ezini.php' );
//include_once( 'lib/version.php' );

//if (eZPublishSDK::minorVersion() >= 10)
    //include_once( 'kernel/classes/ezurlaliasml.php' );

class eZStaticExport
{
	// Result codes
	const RESULT_DONE = 1;
	const RESULT_INTERRUPTED = 2;
	
	// Status codes
	const STATUS_PENDING = 1;
	const STATUS_RUNNING = 2;
	const STATUS_FAILED = 3;
	const STATUS_DELETED = 4;
	const STATUS_COMPLETED = 5;
	const STATUS_INTERRUPT_REQUESTED = 6;
	const STATUS_INTERRUPTED = 7;
	const STATUS_SYNCING = 8;
	
    /*!
     Initialises the static cache object with settings from staticexport.ini.
    */
    function eZStaticExport( &$export )
    {
        $ini = eZINI::instance( 'staticexport.ini');

        $cli = eZCLI::instance();

        $this->exportID = $export->attribute( 'id' );
        $this->export = $export;

        $this->HostName         = $ini->variable( 'ExportSettings', 'HostName' );
        $this->StaticStorageDir = $export->workFolder;
        $this->FolderPrefix     = $ini->variable( 'ExportSettings', 'FolderPrefix' );
        $this->timeout = $ini->variable('ExportSettings', 'TimeoutValue');

        $this->Target     = $export->attribute( 'target' );
        $this->ExportType = $export->attribute( 'type' );
        $this->PathString = $export->attribute( 'path_string' );

        $this->OutputHandler = 'ezdebug';
    }

    /*!
     Sets the output handler to \a $handler. Possible values: ezcli or ezdebug
    */
    function setOutputHandler( $handler )
    {
        if( $handler == 'ezcli' )
        {
            $this->OutputHandler = 'ezcli';
            $this->CliOutputHandler = eZCLI::instance();
        }
        else
        {
            $this->OutputHandler == 'ezdebug';
        }
    }

    /*!
     \return The currently configured host-name.
    */
    function hostName()
    {
        return $this->HostName;
    }

    /*!
     \return The export target
    */
    function target()
    {
        return $this->Target;
    }

    /*!
     \return The currently configured storage directory for the static cache.
    */
    function storageDirectory()
    {
        return $this->StaticStorageDir;
    }

    /*!
     \return An array with site-access names that should be cached.
    */
    function cachedSiteAccesses()
    {
        return $this->CachedSiteAccesses;
    }

    /*!
     \return An array with URLs that is to be cached statically, the URLs may contain wildcards.
    */
    function cachedURLArray()
    {
        return $this->CachedURLArray;
    }

    /*!
     Generated the exported URL array from the given \a $offset and \a $limit
     \return array of URL aliases
    */
    function exportedURLArray( $offset = false, $limit = false )
    {
        //include_once( 'kernel/classes/ezcontentcachemanager.php' );

        $exportedURLArray = array();

        $node = eZContentObjectTreeNode::fetchByPath( $this->PathString );
        if ( $node === null )
        {
            $this->output( "The static export root node '$this->PathString' could not be fetched" );
            return false;
        }
        else
        {
            $exportedURLArray = array_merge( $exportedURLArray,
                                             $this->getURLAliases( $node->attribute( 'node_id' ),
                                             $node->attribute( 'path_string' ) ) );

            $exportedNodeIDArray[] = $node->attribute( 'node_id' );

            // if we handle a subtree export, subitems of the requested node are added to the list
            $ini = eZINI::instance('staticexport.ini');
            $aExcludeContentClasses = $ini->variable('ExportSettings', 'ExcludeContentClasses');
            if ( $this->ExportType == eZStaticExportExport::TYPE_SUBTREE )
            {
                $subTreeParams = array( 'Limit' => $limit, 
                						'Offset' => $offset, 
                						'ClassFilterType' => 'exclude', 
                						'ClassFilterArray' => $aExcludeContentClasses );
                $subtreeNodes = eZContentObjectTreeNode::subtreeByNodeID( $subTreeParams, $node->attribute( 'node_id' ) );

                /*
                foreach( $subtreeNodes as $subtreeNode )
                {
                	$exportedNodeIDArray[] = $subtreeNode->attribute('node_id');
                    $exportedURLArray = array_merge( $exportedURLArray,
                                                     $this->getURLAliases( $subtreeNode->attribute('node_id'),
                                                                           $subtreeNode->attribute('path_string') ) );
                }
				*/
            	for( $i=0, $iMax=count($subtreeNodes); $i<$iMax; ++$i)
                {
                	$exportedNodeIDArray[] = $subtreeNodes[$i]->attribute('node_id');
                    $exportedURLArray = array_merge( $exportedURLArray,
                                                     $this->getURLAliases( $subtreeNodes[$i]->attribute('node_id'),
                                                                           $subtreeNodes[$i]->attribute('path_string') ) );
                }
            }
            else if ( $this->ExportType == eZStaticExportExport::TYPE_NODE )
            {
            	
            }

            // for each exported node, we fetch the list of dependant nodes
            // and add their URL aliases if they're not part of the initial export
            /*
        	for( $i=0, $iMax=count($exportedNodeIDArray); $i<$iMax; ++$i )
            {
                $exportedNode   = eZContentObjectTreeNode::fetch( $exportedNodeIDArray[$i] );
                $exportedObject = $exportedNode->object();

                $dependantNodeIDArray = eZContentCacheManager::nodeList( $exportedObject->attribute( 'id' ), true );

                for( $j=0, $jMax=count($dependantNodeIDArray); $j<$jMax; ++$j )
                {
                    // list of node IDs that won't be generated
                    if ( in_array( $dependantNodeIDArray[$j], array( 1 ) ) )
                        continue;

                    // we only handle the node if it is not part of the initial export
                    if ( !in_array( $dependantNodeIDArray[$j], $exportedNodeIDArray ) )
                    {
                        $dependantNode = eZContentObjectTreeNode::fetch( $dependantNodeIDArray[$j], false, false );
                        $exportedURLArray = array_merge( $exportedURLArray,
                                                         $this->getURLAliases( $dependantNode['node_id'],
                                                                               $dependantNode['path_string'] ) );
                    }
                }
            }
			*/

            /*foreach( $exportedNodeIDArray as $exportedNodeID )
            {
                $exportedNode   = eZContentObjectTreeNode::fetch( $exportedNodeID );
                $exportedObject = $exportedNode->object();

                $dependantNodeIDArray = eZContentCacheManager::nodeList( $exportedObject->attribute( 'id' ), true );

                foreach( $dependantNodeIDArray as $dependantNodeID )
                {
                    // list of node IDs that won't be generated
                    if ( in_array( $dependantNodeID, array( 1 ) ) )
                        continue;

                    // we only handle the node if it is not part of the initial export
                    if ( !in_array( $dependantNodeID, $exportedNodeIDArray ) )
                    {
                        $dependantNode = eZContentObjectTreeNode::fetch( $dependantNodeID, false, false );
                        $exportedURLArray = array_merge( $exportedURLArray,
                                                         $this->getURLAliases( $dependantNode['node_id'],
                                                                               $dependantNode['path_string'] ) );
                    }
                }
            }*/
        }

        return $exportedURLArray;
    }


    /*!
     \private
     Gets the list of (non-internal) URL aliases for \a $node
     \return the URL aliases for \a $node
    */
    function getURLAliases( $nodeID, $pathString, $mainNodeOnly = false )
    {
        //include_once( 'extension/ezstaticexport/classes/ezstaticexportcontenttype.php' );
        $nodeContentType = eZStaticExportContentType::fetchByNodePathString( $pathString );
        $contentType = $nodeContentType->attribute( 'content_type' );

        //include_once( 'kernel/classes/ezurlalias.php' );
        $URLAliases = array();

        if (eZPublishSDK::majorVersion() < 4 && eZPublishSDK::minorVersion() < 10)
            $systemURL = eZURLAlias::cleanURL( 'content/view/full/' . $nodeID );
        else
            $systemURL = eZURLAliasML::cleanURL( 'content/view/full/' . $nodeID );

        // internal URL alias
        if ( eZPublishSDK::majorVersion() < 4 && eZPublishSDK::minorVersion() < 10 )
        {
            $mainURLAlias = eZURLAlias::fetchByDestinationURL( $systemURL, true, false );
            $url = $mainURLAlias['source_url'];
        }
        else
        {
            $url = $systemURL;
            eZURLAliasML::translate( $url, true );
            $mainURLAlias = $url;
        }

        $URLAliases[] = array( $url, $contentType );
		/*
        // additional URL aliases
        if ( eZPublishSDK::majorVersion() < 4 && eZPublishSDK::minorVersion() < 10 )
            $definition = eZURLAlias::definition();
        else
            $definition = eZURLAliasML::definition();

        if( eZPublishSDK::majorVersion() < 4 && eZPublishSDK::minorVersion() < 10 )
            $destinationURLParams = array( 'destination_url' => array( array( $systemURL, $mainURLAlias['destination_url'] ),
                                           'is_wildcard' => 0,
                                           'is_internal' => 0 ) );
        else
            $destinationURLParams = array( 'destination_url' => array( array( $systemURL, $mainURLAlias ),
                                           'is_wildcard' => 0,
                                           'is_internal' => 0 ) );

        $addURLAlias = eZPersistentObject::fetchObjectList( $definition,
                                                            null,
                                                            $destinationURLParams,
                                                            false, false, false );

        // additional URL aliases
        foreach( $addURLAlias as $alias )
        {
            if( $alias['source_url'] != $URLAliases[0][0] )
            {
            	$URLAliases[] = array( $alias['source_url'], $contentType );
            }
        }
		*/
        return $URLAliases;
    }

    /*!
     Generates the static cache from the configured INI settings.

     \param $force If \c true then it will create all static caches even if it is not outdated.
     \param $quiet If \c true then the function will not output anything.
     \param $cli The eZCLI object or \c false if no output can be done.
    */
    function run( $offset, $limit )
    {
        //include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

        if ( $this->interruptIfRequested() )
            return eZStaticExport::RESULT_INTERRUPTED;

        $staticURLArray = $this->exportedURLArray( $offset, $limit );

        if ( $this->interruptIfRequested() )
            return eZStaticExport::RESULT_INTERRUPTED;

        // if we are running a subtree export, this subtree is removed from the repository if it exists
        if( $offset == 0 )
        {
            $this->cleanup();
        }

        $db = eZDB::instance();
        $currentSetting = 0;

        $iteration = 1;
        foreach ( $staticURLArray as $urlData )
        {
            $bExportResult = $this->exportURL( $urlData );
            $iteration++;

            // we only check interruption requests every 5 URL fetch
            if ( $iteration % 5 == 0 )
            {
                if ( $this->interruptIfRequested() )
                    return eZStaticExport::RESULT_INTERRUPTED;
                $iteration = 1;
            }
            $iteration++;
        }

        // content/download files
        if ( ( $filesCount = count( $this->externalFilesContentDownload ) ) > 0 )
        {
            $this->externalFilesContentDownload = array_unique( $this->externalFilesContentDownload );
            foreach( $this->externalFilesContentDownload as $file )
            {
                eZStaticExportLogger::log( "Exporting binary file $file" );
                $url = $this->generateURL( $file );
               	$fileData = @file_get_contents( $url );
                $cacheFileName = $this->buildBinaryCacheFilename( $file );
                $this->storeCachedFile( $cacheFileName, $fileData );
            }
        }

        // var/storage files
        if ( ( $filesCount = count( $this->externalFilesVarStorage ) ) > 0 )
        {
            $this->externalFilesVarStorage = array_unique( $this->externalFilesVarStorage );
            foreach( $this->externalFilesVarStorage as $file )
            {
                eZStaticExportLogger::log( "Exporting binary file $file" );
                $file = ltrim( $file, '/' );
               	$fileData = file_get_contents( $file );
                $cacheFileName = $this->buildBinaryCacheFilename( $file );
                $this->storeCachedFile( $cacheFileName, $fileData );
            }
        }

        return eZStaticExport::RESULT_DONE;
    }

    /*!
     \private
     Generates the caches for the url \a $url using the currently configured hostName() and storageDirectory().

     \param $url The URL to cache, e.g \c /news
     \param $nodeID The ID of the node to cache, if supplied it will also cache content/view/full/xxx.
     \param $skipExisting If \c true it will not unlink existing cache files.
    */
    function exportURL( $urlData )
    {
        eZStaticExportLogger::log( "Exporting URL $urlData[0] as $urlData[1]" );

        $url = $urlData[0];
        $contentType = $urlData[1];

        // Set default hostname
        $realURL = $this->generateURL( $url );

        $exportedFileName = $this->buildCacheFilename( $url, $contentType );

        $data = @file_get_contents( $realURL );
        
        // Check if the webserver call has failed or not. If so, we retry. Max number of retries are defined in staticexport.ini
        // The webserver call can fail if you use the multithread functionnality, due to stream timeouts
        if ($data === false)
        {
        	eZStaticExportLogger::log("ERROR Exporting URL $urlData[0] as $urlData[1]", eZStaticExportLogger::LOG_TYPE_ERROR);
        	
        	$ini = eZINI::instance('staticexport.ini');
        	$maxRetry = $ini->variable('ExportSettings', 'MaxWebServerCallRetry');
        	$nbRetry = 1;
        	
        	while($data === false && $nbRetry <= $maxRetry)
        	{
        		eZStaticExportLogger::log( "retry n.$nb_retry - Exporting URL $urlData[0] as $urlData[1]" );
        		$data = @file_get_contents( $realURL );
        		$nbRetry++;
        	}
        }
        
        if ($data !== false)
        	eZStaticExportLogger::log( "URL Exported $urlData[0] successfully " );
        else
        	eZStaticExportLogger::log( "Error unable to export $urlData[0] ", eZStaticExportLogger::LOG_TYPE_ERROR );

        // external files are listed
        $this->parseExternalFiles( $data );

        // the exported file is stored as index.html
        $this->storeCachedFile( $exportedFileName, $data );

        return true;
    }

    function generateURL( $url )
    {
        return 'http://' . $this->HostName . '/' . $this->FolderPrefix . $url;
    }

    /*!
     \private
     \param $staticStorageDir The storage for cache files.
     \param $url The URL for the current item, e.g \c /news
     \return The full path to the cache file (index.html) based on the input parameters.
    */
    function buildCacheFilename( $url, $contentType )
    {
        $ini = eZINI::instance( 'staticexport.ini' );
        $contentTypesExtensions = $ini->variable( 'ExportSettings', 'ContentTypesExtension' );

        $file = "{$this->StaticStorageDir}/{$url}/index." . $contentTypesExtensions[$contentType];
        $file = preg_replace( '#//+#', '/', $file );
        return $file;
    }

    /*!
     \private
     \param $url The URL for the current binary item, e.g \c /content/download/foo/bar.zip
     \return The full path to the cache file based on the input parameters.
    */
    function buildBinaryCacheFilename( $url )
    {
        $url = preg_replace('#^http://[^/]+/(.+)#', '$1', $url);
    	$file = $this->StaticStorageDir . '/' . $url;
        $file = preg_replace( '#//+#', '/', $file );
        return $file;
    }

    /*!
     \private
     \static
     Stores the cache file \a $file with contents \a $content.
     Takes care of setting proper permissions on the new file.
    */
    public static function storeCachedFile( $file, $content )
    {
        eZStaticExportLogger::log( "Storing $file" );
        $dir = dirname( $file );
        if ( !is_dir( $dir ) )
        {
            eZDir::mkdir( $dir, 0777, true );
        }

        $oldumask = umask( 0 );

        $tmpFileName = $file . '.' . md5( $file. uniqid( "ezp". getmypid(), true ) );

        /* Remove files, this might be necessary for Windows */
        @unlink( $tmpFileName );

        /* Write the new cache file with the data attached */
        $fp = fopen( $tmpFileName, 'w' );
        if ( $fp )
        {
            fwrite( $fp, $content );
            fclose( $fp );
            //include_once( 'lib/ezfile/classes/ezfile.php' );

            // existing index files in the folder are removed if they have a different extension
            $directory = dirname( $file );
            $currentFilePathInfo = pathinfo( $file );
            $existingFiles = eZDir::findSubItems( $directory, "f", true );
            foreach( $existingFiles  as $existingFile )
            {
                $existingFilePathInfo = pathinfo( $existingFile );

                if ( $existingFile != $tmpFileName and
                     $existingFilePathInfo['extension'] != $currentFilePathInfo['extension'] )
                {
                    @unlink( $existingFile );
                }
            }
            eZFile::rename( $tmpFileName, $file );
        }

        umask( $oldumask );
    }

    /*!
     Checks if an interruption of the current export was requested
     (status = EZ_STATIC_EXPORT_INTERRUPT_REQUESTED), and if yes, performs
     the interruption on the export
    */
    function interruptIfRequested()
    {
        // we don't even check if the export is scheduled (can not be interrupted)
        if ( $this->export->attribute( 'schedule_type' ) == 'scheduled' )
            return false;

        // the export is loaded from the database again
        $export = eZStaticExportExport::fetch( $this->exportID );
        if ( $export->attribute( 'status' ) == eZStaticExport::STATUS_INTERRUPT_REQUESTED )
        {
            $this->output("Interruption requested");
            $export->interrupt();
            return true;
        }
        else
        {
            return false;
        }
    }

    /*!
     Output function
     Uses either ezdebug (default) or eZCLI if the object is specified
    */
    function output( $string )
    {
        if ( $this->OutputHandler == 'ezcli' )
        {
            $this->CliOutputHandler->output( $string );
        }
        else
        {
            eZDebug::writeDebug( $string );
        }
    }

    /*!
    Recursively deletes a folder, leaving the folders containing a .external
    Item
    \param $dir the folder to delete
    \private
    */
    function cleanup()
    {
        // nothing to cleanup if we're not running a subtree export
        if ( $this->ExportType != 'subtree' )
        {
            return;
        }

        $this->export->setAttribute('removing_root_folder', true);
        $baseNode = eZContentObjectTreeNode::fetchByPath( $this->PathString );
        $baseURLAliases = $this->getURLAliases( $baseNode->attribute( 'node_id' ), $this->PathString, true );
        $baseURLAlias = $baseURLAliases[0][0];
        $folder = $this->StaticStorageDir . $baseURLAlias;
        eZDebug::writeDebug( $folder, "Removing exported folder before the subtree export" );
        eZStaticExportLogger::log( "Removing subtree export's folder: $folder" );

        if ( $handle = @opendir( $folder ) )
        {
            while ( $baseURLAlias == '' and ( $file = readdir( $handle ) ) !== false )
            {
                // eZStaticExport::recursiveDelete is only used for root level.
                // static items at root level are ignored in this function.
                // eZDir::recursiveDelete is used at other levels
                if ( $file == 'design' or $file == 'extension' or $file == 'var' )
                {
                    echo "'$file' is a static folder, not deleting\n";
                    continue;
                }
                if ( ( $file == "." ) || ( $file == ".." ) )
                {
                    continue;
                }
                if ( is_dir( $folder . '/' . $file ) )
                {
                    eZDir::recursiveDelete( $folder . '/' . $file );
                }
                else
                {
                    unlink( $folder . '/' . $file );
                }
            }
            @closedir( $handle );
            // we don't remove the folder if the root folder is the
            if ( $baseURLAlias != '' )
            {
                $this->export->setAttribute('removing_root_folder', true);
            	echo "Removing export root folder $folder\n";
                eZDir::recursiveDelete( $folder );
                echo "Export root folder removed\n";
                $this->export->setAttribute('removing_root_folder', false);
            }
            else
            {
                echo "Export from root, NOT removing export root folder $folder\n";
            }
        }
        
        $this->export->setAttribute('removing_root_folder', false);
    }

    /*!
     Parses a HTML document and extracts external files URLs
    */
    function parseExternalFiles( $HTMLString )
    {
        
    	// var/storage items
        $regexpVarStorage = '#<(?:a|img).*?(?:src|href)="([^"]*var/[^/]+/storage[^"]*)".*?/?>#xmi';
        if ( preg_match_all( $regexpVarStorage, $HTMLString, $varStorageMatches, PREG_PATTERN_ORDER ) )
            $this->externalFilesVarStorage = array_merge( $this->externalFilesVarStorage, $varStorageMatches[1] );

        // content/download items
        $regexpContentDownload = '#<(?:a|img).*?(?:src|href)="([^"]*content/download[^"]*)"/?>#xmi';
        if ( preg_match_all( $regexpContentDownload, $HTMLString, $contentDownloadMatches, PREG_PATTERN_ORDER ) )
            $this->externalFilesContentDownload = array_merge( $this->externalFilesContentDownload, $contentDownloadMatches[1][0] );
		
    }

    /// \privatesection
    /// The name of the host to fetch HTML data from.
    var $HostName;
    /// The base path for the directory where static files are placed.
    var $StaticStorageDir;
    /// The maximum depth of URLs that will be cached.
    var $MaxCacheDepth;
    /// Array of URLs to cache.
    var $CachedURLArray;
    /// Export target
    var $Target;
    /// Exported path string
    var $PathString;
    /// Export type (node or subtree)
    var $ExportType;
    /// Output handler, \s setOuputHandler and \s CliOutputHandler
    var $OutputHandler;
    /// Output handler object
    var $CliOutputHandler;
    /// Folder prefix
    var $FolderPrefix;
    /// current export ID
    var $exportID;
    /// the current export
    var $export;
    /// Time out value
    var $timeout;

    /// external files from var/storage
    var $externalFilesVarStorage = array();
    /// external files from content/download
    var $externalFilesContentDownload = array();
}

?>
