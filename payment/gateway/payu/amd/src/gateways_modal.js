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
 * This module is responsible for PayPal content in the gateways modal.
 *
 * @module     paygw_payu/gateways_modal
 * @copyright  2026 Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from './repository';
import Templates from 'core/templates';
import Modal from 'core/modal';

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
const showModalWithPlaceholder = async () => await Modal.create({
    body: await Templates.render('paygw_payu/payu_button_placeholder', {}),
    show: true,
    removeOnClose: true,
});

/**
 * Process the payment.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} description Description of the payment
 * @returns {Promise<string>}
 */
export const process = (component, paymentArea, itemId, description) => {
    return Promise.all([
        showModalWithPlaceholder(),
        Repository.getConfigForJs(component, paymentArea, itemId, description),
    ])
        .then(([modal, payuConfig]) => {
            return Promise.all([
                modal,
                payuConfig,
                switchSdk(payuConfig.environment),
            ]);
        })
        .then(([modal, payuConfig]) => {
            // We have to clear the body. The render method in payu.Buttons will render everything.
            modal.setBody('');

            return new Promise(resolve => {
                var handlers = {
                    responseHandler: function (BOLT) {
                        if (BOLT.response.txnStatus == "SUCCESS") {
                            Repository.markTransactionComplete(
                                component, paymentArea, itemId, BOLT.response.txnid, BOLT.response.mihpayid)
                                .then(res => {
                                    modal.hide();
                                    return res;
                                })
                                .then(resolve);
                        }
                        if (BOLT.response.txnStatus == "FAILED") {
                            return Promise.reject(BOLT.response.txnMessage);
                        }
                        if (BOLT.response.txnStatus == "CANCEL") {
                            return Promise.reject(BOLT.response.txnMessage);
                        }
                    },
                    catchException: function (BOLT) {
                        return Promise.reject(BOLT.response.txnMessage);
                    }
                };
                window.bolt.launch(payuConfig, handlers);
            });
        })
        .then(res => {
            if (res.success) {
                return Promise.resolve(res.message);
            }

            return Promise.reject(res.message);
        });
};

/**
 * Unloads the previously loaded PayU JavaScript SDK, and loads a new one.
 * @param {string} environment Environment to load production or sandbox js sdk
 *
 * @returns {Promise}
 */
const switchSdk = (environment) => {
    let sdkUrl = '';
    if (environment === 'sandbox') {
        sdkUrl = `https://jssdk-uat.payu.in/bolt/bolt.min.js`;
    } else {
        sdkUrl = `https://jssdk.payu.in/bolt/bolt.min.js`;
    }

    // Check to see if this file has already been loaded. If so just go straight to the func.
    if (switchSdk.currentlyloaded === sdkUrl) {
        return Promise.resolve();
    }

    if (switchSdk.currentlyloaded) {
        const suspectedScript = document.querySelector(`script[src="${switchSdk.currentlyloaded}"]`);
        if (suspectedScript) {
            suspectedScript.parentNode.removeChild(suspectedScript);
        }
    }

    const script = document.createElement('script');

    return new Promise(resolve => {
        if (script.readyState) {
            script.onreadystatechange = function () {
                if (this.readyState == 'complete' || this.readyState == 'loaded') {
                    this.onreadystatechange = null;
                    resolve();
                }
            };
        } else {
            script.onload = function () {
                resolve();
            };
        }

        script.setAttribute('src', sdkUrl);
        document.head.appendChild(script);

        switchSdk.currentlyloaded = sdkUrl;
    });
};

/**
 * Holds the full url of loaded PayPal JavaScript SDK.
 *
 * @static
 * @type {string}
 */
switchSdk.currentlyloaded = '';