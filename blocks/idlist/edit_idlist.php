<?php

  /**
   *  Time-stamp:  <2009-07-17 16:51:07 raskolnikov>
   *
   *  @file        edit_idlist.php
   *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
   *  @date        Thu Jul  2 12:39:08 2009
   *
   *  Editor form for the block_idlist plugin.
   */

  /*
   *  Copyright (C) 2009 Juan Pedro Bolívar Puente
   *
   *  This file is part of cvg-moodle.
   *   
   *  cvg-moodle is free software: you can redistribute it and/or modify
   *  it under the terms of the GNU General Public License as published by
   *  the Free Software Foundation, either version 3 of the License, or
   *  (at your option) any later version.
   *
   *  cvg-moodle is distributed in the hope that it will be useful,
   *  but WITHOUT ANY WARRANTY; without even the implied warranty of
   *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   *  GNU General Public License for more details.
   *
   *  You should have received a copy of the GNU General Public License
   *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
   *
   */

  /* FUNCTIONS */

function block_idlist_get_user_id_by_custom_field ($fieldname, $fieldvalue)
{
  global $CFG;
  
  $info_tbl      = $CFG->prefix . "user_info_field";
  $info_data_tbl = $CFG->prefix . "user_info_data";
  
  $sql =
    "select userid from $info_data_tbl " .
    "where " .
    "data = '$fieldvalue' and " .
    "fieldid in (select id from $info_tbl where shortname = '$fieldname')";
  
  $res = get_record_sql ($sql, true);

  if ($res !== false)
    return $res->userid;

  return null;
}

function block_idlist_print_header ($fname, $regexp)
{
  global $COURSE;
  
  $title = get_string('idlist_editor', 'block_idlist');
  $navlinks = array();
  $navlinks[] = array('name' => $title, 'link' => null, 'type' => 'misc');
  $navigation = build_navigation($navlinks);
  
  print_header($title, $COURSE->fullname, $navigation);
  echo "<br/>";
  
  echo get_string ('editing_msg', 'block_idlist');
  echo "<br/>";
  echo "<br/><strong>" . get_string ('edit_regexp', 'block_idlist') . ":</strong> " . $regexp;
  echo "<br/><strong>" . get_string ('edit_fname', 'block_idlist') . ":</strong> " . $fname;  
}

function block_idlist_print_footer ()
{
  print_footer ();
}

function block_idlist_write_file ($fname, $content)
{
  $dir = dirname ($fname);
  if (!file_exists ($dir))
    mkdir ($dir, 0777, true);
  
  $fh = fopen ($fname, "w");

  if ($fh) {
    fwrite ($fh, $content);
    fclose ($fh);
    print_box (get_string ("file_write_ok", "block_idlist"), 'feedbackbox');
    echo "<br/>";
  } else {
    print_box (get_string ("file_write_error", "block_idlist"), 'errorbox');
  }
}

function block_idlist_print_editor ($content)
{
  global $COURSE;

  print_box_start ('generalbox centerpara');

  $checkmsg      = get_string("edit_check",  "block_idlist");
  $savemsg       = get_string("edit_save",   "block_idlist");
  $notinlistmsg  = get_string("not_in_list", "block_idlist");
  $notenroledmsg = get_string("not_enroled", "block_idlist");
    
  print
    '<form method="post" action="edit_idlist.php">'.
    '<textarea name="content" rows="20" cols="80">'."$content".'</textarea>'.
    '<input type="hidden" name="id" value="'. "{$COURSE->id}" .'"  />'.
    '<br/><br/>'.
    '<input type="submit" value="'."$savemsg".'" name="do_save">'.
    '<input type="submit" value="'."$checkmsg".'" name="do_check">'.
    '<input type="submit" value="'."$notinlistmsg".'" name="do_not_in_list">'.
    '<input type="submit" value="'."$notenroledmsg".'" name="do_not_enroled">'.
    '</form>';
  
  print_box_end ();
}

