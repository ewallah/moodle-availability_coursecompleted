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
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;

use completion_info;
use core_availability\info;
use coding_exception;
use stdClass;

/**
 * Condition main class.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var bool completed Is course completed or not */
    protected bool $completed;

    /** @var int courseid 0 => Current course, 2 => course 2 ... */
    protected int $courseid = 0;

    /**
     * Constructor.
     *
     * @param stdClass $structure Data structure from JSON decode
     */
    public function __construct($structure) {
        $this->completed = property_exists($structure, 'id') && (bool)$structure->id;
        $this->courseid = property_exists($structure, 'courseid') ? $structure->courseid : 0;
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object)['type' => 'coursecompleted', 'id' => $this->completed, 'courseid' => $this->courseid];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * @param bool $completed Completed or not, default false
     * @param int $courseid Course id, default 0
     * @return stdClass Object representing condition
     */
    public static function get_json(bool $completed = false, int $courseid = 0) {
        return (object)['type' => 'coursecompleted', 'id' => $completed, 'courseid' => $courseid];
    }

    /**
     * Determines whether an item is currently available according to this availability condition.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        $cache = \cache::make('core', 'coursecompletion');
        $course = $this->courseid === 0 ? $info->get_course() : get_course($this->courseid);
        $values = $cache->get("{$userid}_{$course->id}");
        if ($values && $value = current($values)) {
            $allow = (bool)$value->timecompleted;
        } else {
            $completioninfo = new \completion_info($course);
            $allow = $completioninfo->is_course_complete($userid);
            unset($completioninfo);
        }

        if (!$this->completed) {
            $allow = !$allow;
        }

        if ($not) {
            return !$allow;
        }

        return $allow;
    }

    /**
     * Obtains a string describing this restriction (whether or not it actually applies).
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @return string Information string (for admin) about all restrictions on this item
     */
    public function get_description($full, $not, info $info) {
        $allow = $this->completed;
        if ($not) {
            $allow = !$allow;
        }

        if ($this->courseid === 0) {
            return get_string($allow ? 'getdescription' : 'getdescriptionnot', 'availability_coursecompleted');
        }

        $name = format_string(get_course($this->courseid)->shortname);
        return get_string($allow ? 'getotherdescription' : 'getotherdescriptionnot', 'availability_coursecompleted', $name);
    }

    /**
     * Obtains a representation of the options of this condition as a string for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        if ($this->courseid === 0) {
            return get_string($this->completed ? 'true' : 'false', 'mod_quiz');
        }

        $name = format_string(get_course($this->courseid)->shortname);
        return get_string($this->completed ? 'true' : 'false', 'mod_quiz') . ' ' . $name;
    }

    /**
     * Checks whether this condition applies to user lists.
     *
     * @return bool True if this condition applies to user lists
     */
    public function is_applied_to_user_lists() {
        // Course completions are assumed to be 'permanent'.
        return true;
    }

    /**
     * Tests against a user list. Users who cannot access the activity due to
     * availability restrictions will be removed from the list.
     *
     * @param array $users Array of userid => object
     * @param bool $not If tree's parent indicates it's being checked negatively
     * @param info $info Info about current context
     * @param capability_checker $checker Capability checker
     * @return array Filtered version of input array
     */
    public function filter_user_list(
        array $users,
        $not,
        \core_availability\info $info,
        \core_availability\capability_checker $checker
    ) {

        global $DB;
        $result = [];
        // If the array is not empty.
        if ($users !== []) {
            $courseid = $this->courseid === 0 ? $info->get_course()->id : $this->courseid;
            $cond = $this->completed ? 'NOT' : '';
            $sql = "SELECT DISTINCT userid
                      FROM {course_completions}
                      WHERE timecompleted IS {$cond} NULL AND course = ?";
            $compusers = $DB->get_records_sql($sql, [$courseid]);

            // List users who have access to the completion report.
            $adusers = $checker->get_users_by_capability('report/completion:view');
            // Filter the user list.
            foreach ($users as $id => $user) {
                // Always include users with access to completion report.
                if (array_key_exists($id, $adusers)) {
                    $result[$id] = $user;
                } else {
                    // Other users are included or not based on course completion.
                    $allow = array_key_exists($id, $compusers);
                    if ($not) {
                        $allow = !$allow;
                    }

                    if ($allow) {
                        $result[$id] = $user;
                    }
                }
            }
        }

        return $result;
    }
}
