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

$id            = required_param ('id', PARAM_INT); // Course Module ID
$chapterid     = optional_param ('chapterid', 0, PARAM_INT); // Chapter ID
$orderposition = optional_param ('orderposition', 0, PARAM_INT);
$paragraphid   = required_param ('paragraphid', PARAM_INT);
$sel_vizalgo   = optional_param ('selected_vizalgo',NULL, PARAM_INT);
//ID of the vizalgo which was selected to add to the chapter

// =========================================================================
// security checks START - only teachers edit
// =========================================================================

//Todo checks genau überprüfen

require_login();
if (!$cm = get_coursemodule_from_id('vizcosh', $id))
  error (get_string ('wrong_parameter', 'vizcosh'));

if (!$course = get_record('course', 'id', $cm->course))
  error (get_string ('wrong_parameter', 'vizcosh'));

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/vizcosh:useredit', $context);
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
  error (get_string ('wrong_parameter', 'vizcosh'));

$paragraph = get_record('vizcosh_paragraphs', 'id', $paragraphid);

if ($paragraph)
  {
    //Editing an existing paragraph
    if ($paragraph->vizcoshid != $vizcosh->id)
      error (get_string ('wrong_parameter', 'vizcosh'));
    $orderposition = $paragraph->orderposition;
  }
else
  {
    $orderposition = (integer) $orderposition;
  }

//used for redirecting if errors occur
$errorurl = 'view.php?id='.$id.'&chapterid='.$chapterid;

//used by edit.html
$usehtmleditor = can_use_html_editor();

// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// Process submitted data
// =========================================================================

