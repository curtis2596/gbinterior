<?php

namespace App\Http\Controllers\Backend;

use App\Models\AutoIncreament;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseInventoryMovement;
use App\Models\WarehouseInventoryMovementItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class WarehouseMovementController extends BackendController
{
    public String $routePrefix = "warehouse-movements";
    public $modelClass = WarehouseInventoryMovement::class;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            'fromWarehouse',
            'toWarehouse',
            'item'
        ]),Route::currentRouteName());
        // dd($records->toArray());

        $warehouse_list = Warehouse::getList();

        $this->setForView(compact("records", "warehouse_list"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field' => 'challan_no', 'type' => 'string'],
            ['field' => 'from_warehouse_id', 'type' => ''],
            ['field' => 'to_warehouse_id', 'type' => '']
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
        $warehouse_list = Warehouse::getList();
        $item_list = Item::getList();

        $this->setForView(compact("warehouse_list", "item_list"));
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
            'challan_date' => "required|date",
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'warehouse_movements.item_id' => 'required|array',
            'warehouse_movements.item_id.*' => 'required|exists:items,id',
            'warehouse_movements.qty' => 'required|array',
            'warehouse_movements.qty.*' => 'required|numeric|min:1',
            'comments' => 'nullable|string'
        ]);
        $validated['challan_date'] = \Carbon\Carbon::parse($validated['challan_date'])->format('Y-m-d');
        try {
            DB::beginTransaction();
            $validated['challan_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_INVENTORY_MOVEMENT);
            $inventoryMovement = WarehouseInventoryMovement::create($validated);
            foreach ($validated['warehouse_movements']['item_id'] as $key => $itemId) {
                WarehouseInventoryMovementItem::processStockMovement([
                    'warehouse_movement_id' => $inventoryMovement->id,
                    'item_id' => $itemId,
                    'qty' => $validated['warehouse_movements']['qty'][$key],
                ]);
            }
            // dd("test");
            AutoIncreament::increaseCounter(AutoIncreament::TYPE_INVENTORY_MOVEMENT);
            DB::commit();
            return redirect()->back()->with('success', 'Warehouse Movement recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('fail', $e->getMessage());
        }
    }

    public function getAvailableStock(Request $request)
    {
        $quantity = 0;
        $warehouse_id = $request->warehouse_id;
        $item_id = $request->item_id;

        $stock = WarehouseStock::where('warehouse_id', $warehouse_id)
            ->where('item_id', $item_id)
            ->first();
        if ($stock) {
            $quantity = $stock->getAvailabilitQty();
        }

        return response()->json(['available_quantity' => $quantity]);
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $tId = $model->to_warehouse_id;

        $WarehouseInventoryMovementItem = WarehouseInventoryMovementItem::where('warehouse_movement_id', $id)->first();

        $itemId = $WarehouseInventoryMovementItem->item_id;
        
        $wsf = WarehouseStock::where('item_id', $itemId)->where('warehouse_id', $model->to_warehouse_id)->first();

        $wsf->qty = '0';
        $wsf->save();
        
        $wsf = WarehouseStock::where('item_id', $itemId)->where('warehouse_id', $model->from_warehouse_id)->first();
        $wsf->qty += $WarehouseInventoryMovementItem->qty;
        $wsf->save();

        return $this->_destroy($model);
    }
}
