<!--$Id: edit.php,v 2.6 2008/02/03 22:32:00 vellaramkalayil Exp $-->
<!-- Editing VizCoSH Chapters
Fill and Print form for editing chapter
or
Save submitted data (new or edited chapter) to database
or
Redirect to "Add Algorithm Visualization" (addvis.php)
-->
<?PHP

require_once ('../../config.php');
require_once ('lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$orderposition = optional_param('orderposition', 0, PARAM_INT);
$paragraphid = required_param('paragraphid', PARAM_INT);

$sel_vizalgo = optional_param('selected_vizalgo',NULL, PARAM_INT); //ID of the vizalgo which was selected to add to the chapter

// =========================================================================
// security checks START - only teachers edit
// =========================================================================

//Todo checks genau überprüfen

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

$paragraph = get_record('vizcosh_paragraphs', 'id', $paragraphid);

if ($paragraph) {
	//Editing an existing paragraph
    if ($paragraph->vizcoshid != $vizcosh->id) { //chapter id not in this vizcosh!!!!
        error('Paragraph not part of this vizcosh!');
    }
    $orderposition = $paragraph->orderposition;
} else {
    $orderposition = (integer) $orderposition;
}
//used for redirecting if errors occur
$errorurl = 'view.php?id='.$id.'&chapterid='.$chapterid;

//used by edit.html
$usehtmleditor = can_use_html_editor();

unset ($id);
#unset ($chapterid);
// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// Process submitted data
// =========================================================================

/// If data submitted (user saves changes or wants to add algorithm visualization), then process.
if (($form = data_submitted()) && (confirm_sesskey())) {
    //if "Add Algorithm Visualization"-button was hit--> save current changes and redirect
    if (isset ($_REQUEST['addvissubmit'])) {
        //save current form (with changes)
        $_SESSION['temp_edit_form'] = $form;
        //redirect to listing of available algorithm visualizations
        redirect("addvis.php?tab=list");
        die;
    }
    //if "Save"-Button was hit: Save new or edited chapter to database
    //if editing existing chapter
    if ($paragraph) {
        $paragraph->content = $form->content;
        #$paragraph->timemodified = time();

        //save to database
        if (!update_record('vizcosh_paragraphs', $paragraph)) {
            error('Could not update this chapter.', $errorurl);
        }

        add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
        add_to_log($course->id, 'vizcosh', 'update', 'view.php?id=' . $cm->id . '&chapterid=' . $paragraph->id, $vizcosh->id, $cm->id);
    }
	
    //if adding new chapter 
    else {
        $paragraph->vizcoshid = $vizcosh->id;
        $paragraph->chapterid = $chapterid;
        $paragraph->orderposition = $form->orderposition + 1; //place after given orderposition, lets hope it is a number
        $paragraph->content = $form->content;
        #$chapter->timecreated = time();
        #$chapter->timemodified = $chapter->timecreated;
		$paragraphs = get_records_select('vizcosh_paragraphs', "vizcoshid = $vizcosh->id AND chapterid = $chapterid", 'chapterid, orderposition', 'id, orderposition');

		#Renumber following paragraphs
		if ($paragraphs) {
            foreach ($paragraphs as $par) {
                if ($par->orderposition > $orderposition) {
                    $par->orderposition = $par->orderposition + 1;
					print "<br><br>";
                    if (!update_record('vizcosh_paragraphs', $par)) {
                        error('Could not update this paragaph',$errorurl);
                    }
                }
            }
        }
        //save to database
        if (!$paragraph->id = insert_record('vizcosh_paragraphs', $paragraph)) {
            error('Could not insert a new paragraph',$errorurl);
        }
        add_to_log($course->id, 'course', 'update mod', '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
    }
    #vizcosh_check_structure($vizcosh->id);
    //show new or edited paragraph
    redirect("view.php?id=$cm->id&chapterid=$paragraph->chapterid#$paragraph->id");
    die;
}
// =========================================================================
// Process submitted data END
// =========================================================================

// =========================================================================
// Fill and Print the form (only if no data was yet submitted)
// =========================================================================

//Filling the form fields---------------------------------------------------

//clear all the fields in the form (for new paragraphs)
if (!$paragraph) {
    $paragraph->id = $paragraphid;
    $paragraph->content = '';
    $paragraph->orderposition = $orderposition;
    $paragraph->chapterid = $chapterid;
}

//if user had already made changes: read those changes from the session variable "temp_edit_form"
//and fill the form with them
if (isset ($_SESSION['temp_edit_form'])) {
    $temp = $_SESSION['temp_edit_form'];
	
    //if a vizalgo to add was selected
	//Todo
    if(isset($sel_vizalgo)){
        //read the vizalgo from database
        $vizalgo = get_record('vizcosh_vizalgos', 'id', $sel_vizalgo);
        //if vizalgo contains its own thumbnail, read that picture from database and insert it as a link (to jnlp-file)
        if (isset($vizalgo->thumbnail) && $vizalgo->thumbnail!=null) {
          if (strcmp($vizalgo->thumbnail,"default") != 0) {
                $temp->content = stripslashes($temp->content). "<a href='createjnlp.php?id=" . $cm->id . "&selected_vizalgo=".$sel_vizalgo."' target='_jnlp'><img src='generatethumb.php?vizalgo=".$sel_vizalgo."'></a>";
} else 
          // GR below
// was: if (strcmp($vizalgo->thumbnail, "text") == 0) {
          if (strlen($vizalgo->thumbnail) < 10) {
            if (isset($vizalgo->fnthumbnail)) {
               $temp->content = stripslashes($temp->content). "<a href='createjnlp.php?id=" . $cm->id . "&selected_vizalgo=".$sel_vizalgo."' target='_jnlp'>" .$vizalgo->fnthumbnail . "</a>";
            } else {
	       $temp->content = stripslashes($temp->content). "<a href='createjnlp.php?id=" . $cm->id . "&selected_vizalgo=".$sel_vizalgo."' target='_jnlp'>" .$vizalgo->description . "</a>";
	    }
          }
        //otherwise use the default picture to create the link (to jnlp-file)
          else {
            $temp->content = stripslashes($temp->content). "<a href='createjnlp.php?id=" . $cm->id . "&selected_vizalgo=".$sel_vizalgo."' target='_jnlp'><img src='./pix/default.gif'></a>";
        }
      } 
  }
    //need to remove slashes before storing to database
	//Todo: prüfen
    $paragraph->content = stripslashes($temp->content);
    //delete session variable "temp_edit_form"
    unset($_SESSION['temp_edit_form']);
}

//Printing the form---------------------------------------------------------

if ($course->category) {
    $navigation = '<a href="../../course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> ->';
} else {
    $navigation = '';
}
//needed for header

$strvizcosh = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit = get_string('editchapter','vizcosh');
$pageheading = get_string('editingchapter', 'vizcosh');
//print header bar
print_header(   "$course->shortname: $vizcosh->name", 
                $course->fullname, 
                "$navigation <a href=\"index.php?id=$course->id\">$strvizcoshs</a> -> <a href=\"view.php?id=$cm->id\">$vizcosh->name</a> -> $stredit",
                '', 
                '<style type="text/css">@import url('.$CFG->wwwroot.'/mod/vizcosh/vizcosh_theme.css);</style>', 
                true, 
                '', 
                '');

$icon = '<img align="absmiddle" height="16" width="16" src="icon_chapter.gif" />&nbsp;';
print_heading_with_help($pageheading, 'edit', 'vizcosh', $icon);
print_simple_box_start('center', '');
include ('editparagraph.html');
print_simple_box_end();

if ($usehtmleditor) {
    use_html_editor();
}
print_footer($course);
// =========================================================================
// Fill and Print the form END
// =========================================================================
?>
