<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AutoIncreament;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\LedgerPayment;
use App\Models\LedgerTransaction;
use App\Models\Party;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PaymentController extends BackendController
{
    public String $routePrefix = "ledger-payments";
    protected $modelClass = LedgerPayment::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions),Route::currentRouteName());

        $accountList = LedgerAccount::getList('id','name');
        $this->setForView(compact("records", "accountList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "voucher_no", "type" => "string", "view_field" => "voucher_no"],
            ["field" => "narration", "type" => "string", "view_field" => "narration"],
            ["field" => "from_account_id", "type" => "int", "view_field" => "from_account_id"],
            ["field" => "to_account_id", "type" => "int", "view_field" => "to_account_id"],
            ["field" => "voucher_date", "type" => "from_date", "view_field" => "from_date"],
            ["field" => "voucher_date", "type" => "to_date", "view_field" => "to_date"],
        ]);

        return $conditions;
    }

    public function create()
    {
        $model = new $this->modelClass();

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_form(null);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_list_form($model)
    {
        $conditions = [
            "allow_pay_for_end_user" => 1,
        ];

        $accountList = LedgerAccount::getList("id", "name", $conditions);


        $this->setForView(compact("accountList"));
    }

    private function _get_comman_validation_rules()
    {
        return [
            "from_account_id" => ["required"],
            "to_account_id" => ["required"],
            "voucher_date" => ["required"],
            "amount" => ["required", "numeric", "min:0", "not_in:0"],
            "narration" => ["required", "max:255"]
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
        ]));

        try
        {
            DB::beginTransaction();

            $validatedData['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PAYMENT);

            $model = $this->modelClass::create($validatedData);

            $this->_afterSave($model);

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_PAYMENT);

            DB::commit();

            $this->saveSqlLog();

            return back()->with('success', 'Record created successfully');

        } catch (Exception $ex) {

            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        abort(\ACTION_NOT_PROCEED, "Payment can not be edit");

        $model = $this->modelClass::findOrFail($id);

        if (!$model->can_edit)
        {
            abort(\ACTION_NOT_PROCEED, "Payment Voucher $model->voucher_no can not be edit, bcoz it link with other Ledger Account");
        }

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_list_form($model);

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
        ]));

        // dd($validatedData);

        try {
            DB::beginTransaction();

            $model->fromAccount->updateBalance($model->amount);
            $model->toAccount->updateBalance(-1 * $model->amount);

            $model->fill($validatedData);

            $model->save();

            $this->_afterSave($model);

            DB::commit();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');

        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _afterSave(LedgerPayment $model)
    {
        $model->ledgerTransaction()->delete();

        $save_arr = [
            "main_account_id" => $model->to_account_id,
            "other_account_id" => $model->from_account_id,
            "voucher_type" => laravel_constant("voucher_payment"),
            "voucher_date" => $model->voucher_date,
            "voucher_no" => $model->voucher_no,
            "amount" => $model->amount,
            "narration" => $model->narration,
            "payment_id" => $model->id,
        ];

        LedgerTransaction::createDoubleEntry($save_arr);
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        
        return $this->_destroy($model);
    }

    public function csv()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $count = $this->modelClass::where($conditions);

        $this->beforeCSVExport($count);

        $records = $this->modelClass::where($conditions)->with([
            "fromAccount:id,name,party_id", "toAccount:id,name,party_id",
            "fromAccount.party:id,name", "toAccount.party:id,name"
        ])->get()->toArray();

        // d($records); exit;

        $csv_records = [];

        $yes_no_list = config('constant.yes_no');
        $user_list = User::getListCache();

        foreach($records as $record)
        {

            $from_account = $record['from_account']['name'] ?? $record['from_account']['party']['display_name'];
            $to_account = $record['to_account']['name'] ?? $record['to_account']['party']['display_name'];

            $csv_records[] = [
                'ID' => $record['id'],
                "Voucher Date" => $record['voucher_date'],
                "Voucher No." => $record['voucher_no'],
                'From Account' => $from_account,
                'To Account' => $to_account,
                'Amount' => $record['amount'],
                'Bank Transaction No' => $record['bank_transaction_no'] ?? "",
                'Narration' => $record['narration'] ?? "",
                'Advance Pay' => $yes_no_list[$record['is_advance_pay']] ?? "",                
                'Created' => if_date_time($record['created_at']),
                'Created By' => $user_list[$record['created_by']] ?? "",
                'Updated' => if_date_time($record['updated_at']),
                'Updated By' => $user_list[$record['updated_by']] ?? "",
            ];
        }

        $path = laravel_constant('path.temp');
        FileUtility::createFolder($path);
        $file = $path . $this->tableName .  "_" . date(DateUtility::DATETIME_OUT_FORMAT_FILE) . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        download_start($file, "application/octet-stream");
    }

    public function pay_for_purchase(Request $request)
    {
        if ($request->isMethod("post")) {
            // dd($request->all());

            $validatedData = $request->validate([
                "to_account_id" => ["required"],
                "from_account_id" => ["required"],
                "voucher_date" => ["required"],
                "amount" => ["required", "numeric", "min:0", "not_in:0"],
                "bank_transaction_no" => "",
                "is_advance_pay" => "",
                "narration" => ["required", "max:255"],
            ]);

            // d($validatedData); exit;

            try {
                DB::beginTransaction();

                $purchase_account = LedgerAccount::getByCode(LedgerAccount::CODE_purchase);

                $party_account = LedgerAccount::findOrFail($validatedData['to_account_id']);
                
                $via_account = LedgerAccount::findOrFail($validatedData['from_account_id']);

                $validatedData['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PAYMENT);
                $validatedData['can_edit'] = false;

                $model = LedgerPayment::create($validatedData);

                LedgerTransaction::where("payment_id", $model->id)->delete();

                $save_arr = [
                    "main_account_id" => $party_account->id,
                    "other_account_id" => $via_account->id,
                    "voucher_type" => laravel_constant("voucher_payment"),
                    "voucher_date" => $model->voucher_date,
                    "voucher_no" => $model->voucher_no,
                    "amount" => $model->amount,
                    "narration" => $model->narration,
                    "payment_id" => $model->id,
                ];

                // d($save_arr);

                LedgerTransaction::createDoubleEntry($save_arr);

                $save_arr['main_account_id'] = $purchase_account->id;
                $save_arr['other_account_id'] = $party_account->id;
                $save_arr['amount'] *= -1;
                // d($save_arr); exit;

                LedgerTransaction::create($save_arr);

                AutoIncreament::increaseCounter(AutoIncreament::TYPE_PAYMENT);

                DB::commit();

                $this->saveSqlLog();

                return back()->with('success', 'Record created successfully');
            } catch (Exception $ex) {

                DB::rollBack();

                $this->saveSqlLog();

                return back()->withInput()->with('fail', $ex->getMessage());
            }
        }

        $ledger_category_id_list = LedgerCategory::getList("id", "id", [
            "code" => [
                LedgerCategory::CODE_cash,
                LedgerCategory::CODE_bank
            ]
        ]);

        $our_account_list = LedgerAccount::getList("id", "name", [
            "type" => LedgerAccount::TYPE_SIMPLE,
            "ledger_category_id" => $ledger_category_id_list
        ]);

        // d($our_account_list);

        $ledger_category_id_list = LedgerCategory::getList("id", "id", [
            "code" => [
                LedgerCategory::CODE_creditor,
                LedgerCategory::CODE_creditor_debitor
            ]
        ]);
        $other_account_list = LedgerAccount::getList("id", "name", [
            "ledger_category_id" => $ledger_category_id_list
        ]);


        // d($other_account_list);

        // d($this->getQueryLog()); exit;

        $this->setForView(compact("our_account_list", "other_account_list"));

        return $this->view(__FUNCTION__);
    }

    public function receive_for_sale(Request $request)
    {
        if ($request->isMethod("post")) {
            // dd($request->all());

            $validatedData = $request->validate([
                "to_account_id" => ["required"],
                "from_account_id" => ["required"],
                "voucher_date" => ["required"],
                "amount" => ["required", "numeric", "min:0", "not_in:0"],
                "bank_transaction_no" => "",
                "is_advance_pay" => "",
                "narration" => ["required", "max:255"],
            ]);

            // d($validatedData); exit;

            try {
                DB::beginTransaction();

                $sale_account = LedgerAccount::getByCode(LedgerAccount::CODE_sale);

                $party_account = LedgerAccount::findOrFail($validatedData['from_account_id']);
                
                $receive_account = LedgerAccount::findOrFail($validatedData['to_account_id']);

                $validatedData['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_RECEIPT);
                $validatedData['can_edit'] = false;

                $model = LedgerPayment::create($validatedData);

                LedgerTransaction::where("payment_id", $model->id)->delete();

                $save_arr = [
                    "main_account_id" => $receive_account->id,
                    "other_account_id" => $party_account->id,
                    "voucher_type" => laravel_constant("voucher_receipt"),
                    "voucher_date" => $model->voucher_date,
                    "voucher_no" => $model->voucher_no,
                    "amount" => $model->amount,
                    "narration" => $model->narration,
                    "payment_id" => $model->id,
                ];

                // d($save_arr);

                LedgerTransaction::createDoubleEntry($save_arr);

                $save_arr['main_account_id'] = $sale_account->id;
                $save_arr['other_account_id'] = $party_account->id;
                $save_arr['amount'] *= -1;
                // d($save_arr); exit;

                LedgerTransaction::create($save_arr);

                AutoIncreament::increaseCounter(AutoIncreament::TYPE_RECEIPT);

                DB::commit();

                $this->saveSqlLog();

                return back()->with('success', 'Record created successfully');
            } catch (Exception $ex) {

                DB::rollBack();

                $this->saveSqlLog();

                return back()->withInput()->with('fail', $ex->getMessage());
            }
        }

        $ledger_category_id_list = LedgerCategory::getList("id", "id", [
            "code" => [
                LedgerCategory::CODE_cash,
                LedgerCategory::CODE_bank
            ]
        ]);

        $our_account_list = LedgerAccount::getList("id", "name", [
            "type" => LedgerAccount::TYPE_SIMPLE,
            "ledger_category_id" => $ledger_category_id_list
        ]);

        // d($our_account_list);

        $ledger_category_id_list = LedgerCategory::getList("id", "id", [
            "code" => [
                LedgerCategory::CODE_debitor,
                LedgerCategory::CODE_creditor_debitor
            ]
        ]);

        $other_account_list = LedgerAccount::getList("id", "nameId", [
            "ledger_category_id" => $ledger_category_id_list
        ]);

        // d($other_account_list);

        // d($this->getQueryLog()); exit;

        $this->setForView(compact("our_account_list", "other_account_list"));

        return $this->view(__FUNCTION__);
    }

    public function pay_for_job_work(Request $request)
    {
        if ($request->isMethod("post")) {
            // dd($request->all());

            $validatedData = $request->validate([
                "to_account_id" => ["required"],
                "from_account_id" => ["required"],
                "voucher_date" => ["required"],
                "amount" => ["required", "numeric", "min:0", "not_in:0"],
                "bank_transaction_no" => "",
                "is_advance_pay" => "",
                "narration" => ["required", "max:255"],
            ]);

            // d($validatedData); exit;

            try {
                DB::beginTransaction();

                $manufacture_account = LedgerAccount::getByCode(LedgerAccount::CODE_manufacturing);

                $party_account = LedgerAccount::findOrFail($validatedData['to_account_id']);
                
                $via_account = LedgerAccount::findOrFail($validatedData['from_account_id']);

                $validatedData['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PAYMENT);
                $validatedData['can_edit'] = false;

                $model = LedgerPayment::create($validatedData);

                LedgerTransaction::where("payment_id", $model->id)->delete();

                $save_arr = [
                    "main_account_id" => $party_account->id,
                    "other_account_id" => $via_account->id,
                    "voucher_type" => laravel_constant("voucher_manufacture"),
                    "voucher_date" => $model->voucher_date,
                    "voucher_no" => $model->voucher_no,
                    "amount" => $model->amount,
                    "narration" => $model->narration,
                    "payment_id" => $model->id,
                ];

                // d($save_arr);

                LedgerTransaction::createDoubleEntry($save_arr);

                $save_arr['main_account_id'] = $manufacture_account->id;
                $save_arr['other_account_id'] = $party_account->id;
                $save_arr['amount'] *= -1;
                // d($save_arr); exit;

                LedgerTransaction::create($save_arr);

                AutoIncreament::increaseCounter(AutoIncreament::TYPE_PAYMENT);

                DB::commit();

                $this->saveSqlLog();

                return back()->with('success', 'Record created successfully');
            } catch (Exception $ex) {

                DB::rollBack();

                $this->saveSqlLog();

                return back()->withInput()->with('fail', $ex->getMessage());
            }
        }

        $ledger_category_id_list = LedgerCategory::getList("id", "id", [
            "code" => [
                LedgerCategory::CODE_cash,
                LedgerCategory::CODE_bank
            ]
        ]);

        $our_account_list = LedgerAccount::getList("id", "name", [
            "type" => LedgerAccount::TYPE_SIMPLE,
            "ledger_category_id" => $ledger_category_id_list
        ]);

        // d($our_account_list);

        $party_id_list = Party::getList("id", "id", [
            "is_job_worker" => 1
        ]);

        $other_account_list = LedgerAccount::getList("id", "nameId", [
            "party_id" => $party_id_list
        ]);

        // d($other_account_list);

        // d($this->getQueryLog()); exit;

        $this->setForView(compact("our_account_list", "other_account_list"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_get_pending_payable_amount($id)
    {
        $response = ["status" => 1, "data" => [
            "pending_amount" => 0
        ]];

        $model = LedgerAccount::where('is_active', 1)->findOrFail($id);

        $balance = $model->getBalance();

        if ($balance < 0)
        {
            $response['data']['pending_amount'] = abs($balance);
        }

        return $this->responseJson($response);
    }

    public function ajax_get_pending_receiveable_amount($id)
    {
        $response = ["status" => 1, "data" => [
            "pending_amount" => 0
        ]];

        $model = LedgerAccount::where('is_active', 1)->findOrFail($id);

        $balance = $model->getBalance();

        if ($balance > 0)
        {
            $response['data']['pending_amount'] = $balance;
        }

        return $this->responseJson($response);
    }
}
