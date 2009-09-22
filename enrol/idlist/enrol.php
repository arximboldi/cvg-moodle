<?php

  /**
   *  Time-stamp:  <2009-09-22 19:46:26 raskolnikov>
   *
   *  @file        enrol.php
   *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
   *  @date        Thu Jul  2 12:40:44 2009
   *
   *  Enrolment plugin to allow enrolments from users listed in a file
   *  marked by a custom field.
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

require_once ($CFG->dirroot.'/group/lib.php');
require_once (dirname (__FILE__) . '/lib.php');

/**
 * enrolment_plugin_idlist allows user enrolments to be checked against an
 * allowed id list given by the course manager.
 */
class enrolment_plugin_idlist
{
  var $errormsg;

  function check_idlist ($course)
  {
    global $CFG, $USER;
    
    $fullpath       = enrol_idlist_get_file_name ($course);
    $attr           = enrol_idlist_get_idattr ();
    $strict_check_p = enrol_idlist_strict_check_p ();
    $strict_hint    = enrol_idlist_get_strict_hint ();
    
    /* user id */
    $userid = $USER->$attr;
    $filtered = enrol_idlist_get_ids ($userid);

    if ($strict_check_p === '1')
      {
	if (! $filtered || count($filtered) < 1 || $filtered[0] != $userid)
	  return $strict_hint;  
      }
    else
      {
	if ($filtered && count ($filtered) > 0)
	  $userid = $filtered[0];
      }
    
    /* Check user id against id list */
    if (file_exists ($fullpath)) {
      $content = file_get_contents ($fullpath);
      $ids = enrol_idlist_get_ids ($content);
      
      foreach ($ids as $id) {
	if ($id == $userid) {
	  return true;
	}
      }
    }
    
    return get_string ('enrolment_id_error', 'enrol_idlist');
  }
  
  /**
   * Prints the entry form/page for this enrolment
   *
   * This is only called from course/enrol.php
   * Most plugins will probably override this to print payment
   * forms etc, or even just a notice to say that manual enrolment
   * is disabled
   *
   * @param    course  current course object
   */
  function print_entry($course) {
    global $CFG, $USER, $SESSION, $THEME;

    $strloginto = get_string('loginto', '', $course->shortname);
    $strcourses = get_string('courses');

    $navlinks = array();
    $navlinks[] = array('name' => $strcourses, 'link' => ".", 'type' => 'misc');
    $navlinks[] = array('name' => $strloginto, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header($strloginto, $course->fullname, $navigation);
    
    print_course($course, "80%");

    include("$CFG->dirroot/enrol/idlist/enrol.html");

    print_footer();
  }

  /**
   * The other half to print_entry, this checks the form data
   *
   * This function checks that the user has completed the task on the
   * enrolment entry page and then enrolls them.
   *
   * @param    form    the form data submitted, as an object
   * @param    course  the current course, as an object
   */
  function check_entry($form, $course) {
    global $CFG, $USER, $SESSION, $THEME;

    $result = $this->check_idlist ($course);
    
    if ($result === true)
      {
	if (isguestuser()) { // only real user guest, do not use this for users with guest role
	  $USER->enrolkey[$course->id] = true;
	  add_to_log($course->id, 'course', 'guest', 'view.php?id='.$course->id, getremoteaddr());

	} else {  /// Update or add new enrolment
	  if (enrol_into_course($course, $USER, 'idlist')) {
	    // force a refresh of mycourses
	    unset($USER->mycourses);
	  } else {
	    print_error('couldnotassignrole');
	  }
	}

	if ($SESSION->wantsurl) {
	  $destination = $SESSION->wantsurl;
	  unset($SESSION->wantsurl);
	} else {
	  $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
	}

	redirect($destination);
      }
    else
      {
	$this->errormsg = $result;
      }

  }

  /**
   * Check if the given enrolment key matches a group enrolment key for the given course
   *
   * @param    courseid  the current course id
   * @param    password  the submitted enrolment key
   */
  function check_group_entry ($courseid, $password)
  {
    return false;
  }

  /**
   * Prints a form for configuring the current enrolment plugin
   *
   * This function is called from admin/enrol.php, and outputs a
   * full page with a form for defining the current enrolment plugin.
   *
   * @param    frm  an object containing all the data for this page
   */
  function config_form($frm)
  {
    global $CFG;

    if (!isset( $frm->enrol_idlist_path )) {
      $frm->enrol_idlist_path = ENROL_IDLIST_DEFAULT_PATH;
    }

    if (!isset($frm->enrol_idlist_idattr)) {
      $frm->enrol_idlist_idattr = ENROL_IDLIST_DEFAULT_IDATTR;
    }
    
    if (!isset($frm->enrol_idlist_regexp)) {
      $frm->enrol_idlist_regexp = ENROL_IDLIST_DEFAULT_REGEXP;
    }

    if (!isset($frm->enrol_idlist_strict_check_p)) {
      $frm->enrol_idlist_strict_check_p = ENROL_IDLIST_DEFAULT_STRICT_CHECK_P;
    }

    if (!isset($frm->enrol_idlist_strict_hint)) {
      $frm->enrol_idlist_strict_hint = get_string ('enrol_idlist_default_strict_hint', 'enrol_idlist');
    }
    
    include ("$CFG->dirroot/enrol/idlist/config.html");
  }
  
  /**
   * Processes and stored configuration data for the enrolment plugin
   *
   * @param    config  all the configuration data as entered by the admin
   */
  function process_config($config)
  {
    $return = true;

    foreach ($config as $name => $value) {
      if (!set_config($name, $value)) {
	$return = false;
      }
    }

    return $return;
  }

  /**
   * @return void
   */
  function cron()
  {
  }

  /**
   * Returns the relevant icons for a course
   *
   * @param    course  the current course, as an object
   */
  function get_access_icons($course)
  {
    global $CFG;

    global $strallowguests;
    global $strrequireskey;

    if (empty($strallowguests)) {
      $strallowguests = get_string('allowguests');
      $strrequireskey = get_string('requireskey');
    }

    $str = '';

    if (!empty($course->guest)) {
      $str .= '<a title="'.$strallowguests.'" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">';
      $str .= '<img class="accessicon" alt="'.$strallowguests.'" src="'.$CFG->pixpath.'/i/guest.gif" /></a>&nbsp;&nbsp;';
    }
    if (!empty($course->password)) {
      $str .= '<a title="'.$strrequireskey.'" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">';
      $str .= '<img class="accessicon" alt="'.$strrequireskey.'" src="'.$CFG->pixpath.'/i/key.gif" /></a>';
    }

    return $str;
  }

} /// end of class

?>
