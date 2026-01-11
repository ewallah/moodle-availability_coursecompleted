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
 * Basic unit tests for the coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;

use availability_coursecompleted\{condition, frontend};
use completion_info;
use core_availability\{tree, info_module, mock_info, mock_condition};
use core_completion;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Bare tests for the coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(condition::class)]
final class basic_test extends \basic_testcase {
    /**
     * Tests the constructor including error conditions.
     */
    public function test_constructor(): void {
        // This works with no parameters.
        $structure = (object)[];
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (\exception $exception) {
            $this->assertEquals('', $exception->getMessage());
        }

        $this->assertNotEmpty($completed);

        // This works with '1'.
        $structure->id = '1';
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (\exception $exception) {
            $this->assertEquals('', $exception->getMessage());
        }

        // This works with an integer.
        $structure->id = 1;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (\exception $exception) {
            $this->assertEquals('', $exception->getMessage());
        }

        $this->assertNotEmpty($completed);

        // This works with a course.
        $structure->id = 0;
        $structure->courseid = 33;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (\exception $exception) {
            $this->assertEquals('', $exception->getMessage());
        }

        $this->assertNotEmpty($completed);

        // This does not fail with null.
        $structure->id = null;
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (\exception $exception) {
            $this->assertEquals('', $exception->getMessage());
        }

        // Works with courseid string.
        $structure->id = false;
        $structure->courseid = '22';
        try {
            $completed = new condition($structure);
            $this->fail();
        } catch (\exception $exception) {
            $this->assertEquals('', $exception->getMessage());
        }
    }

    /**
     * Tests the save() function.
     */
    public function test_save(): void {
        $structure = (object)['id' => '1', 'courseid' => '33'];
        $cond = new condition($structure);
        $structure->type = 'coursecompleted';
        $this->assertEquals($structure, $cond->save());

        $structure = (object)['id' => true, 'courseid' => 33];
        $cond = new condition($structure);
        $structure->type = 'coursecompleted';
        $this->assertEquals($structure, $cond->save());
    }

    /**
     * Tests json.
     */
    public function test_json(): void {
        $thing = (object)['type' => 'coursecompleted', 'id' => true, 'courseid' => 0];
        $this->assertEqualsCanonicalizing($thing, condition::get_json(true));
        $thing = (object)['type' => 'coursecompleted', 'id' => false, 'courseid' => 0];
        $this->assertEqualsCanonicalizing($thing, condition::get_json());
        $this->assertEqualsCanonicalizing($thing, condition::get_json(false));
        $thing = (object)['type' => 'coursecompleted', 'id' => true, 'courseid' => 1];
        $this->assertEqualsCanonicalizing($thing, condition::get_json(true, 1));
        $thing = (object)['type' => 'coursecompleted', 'id' => false, 'courseid' => 2];
        $this->assertEqualsCanonicalizing($thing, condition::get_json(false, 2));
        $thing = (object)['type' => 'coursecompleted', 'id' => true, 'courseid' => 2];
        $this->assertEqualsCanonicalizing($thing, condition::get_json('2', '2'));
        $thing = (object)['type' => 'coursecompleted', 'id' => false, 'courseid' => 0];
        $this->assertEqualsCanonicalizing($thing, condition::get_json('', '0'));
    }

    /**
     * Test debug string.
     */
    public function test_debug(): void {
        $name = 'availability_coursecompleted\condition';
        $condition = new condition((object)['type' => 'coursecompleted', 'id' => false]);
        $this->assertEquals('False', \phpunit_util::call_internal_method($condition, 'get_debug_string', [], $name));
        $condition = new condition((object)['type' => 'coursecompleted', 'id' => true]);
        $this->assertEquals('True', \phpunit_util::call_internal_method($condition, 'get_debug_string', [], $name));
        $condition = new condition((object)['type' => 'coursecompleted', 'id' => true, 'courseid' => 1]);
        $this->assertEquals('True phpunit', \phpunit_util::call_internal_method($condition, 'get_debug_string', [], $name));
    }
}
