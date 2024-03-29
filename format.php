<?php
// This file is part of Moodle - http://moodle.org/
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
 * format_mooin4_renderer
 *
 * @package    format_mooin4
 * @author     Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2020 Rodrigo Brandão <rodrigo.brandao.contato@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\check\check;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

$courseid = required_param('id', PARAM_INT); // Course id


global $PAGE;
global $USER;

// $url_new = new moodle_url('/course/inf.php', array('id' => $course->id), $anchor='infos');

if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
}
$context = context_course::instance($course->id);
if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

$course = course_get_format($course)->get_course();
    /* echo'<pre>';
        var_dump($course);
    echo'</pre>'; */
course_create_sections_if_missing($course, range(0, $course->numsections));

// Get User Preferences
get_user_preferences();


$renderer = $PAGE->get_renderer('format_mooin4');

$desc = $course->summary;

$first_bloc = null;
$second_bloc = null;

$temp_bloc = $first_bloc;
// echo('Display Section'. $displaysection);

if (!empty($displaysection)) {
    // $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
    $renderer->print_multiple_section_page($course, null, null, null, null, $displaysection);
} else {
    // $renderer->print_multiple_section_page($course, null, null, null, null, $displaysection);
     /* if((get_user_preferences('mooin4_display') == null && get_user_preferences('card_display') == null ) || get_user_preferences('card_display') == 'yes' ){
       redirect('/moodle/course/format/mooin4/infos.php'.'?id='.$courseid);
     } */
     if(get_user_preferences('mooin4_display') ==  'yes') {
        
        $PAGE->navbar;
        $renderer->print_multiple_section_page($course, null, null, null, null, $displaysection);
     }else {
        redirect('/moodle/course/format/mooin4/infos.php'.'?id='.$courseid);
     }
}

$PAGE->requires->js('/course/format/mooin4/format.js');