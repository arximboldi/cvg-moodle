<!--$Id: editorvis.php,v 1.5 2008/02/03 22:32:00 vellaramkalayil Exp $ -->
<!-- Editing Algorithm Visualizations
   Fill and Print form for editing algorithm visualizations
   or
   Save submitted data (new or edited or deleted algorithm visualization) to database:
   1. process user inputs (title, description, topics,author, data file, thumbnail,... )
   2. insert or update this algorithm visualization data to the database
     or
   1. delete the selected algorithm visualization from database
-->

<?PHP

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/uploadlib.php');

if (isset ($_SESSION['temp_edit_form']))
  {
    $temp      = $_SESSION['temp_edit_form'];
    $id        = $temp->id;        // Course Module ID
    $chapterid = $temp->chapterid; // Chapter ID
  }
else
  {
    error (get_string ('session_var_error', 'vizcosh'));
  }

$vizalgoid = required_param('vizalgo', PARAM_INT);
$modus = required_param('modus', PARAM_TEXT);


// =========================================================================
// security checks START - only teachers add visualizations
// =========================================================================

require_login();

// these session variables are set for storing currently editing vizalgo
$_SESSION['editor_vizalgoid'] = $vizalgoid;
$_SESSION['editor_modus'] = $modus;

if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
  error(get_string ('session_var_error', 'vizcosh'));
}

if (!$course = get_record('course', 'id', $cm->course)) {
  error(get_string ('session_var_error', 'vizcosh'));
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:manageactivities', $context);

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
  error('Course module is incorrect');
}

// get algorithm visualization from database
if (isset ($vizalgoid) && $vizalgoid != -1)
  $vizalgo = get_record ('vizcosh_vizalgos', 'id', $vizalgoid);

// used for redirecting if errors occur
$errorurl1 = 'editorvis.php?vizalgo=' . $vizalgoid . '&modus=' . $modus;
$errorurl2 = 'addvis.php?tab=list';

if (isset ($_REQUEST['cancel_submit']))
  redirect ($errorurl2);

// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// Process submitted data
// =========================================================================

// If data submitted (user saves changes or inserts new algorithm
// visualization or deletes an algorithm visualization),
// then process and store.
if (($form = data_submitted ()) && confirm_sesskey ())
  {
    // if "Format Editor"-button was hit --> save current changes and
    // redirect
    if (isset ($_REQUEST['formateditorsubmit']))
      {
	$_SESSION['temp_editorvis_form'] = $form;
	redirect("addformat.php");
	die;
      }

    // Process user inputs ================================================
    // read the user inputs from the submitted form
    // if a required field is missing -> error message
    if (!isset ($form->title) || 
	$form->title == null || 
	!isset ($form->description) ||
	$form->description == null ||
	!isset ($form->author) ||
	$form->author == null ||
	!isset ($form->format) ||
	$form->format == null)
      {
	error(get_string('edit_algo_missing_field', 'vizcosh'), $errorurl1);
      }
    //if form was filled correctly: create/edit $vizalgo object to store the user inputs
    //required fields: title, description, author, date, format
    else
      {
	$vizalgo->title = $form->title;
	$vizalgo->description = $form->description;
	$vizalgo->author = $form->author;
	$vizalgo->date = date("Ymd");
	$vizalgo->format = $form->format;
      }    
    // optional field: topics
    if (isset ($form->topics) && $form->topics != null)
      {
	$vizalgo->topics = $form->topics;
      }
    
    // optional field: thumbnail
    // 1. case: thumbnail was uploaded:
    // if a thumbnail file was uploaded: 
    //   1. save this file to files/tmp directory
    //   2. save file content to $vizalgo
    //   3. delete file from files/tmp directory
    // 2. case: text for link is given:
    // 	 text is saved in $vizalgo->fnthumbnail and $vizalgo->thumbnail is set to "text" 
    // 3. case: if no file was uploaded and no text given:
    //   - if no old thumbnail file or deleted old thumbnail file:
    //     save default thumbnail file (default.gif in pix directory) to $vizalgo
    //   - or if there is an old thumbnail file in $vizalgo:
    //     save this old thumbnail file to $vizalgo
    if(isset ($form->deletethumb) && $form->deletethumb == "thumbnail")
      {
	if (!empty ($_FILES) && !empty ($_FILES['thumbnail']['tmp_name']))
	  {
	    $thumb_um = new upload_manager ("thumbnail", false, false, null,
					    false, 0, true, true, false);
	    if ($thumb_um->preprocess_files ())
	      {
		$vizalgo->fnthumbnail = $_FILES['thumbnail']['name'];
		$vizalgo->thumbnail = addslashes (file_get_contents ($_FILES['thumbnail']['tmp_name']));
	      }
	  }
      }
    
    if (isset($form->deletethumb) && $form->deletethumb == "text")
      {
	$vizalgo->thumbnail = "text";
	$vizalgo->fnthumbnail = $form->text;
      }
    if (isset($form->deletethumb) && $form->deletethumb == "delete")
      {
	$vizalgo->thumbnail = "default";
	$vizalgo->fnthumbnail = 'default.gif';
      }
    if ((isset($form->deletethumb) && $form->deletethumb == "old") ||
	!isset($form->deletethumb))
      {
	if (!isset ($vizalgo->thumbnail) || $vizalgo->thumbnail == null)
	  {
	    $vizalgo->thumbnail = "default";
	    $vizalgo->fnthumbnail = 'default.gif';
	  }
	else if (strcmp($vizalgo->thumbnail, "default") != 0)
	  {
	    $vizalgo->thumbnail = addslashes ($vizalgo->thumnail);
	  }
      }

    // optional field: data
    // if a data file was uploaded: 
    //   1. save this file to files/tmp directory
    //   2. save file content to $vizalgo
    //   3. delete file from files/tmp directory
    if (!empty ($_FILES) && !empty ($_FILES['data']['tmp_name']))
      {
	$data_um = new upload_manager("data", false, false, null, false, 0, true, true, false);
	if ($data_um->preprocess_files ())
	  {
	    $vizalgo->fndata = $_FILES['data']['name'];
	    $vizalgo->data = addslashes (file_get_contents ($_FILES['data']['tmp_name']));
	  }
      }
    //if no file was uploaded: 
    //  - if there is an old data file in $vizalgo:
    //          save this old data file to $vizalgo
    //  - if user wants to delete old data file:
    //          clear data field in $vizalgo
    else
      {
	if (isset ($vizalgo->data) &&
	    $vizalgo->data != null &&
	    !isset ($form->deletedata))
	  {
	    $vizalgo->data = addslashes ($vizalgo->data);
	  }
	else if (isset ($form->deletedata))
	  {
	    $vizalgo->data = "";
	    $vizalgo->fndata = "";
	  }
      }

    // check whether a new uploaded data file has the correct file
    // extension; it must match extension the chosen format defines
    $vizalgoformat = get_record ('vizcosh_vizalgo_formats', 'id', $vizalgo->format);

    if (isset ($vizalgo->fndata) &&
	$vizalgo->fndata != null &&
	$vizalgo->fndata != "" &&
	isset ($vizalgo->data) &&
	$vizalgo->data != null &&
	$vizalgo->data != "")
      {
	if (isset ($vizalgoformat->extension) &&
	    $vizalgoformat->extension != null &&
	    $vizalgoformat->extension != "")
	  {
	    if (!preg_match("/\." . $vizalgoformat->extension . "$/i", $vizalgo->fndata))
	      {
		error(get_string ('edit_viz_wrong_format', 'vizcosh',
				  $vizalgoformat->name .
				  "(*.$vizalgoformat->extension)"), $errorurl1);
	      }
	  }
      }
    
    // save the algorithm visualization to database
    // =================================

    // if user wanted to update existing algorithm visualization
    if ($modus == 'edit' && $vizalgo) {
      $vizalgo->course = $COURSE->id;
      if (!update_record ('vizcosh_vizalgos', $vizalgo))
	{
	  error (get_string ('viz_db_error', 'vizcosh'), $errorurl2);
	}
      else
	{
	  add_to_log($course->id, 'vizcosh', 'update visualization', '', $vizalgo->title);
	  redirect("addvis.php?tab=list");
	}
    } 
    //if user wanted to insert new algorithm visualization
    else if ($modus == 'new' && $vizalgo)
      {
	$vizalgo->course = $COURSE->id;
	if (!$vizalgo->id = insert_record ('vizcosh_vizalgos', $vizalgo))
	  {
	    error (get_string ('viz_db_error', 'vizcosh'), $errorurl2);
	  }
	else
	  {
	    add_to_log ($course->id, 'vizcosh', 'add visualization', '', $vizalgo->title);
	    redirect("addvis.php?tab=list");
	  }
      }
    die;
  }

