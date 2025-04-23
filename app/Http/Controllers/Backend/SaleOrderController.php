<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ArrayHelper;
use App\Models\AutoIncreament;
use App\Models\BaseModel;
use App\Models\Item;
use App\Models\Party;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SaleOrderController extends BackendController
{
    public String $routePrefix = "sale-orders";
    
    public $modelClass = SaleOrder::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "party" => function ($q)
            {
                $q->select("id", "name");
            },
            "saleOrderItem" => function ($q)
            {
                $q->with([
                    "item" => function ($q)
                    {
                        $q->with("unit");
                    }
                ]);
            }
        ]),Route::currentRouteName());

        foreach($records as $k => $record)
        {
            $records[$k]->checkPending();
        }

        // dd($records->toArray());

        $partyList = Party::getListCache();

        $this->setForView(compact("records", "partyList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "voucher_no", "type" => "string", "view_field" => "voucher_no"],
            ["field" => "party_order_no", "type" => "string", "view_field" => "party_order_no"],
            ["field" => "party_id", "type" => "int", "view_field" => "party_id"],
        ]);

        return $conditions;
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_ORDER);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
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

        if ($model && $model->saleOrderItem)
        {
            foreach($model->saleOrderItem as $saleOrderItem)
            {
                $conditions["or_id"][] = $saleOrderItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);

        $item_unit_list = Item::getUnitList('id','code');
        
        $this->setForView(compact('partyList', 'itemList', 'item_unit_list'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'party_id' => ['required'],
            'party_order_no' => ['required'],
            'order_date' => ['required'],
            'expected_delivery_date' => ['required'],
            'total_amount' => ['required'],
            'shipping_instructions' => '',
            'comments' => '',
            "sale_items.*.item_id" => ["required"],
            "sale_items.*.required_qty" => ["required"],
            "sale_items.*.rate" => "",
            "sale_items.*.amount" => "",
            "sale_items.*.comments" => "",
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            "sale_items.*.item_id.required" => "Item is required",
            "sale_items.*.required_qty.required" => "Qty is required",
        ];
    }

    public function store(Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), [
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);
        $validatedData['order_date'] = \Carbon\Carbon::parse($validatedData['order_date'])->format('Y-m-d');
        $validatedData['expected_delivery_date'] = \Carbon\Carbon::parse($validatedData['expected_delivery_date'])->format('Y-m-d');
        // d($validatedData); exit;

        try
        {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->ignoreKeys(["sale_items"]);

            $save_data['voucher_no'] = $voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_SALE_ORDER);
            // dd($save_data);

            $model = $this->modelClass::create($save_data);
            if (!$model)
            {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            DB::commit();

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_PURCHASE_ORDER);

            $this->saveSqlLog();

            return back()->with('success', "$voucher_no created successfully");
        }
        catch(Exception $ex)
        {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _afterSave($validatedData, SaleOrder $model)
    {
        if (!isset($validatedData['sale_items'])  || empty($validatedData['sale_items']))
        {
            throw new Exception("Items are required");
        }

        $item_id_list = $model->saleOrderItem()->pluck("item_id", "item_id")->toArray();

        foreach($validatedData['sale_items'] as $arr)
        {
            $purchase_order_item = $model->saleOrderItem()->where("item_id", $arr['item_id'])->first();

            if ($purchase_order_item)
            {
                unset($item_id_list[$arr['item_id']]);
                $purchase_order_item->update($arr);
            }
            else
            {
                $saleOrderItem = new SaleOrderItem();
                $saleOrderItem->fill($arr);
                $model->saleOrderItem()->save($saleOrderItem);
            }
        }

        $model->saleOrderItem()->whereIn("item_id", $item_id_list)->delete();
    }

    public function edit($id)
    {
        $model = $this->modelClass::with([
            "saleOrderItem",
        ])->findOrFail($id);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $sale_items = $model->saleOrderItem->toArray();

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form", "sale_items"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), [
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);
        $validatedData['order_date'] = \Carbon\Carbon::parse($validatedData['order_date'])->format('Y-m-d');
        $validatedData['expected_delivery_date'] = \Carbon\Carbon::parse($validatedData['expected_delivery_date'])->format('Y-m-d');

        try
        {
            // d($validatedData, true);
            
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->ignoreKeys(["sale_items"]);

            // dd($save_data);

            $model = $this->modelClass::findOrFail($id);

            $model->fill($save_data);

            if ( !$model->save() )
            {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            DB::commit();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        }
        catch(Exception $ex)
        {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try
        {
            $model = $this->modelClass::findOrFail($id)->with("saleOrderItem");

            $model->checkPending();

            $response['data'] = $model->toArray();
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function ajax_get_already_order_qty($party_id, $item_id, $id = 0)
    {
        $response = ["status" => 1];
        try
        {
            $builder = $this->modelClass::where("party_id", $party_id)->with([
                "saleOrderItem" => function ($q) use ($item_id)
                {
                    $q->where("item_id", $item_id);
                }
            ]);
            
            if($id)
            {
                $builder->where("id", "<>", $id);
            }
            
            $records = $builder->get();
            $order_qty = 0;
            foreach($records as $record)
            {
                $record->checkPending();
                
                $order_qty += $record->pending_qty;
            }
            
            $response['data']['party_id'] = $party_id;
            $response['data']['item_id'] = $item_id;
            $response['data']['order_qty'] = $order_qty;
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function ajax_get_list($party_id, $ignore_ids = "")
    {
        $response = ["status" => 1, "data" => []];

        $ignore_id_list = explode(",", $ignore_ids);

        $conditions = [];

        if ($party_id)
        {
            $conditions["party_id"] = $party_id;
        }

        $records = $this->modelClass::where($conditions)->with("saleOrderItem")
                            ->orderBy("id", "DESC")
                            ->get()
                            ->toArray();

        $response['data'] = [];

        foreach($records as $record)
        {
            $pending_qty = 0;

            foreach($record['sale_order_item'] as $sale_order_item)
            {
                $pending_qty += $sale_order_item['required_qty'] - $sale_order_item['sent_qty'];
            }

            if ($pending_qty > 0 || in_array($record['id'], $ignore_id_list))
            {
                $response['data'][] = [
                    "id" => $record['id'],
                    "name" => $record['voucher_no'],
                ];
            }
        }

        return $this->responseJson($response);
    }

    public function print($id)
    {
        $model = $this->modelClass::with([
            "party" => function ($q)
            {
                $q->with(["city"]);
            },
            "saleOrderItem" => function ($q)
            {
                $q->with([
                    "item" => function ($q)
                    {
                        $q->with("unit");
                    }
                ]);
            }
        ])->findOrFail($id);
        // dd($model->toArray());

        $this->setForView(compact("model"));

        return $this->view(__FUNCTION__);
    }
}
