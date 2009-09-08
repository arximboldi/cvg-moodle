<?php

require_once('../../config.php');
require_once('lib.php');

require_once("pagelib.php");
require_once($CFG->libdir . '/blocklib.php');

$id			= required_param('id', PARAM_INT);				// Course Module ID
$chapterid	= optional_param('chapterid', 0, PARAM_INT);	// Chapter ID
$edit		= optional_param('edit', -1, PARAM_BOOL);		// Edit mode
$discussion = optional_param('discussion', -1, PARAM_BOOL);	// Discussion view

$moduleName = "vizcosh";

// =========================================================================
// security checks START - teachers edit; students view
// =========================================================================

if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
	error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
	error('Course is misconfigured');
}

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
	error('Course module is incorrect');
}

require_course_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
#allowedit contains teacher editing rights
$allowedit = has_capability('moodle/course:manageactivities', $context);
#allowtextedit contains user editing rights
$allowtextedit = (has_capability('mod/vizcosh:useredit', $context) && ($vizcosh->enablegroupfunction ==1));

if (($allowtextedit) and (!$allowedit)) {
	#Studierende, die bearbeiten dürfen, sind für das eMargo automatisch im Bearbeitenmodus
	$edit = 1;
	$USER->editing = $edit;
}

if ($allowedit) {
	if ($edit != -1) {
		$USER->editing = $edit;
	} else {
		if (isset($USER->editing)) {
			$edit = $USER->editing;
		} else {
			$edit = 0;
		}
	}
} else {
	$edit = 0;
}

// read chapters
//$chapters is an array of all chapters
//$select determines the vizcoshid
$select = ($allowedit or $allowtextedit) ? "vizcoshid = $vizcosh->id" : "vizcoshid = $vizcosh->id AND hidden = 0";
$ignorehidden = ($allowedit or $allowtextedit) ? "" : "AND ".$CFG->prefix."vizcosh_chapters.hidden = 0 ";
$chapters = get_records_select('vizcosh_chapters', $select, 'pagenum', 'id, pagenum, title, hidden');

#display hidden chapters only for teachers, otherwise exclude them
$paragraphs = get_records_sql('SELECT '.$CFG->prefix.'vizcosh_paragraphs.id, '.$CFG->prefix.'vizcosh_paragraphs.chapterid, '.$CFG->prefix.'vizcosh_paragraphs.orderposition FROM '.$CFG->prefix.'vizcosh_paragraphs INNER JOIN '.$CFG->prefix.'vizcosh_chapters ON '.$CFG->prefix.'vizcosh_paragraphs.chapterid = '.$CFG->prefix.'vizcosh_chapters.id WHERE '.$CFG->prefix.'vizcosh_paragraphs.vizcoshid = '.$vizcosh->id.' '.$ignorehidden.' ORDER BY '.$CFG->prefix.'vizcosh_paragraphs.chapterid, '.$CFG->prefix.'vizcosh_paragraphs.orderposition;');

if (!$chapters) {
	if ($allowedit || $allowtextedit) {
		redirect('edit.php?id=' . $cm->id); // no chapters - add new one
		die;
	} else {
		error(get_string('empty_emargo_no_rights', 'emargo'));
	}
}

// check chapterid and read chapter data
if ($chapterid == '0') { // go to first chapter if no given
	foreach($chapters as $ch) {
		if ($allowedit) {
			$chapterid = $ch->id;
			break;
		}
		if (!$ch->hidden) {
			$chapterid = $ch->id;
			break;
		}
	}
}

if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid)) {
	error('Error reading eMargo chapters.');
}

// chapter is hidden for students
if (!($allowedit or $allowtextedit) and $chapter->hidden) {
	error(get_string('hiddenchapter', 'emargo'));
}

// chapter not part of this vizcosh!
if ($chapter->vizcoshid != $vizcosh->id) {
	error('Chapter not part of this eMargo!');
}

// include all emargo features and all required header metadata
if (!$vizcosh->disableemargo) {
	require_once($CFG->dirroot . '/mod/vizcosh/emargo/emargoheader.php');
}

$display_toc_as_html_list = ($CFG->vizcosh_tocformat == "list" || $edit || (($allowtextedit) and (!$allowedit)));

