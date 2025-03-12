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


use tool_srs_import\local\srsimport\Curl;
use \Exception;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../config.php');
require_once __DIR__ . '/classes/local/srsimport/Curl.php';


class ImportManager
{
    const PLUGINNAME = 'tool_srs_import';



    /**
     * Import data from SRS
     * 
     * @param string $type
     * @return bool
     */
    public function importDataFromSRS($type = 'auto'): bool
    {
        $apiUrl = get_config('tool_srs_import', 'endpoint');
        $apiKey = get_config('tool_srs_import', 'endpoint_key');

        // Initialize Curl instance
        $curl = new Curl();

        global $DB;

        $last_import = $DB->get_record_sql("SELECT * FROM {tool_srs_imports} order by id desc limit 1");
        $last_import_time = $last_import->import_time ?? 0;

        $url = $apiUrl . '?last_import=' . $last_import_time;

        $data = $curl->get($url, ['Authorization: Bearer ' . $apiKey]);


        // Get the response
        try {
            if ($data) {
                $response = json_decode($data, true);

                $import_id = $this->updateimports([]);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo 'Error decoding JSON: ' . json_last_error_msg();
                    return false;
                }
                $this->importStudents($response, $import_id, $type);
            }
        } catch (Exception $e) {
            echo 'Error during import: ' . $e->getMessage();
            return false;
        }

