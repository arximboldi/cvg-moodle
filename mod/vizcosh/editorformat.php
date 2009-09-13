<!--$Id: editorformat.php,v 1.5 2008/02/03 22:32:00 vellaramkalayil Exp $ -->
<!-- Editing Algorithm Visualization Format
     Fill and Print form for editing algorithm visualization formats (and load a jnlp template to form)
     or
     Save submitted data (new or edited or deleted algorithm visualization format) to database:
     1. process user inputs (name, author, jnlp_template, extension)
     2. insert or update this algorithm visualization data format to the database
     or
     1. delete the selected algorithm visualization format from database
-->
<?php

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/uploadlib.php');

$formatid = required_param ('formatid', PARAM_INT);
$modus = required_param ('modus', PARAM_TEXT); //new, edit or delete

//=========================================================================
// security checks START - only teachers add visualizations
//=========================================================================

require_login();
     
if (isset ($_SESSION['temp_edit_form']))
  {
    $temp = $_SESSION['temp_edit_form'];
    $id = $temp->id; // Course Module ID
    $chapterid = $temp->chapterid; // Chapter ID
  }
else
  error (get_string ('wrong_parameter', 'vizcosh'));

if (!$cm = get_coursemodule_from_id('vizcosh', $id))
  error (get_string ('wrong_parameter', 'vizcosh'));

if (!$course = get_record('course', 'id', $cm->course))
  error (get_string ('wrong_parameter', 'vizcosh'));

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:manageactivities', $context);
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
  error('Course module is incorrect');

//get algorithm visualization format from database
$format = get_record ('vizcosh_vizalgo_formats', 'id', $formatid);

//used for redirecting if errors occur
$errorurl1 = 'editorformat.php?formatid=' . $formatid . '&modus=' . $modus;
$errorurl2 = 'addformat.php';

//=========================================================================
//security checks END
//=========================================================================

// =========================================================================
// Process submitted data
// =========================================================================

//If data submitted (user saves changes or inserts new algorithm
//visualization or deletes an algorithm visualization), then process
//and store.
if (($form = data_submitted()) && confirm_sesskey())
  {
    //read the user inputs from the submitted form
    //if a required field is missing -> error message
    if ((!isset ($form->name) ||
	 $form->name == null ||
	 !isset ($form->author) ||
	 $form->author == null ||
	 !isset ($form->template) ||
	 $form->template == null) &&
	!isset ($_REQUEST['load']))
      {
	error (get_string ('missing_field', 'vizcosh'), $errorurl1);
      }

    //if form was filled correctly: create/edit $vizalgo object to
    //store the user inputs required fields: name, author, date,
    //jnlp_template
    else
      {
	$format->name = $form->name;
	$format->author = $form->author;
	$format->date = date("Ymd");
	$format->jnlp_template = $form->template;
      }
    
    //optional field: extension
    $format->extension = $form->extension;
    
    //if user wants to load one of the available jnlp templates into
    //the template field in the form
    if (isset ($_REQUEST['load']))
      {
	//load the file from files/visualizers/jnlps and print it to the
	//form
	if (!isset ($jnlp_samples))
	  {
	    $animal_data = file_get_contents ("./files/visualizers/jnlps/animal.jnlp");
	    $jhave_data  = file_get_contents ("./files/visualizers/jnlps/jhave.jnlp");
	    $jawaa_data = file_get_contents ("./files/visualizers/jnlps/jawaa.jnlp");
	    $jnlp_samples = array ("animal" => $animal_data,
				   "jhave"  => $jhave_data,
				   "jawaa"  => $jawaa_data);
	  }
	
	if (isset ($form->template_sample))
	  $format->jnlp_template = $jnlp_samples[$form->template_sample];
    }
    //if edit, new or delete 
    else
      {
	//if user wanted to update existing algorithm visualization
	if ($modus == 'edit' && $format)
	  {
	    /// editing existing vizalgo_format in database
	    if (!update_record ('vizcosh_vizalgo_formats', $format))
	      {
		error (get_string ('db_error', 'vizcosh'), $errorurl2);
	      }
	    else
	      {
		add_to_log($course->id, 'vizcosh', 'update visualization format', '', $format->name);
		redirect ("addformat.php");
	      }
	  }
	//if user wanted to insert new algorithm visualization 
	else if ($modus == 'new')
	  {
	    /// adding new vizalgo_format to database
	    if (!$format->id = insert_record('vizcosh_vizalgo_formats', $format))
	      {
		error (get_string ('db_error', 'vizcosh'), $errorurl2);
	      }
	    else
	      {
		add_to_log($course->id, 'vizcosh', 'add visualization format', '', $format->name);
		redirect ("addformat.php");
	      }
	  }
	die;
      }
  }

//if user wanted to delete the algorithm visualization
if ($modus == 'delete' && $vizcosh)
  {
    /// deleting vizalgo_format from database
    if (!$format->id = delete_records('vizcosh_vizalgo_formats', 'id', $format->id))
      error (get_string ('db_error', 'vizcosh'), $errorurl2);  
    else 
      redirect("addformat.php");
  
    die;
  }

// =========================================================================
// Process submitted data END
// =========================================================================

// =========================================================================
// Fill and Print the form (only if no data was yet submitted)
// =========================================================================

//if new algorithm visualization format to create: clear all fields in form
if (!$format)
  {
    $format->name = '';
    $format->extension = '';
    $format->author = '';
    $format->jnlp_template = '';
  }

///prepare the page header

$strvizcosh = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit = get_string('editchapter', 'vizcosh');
$strlist = get_string('addvis', 'vizcosh');
$streditor = get_string('editoralvis', 'vizcosh');
$strformatadd = get_string('addformat', 'vizcosh');
$strformateditor = get_string('editorformat', 'vizcosh');
$pageheading = get_string('editorformat', 'vizcosh');

if ($course->category)
  $navigation = '<a href="../../course/view.php?id=' . $course->id .
    '">' . $course->shortname . '</a> ->';
else
  $navigation = '';

$editorvislink = $streditor;
if (isset ($_SESSION['editor_vizalgoid']) &&
    isset ($_SESSION['editor_modus']))
  {
    $tempvizalgoid = $_SESSION['editor_vizalgoid'];
    $tempmodus = $_SESSION['editor_modus'];
    $editorvislink = "<a href=\"editorvis.php?vizalgo=" . $tempvizalgoid .
      '&modus=' . $tempmodus . "\">" . $streditor . "</a>";
  }

//print the page
print_header("$course->shortname: $vizcosh->name", $course->fullname,
	     "<a href=\"index.php?id=$course->id\">$strvizcoshs</a> ->". 
	     "<a href=\"view.php?id=$cm->id\">$vizcosh->name</a> -> ".
	     "<a href=\"edit.php?id=$cm->id&chapterid=$chapterid\">$stredit</a> ->".
	     "<a href=\"addvis.php?tab=list\">$strlist</a> ->".
	     "$editorvislink ->".
	     "<a href=\"addformat.php\">$strformatadd</a> ->".
	     $strformateditor, '', '', true, '', '');

print_heading_with_help($pageheading, 'editorformathelp', 'vizcosh');
print_simple_box_start('center', '');

include ('editorformat.html');

print_simple_box_end();
print_footer($course);

// =========================================================================
// Fill and Print the form (only if no data was yet submitted) END
// =========================================================================

?>
