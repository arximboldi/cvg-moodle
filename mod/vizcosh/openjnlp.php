<?php
require_once ('../../config.php');
require_once ('lib.php');
$filepath = required_param('filepath', PARAM_TEXT);
$filename = required_param('filename', PARAM_TEXT); 
//headers for opening jnlp-file
header('Content-type: application/x-java-jnlp-file');
header('Content-Disposition: inline; filename="'.$filename.'"');
// read the file from the given location and with the given name
$file = $filepath.'/'.$filename;
readfile($file);
?>