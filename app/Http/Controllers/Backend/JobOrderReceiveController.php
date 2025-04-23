<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Models\Item;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\JobOrderReceive;
use App\Models\JobOrderReceiveItem;
use App\Models\LedgerAccount;
use App\Models\LedgerTransaction;
use App\Models\Party;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class JobOrderReceiveController extends BackendController
{
    public String $routePrefix = "job-orders-receive";

    public $modelClass = JobOrderReceive::class;


    public function index()
    {
        $builder = $this->_getBuilder();
        $records = $this->getPaginagteRecords(
            $builder,Route::currentRouteName()
        );

        // dd($records->toArray());

        $partyList = Party::getListCache();

        $this->setForView(compact("records", "partyList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuilder()
    {
        $cache_key = Route::currentRouteName();

        // Fetch conditions, but exclude `order_no` from the default condition list
        $conditions = $this->getConditions($cache_key, [
            ["field" => "party_id", "type" => ""],
            ["field" => "receive_date", "type" => "date"],
            ["field" => "challan_no", "type" => "string"],
        ]);

        $orderNo = request('order_no');

        $builder = $this->modelClass::where($conditions)
            ->when(!empty($orderNo), function ($query) use ($orderNo) {
                $query->whereHas('jobOrders', function ($q) use ($orderNo) {
                    $q->where('job_orders.order_no', 'LIKE', "%{$orderNo}%");
                });
            })
            ->with([
                "jobOrders",
                "party:id,name",
                "jobOrderReceiveItem.toItem",
                "jobOrderReceiveItem.jobOrderItem.fromItem.unit",
                "jobOrderReceiveItem.toItem.unit",
                "jobOrderReceiveItem.warehouse"
            ]);

        return $builder;
    }



    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "party_id", "type" => ""],
            ["field" => "receive_date", "type" => "date"],
            ["field" => "challan_no", "type" => "string"],
            ["field" => "job_order_id", "type" => ""],
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

        if ($model && $model->jobOrderReceiveItem) {
            foreach ($model->jobOrderReceiveItem as $jobOrderReceiveItem) {
                $conditions["or_id"][] = $jobOrderReceiveItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);
        $warehouseList = Warehouse::getList('id', 'name');

        $this->setForView(compact('partyList', 'itemList', 'warehouseList'));
    }
    public function getJobOrders($party_id, $id = "")
    {
        if ($party_id) {
            $old_job_order_id_list = JobOrderReceive::whereNotNull('job_order_id')->pluck("job_order_id")->toArray();
        } else {
            $old_job_order_id_list = JobOrderReceive::pluck("job_order_id")->toArray();
        }

        $jobOrders = JobOrder::where('party_id', $party_id)->get();

        return response()->json($jobOrders);
    }

    public function getJobOrderItems($job_order_id)
    {
        $job_order = JobOrder::findOrFail($job_order_id);
        // dd($job_order);

        $items = JobOrderItem::where('job_order_id', $job_order_id)
            ->with('toItem', 'fromItem', 'joborder:id,order_no', 'toItem.unit', 'fromItem.unit')
            ->get();

        // dd($items);
        foreach ($items as $k => $item) {
            $item->toItem->display_name = $item->toItem->getDisplayName();
            $item->fromItem->display_name = $item->fromItem->getDisplayName();
            $items[$k] = $item;
        }


        $itemList = Item::getList();
        $warehouseList = Warehouse::getList();
        $partyList = Party::getList('id', 'display_name', ['is_customer' => 1]);
        // dd($itemList);

        return response()->json([
            'status' => true,
            'job_order' => $job_order,
            'data' => $items,
            'itemList' => $itemList,
            'warehouseList' => $warehouseList,
            "partyList" => $partyList
        ]);
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
        $validate_data = $request->validate([
            'party_id' => 'required|integer',
            'job_order_id' => 'required|integer',
            'receive_date' => 'required|date',
            'amount' => 'required|numeric',
            'challan_no' => 'required|string|max:255',
            'narration' => 'nullable|string',
            'comments' => 'nullable|string',
            'job_items' => 'required|array',
            'job_items.*.job_order_item_id' => 'required|integer',
            'job_items.*.to_item_id' => 'required|integer',
            'job_items.*.to_qty' => 'required|integer',
            'job_items.*.receive_warehouse_id' => 'nullable|integer',
            'job_items.*.receive_party_id ' => 'nullable|integer',
            'job_items.*.comments' => 'nullable|string',
        ]);
        $validate_data['receive_date'] = \Carbon\Carbon::parse($validate_data['receive_date'])->format('Y-m-d');

        // dd($validate_data);

        try {
            DB::beginTransaction();

            $job_items = $validate_data['job_items'];

            unset($validate_data['job_items']);

            $jobReceive = JobOrderReceive::create($validate_data);
            // dd($jobReceive);
            foreach ($job_items as $item) {
                // dd($item);
                $item['job_order_receive_id'] = $jobReceive->id;


                $item['job_order_item_id'] = $item['job_order_item_id'];

                unset($item['job_item']);
                // dd($item);
                JobOrderReceiveItem::create($item);
            }

            $this->_afterSave($jobReceive);

            DB::commit();

            return back()->with('success', 'Job order received created successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            return back()->withInput()->with('fail', $ex->getMessage());
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
            "jobOrders:id,order_no",
            "joborder.fromItem.unit",
            "joborder.toItem.unit",
            "jobOrderReceiveItem.jobOrderItem",

        ])->findOrFail($id);
        // dd($model);
        // dd($model->jobOrders->order_no);
        // dd($jobitem);
        // dd($model->jobOrders);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $job_order_receive_items = $model->jobOrderReceiveItem;
        // dd($job_order_receive_items);
        $itemList = Item::pluck('name', 'id')->toArray();
        // dd($joborder);
        $warehouseList = Warehouse::getList();

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form", "job_order_receive_items", "itemList", 'warehouseList'));

        // $view_name = $model->with_po ? "form_with_po" : "form";

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
        $jobReceive = JobOrderReceive::findOrFail($id);
        $validate_data = $request->validate([
            'party_id' => 'required|integer',
            'job_order_id' => 'required|integer',
            'receive_date' => 'required|date',
            'amount' => 'required|numeric',
            'challan_no' => 'required|string|max:255',
            'narration' => 'nullable|string',
            'comments' => 'nullable|string',
            'job_items' => 'required|array',
            'job_items.*.job_order_item_id' => 'required|integer',
            'job_items.*.to_item_id' => 'required|integer',
            'job_items.*.to_qty' => 'required|integer',
            'job_items.*.receive_warehouse_id' => 'nullable|integer',
            'job_items.*.receive_party_id ' => 'nullable|integer',
            'job_items.*.comments' => 'nullable|string',
        ]);
        $validate_data['receive_date'] = \Carbon\Carbon::parse($validate_data['receive_date'])->format('Y-m-d');

        // dd($validate_data);

        try {
            DB::beginTransaction();

            $job_items = array_values($validate_data['job_items']);

            unset($validate_data['job_items']);

            $jobReceive->update($validate_data);
            // dd($jobReceive);
            foreach ($job_items as $item) {
                $jobReceiveItem = JobOrderReceiveItem::where('job_order_receive_id', $jobReceive->id)
                    ->where('job_order_item_id', $item['job_order_item_id'])
                    ->first();
                // dd($jobReceiveItem);


                if ($jobReceiveItem) {
                    $old_qty = $jobReceiveItem->to_qty;
                    $jobReceiveItem->fill($item);
                    if (!empty($jobReceiveItem->receive_warehouse_id) && $jobReceiveItem->isDirty()) {                        
                        WarehouseStock::updateQty($jobReceiveItem->receive_warehouse_id, $jobReceiveItem->to_item_id, $old_qty * -1);
                    }
                    

                    $jobReceiveItem->save();

                } else {
                    $item['job_order_receive_id'] = $jobReceive->id;
                    JobOrderReceiveItem::create($item);
                }
            }

            $this->_afterSave($jobReceive);

            DB::commit();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _afterSave(JobOrderReceive $model)
    {
        // dd($model->toArray()); exit;

        $model->ledgerTransaction()->delete();

        if ($model->amount > 0) {
            $manufacture_account = LedgerAccount::getByCode(LedgerAccount::CODE_manufacturing);

            $party_account = $model->party->ledgerAccount()->first();

            if (!$party_account) {
                abort(\ACTION_NOT_PROCEED, "Party's Ledger Account Not Found");
            }

            $save_arr = [
                "main_account_id" => $manufacture_account->id,
                "other_account_id" => $party_account->id,
                "voucher_type" => laravel_constant("voucher_manufacture"),
                "voucher_date" => \Carbon\Carbon::parse($model->receive_date)->format('Y-m-d'),
                "voucher_no" => $model->jobOrders->order_no,
                "amount" => $model->amount,
                "narration" => $model->narration,
                "manufacture_id" => $model->id
            ];

            // dd($save_arr);

            LedgerTransaction::createDoubleEntry($save_arr);
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

    public function csv()
    {

        $builder = $this->_getBuilder();

        $records = $builder->get();
        // dd($jobReceiveItem);

        $user_list = User::getListCache();

        $csv_records = [];

        foreach ($records as $record) {
            foreach ($record->jobOrderReceiveItem as $jobReceiveItem) {
                // dd($record->narration);
                $csv_records[] = [
                    'Job Order ID' => $record->id,
                    'Job Order No' => $record->jobOrders->order_no ?? '',
                    'Challan No' => $record->jobOrders->challan_no ?? '',
                    'Amount' => $record->jobOrders->amount ?? '',
                    'Party' => $record->party->name ?? '',
                    'Challan No' => $record->challan_no ?? '',
                    'Narration' => $record->narration ?? '',
                    'Comment' => $record->comments ?? '',
                    'Receive Date' => if_date($record->receive_date),
                    'Sent Item' => $jobReceiveItem->jobOrderItem->fromItem->display_name ?? '',
                    'Sent Quantity' => $jobReceiveItem->jobOrderItem->from_qty ?? '',
                    'Unit' => $jobReceiveItem->jobOrderItem->fromItem->unit->display_name ?? '',
                    'Received Item' => $jobReceiveItem->toItem->display_name ?? '',
                    'Received Quantity' => $jobReceiveItem->to_qty ?? '',
                    'Received Unit' => $jobReceiveItem->toItem->unit->display_name ?? '',
                    'Warehouse' => $jobReceiveItem->warehouse->display_name ?? '',
                    'Comments' => $jobReceiveItem->comments ?? '',


                    'Created At' => $record->jobOrders->created_at ?? '',
                    'Updated At' => $record->jobOrders->updated_at ?? '',

                    'Created By' => $user_list[$record->created_by] ?? '',
                    'Updated By' => $user_list[$record->updated_by] ?? '',
                ];
            }
        }

        $path = config('constant.path.temp');
        FileUtility::createFolder($path);
        $file = $path . $this->tableName .  "_" . date(DateUtility::DATETIME_OUT_FORMAT_FILE) . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        download_start($file, "application/octet-stream");
    }
}
