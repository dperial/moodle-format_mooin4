<?php
/**
 * @package format_mooin4
 * @author Nguefack
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// require('../../../config.php');

namespace format_mooin4\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

/* require_once('../../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once('../mooin4/lib.php'); */


 class edit extends moodleform {

    public function definition() {
        global $CFG;

        $courseconfig = get_config('moodlecourse');

        $max_section = $courseconfig->maxsections ; //52
        if (!isset($max_section) || !is_numeric($max_section)) {
            $max_section = 20;
        }
        
        $sectionmenu = array();
        for ($i = 0; $i <= $max_section; $i++) {
            $sectionmenu[$i] = "$i";
        }

        $mform = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sectionid');
        $mform->setType('sectionid', PARAM_INT);
        $mform->addElement('text', 'chapter_title', 'Chapter Title');
        $mform->setType('chapter_title', PARAM_NOTAGS);
        $mform->setDefault('chapter_title', 'Enter a chapter title');

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $key = 'chapterid';
        if (strpos($actual_link, $key) == false){
            $mform-> addElement('select', 'sectionnumber', 'Section number',
                $sectionmenu,
            );
        }
        $this->add_action_buttons($cancel = true, "Save Chapter");
    }

    //Custom validation should be added here
    function validation($data, $files){
        return array();
    }
 }