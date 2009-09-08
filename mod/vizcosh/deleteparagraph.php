<?php 
 
require_once ('../../config.php');
require_once ('lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$paragraphid = required_param('paragraphid', PARAM_INT); //id in mdl_vizcosh_paragraphs
$paragraph = get_record('vizcosh_paragraphs', 'id', $paragraphid);
if (!$paragraph) {
	error('Paragraph doesn\'t exist');
}

require_login();
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
    error('Course Module ID was incorrect');
}
if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

#allowedit contains teacher editing rights
$allowedit = has_capability('moodle/course:manageactivities', $context);
#allowtextedit contains user editing rights
$allowtextedit = (has_capability('mod/vizcosh:useredit', $context) && ($vizcosh->enablegroupfunction ==1));

if (($allowtextedit) or ($allowedit)) {

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
			#Delete paragraph
			if (!delete_records('vizcosh_paragraphs', 'vizcoshid', $vizcosh->id, 'id', $paragraph->id)) {
				error('Could not delete paragraph');
			}
			
		   #Delete comments for this paragraph
			if (!delete_records('vizcosh_comments', 'vizcoshid', $vizcosh->id, 'paragraph', $paragraph->id, 'chapter', $paragraph->chapterid)) {
				error('Could not delete comments belonging to this paragraph');
			}
			
		   #Delete comment read status for this paragraph
			if (!delete_records('vizcosh_commentread', 'vizcoshid', $vizcosh->id, 'paragraphid', $paragraph->id, 'chapterid', $paragraph->chapterid)) {
				error('Could not delete comments read status');
			}		
		}
		else if ($allowtextedit){
			#authors in group emargo aren't allowed to really delete items
			if ($paragraph) {
				$paragraph->content = "";

				//save to database
				if (!update_record('vizcosh_paragraphs', $paragraph)) {
					error('Could not update this paragraph.', $errorurl);
				}
			}
		}
		
		#Delete markings for this paragraph
		if (!delete_records('vizcosh_markings', 'vizcoshid', $vizcosh->id, 'paragraphid', $paragraph->id, 'chapter', $paragraph->chapterid)) {
			error('Could not delete paragraph markings');
		}

	   #Delete questionmarks for this paragraph
		if (!delete_records('vizcosh_questionmarks', 'vizcoshid', $vizcosh->id, 'paragraph', $paragraph->id, 'chapter', $paragraph->chapterid)) {
			error('Could not delete paragraph questionmarks');
		}

	   #Delete bookmarks for this paragraph
		if (!delete_records('vizcosh_bookmarks', 'vizcoshid', $vizcosh->id, 'paragraph', $paragraph->id, 'chapter', $paragraph->chapterid)) {
			error('Could not delete paragraph bookmarks');
		}	
		 
		add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
		add_to_log($course->id, 'vizcosh', 'update', 'view.php?id=' . $cm->id, $vizcosh->id, $cm->id);
		#vizcosh_check_structure($vizcosh->id);
		redirect('view.php?id=' . $cm->id);
		die;
	} else {
		// the operation has not been confirmed yet so ask the user to do so
		$strconfirm = get_string('confparagraphdelete','vizcosh')."<div class=\"box errorbox errorboxcontent\">";
		if ($allowedit)
			$strconfirm .= get_string('paragraphdeletewarning','vizcosh');
		else if ($allowtextedit)
			$strconfirm .= get_string('paragraphdeletegroupemargonotice','vizcosh');
		$strconfirm .= "</div>";
		
		echo '<br />';
		notice_yesno("<strong>Absatz l&ouml;schen?</strong><p>$paragraph->content</p><p>$strconfirm</p>",
					  "deleteparagraph.php?id=$cm->id&chapterid=$paragraph->chapterid&confirm=1&paragraphid=$paragraph->id&sesskey=$USER->sesskey",
					  "view.php?id=$cm->id&chapterid=$chapterid");				  
	}

	print_footer($course);
}
else
	error('Action not allowed');
?>
