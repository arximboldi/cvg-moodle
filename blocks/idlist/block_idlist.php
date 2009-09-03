<?php

/**
 *  Time-stamp:  <2009-07-02 12:51:27 raskolnikov>
 *
 *  @file        block_idlist.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Thu Jul  2 12:51:19 2009
 *
 *  block_idlist block plugin.
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

class block_idlist extends block_base
{
  function init()
  {
    $this->title = get_string('title', 'block_idlist');
    $this->version = 2009070100;
  }

  function get_content()
  {
    global $USER, $CFG, $SESSION, $COURSE;

    $context = get_context_instance (CONTEXT_COURSE, $COURSE->id);
    
    if (has_capability ('moodle/course:managegroups', $context))
      {
	if ($this->content !== NULL) {
	  return $this->content;
	}

	$this->content = new stdClass;
	$this->content->text = '';
	$this->content->footer = '';
    
	if (!empty ($COURSE)) {
	  $this->content->text =
	    '<a href = "'
	    . $CFG->wwwroot
	    . '/blocks/idlist/edit_idlist.php?id='.$COURSE->id.'">'
	    . get_string ('link', 'block_idlist'). '</a>';  
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
  
}

?>
