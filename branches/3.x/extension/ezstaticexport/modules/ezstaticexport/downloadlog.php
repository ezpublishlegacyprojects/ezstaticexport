<?php
include_once( 'extension/ezstaticexport/classes/ezstaticexportlog.php' );
include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );

$export = eZStaticExportExport::fetch($Params['ExportID']);

$logString = '';
$log = eZStaticExportLog::fetchByExportID( $Params['ExportID'] );
foreach($log as $line)
{
    $logString .= '['.date( 'd/m/y H:i:s', $line['date']).'] ' . $line['message'] . "\r\n";
}

header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // required for certain browsers
header("Content-Type: text/plain");
// change, added quotes to allow spaces in filenames, by Rajkumar Singh
header("Content-Disposition: attachment; filename=\"staticexport-".$export->attribute('id') . '-' . date('Ymdhi').".log\";" );
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".strlen($logString));
echo $logString;
?>
