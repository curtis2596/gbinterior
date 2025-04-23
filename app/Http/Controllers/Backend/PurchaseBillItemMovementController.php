<?php

namespace App\Http\Controllers\Backend;

use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseBillItemWarehouse;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


class PurchaseBillItemMovementController extends BackendController
{
    public String $routePrefix = "purchase-bill-item-movement";

    public $modelClass = PurchaseBillItemWarehouse::class;

    private PurchaseBill $purchaseBill;

    private function _get_purchaseBill($purchase_bill_id)
    {
        $this->purchaseBill = $purchaseBill = PurchaseBill::with([
            "party",
            "purchaseBillItem",
        ])->findOrFail($purchase_bill_id);

        $this->setForView(compact("purchaseBill"));
    }

    public function index($purchase_bill_id)
    {
        $this->_get_purchaseBill($purchase_bill_id);

        $purchase_bill_item_id_list = $this->purchaseBill->purchaseBillItem->pluck("id")->toArray();



        $builder = $this->modelClass::whereIn("purchase_bill_item_id", $purchase_bill_item_id_list);

        $builder->with([
            "warehouse",
            "purchaseBillItem" => function ($q) {
                $q->with([
                    "item" => function ($q) {
                        $q->select("id", "name", "sku", "unit_id");
                        $q->with([
                            "unit" => function ($q)
                            {
                                $q->select("id", "name", "code");
                            }
                        ]);
                    }
                ]);
            }
        ]);

        $records = $this->getPaginagteRecords($builder,Route::currentRouteName());

        // d($records->toArray()); exit;

        $this->_set_list();

        $form = [
            'url' => route($this->routePrefix . '.store', [$purchase_bill_id]),
            'method' => 'POST',
        ];

        $total_qty = $this->purchaseBill->purchaseBillItem->sum("qty");
        $total_return_qty = $this->purchaseBill->purchaseBillItem->sum("return_qty");

        $moved_qty = $this->modelClass::whereIn("purchase_bill_item_id", $purchase_bill_item_id_list)->sum("qty");

        $pending_qty_to_move = $total_qty - $total_return_qty - $moved_qty;

        $this->setForView(compact("records", "form", 'total_qty', 'total_return_qty', 'moved_qty', 'pending_qty_to_move'));

        return $this->viewIndex("index");
    }

    private function _set_list()
    {
        $warehouseList = Warehouse::getList();

        $item_id_counter_list = [];
        foreach($this->purchaseBill->purchaseBillItem as $purchaseBillItem)
        {
            $item_id = $purchaseBillItem->item_id;
            if (!isset($item_id_counter_list[$item_id]))
            {
                $item_id_counter_list[$item_id] = 0;
            }

            $item_id_counter_list[$item_id]++;
        }

        $itemList = [];
        foreach($this->purchaseBill->purchaseBillItem as $purchaseBillItem)
        {
            $name = $purchaseBillItem->item->getDisplayName();

            if ($item_id_counter_list[$purchaseBillItem->item_id] > 1)
            {
                $name .= " ( " . $this->purchaseBill->getDisplayName()  . " )";
            }

            $itemList[$purchaseBillItem->id] = $name;
        }

        asort($itemList);

        // dd($itemList);


        $this->setForView(compact('warehouseList', 'itemList'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'purchase_bill_item_id' => ['required'],
            'warehouse_id' => ['required'],
            "qty" => ["required", "numeric", "min:0", "not_in:0"],
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            "qty.required" => "Qty is required",
            "qty.numeric" => "Qty should be numeric",
            "qty.min" => "Qty should be greter than 0",
            "qty.not_in" => "Qty should be greter than 0",
        ];
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = $this->_get_comman_validation_rules();

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        // d($validatedData); exit;

        try {
            DB::beginTransaction();


            $model = $this->modelClass::create($validatedData);

            if (!$model) {
                throw_exception("Fail To Save");
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

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }


    public function ajax_get_pending_qty($purchase_bill_item_id)
    {
        $response = ["status" => 1, "data" => []];

        try
        {
            $purchaseBillItem = PurchaseBillItem::with("purchaseBillItemWarehouse")->findOrFail($purchase_bill_item_id);

            $saved_qty = $purchaseBillItem->purchaseBillItemWarehouse->sum("qty");

            $pending_qty = $purchaseBillItem->qty - $purchaseBillItem->return_qty - $saved_qty;

            $response['data']['pending_qty'] = $pending_qty;
            $response['data']['unit'] = $purchaseBillItem->item->unit->getDisplayName();
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

}
