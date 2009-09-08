<?php

require_once ('../../config.php');
require_once ('lib.php');

$up = optional_param('up', 0, PARAM_BOOL);
$id = required_param('id', PARAM_INT); // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID

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

$oldchapters = get_records('vizcosh_chapters', 'vizcoshid', $vizcosh->id, 'pagenum', 'id, pagenum');

$nothing = 0;

$chapters = array();
$chs = 0;
$che = 0;
$ts = 0;
$te = 0;
// create new ordered array and find chapters to be moved
$i = 1;
$found = 0;
foreach ($oldchapters as $ch) {
	$chapters[$i] = $ch;
	if ($chapterid == $ch->id) {
		$chs = $i;
		$che = $chs;
	} else if ($chs) {
		if ($found) {
			// nothing
		} else {
			$found = 1;
		}
	}
	$i++;
}

// find target chapter(s)
// moving chapter and looking for next/previous chapter
if ($up) { // up
	if ($chs == 1) {
		$nothing = 1; // already first
	} else {
		$te = $chs - 1;
		for($i = $chs-1; $i >= 1; $i--) {
			$ts = $i;
			break;
		}
	}
} else { //down
	if ($che == count($chapters)) {
		$nothing = 1; // already last
	} else {
		$ts = $che + 1;
		$found = 0;
		for($i = $che+1; $i <= count($chapters); $i++) {
			if ($found) {
				break;
			} else {
				$te = $i;
				$found = 1;
			}
		}
	}
}

// recreated newly sorted list of chapters
if (!$nothing) {
	$newchapters = array();

	if ($up) {
		if ($ts > 1) {
			for ($i=1; $i<$ts; $i++) {
				$newchapters[] = $chapters[$i];
			}
		}
		for ($i=$chs; $i<=$che; $i++) {
			$newchapters[$i] = $chapters[$i];
		}
		for ($i=$ts; $i<=$te; $i++) {
			$newchapters[$i] = $chapters[$i];
		}
		if ($che<count($chapters)) {
			for ($i=$che; $i<=count($chapters); $i++) {
				$newchapters[$i] = $chapters[$i];
			}
		}
	} else {
		if ($chs > 1) {
			for ($i=1; $i<$chs; $i++) {
				$newchapters[] = $chapters[$i];
			}
		}
		for ($i=$ts; $i<=$te; $i++) {
			$newchapters[$i] = $chapters[$i];
		}
		for ($i=$chs; $i<=$che; $i++) {
			$newchapters[$i] = $chapters[$i];
		}
		if ($te<count($chapters)) {
			for ($i=$te; $i<=count($chapters); $i++) {
				$newchapters[$i] = $chapters[$i];
			}
		}
	}

	// store chapters in the new order
	$i = 1;
	foreach ($newchapters as $ch) {
		$ch->pagenum = $i;
		update_record('vizcosh_chapters', $ch);
		$i++;
	}
}

add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
add_to_log($course->id, 'vizcosh', 'update', 'view.php?id=' . $cm->id, $vizcosh->id, $cm->id);
#vizcosh_check_structure($vizcosh->id);
redirect('view.php?id=' . $cm->id . '&chapterid=' . $chapterid);
die;

?>
