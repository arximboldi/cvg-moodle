<?php

/**
 *  Time-stamp:  <2009-09-11 21:16:19 raskolnikov>
 *
 *  @file        dl_data.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Fri Sep 11 20:51:54 2009
 *
 *  Send the visualization data.
 */

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/filelib.php');

$vizalgoid = required_param ('id', PARAM_INT);
$vizalgo = vizcosh_file_send_prepare ($vizalgoid, false);
if (!$vizalgo)
  error (get_string ('wrong_vizalgo_error', 'vizalgo'));

send_file ($vizalgo->data, $vizalgo->fndata, 'default', 0, true);

?>
