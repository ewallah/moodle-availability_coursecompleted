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
 * Backup tests for the coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_coursecompleted;

use availability_coursecompleted\{condition, frontend};
use completion_info;
use core_availability\{tree, info_module, capability_checker};
use core_completion;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Backup tests for the coursecompleted condition.
 *
 * @package   availability_coursecompleted
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(condition::class)]
#[CoversClass(frontend::class)]
final class backup_test extends \advanced_testcase {
    /** @var stdClass course. */
    private $course;

    /**
     * Setup.
     */
    public function setUp(): void {
        global $CFG;
        parent::setUp();
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;

        $dg = $this->getDataGenerator();
        $this->course = $dg->create_course(['enablecompletion' => 1]);
    }

    /**
     * Backup course check.
     */
    public function test_backup_course(): void {
        global $CFG, $DB;
        $dg = $this->getDataGenerator();
        $pg = $dg->get_plugin_generator('mod_page');
        $page = $pg->create_instance(['course' => $this->course, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $str = '{"op":"|","show":true,"c":[{"type":"coursecompleted","id":0,"courseid":0}]}';
        $DB->set_field('course_modules', 'availability', $str, ['id' => $page->cmid]);
        rebuild_course_cache($this->course->id, true);

        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $this->course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            2
        );
        $bc->execute_plan();

        $results = $bc->get_results();
        $file = $results['backup_destination'];
        $fp = get_file_packer('application/vnd.moodle.backup');
        $filepath = $CFG->dataroot . '/temp/backup/test-restore-course-event';
        $file->extract_to_pathname($fp, $filepath);
        $bc->destroy();

        $newcourse = $dg->create_course(['enablecompletion' => 1]);
        $rc = new \restore_controller(
            'test-restore-course-event',
            $newcourse->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            2,
            \backup::TARGET_NEW_COURSE
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        $modinfo = get_fast_modinfo($newcourse);
        $pages = $modinfo->get_instances_of('page');
        $this->assertCount(1, $pages);
        $arr = [];
        foreach ($pages as $page) {
            if ($page->availability) {
                $arr[] = $page->availability;
            }
        }

        $this->assertStringContainsString('[{"type":"coursecompleted","id":0,"courseid":0', $arr[0]);
    }

    /*
     * Backup same course.
     */
    public function test_backup_same_course(): void {
        global $CFG, $DB;
        $dg = $this->getDataGenerator();
        $course2id = $dg->create_course(['enablecompletion' => 1])->id;
        $pg = $dg->get_plugin_generator('mod_page');
        $page = $pg->create_instance(['course' => $this->course, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $str = '{"op":"|","show":true,"c":[{"type":"coursecompleted","id":1,"courseid":' . $course2id . '}]}';
        $DB->set_field('course_modules', 'availability', $str, ['id' => $page->cmid]);

        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $this->course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            2
        );
        $bc->execute_plan();

        $results = $bc->get_results();
        $file = $results['backup_destination'];
        $fp = get_file_packer('application/vnd.moodle.backup');
        $filepath = $CFG->dataroot . '/temp/backup/test-restore-course-event';
        $file->extract_to_pathname($fp, $filepath);
        $bc->destroy();

        $rc = new \restore_controller(
            'test-restore-course-event',
            $this->course->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            2,
            \backup::TARGET_CURRENT_ADDING
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        $modinfo = get_fast_modinfo($this->course);
        $pages = $modinfo->get_instances_of('page');
        $this->assertCount(2, $pages);
        $arr = [];
        foreach ($pages as $page) {
            if (!is_null($page->availability)) {
                $arr[] = $page->availability;
            }
        }

        $this->assertStringContainsString('[{"type":"coursecompleted","id":1,"courseid":', $arr[0]);
        $this->assertStringContainsString($course2id, $arr[0]);
    }
}
