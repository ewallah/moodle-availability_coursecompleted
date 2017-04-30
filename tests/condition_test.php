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
 * @copyright 2017 eWallah.net (info@eWallah.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use availability_coursecompleted\condition;

/**
 * Unit tests for the coursecompleted condition.
 *
 * @package availability_coursecompleted
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
    }

    /**
     * Tests constructing and using coursecompleted condition as part of tree.
     */
    public function test_in_tree() {
        global $CFG, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create course with coursecompleted turned on and a Page.
        $CFG->enableavailability = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course([]);
        $info = new \core_availability\mock_info($course, $USER->id);

        $arr1 = ['type' => 'coursecompleted', 'id' => 1];
        $arr2 = ['type' => 'coursecompleted', 'id' => 0];
        $structure1 = (object)['op' => '|', 'show' => true, 'c' => [(object)$arr1]];
        $structure2 = (object)['op' => '|', 'show' => true, 'c' => [(object)$arr2]];
        $tree1 = new \core_availability\tree($structure1);
        $tree2 = new \core_availability\tree($structure2);

        // Initial check.
        $result1 = $tree1->check_available(false, $info, true, $USER->id);
        $result2 = $tree2->check_available(false, $info, true, $USER->id);
        $this->assertTrue($result1->is_available());
        $this->assertFalse($result2->is_available());

        // Change course completed.
        $result1 = $tree1->check_available(false, $info, true, $USER->id);
        $result2 = $tree2->check_available(false, $info, true, $USER->id);
        $this->assertFalse($result1->is_available());
        $this->assertTrue($result2->is_available());
    }

    /**
     * Tests the constructor including error conditions.
     */
    public function test_constructor() {
        // This works with no parameters.
        $structure = (object)[];
        $completed = new condition($structure);

        // This works with true.
        $structure->id = true;
        $completed = new condition($structure);

        // This works with false.
        $structure->id = false;
        $completed = new condition($structure);

        // Invalid ->id.
        $structure->id = null;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid ->id for coursecompleted condition', $e->getMessage());
        }

        // Invalid string.
        $structure->id = 'completed';
        try {
            $language = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid ->id for coursecompleted condition', $e->getMessage());
        }
    }

    /**
     * Tests the save() function.
     */
    public function test_save() {
        $structure = (object)['id' => 1];
        $cond = new condition($structure);
        $structure->type = 'coursecompleted';
        $this->assertEquals($structure, $cond->save());
    }

    /**
     * Tests the get_description and get_standalone_description functions.
     */
    public function test_get_description() {
        $info = new \core_availability\mock_info();
        $completed = new condition((object)['type' => 'coursecompleted', 'id' => 1]);
        $information = $completed->get_description(true, false, $info);
        $information = $completed->get_description(true, true, $info);
        $information = $completed->get_standalone_description(true, false, $info);
        $information = $completed->get_standalone_description(true, true, $info);
    }
}