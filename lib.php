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

require_once(__DIR__ . '/../../../config.php');
global $CFG;
require_once($CFG->libdir . '/adminlib.php');

function srs_import_draw_menu($cmd = 'settings')
{
    $tabs = [];

    $tabs[] = new tabobject('settings', new moodle_url('/admin/settings.php?section=tool_srs_import_settings'), get_string('settings', 'tool_srs_import'));
    $tabs[] = new tabobject('imports', new moodle_url('/admin/tool/srs_import/settings_imports.php'), get_string('imports', 'tool_srs_import'));

    $selected = $cmd;

    ob_start();
    print_tabs(array($tabs), $selected);
    $tabs = ob_get_contents();
    ob_end_clean();
    return $tabs;
}


function srs_import_config()
{
    return get_config('tool_srs_import');
}
