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
$pagenum = optional_param('pagenum', 0, PARAM_INT);

$sel_vizalgo = optional_param('selected_vizalgo',NULL, PARAM_INT);
//ID of the vizalgo which was selected to add to the chapter

// =========================================================================
// security checks START - only teachers edit
// =========================================================================

require_login();
if (!$cm = get_coursemodule_from_id('vizcosh', $id))
  error(get_string ('wrong_parameter', 'vizcosh'));

if (!$course = get_record('course', 'id', $cm->course))
  error(get_string ('wrong_parameter', 'vizcosh'));

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/vizcosh:useredit', $context);
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
  error(get_string ('wrong_parameter', 'vizcosh'));

$chapter = get_record('vizcosh_chapters', 'id', $chapterid);

if ($chapter)
  {
    if ($chapter->vizcoshid != $vizcosh->id)
      error(get_string ('wrong_parameter', 'vizcosh'));
    $pagenum = $chapter->pagenum;
  }
else
  $pagenum = (integer) $pagenum;

//used for redirecting if errors occur
$errorurl = 'view.php?id='.$id.'&chapterid='.$chapterid;

//used by edit.html
$usehtmleditor = can_use_html_editor();

unset ($id);
unset ($chapterid);

// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// Process submitted data
// =========================================================================

/// If data submitted (user saves changes or wants to add algorithm
/// visualization), then process.
if (($form = data_submitted()) && confirm_sesskey())
  {
    //if "Add Algorithm Visualization"-button was hit--> save current changes and redirect
    if (isset ($_REQUEST['addvissubmit']))
      {
	$_SESSION['temp_edit_form'] = $form;
	redirect("addvis.php?tab=list");
	die;
      }
    
    //if "Save"-Button was hit: Save new or edited chapter to database
    //if editing existing chapter
    if ($chapter)
      {
	$chapter->title = $form->title;
	$chapter->timemodified = time();
	$chapter->importsrc = addslashes($chapter->importsrc); //use already stored importsrc
	//save to database
	if (!update_record('vizcosh_chapters', $chapter)) 
	  error (get_string ('db_error', 'vizcosh'), $errorurl);
	
	add_to_log($course->id, 'course', 'update mod',
		   '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
	add_to_log($course->id, 'vizcosh', 'update',
		   'view.php?id=' . $cm->id . '&chapterid=' . $chapter->id,
		   $vizcosh->id, $cm->id);
      }
    //if adding new chapter 
    else
      {
	$chapter->vizcoshid = $vizcosh->id;
	$chapter->pagenum = $form->pagenum + 1;
	//place after given pagenum, lets hope it is a number
	$chapter->title = $form->title;
	$chapter->hidden = 0;
	$chapter->timecreated = time();
	$chapter->timemodified = $chapter->timecreated;
	$chapter->importsrc = '';
	$chapters = get_records ('vizcosh_chapters',
				 'vizcoshid', $vizcosh->id,
				 'pagenum', 'id, pagenum');
	if ($chapters)
	  {
	    foreach ($chapters as $ch)
	      {
		if ($ch->pagenum > $pagenum)
		  {
		    $ch->pagenum = $ch->pagenum + 1;
		    if (!update_record('vizcosh_chapters', $ch))
		      error (get_string ('db_error', 'vizcosh'), $errorurl);
		  }
	      }
	  }
	
	//save to database
	if (!$chapter->id = insert_record('vizcosh_chapters', $chapter))
	  error(get_string ('db_error', 'vizcosh'), $errorurl);
	
	add_to_log($course->id, 'course', 'update mod',
		   '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
	add_to_log($course->id, 'vizcosh', 'update',
		   'view.php?id=' . $cm->id . '&chapterid=' . $chapter->id,
		   $vizcosh->id, $cm->id);
      }
    
    #vizcosh_check_structure($vizcosh->id);
    //show new or edited chapter
    redirect("view.php?id=$cm->id&chapterid=$chapter->id");
    die;
  }

// =========================================================================
// Process submitted data END
// =========================================================================

// =========================================================================
// Fill and Print the form (only if no data was yet submitted)
// =========================================================================

//Filling the form fields---------------------------------------------------

//clear all the fields in the form
if (!$chapter)
  {
    $chapter->id = -1;
    $chapter->title = '';
    $chapter->pagenum = $pagenum;
  }

//if user had already made changes: read those changes from the session variable "temp_edit_form"
//and fill the form with them
if (isset ($_SESSION['temp_edit_form']))
  {
    $temp = $_SESSION['temp_edit_form'];
    if ($temp->title)
      $chapter->title = $temp->title;
    else
      $chapter->title = '';
    
  //if a vizalgo to add was selected
  /**
     if(isset($sel_vizalgo)){
     //read the vizalgo from database
     $vizalgo = get_record('vizcosh_vizalgos', 'id', $sel_vizalgo);
     //if vizalgo contains its own thumbnail, read that picture from database and insert it as a link (to jnlp-file)
     if(isset($vizalgo->thumbnail) && $vizalgo->thumbnail!=null && strcmp($vizalgo->thumbnail,"default")!=0){
     $temp->content = stripslashes($temp->content). "<a href='dl_jnlp.php?id=" . $cm->id . "&selected_vizalgo=".$sel_vizalgo."' target='_jnlp'><img src='dl_thumb.php?vizalgo=".$sel_vizalgo."'></a>";
     }
     //otherwise use the default picture to create the link (to jnlp-file)
     else{
     $temp->content = stripslashes($temp->content). "<a href='dl_jnlp.php?id=" . $cm->id . "&selected_vizalgo=".$sel_vizalgo."' target='_jnlp'><img src='./pix/default.gif'></a>";
     }
     }
  */
  //delete session variable "temp_edit_form"
    unset($_SESSION['temp_edit_form']);
}

//Printing the form---------------------------------------------------------

if ($course->category)
  {
    $navigation =
      '<a href="../../course/view.php?id=' . $course->id . '">'
      . $course->shortname . '</a> ->';
  }
else
  {
    $navigation = '';
  }

//needed for header
$strvizcosh = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit = get_string('editchapter','vizcosh');
$pageheading = get_string('editingchapter', 'vizcosh');
//print header bar
print_header ("$course->shortname: $vizcosh->name", 
	      $course->fullname, 
	      "$navigation <a href=\"index.php?id=$course->id\">$strvizcoshs</a>".
	      " -> <a href=\"view.php?id=$cm->id\">$vizcosh->name</a> -> $stredit",
	      '', '', true, '', '');

$icon = '<img align="absmiddle" height="16" width="16" src="icon_chapter.gif" />&nbsp;';
print_heading_with_help($pageheading, 'edit', 'vizcosh', $icon);
print_simple_box_start('center', '');
include ('edit.html');
print_simple_box_end();

print_footer($course);

// =========================================================================
// Fill and Print the form END
// =========================================================================
?>
