<?php

namespace App\Http\Controllers\Backend;

use App\Models\AutoIncreament;
use App\Models\Item;
use App\Models\ItemConversionAndMovement;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\LedgerPayment;
use App\Models\LedgerTransaction;
use App\Models\Process;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class JobWorkManufacturingController extends BackendController
{
    public String $routePrefix = "job-work-manufacturing";
    public $modelClass = ItemConversionAndMovement::class;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            'fromWarehouse' , 'toWarehouse' , 'fromitem' , 'toitem' , 'process'
        ]),Route::currentRouteName());
        // dd($records);
        $from_warehouse_list = Warehouse::getList('id', 'name', [
            "type"=>Warehouse::TYPE_PARTY
        ]);
        $warehouse_list = Warehouse::getList();

        $process_list = Process::getList('id','name');
        $item_list = Item::getList();

        $this->setForView(compact("records","from_warehouse_list","warehouse_list","process_list","item_list"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field'=>'challan_no','type'=>'string'],
            ['field'=>'challan_date','type'=>'date'],
            ['field'=>'from_warehouse_id','type'=>''],
            ['field'=>'to_warehouse_id','type'=>''],
            ['field'=>'from_item_id','type'=>''],
            ['field'=>'to_item_id','type'=>''],
            ['field'=>'process_id','type'=>''],
        ]);

        return $conditions;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new $this->modelClass();

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];
        $this->_set_form_list();

        $this->setForView(compact("model", 'form'));


        return $this->view("form");
    }

    private function _set_form_list()
    {
        $from_warehouse_list = Warehouse::getList('id', 'name', [
            "type" => Warehouse::TYPE_PARTY
        ]);
        $warehouse_list = Warehouse::getList();
        // dd($from_warehouse_list);
        $process_list = Process::getList('id','name');
        $item_list = Item::getList();

        $this->setForView(compact("from_warehouse_list","warehouse_list", "item_list","process_list"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'from_item_id' => 'required|exists:items,id',
            'to_item_id' => 'required|exists:items,id|different:from_item_id',
            'from_qty' => 'required|numeric',
            'to_qty' => 'required|numeric',
            'amount' => 'required|numeric',
            'process_id' => 'required|exists:processes,id',
            'wastage_qty' => 'required|numeric',
            'challan_date' => 'required|date',
            'comments' => 'nullable|string'
        ]);
        $validated['challan_date'] = \Carbon\Carbon::parse($validated['challan_date'])->format('Y-m-d');
        try {
            DB::beginTransaction();
            $validated['challan_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_INVENTORY_MOVEMENT);
            $model = ItemConversionAndMovement::processStockMovement($validated);

            if ($model->amount > 0) {
                $manufacture_account = LedgerAccount::getByCode(LedgerAccount::CODE_manufacturing);
                $stock_account = LedgerAccount::getByCode(LedgerAccount::CODE_stock);

                $party_account = $model->fromWarehouse->party->ledgerAccount()->first();
    
                if (!$party_account) {
                    abort(\ACTION_NOT_PROCEED, "Party's Ledger Account Not Found");
                }
    
                LedgerTransaction::where("manufacture_id", $model->id)->delete();
    
                $save_arr = [
                    "main_account_id" => $manufacture_account->id,
                    "other_account_id" => $party_account->id,
                    "voucher_type" => laravel_constant("voucher_manufacture"),
                    "voucher_date" => $model->challan_date,
                    "voucher_no" => $model->challan_no,
                    "amount" => $model->amount,
                    // "narration" => $model->narration,
                    "manufacture_id" => $model->id
                ];
    
                // dd($save_arr);
    
                LedgerTransaction::createDoubleEntry($save_arr);

                // $save_arr['main_account_id'] = $stock_account->id;
                // $save_arr['other_account_id'] = $party_account->id;

                // LedgerTransaction::create($save_arr);
            }

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_INVENTORY_MOVEMENT);
            DB::commit();
            $this->saveSqlLog();
            return redirect()->back()->with('success', 'Job Work Manufacturing recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('fail', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);   
    }

    public function pay(Request $request)
    {
        if ($request->isMethod("post")) {
            // dd($request->all());

            $validatedData = $request->validate([
                "to_account_id" => ["required"],
                "from_account_id" => ["required"],
                "voucher_date" => ["required"],
                "amount" => ["required", "numeric", "min:0", "not_in:0"],
                "is_advance_pay" => "",
                "bank_transaction_no" => "",
                "narration" => "",
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
                    "voucher_type" => laravel_constant("voucher_payment"),
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

        $ledger_category_id_list = LedgerCategory::getList("id", "id", [
            "code" => [
                LedgerCategory::CODE_creditor,
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
}
