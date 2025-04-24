<?php

namespace App\Http\Controllers\Backend;


use App\Helpers\ArrayHelper;
use App\Helpers\DateUtility;
use App\Models\AutoIncreament;
use App\Models\Company;
use App\Models\Transport;
use App\Models\Item;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\LedgerPayment;
use App\Models\LedgerTransaction;
use App\Models\Party;
use App\Models\SaleBill;
use App\Models\SaleBillItem;
use App\Models\SaleBillItemWarehouse;
use App\Models\SaleBillSaleOrder;
use App\Models\SaleOrderItem;
use App\Models\SaleReturn;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class SaleBillController extends BackendController
{
    public String $routePrefix = "sale-bills";

    public $modelClass = SaleBill::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "party" => function ($q) {
                $q->select("id", "name");
            },
            "saleBillItem" => function ($q) {
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
            ["field" => "reference_no", "type" => "string", "view_field" => "reference_no"],
            ["field" => "narration", "type" => "string", "view_field" => "narration"],
            ["field" => "bill_date", "type" => "from_date", "view_field" => "from_bill_date"],
            ["field" => "bill_date", "type" => "to_date", "view_field" => "to_bill_date"],
        ]);

        return $conditions;
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_BILL);
        $model->bill_date = date(DateUtility::DATE_OUT_FORMAT);
        $transportes = Transport::where('is_active',1)->get(); 
        
        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", 'form','transportes'));

        return $this->view("form");
    }

    public function create_with_so()
    {
        $model = new $this->modelClass();
        $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_BILL);
        $model->bill_date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", 'form'));

        return $this->view("form_with_so");
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

        if ($model && $model->saleBillItem)
        {
            foreach($model->saleBillItem as $billItem)
            {
                $conditions["or_id"][] = $billItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions); 
        
        $itemMaxGSTList = Item::getListCache("id", "max_gst_per");

        $this->setForView(compact('partyList', 'itemList', 'itemMaxGSTList'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'sale_order_ids' => "",
            'party_id' => ['required'],
            'transport' =>"",
            'vehicle_no' =>"",
            'dispatch' =>"",
            'delivered' =>"",
            'bill_date' => ['required'],
            'reference_no' => [],
            'amount' => ['required'],
            'freight' => "",
            'discount' => "",
            'igst' => ['required'],
            'sgst' => ['required'],
            'cgst' => ['required'],
            'receivable_amount' => ['required'],
            'narration' => ['required'],
            'comments' => "",
            'with_order' => ['required'],
            "sale_items.*.id" => "",
            "sale_items.*.sale_order_item_id" => "",
            "sale_items.*.item_id" => ["required"],
            "sale_items.*.qty" => ["required", "numeric"],
            "sale_items.*.rate" => ["required", "numeric", "min:0", "not_in:0"],
            "sale_items.*.igst_per" => "",
            "sale_items.*.sgst_per" => "",
            "sale_items.*.cgst_per" => "",
            "sale_items.*.igst" => "",
            "sale_items.*.sgst" => "",
            "sale_items.*.cgst" => "",
            "sale_items.*.amount" => ["required", "numeric"],
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            "sale_items.*.item_id.required" => "Item is required",

            "sale_items.*.qty.required" => "Item Qty is required",
            "sale_items.*.qty.numeric" => "Item Qty should be numeric",
            "sale_items.*.qty.min" => "Item Qty should be greter than 0",
            "sale_items.*.qty.not_in" => "Item Qty should be greter than 0",

            "sale_items.*.rate.required" => "Item Rate is required",
            "sale_items.*.rate.numeric" => "Item Rate should be numeric",
            "sale_items.*.rate.min" => "Item Rate should be greter than 0",
            "sale_items.*.rate.not_in" => "Item Rate should be greter than 0",

            "sale_items.*.amount.required" => "Item Amount is required",
            "sale_items.*.amount.numeric" => "Item Amount should be numeric",
            "sale_items.*.amount.min" => "Item Amount should be greter than 0",
            "sale_items.*.amount.not_in" => "Item Amount should be greter than 0",
        ];
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = array_merge($this->_get_comman_validation_rules(), [
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        $validatedData = array_make_all_values_zero_if_null($validatedData);
 
        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->ignoreKeys([
                "sale_items",
            ]);

            $save_data['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_BILL); 

            $model = $this->modelClass::create($save_data);
            if (!$model) {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_SALE_BILL);

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
        if (!$model->relationLoaded('saleBillItem')) {
            $model->load('saleBillItem');
        }

        $sale_bill_id_list = $model->saleBillItem->pluck("id")->toArray();

        $moved_count = SaleBillItemWarehouse::whereIn("sale_bill_item_id", $sale_bill_id_list)->count();
        
        $name = $model->display_name;
        
        if ($moved_count > 0)
        {
            abort(\ACTION_NOT_PROCEED, "Items of Bill : $name are sent. can not edit or delete");
        }

        if (!$model->relationLoaded('saleReturn')) {
            $model->load('saleReturn');
        }

        if ($model->saleReturn)
        {
            abort(\ACTION_NOT_PROCEED, "Bill : $name has marked return. can not be edit or delete");
        }
    }

    public function edit($id)
    {
        $model = $this->modelClass::with([
            "saleBillItem",
            'saleBillSaleOrder',
        ])->findOrFail($id);

        // d($model->toArray()); exit;
        $transportes = Transport::where('is_active',1)->get(); 
        $this->beforeEditOrDelete($model);        

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $sale_items = $model->saleBillItem->toArray();

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form", "sale_items",'transportes'));

        $view_name = $model->with_order ? "form_with_so" : "form";

        return $this->view($view_name);
    }

    public function print($id)
    {
        $model = $this->modelClass::with([
            "saleBillItem",
            'saleBillSaleOrder',
            'transport',
        ])->findOrFail($id);


        // dd($model->toArray());

        $this->setForView(compact("model"));

        return $this->view(__FUNCTION__);
    }

    public function update($id, Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), [
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        $validatedData = array_make_all_values_zero_if_null($validatedData);

        // d($validatedData); exit;

        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->ignoreKeys([
                "sale_items"
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

    private function _afterSave($validatedData, SaleBill $model)
    {
        if (!$validatedData['sale_items']) {
            throw new Exception("Items are required");
        }

        $not_saved_list = $model->saleBillItem()->pluck("id", "id")->toArray();
        
        // d($validatedData['sale_items']); exit;
        foreach ($validatedData['sale_items'] as $arr) {

            $bill_item = $model->saleBillItem()->find($arr['id']);
            // dd($bill_item);
            if ($bill_item) {
                $order_item = $bill_item->saleOrderItem()->first();
                if ($order_item) {
                    $order_item->sent_qty -= $bill_item->qty;
                }
            }
            // dd("test");
            if ($arr['qty'] > 0) {
                if ($bill_item) {

                    $bill_item->update($arr);

                    unset($not_saved_list[$arr['id']]);
                } else {

                    $saleBillItem = new SaleBillItem();
                    $saleBillItem->fill($arr);
                    $bill_item = $model->saleBillItem()->save($saleBillItem);

                    $order_item = $bill_item->saleOrderItem()->first();
                }
            }

            if (isset($order_item) && $order_item) {
                $order_item->sent_qty += $arr['qty'];
                $order_item->save();
            }
        }

        if ($not_saved_list) {
            // dd($not_saved_list);
            $not_saved_bill_items = $model->saleBillItem()->whereIn("id", $not_saved_list)->get();

            foreach ($not_saved_bill_items as $not_saved_bill_item) {
                $not_saved_bill_item->delete();
            }

            // dd($not_saved_bill_items);
        }

        if (isset($validatedData['sale_order_ids']) && $validatedData['sale_order_ids']) {
            $model->saleBillSaleOrder()->delete();

            foreach ($validatedData['sale_order_ids'] as $sale_order_id) {
                $arr = [
                    "sale_order_id" => $sale_order_id
                ];

                $child_model = new SaleBillSaleOrder();
                $child_model->fill($arr);
                $model->saleBillSaleOrder()->save($child_model);
            }
        }

        // dd($model); 
        $model->ledgerTransaction()->delete();

        if ($model->receivable_amount > 0) {

            $amount_without_gst = $model->receivable_amount - $model->igst - $model->sgst - $model->cgst ;

            $sale_account = LedgerAccount::getByCode(LedgerAccount::CODE_sale);

            $igst_account = LedgerAccount::getByCode(LedgerAccount::CODE_igst);

            $sgst_account = LedgerAccount::getByCode(LedgerAccount::CODE_sgst);

            $cgst_account = LedgerAccount::getByCode(LedgerAccount::CODE_cgst);

            $party_account = $model->party->ledgerAccount()->first();

            if (!$party_account) {
                abort(\ACTION_NOT_PROCEED, "Party's Ledger Account Not Found");
            }

            $save_arr = [
                "main_account_id" => $party_account->id,
                "other_account_id" => $sale_account->id,
                "voucher_type" => laravel_constant("voucher_sale"),
                "voucher_date" => $model->bill_date,
                "voucher_no" => $model->voucher_no,
                "amount" => $amount_without_gst,
                "narration" => $model->narration,
                "sale_bill_id" => $model->id
            ];

            // dd($save_arr);

            LedgerTransaction::create($save_arr);

            $save_arr['main_account_id'] = $sale_account->id;
            $save_arr['other_account_id'] = $party_account->id;

            LedgerTransaction::create($save_arr);

            if ($model->igst > 0)
            {
                $save_arr['main_account_id'] = $party_account->id;
                $save_arr['other_account_id'] = $igst_account->id;
                $save_arr['amount'] = $model->igst;

                LedgerTransaction::createDoubleEntry($save_arr);
            }

            if ($model->sgst > 0)
            {
                $save_arr['main_account_id'] = $party_account->id;
                $save_arr['other_account_id'] = $sgst_account->id;
                $save_arr['amount'] = $model->sgst;

                LedgerTransaction::createDoubleEntry($save_arr);
            }


            if ($model->cgst > 0)
            {
                $save_arr['main_account_id'] = $party_account->id;
                $save_arr['other_account_id'] = $cgst_account->id;
                $save_arr['amount'] = $model->cgst;

                LedgerTransaction::createDoubleEntry($save_arr);
            }
        }
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $this->beforeEditOrDelete($model);

        return $this->_destroy($model);        
    }

    public function ajax_get_items($party_id, $sale_order_ids, $id = 0)
    {
        $response = ["status" => 1, "data" => []];

        try
        {
            $qty_variation_per = Setting::getValueOrFail("sale_qty_variation_percentage_between_order_and_bill");
            $rate_variation_per = Setting::getValueOrFail("sale_rate_variation_percentage_between_order_and_bill");

            // dd($sale_qty_variation_percentage_between_order_and_bill);

            $sale_order_id_list = explode(",", $sale_order_ids);

            // dd($sale_order_id_list);

            $sale_order_items = SaleOrderItem::whereIn("sale_order_id", $sale_order_id_list)->with([
                "item" => function ($q) {
                    $q->with(["unit"]);
                },
                "saleOrder"
            ])->get();

            // dd($sale_order_items);

            $bill_item_records = [];

            if ($id) {
                $temp = SaleBillItem::where("sale_bill_id", $id)->get()->toArray();

                foreach ($temp as $arr) {
                    $bill_item_records[$arr['item_id']][$arr['sale_order_item_id']] = $arr;
                }

                // d($bill_item_records); exit;
            }

            $item_list = Item::getListCache("id", "name");


            $records = [];

            foreach ($sale_order_items as $k => $sale_order_item) {
                // $bill_item_record = $bill_item_records[$record->item_id] ?? [];

                $record = $sale_order_item->toArray();
                $record['number_round_type'] = $sale_order_item->item->unit->number_round_type;

                $record['sale_order_item_id'] = $record['id'];
                unset($record['id']);

                $record['item']['full_name'] = $item_list[$record['item']['id']];

                if (!isset($record["item"]['unit']) || empty($record["item"]['unit'])) {
                    $record["item"]['unit'] = [];
                }



                if (isset($bill_item_records[$record['item_id']][$record['sale_order_item_id']])) {
                    $bill_item = $bill_item_records[$record['item_id']][$record['sale_order_item_id']];
                    // d($bill_item);

                    $record =  array_merge($record, $bill_item);

                    $record['sent_qty'] -= $bill_item['qty'];
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

                $record['pending_qty'] = $record['required_qty'] - $record['sent_qty'];
                $record['pending_qty'] = $sale_order_item->item->unit->round($record['pending_qty']);

                $record['max_qty'] = 0;

                if ($record['pending_qty'] > 0)
                {
                    $variation_qty = $record['pending_qty'] * $qty_variation_per / 100;
                    $variation_qty = $sale_order_item->item->unit->round($variation_qty);
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

            // dd($records);

            $response['data'] = $records;
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function return_items($sale_bill_id, Request $request)
    {
        if ($request->isMethod("post"))
        {
            // d($request->all()); exit;
            $rules = [
                'voucher_date' => ['required'],
                'refrence_no' => "",
                'amount' => ['required'],
                'other_deduction' => "",
                'other_deduction_reason' => "",
                'igst' => ['required'],
                'sgst' => ['required'],
                'cgst' => ['required'],
                'payable_amount' => ['required'],
                'narration' => ['required'],
                'comments' => "",
                "sale_items.*.id" => "",
                "sale_items.*.item_id" => ["required"],                
                "sale_items.*.return_qty" => ["nullable", "numeric", "min:0"],
                "sale_items.*.return_igst" => "",
                "sale_items.*.return_sgst" => "",
                "sale_items.*.return_cgst" => "",                
                "sale_items.*.return_amount" => ["nullable", "numeric"],
            ];

            $validatedData = $request->validate($rules);

            // d($validatedData); exit;

            try {
                
                if (!$validatedData['sale_items']) {
                    throw new Exception("Items are required");
                }

                DB::beginTransaction();
    
                $arry_helper = new ArrayHelper($validatedData);
    
                $save_data = $arry_helper->ignoreKeys(["sale_items"]);                   
    
                $save_data['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_RETURN);
                $save_data['sale_bill_id'] = $sale_bill_id;

                $save_data = array_make_all_values_zero_if_null($save_data);

                // dd($save_data);

                $model = SaleReturn::where("sale_bill_id", $sale_bill_id)->first();

                if (!$model)
                {
                    $model = new SaleReturn();
                }
    
                $model->fill($save_data);

                if (!$model->save()) 
                {
                    throw_exception("Fail To Save");
                }

                AutoIncreament::increaseCounter(AutoIncreament::TYPE_SALE_RETURN);
        
                foreach ($validatedData['sale_items'] as $arr) 
                {
                    if ($arr['return_qty'] > 0)
                    {
                        $bill_item = SaleBillItem::find($arr['id']);
            
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

                if ($model->payable_amount > 0) {

                    $amount_without_gst = $model->payable_amount - $model->igst - $model->sgst - $model->cgst ;
        
                    $sale_account = LedgerAccount::getByCode(LedgerAccount::CODE_sale);
        
                    $igst_account = LedgerAccount::getByCode(LedgerAccount::CODE_igst);
        
                    $sgst_account = LedgerAccount::getByCode(LedgerAccount::CODE_sgst);
        
                    $cgst_account = LedgerAccount::getByCode(LedgerAccount::CODE_cgst);
        
                    $party_account = $model->saleBill->party->ledgerAccount()->first();
        
                    if (!$party_account) {
                        abort(\ACTION_NOT_PROCEED, "Party's Ledger Account Not Found");
                    }
        
                    $save_arr = [
                        "main_account_id" => $party_account->id,
                        "other_account_id" => $sale_account->id,
                        "voucher_type" => laravel_constant("voucher_sale_return"),
                        "voucher_date" => $model->voucher_date,
                        "voucher_no" => $model->voucher_no,
                        "amount" => -1 * $amount_without_gst,
                        "narration" => $model->narration,
                        "sale_bill_id" => $model->id
                    ];
        
                    // dd($save_arr);
        
                    LedgerTransaction::create($save_arr);
        
                    $save_arr['main_account_id'] = $sale_account->id;
                    $save_arr['other_account_id'] = $party_account->id;
        
                    LedgerTransaction::create($save_arr);
        
                    if ($model->igst > 0)
                    {
                        $save_arr['main_account_id'] = $igst_account->id;
                        $save_arr['other_account_id'] = $party_account->id;
                        $save_arr['amount'] = $model->igst;
        
                        LedgerTransaction::createDoubleEntry($save_arr);
                    }
        
                    if ($model->sgst > 0)
                    {
                        $save_arr['main_account_id'] = $sgst_account->id;
                        $save_arr['other_account_id'] = $party_account->id;
                        $save_arr['amount'] = $model->sgst;
        
                        LedgerTransaction::createDoubleEntry($save_arr);
                    }
        
        
                    if ($model->cgst > 0)
                    {
                        $save_arr['main_account_id'] = $cgst_account->id;
                        $save_arr['other_account_id'] = $party_account->id;
                        $save_arr['amount'] = $model->cgst;
        
                        LedgerTransaction::createDoubleEntry($save_arr);
                    }
                }
        
                
    
                DB::commit();
    
                $this->saveSqlLog();
    
                return back()->with('success', 'Record created successfully');
            } catch (Exception $ex) {
                DB::rollBack();
    
                $this->saveSqlLog();
    
                return back()->withInput()->with('fail', $ex->getMessage());
            }
        }

        $saleBill = SaleBill::with([
            "saleBillItem.item.unit", 
            "saleBillItem.saleBillItemWarehouse",
            "saleReturn"
        ])->findOrFail($sale_bill_id);

        $total_qty = $moved_qty = $return_qty = 0;
        foreach($saleBill->saleBillItem as $saleBillItem)
        {
            $total_qty += $saleBillItem->qty;
            $return_qty += $saleBillItem->return_qty;

            $saleBillItem->moved_qty = 0;
            foreach($saleBillItem->saleBillItemWarehouse as $saleBillItemWarehouse)
            {
                $saleBillItem->moved_qty += $saleBillItemWarehouse->qty;
            }

            $moved_qty += $saleBillItem->moved_qty;
        }

        // dd($saleBill->toArray());

        $model = $saleBill->saleReturn;

        if (is_null($model))
        {
            $model = new SaleReturn();                
            $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_RETURN);
            $model->voucher_date = date(DateUtility::DATE_OUT_FORMAT);
        }

        $this->setForView(compact("saleBill", "total_qty", "return_qty", "moved_qty", "model"));

        return $this->view(__FUNCTION__);
    }
}
