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
 * Front-end class.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;

use cm_info;
use completion_info;
use section_info;
use stdClass;

/**
 * Front-end class.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    /**
     * Decides whether this plugin should be available in a given course.
     *
     * @param stdClass $course Course object
     * @param cm_info|null $cm Course-module currently being edited (null if none)
     * @param section_info|null $section Section currently being edited (null if Course object)
     * @return bool True if there are completion criteria
     */
    protected function allow_add($course, ?cm_info $cm = null, ?section_info $section = null) {
        global $DB, $USER;
        $courses = enrol_get_users_courses($USER->id, true, 'id, enablecompletion');
        foreach ($courses as $course) {
            if ($course->enablecompletion == 1) {
                if ($DB->record_exists('course_completion_criteria', ['course' => $course->id])) {
                    return true;
                }
            }
        }

        return is_siteadmin();
    }

    /**
     * Get JavaScript initialization parameters.
     *
     * @param stdClass $currentcourse The course object.
     * @param cm_info|null $cm The course module info.
     * @param section_info|null $section The section info.
     * @return array The JavaScript initialization parameters.
     */
    protected function get_javascript_init_params($currentcourse, ?cm_info $cm = null, ?section_info $section = null) {
        global $USER;
        $courses = is_siteadmin() ? get_courses() : enrol_get_users_courses($USER->id, true, 'id, enablecompletion');
        $arr = [];
        foreach ($courses as $course) {
            if ($course->enablecompletion == 1) {
                if ($course->id == $currentcourse->id) {
                    $arr[] = ['id' => 0, 'name' => get_string('currentcourse')];
                } else {
                    $completioninfo = new \completion_info($course);
                    if ($completioninfo->has_criteria()) {
                        $arr[] = ['id' => $course->id, 'name' => format_string($course->shortname)];
                    }

                    unset($completioninfo);
                }
            }
        }

        return [$arr];
    }

    /**
     * Gets a list of string identifiers
     *
     * @return array Array of required string identifiers
     */
    protected function get_javascript_strings() {
        return ['title', 'select'];
    }
}
