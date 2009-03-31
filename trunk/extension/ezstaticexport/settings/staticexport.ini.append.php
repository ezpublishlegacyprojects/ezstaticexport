<?php /* #?ini charset="utf-8"?

[ExportSettings]
HostName=localhost
# Base folder where static export files should be stored, WITH trailing /
StaticStorageDir=var/staticexport/

# This parameter indicates the siteaccess that should be used in order to determine
# the static resources that should be exported
Siteacess=noven

# Indicate here the subfolder of the document root the instance of eZ publish is
# located at (.e.g http://localhost/foo/bar => foo/bar/). Trailing slash does matter.
# if you are using index.php in the URL, just add it:
# http://localhost/foo/bar/index.php => foo/bar/index.php/
FolderPrefix=php4/noven/index.php/

# Useful only if you have activated cURL extension
# Timeout Value in seconds to wait Apache response when exporting
TimeoutValue=300

# Number of possible retry for each call to the web server if the first call fails
MaxWebServerCallRetry=3 

# Define the log handler
# Values can be :
#     - ezstaticexportlog
#     - ezlog
# Default is ezstaticexportlog and logs everything in the database, ezstaticexport_log table
# ezlog uses the common eZPublish API for logging
[LogSettings]
LogHandler=ezlog

# List of target servers used to store the complete directory structure
# of the generated static cache.
#
# /!\ All default settings must be set via DefaultTargetServer* configuration
# directives /!\
#
# TargetServerList['name'] : the name of the target server
# TargetServerList['url']  : the URL of the target server : it is meta data only, not used for sync
[DefaultTargetServerSettings]
TargetServerName=defaulttarget
TargetServerURL=www.defaulttarget.com
# Connection infos required for sync
PublishingTargets[]
PublishingTargets[]=rsync://localhost/wamp/www/clients/noven-static/folder-default/;options:recursive,update,compress,del,content-type-default
#PublishingTargets[]=rsync://localhost/wamp/www/clients/noven-static/folder-jsp/;options:recursive,update,compress,del,content-type-jsp
#PublishingTargets[]=rsync://katana@fortress.ankh-morpork.net:/home/katana/public_html/noven/;recursive,update,del,compress,ssh

[TargetServerList]
TargetServer[]
TargetServer[]=default1
TargetServer[]=target1

[TargetServer-default1]
TargetServerName=default
TargetServerURL=www.example.com
# IP needed for sync
PublishingTargets[]
PublishingTargets[]=rsync://username:password@8.9.10.11:111/path/to/folder;recursive,update,verbose,del,content-type-default

[TargetServer-target1]
TargetServerName=target1
TargetServerURL=www.target1.com
# IP needed for sync
PublishingTargets[]
PublishingTargets[]=rsync://username:password@16.17.18.19/path/to/folder;options:recursive,update,del,content-type-default
PublishingTargets[]=rsync://username:password@16.17.18.19/path/to/folder;options:recursive,update,del,content-type-jsp

[ExportSettings]
DefaultContentType=text/html
AllowedContentTypes[]
AllowedContentTypes[]=text/html
AllowedContentTypes[]=text/jsp
ContentTypesExtension[text/jsp]=jsp
ContentTypesExtension[text/html]=html

# The number of objects to exports
# in each cron
# Note : if you change this value whereas all exports
# are not finished you may get unexpected results
NumberOfObjectsToExportInTheSameTime=37

# Number of processes that can run simulteaneously
# Caution ! A number too high can kill your server performance !
MaxAuthorizedRunningProcesses=3

# Content class identifiers you want to exclude from the static export
# Useful if you use object_relations and/or node_view_gui
ExcludeContentClasses[]

# List the available drivers to synchronize
# with a remote server
# There must be a match with the name of the
# driver and the name of its configuration group
# For example:
# DriverList[]=rsync
# [RsyncDriverSettings]
#
# Another example
# DriverList[]=ftp
# [FtpDriverSettings]
#
[SynchronizationSettings]
DriverList[]
DriverList[]=rsync

# Scripts you want to execute BEFORE the synchronisation process, once the export is done
# Scripts path must be absolute
PreSyncScripts[]
#PreSyncScripts[]=/home/path/to/my/script.sh
#PreSyncScripts[]=php /path/to/php/script.php

# Scripts you want to execute AFTER the synchronisation process
# Scripts path must be absolute
PostSyncScripts[]
#PreSyncScripts[]=/home/path/to/my/script.sh
#PreSyncScripts[]=php /path/to/php/script.php

# DriverArgs :
#
# DriverArgs[SimulationMode]=dry-run : will use rsync --dry-run if desired
# DriverArgs[RecursiveMode]=recursive : will use rsync --recursive if desired
# DriverArgs[UpdateMode]=update : will use rsync --update if desired
# DriverArgs[CompressMode]=compress : will user rsync --compress if desired
#
[DriverSettings-rsync]
DriverPath=c:/cygwin/bin/rsync.exe
DriverArgs[]
DriverArgs[simulation]=dry-run
DriverArgs[recursive]=recursive
DriverArgs[update]=update
DriverArgs[compress]=compress
DriverArgs[verbose]=verbose
DriverArgs[ssh]=--rsh="ssh -o 'BatchMode yes'"
DriverArgs[delete]=--del
DriverArgs[content-type-default]=--filter="- *.jsp"
DriverArgs[content-type-jsp]=--filter="+ *.jsp" --filter="- *.html" --filter="- var/" --filter="- extension/"
DriverArgs[content-type-all]=--filter="+ *"

*/
?>
