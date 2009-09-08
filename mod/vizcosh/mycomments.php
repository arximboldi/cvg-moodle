<?php

require_once('../../config.php');
require_once('lib.php');

$id        = required_param('id', PARAM_INT);           // Course Module ID

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

if (!$all_comments_array = get_recordset_select('vizcosh_comments', 'vizcoshid = '.$vizcosh->id.' AND author = '.$USER->id. ' AND type = 0', 'timecreated')){	
	error('Could not load comments');	
}
else{
	#Comments Array is not empty
	if ($all_comments_array->_numOfRows > 0) {
		$content = '';
		$all_comments_array = array_values(recordset_to_array($all_comments_array));

		for($i=0; $i<count($all_comments_array); $i++) {
		
			if (!$chapter = get_record('vizcosh_chapters', 'id', $all_comments_array[$i]->chapter)) {
				error('Unable to fetch the chapter name');
			}	
		
			$content .= "\n<p><div class='comment'><b>Betreff: ".$all_comments_array[$i]->subject."</b><div class=\"message\">".nl2br($all_comments_array[$i]->message).'</div><div class="date">Geschrieben in Kapitel <em>' . $chapter->title . "</em> am ". userdate($all_comments_array[$i]->timecreated, get_string('strftimerecentfull')) . '</div></div></p>';
		}
	}
	else
		$content = "Es wurden noch keine Kommentare abgegeben.";
}

// page header
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?php echo format_string($vizcosh->name); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="description" content="<?php echo s(format_string($vizcosh->name)); ?>" />
  <link rel="stylesheet" type="text/css" href="vizcosh_print.css" />
	<style type="text/css">
	  div.comment { border:1px solid black; padding:10px; }
	  div.message { border: 1px dashed black; margin-top:10px; padding:10px; }
	  div.date { padding:10 10 0 10; font-size:0.7em; text-align:right;}
	</style>

</head>
<body>
<a name="top"></a>
<div class="chapter">
<?php

echo '<p class="vizcosh_chapter_title">&Ouml;ffentliche Kommentare zu '.$vizcosh->name.' von '.$USER->firstname.' '.$USER->lastname.'<p>';
echo $content;
echo '</div>';
echo '</body></html>';

?>
