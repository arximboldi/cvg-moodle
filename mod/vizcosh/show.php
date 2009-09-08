<?php 

//Manages show/hide of chapters

require_once('../../config.php');
require_once('lib.php');

$id			= required_param('id', PARAM_INT);				// Course Module ID
$chapterid	= optional_param('chapterid', 0, PARAM_INT);	// Chapter ID

if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
	error('Course Module ID was incorrect');
}

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
	error('Course module is incorrect');
}

if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid)) {
	error('Incorrect chapter ID');
}

if ($chapter->vizcoshid != $vizcosh->id) {//chapter id not in this vizcosh!!!!
	error('Chapter not in this vizcosh!');
}

if (!$course = get_record('course', 'id', $cm->course)) {
	error('Course is misconfigured');
}

require_course_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
#allowedit contains teacher editing rights
$allowedit = has_capability('moodle/course:manageactivities', $context);
#allowtextedit contains user editing rights
$allowtextedit = (has_capability('mod/vizcosh:useredit', $context) && ($vizcosh->enablegroupfunction ==1));

if (($allowtextedit) or ($allowedit)) {
	// switch hidden state
	$chapter->hidden = $chapter->hidden ? 0 : 1;

	// add slashes to all text fields
	#$chapter->title = $chapter->title;
	#$chapter->title = addslashes($chapter->title);
	#$chapter->importsrc = addslashes($chapter->importsrc);
	if (!update_record('vizcosh_chapters', $chapter)) {
		error('Could not update your vizcosh');
	}

	add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
	add_to_log($course->id, 'vizcosh', 'update', 'view.php?id=' . $cm->id, $vizcosh->id, $cm->id);
	#vizcosh_check_structure($vizcosh->id);
	redirect('view.php?id='.$cm->id.'&chapterid='.$chapter->id);
	die;
}
else
	error('You are not allowed to do this.');

?>
