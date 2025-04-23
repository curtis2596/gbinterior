<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Helpers\Util;
use App\Models\Item;
use App\Models\Unit;
use App\Models\ItemBrand;
use App\Models\ItemGroup;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class ItemController extends BackendController
{
    public String $routePrefix = "items";
    public $modelClass = Item::class;

    protected function beforeViewRender()
    {
        parent::beforeViewRender();

        $item_category_list = ItemCategory::getTreeList();

        $this->setForView(compact("item_category_list"));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $records = $this->getPaginagteRecords($this->_getBuilder(), Route::currentRouteName());

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuilder()
    {
        $cache_key = Route::currentRouteName();

        $conditions = $this->getConditions($cache_key . ".1", [
            ["field" => "name", "type" => "string"],
            ["field" => "sku", "type" => "string"],
            ["field" => "is_active", "type" => "int"],
            ["field" => "is_finished_item", "type" => "int"],
        ]);

        $builder = $this->modelClass::where($conditions)->with([
            "itemGroup",
            "brand",
            "unit"
        ]);

        $category_conditions = $this->getConditions($cache_key . ".2", [
            ["field" => "item_category_id", "type" => "int"],
        ], true);

        if ($category_conditions) {
            $item_category_id_list = [];
            $item_category = ItemCategory::with("children")->find($category_conditions['item_category_id']);

            if ($item_category) {
                $item_category_id_list[] = $item_category->id;

                $item_category_child_id_list = $item_category->children->pluck("id")->toArray();

                $item_category_id_list = array_merge($item_category_id_list, $item_category_child_id_list);
            }

            if ($item_category_id_list) {
                $builder->whereIn("item_category_id", $item_category_id_list);
            }
        }

        return $builder;
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

        $this->_set_form_list(null);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_form_list($model)
    {
        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->unit_id) {
            $conditions["or_id"] = $model->unit_id;
        }

        $unitList = Unit::getList("id", "name_code", $conditions);
        // dd($conditions);

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->item_group_id) {
            $conditions["or_id"] = $model->item_group_id;
        }

        $itemGroupList = ItemGroup::getList("id", "name", $conditions);

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->brand_id) {
            $conditions["or_id"] = $model->brand_id;
        }

        $brandList = ItemBrand::getList("id", "name", $conditions);

        $item_sku_pattern = Setting::getValueOrFail("item_sku_pattern");

        $this->setForView(compact("unitList", 'itemGroupList', 'brandList', 'item_sku_pattern'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'item_category_id' => 'required',
            'item_group_id' => 'required',
            'brand_id' => 'required',
            'unit_id' => 'required',
            'sku' => 'required',
            "hsn_code" => "required",
            "tax_rate" => "required",
            "specification" => "",
            "purchase_rate" => "",
            "sale_rate" => "",
            "is_active" => "",
            "is_finished_item" => "",
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            'name.unique' => 'Item Name is unique with in category and specification',
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();
        $messages = $this->_get_comman_validation_messages();

        $rules = array_merge($rules, [
            'name' => [
                'required',
                "min:2",
                "max:180",
                Rule::unique($this->tableName)->where(function ($query) use ($request) {
                    $builder = $query
                        ->where('item_category_id', $request->input('item_category_id'))
                        ->where('name', $request->input('name'));

                    $specification = $request->input('specification');

                    if ($specification) {
                        $builder->where('specification', $request->input('specification'));
                    }

                    return $builder;
                })
            ],
            'sku' => 'required|min:2|max:255|unique:' . $this->tableName,
        ]);

        $validatedData = $request->validate($rules, $messages);

        try {
            $validatedData = array_make_all_values_zero_if_null($validatedData);

            // dd($validatedData);

            $this->modelClass::create($validatedData);

            return back()->with('success', 'Record created successfully');
        } catch (Exception $ex) {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_form_list($model);

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }


    public function update(Request $request, $id)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => [
                "required",
                "min:2",
                "max:180",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {

                    $builder = $query->where('id',  '<>', $id)
                        ->where('item_category_id', $request->input('item_category_id'))
                        ->where('name', $request->input('name'));

                    $specification = $request->input('specification');

                    if ($specification) {
                        $builder->where('specification', $request->input('specification'));
                    }

                    return $builder;
                })
            ],
            'sku' => [
                "required",
                "min:2",
                "max:255",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {
                    return $query
                        ->where('id',  '<>', $id)
                        ->where('sku', $request->input('sku'));
                })
            ]
        ]));

        try {
            $model = $this->modelClass::findOrFail($id);

            $validatedData = array_make_all_values_zero_if_null($validatedData);

            $model->fill($validatedData);
            $model->save();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        } catch (Exception $ex) {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->_destroy($id);
    }

    public function set_warehouse_opening_qty($id, Request $request)
    {
        $item = $this->modelClass::with("unit")->findOrFail($id);

        if ($request->isMethod("post")) {
            // dd($request->all());

            $validateData = $request->validate([
                'warehouse_stocks.*.warehouse_id' => 'required',
                'warehouse_stocks.*.opening_qty' => 'required|numeric|min:0'
            ], [
                "warehouse_stocks.*.warehouse_id.required" => "Warehouse is required",
                "warehouse_stocks.*.opening_qty.required" => "Opening Qty is required",
                "warehouse_stocks.*.opening_qty.numeric" => "Opening Qty should be numeric",
                "warehouse_stocks.*.opening_qty.min" => "Opening Qty should be more than or equal to 0",
            ]);

            try {
                $counters = [
                    "Warehouse Stock" => [
                        "Insert" => 0,
                        "Update" => 0,
                    ],
                ];

                $fail_msg_list = [];

                foreach ($validateData['warehouse_stocks'] as $warehouse_stock) {
                    $warehouse_stock['item_id'] = $item->id;

                    $warehouseStockModel = new WarehouseStock();
                    $id = $warehouseStockModel->getUniqueId($warehouse_stock);

                    $is_insert = null;
                    $is_update = null;

                    if ($id) {
                        $warehouseStockModel = WarehouseStock::with("warehouse")->find($id)->first();
                        $warehouseStockModel->fill($warehouse_stock);
                        if ($warehouseStockModel->getAvailabilitQty() < 0) {
                            $qty = abs($warehouseStockModel->qty);
                            $name = $warehouseStockModel->warehouse->getDisplayName();
                            $fail_msg_list[] = "There are some inventory transactions of Warehouse $name, So can not go below $qty";
                        } else {
                            if ($warehouseStockModel->isDirty()) {
                                $warehouseStockModel->save();

                                $counters["Warehouse Stock"]['Update']++;
                            }
                        }
                    } else {
                        $warehouseStockModel->fill($warehouse_stock);
                        $warehouseStockModel->save();
                        $counters["Warehouse Stock"]['Insert']++;
                    }

                    if ($is_insert) {
                    }

                    if ($is_update) {
                    }
                }

                if ($fail_msg_list) {
                    $msg = implode(", ", $fail_msg_list);

                    Session::flash("fail", $msg);

                    return back();
                }

                $msg_list = [];
                foreach ($counters as $title => $arr) {
                    foreach ($arr as $db_op => $counter) {
                        if ($counter > 0) {
                            $msg_list[] = $title . " " . $db_op . " : " . $counter;
                        }
                    }
                }

                if ($msg_list) {
                    $msg = implode(", ", $msg_list);

                    Session::flash("success", $msg);
                }
            } catch (Exception $ex) {
                Session::flash("fail", $ex->getMessage());
            }

            return back();
        }

        $warehouses = Warehouse::with([
            "warehouseStock" => function ($q) use ($id) {
                $q->where("item_id", $id);
            }
        ])->get();

        // dd($warehouses);

        $this->setForView(compact("item", "warehouses"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::with('unit')->findOrFail($id);

            $response['data'] = $model->toArray();
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function csv()
    {
        // $builder = $this->_getBuilder();

        // $count = $builder->count();

        // $this->beforeCSVExport($count);

        // $records = $builder->get()->toArray();

        $builder = $this->_getBuilder();
        $count = $builder->count();

        $this->beforeCSVExport($builder); 

        $records = $builder->get()->toArray();

        // d($records); exit;

        $csv_records = [];

        $item_category_list = ItemCategory::getTreeList();

        $yes_no_list = config('constant.yes_no');
        $user_list = User::getListCache();

        foreach ($records as $record) {
            $csv_records[] = [
                'ID' => $record['id'],
                'Category' => $item_category_list[$record['item_category_id']] ?? "",
                'Group' => $record['item_group']['name'] ?? "",
                'Brand' => $record['brand']['name'] ?? "",
                'Name' => $record['name'],
                'Specification' => $record['specification'] ?? "",
                'Sku' => $record['sku'],
                'Unit' => $record['unit']['name'] ?? "",
                'HSN' => $record['hsn_code'] ?? "",
                'Purchase Rate' => $record['purchase_rate'] ?? "",
                'Sale Rate' => $record['sale_rate'] ?? "",
                'GST Percentage' => $record['tax_rate'] ?? "",
                'Finished' => $yes_no_list[$record['is_finished_item']] ?? "",
                'Active' => $yes_no_list[$record['is_active']] ?? "",
                'Created' => if_date_time($record['created_at']),
                'Created By' => $user_list[$record['created_by']] ?? "",
                'Updated' => if_date_time($record['updated_at']),
                'Updated By' => $user_list[$record['updated_by']] ?? "",
            ];
        }

        $path = config('constant.path.temp');
        FileUtility::createFolder($path);
        $file = $path . $this->tableName .  "_" . date(DateUtility::DATETIME_OUT_FORMAT_FILE) . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        download_start($file, "application/octet-stream");
    }
}
