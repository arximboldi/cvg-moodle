<?php

require_once('../../config.php');
require_once('lib.php');

$id        = required_param('id', PARAM_INT);           // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID

// =========================================================================
// security checks START - teachers and students view
// =========================================================================
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
	error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
	error('Course is misconfigured');
}

require_course_login($course, true, $cm);

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
	error('Course module is incorrect');
}

if ($vizcosh->disableprinting) {
	error('Printing is disabled');
}

require_once("$CFG->dirroot/mod/vizcosh/emargo/emargolib.php");

// check all variables
if ($chapterid) {
	// single chapter printing
	if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid)) {
		error('Incorrect chapter ID');
	}
	if ($chapter->vizcoshid != $vizcosh->id) { //chapter id not in this vizcosh!!!!
		error('Chapter not in this vizcosh!');
	}
	if ($chapter->hidden) {
		error('Only visible chapters can be printed');
	}
} else {
	// complete vizcosh
	
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);
	$allowedit = has_capability('moodle/course:manageactivities', $context);

	// read chapters
	//$chapters is an array of all chapters
	//$select determines the vizcoshid
	$select = $allowedit ? "vizcoshid = $vizcosh->id" : "vizcoshid = $vizcosh->id AND hidden = 0";
	$ignorehidden = $allowedit ? "" : "AND ".$CFG->prefix."vizcosh_chapters.hidden = 0 ";
	$chapters = get_records_select('vizcosh_chapters', $select, 'pagenum', 'id, pagenum, title, hidden');

	$chapter = false;
}
unset($id);
#unset($chapterid);
// =========================================================================
// security checks END
// =========================================================================

$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$strvizcosh  = get_string('modulename', 'vizcosh');
$strtop = get_string('top', 'vizcosh');

@header('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
@header('Pragma: no-cache');
@header('Expires: ');
@header('Accept-Ranges: none');
@header('Content-type: text/html; charset=utf-8');

$formatoptions = new object();
$formatoptions->noclean = true;

if ($chapter) {
	#show the current chapter
    add_to_log($course->id, 'vizcosh', 'print', 'print.php?id=' . $cm->id . '&chapterid=' . $chapter->id, $vizcosh->id, $cm->id);

	#Load all paragraphs of the $chapterID into a recordset
	if (!$all_paragraphs_array = get_recordset_select('vizcosh_paragraphs', 'vizcoshid = '.$vizcosh->id.' AND chapterid = '.$chapterid, 'orderposition')){	
		error('Could not load paragraphs');	
	}
	else{
		$content = "";
		if ($all_paragraphs_array->_numOfRows > 0){
			#contains paragraphs, now convert to an array and renumber index
			$all_paragraphs_array = array_values(recordset_to_array($all_paragraphs_array));
			// print all paragraphs
				#var_dump($cm);
				#var_dump($cm->id);

			for($i=0; $i<count($all_paragraphs_array); $i++) {
			/**
				$content .= "\n
				<div class=\"wrapper\">
		<div class=\"left\">".$all_paragraphs_array[$i]->content	." </div>
		<div class=\"right\">"

				.emargo_print_commentbox($cm->id, $cm, $all_paragraphs_array, $cm->modname, $chapterid)....
."</div>
</div>";
*/
				$content .= "\n<p>".$all_paragraphs_array[$i]->content	." </p>";
			}
		}
	}
	
	
    $print = 0;
    $edit = 0;

    // page header
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <title><?php echo format_string($vizcosh->name); ?></title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="description" content="<?php echo s(format_string($vizcosh->name)); ?>" />
	      <link rel="stylesheet" type="text/css" href="vizcosh_print.css" />  
    </head>
    <body>
    <a name="top"></a>
    <div class="chapter">
    <?php
	
    echo '<p class="vizcosh_chapter_title">' . $chapter->title . '<p>';
    echo $content;
    echo '</div>';
    echo '</body></html>';

} else {
	#Show all chapters
    add_to_log($course->id, 'vizcosh', 'print', 'print.php?id=' . $cm->id, $vizcosh->id, $cm->id);
    $site = get_record('course','id',1);

    // page header
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <title><?php echo format_string($vizcosh->name); ?></title>
      <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $encoding; ?>" />
      <meta name="description" content="<?php echo s(format_string($vizcosh->name)); ?>" />
      <link rel="stylesheet" type="text/css" href="vizcosh_print.css" />

	  
    </head>
    <body>
    <a name="top"></a>
    <p class="vizcosh_title"><?php echo format_string($vizcosh->name); ?></p>
    <p class="vizcosh_summary"><?php echo format_string($vizcosh->summary); ?></p>
    <div class="vizcosh_info"><table>
    <tr>
    <td><?php echo get_string('site'); ?>:</td>
    <td><a href="<?php echo $CFG->wwwroot; ?>"><?php echo format_string($site->fullname); ?></a></td>
    </tr><tr>
    <td><?php echo get_string('course'); ?>:</td>
    <td><?php echo format_string($course->fullname); ?></td>
    </tr><tr>
    <td><?php echo get_string('modulename', 'vizcosh'); ?>:</td>
    <td><?php echo format_string($vizcosh->name); ?></td>
    </tr><tr>
    <td><?php echo get_string('printedby', 'vizcosh'); ?>:</td>
    <td><?php echo format_string(fullname($USER, true)); ?></td>
    </tr><tr>
    <td><?php echo get_string('printdate','vizcosh'); ?>:</td>
    <td><?php echo userdate(time()); ?></td>
    </tr>
    </table></div>

    <?php
    $print = 1;

    foreach ($chapters as $ch) {
        if (!$ch->hidden) {
            echo '<div class="vizcosh_chapter"><a name="ch' . $ch->id . '"><p class="vizcosh_chapter_title">' . $ch->title . '<p></a>';
 
			$paragraphs = get_records_select('vizcosh_paragraphs', "vizcoshid = $vizcosh->id AND chapterid = ".$ch->id, 'orderposition');
			
			if ($paragraphs){	 
				foreach($paragraphs as $cparagraph){
					echo "<p>".$cparagraph->content."</p>";
				}
			}
			echo '</div>';

        }
    }
    echo '</body> </html>';
}

?>
