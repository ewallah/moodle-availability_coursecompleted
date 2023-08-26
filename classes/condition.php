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
 * @copyright 2015 iplusacademy (www.iplusacademy.org)
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
 * @copyright 2015 iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var string coursecompleted 0 => No, 1 => Yes */
    protected $coursecompleted;

    /**
     * Constructor.
     *
     * @param stdClass $structure Data structure from JSON decode
     * @throws coding_exception If invalid data.
     */
    public function __construct($structure) {
        if (!property_exists($structure, 'id')) {
            $this->coursecompleted = '';
        } else if (is_string($structure->id)) {
            $this->coursecompleted = $structure->id;
        } else {
            throw new coding_exception('Invalid value for course completed condition');
        }
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object)['type' => 'coursecompleted', 'id' => $this->coursecompleted];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $coursecompleted default empty string
     * @return stdClass Object representing condition
     */
    public static function get_json($coursecompleted = '') {
        return (object)['type' => 'coursecompleted', 'id' => $coursecompleted];
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        $completioninfo = new \completion_info($info->get_course());
        $allow = $completioninfo->is_course_complete($userid);
        unset($completioninfo);
        if (!$this->coursecompleted) {
            $allow = !$allow;
        }
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @return string Information string (for admin) about all restrictions on
     *   this item
     */
    public function get_description($full, $not, info $info) {
        $allow = $this->coursecompleted;
        if ($not) {
            $allow = !$allow;
        }
        return get_string($allow ? 'getdescription' : 'getdescriptionnot', 'availability_coursecompleted');
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        return $this->coursecompleted ? '#' . 'True' : 'False';
    }

    /**
     * Checks whether this condition applies to user lists.
     *
     * @return bool True if this condition applies to user lists
     */
    public function is_applied_to_user_lists() {
        // Course completions are assumed to be 'permanent', so they affect the
        // display of user lists for activities.
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
        \core_availability\capability_checker $checker) {

        global $DB;

        // If the array is empty already, just return it.
        if (!$users) {
            return $users;
        }

        $course = $info->get_course();
        $calc = $this->coursecompleted ? 'IS NOT NULL' : 'IS NULL';
        $compusers = $DB->get_records_sql("
                SELECT DISTINCT userid
                  FROM {course_completions}
                  WHERE course = ? AND timecompleted ?", [$course->id, $calc]);

        // List users who have access to the completion report.
        $adusers = $checker->get_users_by_capability('report/completion:view');

        // Filter the user list.
        $result = [];
        foreach ($users as $id => $user) {
            // Always include users with access to completion report.
            if (array_key_exists($id, $adusers)) {
                $result[$id] = $user;
                continue;
            }
            // Other users are included or not based on course completion.
            $allow = array_key_exists($id, $compusers);
            if ($not) {
                $allow = !$allow;
            }
            if ($allow) {
                $result[$id] = $user;
            }
        }
        return $result;
    }
}
