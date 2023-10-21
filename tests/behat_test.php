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
 * Unit tests for the behat coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright 2017 iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;

/**
 * Unit tests for the behat coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright 2017 iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \availability_coursecompleted
 */
class behat_test extends \advanced_testcase {

    /**
     * Test behat funcs
     * @covers \behat_availability_coursecompleted
     */
    public function test_behat(): void {
        global $CFG;
        require_once($CFG->dirroot . '/availability/condition/coursecompleted/tests/behat/behat_availability_coursecompleted.php');
        $class = new \behat_availability_coursecompleted();
        $this->resetAfterTest();
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        set_config('enableavailability', true);
        $dg = $this->getDataGenerator();
        $course = $dg->create_course(['enablecompletion' => 1]);
        $user = $dg->create_user();
        $class->i_mark_course_completed_for_user($course->fullname, $user->username);
        $this->expectExceptionMessage("A user with username 'otheruser' does not exist");
        $class->i_mark_course_completed_for_user($course->fullname, 'otheruser');
    }
}
