<?php

/**
 *  Time-stamp:  <2009-07-17 16:46:59 raskolnikov>
 *
 *  @file        lib.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Thu Jul  2 12:45:52 2009
 *
 *  Library functions for the enrol_idlist plugin. These may be used
 *  by other related plugins, such as block_idlist.
 */

/*
 *  Copyright (C) 2009 Juan Pedro Bolívar Puente
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

define ('ENROL_IDLIST_DEFAULT_IDATTR',         "dni");
define ('ENROL_IDLIST_DEFAULT_PATH',           "idlist");
define ('ENROL_IDLIST_DEFAULT_REGEXP',         "%[0-9][0-9][0-9][0-9]+%");
define ('ENROL_IDLIST_DEFAULT_STRICT_CHECK_P', '1');

function enrol_idlist_get_path ()
{
  global $CFG;
  
  $path = ENROL_IDLIST_DEFAULT_PATH;
  if (isset ($CFG->enrol_idlist_path))
    $path = $CFG->enrol_idlist_path;

  if (strlen ($path) < 1 ||  $path[0] != '/') {
    $path = $CFG->dataroot . '/' . $path;
  }

  return $path;
}

function enrol_idlist_get_file_name ($course)
{
  global $CFG;

  $path = enrol_idlist_get_path ();
  $fullpath = $path . '/' . $course->id; // TODO: Generalize?

  return $fullpath;
}

function enrol_idlist_get_regexp ()
{
  global $CFG;
    
  if (isset ($CFG->enrol_idlist_regexp))
    return $CFG->enrol_idlist_regexp;
  else
    return  ENROL_IDLIST_DEFAULT_REGEXP;
}

function enrol_idlist_get_idattr ()
{
  global $CFG;
  
  if (isset ($CFG->enrol_idlist_idattr))
    return $CFG->enrol_idlist_idattr;
  else
    return  ENROL_IDLIST_DEFAULT_IDATTR;
}

function enrol_idlist_strict_check_p ()
{
  global $CFG;
  
  if (isset ($CFG->enrol_idlist_strict_check_p))
    return $CFG->enrol_idlist_strict_check_p;
  else
    return ENROL_IDLIST_DEFAULT_STRICT_CHECK_P;
}

function enrol_idlist_get_strict_hint ()
{
  global $CFG;
  
  if (isset ($CFG->enrol_idlist_strict_hint))
    return $CFG->enrol_idlist_strict_hint;
  else
    return  get_string ('enrol_idlist_default_strict_hint', 'enrol_idlist');;
}

function enrol_idlist_get_ids ($content)
{
  global $CFG;
  
  $regexp = enrol_idlist_get_regexp ();
  if (preg_match_all ($regexp, $content, $ids) > 0)
    {
      return $ids[0];
    }
  return array ();
}

?>
