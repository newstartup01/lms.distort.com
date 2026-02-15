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
 * Displays transactions associated with payu gateway
 *
 * @package    paygw_payu
 * @copyright  Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());
$download     = optional_param('download', '', PARAM_ALPHA);
$url = new moodle_url('/payment/gateway/payu/orders.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');

use paygw_payu\orders;

$table = new \paygw_payu\table\transactions_table('transactions');
$table->define_baseurl($url);


$table->is_downloading($download, 'transactions');
if ($table->is_downloading()) {
    $table->out(0, false);
    exit;
}
echo $OUTPUT->header();
echo $table->out(20, true);
echo $OUTPUT->footer();