add_to_log($course->id, 'vizcosh', 'view', 'view.php?id=' . $cm->id . '&amp;chapterid=' . $chapter->id, $vizcosh->id, $cm->id);

// read standard strings
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$strvizcosh = get_string('modulename', 'vizcosh');
$strTOC = get_string('TOC', 'vizcosh');

// prepare header
$PAGE = page_create_instance($vizcosh->id);
$PAGE->set_vizcoshpageid($PAGE->id);
$pageblocks = blocks_setup($PAGE);

global $emargometadata;
$PAGE->print_header($course->shortname . ': ' . $vizcosh->name . ' (' . $chapter->title . ' )', '', $chapter, $emargometadata);

// prepare chapter navigation icons
$previd = null;
$nextid = null;
#found represents the current chapter
$found = 0;

foreach ($chapters as $ch) {

	#ignore hidden chapters
	if ($ch->hidden == 0){
		if ($found) {	
			$nextid = $ch->id;
			break;
		}
		if ($ch->id == $chapter->id) {
			$found = 1;
		}
		if (!$found) {
			$previd = $ch->id;
		}
	}
}
if ($ch == current($chapters)) {
	$nextid = $ch->id;
}

$chnavigation = '';
if ($previd) {
	$chnavigation .= '<a title="' . get_string('navprev', 'vizcosh') . '" href="view.php?id=' . $cm->id . '&amp;chapterid=' . $previd . '"><img src="pix/nav_prev.png" class="bigicon" alt="' . get_string('navprev', 'vizcosh') . '"/></a>';
} else {
	$chnavigation .= '<img src="pix/nav_prev_dis.png" class="bigicon" alt="" />';
}
if ($nextid) {
	$chnavigation .= '<a title="' . get_string('navnext', 'vizcosh') . '" href="view.php?id=' . $cm->id . '&amp;chapterid=' . $nextid . '"><img src="pix/nav_next.png" class="bigicon" alt="' . get_string('navnext', 'vizcosh') . '" /></a>';
} else {
	$sec = '';
	if ($section = get_record('course_sections', 'id', $cm->section)) {
		$sec = $section->section;
	}
	$chnavigation .= '<a title="' . get_string('navexit', 'vizcosh') . '" href="../../course/view.php?id=' . $course->id . '#section-' . $sec . '"><img src="pix/nav_exit.png" class="bigicon" alt="' . get_string('navexit', 'vizcosh') . '" /></a>';
}

// prepare print icons
if ($vizcosh->disableprinting) {
	$printvizcosh = '';
	$printchapter = '';
} else {
	$printvizcosh = '<a title="' . get_string('printvizcosh', 'vizcosh') . '" target="_blank" href="print.php?id=' . $cm->id . '" onclick="this.target=\'_blank\'"><img src="pix/print_vizcosh.png" class="bigicon" alt="' . get_string('printvizcosh', 'vizcosh') . '"/></a>';
	$printchapter = '<a title="' . get_string('printchapter', 'vizcosh') . '" target="_blank" href="print.php?id=' . $cm->id . '&amp;chapterid=' . $chapter->id . '" onclick="this.target=\'_blank\'"><img src="pix/print_chapter.png" class="bigicon" alt="' . get_string('printchapter', 'vizcosh') . '"/></a>';

}

// prepare $toc and $currtitle, $currsubtitle
require('toc.php');

if ($edit) {
	$tocwidth = $CFG->vizcosh_tocwidth + 80;
} else {
	$tocwidth = $CFG->vizcosh_tocwidth;
}

$doimport = ($allowedit and $edit) ? '<a href="import.php?id=' . $cm->id . '">' . get_string('doimport', 'vizcosh') . '</a>' : '';

// Enable the IMS CP button
#$generateimscp = ($allowedit) ? '<a title="' . get_string('generateimscp', 'vizcosh') . '" href="generateimscp.php?id=' . $cm->id . '"><img class="bigicon" src="pix/generateimscp.gif" height="24" width="24" border="0"></img></a>' : '';
$generateimscp = '';

// =====================================================
// VizCoSH display HTML code
// =====================================================

