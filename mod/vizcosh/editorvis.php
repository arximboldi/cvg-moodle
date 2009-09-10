<!--$Id: editorvis.php,v 1.5 2008/02/03 22:32:00 vellaramkalayil Exp $ -->
<!-- Editing Algorithm Visualizations
     Fill and Print form for editing algorithm visualizations
     or
     Save submitted data (new or edited or deleted algorithm visualization) to database:
     1. process user inputs (title, description, topics,author, data file, thumbnail,... )
     2. create jnlp file
     3. insert or update this algorithm visualization data to the database
     or
     1. delete the selected algorithm visualization from database
-->

<?PHP

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/uploadlib.php');

if (isset ($_SESSION['temp_edit_form'])) {
  $temp = $_SESSION['temp_edit_form'];
  $id = $temp->id; // Course Module ID
  $chapterid = $temp->chapterid; // Chapter ID
} else {
  error('Session variable error.');
}
$vizalgoid = required_param('vizalgo', PARAM_INT);
// algorithm visualization ID (vizalgo selected for updating (deleting or if inserting =-1)
$modus = required_param('modus', PARAM_TEXT);
// new or edit or delete

// =========================================================================
// security checks START - only teachers add visualizations
// =========================================================================
require_login();
//these session variables are set for storing currently editing vizalgo
$_SESSION['editor_vizalgoid'] = $vizalgoid;
$_SESSION['editor_modus'] = $modus;
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
  error('Course Module ID was incorrect');
}
if (!$course = get_record('course', 'id', $cm->course)) {
  error('Course is misconfigured');
}
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:manageactivities', $context);
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
  error('Course module is incorrect');
}
//get algorithm visualization from database
if (isset ($vizalgoid) && $vizalgoid != -1) {
  $vizalgo = get_record('vizcosh_vizalgos', 'id', $vizalgoid);
}
//used for redirecting if errors occur
$errorurl1 = 'editorvis.php?vizalgo=' . $vizalgoid . '&modus=' . $modus;
$errorurl2 = 'addvis.php?tab=list';

// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// Process submitted data
// =========================================================================