//if user wanted to delete the algorithm visualization
if ($modus == 'delete' && $vizcosh)
  {
    if (!$vizalgo->id = delete_records ('vizcosh_vizalgos', 'id', $vizalgo->id))
      {
	error(get_string ('viz_db_error', 'vizcosh'), $errorurl2);
      }
    else
      {
	redirect("addvis.php?tab=list");
      }
    die;
  }

// =========================================================================
// Process submitted data END
// =========================================================================

// =========================================================================
// Fill and Print the form (only if no data was yet submitted)
// =========================================================================

// if new algorithm visualization to create: clear all fields in form
if (!isset ($vizalgo))
  {
    $vizalgo->title = '';
    $vizalgo->description = '';
    $vizalgo->author = -1;
    $vizalgo->topics = '';
    $vizalgo->format = -1;
  }

// if user had already made changes: read those changes from the
// session variable "temp_editorvis_form"
// and fill the form with them: NOTE: data and thumbnail changes are
// not restored

if (isset ($_SESSION['temp_editorvis_form']))
  {
    $tempvis = $_SESSION['temp_editorvis_form'];
    $vizalgo->title = $tempvis->title;
    $vizalgo->description = $tempvis->description;
    $vizalgo->author = $tempvis->author;
    $vizalgo->topics = $tempvis->topics;
    if (isset ($vizalgo->format))
      $vizalgo->format = $tempvis->format;
    else
      $vizalgo->format = -1;
    unset($_SESSION['temp_editorvis_form']);
  }

// prepare the page header
$strvizcosh  = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit     = get_string('editchapter', 'vizcosh');
$strlist     = get_string('addvis', 'vizcosh');
$streditor   = get_string('editoralvis', 'vizcosh');
$pageheading = get_string('editoralvis', 'vizcosh');

if ($course->category)
  $navigation =
    '<a href="../../course/view.php?id=' .
    $course->id . '">' . $course->shortname . '</a> ->';
else
  $navigation = '';

// print the page
print_header("$course->shortname: $vizcosh->name", $course->fullname, "<a href=\"index.php?id=$course->id\">$strvizcoshs</a> -> 
<a href=\"view.php?id=$cm->id\">$vizcosh->name</a> -> 
<a href=\"edit.php?id=$cm->id&chapterid=$chapterid\">$stredit</a> ->
<a href=\"addvis.php?tab=list\">$strlist</a> -> 
$streditor", '', '', true, '', '');
print_heading_with_help($pageheading, 'editorvishelp', 'vizcosh');
print_simple_box_start('center', '');
include ('editorvis.html');
print_simple_box_end();
print_footer($course);

// =========================================================================
// Fill and Print the form (only if no data was yet submitted) END
// =========================================================================

?>
