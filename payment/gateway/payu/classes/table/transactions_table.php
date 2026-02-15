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

namespace paygw_payu\table;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

use table_sql;
use paygw_payu\orders;
/**
 * Class transactions_table
 *
 * @package    paygw_payu
 * @copyright  Ranga Reddy <ranga.seguri@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transactions_table extends table_sql {
    /**
     * Constructor.
     *
     * @param string $uniqueid Unique identifier for table instance.
     */
    public function __construct(string $uniqueid) {
        parent::__construct($uniqueid);

        $this->define_columns([
            'payu_orderid',
            'userfullname',
            'email',
            'coursename',
            'amount',
            'payu_paymentid',
            'timecreated',
            'status'
        ]);

        $this->define_headers([
            get_string('orderid', 'paygw_payu'),
            get_string('fullname', 'paygw_payu'),
            get_string('email', 'paygw_payu'),
            get_string('course', 'paygw_payu'),
            get_string('amount', 'paygw_payu'),
            get_string('txnid', 'paygw_payu'),
            get_string('orderdate', 'paygw_payu'),
            get_string('orderstatus', 'paygw_payu'),
        ]);
        
        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('message');
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->collapsible(false);
    }

    /**
     * Format Status
     * @param \stdClass $row Table row data
     * @return string
     */
    public function col_status($row): string {
        if(empty($row->status)) {
            return '';
        }

        return orders::get_status_name($row->status);
    }

    /**
     * Format date
     * @param \stdClass $row Table row data
     * @return string
     */
    public function col_timecreated($row): string {
        if(empty($row->timecreated)){
            return '';
        }
        return userdate($row->timecreated);
    }

    /**
     * Query the logs table. Store results in the object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        $filters = [];
        $sort = $this->get_sql_sort();
        $transactions = orders::get_transactions($filters, $sort, $this->get_page_start(), $this->get_page_size());
        $total = $transactions['count'];
        $this->pagesize($pagesize, $total);
        $this->rawdata = $transactions['data'];

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }
}
