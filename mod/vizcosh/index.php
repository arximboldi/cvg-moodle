<?php 

/**
 * This page lists all the instances of eMargo in a particular course
 *
 **/

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

// =========================================================================
// security checks START - teachers and students view
// =========================================================================

if (!$course = get_record('course', 'id', $id)) {
	error('Course ID is incorrect');
}

require_course_login($course, true);

unset($id);

// =========================================================================
// security checks END
// =========================================================================


// get all required strings
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$strvizcosh  = get_string('modulename', 'vizcosh');

/// print the header
$navlinks = array();
$navlinks[] = array('name' => $strvizcoshs, 'link' => '', 'type' => 'activity');
$navigation = build_navigation($navlinks);

print_header_simple("$strvizcoshs", "", $navigation, "", "", true, "", navmenu($course));

add_to_log($course->id, 'vizcosh', 'view all', 'index.php?id='.$course->id, '');

// get all the appropriate data
if (!$vizcoshs = get_all_instances_in_course('vizcosh', $course)) {
	notice('There are no VizCoSHs', '../../course/view.php?id='.$course->id);
	die;
}

/// print the list of instances
$strname = get_string('name');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strsummary = get_string('summary');
$strchapters = get_string('chapterscount', 'vizcosh');

if ($course->format == 'weeks') {
	$table->head  = array($strweek, $strname, $strsummary, $strchapters);
	$table->align = array('center', 'left', 'left', 'center');
} else if ($course->format == 'topics') {
	$table->head  = array($strtopic, $strname, $strsummary, $strchapters);
	$table->align = array('center', 'left', 'left', 'center');
} else {
	$table->head  = array($strname, $strsummary, $strchapters);
	$table->align = array('left', 'left', 'left');
}

$currentsection = '';
foreach ($vizcoshs as $vizcosh) {
	$nocleanoption = new object();
	$nocleanoption->noclean = true;
	$vizcosh->summary = format_text($vizcosh->summary, FORMAT_HTML, $nocleanoption, $course->id);
	$vizcosh->summary = '<span style="font-size:x-small;">' . $vizcosh->summary . '</span>';

	if (!$vizcosh->visible) {
		// show dimmed if the mod is hidden
		$link = '<a class="dimmed" href="view.php?id=' . $vizcosh->coursemodule . '">' . $vizcosh->name . '</a>';
	} else {
		// show normal if the mod is visible
		$link = '<a href="view.php?id=' . $vizcosh->coursemodule . '">' . $vizcosh->name . '</a>';
	}

	$count = count_records('vizcosh_chapters', 'vizcoshid', $vizcosh->id, 'hidden', '0');

	if ($course->format == 'weeks' or $course->format == 'topics') {
		$printsection = '';
		if ($vizcosh->section !== $currentsection) {
			if ($vizcosh->section) {
				$printsection = $vizcosh->section;
			}
			if ($currentsection !== '') {
				$table->data[] = 'hr';
			}
			$currentsection = $vizcosh->section;
		}
		$table->data[] = array($printsection, $link, $vizcosh->summary, $count);
	} else {
		$table->data[] = array($link, $vizcosh->summary, $count);
	}
}

echo '<br />';
print_table($table);
echo '<br />';

print_footer($course);

?>
