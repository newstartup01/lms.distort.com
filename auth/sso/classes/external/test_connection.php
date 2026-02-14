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

use auth_sso\helper as sso_helper;

/**
 * Class test_connection
 *
 * @package    auth_sso
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_connection extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'encryptionkey'    => new external_value(PARAM_TEXT, 'encryptionkey'),
            ]
        );
    }

    /**
     * Validates the encryption key and returns the response.
     *
     * @param string $encryptionkey
     * @return array
     */
    public static function execute(string $encryptionkey): array {
        self::validate_parameters(self::execute_parameters(), [
            'encryptionkey' => $encryptionkey,
        ]);
        $iskeyvalid = sso_helper::is_encryption_key_valid($encryptionkey);

        return [
            'valid' => $iskeyvalid,
            'message' => $iskeyvalid
                ? get_string('connectionsuccessful', 'auth_sso')
                : get_string('invalidencryptionkey', 'auth_sso'),
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
                'valid' => new external_value(PARAM_BOOL, 'valid'),
                'message' => new external_value(PARAM_RAW, 'message'),
            ]
        );
    }

}
