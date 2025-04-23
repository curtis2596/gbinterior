<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ArrayHelper;
use App\Models\AutoIncreament;
use App\Models\Item;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\Party;
use App\Models\Process;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class JobOrderController extends BackendController
{
    public String $routePrefix = "job-orders";

    public $modelClass = JobOrder::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "party" => function ($q) {
                $q->select("id", "name");
            },
            "JobOrderItem" => function ($q) {
                $q->with([
                    "fromItem" => function ($q) {
                        $q->with("unit");
                    }
                ]);
            }
        ]),Route::currentRouteName());

        foreach ($records as $k => $record) {
            $records[$k]->checkPending();
        }

        // dd($records->toArray());

        $partyList = Party::getListCache();
        $processList = Process::getListCache("id",'name');

        $this->setForView(compact("records", "partyList", 'processList'));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "order_no", "type" => "string",],
            ["field" => "party_id", "type" => "int"],
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
        $model->order_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_JOB_ORDER);
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
            "is_job_worker" => 1,
            "or_id" => []
        ];

        if ($model && $model->party_id) {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->JobOrderItem) {
            foreach ($model->JobOrderItem as $JobOrderItem) {
                $conditions["or_id"][] = $JobOrderItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);

        // $warehouseList = Warehouse::getList("id");

        $processList = Process::getList('id','name');

        $this->setForView(compact('partyList', 'itemList', 'processList'));
    }


    public function store(Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), []);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);
        $validatedData['expected_complete_date'] = \Carbon\Carbon::parse($validatedData['expected_complete_date'])->format('Y-m-d');
        // d($validatedData);

        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->getOnlyWhichHaveKeys([
                "party_id",
                "expected_complete_date",
                "process_id",
                "amount",
                "comments",
                "will_receive_at_type"
            ]);

            $save_data['order_no'] = $order_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_JOB_ORDER);
            // dd($save_data);

            $model = $this->modelClass::create($save_data);
            if (!$model) {
                throw_exception("Fail To Save");
            }

            $this->_afterSave($validatedData, $model);

            DB::commit();

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_JOB_ORDER);

            $this->saveSqlLog();

            return back()->with('success', "$order_no created successfully");
        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _get_comman_validation_rules()
    {
        return [
            'party_id' => ['required'],
            'expected_complete_date' => ['required'],
            'process_id' => ['required'],
            'amount' => ['required'],
            'will_receive_at_type' => ['required'],
            'comments' => '',
            "job_items.*.id" => "",
            "job_items.*.from_item_id" => ["required"],
            "job_items.*.from_qty" => ["required"],
            "job_items.*.to_item_id" => "",
            "job_items.*.to_qty" => "",
            "job_items.*.comments" => "",
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            "job_items.*.from_item_id.required" => "From Item is required",
            "job_items.*.from_qty.required" => "From Qty is required",
        ];
    }

    private function _afterSave($validatedData, JobOrder $model)
    {
        // d($validatedData);
        if (!isset($validatedData['job_items'])  || empty($validatedData['job_items'])) {
            throw new Exception("Items are required");
        }

        $id_list = $model->JobOrderItem()->pluck("id", "id")->toArray();

        foreach ($validatedData['job_items'] as $arr) {
            $job_order_item = $model->JobOrderItem()->where("id", $arr['id'])->first();

            // dd($job_order_item);
            $arr = array_make_all_values_zero_if_null($arr);

            if ($job_order_item) {
                unset($id_list[$arr['id']]);

                $job_order_item->fill($arr);

                if ($job_order_item->isDirty())
                {
                    $job_order_item->save();
                }
            } else {
                $JobOrderItem = new JobOrderItem();
                $JobOrderItem->fill($arr);
                $model->JobOrderItem()->save($JobOrderItem);
            }
        }

        if ($id_list)
        {
            $model->JobOrderItem()->whereIn("id", $id_list)->delete();
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
        $model = $this->modelClass::with([
            "jobOrderItem",
            "process"
        ])->findOrFail($id);
        // dd($model->process->name);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $job_items = $model->jobOrderItem->toArray();

        $processList = Process::getList("id","name");


        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form", "job_items", "processList"));

        return $this->view("form");
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
        // dd($request->all());
        $rules = array_merge($this->_get_comman_validation_rules(), []);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);
        $validatedData['expected_complete_date'] = \Carbon\Carbon::parse($validatedData['expected_complete_date'])->format('Y-m-d');


        try {
            DB::beginTransaction();

            $arry_helper = new ArrayHelper($validatedData);

            $save_data = $arry_helper->getOnlyWhichHaveKeys([
                "party_id",
                "expected_complete_date",
                "process_id",
                "amount",
                "comments",
                "will_receive_at_type"
            ]);

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }

    public function print($id)
    {
        $model = $this->modelClass::with([
            "party" => function ($q) {
                $q->with(["city"]);
            },
            "jobOrderItem" => function ($q) {
                $q->with([
                    "fromItem",
                    "toItem" => function ($q) {
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
