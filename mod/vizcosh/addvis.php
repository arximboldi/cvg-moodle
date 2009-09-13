<!--$Id: addvis.php,v 2.6 2008/02/03 22:32:00 vellaramkalayil Exp $ -->
<!-- List/Search Algorithm Visualizations from/in DB
     - Search the database for matching vizalgos
     - Fill a table listing the results
     - Print this table
-->

<?PHP

     require_once ('../../config.php');
require_once ('lib.php');

$tab = required_param('tab', PARAM_TEXT); // selected tab (list or search)

//the following parameters define the search query a user submitted
//(input-fields in search-tab (addvis.html))
$search_title  = optional_param('search_title', NULL, PARAM_TEXT);
$search_desc   = optional_param('search_description', NULL, PARAM_TEXT);
$search_auth   = optional_param('search_author', NULL, PARAM_TEXT);
$search_topics = optional_param('search_topics', NULL, PARAM_TEXT);
$search_sort   = optional_param('search_sort', NULL, PARAM_TEXT);

// =========================================================================
// security checks START - only teachers add visualizations
// =========================================================================

require_login();

//use session variable "temp_edit_form" which should have been set
//from the chapter editing page (editparagraph.php)
if (isset ($_SESSION['temp_edit_form']))
  {
    $temp = $_SESSION['temp_edit_form'];
    $id = $temp->id; // Course Module ID
    $chapterid = $temp->chapterid; // Chapter ID
    $paragraphid = $temp->paragraphid;
    $orderposition = $temp->orderposition;    
  }
else
  {
    error (get_string ('wrong_parameter', 'vizcosh'));
  }

if (!$cm = get_coursemodule_from_id('vizcosh', $id))
  error (get_string ('wrong_parameter', 'vizcosh'));

if (!$course = get_record ('course', 'id', $cm->course))
  error (get_string ('wrong_parameter', 'vizcosh'));

$context = get_context_instance (CONTEXT_MODULE, $cm->id);
require_capability ('moodle/course:manageactivities', $context);

if (!$vizcosh = get_record ('vizcosh', 'id', $cm->instance))
  error (get_string ('wrong_parameter', 'vizcosh'));

$chapter = get_record('vizcosh_chapters', 'id', $chapterid);

//check all variables
if ($chapter && $chapter->vizcoshid != $vizcosh->id)
  error (get_string ('wrong_parameter', 'vizcosh'));

//these two session variables were possibly set to identify a
//particular vizcosh selected for editing or inserting they can be
//deleted as no particular vizcosh is selected (anymore)
unset ($_SESSION['editor_vizalgoid']);
unset ($_SESSION['editor_modus']);

// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// List or Search Algorithm Visualizations available in DB
// =========================================================================

//create tabs: one for listing the available vizalgos and one for searching
$tabs = $row = $inactive = $activated = array ();
$row[] = new tabobject ('list', "addvis.php?tab=list",
			get_string('listtab', 'vizcosh'),
			get_string('listtab', 'vizcosh'));
$row[] = new tabobject ('search', "addvis.php?tab=search",
			get_string('searchtab', 'vizcosh'),
			get_string('searchtab', 'vizcosh'));
$tabs[] = $row;

//if user wants to see the list of available (or searched for)
//algorithm visualizations
if ($tab == 'list')
  {
    //create table which lists the vizalgos
    $table->head = array ('',
			  get_string('alvizname',   'vizcosh'),
			  get_string('alvizdesc',   'vizcosh'),
			  get_string('alvizauthor', 'vizcosh'),
			  get_string('alvizformat', 'vizcosh'),
			  "<a class='editing_new' title='New'".
			  "href='editorvis.php?vizalgo=-1&modus=new'>".
			  "<img src='pix/add.gif' class='iconbig'  alt='New' /></a>");

    $table->align = array ('left', 'left', 'center', 'center', 'center', 'center');
    
    $vizalgos = vizcosh_search_vizalgos ($COURSE->id, $search_title,
					 $search_desc, $search_auth,
					 $search_topics, $search_sort);
    
    //fill the table with the data received from the database
    if ($vizalgos)
      {
	foreach ($vizalgos as $viz)
	  {
	    $options = array ('id' => $cm->id,
			      'selected_vizalgo' => $viz->id);
	    
	    $user_data = get_record ('user', 'id', $viz->author, '', '', '', '',
				     'firstname, lastname');
	    $user_full_name = '-';
	    if ($user_data)
	      $user_full_name = $user_data->firstname . ' ' . $user_data->lastname;

	    $format_name = get_field ('vizcosh_vizalgo_formats',
				      'name', 'id', $viz->format);
	    
	    //if current user is author of a vizalgo she is also allowed to
	    //update or delete the vizalgo ->button for updating and
	    //deleting is added to this particular vizcosh entry in the
	    //table
	    $edit_button = "";
	    if ($viz->author == $USER->id)
	      $edit_button =
		"<a class='editing_update' title='Update' ".
		"href='editorvis.php?vizalgo=" . $viz->id . "&modus=edit'>".
		"<img src='../../pix/t/edit.gif' class='iconbig'  alt='Update'/>".
		"</a>" . " " . "<a class='editing_delete' title='Delete' ".
		"href='editorvis.php?vizalgo=" . $viz->id . "&modus=delete'>".
		"<img src='../../pix/t/delete.gif' class='iconbig' alt='Delete'/>".
		"</a>";
	    
	    $vizdata[] = array ("<input type='radio' name='selected_vizalgo' value='" .
				$viz->id .
				"'/>",
				$viz->title,
				$viz->description,
				$user_full_name,
				$format_name,
				$edit_button);
	  }
      }
    
    if (isset ($vizdata))
      $table->data = $vizdata;
  }

//Print the page with its tabs and algorithm visualizations table and
//its search fields prepare the page header
if ($course->category)
  $navigation = '<a href="../../course/view.php?id=' . $course->id
    . '">' . $course->shortname . '</a> ->';
else
  $navigation = '';

//needed for header bar
$strvizcosh  = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit     = get_string('editchapter', 'vizcosh');
$strlist     = get_string('addvis', 'vizcosh');
$pageheading = get_string('listalvis', 'vizcosh');

print_header ("$course->shortname: $vizcosh->name",
	      $course->fullname,
	      "<a href=\"index.php?id=$course->id\">$strvizcoshs</a>".
	      " -> <a href=\"view.php?id=$cm->id\">$vizcosh->name</a>".
	      " -> <a href=\"editparagraph.php?id=$cm->id&chapterid=$chapter->id".
	      "&paragraphid=$paragraphid&orderposition=$orderposition\">$stredit</a>".
	      " -> $strlist", '', '', true, '', '');

print_heading_with_help($pageheading, 'addvishelp', 'vizcosh');
print_simple_box_start('center', '');
print_tabs ($tabs, $tab, $inactive, $activated); 

include ('addvis.html');

print_simple_box_end ();
print_footer ($course);

// =========================================================================
// List or Search Algorithm Visualizations available in DB END
// =========================================================================
?>
