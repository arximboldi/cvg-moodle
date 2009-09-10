<!--$Id: createjnlp.php,v 1.3 2008/02/03 23:55:52 vellaramkalayil Exp $ -->
<!-- Creating JNLP file from algorithm visualization in database
     1. Save adjusted jnlp file (insert path to temporary directory) and data file from algorithm visualization to temporary directory
     2. redirect to open the jnlp file
-->
<?PHP
require_once ('../../config.php');
require_once ('lib.php');
$id = required_param('id', PARAM_INT); // Course Module ID
$vizalgoid = required_param('selected_vizalgo', PARAM_INT); // Algorithm Visualization ID which was selected for starting via jnlp
// =========================================================================
// security checks START - only teachers add visualizations
// =========================================================================

if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
  error(get_string ('wrong_course_module_id', 'vizcosh'));
}
if (!$course = get_record('course', 'id', $cm->course)) {
  error(get_string ('wrong_course_id', 'vizcosh'));
}
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
  error(get_string ('wrong_vizcosh_id', 'vizcosh'));
}

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:view', $context);

/* TODO: This code seems... weird */
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
  error(get_string ('wrong_vizcosh_id', 'vizcosh'));
}

//get algorithm visualization from database
$vizalgo = get_record('vizcosh_vizalgos', 'id', $vizalgoid);

//check all variables
unset ($id);
unset ($vizalgoid);

// =========================================================================
// security checks END
// =========================================================================

// =========================================================================
// Save data file and jnlp file to tmp directory and redirect to open it
// =========================================================================

//get temporary directory on the machine of the user
$temp = $CFG->wwwroot . '/mod/vizcosh/files/tmp';

//replace <TMP-PATH> variable in jnlp with path to temporary directory
//and store it in this directory
if (isset ($vizalgo->jnlp) && $vizalgo->jnlp != null)
  {
    $jnlp_dec = $vizalgo->jnlp;
    $jnlp_dec = str_replace("<TMP-PATH>", $temp . "/", $jnlp_dec);
    $jnlp_dec = str_replace("<JNLP-FILENAME>", $vizalgo->id . "jnlp.jnlp", $jnlp_dec);
    $jnlp_temp = fopen("./files/tmp/" . $vizalgo->id . "jnlp.jnlp", "w");
    fwrite($jnlp_temp, $jnlp_dec);
    fclose($jnlp_temp);
  }

//store data file in temporary directory
if (isset ($vizalgo->data) && $vizalgo->data != null)
  {
    $data_dec = $vizalgo->data;
    $data_temp = fopen("./files/tmp/" . $vizalgo->fndata, "w");
    fwrite($data_temp, $data_dec);
    fclose($data_temp);
  }
?>

<html>
  <head>
    <title>Start Algorithm Visualisation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body>
      <?php
      echo "<h2>Title: </h2>".$vizalgo->title;
      echo "<h3>Description: </h3>".$vizalgo->description;
      
      $user_data = get_record ('user', 'id', $vizalgo->author, '', '', '', '',
			       'firstname, lastname');
      $user_full_name = '-';
      if ($user_data)
	$user_full_name = $user_data->firstname . ' ' . $user_data->lastname;
      
      echo "<h3>Author: </h3>" . $user_full_name;		       
      echo "<h3>Date last modified: </h3>". userdate(strtotime($vizalgo->date),"%Y/%m/%d");
      echo "<br>";
      $openlink =
	'<a href="openjnlp.php?filepath='
	. $temp .'&filename=' . $vizalgo->id .
	'jnlp.jnlp" target="_blank" onclick="window.close();" >Download and Run</a>';
      echo "<h1>" .$openlink. "</h1>";
      ?>
    </body>
  </html>