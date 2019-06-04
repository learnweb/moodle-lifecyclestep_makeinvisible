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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../tests/generator/lib.php');

use tool_lifecycle\manager\process_manager;
use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\processor;

/**
 * Tests the field is manual after activating workflows.
 *
 * @package    lifecyclestep_makeinvisible
 * @group      lifecyclestep_makeinvisible
 * @category   test
 * @copyright  2019 Justus Dieckmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class make_invisible_testcase extends \advanced_testcase {

    public function test_make_invisible() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $workflow = $generator->create_workflow([], []);
        $trigger = $generator->create_trigger('manual', 'manual', $workflow->id);
        $generator->create_step('makeinvisible', 'makeinvisible', $workflow->id);
        $step = $generator->create_step('email', 'email', $workflow->id);
        settings_manager::save_settings($step->id, SETTINGS_TYPE_STEP, 'email', null);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $workflow->id);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        course_change_visibility($course2->id, false);

        $process1 = process_manager::manually_trigger_process($course1->id, $trigger->id);
        $process2 = process_manager::manually_trigger_process($course2->id, $trigger->id);

        $processor = new processor();
        $processor->process_courses();

        $course1 = get_course($course1->id);
        $course2 = get_course($course2->id);

        $this->assertFalse((bool) $course1->visible);
        $this->assertFalse((bool) $course2->visible);

        $process1 = process_manager::get_process_by_id($process1->id);
        $process2 = process_manager::get_process_by_id($process2->id);
        process_manager::rollback_process($process1);
        process_manager::rollback_process($process2);

        $course1 = get_course($course1->id);
        $course2 = get_course($course2->id);

        $this->assertTrue((bool) $course1->visible);
        $this->assertFalse((bool) $course2->visible);
    }

}