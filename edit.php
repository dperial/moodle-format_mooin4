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
 * Lists all the users within a given course.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package format_mooin4
 * @author Nguefack
 */

use format_mooin4\form\edit;


require_once('../../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once('../mooin4/lib.php');

// require_once($CFG->dirroot . '/course/format/mooin4/classes/form/edit.php');
require_once('../mooin4/classes/form/edit.php');

define('USER_SMALL_CLASS', 20);   // Below this is considered small.
define('USER_LARGE_CLASS', 200);  // Above this is considered large.
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);
define('MODE_BRIEF', 0);
define('MODE_USERDETAILS', 1);

global $DB;
global $PAGE;

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$mode         = optional_param('mode', null, PARAM_INT); // Use the MODE_ constants.
$accesssince  = optional_param('accesssince', 0, PARAM_INT); // Filter by last access. -1 = never.
$search       = optional_param('search', '', PARAM_RAW); // Make sure it is processed with p() or s() when sending to output!
$roleid       = optional_param('roleid', 0, PARAM_INT); // Optional roleid, 0 means all enrolled users (or all on the frontpage).
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('id', 0, PARAM_INT); // This are required. required_param('id', PARAM_INT); 

$urlpage = new moodle_url('/course/format/mooin4/edit.php', array('id' => $courseid ));
$PAGE->set_url($urlpage); //'/course/format/mooin4/edit', array('id' => $courseid )); //new moodle_url('/course/format/mooin4/edit-inhalt.php', array('id' => $courseid )),


// Get User Preferences
get_user_preferences();
$userPreferencesEdit = get_user_preferences('id');

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $userPreferencesEdit), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}

$courseformat = course_get_format($course);
$course_new = $courseformat->get_course();


require_login($course); // $course
$PAGE->set_course($course);
$PAGE->set_pagelayout('standard');
$PAGE->set_context(\context_course::instance($course->id));
// $PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit Inhalt');
$PAGE->set_heading('Edit Inhalt');
$PAGE->navbar->add('Edit inhalt');

$getlastsectionid = $DB->get_records('format_mooin4_chapter', array(), 'sectionid', 'sectionid', IGNORE_MISSING);
$getlastid = $DB->get_records('format_mooin4_chapter', array(), 'id', 'id', IGNORE_MISSING);

$lastsectionid = array_key_last($getlastsectionid);
$lastid = array_key_last($getlastid);

// We instaciate our form here.
$edit_form = new edit();

//Form processing and displaying is done here
$url = new moodle_url('/course/format/mooin4/inhalt.php', array('id' => $course->id));
if ($edit_form->is_cancelled()) {
    redirect($url, 'You have cancelled the creation of a new chapter');
} else if ($fromform = $edit_form->get_data()) {    
  //In this case you process validated data. $edit_form->get_data() returns data posted in form.
  var_dump($fromform);
  // Save the new creae Chapter in the format_mooin4_chapter DB
  $new_chapter = new stdClass();
  $new_chapter->id = $lastid + 1;
  $new_chapter->courseid = $userPreferencesEdit;
  $new_chapter->chapter_title = $fromform->chapter_title;
  $new_chapter->sectionid = $lastsectionid + 1;
  $new_chapter->sectionnumber = $fromform->section_number;

  // var_dump($new_chapter);
   $DB->insert_record('format_mooin4_chapter', $new_chapter);

   //unset($new_chapter);
    $new_array_text = [];
    $new_array_int = [];
    $new_array = [];
    foreach ($course_new as $key => $value) {
        if(!str_contains($key, 'divisor')){
            if($value != '' || $value != 0){
                array_push($new_array, (object)[
                    $key => $value
                ]);
            }
        }
        if(str_contains($key, 'divisor')){
            // var_dump($value);
            if($value != '' && $value != 0){
                array_push($new_array_text, (object)[
                    $key => $value
                ]);
            }
            if($value == '' || $value == 0){
                array_push($new_array_int, (object)[
                    $key => $value
                ]);            
            }
        }
    }
    $t = count($new_array_text)/2 +1;
    $b = 'divisortext' . strval($t);
    $c = 'divisor' . strval($t);
    $d = 'numsections';
    (array)$course_new->$b = $fromform->chapter_title;
    (array)$course_new->$c = $fromform->section_number;
    (array)$course_new->$d += $fromform->section_number; 

    $courseformat->update_course_format_options($course_new);
    // print_r($course_new);
    redirect($url, ' You have created a new chapter : ' . $fromform->chapter_title);
}

echo $OUTPUT->header();

$edit_form->display();

echo $OUTPUT->footer();