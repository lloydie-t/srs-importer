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
require_once(__DIR__ . '/lib.php');

require_once(__DIR__ . '/ImportManager.php');

require_once($CFG->libdir . '/adminlib.php');
$config = srs_import_config();

admin_externalpage_setup('managemodules');

$settings = new admin_settingpage('tool_srs_import_settings', new lang_string('pluginname', 'tool_srs_import'));

$srs_imports = $DB->get_records_sql('SELECT * FROM {tool_srs_imports} order by id desc limit 10');
//$cmd = optional_param('cmd', null, PARAM_ALPHA);
$cmd = $_POST['cmd'] ?? '';

if ($cmd === 'manual_import' && confirm_sesskey()) {

    // Run the manual import
    $importManager = new ImportManager();
    $importManager->manualImport();

    echo '<div class="alert alert-success">' . get_string('manual_import_success', 'tool_srs_import') . '</div>';
}

echo $OUTPUT->header();
echo srs_import_draw_menu('tool_srs_import', 'imports');
echo '<h2>' . get_string('imports', 'tool_srs_import') . '</h2>';

echo '<form method="post" action="">';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
echo '<input type="hidden" name="cmd" value="manual_import">';
echo '<button type="submit" class="btn btn-primary mb-3">' . get_string('run_manual_import', 'tool_srs_import') . '</button>';
echo '</form>';

echo '<table class="table table-striped table-hover generaltable">';
echo '<tr>';
echo '<th class="header">' . get_string('import_id', 'tool_srs_import') . '</th>';
echo '<th class="header">' . get_string('import_time', 'tool_srs_import') . '</th>';
echo '<th class="header">' . get_string('import_type', 'tool_srs_import') . '</th>';
echo '<th class="header">' . get_string('import_changes', 'tool_srs_import') . '</th>';
echo '<th class="header">' . get_string('import_errors', 'tool_srs_import') . '</th>';
echo '</tr>';

if ($srs_imports) {
    foreach ($srs_imports as $import) {
        echo '<tr>';
        echo '<td class="cell">' . $import->id . '</td>';
        echo '<td class="cell">' . date('d/m/Y H:i:s', $import->import_time) . '</td>';
        echo '<td class="cell">' . $import->import_type . '</td>';
        echo '<td class="cell">' . $import->change_count . '</td>';
        echo '<td class="cell">' . $import->error_count . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" class="cell">' . get_string('no_imports', 'tool_srs_import') . '</td></tr>';
}
