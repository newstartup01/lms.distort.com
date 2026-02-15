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
 * Class orders
 *
 * @package    paygw_payu
 * @copyright  2026 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orders {

    public const PROCESSING = 1;
    public const COMPLETED = 2;
    public const FAILED = 3;

    private const STATUSMAP = [
        orders::PROCESSING   => 'processing',
        orders::COMPLETED => 'completed',
        orders::FAILED    => 'failed',
    ];

    public static function get_status_name($status): string {
        return get_string(self::STATUSMAP[$status] ?? 'unknown', 'paygw_payu');
    }

    public static function get_transactions(array $filters = [], $sort = '', $offset = 0, $perpage = 0): array {
        global $DB;
        $params = [];
        $selectsql = "SELECT pgp.id, 
                    CONCAT(u.firstname, ' ', u.lastname) as userfullname,  
                    u.email,
                    pgp.payu_orderid,
                    c.fullname as coursename,
                    pgp.amount,
                    pgp.currency,
                    pgp.payu_paymentid,
                    pgp.timecreated,
                    pgp.status";
        $countsql = "SELECT count(pgp.id)";
        $fromsql = " FROM {paygw_payu} pgp
                 JOIN {user} u on u.id = pgp.userid
                 JOIN {enrol} e on e.id = pgp.itemid
                 JOIN {course} c on c.id = e.courseid";
        $wheresql = " WHERE 1 = 1";

        if($sort){
            $sortsql = " ORDER BY $sort";
        }else{
            $sortsql = " ORDER BY timecreated DESC";
        }
        
        $datasql = $selectsql.$fromsql.$wheresql.$sortsql;
        $countsql = $countsql.$fromsql.$wheresql;

        $orders = $DB->get_records_sql($datasql, $params, $offset, $perpage);
        $count = $DB->count_records_sql($countsql, $params);
        return [
            'data' => $orders,
            'count' => $count
        ];
    }

    public static function format_transactions(array $orders): array {
        $response = [];

        foreach ($orders as $order) {
            $response[] = [
                $order->payu_orderid,
                $order->fullname,
                $order->email,
                $order->coursename,
                $order->currency. ' '.$order->amount,
                $order->payu_paymentid,
                userdate($order->timecreated),
                self::get_status_name($order->status),
            ];
        }

        return $response;
    }
}
