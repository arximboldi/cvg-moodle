<?php
require_once ('../../config.php');
require_once ('lib.php');

/**
Parameter:
ChapterID: The chapters ID
Paragraph: The position of the paragraph to move
moveup: Direction of movement, 1 = up, 0 = down
*/

$id = required_param('id', PARAM_INT); // Course Module ID
$chapterid = required_param('chapterid', PARAM_INT);
$paragraphid = required_param('paragraphid', PARAM_INT);
$position = required_param('position', PARAM_INT);
$moveup = required_param('moveup', PARAM_BOOL); 

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

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/vizcosh:useredit', $context);

$paragraph = get_record('vizcosh_paragraphs', 'vizcoshid', $vizcosh->id, 'id', $paragraphid);

if ($paragraph) {	
	#paragraph exists
	if ($moveup == 1){
		//Move paragraph up
		if ($position == 1){
			//Paragraph already at top
		}
		else if ($position > 1){
			//move		
			$move_to_paragraph = get_record_sql('SELECT id, chapterid, orderposition FROM ' . $CFG->prefix . 'vizcosh_paragraphs WHERE vizcoshid = '.$vizcosh->id. ' AND orderposition < '.$paragraph->orderposition.' AND chapterid = '.$chapterid. ' ORDER BY orderposition DESC', true);
						
			//Switch both paragraphs
			$move_to_paragraph_orderposition = $move_to_paragraph->orderposition;
			$move_to_paragraph->orderposition = $paragraph->orderposition;
			$paragraph->orderposition = $move_to_paragraph_orderposition;
			
			update_record('vizcosh_paragraphs', $paragraph);
			update_record('vizcosh_paragraphs', $move_to_paragraph);			
		}
	}
	else{
		//Move paragraph down
		
		//Get the highest position value of the paragraphs
		$count = count_records('vizcosh_paragraphs', 'vizcoshid', $vizcosh->id, 'chapterid', $chapterid);
		
		if ($position >= $count){
			//Paragraph already at the bottom
		}
		else if ($position >= 1){
			$move_to_paragraph = get_record_sql('SELECT id, chapterid, orderposition FROM ' . $CFG->prefix . 'vizcosh_paragraphs WHERE vizcoshid = '.$vizcosh->id. ' AND orderposition > '.$paragraph->orderposition.' AND chapterid = '.$chapterid. ' ORDER BY orderposition ASC', true);
			
			//Switch both paragraphs
			$move_to_paragraph_orderposition = $move_to_paragraph->orderposition;
			$move_to_paragraph->orderposition = $paragraph->orderposition;
			$paragraph->orderposition = $move_to_paragraph_orderposition;
			
			update_record('vizcosh_paragraphs', $paragraph);
			update_record('vizcosh_paragraphs', $move_to_paragraph);
		}
	}
}
	
#add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
#add_to_log($course->id, 'vizcosh', 'update', 'view.php?id=' . $cm->id, $vizcosh->id, $cm->id);
#vizcosh_check_structure($vizcosh->id);
redirect('view.php?id=' . $cm->id . '&chapterid=' . $chapterid);
die;

?>
