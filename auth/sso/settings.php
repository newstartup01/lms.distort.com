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
 * TODO describe file settings
 *
 * @package    auth_sso
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('auth_sso/pluginname', '',
        new lang_string('auth_sso_description', 'auth_sso')
    ));

    $settings->add(
        new admin_setting_configpasswordunmask('auth_sso/encryption_key',
            get_string('auth_sso_encryption_key', 'auth_sso'),
            get_string('auth_sso_encryption_key_desc', 'auth_sso'),
            '',
            PARAM_RAW
        )
    );

    $settings->add(
        new admin_setting_configtext('auth_sso/issuer',
            get_string('auth_sso_issuer', 'auth_sso'),
            get_string('auth_sso_issuer_desc', 'auth_sso'),
            '',
            PARAM_RAW
        )
    );

    $settings->add(
        new admin_setting_configtext('auth_sso/logout_redirect_url',
            get_string('auth_sso_logout_redirect_url', 'auth_sso'),
            get_string('auth_sso_logout_redirect_url_desc', 'auth_sso'),
            '',
            PARAM_RAW
        )
    );

    $settings->add(
        new admin_setting_configcheckbox('auth_sso/auto_create_user',
            get_string('auth_sso_auto_create_user', 'auth_sso'),
            get_string('auth_sso_auto_create_user_desc', 'auth_sso'),
            '',
            PARAM_RAW
        )
    );
}

