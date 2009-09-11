<?php

/**
 *  Time-stamp:  <2009-09-11 21:10:54 raskolnikov>
 *
 *  @file        dl_thumb.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Fri Sep 11 20:49:54 2009
 *
 *  Send the visualization thumbnail.
 */

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/filelib.php');

$vizalgoid = required_param ('id', PARAM_INT);
$vizalgo = vizcosh_file_send_prepare ($vizalgoid);

if ($vizalgo &&
    isset ($vizalgo->thumbnail) && 
    (strcmp($vizalgo->thumbnail, "default") != 0))
  {
    send_file ($vizalgo->thumbnail, $vizalgo->fnthumbnail, 'default', 0, true);
  }
else
  send_file ('./pix/default.gif', 'default.gif');

?>
