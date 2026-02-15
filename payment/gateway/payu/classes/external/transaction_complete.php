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

namespace paygw_payu\external;
use core_payment\helper as payment_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use paygw_payu\payu_helper;

/**
 * Class transaction_complete
 *
 * @package    paygw_payu
 * @copyright  2026 Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transaction_complete extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'txnid' => new external_value(PARAM_TEXT, 'The txnid id coming back from PayU'),
            'payid' => new external_value(PARAM_TEXT, 'The pay id coming back from PayU'),
        ]);
    }

    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $component Name of the component that the itemid belongs to
     * @param string $paymentarea
     * @param int $itemid An internal identifier that is used by the component
     * @param string $orderid PayU order ID
     * @return array
     */
    public static function execute(string $component, string $paymentarea, int $itemid, string $txnid, string $payid): array {
        global $USER, $DB;

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'txnid' => $txnid,
            'payid' => $payid,
        ]);

        $config = payment_helper::get_gateway_configuration($component, $paymentarea, $itemid, 'payu');
        $payable = payment_helper::get_payable($component, $paymentarea, $itemid);
        $currency = $payable->get_currency();
        $surcharge = payment_helper::get_gateway_surcharge('payu');
        $amount = payment_helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
        $payuhelper = new payu_helper($config['clientid'], $config['secret'], $config['environment']);
        $verifypayment = $payuhelper->verify_payment($txnid);

        if ($verifypayment) {
            try {
                $paymentid1 = payment_helper::save_payment($payable->get_account_id(), $component, $paymentarea,
                                $itemid, (int)$USER->id, $amount, $currency, 'payu');
                $record = $DB->get_record('paygw_payu', ['payu_orderid' => $txnid], 'id', MUST_EXIST);
                if ($record) {
                    $record->paymentid = $paymentid1;
                    $record->payu_paymentid = $payid;
                    $record->ordercompletedat = time();
                    $record->timemodified = time();
                    $record->status = 2;
                    $DB->update_record('paygw_payu', $record);

                    payment_helper::deliver_order($component, $paymentarea, $itemid, $paymentid1, (int)$USER->id);
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
            return [
                'success' => true,
                'message' => get_string('paymentsuccessful', 'paygw_payu'),
            ];
        }

        return [
            'success' => false,
            'message' => get_string('paymentfailed', 'paygw_payu'),
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether everything was successful or not.'),
            'message' => new external_value(PARAM_RAW, 'Message (usually the error message).'),
        ]);
    }
}