// construct left column
$leftcolumn = print_box_start('generalbox', '', true);
$leftcolumn .= $toc;
$leftcolumn .= print_box_end(true);
if ($allowedit and $edit) {
	$leftcolumn .= '<div style="font-size:0.9em; margin-bottom:10px;">';
	$leftcolumn .= helpbutton('index', get_string('faq','vizcosh'), 'vizcosh', true, true, '', true);
	$leftcolumn .= '</div>';
}
ob_start();
	// unfortunately, the result of blocks_print_group() cannot be saved in a variable (bad moodle core design), 
	// which means that we need to wrap it around an output buffer
	if (vizcosh_blocks_have_content($vizcosh, $pageblocks, BLOCK_POS_LEFT)) { 
		if (!empty($CFG->showblocksonmodpages)) {
			if ((blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing())) {
				blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
			}
		}
	}
$leftcolumn .= ob_get_contents();
ob_end_clean();

// construct right column
ob_start();
	if (!$vizcosh->disableemargo) {
		// same problem as explained above, regarding blocks_print_group()
		//emargo_print_view_block($cm->id, $vizcosh);
				
		emargo_print_button_block($cm->id, $vizcosh, $paragraphs, 'vizcosh');
		if (vizcosh_blocks_have_content($vizcosh, $pageblocks, BLOCK_POS_RIGHT)) { 
			if (!empty($CFG->showblocksonmodpages)) {
				if ((blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $PAGE->user_is_editing())) {
					blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
				}
			}
		}
	}
$rightcolumn = ob_get_contents();
ob_end_clean();

require('view.html');

// construct title
print_box_start('generalbox');

?>

<!-- eMargo statusbar for AJAX status messages -->
<div style="height:16px;" id="statusbar"></div>

<!-- eMargo content-block, will be filled using AJAX -->
<div id="cbcontent"></div>

<?php
if (!$vizcosh->disableemargo) {
	// include additional (hidden) layers used for eMargo features like button-tooltips and context-menus
	echo emargo_print_marker_contextmenu();
} else {
	// include VizCoSH-content without AJAX if eMargo-features are disabled by the teacher
    $content = '<p class="vizcosh_chapter_title">'.$currtitle.'</p>';

	#Load all paragraphs of the $chapterID into a recordset
	if (!$all_paragraphs_array = get_recordset_select('vizcosh_paragraphs', 'vizcoshid = '.$vizcosh->id.' AND chapterid = '.$chapterid, 'orderposition')){	
		error('Could not load paragraphs');	
	}
	else{
		if ($all_paragraphs_array->_numOfRows > 0){
			#contains paragraphs, now convert to an array and renumber index
			$all_paragraphs_array = array_values(recordset_to_array($all_paragraphs_array));

			// print all paragraphs
			for($i=0; $i<count($all_paragraphs_array); $i++) {
				$content .= "<p>".$all_paragraphs_array[$i]->content."</p>";
			}					
		}
		else{
			//contains no paragraphs
			//Todo language
			$content .= '<p>Dieses Kapitel enth&auml;lt noch keinen Inhalt. ';
			if ($USER->editing==1){
				$content .= '<br><br><a title="'.get_string('edit').'" href="editparagraph.php?id='.$cm->id.'&amp;chapterid='.$chapterid.'&paragraphid=-1&orderposition=1"><img src="pix/add.gif" height="11" class="iconsmall" alt="'.get_string('edit').'" /> Absatz hinzuf&uuml;gen</a>';		
			}
			else{
				$content .= 'Um Inhalt hinzuzuf&uuml;gen, wechseln Sie in den Bearbeiten-Modus.</p>';
			}
		}
	}		

    $nocleanoption = new object();
    $nocleanoption->noclean = true;
    echo '<div class="vizcosh_content">';
    echo format_text($content, FORMAT_HTML, $nocleanoption, $course->id);
    echo '</div>';
}

print_box_end();

// lower navigation
echo '<p>' . $chnavigation . '</p>';
		
// finish the page
		?>
		</td>
	</tr>
</table>

<?php
// include emargodata needed in the page footer
if (!$vizcosh->disableemargo) {
	require_once($CFG->dirroot . '/mod/vizcosh/emargo/emargofooter.php');
}
?>

<?php
	print_footer($course);
?>
