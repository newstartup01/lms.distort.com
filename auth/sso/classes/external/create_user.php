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

namespace auth_sso\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
/**
 * Class create_user
 *
 * @package    auth_sso
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_user extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'email' => new external_value(PARAM_TEXT, 'email'),
                'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                'idnumber' => new external_value(PARAM_TEXT, 'idnumber'),
                'username' => new external_value(PARAM_TEXT, 'username', VALUE_OPTIONAL),
            ]
        );
    }

    /**
     * Creates the user if doesn't exits.
     *
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $idnumber
     * @param string $username
     * @return array
     */
    public static function execute($email, $firstname, $lastname, $idnumber, $username = ''): array {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        self::validate_parameters(self::execute_parameters(), [
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'idnumber' => $idnumber,
            'username' => $username,
        ]);

        $userid = $DB->get_field('user', 'id', ['email' => $email], IGNORE_MISSING);
        if (!$userid) {
            $record = new \stdClass();
            $record->email = $email;
            $record->firstname = $firstname;
            $record->lastname = $lastname;
            $record->idnumber = $idnumber;
            $record->username = $username;
            $record->confirmed = 1;
            if (!empty($username)) {
                $record->username = $username;
            }
            $userid = user_create_user($record, false, false);
        }

        return [
            'userid' => $userid,
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'userid' => new external_value(PARAM_RAW, 'userid'),
            ]
        );
    }
}
