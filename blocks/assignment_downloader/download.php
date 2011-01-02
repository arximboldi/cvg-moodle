<?php

  /**
   *  Time-stamp:  <2010-02-20 01:12:52 raskolnikov>
   *
   *  @file        download.php
   *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
   *  @date        Tue Jul 14 19:23:04 2009
   *
   *  Interfaz para descargar masivamente tareas de los alumnos.
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

require_once ('../../config.php');
require_once ('../../lib/filelib.php');
require_once ('../../lib/grouplib.php');
require_once ('Tar.php');

$ASSIGNMENT_DOWNLOADER_COMPRESSION_EXT =
  array ('' => ".tar",
	 "gz" => ".tar.gz",
	 "bz2" => ".tar.bz2");

function assignment_downloader_print_header ()
{
  global $COURSE;
  
  $title = get_string('downloading', 'block_assignment_downloader');
  $navlinks = array();
  $navlinks[] = array('name' => $title, 'link' => null, 'type' => 'misc');
  $navigation = build_navigation($navlinks);
  
  print_header($title, $COURSE->fullname, $navigation);
  echo "<br/>";  
}

function assignment_downloader_print_downloading_box ($file)
{
  print_box_start('generalbox centerpara');
  
  echo '<h3>' . get_string ('downloading', 'block_assignment_downloader') . '</h3>';
  echo "<br/>";
  echo get_string ('download_here', 'block_assignment_downloader', $file);

  print_box_end ();
}

function assignment_downloader_print_error ()
{
  print_box_start ('errorbox');
  print_string ('archive_error', 'block_assignment_downloader');
  print_box_end ();
}

function assignment_downloader_print_footer ()
{
  print_footer ();
}

function assignment_downloader_input_area ($assignid)
{
  global $CFG, $COURSE;
  return $COURSE->id.'/'.$CFG->moddata.'/assignment/'.$assignid;
}

function assignment_downloader_output_area ()
{
  global $CFG, $COURSE;
  $skey = sesskey ();
  return $COURSE->id . '/' . $CFG->moddata . '/assignment_downloader';
}

function assignment_downloader_file_name ($assignid, $groupid)
{
  global $CFG;
  
  $grpattr = $CFG->block_assignment_downloader_group_attribute;
  $assattr = $CFG->block_assignment_downloader_assignment_attribute;

  $ass = get_record ('assignment', 'id', $assignid);

  switch ($groupid) {
  case -1:
    $strgrp = strtolower (get_string ('all'));
    break;
  case -2:
    $strgrp = get_string ('no_group_file', 'block_assignment_downloader');
    break;
  default:
    $strgrp = groups_get_group ($groupid)->$grpattr;
    break;
  }
  
  return
    strtolower (get_string ('modulename', 'assignment')) .'-'.
    $ass->$assattr .'-'.
    $strgrp .'-'.
    date ("d-m-Y");
}

function assignment_downloader_get_ext ($compression)
{
  global $ASSIGNMENT_DOWNLOADER_COMPRESSION_EXT;
  return $ASSIGNMENT_DOWNLOADER_COMPRESSION_EXT [$compression];
}

function assignment_downloader_make_output_area ($dir)
{
  if (!file_exists ($dir))
    mkdir ($dir, 0777, true);
}

function assignment_downloader_add_group_files ($srcdir, $dstdir, $archive, $assignid, $group)
{
  global $CFG, $COURSE;

  $grpattr  = $CFG->block_assignment_downloader_group_attribute;
  $userattr = $CFG->block_assignment_downloader_user_attribute;

  if ($group != null)
    $dstdir .= '/'. $group->$grpattr;
  else
    $dstdir .= '/'. get_string ('no_group_file', 'block_assignment_downloader');

  if (file_exists ($srcdir))
    $files  = array_diff (scandir ($srcdir), array ('.', '..'));
  else
    $files = array ();
  foreach ($files as $file)
    {
      $srcuserdir = $srcdir .'/'. $file;
      
      if (is_dir ($srcuserdir) &&
	  (($group !== null && groups_is_member ($group->id, $file)) ||
	   ($group === null && groups_get_all_groups ($COURSE->id, $file) === false)))
	{
	  $user = get_complete_user_data ('id', $file);
	  $dstuserdir = $dstdir .'/'. $user->$userattr;
	  
	  $comment = get_record ('assignment_submissions',
				 'assignment', $assignid,
				 'userid', $file, '', '',
				 'submissioncomment');
	  if ($comment && $comment->submissioncomment != '')
	    $archive->addString ($dstuserdir .'/'. get_string ('comment_file', 'block_assignment_downloader'),
				 $comment->submissioncomment);

	  $user_files = array_diff (scandir ($srcuserdir), array ('.', '..'));
	  $compress_files = array ();
	  foreach ($user_files as $uf)
	    $compress_files[] = $srcuserdir .'/'. $uf;

	  $archive->addModify ($compress_files, $dstuserdir, $srcuserdir);
	}
    }
}

function assignment_downloader_create_archive ($fname, $compression)
{  
  if (file_exists ($fname))
    {
      unlink ($fname);
    }

  return new Archive_Tar ($fname, $compression);
}

function assignment_downloader_build ($assignid, $groupid, $compression)
{
  global $CFG, $COURSE;

  $grpattr = $CFG->block_assignment_downloader_group_attribute;
  
  $srcdir = $CFG->dataroot . '/' . assignment_downloader_input_area ($assignid);
  $dstdir = $CFG->dataroot . '/' . assignment_downloader_output_area ();
  $fname  = assignment_downloader_file_name ($assignid, $groupid);
  $fpath  = $dstdir.'/'.sesskey ().'-'.$fname.
    assignment_downloader_get_ext ($compression);
  
  assignment_downloader_make_output_area ($dstdir);
  $archive = assignment_downloader_create_archive ($fpath, $compression);

  $archive->create ('');
  if ($groupid == -1)
    {
      $groups = groups_get_all_groups ($COURSE->id);
      if ($groups)
	foreach ($groups as $grp)
	  assignment_downloader_add_group_files ($srcdir, $fname, $archive, $assignid, $grp);
      assignment_downloader_add_group_files ($srcdir, $fname, $archive, $assignid, null);
    }
  elseif ($groupid == -2)
    {
      assignment_downloader_add_group_files ($srcdir, $fname, $archive, $assignid, null);
    }
  else
    {
      $grp = groups_get_group ($groupid);
      assignment_downloader_add_group_files ($srcdir, $fname, $archive, $grp);
    }

  return $fpath;
}

$courseid = required_param ('id', PARAM_INT);
$groupid = required_param ('group_id', PARAM_INT);
$assignid = required_param ('assignment_id', PARAM_INT);
$compression = required_param ('compression', PARAM_ALPHANUM);

$context = get_context_instance (CONTEXT_COURSE, $courseid);
require_capability ('mod/assignment:grade', $context);  

$fname = assignment_downloader_build ($assignid, $groupid, $compression);
$send_fname = assignment_downloader_file_name ($assignid, $groupid) .
  assignment_downloader_get_ext ($compression);
  
if ($fname !== false && file_exists ($fname))
  {
    send_temp_file ($fname, $send_fname);
  }
else
  {
    assignment_downloader_print_header ($fname);
    assignment_downloader_print_error ();
    assignment_downloader_print_footer ();
  }

?>
