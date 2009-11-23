<?php

/**
 *  Time-stamp:  <2009-09-14 22:50:32 raskolnikov>
 *
 *  @file        dl_xaal.php
 *  @author      Juan Pedro Bolivar Puente <raskolnikov@es.gnu.org>
 *  @date        Mon Sep 14 22:44:46 2009
 *
 *  Send the Xaal file to the user.
 */

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/filelib.php');

$courseid = required_param ('id', PARAM_INT);
$filename = required_param ('file', PARAM_PATH);

require_course_login ($courseid, true);
$context = get_context_instance (CONTEXT_COURSE, $courseid);
require_capability ('moodle/course:view', $context);

$full_filename = $CFG->dataroot.'/'.$courseid.'/'.$filename;

send_file ($full_filename, $filename);

?>
