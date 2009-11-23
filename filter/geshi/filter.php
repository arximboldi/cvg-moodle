<?php
/**
 * GeSHi filter for Moodle
 * <pre>
 *   File:   filter/geshi/filter.php
 *   Author: Nigel McNie
 *   E-mail: nigel@mcnie.name
 * </pre>
 * 
 * Moodle filter API file for the GeSHi filter.
 * 
 * This program is part of the GeSHi filter for Moodle.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package   geshifilter
 * @author    Nigel McNie
 * @copyright (C) 2006 Nigel McNie
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version   $Id$
 */

//
// GeSHi Filter:
//
//   - GeSHi 1.0.X for now
//   - Shouldn't conflict with other filters (hard)
//   - Should be inputtable by the WYSIWYG editor(s) and the text area
//     * Perhaps match multiple types of starters and enders for this?
//     * Should prevent other filters from messing with the output (?)
//     * Should allow configurability at "runtime" but should have sane defaults
//
function geshi_filter ($courseid, $input)
{
    $result = '';
    
    while (false !== ($pos = strpos($input, '[code '))) {
        $endpos = strpos($input, '[/code]', $pos);
        if (false === $endpos) {
            break;
        }
        // Detect escaped ending blocks
        while (substr($input, $endpos - 1, 1) == '\\') {
            $endpos = strpos($input, '[/code]', $endpos + 1);
        }
        if (false === $endpos) {
            break;
        }
        
        // Found a valid code block
        $result .= substr($input, 0, $pos);
        
        // The + 8 removes &lt;code
        $codeblock = substr($input, $pos + 6, $endpos - $pos - 6);
        // Remove escaped enders
        $codeblock = str_replace('\\[/code]', '[/code]', $codeblock);
        
        //echo "CODEBLOCK:".htmlspecialchars($codeblock);echo '<br><br>';
        
        $parts = explode(']', $codeblock, 2);
        $result .= geshi_filter_callback($parts[0], $parts[1]);
        // 8 for &lt;code, 4 for &gt;
        $input = substr($input, $endpos + 1 + 6);
    }
    
    $result .= $input;
        
    return $result;
}

function geshi_filter_callback ($config, $source)
{
    global $CFG;
    /** Get the GeSHi class */
    require_once("$CFG->dirroot/filter/geshi/geshi/geshi.php");

    // Parse out parameters
    $config = trim($config);
    $spacepos = strpos($config, ' ');
    if (!$spacepos) {
        $spacepos = strlen($config);
    }
    $language = substr($config, 0, $spacepos);
    
    
    // GeSHi doesn't have HTML language, just html4strict (nobody bothered
    // writing a language file for other versions... in any event, most people
    // expect the "html" language to be available.
    if ('html' == $language) {
        $language = 'html4strict';
    }
    
    
    // Tidy up the source.
    // Replace HTML entities which have been converted for display. GeSHi
    // will reconvert them as it highlights, in the meantime they just get
    // in the way.
    $source = str_replace(
        array('&lt;', '&gt;', '&amp;', '<br />', '&nbsp;'),
        array('<',    '>',    '&',     '', ' '),
    $source);
    // Here we remove a leading newline if there is one. If there is it almost
    // certainly means that the user wrote something like this:
    //
    //  <code language>
    //  // language source
    //  ...
    //  </code>
    //
    // And thus they probably didn't mean the newline between the > and the
    // start of the source to actually count.
    //
    // This way of removing the newlines ensures that it works for HTMLarea,
    // I will have to test with just the text area.
    //$start = substr($source, 0, 2);
    //$end   = substr($source, -2);
    if (substr($source, 0, 2) == "\r\n") {
        $source = substr($source, 2);
    } elseif (substr($source, 0, 1) == "\n") {
        $source = substr($source, 1);
    }
    if (substr($source, -4) == "\r\n\r\n") {
        $source = substr($source, 0, -2);
    } elseif (substr($source, -2) == "\r\n") {
        $source = substr($source, 0, -2);
    } elseif (substr($source, 0, -1) == "\n") {
        $source = substr($source, 0, -1);
    }

    // Stuff that might be moved into the config
    $insidepadding = '.75em';
    
    $is_ie = false !== strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE');
    
    $geshi = new GeSHi($source, $language);
    $geshi->set_encoding('utf-8');
    $geshi->set_overall_style("margin:0 1em;border:1px solid #ccc;background-color:#f0f0f0;padding-left:{$insidepadding};");
    
    // To make things look similar in IE...
    $fontfamily = 'monospace';
    $fontsize = '110%';
    $linenumberfontsize = '';
    if ($is_ie) {
        $fontfamily = "'Courier New', monospace";
        $fontsize = '100%';
        $linenumberfontsize = 'font-size: 90%;';
    }
    
    // Handle line numbering
    if (false !== strpos($config, 'linenumbers')) {
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        
        if ($is_ie) {
            $geshi->set_overall_style("padding:{$insidepadding} 0 {$insidepadding} {$insidepadding};", true);
        }
        
        $geshi->set_line_style("font-family: Arial;padding-left:.2em;{$linenumberfontsize}", "font-family: Arial;padding-left:.2em;{$linenumberfontsize}", true);
        $geshi->set_code_style("font-size:{$fontsize};font-family:{$fontfamily};font-weight:normal;','font-size:{$fontsize};font-family:{$fontfamily};");
        
        $match = array();
        if (preg_match('/start=(\d+)/', $config, $match)) {
            $geshi->start_line_numbers_at(intval($match[1]));
        }
    } else {
        // the 2.25em should be insidepadding*3
        $geshi->set_overall_style("padding: {$insidepadding} 0 {$insidepadding} 2.25em;{$linenumberfontsize}", true);
        
        $source .= "\n";
        $geshi->set_source($source);
        
    }
    
    // Look for lines to highlight
    $lines = array();
    $match = array();
    if (preg_match('/highlight=([0-9,-]+)/', $config, $match)) {
        $match = preg_replace('/\s/', '', $match[1]);
        $parts = explode(',', $match);
        foreach ($parts as $part) {
            if (false !== strpos($part, '-')) {
                list($start, $end) = explode('-', $part);
                $lines = array_merge($lines, range($start, $end));
            } else {
                $lines[] = intval($part);
            }
        }
    }
    $lines = array_unique($lines);
    
    $geshi->highlight_lines_extra($lines);
    
    return $geshi->parse_code();
}
?>
