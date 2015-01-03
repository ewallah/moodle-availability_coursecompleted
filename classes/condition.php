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
 * Condition main class.
 *
 * @package availability_coursecompleted
 * @copyright 2015 iplusacademy (www.iplusacademy.org)
 * @developped by Renaat Debleu {info@eWallah.net}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/completionlib.php');

/**
 * Condition main class.
 *
 * @package availability_coursecompleted
 * @copyright 2015 iplusacademy (www.iplusacademy.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var string coursecompleted 0 => No, 1 => Yes */
    protected $coursecompleted;

    /**
     * Constructor.
     *
     * @param \int $value from JSON decode
     * @throws \coding_exception If invalid data.
     */
    public function __construct($structure) {
        if (!property_exists($structure, 'id')) {
            $this->coursecompleted = '';
        } else if (is_string($structure->id)) {
            $this->coursecompleted = $structure->id;
        } else {
            throw new \coding_exception('Invalid value for course completed condition');
        }
    }

    public function save() {
        $result = (object)array('type' => 'coursecompleted');
        if ($this->coursecompleted) {
            $result->id = $this->coursecompleted;
        }
        return $result;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $course = $info->get_course();
        $completioninfo = new \completion_info($course);
        $allow = $completioninfo->is_course_complete($userid);
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        if ($this->coursecompleted == '') {
            return '';
        }
        $available = $this->coursecompleted;
        if ($not) {
            $available = !$available;
        }
        if ($available) {
            return get_string('getdescription', 'availability_coursecompleted');
        }
        return get_string('getdescriptionnot', 'availability_coursecompleted');
    }

    protected function get_debug_string() {
        return $this->coursecompleted ? '#' . 'True' : 'False';
    }
}