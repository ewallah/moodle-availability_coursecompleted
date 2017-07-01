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
 * Unit tests for the coursecompleted condition.
 *
 * @package availability_coursecompleted
 * @category   phpunit
 * @copyright 2017 eWallah.net (info@eWallah.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use availability_coursecompleted\condition;

/**
 * Unit tests for the coursecompleted condition.
 *
 * @package availability_coursecompleted
 * @category   phpunit
 * @copyright 2017 eWallah.net (info@eWallah.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_coursecompleted_condition_testcase extends advanced_testcase {

    /**
     * Load required classes.
     */
    public function setUp() {
        // Load the mock info class so that it can be used.
        global $CFG;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
        require_once($CFG->libdir . '/completionlib.php');
    }

    /**
     * Tests constructing and using coursecompleted condition as part of tree.
     */
    public function test_in_tree() {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create course with coursecompleted turned on.
        $CFG->enableavailability = true;
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => true]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $info = new \core_availability\mock_info($course, $user->id);

        $structure1 = (object)['op' => '|', 'show' => true, 'c' => [(object)['type' => 'coursecompleted', 'id' => '1']]];
        $structure2 = (object)['op' => '|', 'show' => true, 'c' => [(object)['type' => 'coursecompleted', 'id' => '0']]];
        $tree1 = new \core_availability\tree($structure1);
        $tree2 = new \core_availability\tree($structure2);

        // Initial check.
        $result1 = $tree1->check_available(false, $info, true, $user->id);
        $result2 = $tree2->check_available(false, $info, true, $user->id);
        $this->assertFalse($result1->is_available());
        $this->assertTrue($result2->is_available());

        // Change course completed.
        $ccompletion = new completion_completion(['course' => $course->id, 'userid' => $user->id]);
        $ccompletion->mark_complete();

        $result1 = $tree1->check_available(false, $info, true, $user->id);
        $result2 = $tree2->check_available(false, $info, true, $user->id);
        $this->assertTrue($result1->is_available());
        $this->assertFalse($result2->is_available());
    }

    /**
     * Tests the constructor including error conditions.
     */
    public function test_constructor() {
        // This works with no parameters.
        $structure = (object)[];
        $completed = new condition($structure);

        // This works with '1'.
        $structure->id = '1';
        $completed = new condition($structure);

        // This works with '0'.
        $structure->id = '0';
        $completed = new condition($structure);

        // This fails with null.
        $structure->id = null;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid value for course completed condition', $e->getMessage());
        }

        // Invalid ->id.
        $structure->id = false;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid value for course completed condition', $e->getMessage());
        }

        // Invalid string. Should be checked 'longer string'.
        $structure->id = 1;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid value for course completed condition', $e->getMessage());
        }
    }

    /**
     * Tests the save() function.
     */
    public function test_save() {
        $structure = (object)['id' => '1'];
        $cond = new condition($structure);
        $structure->type = 'coursecompleted';
        $this->assertEquals($structure, $cond->save());
    }

    /**
     * Tests the get_description and get_standalone_description functions.
     */
    public function test_get_description() {
        $info = new \core_availability\mock_info();
        $completed = new condition((object)['type' => 'coursecompleted', 'id' => '1']);
        $information = $completed->get_description(true, false, $info);
        $information = $completed->get_description(true, true, $info);
        $information = $completed->get_standalone_description(true, false, $info);
        $information = $completed->get_standalone_description(true, true, $info);
    }
}