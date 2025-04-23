<?php

namespace App\Http\Controllers\Backend;

use App\Models\SaleBill;
use App\Models\SaleBillItem;
use App\Models\SaleBillItemWarehouse;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SaleBillItemMovementController extends BackendController
{
    public String $routePrefix = "sale-bill-item-movement";

    public $modelClass = SaleBillItemWarehouse::class;

    private SaleBill $saleBill;

    private function _get_saleBill($sale_bill_id)
    {
        $this->saleBill = $saleBill = SaleBill::with([
            "party",
            "saleBillItem",
        ])->findOrFail($sale_bill_id);

        $this->setForView(compact("saleBill"));
    }

    public function index($sale_bill_id)
    {
        $this->_get_saleBill($sale_bill_id);

        $bill_item_id_list = $this->saleBill->saleBillItem->pluck("id")->toArray();

        $builder = $this->modelClass::whereIn("sale_bill_item_id", $bill_item_id_list);

        $builder->with([
            "warehouse",
            "saleBillItem" => function ($q) {
                $q->with([
                    "item" => function ($q) {                        
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
            'url' => route($this->routePrefix . '.store', [$sale_bill_id]),
            'method' => 'POST',
        ];

        $total_qty = $this->saleBill->saleBillItem->sum("qty");   
        $total_return_qty = $this->saleBill->saleBillItem->sum("return_qty");

        $moved_qty = $this->modelClass::whereIn("sale_bill_item_id", $bill_item_id_list)->sum("qty");

        $pending_qty_to_move = $total_qty - $total_return_qty - $moved_qty;

        $this->setForView(compact("records", "form", 'total_qty', 'moved_qty', 'total_return_qty', 'pending_qty_to_move'));

        return $this->viewIndex("index");
    }

    private function _set_list()
    {
        $warehouseList = Warehouse::getList();

        $item_id_counter_list = [];
        foreach($this->saleBill->saleBillItem as $saleBillItem)
        {
            $item_id = $saleBillItem->item_id;
            if (!isset($item_id_counter_list[$item_id]))
            {
                $item_id_counter_list[$item_id] = 0;
            }

            $item_id_counter_list[$item_id]++;
        }

        $itemList = [];
        foreach($this->saleBill->saleBillItem as $saleBillItem)
        {
            $name = $saleBillItem->item->getDisplayName();

            if ($item_id_counter_list[$saleBillItem->item_id] > 1)
            {
                $name .= " ( " . $this->saleBill->getDisplayName()  . " )";
            }

            $itemList[$saleBillItem->id] = $name;
        }

        asort($itemList);

        // dd($itemList);
        

        $this->setForView(compact('warehouseList', 'itemList'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'sale_bill_item_id' => ['required'],
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


    public function ajax_get_pending_qty($sale_bill_item_id)
    {
        $response = ["status" => 1, "data" => [
            "pending_qty" => 0,
            "unit" => ""
        ]];

        try
        {
            $saleBillItem = SaleBillItem::with("saleBillItemWarehouse")->findOrFail($sale_bill_item_id);

            $saved_qty = $saleBillItem->saleBillItemWarehouse->sum("qty");

            $pending_qty = $saleBillItem->qty - $saved_qty;

            $response['data']['pending_qty'] = $pending_qty;
            $response['data']['unit'] = $saleBillItem->item->unit->getDisplayName();
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

}
