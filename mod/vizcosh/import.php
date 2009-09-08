<?php 
//Todo Remove
#require_once('Z:/Software/Server2Go/htdocs/FirePHPCore/FirePHP.class.php');
#ob_start();
require_once('../../config.php');
require_once('lib.php');

$id         = required_param('id', PARAM_INT);           // Course Module ID
$cancel     = optional_param('cancel', 0, PARAM_BOOL);

// =========================================================================
// security checks START - only teachers edit
// =========================================================================

require_login();

if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
	error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
	error('Course is misconfigured');
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/vizcosh:useredit', $context);

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
	error('Course module is incorrect');
}

//check all variables
unset($id);

// =========================================================================
// security checks END
// =========================================================================

// cancel pressed, go back to vizcosh
if ($cancel) {
	redirect('view.php?id='.$cm->id);
	die;
}

/**
 * splits text into paragraphs (paragraph divider = two <br /> tags)
 */
function emargo_split_paragraphs($text, $OpenOffice, $paragraphsplitter= '<br /><br />') {
	#$text = str_replace('<br /><span class="emargomarker"></span><br />', '<br /><br />', $text); // remove unneccessary markers between br-tags
	if ($OpenOffice){	
		#todo mit regulären ausdrücken		
					#$firephp = FirePHP::getInstance(true);	

#	$firephp->log($text, '$text');	
#		$text = str_replace('<p class=\"Standard\"> </p>', '', $text);
		$text = str_replace('<p class=\"Standard\">', '', $text);
#	$firephp->log($text, '$text');	
		$markedParagraphsArray = explode('</p>', $text);
#	$firephp->log($markedParagraphsArray, '$markedParagraphsArray');	
	}
	else
		$markedParagraphsArray = explode($paragraphsplitter, $text);
	return $markedParagraphsArray;
}

// prepare the page header
$strvizcosh = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$strimport = get_string('import', 'vizcosh');

if ($course->category) {
	$navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
} else {
	$navigation = '';
}

print_header( "$course->shortname: $vizcosh->name", 
	$course->fullname, 
	"$navigation <a href=\"index.php?id=$course->id\">$strvizcoshs</a> -> <a href=\"view.php?id=$cm->id\">$vizcosh->name</a> -> $strimport", 
	'', 
	'', 
	true, 
	'', 
	''
);

/// If data submitted, then process, store and relink.
if (($form = data_submitted()) && (confirm_sesskey())) {
    $form->reference = stripslashes($form->reference);
    if ($form->reference != '') { //null path is root
        $form->reference = vizcosh_prepare_link($form->reference);
        if ($form->reference == '') { //evil characters in $ref!
            error('Invalid character detected in given path!');
        }
    }
    $coursebase = $CFG->dataroot.'/'.$vizcosh->course;
    if ($form->reference == '') {
        $base = $coursebase;
    } else {
        $base = $coursebase.'/'.$form->reference;
    }

    //prepare list of html files in $refs
    $refs = array();
    $htmlpat = '/\.html$|\.htm$/i';
    if (is_dir($base)) { //import whole directory
        $basedir = opendir($base);
        while ($file = readdir($basedir)) {
            $path = $base.'/'.$file;
            if (filetype($path) == 'file' and preg_match($htmlpat, $file)) {
                $refs[] = str_replace($coursebase, '', $path);
            }
        }
        asort($refs);
    } else if (is_file($base)) { //import single file
        $refs[] = '/'.$form->reference;
    } else { //what is it???
        error('Incorrect file/directory specified!');
    }
    
	//import files
    echo '<center>';
    echo '<b>'.get_string('importing', 'vizcosh').':</b>';
    echo '<table cellpadding="2" cellspacing="2" border="1">';
    #vizcosh_check_structure($vizcosh->id);
    foreach($refs as $ref) {
        $chapter = vizcosh_read_chapter($coursebase, $ref);

        if ($chapter) {
            $chapter->title = addslashes($chapter->title);
            $chapter->importsrc = addslashes($chapter->importsrc);
            $chapter->vizcoshid = $vizcosh->id;
            $chapter->pagenum = count_records('vizcosh_chapters', 'vizcoshid', $vizcosh->id)+1;
            $chapter->timecreated = time();
            $chapter->timemodified = time();
            echo "imsrc:".$chapter->importsrc;
			
			$paragraphsArray = emargo_split_paragraphs($chapter->content, ($form->importfrom == "openoffice"));
				
			unset($chapter->content);	
			
            if (!$chapter->id = insert_record('vizcosh_chapters', $chapter)) {
                error('Could not insert chapter');
            }
					
			$i = 1;
			$paragraph = new StdClass;    
			
			if ($paragraphsArray){
				foreach($paragraphsArray as $cparagraph){					

					$paragraph->vizcoshid = $vizcosh->id;
					$paragraph->orderposition = $i;
					$paragraph->chapterid = $chapter->id;
					$paragraph->content = $cparagraph;

					if (!insert_record('vizcosh_paragraphs', $paragraph)) {
						error('Could not insert paragraph');
					}
										
					$i = $i + 1;
				}
			}
			
            add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id='.$cm->id, 'vizcosh '.$vizcosh->id);
            add_to_log($course->id, 'vizcosh', 'update', 'view.php?id='.$cm->id.'&chapterid='.$chapter->id, $vizcosh->id, $cm->id);
        }
    }
    echo '</table><br />';
    #echo '<b>'.get_string('relinking', 'vizcosh').':</b>';
    #echo '<table cellpadding="2" cellspacing="2" border="1">';
    //relink whole vizcosh = all chapters
    #vizcosh_relink($cm->id, $vizcosh->id, $course->id);
    #echo '</table><br />';
    echo '<a href="view.php?id='.$cm->id.'">'.get_string('continue').'</a>';
    echo '</center>';
} else {
/// Otherwise fill and print the form.
    $strdoimport = get_string('doimport', 'vizcosh');
    $strchoose = get_string('choose');
    $pageheading = get_string('importingchapters', 'vizcosh');

    $icon = '<img align="absmiddle" height="16" width="16" src="icon_chapter.gif" />&nbsp;';
    print_heading_with_help($pageheading, 'import', 'vizcosh', $icon);
    print_simple_box_start('center', '');
    ?>
    <form name="theform" method="post" action="import.php">
    <table cellpadding="5" align="center">
    <tr valign="top">
        <td valign="top" align="right">
            <b><?php print_string('fileordir', 'vizcosh') ?>:</b>
        </td>
        <td>
            <?php
              echo '<input id="id_reference" name="reference" size="40" value="" />&nbsp;';
              button_to_popup_window ('/mod/vizcosh/coursefiles.php?choose=id_reference&id='.$course->id,
                                      'coursefiles', $strchoose, 500, 750, $strchoose);
            ?>
			 <br><input type="checkbox" name="importfrom" value="openoffice"> Aus OpenOffice importieren

        </td>
    </tr>
    <tr valign="top">
        <td valign="top" align="right">&nbsp;</td>
        <td><p><?php print_string('importinfo', 'vizcosh') ?></p></td>
    </tr>
    </table>
    <center>
        <input type="submit" value="<?php echo $strdoimport ?>" />
        <input type="submit" name="cancel" value="<?php print_string("cancel") ?>" />
    </center>
        <input type="hidden" name="id" value="<?php p($cm->id) ?>" />
        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" /> 
    </form>

    <?php
    print_simple_box_end();
}

print_footer($course);

?>
