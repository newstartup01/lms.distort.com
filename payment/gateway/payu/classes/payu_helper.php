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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Class payu_helper
 *
 * @package    paygw_payu
 * @copyright  2026 Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class payu_helper {

    /** @var string Merchant Key from PayU. */
    private $key;

    /** @var string Merchant Salt from PayU. */
    private $secret;

    /** @var string Payment gateway enviroment. */
    private $environment = 'live';

    /** @var string Payment gateway API URL. */
    private $apiurl;

    /**
     * Main Constructor
     * @param string $key Merchant key generated from PayU
     * @param string $secret Merchant salt generated from PayU
     * @param string $environment Payment Environment (Sandbox/Live)
     */
    public function __construct(string $key, string $secret, string $environment) {
        $this->key = $key;
        $this->secret = $secret;
        $this->environment = $environment;
        $this->apiurl = ($environment === 'live')
            ? 'https://info.payu.in/merchant/postservice.php?form=2'
            : 'https://test.payu.in/merchant/postservice.php?form=2';
    }

    /**
     * Create Order payload to pass into PayU JS SDK
     * @param float $amount
     * @param string $description
     * @return array
     */
    public function create_order(float $amount, string $description): array {
        global $USER, $CFG;

        $successurl = new \moodle_url('/payment/gateway/payu/callback.php', ['status' => 'success']);
        $failurl = new \moodle_url('/payment/gateway/payu/callback.php', ['status' => 'failed']);

        $data = [
            'key' => $this->key,
            'txnid' => uniqid('payu_'),
            'amount' => $amount,
            'firstname' => $USER->firstname,
            'email' => $USER->email,
            'phone' => '9110513099',
            'productinfo' => $description,
            'lastname' => $USER->lastname,
            'surl' => $successurl->out(),
            'furl' => $failurl->out(),
            'environment' => $this->environment,
        ];
        $data['hash'] = $this->get_hash_key($data);

        return $data;
    }

    /**
     * Verify payment
     * @param string $txnid Transaction id
     * @return boolean
     */
    public function verify_payment($txnid) {
        $hashstr = $this->key . '|verify_payment|' . $txnid . '|' . $this->secret;
        $payload = [
            'key' => $this->key,
            'command' => 'verify_payment',
            'var1' => $txnid,
            'hash' => strtolower(hash('sha512', $hashstr)),
        ];

        $response = $this->make_request($payload);

        if ($response['status']) {
            $transactions = $response['transaction_details'];
            $transaction = $transactions[$txnid];
            return $transaction;
        }
        return false;
    }

    /**
     * Generate hashkey
     * @param array $params Order array
     * @return string
     */
    public function get_hash_key(array $params) {
        $hashstr = $this->key . '|' . $params['txnid'] . '|' . $params['amount'] . '|'
            . $params['productinfo'] . '|' . $params['firstname'] . '|' . $params['email'] . '|'
            . $params['udf1'] . '|' . $params['udf2'] . '|' . $params['udf3'] . '|'
            . $params['udf4'] . '|' . $params['udf5'] . '||||||' . $this->secret;
        return hash('sha512', $hashstr);
    }

    /**
     * Function to make curl request
     * @param array $payload Post payload
     * @return array
     */
    private function make_request($payload) {
        $curl = new \curl();
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_CONNECTTIMEOUT' => 30,
            'CURLOPT_SSLVERSION' => 6,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_SSL_VERIFYPEER' => false,
        ];
        $response = $curl->post($this->apiurl, $payload, $options);
        $data = json_decode($response, true);

        return $data;
    }
}
