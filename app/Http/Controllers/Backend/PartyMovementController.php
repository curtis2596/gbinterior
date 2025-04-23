<?php

namespace App\Http\Controllers\Backend;

use App\Models\AutoIncreament;
use App\Models\Item;
use App\Models\Party;
use App\Models\PartyInventoryMovement;
use App\Models\PartyInventoryMovementItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class PartyMovementController extends BackendController
{
    public String $routePrefix = "party-movements";
    public $modelClass = PartyInventoryMovement::class;

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
            'Warehouse',
            'party',
            'item',
            'item.unit'
        ]),Route::currentRouteName());
        // dd($records->toArray());

        $warehouse_list = Warehouse::getListCache();
        $party_list = Party::getListCache();

        $this->setForView(compact("records", "warehouse_list", "party_list"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field' => 'challan_no', 'type' => 'string'],
            ['field' => 'warehouse_id', 'type' => ''],
            ['field' => 'party_id', 'type' => '']
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
        $party_list = Party::getList();

        $warehouse_list = Warehouse::getList();

        $item_list = Item::getList("id", "name");

        $this->setForView(compact("party_list", "warehouse_list", "item_list"));
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'party_id' => 'required|exists:parties,id',
            'party_movements.item_id' => 'required|array',
            'party_movements.item_id.*' => 'required|exists:items,id',
            'party_movements.qty' => 'required|array',
            'party_movements.qty.*' => 'required|numeric|min:1',
            'comments' => 'nullable|string',
        ]);
        $validated['challan_date'] = \Carbon\Carbon::parse($validated['challan_date'])->format('Y-m-d');
        try {
            DB::beginTransaction();
            $validated['challan_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_PARTY_INVENTORY_MOVEMENT);
            $inventoryMovement = PartyInventoryMovement::create($validated);
            foreach ($validated['party_movements']['item_id'] as $key => $itemId) {
                PartyInventoryMovementItem::processStockMovement([
                    'party_inventory_movement_id' => $inventoryMovement->id,
                    'item_id' => $itemId,
                    'qty' => $validated['party_movements']['qty'][$key],
                    'warehouse_id' => $validated['warehouse_id'],
                ]);
            }
            // $this->modelClass::processStockMovement($validated);
            AutoIncreament::increaseCounter(AutoIncreament::TYPE_PARTY_INVENTORY_MOVEMENT);
            DB::commit();
            return redirect()->back()->with('success', 'Record save successfully');
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

        $WarehouseInventoryMovementItem = PartyInventoryMovementItem::where('party_inventory_movement_id', $id)->first();
        $itemId = $WarehouseInventoryMovementItem->item_id;

        $wsf = WarehouseStock::where('item_id', $itemId)->where('warehouse_id', $model->warehouse_id)->first();
        $wsf->qty += $WarehouseInventoryMovementItem->qty;
        $wsf->save();

        return $this->_destroy($model);
    }
}