/// If data submitted (user saves changes or wants to add algorithm
/// visualization), then process.
if (($form = data_submitted ()) && confirm_sesskey ())
  {
    //if "Add Algorithm Visualization"-button was hit--> save current
    //changes and redirect
    if (isset ($_REQUEST['addvissubmit']))
      {
	$_SESSION['temp_edit_form'] = $form;
	redirect ("addvis.php?tab=list");
	die;
      }
    else if (isset ($_REQUEST['addxaalsubmit']))
      {
	$paragraph->content = $form->content . "<br/>[xaal $form->xaalxml]";
      }
    else
      {  
	$full_contents =
	  isset ($form->break_paragraphs) ?
	  vizcosh_split_paragraphs ($form->content) :
	  array ($form->content);
	
	//if "Save"-Button was hit: Save new or edited chapter to database
	//if editing existing paragraph we save the first paragraph on it
	if ($paragraph)
	  {
	    // Deleting a paragraph is a too dangerous operation (destroys
	    // comments) so we'd rather require the user to do it explicitly
	    if (count ($full_contents) > 0)
	      $paragraph->content = array_shift ($full_contents);
	    else
	      $paragraph->content = '';
	    
	    #$paragraph->timemodified = time();
	    
	    //save to database
	    if (!update_record ('vizcosh_paragraphs', $paragraph))
	      error (get_string ('db_error', 'vizcosh'), $errorurl);
	    
	    add_to_log($course->id, 'course', 'update mod',
		       '../mod/vizcosh/view.php?id=' . $cm->id,
		       'vizcosh ' . $vizcosh->id);
	    add_to_log($course->id, 'vizcosh', 'update',
		       'view.php?id=' . $cm->id . '&chapterid=' . $paragraph->id,
		       $vizcosh->id, $cm->id);
	  }
	
	//if there are remaining paragraphs we add them after this one
	// Renumber following paragraphs
	if (count ($full_contents) > 0)
	  {
	    $para_count = count ($full_contents);
	    $new_orderposition = $orderposition + 1;
	    
	    $paragraphs = get_records_select ('vizcosh_paragraphs',
					      "vizcoshid = $vizcosh->id AND ".
					      "chapterid = $chapterid AND ".
					      "orderposition >= $new_orderposition",
					      'chapterid, orderposition',
					      'id, orderposition');
	    
	    if ($paragraphs)
	      {
		foreach ($paragraphs as $par)
		  {
		    $par->orderposition = $par->orderposition + $para_count;
		    if (!update_record ('vizcosh_paragraphs', $par))
		      error (get_string ('db_error', 'vicosh'), $errorurl);
		  }
	      }
	    else
	      die;
	  }
	
	$order_delta = 0;
	foreach ($full_contents as $par_content)
	  {
	    $order_delta ++;
	    
	    $paragraph->vizcoshid = $vizcosh->id;
	    $paragraph->chapterid = $chapterid;
	    $paragraph->orderposition = $form->orderposition + $order_delta; 
	    $paragraph->content = $par_content;
	    
	    #$chapter->timecreated = time();
	    #$chapter->timemodified = $chapter->timecreated;
	    
	    //save to database
	    if (!$paragraph->id = insert_record('vizcosh_paragraphs', $paragraph))
	      error (get_string ('db_error', 'vizcosh'), $errorurl);
	    
	    add_to_log ($course->id, 'course', 'update mod',
			'../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
	  }

	#vizcosh_check_structure($vizcosh->id);
	//show new or edited paragraph
	redirect ("view.php?id=$cm->id&chapterid=$paragraph->chapterid#$paragraph->id");
	die;
      }
  }

// =========================================================================
// Process submitted data END
// =========================================================================

// =========================================================================
// Fill and Print the form (only if no data was yet submitted)
// =========================================================================

// Filling the form fields ---------------------------------------------------

//clear all the fields in the form (for new paragraphs)
if (!$paragraph)
  {
    $paragraph->id = $paragraphid;
    $paragraph->content = '';
    $paragraph->orderposition = $orderposition;
    $paragraph->chapterid = $chapterid;
  }

//if user had already made changes: read those changes from the
//session variable "temp_edit_form" and fill the form with them
if (isset ($_SESSION['temp_edit_form']))
  {
    $temp = $_SESSION['temp_edit_form'];
    
    //if a vizalgo to add was selected
    //Todo
    if (isset($sel_vizalgo) && empty ($_REQUEST['cancel_submit']))
      {
	//read the vizalgo from database
	$vizalgo = get_record ('vizcosh_vizalgos', 'id', $sel_vizalgo);
	
	//if vizalgo contains its own thumbnail, read that picture from
	//database and insert it as a link (to jnlp-file)
	if (isset ($vizalgo->thumbnail) && $vizalgo->thumbnail != null)
	  {
	    $thumbnail =
	      strcmp($vizalgo->thumbnail, "text") == 0 ? 
	      (isset ($vizalgo->fnthumbnail) ?
	       $vizalgo->fnthumbnail :
	       $vizalgo->description) :
	      "<img src='dl_thumb.php?id=$sel_vizalgo'>";

	    $temp->content = stripslashes ($temp->content) .
	      "<a href='dl_jnlp.php?id=$sel_vizalgo'>" .
	      $thumbnail .
	      "</a>";
	  }
      }
    
    //need to remove slashes before storing to database
    //Todo: prüfen
    $paragraph->content = stripslashes ($temp->content);
    
    //delete session variable "temp_edit_form"
    unset ($_SESSION['temp_edit_form']);
  }

// Printing the form ---------------------------------------------------------

if ($course->category)
  $navigation = '<a href="../../course/view.php?id=' . $course->id . '">' .
    $course->shortname . '</a> ->';
else
  $navigation = '';

//needed for header
$strvizcosh  = get_string ('modulename', 'vizcosh');
$strvizcoshs = get_string ('modulenameplural', 'vizcosh');
$stredit     = get_string ('editchapter','vizcosh');
$pageheading = get_string ('editingchapter', 'vizcosh');

// TODO: parametrize
vizcosh_print_jsxaal_header ();

//print header bar
print_header ("$course->shortname: $vizcosh->name", 
	      $course->fullname, 
	      "$navigation <a href=\"index.php?id=$course->id\">$strvizcoshs</a> ".
	      "-> <a href=\"view.php?id=$cm->id\">$vizcosh->name</a> ".
	      "-> $stredit",
	      '', '', true, '', '');

$icon = '<img align="absmiddle" height="16" width="16" src="icon_chapter.gif" />&nbsp;';
print_heading_with_help($pageheading, 'edit', 'vizcosh', $icon);
print_simple_box_start('center', '');

include ('editparagraph.html');

print_simple_box_end();
if ($usehtmleditor)
  use_html_editor();
print_footer($course);

// =========================================================================
// Fill and Print the form END
// =========================================================================

?>
