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

//defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    require_once(__DIR__ . '/lib.php');

    $config = srs_import_config();

    $settings = new admin_settingpage('tool_srs_import_settings', new lang_string('pluginname', 'tool_srs_import'));

    if ($ADMIN->fulltree) {

        $tabmenu = srs_import_draw_menu('settings');

        //$version = empty($module->version) ? $module->version_disk : $module->version;

        $version = '0.1.0';

        $desc = '(' . get_string('moduleversion', 'tool_srs_import') . ': ' . $version . ')';

        $settings->add(new admin_setting_heading('tool_srs_import_settings', $desc, $tabmenu));

        $settings->add(new admin_setting_configtext(
            'tool_srs_import/endpoint',
            new lang_string('endpoint', 'tool_srs_import'),
            new lang_string('endpoint_desc', 'tool_srs_import'),
            '',
            PARAM_RAW
        ));

        $settings->add(new admin_setting_configtext(
            'tool_srs_import/endpoint_key',
            new lang_string('endpoint_key', 'tool_srs_import'),
            new lang_string('endpoint_key_desc', 'tool_srs_import'),
            '',
            PARAM_RAW
        ));
    }

    $ADMIN->add('tools', $settings);
}
