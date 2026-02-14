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
 * Main class for the auth_jobportalsso authentication plugin
 *
 * Documentation: {@link https://docs.moodle.org/dev/Authentication_plugins}
 *
 * @package    auth_sso
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_sso;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Helpers for sso
 *
 * @package    auth_plugin_sso
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /** Component config. */
    private const CONFIG_COMPONENT = 'auth_sso';

    /** @var $leeway Token expiry limit. */
    private static $leeway = 60;

    /**
     * Get configuration
     * @return stdClass
     */
    private static function config(): \stdClass {
        $config = get_config(self::CONFIG_COMPONENT);

        if (empty($config->encryption_key)) {
            throw new \moodle_exception('SSO salt is not configured');
        }

        return $config;
    }

    /**
     * Get Encryption key
     * @return string
     */
    public static function encryption_key(): string {
        return self::config()->encryption_key;
    }

    /**
     * Get Issuer URL
     * @return string
     */
    public static function issuer(): string {
        return self::config()->issuer;
    }

    /**
     * Checks if user can be created automatically
     * @return bool
     */
    public static function auto_create_user(): bool {
        return self::config()->auto_create_user;
    }

    /**
     * Get logout redirect url
     * @return string
     */
    public static function logout_redirect_url(): string {
        return self::config() ? self::config()->logout_redirect_url : '';
    }

    /**
     * Validates encryption key
     * @param string $encryptionkey
     * @return bool
     */
    public static function is_encryption_key_valid(string $encryptionkey): bool {
        return hash_equals(self::encryption_key(), $encryptionkey) ? true : false;
    }

    /**
     * Save nonce into database to avoid autoreplays
     * @param string $nonce
     * @return void
     */
    public static function consume_nonce(string $nonce): void {
        global $DB;
        try {
            if ($exists = $DB->record_exists('auth_sso_state', ['nonce' => $nonce])) {
                throw new \moodle_exception('autoreplaydetected', 'auth_sso');
            }

            $record = new \stdClass;
            $record->nonce = $nonce;
            $record->timecreated = time();
            $record->expires = time() + static::$leeway;
            $DB->insert_record('auth_sso_state', $record);

        } catch (Exception $e) {
            throw new \moodle_exception($e->getMessage(), 'auth_sso');
        }
    }

    /**
     * Validates Claims
     * @param object $payload
     * @return void
     */
    public static function validate_claims(object $payload): void {
        self::consume_nonce($payload->nonce);
        $now = time();
        if ($payload->iss !== self::issuer()) {
            throw new \moodle_exception('invalidissuer', 'auth_sso');
        }

        if ($payload->iat > ($now + static::$leeway)) {
            throw new \moodle_exception('invalidtoken', 'auth_sso');
        }

        if ($payload->exp < ($now - static::$leeway)) {
            throw new \moodle_exception('tokenexpired', 'auth_sso');
        }
    }

    /**
     * Validates token
     * @param string $token
     * @return object
     */
    public static function validate_token(string $token): object {
        $payload = JWT::decode(
            $token,
            new Key(self::encryption_key(), 'HS256')
        );
        self::validate_claims($payload);

        return $payload;
    }
}

