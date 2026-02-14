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
 * Main class for the auth_sso authentication plugin
 *
 * Documentation: {@link https://docs.moodle.org/dev/Authentication_plugins}
 *
 * @package    auth_sso
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * Authentication plugin auth_plugin_sso
 *
 * @package    auth_plugin_sso
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_sso extends auth_plugin_base {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'sso';
        $this->config = get_config('auth_sso');
    }

    /**
     * External login only.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * No internal passwords are allowed
     *
     * @return bool
     */
    public function is_internal(): bool {
        return false;
    }

    /**
     * Prevent local password changes.
     *
     * @return bool
     */
    public function can_change_password(): bool {
        return false;
    }

    /**
     * Avoid local password usage.
     *
     * @return bool
     */
    public function prevent_local_passwords(): bool {
        return true;
    }

    /**
     * Executes when user is loggedout from the system
     *
     */
    public function logoutpage_hook() {
        global $USER, $redirect, $CFG;
        if ($USER->auth === $this->authtype) {
            $redirect = $this->config->logout_redirect_url ? $this->config->logout_redirect_url : $CFG->wwwroot;
        }
    }
}

