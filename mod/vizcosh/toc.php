<?php 

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
 
// included from mod/vizcosh/view.php and print.php
//
// uses:
//   $chapters - all vizcosh chapters
//   $chapter - may be false
//   $cm - course module
//   $vizcosh - vizcosh
//   $edit - force editing view


// fills:
//   $toc
//   $title (not for print)

$currtitle = '';			// active chapter title (plain text)
$prevtitle = '&nbsp;';
$toc = '';					// representation of toc (HTML)

$nch = 0;					// chapter number
$title = '';
$first = 1;

if (!isset($print)) {
	$print = 0;
}
	
#	switch ($vizcosh->numbering) {
#		case NUM_NONE:
#			$toc .= '<div class="vizcosh_toc_none">';
#			break;
#		case NUM_NUMBERS:
#			$toc .= '<div class="vizcosh_toc_numbered">';
#			break;
#	}

if ($print) { // TOC for printing view
	
	$toc .= '<a name="toc"></a>';
	$toc .= '<p class="vizcosh_chapter_title">' . get_string('toc', 'vizcosh') . '</p>';

	$titles = array();
	$toc .= '<ul>';
	foreach($chapters as $ch) {
		$title = trim(strip_tags($ch->title));
		if (!$ch->hidden) {
			$nch++;
			$toc .= ($first) ? '<li>' : '</ul></li><li>';
			if ($vizcosh->numbering == NUM_NUMBERS) {
				$title = "$nch $title";
			}
			$titles[$ch->id] = $title;
			$toc .= '<a title="'.htmlspecialchars($title).'" href="#ch'.$ch->id.'">'.$title.'</a>';
			$toc .= '<ul>';
			$first = 0;
		}
	}
	$toc .= '</ul></li></ul>';
}
else{
	$showteacherfunctions = (($edit) || (($allowtextedit) and (!$allowedit)));
	#$showteacherfunctions = true;
	
	$toc .= '<div class="generalbox box" style="padding:3px; margin:0;"><a style="cursor: pointer; font-size:0.8em;" id="toggletoc" title='.(count($chapters)+2).'>'. get_string('toc', 'vizcosh') . ' <img align=absmiddle src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/toggletoc.png" /></a>
			<div id="s_container">
				<div id="slider">
					<div class="scontent">';
					
					if ($vizcosh->numbering == NUM_NUMBERS)
						$toc .= '<ol';
					else
						$toc .= '<ul';
						
			$toc .= ' style="font-size:0.8em;">';
	
	$i = 0;
	foreach($chapters as $ch) {
		$i++;
		
		$title = trim(strip_tags($ch->title));

		$numbering = "";
		if (!$ch->hidden) {
			$nch++;
			if ($vizcosh->numbering == NUM_NUMBERS) {
				$title = "$title";
				$numbering = ' value="'.$nch.'"';
			}
		} else {
			if ($vizcosh->numbering == NUM_NUMBERS) {
				$title = "$title";
				$numbering = ' value="'.$nch.'"';
			}
			$title = '<span class="dimmed_text">'.$title.'</span>';
		}
		$prevtitle = $title;

		if (!$ch->hidden)		
			$toc .= '<li'.$numbering.'>';
		else
			$toc .= '<ul style="list-style-position: inside; text-indent: -53px;"><li>';
			
		if ($ch->id == $chapter->id) {
			$toc .= '<strong>'.$title.'</strong>';
			$currtitle = $prevtitle;
		} else {
			$toc .= '<a href="view.php?id='.$cm->id.'&amp;chapterid='.$ch->id.'">'.$title.'</a>';
		}
		
		if ($showteacherfunctions){		
			$toc .=  '&nbsp;&nbsp;';
			if ($i != 1) {
				$toc .=  ' <a title="' . get_string('up') . '" href="move.php?id=' . $cm->id . '&amp;chapterid='.$ch->id.'&amp;up=1&amp;sesskey='.$USER->sesskey.'"><img src="'.$CFG->pixpath.'/t/up.gif" height="11" class="iconsmall" alt="' . get_string('up') . '" /></a>';
			}
			if ($i != count($chapters)) {
				$toc .=  ' <a title="' . get_string('down') . '" href="move.php?id=' . $cm->id . '&amp;chapterid=' . $ch->id . '&amp;up=0&amp;sesskey='.$USER->sesskey.'"><img src="'.$CFG->pixpath.'/t/down.gif" height="11" class="iconsmall" alt="'.get_string('down').'" /></a>';
			}
			$toc .=  ' <a title="'.get_string('edit').'" href="edit.php?id='.$cm->id.'&amp;chapterid='.$ch->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" height="11" class="iconsmall" alt="'.get_string('edit').'" /></a>';
			
			if ($allowedit){
				$toc .=  ' <a title="'.get_string('delete').'" href="delete.php?id='.$cm->id.'&amp;chapterid='.$ch->id.'&amp;sesskey='.$USER->sesskey.'"><img src="'.$CFG->pixpath.'/t/delete.gif" height="11" class="iconsmall" alt="'.get_string('delete').'" /></a>';
				if ($ch->hidden) {
					$toc .= ' <a title="'.get_string('show').'" href="show.php?id='.$cm->id.'&amp;chapterid='.$ch->id.'&amp;sesskey='.$USER->sesskey.'"><img src="'.$CFG->pixpath.'/t/show.gif" height="11" class="iconsmall" alt="'.get_string('show').'" /></a>';
				} else {
					$toc .= ' <a title="'.get_string('hide').'" href="show.php?id='.$cm->id.'&amp;chapterid='.$ch->id.'&amp;sesskey='.$USER->sesskey.'"><img src="'.$CFG->pixpath.'/t/hide.gif" height="11" class="iconsmall" alt="'.get_string('hide').'" /></a>';
				}
			}
			
			$toc .= ' <a title="'.get_string('addafter', 'vizcosh').'" href="edit.php?id='.$cm->id.'&amp;pagenum='.$ch->pagenum.'"><img src="pix/add.gif" height="11" class="iconsmall" alt="'.get_string('addafter', 'vizcosh').'" /></a>';
		}		
		if (!$ch->hidden)				
			$toc .= '</li>';
		else
			$toc .= '</li></ul>';
			
		$first = 0;
	}
		#$toc .= '</div>';
}

if ($vizcosh->numbering == NUM_NUMBERS)
	$toc .= '</ol>';
else
	$toc .= '</ul>';
$toc .= "</div>	</div>
	</div>";
?>
