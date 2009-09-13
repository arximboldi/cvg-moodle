<?php 
 
require_once ('../../config.php');
require_once ('lib.php');

$id          = required_param('id', PARAM_INT); // Course Module ID
$chapterid   = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$paragraphid = required_param('paragraphid', PARAM_INT); //id in mdl_vizcosh_paragraphs

$paragraph   = get_record('vizcosh_paragraphs', 'id', $paragraphid);
if (!$paragraph)
  error (get_string ('wrong_parameter', 'vizcosh'));

require_login();
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) 
  error (get_string ('wrong_paramter', 'vizcosh'));


if (!$course = get_record('course', 'id', $cm->course))
  error (get_string ('wront_paramter', 'vizcosh'));

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
  error (get_string ('wrong_parameter'));

#allowedit contains teacher editing rights
$allowedit = has_capability('moodle/course:manageactivities', $context);

#allowtextedit contains user editing rights
$allowtextedit =
  has_capability ('mod/vizcosh:useredit', $context) &&
  $vizcosh->enablegroupfunction == 1;

if ($allowtextedit or $allowedit)
  {
    $confirm = optional_param ('confirm', 0, PARAM_BOOL);

    // header and strings
    $strvizcoshs = get_string ('modulenameplural', 'vizcosh');
    $strvizcosh  = get_string ('modulename', 'vizcosh');
    
    if ($course->category)
      $navigation = '<a href="../../course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> ->';
    else
      $navigation = '';

    print_header ("$course->shortname: $vizcosh->name",
		  $course->fullname,
		  "$navigation <a href=index.php?id=$course->id>$strvizcoshs</a>".
		  " -> $vizcosh->name",
		  '', '', true, '', '');		
    
    // form processing
    if ($confirm)
      {
	// the operation was confirmed.
	vizcosh_delete_paragraph ($paragraph, !$allowedit);
	
	add_to_log ($course->id, 'course', 'update mod',
		    '../mod/vizcosh/view.php?id=' . $cm->id, 'vizcosh ' . $vizcosh->id);
	add_to_log ($course->id, 'vizcosh', 'update',
		    'view.php?id=' . $cm->id, $vizcosh->id, $cm->id);
  
	#vizcosh_check_structure($vizcosh->id);
	redirect ('view.php?id=' . $cm->id);
	die;
      }
    else
      {
	// the operation has not been confirmed yet so ask the user to do so
	$strconfirm =
	  get_string ('confparagraphdelete', 'vizcosh') .
	  "<div class=\"box errorbox errorboxcontent\">";
	if ($allowedit)
	  $strconfirm .= get_string('paragraphdeletewarning','vizcosh');
	else if ($allowtextedit)
	  $strconfirm .= get_string('paragraphdeletegroupemargonotice','vizcosh');
	$strconfirm .= "</div>";
		
	echo '<br/>';
	notice_yesno ("<strong>".get_string ('delete_paragraph_ask', 'vizcosh')."</strong>".
		      "<p>$paragraph->content</p><p>$strconfirm</p>",
		      "deleteparagraph.php?id=$cm->id".
		      "&chapterid=$paragraph->chapterid&confirm=1".
		      "&paragraphid=$paragraph->id&sesskey=$USER->sesskey",
		      "view.php?id=$cm->id&chapterid=$chapterid");				  
      }

    print_footer($course);
  }
else
  error (get_string ('permission_denied', 'vizcosh'));

?>
