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

namespace paygw_payu;

/**
 * The gateway class for paygw_payu payment gateway
 *
 * @package    paygw_payu
 * @copyright  2026 Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {
    /**
     * Configuration form for the gateway instance
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();
        $mform->addElement('text', 'brandname', get_string('pluginname', 'paygw_payu'));
        $mform->setType('brandname', PARAM_TEXT);

        $mform->addElement('text', 'clientid', get_string('clientid', 'paygw_payu'));
        $mform->setType('clientid', PARAM_TEXT);
        $mform->addHelpButton('clientid', 'clientid', 'paygw_payu');

        $mform->addElement('text', 'secret', get_string('secret', 'paygw_payu'));
        $mform->setType('secret', PARAM_TEXT);
        $mform->addHelpButton('secret', 'secret', 'paygw_payu');

        $options = [
            'live' => get_string('live', 'paygw_payu'),
            'sandbox'  => get_string('sandbox', 'paygw_payu'),
        ];

        $mform->addElement('select', 'environment', get_string('environment', 'paygw_payu'), $options);
        $mform->addHelpButton('environment', 'environment', 'paygw_payu');
    }

    /**
     * Returns the list of currencies that the payment gateway supports.
     *
     * @return string[] An array of the currency codes in the three-character ISO-4217 format
     */
    public static function get_supported_currencies(): array {
        return ['INR'];
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(
        \core_payment\form\account_gateway $form,
        \stdClass $data,
        array $files,
        array &$errors
    ): void {
        if ($data->enabled &&
                (empty($data->brandname) || empty($data->clientid) || empty($data->secret))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
        }
    }
}
