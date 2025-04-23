<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ArrayHelper;
use App\Models\AutoIncreament;
use App\Models\BaseModel;
use App\Models\Company;
use App\Models\Item;
use App\Models\Party;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Unit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PurchaseOrderController extends BackendController
{
    public String $routePrefix = "purchase-orders";

    public $modelClass = PurchaseOrder::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "party" => function ($q) {
                $q->select("id", "name");
            },
            "purchaseOrderItem" => function ($q) {
                $q->with([
                    "item" => function ($q) {
                        $q->with("unit");
                    }
                ]);
            }
        ]), Route::currentRouteName());

        foreach ($records as $k => $record) {
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
            ["field" => "party_id", "type" => "int", "view_field" => "party_id"],
        ]);

        return $conditions;
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->voucher_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_ORDER);

        $company = Company::first();

        if ($company) {
            $model->terms = $company->terms;
        }

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

        if ($model && $model->party_id) {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        $conditions = [
            "is_finished_item" => 0,
            "or_id" => []
        ];

        if ($model && $model->purchaseOrderItem) {
            foreach ($model->purchaseOrderItem as $purchaseOrderItem) {
                $conditions["or_id"][] = $purchaseOrderItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);
        $item_unit_list = Item::getUnitList();

        $this->setForView(compact('partyList', 'itemList', 'item_unit_list'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'po_date' => ['required'],
            'party_id' => ['required'],
            'expected_delivery_date' => ['required'],
            'total_amount' => ['required'],
            'terms' => ['required'],
            'shipping_instructions' => '',
            'comments' => '',
            "purchase_items.*.item_id" => ["required"],
            "purchase_items.*.required_qty" => ["required"],
            "purchase_items.*.rate" => "",
            "purchase_items.*.amount" => "",
            "purchase_items.*.comments" => "",
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            "purchase_items.*.item_id.required" => "Item is required",
            "purchase_items.*.required_qty.required" => "Qty is required",
        ];
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = array_merge($this->_get_comman_validation_rules(), []);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        // d($validatedData);

        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->ignoreKeys([
                "purchase_items"
            ]);
            $save_data['voucher_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PURCHASE_ORDER);
            // dd($save_data);

            $model = $this->modelClass::create($save_data);
            if (!$model) {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            DB::commit();

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_PURCHASE_ORDER);

            $this->saveSqlLog();

            return back()->with('success', 'Record created successfully');
        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _afterSave($validatedData, PurchaseOrder $model)
    {
        if (!isset($validatedData['purchase_items'])  || empty($validatedData['purchase_items'])) {
            throw new Exception("Items are required");
        }

        $item_id_list = $model->purchaseOrderItem()->pluck("item_id", "item_id")->toArray();

        foreach ($validatedData['purchase_items'] as $arr) {
            $purchase_order_item = $model->purchaseOrderItem()->where("item_id", $arr['item_id'])->first();

            if ($purchase_order_item) {
                unset($item_id_list[$arr['item_id']]);
                $purchase_order_item->update($arr);
            } else {
                $purchaseOrderItem = new PurchaseOrderItem();
                $purchaseOrderItem->fill($arr);
                $model->purchaseOrderItem()->save($purchaseOrderItem);
            }
        }

        $model->purchaseOrderItem()->whereIn("item_id", $item_id_list)->delete();
    }

    public function edit($id)
    {
        $model = $this->modelClass::with([
            "purchaseOrderItem",
        ])->findOrFail($id);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $purchase_items = $model->purchaseOrderItem->toArray();

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form", "purchase_items"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), []);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->ignoreKeys(["purchase_items"]);

            // dd($save_data);

            $model = $this->modelClass::findOrFail($id);

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

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::findOrFail($id)->with("purchaseOrderItem");

            $model->checkPending();

            $response['data'] = $model->toArray();
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function ajax_get_already_order_qty($party_id, $item_id, $id = 0)
    {
        $response = ["status" => 1];
        try {
            $builder = $this->modelClass::where("party_id", $party_id)->with([
                "purchaseOrderItem" => function ($q) use ($item_id) {
                    $q->where("item_id", $item_id);
                }
            ]);

            if ($id) {
                $builder->where("id", "<>", $id);
            }

            $records = $builder->get();

            $order_qty = 0;
            foreach ($records as $record) {
                $record->checkPending();

                $order_qty += $record->pending_qty;
            }

            $response['data']['party_id'] = $party_id;
            $response['data']['item_id'] = $item_id;
            $response['data']['order_qty'] = $order_qty;
        } catch (Exception $ex) {
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

        if ($party_id) {
            $conditions["party_id"] = $party_id;
        }

        $records = $this->modelClass::where($conditions)->with("purchaseOrderItem")
            ->orderBy("id", "DESC")
            ->get()
            ->toArray();

        $response['data'] = [];

        foreach ($records as $record) {
            $pending_qty = 0;

            foreach ($record['purchase_order_item'] as $purchase_order_item) {
                $pending_qty += $purchase_order_item['required_qty'] - $purchase_order_item['received_qty'];
            }

            if ($pending_qty > 0 || in_array($record['id'], $ignore_id_list)) {
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
            "party" => function ($q) {
                $q->with(["city"]);
            },
            "purchaseOrderItem" => function ($q) {
                $q->with([
                    "item" => function ($q) {
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
