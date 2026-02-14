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
 * External functions and service declaration for SSO
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    auth_sso
 * @category   webservice
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'auth_sso_test_connection' => [
        'classname'   => 'auth_sso\external\test_connection',
        'classpath'   => '',
        'description' => 'Validates the encryptionkey store and returns the response',
        'type'        => 'read',
        'ajax'        => false,
    ],
    'auth_sso_check_user_existance' => [
        'classname'   => 'auth_sso\external\check_user_existance',
        'classpath'   => '',
        'description' => 'Checks if user exists and returns userid',
        'type'        => 'read',
        'ajax'        => false,
    ],
    'auth_sso_create_user' => [
        'classname'   => 'auth_sso\external\create_user',
        'classpath'   => '',
        'description' => 'Creates a new user in the system',
        'type'        => 'read',
        'ajax'        => false,
    ],
];

