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

declare(strict_types=1);

namespace paygw_payu\external;

use core_payment\helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use paygw_payu\payu_helper;

/**
 * Class get_config_for_js
 *
 * @package    paygw_payu
 * @copyright  2026 Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_config_for_js extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
            'description' => new external_value(PARAM_RAW, 'Description for the payment'),
        ]);
    }

    /**
     * Returns the config values required by the PayPal JavaScript SDK.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return string[]
     */
    public static function execute(string $component, string $paymentarea, int $itemid, string $description): array {
        global $DB, $USER;

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'description' => $description,
        ]);

        $config = helper::get_gateway_configuration($component, $paymentarea, $itemid, 'payu');
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $surcharge = helper::get_gateway_surcharge('payu');
        $cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);

        $payuhelper = new payu_helper($config['clientid'], $config['secret'], $config['environment']);
        $order = $payuhelper->create_order($cost, $description);

        if ($order) {
            $record = new \stdClass;
            $record->userid = $USER->id;
            $record->itemid = $itemid;
            $record->amount = $cost;
            $record->currency = $payable->get_currency();
            $record->component = $component;
            $record->paymentarea = $paymentarea;
            $record->payu_orderid = $order['txnid'];
            $record->status = 1;
            $record->ordercreatedat = time();
            $record->timecreated = time();
            $record->timemodified = time();

            $DB->insert_record('paygw_payu', $record, 'id', false);
        }

        return $order;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'key' => new external_value(PARAM_TEXT, 'PayU client id'),
            'txnid' => new external_value(PARAM_TEXT, 'Unique transaction id'),
            'amount' => new external_value(PARAM_INT, 'Total payable amount along with gateway surcharge'),
            'firstname' => new external_value(PARAM_TEXT, 'Firstname of the user'),
            'email' => new external_value(PARAM_TEXT, 'Email of the user'),
            'phone' => new external_value(PARAM_TEXT, 'Phonenumber of the user'),
            'productinfo' => new external_value(PARAM_TEXT, 'Course name'),
            'lastname' => new external_value(PARAM_TEXT, 'Lastname of the user'),
            'hash' => new external_value(PARAM_TEXT, 'Hashvalue of the transaction'),
            'surl' => new external_value(PARAM_RAW, 'success url'),
            'furl' => new external_value(PARAM_RAW, 'failed url'),
            'environment' => new external_value(PARAM_TEXT, 'Enviroment'),
        ]);
    }
}
