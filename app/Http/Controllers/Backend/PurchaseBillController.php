<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ArrayHelper;
use App\Helpers\DateUtility;
use App\Models\AutoIncreament;
use App\Models\Company;
use App\Models\Item;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\LedgerPayment;
use App\Models\LedgerTransaction;
use App\Models\Party;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseBillItemWarehouse;
use App\Models\PurchaseBillPurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class PurchaseBillController extends BackendController
{
    public String $routePrefix = "purchase-bills";

    public $modelClass = PurchaseBill::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "party" => function ($q) {
                $q->select("id", "name");
            },
            "purchaseBillItem" => function ($q) {
                $q->with([
                    "item" => function ($q) {
                        $q->with("unit");
                    }
                ]);
            }
        ]),Route::currentRouteName());

        $partyList = Party::getListCache();

        $this->setForView(compact("records", "partyList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "party_id", "type" => "int", "view_field" => "party_id"],
            ["field" => "voucher_no", "type" => "string", "view_field" => "voucher_no"],
            ["field" => "party_bill_no", "type" => "string", "view_field" => "party_bill_no"],
            ["field" => "narration", "type" => "string", "view_field" => "narration"],
            ["field" => "bill_date", "type" => "from_date", "view_field" => "from_bill_date"],
            ["field" => "bill_date", "type" => "to_date", "view_field" => "to_bill_date"],
        ]);

        return $conditions;
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_BILL);
        $model->bill_date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    public function create_with_po()
    {
        $model = new $this->modelClass();
        $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_BILL);
        $model->bill_date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", 'form'));

        return $this->view("form_with_po");
    }


    private function _set_list_for_form($model)
    {
        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->party_id)
        {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->purchaseBillItem)
        {
            foreach($model->purchaseBillItem as $purchaseBillItem)
            {
                $conditions["or_id"][] = $purchaseBillItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);   

        $itemMaxGSTList = Item::getListCache("id", "max_gst_per");

        $this->setForView(compact('partyList', 'itemList', 'itemMaxGSTList'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'purchase_order_ids' => "",
            'party_id' => ['required'],
            'party_bill_no' => ['required'],
            'bill_date' => ['required'],
            'amount' => ['required'],
            'freight' => "",
            'discount' => "",
            'igst' => ['required'],
            'sgst' => ['required'],
            'cgst' => ['required'],
            'payable_amount' => ['required'],
            'narration' => ['required'],
            'comments' => "",
            'with_po' => ['required'],
            "purchase_items.*.id" => "",
            "purchase_items.*.purchase_order_item_id" => "",
            "purchase_items.*.item_id" => ["required"],
            "purchase_items.*.qty" => ["required", "numeric"],
            "purchase_items.*.rate" => ["required", "numeric", "min:0", "not_in:0"],
            "purchase_items.*.igst_per" => "",
            "purchase_items.*.sgst_per" => "",
            "purchase_items.*.cgst_per" => "",
            "purchase_items.*.igst" => "",
            "purchase_items.*.sgst" => "",
            "purchase_items.*.cgst" => "",
            "purchase_items.*.amount" => ["required", "numeric"],
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            "purchase_items.*.item_id.required" => "Item is required",

            "purchase_items.*.qty.required" => "Qty is required",
            "purchase_items.*.qty.numeric" => "Qty should be numeric",
            "purchase_items.*.qty.min" => "Qty should be greter than 0",
            "purchase_items.*.qty.not_in" => "Qty should be greter than 0",

            "purchase_items.*.rate.required" => "Rate is required",
            "purchase_items.*.rate.numeric" => "Rate should be numeric",
            "purchase_items.*.rate.min" => "Rate should be greter than 0",
            "purchase_items.*.rate.not_in" => "Rate should be greter than 0",

            "purchase_items.*.amount.required" => "Amount is required",
            "purchase_items.*.amount.numeric" => "Amount should be numeric",
            "purchase_items.*.amount.min" => "Amount should be greter than 0",
            "purchase_items.*.amount.not_in" => "Amount should be greter than 0",
        ];
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = array_merge($this->_get_comman_validation_rules(), [
            "party_bill_no" => [
                "required",
                Rule::unique($this->tableName)->where(function ($query) use ($request) {
                    return $query
                        ->where('party_bill_no', $request->input("party_bill_no"))
                        ->where('party_id', $request->input("party_id"))
                    ;
                })
            ]
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        // d($validatedData);

        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->getOnlyWhichHaveKeys([
                "party_id",
                "party_bill_no",
                "bill_date",
                "amount",
                "freight",
                "discount",
                "igst",
                "sgst",
                "cgst",
                "payable_amount",
                "narration",
                "comments",
                "with_po"
            ]);

            $save_data['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_BILL);
            // dd($save_data);

            $model = $this->modelClass::create($save_data);
            if (!$model) {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_PURCHASE_BILL);

            DB::commit();

            $this->saveSqlLog();

            return back()->with('success', 'Record created successfully');
        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    protected function beforeEditOrDelete($model)
    {
        if (!$model->relationLoaded('purchaseBillItem')) {
            $model->load('purchaseBillItem');
        }

        $purchase_bill_id_list = $model->purchaseBillItem->pluck("id")->toArray();

        $moved_count = PurchaseBillItemWarehouse::whereIn("purchase_bill_item_id", $purchase_bill_id_list)->count();
        
        $name = $model->getDisplayName();

        if ($moved_count > 0)
        {
            abort(\ACTION_NOT_PROCEED, "Items of Bill : $name has moved to warehouse. can not edit or delete");
        }

        if (!$model->relationLoaded('purchaseReturn')) {
            $model->load('purchaseReturn');
        }

        if ($model->purchaseReturn)
        {
            abort(\ACTION_NOT_PROCEED, "Bill : $name has marked return. can not be edit or delete");
        }
    }

    public function edit($id)
    {
        $model = $this->modelClass::with([
            "purchaseBillItem",
            'purchaseBillPurchaseOrder',
        ])->findOrFail($id);

        // d($model->toArray()); exit;

        $this->beforeEditOrDelete($model);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $purchase_items = $model->purchaseBillItem->toArray();

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form", "purchase_items"));

        $view_name = $model->with_po ? "form_with_po" : "form";

        return $this->view($view_name);
    }

    public function update($id, Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), [
            "party_bill_no" => [
                "required",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {
                    return $query
                        ->where('party_bill_no', $request->input("party_bill_no"))
                        ->where('party_id', $request->input("party_id"))
                        ->where("id", "<>", $id)
                    ;
                })
            ]
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);
        // d($validatedData); exit;

        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->getOnlyWhichHaveKeys([
                "party_id",
                "party_bill_no",
                "bill_date",
                "amount",
                "freight",
                "discount",
                "igst",
                "sgst",
                "cgst",
                "payable_amount",
                "narration",
                "comments"
            ]);

            // dd($save_data);

            $model = $this->modelClass::findOrFail($id);

            $this->beforeEditOrDelete($model);

            $model->fill($save_data);

            if (!$model->save()) {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            DB::commit();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _afterSave($validatedData, PurchaseBill $model)
    {
        if (!$validatedData['purchase_items']) {
            throw new Exception("Items are required");
        }

        $not_saved_list = $model->purchaseBillItem()->pluck("id", "id")->toArray();

        // d($validatedData['purchase_items']); exit;
        foreach ($validatedData['purchase_items'] as $arr) {
            $bill_item = $model->purchaseBillItem()->find($arr['id']);

            if ($bill_item) {
                $order_item = $bill_item->purchaseOrderItem()->first();
                if ($order_item) {
                    $order_item->received_qty -= $bill_item->qty;
                }
            }

            if ($arr['qty'] > 0) {
                if ($bill_item) {

                    $bill_item->update($arr);

                    unset($not_saved_list[$arr['id']]);
                } else {

                    $purchaseBillItem = new PurchaseBillItem();
                    $purchaseBillItem->fill($arr);
                    $bill_item = $model->purchaseBillItem()->save($purchaseBillItem);

                    $order_item = $bill_item->purchaseOrderItem()->first();
                }
            }

            if (isset($order_item) && $order_item) {
                $order_item->received_qty += $arr['qty'];
                $order_item->save();
            }
        }

        if ($not_saved_list) {
            // dd($not_saved_list);
            $not_saved_bill_items = $model->purchaseBillItem()->whereIn("id", $not_saved_list)->get();

            foreach ($not_saved_bill_items as $not_saved_bill_item) {
                $not_saved_bill_item->delete();
            }

            // dd($not_saved_bill_items);
        }

        if (isset($validatedData['purchase_order_ids']) && $validatedData['purchase_order_ids']) {
            $model->purchaseBillPurchaseOrder()->delete();

            foreach ($validatedData['purchase_order_ids'] as $purchase_order_id) {
                $arr = [
                    "purchase_order_id" => $purchase_order_id
                ];

                $child_model = new PurchaseBillPurchaseOrder();
                $child_model->fill($arr);
                $model->purchaseBillPurchaseOrder()->save($child_model);
            }
        }

        $model->ledgerTransaction()->delete();

        if ($model->payable_amount > 0) {
            $purchase_account = LedgerAccount::getByCode(LedgerAccount::CODE_purchase);

            $party_account = $model->party->ledgerAccount()->first();

            if (!$party_account) {
                abort(\ACTION_NOT_PROCEED, "Party's Ledger Account Not Found");
            }

            $save_arr = [
                "main_account_id" => $purchase_account->id,
                "other_account_id" => $party_account->id,
                "voucher_type" => laravel_constant("voucher_purchase"),
                "voucher_date" => $model->bill_date,
                "voucher_no" => $model->voucher_no,
                "amount" => $model->payable_amount,
                "narration" => $model->narration,
                "purchase_bill_id" => $model->id
            ];

            // dd($save_arr);

            LedgerTransaction::createDoubleEntry($save_arr);
        }
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $this->beforeEditOrDelete($model);

        return $this->_destroy($model);        
    }


    public function ajax_get_items($party_id, $purchase_ids, $id = 0)
    {
        $response = ["status" => 1, "data" => []];

        try
        {
            $qty_variation_per = Setting::getValueOrFail("purchase_qty_variation_percentage_between_order_and_bill");
            $rate_variation_per = Setting::getValueOrFail("purchase_rate_variation_percentage_between_order_and_bill");

            $purchase_id_list = explode(",", $purchase_ids);

            // dd($purchase_id_list);

            $purchase_order_items = PurchaseOrderItem::whereIn("purchase_order_id", $purchase_id_list)->with([
                "item" => function ($q) {
                    $q->with(["unit"]);
                },
                "purchaseOrder"
            ])->get();

            // d($records);

            $bill_item_records = [];

            if ($id) {
                $temp = PurchaseBillItem::where("purchase_bill_id", $id)->get()->toArray();

                foreach ($temp as $arr) {
                    $bill_item_records[$arr['item_id']][$arr['purchase_order_item_id']] = $arr;
                }

                // d($bill_item_records); exit;
            }

            $item_list = Item::getListCache("id", "name");

            $records = [];

            foreach ($purchase_order_items as $k => $purchase_order_item) {
                // $bill_item_record = $bill_item_records[$record->item_id] ?? [];

                $record = $purchase_order_item->toArray();
                $record['number_round_type'] = $purchase_order_item->item->unit->number_round_type;

                $record['purchase_order_item_id'] = $record['id'];
                unset($record['id']);

                $record['item']['full_name'] = $item_list[$record['item']['id']];

                if (!isset($record["item"]['unit']) || empty($record["item"]['unit'])) {
                    $record["item"]['unit'] = [];
                }



                if (isset($bill_item_records[$record['item_id']][$record['purchase_order_item_id']])) {
                    $bill_item = $bill_item_records[$record['item_id']][$record['purchase_order_item_id']];
                    // d($bill_item);

                    $record =  array_merge($record, $bill_item);

                    $record['received_qty'] -= $bill_item['qty'];
                } else {

                    $party = Party::with("city")->findOrFail($party_id);

                    $company = Company::first();

                    $tax_rate = $record['item']['tax_rate'];

                    $record['sgst_per'] = $record['cgst_per'] = $record['igst_per'] = 0;
                    if ($company['state_id'] == $party['city']['state_id'])
                    {
                        $tax_rate = round($tax_rate * 0.5, 1);
                        $record['sgst_per'] = $record['cgst_per'] = $tax_rate;
                    }
                    else
                    {
                        $record['igst_per'] = $tax_rate;
                    }
                }

                // d($record);

                $record['pending_qty'] = $record['required_qty'] - $record['received_qty'];
                $record['max_qty'] = 0;

                if ($record['pending_qty'] > 0)
                {
                    $variation_qty = $record['pending_qty'] * $qty_variation_per / 100;
                    $variation_qty = $purchase_order_item->item->unit->round($variation_qty);
                    $record['max_qty'] = $record['pending_qty'] + $variation_qty;
                }

                $record['max_rate'] = 1000000;
                if ($record['rate'] > 0)
                {
                    $variation_rate = $record['rate'] * $rate_variation_per  / 100;
                    $variation_rate = round($variation_rate, 1);
                    $record['max_rate'] = $record['rate'] + $variation_rate;
                }

                $records[$k] = $record;
            }

            $response['data'] = $records;
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function return_items($purchase_bill_id, Request $request)
    {
        if ($request->isMethod("post"))
        {
            $rules = [
                'voucher_date' => ['required'],
                'refrence_no' => "",
                'amount' => ['required'],
                'other_deduction' => "",
                'other_deduction_reason' => "",
                'igst' => ['required'],
                'sgst' => ['required'],
                'cgst' => ['required'],
                'receivable_amount' => ['required'],
                'narration' => ['required'],
                'comments' => "",
                "purchase_items.*.id" => "",
                "purchase_items.*.item_id" => ["required"],                
                "purchase_items.*.return_qty" => ["nullable", "numeric", "min:0"],
                "purchase_items.*.return_igst" => "",
                "purchase_items.*.return_sgst" => "",
                "purchase_items.*.return_cgst" => "",                
                "purchase_items.*.return_amount" => ["nullable", "numeric"],
            ];

            $validatedData = $request->validate($rules);

            // d($validatedData); exit;

            try {
                
                if (!$validatedData['purchase_items']) {
                    throw new Exception("Items are required");
                }

                DB::beginTransaction();
    
                $arry_helper = new ArrayHelper($validatedData);
    
                $save_data = $arry_helper->ignoreKeys(["purchase_items"]);        
                
                $save_data = array_make_all_values_zero_if_null($save_data);
    
                $save_data['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_RETURN);
                $save_data['purchase_bill_id'] = $purchase_bill_id;
                // dd($save_data);

                $model = PurchaseReturn::where("purchase_bill_id", $purchase_bill_id)->first();

                if (!$model)
                {
                    $model = new PurchaseReturn();
                }
    
                $model->fill($save_data);

                if (!$model->save()) 
                {
                    throw_exception("Fail To Save");
                }
    
        
                foreach ($validatedData['purchase_items'] as $arr) 
                {
                    if ($arr['return_qty'] > 0)
                    {
                        $bill_item = PurchaseBillItem::find($arr['id']);
            
                        if ($bill_item) 
                        {
                            unset($arr['id']);
                            unset($arr['item_id']);

                            $bill_item->fill($arr);

                            if (!$bill_item->save())
                            {
                                throw_exception("Fail To Save");
                            }
                        }
                    }
                }
                    
                $model->ledgerTransaction()->delete();
        
                if ($model->receivable_amount > 0) {
                    $purchase_account = LedgerAccount::getByCode(LedgerAccount::CODE_purchase);
        
                    $party_account = $model->purchaseBill->party->ledgerAccount()->first();
        
                    if (!$party_account) {
                        abort(\ACTION_NOT_PROCEED, "Party's Ledger Account Not Found");
                    }
        
                    $save_arr = [
                        "main_account_id" => $party_account->id,
                        "other_account_id" => $purchase_account->id,
                        "voucher_type" => laravel_constant("voucher_purchase_return"),
                        "voucher_date" => $model->voucher_date,
                        "voucher_no" => $model->voucher_no,
                        "amount" => $model->receivable_amount,
                        "narration" => $model->narration,
                        "purchase_return_id" => $model->id
                    ];
        
                    // dd($save_arr);
        
                    LedgerTransaction::createDoubleEntry($save_arr);
                }
    
                AutoIncreament::increaseCounter(AutoIncreament::TYPE_PURCHASE_RETURN);
    
                DB::commit();
    
                $this->saveSqlLog();
    
                return back()->with('success', 'Record created successfully');
            } catch (Exception $ex) {
                DB::rollBack();
    
                $this->saveSqlLog();
    
                return back()->withInput()->with('fail', $ex->getMessage());
            }
        }

        $purchaseBill = PurchaseBill::with([
            "purchaseBillItem.item.unit", 
            "purchaseBillItem.purchaseBillItemWarehouse",
            "purchaseReturn"
        ])->findOrFail($purchase_bill_id);

        $total_qty = $moved_qty = $return_qty = 0;
        foreach($purchaseBill->purchaseBillItem as $purchaseBillItem)
        {
            $total_qty += $purchaseBillItem->qty;
            $return_qty += $purchaseBillItem->return_qty;

            $purchaseBillItem->moved_qty = 0;
            foreach($purchaseBillItem->purchaseBillItemWarehouse as $purchaseBillItemWarehouse)
            {
                $purchaseBillItem->moved_qty += $purchaseBillItemWarehouse->qty;
            }

            $moved_qty += $purchaseBillItem->moved_qty;
        }

        // dd($purchaseBill->toArray());

        $model = $purchaseBill->purchaseReturn;

        if (is_null($model))
        {
            $model = new PurchaseReturn();                
            $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_RETURN);
            $model->voucher_date = date(DateUtility::DATE_OUT_FORMAT);
        }

        $this->setForView(compact("purchaseBill", "total_qty", "return_qty", "moved_qty", "model"));

        return $this->view(__FUNCTION__);
    }
}