function block_idlist_check ($content)
{
  $attr = enrol_idlist_get_idattr ();
  
  print_box_start ('generalbox centerpara');
  $ids = enrol_idlist_get_ids ($content);

  echo "<h3>".get_string ("extracted_ids", "block_idlist")."</h3>";
    
  if (count ($ids) > 0)
    {
      $table->head = array ($attr);
      $table->data = array_chunk ($ids, 1, true);
      print_table ($table);
    }
  else
    print_box (get_string ("no_id_found", "block_idlist"), 'informationbox');
  
  print_box_end ();
}

function block_idlist_not_in_list ($context, $content, $courseid)
{
  print_box_start ('generalbox centerpara');
  
  echo "<h3>".get_string ("ids_not_in_list", "block_idlist")."</h3>";
  
  $attr = enrol_idlist_get_idattr ();
  $ids = enrol_idlist_get_ids ($content);
  $users = get_users_by_capability ($context, 'moodle/legacy:student', "u.id");

  $table->data = array ();
  foreach ($users as $u) {
    $udata = get_complete_user_data ("id", $u->id);

    if (!in_array ($udata->$attr, $ids)) {
      $table->data[] = array (print_user_picture ($udata, $courseid, null, 0, true),
			      $udata->$attr,
			      $udata->lastname,
			      $udata->firstname);
    }
  }

  $table->head = array ("", $attr, get_string ("lastname"), get_string ("firstname"));
  print_table ($table);

  print_box_end ();
}

function block_idlist_not_enroled ($context, $content, $courseid)
{
  print_box_start ('generalbox centerpara');

  echo "<h3>".get_string ("ids_not_enroled", "block_idlist")."</h3>";
  
  $attr = enrol_idlist_get_idattr ();
  $ids = enrol_idlist_get_ids ($content);

  $users = get_users_by_capability ($context, 'moodle/legacy:student', "u.id");
  $user_datas = array ();
  foreach ($users as $u)
    $user_datas[] = get_complete_user_data ("id", $u->id);
  
  foreach ($ids as $id) {
    $found = null;
    foreach ($user_datas as $d) {
      if ($d->$attr == $id) {
	$found = $d;
	break;
      }
    }
    if ($found == null) {
      $uid = block_idlist_get_user_id_by_custom_field ($attr, $id);
      
      if ($uid !== null)
	{
	  $udata = get_complete_user_data ("id", $uid);
	  $table_one->data[] = array (print_user_picture ($udata, $courseid, null, 0, true, false),
				      $udata->$attr,
				      $udata->lastname,
				      $udata->firstname);
	}
      else
	{
	  $table_two->data[] = array ($id);
	}
    }    
  }

  
  $table_one->head = array ("", $attr, get_string ("lastname"), get_string ("firstname"));
  print_table ($table_one);
  echo "<br/>";
  
  $table_two->head = array ($attr);
  print_table ($table_two);

  print_box_end ();
}

/* MAIN */

require_once('../../config.php');
require_once($CFG->dirroot.'/enrol/idlist/lib.php');

$courseid = required_param ('id', PARAM_INT);

$context = get_context_instance (CONTEXT_COURSE, $courseid);
require_capability ('moodle/course:managegroups', $context);  

$fname  = enrol_idlist_get_file_name ($COURSE);
$regexp = enrol_idlist_get_regexp ($COURSE);

$file_content = optional_param ('content', null, PARAM_TEXT);
$do_save = optional_param ('do_save', null, PARAM_RAW);
$do_check = optional_param ('do_check', null, PARAM_RAW);
$do_not_in_list = optional_param ('do_not_in_list', null, PARAM_RAW);
$do_not_enroled = optional_param ('do_not_enroled', null, PARAM_RAW);

if ($file_content == null)
  {
    if (file_exists ($fname))
      $file_content = file_get_contents ($fname);
    else 
      $file_content = "";
  }

block_idlist_print_header ($fname, $regexp);

if ($do_save)
  block_idlist_write_file ($fname, $file_content);

block_idlist_print_editor ($file_content);

if ($do_check)
  block_idlist_check ($file_content, $regexp);

if ($do_not_in_list)
  block_idlist_not_in_list ($context, $file_content, $courseid);

if ($do_not_enroled)
  block_idlist_not_enroled ($context, $file_content, $courseid);

block_idlist_print_footer ();

?>
