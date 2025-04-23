<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Helpers\Util;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\LedgerAccount;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ReportController extends BackendController
{
    public String $routePrefix = "reports";

    public function ledger(Request $request)
    {
        $accountList = LedgerAccount::getList('id','name');
        $today_date = DateUtility::getDate(null, DateUtility::DATE_OUT_FORMAT);
        $last_month_date = DateUtility::change($today_date, -1, DateUtility::MONTHS, DateUtility::DATE_OUT_FORMAT);

        $conditions = $this->getConditions(Route::currentRouteName(), [
            ["field" => "LT.main_account_id", "type" => "int", "view_field" => "main_account_id"],

            ["field" => "voucher_date", "type" => "from_date", "view_field" => "from_date", "default" => $last_month_date],
            ["field" => "voucher_date", "type" => "to_date", "view_field" => "to_date", "default" => $today_date],
        ], true);

        // d($conditions); exit;

        if ($conditions && isset($conditions['LT.main_account_id'])) {
            $where_list = [];

            foreach ($conditions as $field => $val) {
                if (str_check_char_array_exist($field, ["=", "<", ">", "!"])) {
                    $where_list[] = "$field'" . $val . "'";
                } else {
                    $where_list[] = "$field='" . $val . "'";
                }
            }

            $where = "";

            if ($where_list) {
                $where = "WHERE " . implode(" AND ", $where_list);
            }

            $q = "
                SELECT 
                    COUNT(1) AS C
                FROM 
                    ledger_transactions LT
                $where
                
            ";

            $data = DB::select($q);

            if (isset($data[0]->C) && $data[0]->C > DEFAULT_EXPORT_CSV_JS_LIMIT) {
                abort(\ACTION_NOT_PROCEED, "Too Much Records Found, Please apply more conditions to reduce record count");
            }

            $q = "
                SELECT 
                    LT.*
                FROM 
                    ledger_transactions LT
                $where
                ORDER BY
                    LT.id ASC
                LIMIT 50000
            ";

            $records = DB::select($q);

            $current_amount = 0;
            foreach ($records as $k => $record) {
                
                $record->other_account = $accountList[$record->other_account_id] ?? "";
                
                $current_amount += $record->amount;

                $records[$k] = Util::objToArray($record);
            }

            $conditions3 = $conditions;

            // d($conditions3);
            $where_list = [];

            unset($conditions3['voucher_date <=']);

            if (isset($conditions3['voucher_date >=']))
            {
                $where_list[] = "voucher_date <= '" . $conditions['voucher_date >='] . "'";
                unset($conditions3['voucher_date >=']);
            }

            foreach ($conditions3 as $field => $val) {
                if (str_check_char_array_exist($field, ["=", "<", ">", "!"])) {
                    $where_list[] = "$field'" . $val . "'";
                } else {
                    $where_list[] = "$field='" . $val . "'";
                }
            }

            $where = "";

            if ($where_list) {
                $where = "WHERE " . implode(" AND ", $where_list);
            }

            $q = "
                SELECT 
                    SUM(LT.amount) as sum_amount
                FROM 
                    ledger_transactions LT
                $where                
            ";

            // d($q);

            $temp = DB::select($q);

            // d($temp); exit;

            $ledgerAccount = LedgerAccount::find($conditions['LT.main_account_id']);

            $opening_amount = $ledgerAccount->getOpeningBalance();

            if ($temp[0]->sum_amount)
            {
                $opening_amount += $temp[0]->sum_amount;
            }

            $conditions3 = $conditions;

            // d($conditions3);
            $where_list = [];

            unset($conditions3['voucher_date >=']);

            foreach ($conditions3 as $field => $val) {
                if (str_check_char_array_exist($field, ["=", "<", ">", "!"])) {
                    $where_list[] = "$field'" . $val . "'";
                } else {
                    $where_list[] = "$field='" . $val . "'";
                }
            }

            $where = "";

            if ($where_list) {
                $where = "WHERE " . implode(" AND ", $where_list);
            }

            $q = "
                SELECT 
                    SUM(LT.amount) as sum_amount
                FROM 
                    ledger_transactions LT
                $where
            ";

            // d($q);

            $temp = DB::select($q);

            // d($temp); exit;

            $closing_amount = $opening_amount;

            if ($temp[0]->sum_amount)
            {
                $closing_amount += $temp[0]->sum_amount;
            }

            $this->setForView(compact("records", "current_amount", "opening_amount", "closing_amount"));
        } 

        $this->setForView(compact("accountList"));

        return $this->view(__FUNCTION__);
    }

    public function current_stock(Request $request)
    {
        $item_list = Item::getListCache("id", "name");
        $warehouse_list = Warehouse::getListCache();

        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = WarehouseStock::where($conditions)->with([
            "item", "item.unit", "item.itemCategory", "item.itemGroup", "item.brand"
            ])->limit(50000)->get();

        $this->setForView(compact("warehouse_list", "item_list","records"));

        return $this->view(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "item_id", "type" => "int"],
            ["field" => "warehouse_id", "type" => "int"]
        ]);

        return $conditions;
    }
}
