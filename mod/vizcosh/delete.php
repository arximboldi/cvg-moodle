<?php 


require_once ('../../config.php');
require_once ('lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$chapterid = required_param('chapterid', PARAM_INT); // Chapter ID

require_login();
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
    error('Course Module ID was incorrect');
}
if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid)) {
    error('Error reading eMargo chapter.');
}	

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

#allowedit contains teacher editing rights
$allowedit = has_capability('moodle/course:manageactivities', $context);

if ($allowedit) {
	$confirm = optional_param('confirm', 0, PARAM_BOOL);

	// header and strings
	$strvizcoshs = get_string('modulenameplural', 'vizcosh');
	$strvizcosh  = get_string('modulename', 'vizcosh');

	if ($course->category) {
		$navigation = '<a href="../../course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> ->';
	} else {
		$navigation = '';
	}

	print_header( "$course->shortname: $vizcosh->name",
				  $course->fullname,
				  "$navigation <a href=index.php?id=$course->id>$strvizcoshs</a> -> $vizcosh->name",
				  '',
				  '',
				  true,
				  '',
				  ''
				);

	// form processing
	if ($confirm) {  // the operation was confirmed.
		if ($allowedit){
			#perform real deletions for teachers
			if (!delete_records('vizcosh_chapters', 'vizcoshid', $vizcosh->id, 'id', $chapterid)) {
				error('Could not delete chapter');
			}
			
			#Delete paragraphs
			if (!delete_records('vizcosh_paragraphs', 'vizcoshid', $vizcosh->id, 'chapterid', $chapterid)) {
				error('Could not delete paragraphs');
			}

		   #Delete comments for this chapter
			if (!delete_records('vizcosh_comments', 'vizcoshid', $vizcosh->id, 'chapter', $chapterid)) {
				error('Could not delete comments belonging to this chapter');
			}
			
		   #Delete comment read status for this chapter
			if (!delete_records('vizcosh_commentread', 'vizcoshid', $vizcosh->id, 'chapterid', $chapterid)) {
				error('Could not delete comments read status');
			}
			
		   #Delete markings for this chapter
			if (!delete_records('vizcosh_markings', 'vizcoshid', $vizcosh->id, 'chapter', $chapterid)) {
				error('Could not delete chapter markings');
			}

		   #Delete questionmarks for this paragraph
			if (!delete_records('vizcosh_questionmarks', 'vizcoshid', $vizcosh->id, 'chapter', $chapterid)) {
				error('Could not delete chapter questionmarks');
			}		
			
			add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
			add_to_log($course->id, 'vizcosh', 'update', 'view.php?id=' . $cm->id, $vizcosh->id, $cm->id);
			#vizcosh_check_structure($vizcosh->id);
			redirect('view.php?id=' . $cm->id);
			die;
		}
	} else {
		// the operation has not been confirmed yet so ask the user to do so
		$strconfirm = get_string('confchapterdelete','vizcosh')."<div class=\"box errorbox errorboxcontent\">"
			. get_string('chapterdeletewarning','vizcosh') . "</div>";
		
		echo '<br />';
		notice_yesno("<strong>$chapter->title</strong><p>$strconfirm</p>",
					  "delete.php?id=$cm->id&chapterid=$chapter->id&confirm=1&sesskey=$USER->sesskey",
					  "view.php?id=$cm->id&chapterid=$chapter->id");
	}

	print_footer($course);
}
else
	error('You are not allowed to delete chapters!');
?>
