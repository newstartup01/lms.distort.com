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

import Ajax from 'core/ajax';

/**
 * Return the PayPal JavaScript SDK URL.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} description description for the payment
 * @returns {Promise<{clientid: string, brandname: string, cost: number, currency: string}>}
 */
export const getConfigForJs = (component, paymentArea, itemId, description) => {
    const request = {
        methodname: 'paygw_payu_get_config_for_js',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            description: description,
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Call server to validate and capture payment for order.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} txnId The order id coming back from PayU
 * @param {string} payId The payid id coming back from PayU
 * @returns {*}
 */
export const markTransactionComplete = (component, paymentArea, itemId, txnId, payId) => {
    const request = {
        methodname: 'paygw_payu_create_transaction_complete',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            txnid: txnId,
            payid: payId,
        },
    };

    return Ajax.call([request])[0];
};