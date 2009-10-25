<?php

  /**
   *  Time-stamp:  <2009-10-25 17:52:24 raskolnikov>
   *
   *  @file        block_assignment_downloader.php
   *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
   *  @date        Tue Jul 14 17:59:04 2009
   *
   *  Massive assignment downloader block.
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

class block_assignment_downloader extends block_base
{
  function init()
  {
    $this->title = get_string('title', 'block_assignment_downloader');
    $this->version = 2009071400;
  }

  function has_config ()
  {
    return true;
  }
  
  function get_content()
  {
    global $USER, $CFG, $SESSION, $COURSE;

    $context = get_context_instance (CONTEXT_COURSE, $COURSE->id);
    
    if (has_capability ('mod/assignment:grade', $context))
      {
	if ($this->content !== NULL) {
	  return $this->content;
	}

	$downloadmsg = get_string ('download', 'block_assignment_downloader');
	
	$this->content = new stdClass;
	$this->content->text = '';
	$this->content->footer = '';
    
	if (!empty ($COURSE)) {
	  $assignments = $this->_get_assignment_chooser ();
	  $groups = $this->_get_group_chooser ();

	  if ($assignments === false)
	    $this->content->text .=
	      get_string ('no_assignments', 'block_assignment_downloader');
	  else
	    $this->content->text .=
	      '<div align="center">'.
	      '<form method="get" action="'. $CFG->wwwroot .'/blocks/assignment_downloader/download.php"> ' .
	      '<table align="center">'.
	      '<tr><td align="right">'.get_string ('modulename', 'assignment').': </td><td></td><tr><td colspan="2">'.
	      $assignments .
	      '</td></tr>'.
	      '<tr><td align="right">'.get_string ('group').': </td><td>' .
	      $groups .
	      '</td></tr>'.
	      '<tr><td align="right">'.get_string ('compression', 'block_assignment_downloader').': </td><td>' .
	      $this->_get_compression_chooser () .
	      '</td></tr>'.
	      '</table>'.
	      '<input type="hidden" name="id" value="'. "{$COURSE->id}" .'"  />'.
	      '<input type="submit" value="' . "$downloadmsg" . '" name="do_download">' .
	      '</form>'.
	      '</div>';
	}
      }
    else
      $this->content = null;

    return $this->content;
  }

  function applicable_formats()
  {
    return array('all'         => false, 
		 'course-view' => true);
  }

  function _get_assignment_chooser ()
  {
    global $COURSE;
    
    $assignments = get_records_select ('assignment',
				       "course = {$COURSE->id} && assignmenttype = 'upload'",
				       'id, name');
    if ($assignments === false)
      return false;

    $choices = array ();
    foreach ($assignments as $curr)
      $choices["{$curr->id}"] =
      strlen ($curr->name) > 20 ?
      substr ($curr->name, 0, 20) . "..." :
      $curr->name;
    
    return choose_from_menu ($choices, 'assignment_id',
			     '', '', '', 0, true,
			     false, null, null,
			     false);	  
  }

  function _get_group_chooser ()
  {
    global $COURSE;

    $choices = array ();
    $choices['-1'] = get_string ('all');
    $choices['-2'] = get_string ('none');
    
    $groups = groups_get_all_groups ($COURSE->id);

    if ($groups)
      foreach ($groups as $curr)
	$choices["{$curr->id}"] = $curr->name;
    
    return choose_from_menu ($choices, 'group_id',
			     '-1', '', '', 0, true);	  
  }

  function _get_compression_chooser ()
  {
    $choices = array ("gz" => "Gzip",
		      "bz2" => "Bzip2",
		      "" => "No");

    return choose_from_menu ($choices, 'compression',
			     'gz', '', '', 0, true);
  }
  
}

?>
