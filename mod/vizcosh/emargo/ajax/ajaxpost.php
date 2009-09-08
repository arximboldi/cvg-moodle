<?php

/*
 * PHP-based AJAX-Backed for YUI ajax-requests
 * some parts of this file are based on http://www.satyam.com.ar/yui/PhpJson.htm
 *
 * @author: Andreas Kothe
 */


require_once('../../../../config.php');

// Moodle security checks START 
$moduleName = required_param('moduleName', PARAM_FORMAT);
$courseModuleId	= required_param('courseModuleId', PARAM_INT);
$chapterid	= required_param('chapterid', PARAM_INT);

if (!$cm = get_coursemodule_from_id($moduleName, $courseModuleId)) {
	error('Course Module ID was incorrect');
}
if (!$course = get_record('course', 'id', $cm->course)) {
	error('Course is misconfigured');
}
if (!$moduleInstance = get_record($moduleName, 'id', $cm->instance)) {
	error('Course module is incorrect');
}
require_course_login($course, true, $cm);


if ($moduleName == 'vizcosh') {
	require_once('../marker.class.php');
}
require_once('../emargolib.php');
require_once('ajaxfunctions.php');


/**
 * this function is called by all AJAX requests and branches to a function with the 
 * name pattern "ajax_${ajaxObj}_${ajaxAction}" where $ajaxObj and $ajaxAction are 
 * PHP variable names representing arguments coming in the POST request. 
 * @see http://www.satyam.com.ar/yui/PhpJson.htm#ajaxReq
 */
function ajaxReq() {
	$ajaxObj = trim($_REQUEST['ajaxObj']);
	if (strlen($ajaxObj)) {
		if (preg_match('/^[a-zA-Z]+$/',$ajaxObj)) {
			$ajaxAction = trim($_REQUEST['ajaxAction']);
			if (strlen($ajaxAction)) {
				if (preg_match('/^[a-zA-Z]+$/',$ajaxAction)) {
					$func = "ajax_${ajaxObj}_${ajaxAction}";
					if (function_exists($func)) {
						// avoid caching of ajax responses
						header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
						header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
						header('Content-type: application/json; charset=utf-8');
						set_error_handler('ajaxErrorHandler');
						$func();
						ajaxReply();
					}
					ajaxReply(601, 'function not defined: ' . $func);
				}
				ajaxReply(602, 'action contains invalid chars: ' . $ajaxAction);
			}
			ajaxReply(603, 'missing action');
		}
		ajaxReply (604, 'object name has invalid characters: ' . $ajaxObj);
	}
}


/**
 * @see http://www.satyam.com.ar/yui/PhpJson.htm#ajaxReply
 */
function ajaxReply($replyCode=200, $replyText='Ok') {
	$s = '';
	$nSql = '';
	for ($iArg=2; $iArg < func_num_args(); $iArg++) {
		$arg = func_get_arg($iArg);
		if (is_array($arg)) {
			$arg = json_encode($arg);
			$s .= ',' . substr($arg,1,strlen($arg)-2);
		} elseif (is_string($arg)) {
			$result = ajaxSqlQuery($arg);
			$s .= ',"data' . $nSql . '":[';
			while ($row = mysql_fetch_assoc($result)) {
				if ($nextRow) {
					$s .= ',';
				} else {
					$nextRow = true;
				}
				$s .= json_encode($row);
			}
			$s .= ']';
			mysql_free_result($result);
			$nSql++;
		} else {
			trigger_error("ajaxReply: optional argument at position $iArg value $arg is invalid, only arrays or SQL statements allowed" , E_USER_ERROR);
		}
	}
	if (isset($_REQUEST['ajaxCallback'])) {
		$ajaxCallback = trim($_REQUEST['ajaxCallback']);
	}
	if (isset($ajaxCallback)) {
		header('Content-type: application/javascript; charset=utf-8');
		echo $ajaxCallback . '({"replyCode":' . $replyCode . ',"replyText":"' . $replyText . '"' . $s . '});';
	} else {
		echo '{"replyCode":' . $replyCode . ',"replyText":"' . $replyText . '"' . $s . '}';
	}
	exit;
}


/**
 * @see http://www.satyam.com.ar/yui/PhpJson.htm#ajaxErrorHandler
 */
function ajaxErrorHandler($errno, $errstr, $errfile, $errline)    {
	switch ($errno) {
		case E_USER_ERROR:
			echo '{"replyCode":611,"replyText":"User Error: ' . addslashes($errstr) . '","errno":' . $errno;
			break;
		case E_USER_WARNING:
			echo '{"replyCode":612,"replyText":"User Warning: ' . addslashes($errstr) . '","errno":' . $errno;
		break;
		case E_USER_NOTICE:
		case E_NOTICE:
			return false;
		default:
			echo '{"replyCode":610,"replyText":"' . addslashes($errstr) . '","errno":' . $errno;
		break;
	}
	if ($errfile) {
		echo ',"errfile":"' . addslashes($errfile) . '"';
	}
	if ($errline) {
		echo ',"errline":"' . $errline . '"';
	}
	echo '}';
	die();
}


/**
 * needed for ajaxReply, but not used in this context
 */
function ajaxSqlQuery($sql) {
	$result = mysql_query($sql);
	if ($result) {
		return $result;
	}
	ajaxReply(620, 'Sql error: ' . mysql_error(), array('sql' => $sql));
}


// fire up the engines!!
ajaxReq(); 


?>