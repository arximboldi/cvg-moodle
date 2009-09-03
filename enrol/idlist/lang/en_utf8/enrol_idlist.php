<?php

/**
 *  Time-stamp:  <2009-07-05 19:46:35 raskolnikov>
 *
 *  @file        enrol_idlist.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Thu Jul  2 12:53:10 2009
 *
 *  ENGLISH UTF-8 translation strings for enrol_idlist.
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

$string['description'] = 'With this enrollment method students are enrolled from an ID list.';
$string['enrolname'] = 'ID list enrolment.';
$string['enrol_idlist_idattr'] = 'Student attribute to use as ID';
$string['enrol_idlist_path'] = 'Folder where the per course ID lists are stored.';
$string['enrol_idlist_regexp'] = 'Regular expression to extract the ID from the file.';
$string['enrolment_id_error'] = 'You seem to not be a valid member of this course. Please contact your teacher in case you are a legitimate participant of this course.';
$string['enrolment_msg'] = 'You are about to enrol into this course. Note that you have to be in the list of legitimate participants of the course. Contact your teacher in case of problems.';
$string['enrol_idlist_strict_check_p'] = 'If disabled the user ID will be filtered using the regular expression before testing.';
$string['enrol_idlist_strict_hint'] = 'Hint to show to the user in case strict_check is enabled and his ID is detected invalid -it changes when filtered by the regexp.';
$string['enrol_idlist_default_strict_hint'] = 'Your ID is not valid, only numerical characteres are allowed in your ID.';

?>
