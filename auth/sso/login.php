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
 * TODO describe file login
 *
 * @package    auth_sso
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

use auth_sso\helper as sso_helper;

global $CFG, $SESSION;

$courseid = optional_param('course', '', PARAM_INT);
$token = required_param('token', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/auth/sso/login.php', ['token' => $token, 'courseid' => $courseid]));

$redirectto = $SESSION->wantsurl ?? $CFG->wwwroot;
try {
    $payload = sso_helper::validate_token($token);

    if (!$user = $DB->get_record('user', ['email' => $payload->email, 'deleted' => 0])) {
        if (sso_helper::auto_create_user()) {
            $u = new \stdClass();
            $u->username = uniqid();
            $u->email = $payload->email;
            $u->idnumber = $payload->idnumber;
            $u->firstname = $payload->firstname;
            $u->lastname = $payload->lastname;
            $u->confirmed = 1;

            $userid = user_create_user($u, false, false);
            $user = core_user::get_user($userid, '*', MUST_EXIST);
        } else {
            throw new \moodle_exception('usernotfound', 'auth_sso', '', $payload->email);
        }
    }

    \auth_sso\event\sso_login_success::create([
        'userid'  => $user->id,
        'context' => $context,
    ])->trigger();

    complete_user_login($user);

    if ($courseid) {
        $redirectto = new \moodle_url('/course/view.php', ['id' => $courseid]);
    }

    redirect($redirectto);
} catch (Exception $e) {
    \auth_sso\event\sso_login_failed::create([
        'message'  => $e->getMessage(),
        'context' => \context_system::instance(),
    ])->trigger();

    redirect(new moodle_url('/login/index.php'));
}