        return true;
    }


    /**
     * Import students
     * 
     * @param array $data
     * @param stdClass $import
     * @param string $type
     */
    protected function importStudents($data, $import_id, $type = 'auto'): void
    {
        global $DB;

        $imported_count = 0;
        $error_count = 0;

        // Loop through data and insert into table
        foreach ($data as $student) {
            try {

                $studentExists = $DB->get_record('tool_srs_import_students', ['srs_id' => $student['srs_id']]);

                if ($studentExists) {
                    $this->upsertStudent($student, 'update');
                    $this->importMoodleUser($student);
                } else {
                    $this->upsertStudent($student);
                    $this->importMoodleUser($student);
                }

                $imported_count++;
            } catch (Exception $e) {
                $DB->insert_record('tool_srs_import_errors', ['error_text' => 'import error: ' . $e->getMessage(), 'import_srs_id' => $import_id]);
                $error_count++;
            }
        }

        $import = $DB->get_record('tool_srs_imports', ['id' => $import_id]);
        $import->import_time = time();
        $import->import_type = $type;
        $import->error_count = $error_count;
        $import->change_count = $imported_count;
        $DB->update_record('tool_srs_imports', $import);
    }


    /**
     * Upsert student record
     * 
     * @param array $data
     * @param string $method
     * 
     * @return bool
     */

    protected  function upsertStudent($data, $method = 'insert'): bool
    {
        global $DB;

        $record = new \stdClass();
        $record->srs_id = $data['srs_id'];
        $record->firstname = $data['firstname'];
        $record->surname = $data['surname'];
        $record->preferred_name = $data['preferred_name'] ?? null;
        $record->title = $data['title'] ?? null;
        $record->dob = $this->convertSqltimeToUnix($data['dob']);
        $record->sex = $data['sex'];
        $record->pronoun = $data['pronoun'];
        $record->marital_status = $data['marital_status'];
        $record->email = $data['email'];
        $record->phone = $data['phone'];
        $record->address = $data['address'];
        $record->city = $data['city'];
        $record->postcode = $data['postcode'];
        $record->country = $data['country'];
        $record->nationality = $data['nationality'];
        $record->ethnicity = $data['ethnicity'];
        $record->religion = $data['religion'];
        $record->course_id = $data['course_id'];
        $record->cohort = $data['cohort'];
        $record->enrolled = $data['enrolled'] ? $this->convertSqltimeToUnix($data['enrolled']) : null;
        $record->graduated = $data['graduated'] ? $this->convertSqltimeToUnix($data['graduated']) : null;
        $record->withdrawn = $data['withdrawn'] ? $this->convertSqltimeToUnix($data['withdrawn']) : null;
        $record->termination = $data['termination'] ? $this->convertSqltimeToUnix($data['termination']) : null;
        $record->suspended = $data['suspended'] ? $this->convertSqltimeToUnix($data['suspended']) : null;
        $record->status = $data['status'];
        $record->tutor = $data['tutor'] ?? null;
        $record->updated_at = $this->convertSqltimeToUnix($data['updated_at']) ?? time();

        try {
            if ($method === 'insert') {
                $record->created_at = $this->convertSqltimeToUnix($data['created_at']) ?? time();
                $DB->insert_record('tool_srs_import_students', $record);
            } else {
                $DB->update_record('tool_srs_import_students', $record, ['srs_id' => $record->srs_id]);
            }
            return true;
        } catch (Exception $e) {
            $DB->insert_record('tool_srs_import_errors', 'student import error: ' . $e->getMessage());
            echo 'Error importing student: ' . $e->getMessage();
        }

        return false;
    }


    /**
     * Import Moodle user
     * 
     * @param array $data
     */
    protected  function importMoodleUser($data): void
    {
        global $DB;

        $userExists = $DB->get_record('user', ['username' => $data['srs_id']]);

        if (!empty($userExists)) {
            $this->upsertMoodleUser($data, 'update');
        } else {
            $this->upsertMoodleUser($data);
        }
    }

    /**
     * Upsert Moodle user
     * 
     * @param array $data
     * @param string $method
     * 
     * @return void
     */
    protected  function upsertMoodleUser($data, $method = 'insert'): void
    {
        global $DB;

        try {

            //set cohort


            //Set user data
            if ($method === 'update') {
                $user = $DB->get_record('user', ['username' => $data['srs_id']]);
                $user->firstname = $data['firstname'];
                $user->lastname = $data['surname'];
                $user->email = $data['email'];
                $user->phone1 = $data['phone'];
                $user->address = $data['address'];
                $user->city = $data['city'];
                $user->timemodified = time();
                $DB->update_record('user', $user, ['username' => $data['srs_id']]);
            } else {
                $user = new \stdClass();
                $user->username = $data['srs_id'];
                $user->firstname = $data['firstname'];
                $user->lastname = $data['surname'];
                $user->email = $data['email'];
                $user->phone1 = $data['phone'];
                $user->address = $data['address'];
                $user->city = $data['city'];
                $user->timemodified = time();
                $user->timecreated = time();
                $user->id = $DB->insert_record('user', $user);
            }

            $new_user = $user;

            $cohort = $this->upsertCohorts($data['cohort']);

            //Add user to cohort
            if ($cohort) {
                $cohort_member = $DB->get_record('cohort_members', ['cohortid' => $cohort->id, 'userid' => $new_user->id]);

                if (empty($cohort_member) && $cohort->id && $new_user->id) {
                    $cohort_member = new \stdClass();
                    $cohort_member->cohortid = $cohort->id;
                    $cohort_member->userid = $new_user->id;
                    $DB->insert_record('cohort_members', $cohort_member);
                }
            }

            // // Assign student role to user
            $role = $DB->get_record('role', ['shortname' => 'student']);
            $role_assign = $DB->get_record('role_assignments', ['userid' => $new_user->id, 'roleid' => $role->id]);

            if (empty($role_assign) && $new_user->id) {
                $role_assign = new \stdClass();
                $role_assign->roleid = $role->id;
                $role_assign->userid = $new_user->id;
                $role_assign->contextid = 1;
                $role_assign->contextlevel = 50;
                $role_assign->instanceid = 0;
                $role_assign->timemodified = time();
                $DB->insert_record('role_assignments', $role_assign);
            }
        } catch (Exception $e) {
            $DB->insert_record('tool_srs_import_errors', 'Moodle user import error: ' . $e->getMessage());
            echo 'Error importing student: ' . $e->getMessage();
        }
    }


    protected function upsertCohorts($cohort_name = ''): stdClass
    {
        global $DB;

        try {
            if (!empty($cohort_name)) {
                $cohort = $DB->get_record('cohort', ['name' => $cohort_name]);
                if (!empty($cohort)) {
                    return $cohort;
                } else {
                    $record = new \stdClass();
                    $record->name = $cohort_name;
                    $record->contextid = 1;
                    $record->idnumber = $cohort_name . '1';
                    $record->description = $cohort_name;
                    $record->descriptionformat = 1;
                    $record->timecreated = time();
                    $record->timemodified = time();
                    $record->id = $DB->insert_record('cohort', $record);
                    return $record;
                }
            }
        } catch (Exception $e) {
            $DB->insert_record('tool_srs_import_errors', 'cohort import error: ' . $e->getMessage());
            echo 'Error importing cohort: ' . $e->getMessage();
            return new \stdClass();
        }

        return new \stdClass();
    }


    /**
     * Upsert import record
     * 
     * 
     * @return stdClass
     */
    protected  function updateImports($data)
    {
        global $DB;

        if (isset($data->id)) {
            $record = new \stdClass();
            $record->import_time  = time();
            $record->error_count = $data->change_count ?? 0;
            $record->change_count = $data->error_count ?? 0;
            $record->import_type = $data->import_type ?? 'auto';
            $DB->update_record('tool_srs_imports', $record, ['id' => $data->id]);
            return $record;
        } else {
            $record = new \stdClass();
            $record->import_time = time();
            $record->error_count = 0;
            $record->change_count = 0;
            return $DB->insert_record('tool_srs_imports', $record);
        }
    }


    /**
     * Validate student data
     * 
     * @param array $data
     * @return bool
     */
    protected  function validateStudent($data): bool
    {
        // Validate student data
        if (empty($data['name']) || filter_var($data['name'], FILTER_VALIDATE_INT) === false) {
            return false;
        }

        if (empty($data['name']) || empty($data['age']) || empty($data['grade'])) {
            return false;
        }

        return true;
    }


    protected function getImportRecords(): array
    {
        global $DB;

        $limitfrom = 0;
        $limitnum = 10;
        return $DB->get_records_select('tool_srs_imports', '', null, '', '*', $limitfrom, $limitnum);
    }

    /**
     * Convert SQL time to Unix
     * 
     * @param string $time
     * @return int
     */
    protected function convertSqltimeToUnix($time): int
    {
        return strtotime($time);
    }

    public function cronImport(): void
    {
        $this->importDataFromSRS();
    }

    public function manualImport(): void
    {
        $this->importDataFromSRS('manual');
    }
}
