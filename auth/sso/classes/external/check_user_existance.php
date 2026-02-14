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
 * Class check_user_existance
 *
 * @package    auth_sso
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_user_existance extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'identifier'    => new external_value(PARAM_TEXT, 'identifier'),
                'value' => new external_value(PARAM_TEXT, 'value'),
            ]
        );
    }

    /**
     * Validates the encryption key and returns the response.
     *
     * @param string $encryptionkey
     * @return array
     */
    public static function execute(string $identifier, string $value): array {
        global $DB;

        $exists = $DB->get_field('user', 'id', [$identifier => $value], IGNORE_MISSING);

        return [
            'userid' => $exists,
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
