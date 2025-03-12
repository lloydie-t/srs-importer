<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     tool_srs_import
 * @category    admin
 * @copyright   2025 Lloyd Thomas lloydie.t@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_srs_import\task;

require_once(__DIR__ . '../../../ImportManager.php');

class student_import extends \core\task\scheduled_task
{


    /**
     * Task name.
     */
    public function get_name()
    {
        return get_string('taskstudentimport', 'tool_srs_import');
    }

    /**
     * Import new students.
     */
    public function execute()
    {

        $importManager = new ImportManager();
        $importManager->cronImport();

        return true;
    }
}
