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

namespace auth_sso\task;

/**
 * Class purge_sso_states
 *
 * @package    auth_sso
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purge_sso_states extends \core\task\scheduled_task {
    /**
     * Get taskname for display in Moodle's scheduled tasks UI.
     *
     * @return string Localised taskname.
     */
    public function get_name(): string {
        return get_string('task_purge_sso_states', 'auth_sso');
    }

    /**
     * Executes the cleanup
     *
     * Deletes the nonces that are expired
     *
     * @return void
     */
    public function execute(): void {
        global $DB;

        $DB->delete_records_select('auth_sso_state', 'expires < ?', [time()]);
    }
}
