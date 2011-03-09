<?php

// This file is part of Moodle - http://moodle.org/
//
// GeSHi syntax highlight filter for Moodle
// Based on work by Grigory Rubtsov <rgbeast@onlineuniversity.ru>, 2005
//
// Uses GeSHi syntax highlighter 1.0.7.5
// http://qbnz.com/highlighter/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    filter
 * @subpackage feshi
 * @copyright  2005 Nigel McNie <nigel@geshi.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot."/filter/geshi/geshi/geshi.php");

//
// DEFAULT CONFIGURATION
//
// Configure the GeSHi filter here
//

// Line numbers: set to true to have them on by default
$CFG->geshifilter_linenumbers = false;

// Keyword to URL conversion: set to true to have this conversion made
$CFG->geshifilter_urls = true;

// Indent Size: controls the number of spaces that are substituted for a tab
$CFG->geshifilter_indentsize = 4;


// Highlight code enclosed by <span syntax="langname"> </span>, with options:
// linenumbers="yes": Enable line numbers
// urls="yes":        Enable keyword-to-URL conversion
// indentsize="num":  Switch tabs for this many spaces. Be warned! Only TABS are replaced.
function geshi_filter($courseid, $text) {
  if (stripos($text, '<code>') !== false) {
     $search = '/<code(.*?)>(.*?)<\/code>\s*/is';
  } else if (stripos($text, '<php>') !== false) {
     $search = '/<php(.*?)>(.*?)<\/php>\s*/is';
  } else if (stripos($text, 'syntax=') !== false) {
     $search = '/<span (.*?)>(.*?)<\/span>\s*/is';
  } else {
     return $text;
  }
  return preg_replace_callback($search, 'geshi_filter_callback', $text);
}

function geshi_filter_callback($data) {
    global $CFG;

    //echo 'data as inputted:';
    //geshi_dbg($data);

    $options = array(
        'syntax'      => 'php',
        'linenumbers' => $CFG->geshifilter_linenumbers,
        'urls'        => $CFG->geshifilter_urls,
        'indentsize'  => $CFG->geshifilter_indentsize,
        'inline'      => 'no'
    );

    if (isset($data[2])) {
        // Get the options that the user set in the <span> tag
        $chosen_options = explode(' ', $data[1]);
        //echo 'chosen options RAW:';
        //geshi_dbg($chosen_options);
        foreach ($chosen_options as $key => $option) {
            unset($chosen_options[$key]);
            $parts = explode('=', $option);
            if (!isset($parts[1])) {     // MD to avoid notice
                continue;                // MD
            }                            // MD
            $parts[1] = (($parts[1] && '"' == $parts[1][0])) ? substr($parts[1], 1) : $parts[1];
            $parts[1] = (($parts[1] && '"' == $parts[1][strlen($parts[1]) - 1])) ? substr($parts[1], 0, -1) : $parts[1];
            //echo 'parts:';
            //geshi_dbg($parts);
            //if ($parts[1]) {
                $chosen_options[$parts[0]] = $parts[1];
            //}
        }
        $chosen_options_keys = array_keys($chosen_options);
        //echo 'chosen options processed';
        //geshi_dbg($chosen_options);

        // Set options
        foreach (array_keys($options) as $key) {
            if (in_array($key, $chosen_options_keys)) {
                $options[$key] = $chosen_options[$key];
            }
        }
        //echo 'options as set:';
        //var_dump($options);
        if (is_null($options['syntax'])) {
            return $data[0];
        }
        if ('' == $options['syntax']) {
            return '<pre>' . $data[2] . '</pre>';
        }

        // BC for original plugin
        if ('_' == $options['syntax'][0]) {
            $options['linenumbers'] = true;
            $options['syntax'] = substr($options['syntax'], 1);
        }

        // Because GeSHi uses html4strict as language name for
        // HTML, we should convert the more common "HTML" here.
        if ('html' == $options['syntax']) {
            $options['syntax'] = 'html4strict';
        }
        $code = geshi_filter_decode_special_chars(geshi_filter_br2nl($data[2]));

        $geshi =& new GeSHi($code, $options['syntax']);
        $geshi->enable_classes(true);
        $geshi->set_overall_style('font-family: monospace;');

        $header = $footer = '';
        if (geshi_is_yes($options['inline'])) {
            $geshi->set_header_type(GESHI_HEADER_NONE);
            $header = '<span style="font-family:monospace;" class="' . $options['syntax'] . '">';
            $footer = '</span>';
        } else {
            if (geshi_is_yes($options['linenumbers'])) {
                $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
                $geshi->set_line_style('color:#222;', 'color:#888;');
                $geshi->set_header_type(GESHI_HEADER_DIV);
                $geshi->set_overall_style('font-size: 14px;font-family: monospace;', true);
            }
            if ($options['indentsize']) {
                $geshi->set_tab_width($options['indentsize']);
                $geshi->set_header_type(GESHI_HEADER_DIV);
            }
        }

        if (!geshi_is_yes($options['urls'])) {
            for ($i = 0; $i < 5; $i++) {
                $geshi->set_url_for_keyword_group($i, '');
            }
        }

        return $header . $geshi->parse_code() . $footer;
    }
}

function geshi_filter_br2nl($str) {
  return preg_replace("'<br\s*\/?>\r?\n?'","\n",$str);
}

function geshi_filter_decode_special_chars($str) {
  // analog of htmlspecialchars_decode in PHP 5
  $search = array("&amp;","&quot;", "&lt;", "&gt;","&#92;","&#39;");
  $replace = array("&","\"", "<", ">","\\","\'");
  return str_replace($search, $replace, $str);
}

function geshi_is_yes ($str) {
    return ('yes' == $str || '1' == $str);
}

function geshi_dbg($input) {
    echo '<pre>' . htmlspecialchars(print_r($input, true)) . '</pre>';
}
