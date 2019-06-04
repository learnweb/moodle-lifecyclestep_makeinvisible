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
 * lib for Make Invisible Step
 *
 * @package tool_lifecycle_step
 * @subpackage makeinvisible
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\step;

use tool_lifecycle\manager\process_data_manager;
use tool_lifecycle\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class makeinvisible extends libbase {

    /**
     * Stores old visibility and hides course
     *
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {
        process_data_manager::set_process_data($processid, $instanceid, 'visible', $course->visible);
        process_data_manager::set_process_data($processid, $instanceid, 'visibleold', $course->visibleold);
        course_change_visibility($course->id, false);
        return step_response::proceed();
    }

    public function rollback_course($processid, $instanceid, $course) {
        // If visibility changed, do nothing.
        if (!$course->visible && !$course->visibleold) {
            $record = new \stdClass();
            $record->id = $course->id;
            $record->visible = (bool) process_data_manager::get_process_data($processid, $instanceid, 'visible');
            $record->visibleold = (bool) process_data_manager::get_process_data($processid, $instanceid, 'visibleold');
            update_course($record);
        }
    }

    public function get_subpluginname() {
        return 'makeinvisible';
    }
}
