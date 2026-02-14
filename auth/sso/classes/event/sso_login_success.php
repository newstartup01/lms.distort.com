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

namespace auth_sso\event;

/**
 * Event sso_login_success
 *
 * @package    auth_sso
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sso_login_success extends \core\event\base {
    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get event name
     */
    public static function get_name() {
        return get_string('event_sso_login_success', 'auth_sso');
    }

    /**
     * Get event description
     */
    public function get_description() {
        return "User with id '{$this->userid}' logged in using sso";
    }
}