/// If data submitted (user saves changes or inserts new algorithm visualization or deletes an algorithm visualization), 
//then process and store.
if (($form = data_submitted()) && (confirm_sesskey())) {
  //if "Format Editor"-button was hit--> save current changes and redirect
  if (isset ($_REQUEST['formateditorsubmit'])) {
    //save current form (with changes)
    $_SESSION['temp_editorvis_form'] = $form;
    //redirect to listing of available algorithm visualizations
    redirect("addformat.php");
    die;
  }
  // Process user inputs =====================================================
  //read the user inputs from the submitted form
  //if a required field is missing -> error message
  if (!isset ($form->title) ||
      $form->title == null ||
      !isset ($form->description)
      || $form->description == null ||
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
  //optional field: topics
  if (isset ($form->topics) && $form->topics != null)
    {
      $vizalgo->topics = $form->topics;
    }
  
  //optional field: thumbnail
  //1. case: thumbnail was uploaded:
  //if a thumbnail file was uploaded: 
  //  1. save this file to files/tmp directory
  //  2. save file content to $vizalgo
  //  3. delete file from files/tmp directory
  //2. case: text for link is given:
  //	text is saved in $vizalgo->fnthumbnail and $vizalgo->thumbnail is set to "text" 
  //3. case: if no file was uploaded and no text given:
  //  - if no old thumbnail file or deleted old thumbnail file:
  //          save default thumbnail file (default.gif in pix directory) to $vizalgo
  //  or
  //  - if there is an old thumbnail file in $vizalgo:
  //          save this old thumbnail file to $vizalgo
  if(isset($form->deletethumb) && $form->deletethumb == "thumbnail")
    {
      if (!empty ($_FILES) && !empty ($_FILES['thumbnail']['tmp_name']))
	{
	  $thumb_um = new upload_manager ("thumbnail", false, false, null,
					  false, 0, true, true, false);
	  if ($thumb_um->preprocess_files())
	    {
	      move_uploaded_file ($_FILES['thumbnail']['tmp_name'], "./files/tmp/" .
				  $_FILES['thumbnail']['name']);
	      $vizalgo->fnthumbnail = $_FILES['thumbnail']['name'];
	      chmod("./files/tmp/" . $vizalgo->fnthumbnail, 0644);
	      $thumb_link = fopen("./files/tmp/" . $vizalgo->fnthumbnail, "rb");
	      $thumb_size = $_FILES['thumbnail']['size'];
	      $thumb_data = addslashes(fread($thumb_link, $thumb_size)); 
	      $vizalgo->thumbnail = $thumb_data;
	      fclose($thumb_link);
	      unlink("./files/tmp/" . $vizalgo->fnthumbnail);
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
      if (!isset ($vizalgo->thumbnail) || $vizalgo->thumbnail == null) {
	$vizalgo->thumbnail = "default";
	$vizalgo->fnthumbnail = 'default.gif';
      } else {
	if (strcmp($vizalgo->thumbnail, "default") != 0) {
	  $thumb_dec = $vizalgo->thumbnail;
	  $thumb_temp = fopen("./files/tmp/" . $vizalgo->fnthumbnail, "wb");
	  fwrite($thumb_temp, $thumb_dec);
	  fclose($thumb_temp);
	  chmod("./files/tmp/" . $vizalgo->fnthumbnail, 0644);
	  $thumb_link = fopen("./files/tmp/" . $vizalgo->fnthumbnail, "rb");
	  $thumb_size = filesize("./files/tmp/" . $vizalgo->fnthumbnail);
	  $thumb_data = addslashes(fread($thumb_link, $thumb_size));
	  $vizalgo->thumbnail = $thumb_data;
	  fclose($thumb_link);
	  unlink("./files/tmp/" . $vizalgo->fnthumbnail);
	}
      }
    }

  
  //optional field: data
  //if a data file was uploaded: 
  //  1. save this file to files/tmp directory
  //  2. save file content to $vizalgo
  //  3. delete file from files/tmp directory
  if (!empty ($_FILES) && !empty ($_FILES['data']['tmp_name'])) {
    $data_um = new upload_manager("data", false, false, null, false, 0, true, true, false);
    if ($data_um->preprocess_files()) {
      move_uploaded_file($_FILES['data']['tmp_name'], "./files/tmp/" . $_FILES['data']['name']);
      $vizalgo->fndata = $_FILES['data']['name'];
      chmod("./files/tmp/" . $vizalgo->fndata, 0644);
      $data_link = fopen("./files/tmp/" . $vizalgo->fndata, "rb");
      $data_size = $_FILES['data']['size'];
      $data_data = addslashes(fread($data_link, $data_size));
      $vizalgo->data = $data_data;
      fclose($data_link);
      unlink("./files/tmp/" . $vizalgo->fndata);
    }
  }
  //if no file was uploaded: 
  //  - if there is an old data file in $vizalgo:
  //          save this old data file to $vizalgo
  //  - if user wants to delete old data file:
  //          clear data field in $vizalgo
  else {
    if (isset ($vizalgo->data) && $vizalgo->data != null && !isset ($form->deletedata)) {
      $data_dec = $vizalgo->data;
      $data_temp = fopen("./files/tmp/" . $vizalgo->fndata, "wb");
      fwrite($data_temp, $data_dec);
      fclose($data_temp);
      chmod("./files/tmp/" . $vizalgo->fndata, 0644);
      $data_link = fopen("./files/tmp/" . $vizalgo->fndata, "rb");
      $data_size = filesize("./files/tmp/" . $vizalgo->fndata);
      $data_data = addslashes(fread($data_link, $data_size));
      $vizalgo->data = $data_data;
      fclose($data_link);
      unlink("./files/tmp/" . $vizalgo->fndata);
    } else {
      if (isset ($form->deletedata)) {
	$vizalgo->data = "";
	$vizalgo->fndata = "";
      }
    }
  }

  //check whether a newl uploaded data file has the correct file
  //extension; it must match extension the chosen format defines
  $vizalgoformat = get_record('vizcosh_vizalgo_formats', 'id', $vizalgo->format);
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
				"(*.$vizalgoformat->extension)"),
		    $errorurl1);
	    }
	}
    }
  
  // create jnlp-file used to start algorithm visualization
  // ============================
  
  //replace the variables in jnlp_template of the chosen format with
  //the appropriate values
  $jnlp_text = $vizalgoformat->jnlp_template;
  //jnlp-file will be stored in the temporary directory of the user
  //who starts the visualization, so this variable ist just replaced
  //by another variable which will be replace just before starting the
  //jnlp
  $jnlp_text = str_replace("<JNLP-PATH>", "<TMP-PATH>", $jnlp_text);
  //this defines the path to the jars used (animal, jhave and jawaa)
  $jnlp_text = str_replace("<JAR-PATH>",
			   $CFG->wwwroot . "/mod/vizcosh/files/visualizers/jars",
			   $jnlp_text);
  //if algorithm visualization also needs a data file, this will also
  //be stored in the temporary directory of the user
  if (strpos($jnlp_text, "<DATA-PATHFILENAME>") != false) {
    if (isset ($vizalgo->fndata) &&
	$vizalgo->fndata != null &&
	$vizalgo->fndata != "")
      {
	$jnlp_text = str_replace("<DATA-PATHFILENAME>",
				 "<TMP-PATH>" . $vizalgo->fndata,
				 $jnlp_text);
      }
    else
      {
	error(get_string ('edit_viz_data_file_for_format', 'vizcosh'), $errorurl1);
      }
  }
  
  //specifies of which type the data file must be
  $jnlp_text = str_replace("<DATA-TYPE>", $vizalgoformat->name, $jnlp_text);
  
  //save the newly created jnlp to files/tmp-> store it to $vizalgo -> delete it from files/tmp
  $jnlp_temp = fopen("./files/tmp/" . "tmpjnlp.jnlp", "w");
  fwrite($jnlp_temp, $jnlp_text);
  fclose($jnlp_temp);
  $jnlp_link = fopen("./files/tmp/" . "tmpjnlp.jnlp", "rb");
  $jnlp_size = filesize("./files/tmp/" . "tmpjnlp.jnlp");
  $jnlp_data = addslashes(fread($jnlp_link, $jnlp_size));
  $vizalgo->jnlp = $jnlp_data;
  fclose($jnlp_link);
  unlink("./files/tmp/" . "tmpjnlp.jnlp");

  //save the algorithm visualization to database
  //=================================

  //if user wanted to update existing algorithm visualization
  if ($modus == 'edit' && $vizalgo) {
    /// store vizalgo in database
    if (!update_record('vizcosh_vizalgos', $vizalgo)) {
      error('Could not update this algorithm visualization.', $errorurl2);
    } else {
      add_to_log($course->id, 'vizcosh', 'update visualization', '', $vizalgo->title);
      redirect("addvis.php?tab=list");
    }
  } 
  //if user wanted to insert new algorithm visualization
  else
    if ($modus == 'new') {
      /// inserting vizalgo to database
      if (!$vizalgo->id = insert_record('vizcosh_vizalgos', $vizalgo)) {
	error('Could not insert a new algorithm visualization.', $errorurl2);
      } else {
	add_to_log($course->id, 'vizcosh', 'add visualization', '', $vizalgo->title);
	redirect("addvis.php?tab=list");
      }
    }
  die;
}
//if user wanted to delete the algorithm visualization
if (($modus == 'delete') && ($vizcosh)) {
  /// deleting vizalgo from database
  if (!$vizalgo->id = delete_records('vizcosh_vizalgos', 'id', $vizalgo->id)) {
    error('Could not delete this algorithm visualization.', $errorurl2);
  } else {
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

//if new algorithm visualization to create: clear all fields in form
if (!isset ($vizalgo)) {
  $vizalgo->title = '';
  $vizalgo->description = '';
  $vizalgo->author = -1;
  $vizalgo->topics = '';
  $vizalgo->format = -1;
}
//if user had already made changes: read those changes from the session variable "temp_editorvis_form"
//and fill the form with them: NOTE: data and thumbnail changes are not restored
if (isset ($_SESSION['temp_editorvis_form'])) {
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

///prepare the page header
$strvizcosh = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit = get_string('editchapter', 'vizcosh');
$strlist = get_string('addvis', 'vizcosh');
$streditor = get_string('editoralvis', 'vizcosh');
$pageheading = get_string('editoralvis', 'vizcosh');
if ($course->category) {
  $navigation = '<a href="../../course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> ->';
} else {
  $navigation = '';
}

//print the page
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
