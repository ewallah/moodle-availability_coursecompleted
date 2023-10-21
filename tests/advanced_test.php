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
 * @package   availability_coursecompleted
 * @copyright 2017 iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;


use availability_coursecompleted\{condition, frontend};
use completion_info;
use core_availability\{tree, info_module, capability_checker};
use core_completion;

/**
 * Unit tests for the coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright 2017 iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \availability_coursecompleted
 */
class advanced_test extends \advanced_testcase {

    /** @var stdClass course. */
    private $course;

    /** @var stdClass cm. */
    private $cm;

    /** @var int userid. */
    private $userid;

    /** @var int compid. */
    private $compid;

    /** @var int teacherid. */
    private $teacherid;

    /**
     * Create course and page.
     */
    public function setUp():void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info_module.php');
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        set_config('enableavailability', true);
        $dg = $this->getDataGenerator();
        $this->course = $dg->create_course(['enablecompletion' => 1]);
        $this->userid = $dg->create_user()->id;
        $this->compid = $dg->create_user()->id;
        $this->teacherid = $dg->create_user()->id;
        $role = $DB->get_field('role', 'id', ['shortname' => 'student']);
        $dg->enrol_user($this->userid, $this->course->id, $role);
        $dg->enrol_user($this->compid, $this->course->id, $role);
        $role = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
        $dg->enrol_user($this->teacherid, $this->course->id, $role);
        $feedback = $dg->get_plugin_generator('mod_feedback')->create_instance(['course' => $this->course]);
        $this->cm = get_fast_modinfo($this->course)->get_cm($feedback->cmid);
        $ccompletion = new \completion_completion(['course' => $this->course->id, 'userid' => $this->compid]);
        $ccompletion->mark_complete();
        rebuild_course_cache($this->course->id, true);
    }

    /**
     * Tests constructing and using coursecompleted condition as part of tree.
     * @covers \availability_coursecompleted\condition
     */
    public function test_tree(): void {
        $info1 = new \core_availability\mock_info($this->course, $this->userid);
        $info2 = new \core_availability\mock_info($this->course, $this->compid);

        $structure1 = (object)['op' => '|', 'show' => true, 'c' => [(object)['type' => 'coursecompleted', 'id' => '1']]];
        $structure2 = (object)['op' => '|', 'show' => true, 'c' => [(object)['type' => 'coursecompleted', 'id' => '0']]];
        $tree1 = new tree($structure1);
        $tree2 = new tree($structure2);

        $this->setuser($this->compid);
        $this->assertTrue($tree1->check_available(false, $info2, true, $this->compid)->is_available());
        $this->assertFalse($tree2->check_available(false, $info2, true, $this->compid)->is_available());

        $this->setuser($this->userid);
        $this->assertFalse($tree1->check_available(false, $info1, true, $this->userid)->is_available());
        $this->assertTrue($tree2->check_available(false, $info1, true, $this->userid)->is_available());
    }

    /**
     * Tests the get_description and get_standalone_description functions.
     * @covers \availability_coursecompleted\condition
     * @covers \availability_coursecompleted\frontend
     */
    public function test_get_description(): void {
        $nau = 'Not available unless: ';
        $sections = get_fast_modinfo($this->course)->get_section_info_all();

        $frontend = new frontend();
        $name = 'availability_coursecompleted\frontend';
        $this->assertFalse(\phpunit_util::call_internal_method($frontend, 'allow_add', [$this->course], $name));

        $data = (object) ['id' => $this->course->id, 'criteria_activity' => [$this->cm->id => 1]];
        $criterion = new \completion_criteria_activity();
        $criterion->update_config($data);
        $this->assertTrue(\phpunit_util::call_internal_method($frontend, 'allow_add', [$this->course], $name));
        $this->assertTrue(\phpunit_util::call_internal_method($frontend, 'allow_add', [$this->course, null, $sections[0]], $name));
        $this->assertTrue(\phpunit_util::call_internal_method($frontend, 'allow_add', [$this->course, null, $sections[1]], $name));

        $info = new \core_availability\mock_info_module($this->userid, $this->cm);
        $completed = new condition((object)['type' => 'coursecompleted', 'id' => '1']);
        $information = $completed->get_description(true, false, $info);
        $this->assertEquals($information, get_string('getdescription', 'availability_coursecompleted'));
        $information = $completed->get_description(true, true, $info);
        $this->assertEquals($information, get_string('getdescriptionnot', 'availability_coursecompleted'));
        $information = $completed->get_standalone_description(true, false, $info);
        $this->assertEquals($information, $nau . get_string('getdescription', 'availability_coursecompleted'));
        $information = $completed->get_standalone_description(true, true, $info);
        $this->assertEquals($information, $nau . get_string('getdescriptionnot', 'availability_coursecompleted'));

        $completed = new condition((object)['type' => 'coursecompleted', 'id' => '0']);
        $information = $completed->get_description(true, false, $info);
        $this->assertEquals($information, get_string('getdescriptionnot', 'availability_coursecompleted'));
        $information = $completed->get_description(true, true, $info);
        $this->assertEquals($information, get_string('getdescription', 'availability_coursecompleted'));
        $information = $completed->get_standalone_description(true, false, $info);
        $this->assertEquals($information, $nau . get_string('getdescriptionnot', 'availability_coursecompleted'));
        $information = $completed->get_standalone_description(true, true, $info);
        $this->assertEquals($information, $nau . get_string('getdescription', 'availability_coursecompleted'));
    }

    /**
     * Tests is aplied to user lists.
     * @covers \availability_coursecompleted\condition
     */
    public function test_is_applied_to_user_lists(): void {
        $info = new \core_availability\mock_info_module($this->userid, $this->cm);
        $cond = new condition((object)['type' => 'coursecompleted', 'id' => '1']);
        $this->assertTrue($cond->is_applied_to_user_lists());

        $checker = new \core_availability\capability_checker(\context_course::instance($this->course->id));
        $arr = [
            $this->userid => \core_user::get_user($this->userid),
            $this->compid => \core_user::get_user($this->compid),
            $this->teacherid => \core_user::get_user($this->teacherid), ];

        $result = $cond->filter_user_list([], true, $info, $checker);
        $this->assertEquals([], $result);

        $result = $cond->filter_user_list($arr, true, $info, $checker);
        $this->assertArrayHasKey($this->userid, $result);
        $this->assertArrayNotHasKey($this->compid, $result);
        $this->assertArrayHasKey($this->teacherid, $result);

        $result = $cond->filter_user_list($arr, false, $info, $checker);
        $this->assertArrayHasKey($this->teacherid, $result);
        $this->assertArrayHasKey($this->compid, $result);
        $this->assertArrayNotHasKey($this->userid, $result);
    }

    /**
     * Tests a page before and after completion.
     * @covers \availability_coursecompleted\condition
     * @covers \availability_coursecompleted\frontend
     */
    public function test_page(): void {
        $info = new info_module($this->cm);
        $cond = new condition((object)['type' => 'coursecompleted', 'id' => '1']);
        $this->assertFalse($cond->is_available(false, $info, true, $this->userid));
        $this->assertFalse($cond->is_available(false, $info, false, $this->userid));
        $this->assertTrue($cond->is_available(true, $info, false, $this->userid));
        $this->assertTrue($cond->is_available(true, $info, true, $this->userid));

        $ccompletion = new \completion_completion(['course' => $this->course->id, 'userid' => $this->userid]);
        $ccompletion->mark_complete();
        $this->assertTrue($cond->is_available(false, $info, true, $this->userid));
        $this->assertTrue($cond->is_available(false, $info, false, $this->userid));
        $this->assertFalse($cond->is_available(true, $info, false, $this->userid));
        $this->assertFalse($cond->is_available(true, $info, true, $this->userid));

        // No id.
        $cond = new condition((object)['type' => 'coursecompleted']);
        $this->assertFalse($cond->is_available(false, $info, false, $this->userid));
        $this->assertFalse($cond->is_available_for_all());
        $this->assertFalse($cond->update_dependency_id(null, 1, 2));
        $this->assertEquals($cond->__toString(), '{coursecompleted:False}');
        $this->assertEquals($cond->get_standalone_description(true, true, $info),
            'Not available unless: You completed this course.');
    }
}
