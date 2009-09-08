<?php 

// standard routine to allow only teachers in
// check of $id and $chapterid parameters

require_once('../../config.php');
require_once('lib.php');

$id        = required_param('id', PARAM_INT);        // Course Module ID
$chapterid = required_param('chapterid', PARAM_INT); // Chapter ID

require_login();

if (!confirm_sesskey()) {
	error(get_string('confirmsesskeybad', 'error'));
}
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
	error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
	error('Course is misconfigured');
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:manageactivities', $context);

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
	error('Course module is incorrect');
}

if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid)) {
	error('Incorrect chapter ID');
}

if ($chapter->vizcoshid != $vizcosh->id) {//chapter id not in this vizcosh!!!!
	error('Chapter not in this vizcosh!');
}

// check all variables
unset($id);
unset($chapterid);

?>
