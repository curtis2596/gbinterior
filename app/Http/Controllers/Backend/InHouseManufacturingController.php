<?php

namespace App\Http\Controllers\Backend;

use App\Models\ItemConversion;
use App\Models\Item;
use App\Models\Process;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class InHouseManufacturingController extends BackendController
{
    public String $routePrefix = "in-house-manufacturing";
    public $modelClass = ItemConversion::class;     

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            'Warehouse' , 'fromitem' , 'toitem' , 'process'
        ]),Route::currentRouteName());
        // dd($records);
        $warehouse_list = Warehouse::getList();
        $process_list = Process::getList('id','name');
        $item_list = Item::getList();

        $this->setForView(compact("records","warehouse_list","process_list","item_list"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field'=>'warehouse_id','type'=>''],
            ['field'=>'process_id','type'=>''],
            ['field'=>'from_item_id','type'=>''],
            ['field'=>'to_item_id','type'=>'']
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
        $warehouse_list = Warehouse::getList('id', 'name', [
            "type"=>Warehouse::TYPE_COMPANY
        ]);
        $process_list = Process::getList('id','name');
        $item_list = Item::getList();

        $this->setForView(compact("warehouse_list", "item_list","process_list"));
    }

    public function getAvailableStock(Request $request)
    {
        $quantity=0;
        $warehouse_id = $request->warehouse_id;
        $item_id = $request->from_item_id;

        $stock = WarehouseStock::where('warehouse_id', $warehouse_id)
            ->where('item_id', $item_id)
            ->first();
        if($stock){
            $quantity = $stock->getAvailabilitQty();
        }

        return response()->json(['available_quantity' => $quantity]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'from_item_id' => 'required|exists:items,id',
            'process_id' => 'required|exists:processes,id',
            'to_item_id' => 'required|exists:items,id',
            'from_qty' => 'required|numeric',
            'to_qty' => 'required|numeric',
            'wastage_qty' => 'required|numeric',
            'comments' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            ItemConversion::processStockMovement($validated);
            DB::commit();
            $this->saveSqlLog();
            return redirect()->back()->with('success', 'In House Manufacturing recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('fail', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        // dd($model);

        return $this->_destroy($model);
    }
}
