<?PHP

/**
 *  Time-stamp:  <2009-09-11 21:15:59 raskolnikov>
 *
 *  @file        dl_jnlp.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Fri Sep 11 20:50:09 2009
 *
 *  Send the visualization JNLP luncher.
 */

require_once ('../../config.php');
require_once ('lib.php');
require_once ($CFG->libdir . '/filelib.php');

$vizalgoid = required_param ('id', PARAM_INT);

$vizalgo = vizcosh_file_send_prepare ($vizalgoid, false);
if (!$vizalgo)
  error (get_string ('wrong_vizalgo_error', 'vizalgo'));

$jnlp_text = vizcosh_generate_jnlp ($vizalgo);
send_file ($jnlp_text, "algviz_$vizalgoid.jnlp", 'default', 0, true);

?>
