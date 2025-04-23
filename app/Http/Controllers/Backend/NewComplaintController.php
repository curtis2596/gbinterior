<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Models\AutoIncreament;
use App\Models\Item;
use App\Models\NewComplaint;
use App\Models\NewComplaintItem;
use App\Models\Party;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


class NewComplaintController extends BackendController
{
    public String $routePrefix = "new-complaint";

    public $modelClass = NewComplaint::class;

    public function index()
    {
        $builder = $this->_getBuilder();
        $records = $this->getPaginagteRecords(
            $builder
        ,Route::currentRouteName());

        // dd($records->toArray());

        $partyList = Party::getListCache();
        $complaintstatusList = config('constant.complaintstatus');

        $this->setForView(compact("records", "partyList", "complaintstatusList"));

        return $this->viewIndex(__FUNCTION__);
    }
    private function _getBuilder()
    {
        $cache_key = Route::currentRouteName();

        $conditions = $this->getConditions($cache_key, [
            ["field" => "date", "type" => "date",],
            ["field" => "party_id", "type" => "int"],
            ["field" => "complaint_no", "type" => "string"],
            ["field" => "contact_number", "type" => "int"],
            ["field" => "contact_person", "type" => "string"],
            ["field" => "status", "type" => "string"],
        ]);

        $builder = $this->modelClass::where($conditions)->with([
            'newComplaintItem',
            'user'
        ]);


        return $builder;
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->complaint_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_COMPLAINT);
        $model->date = date(DateUtility::DATE_OUT_FORMAT);

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
        $userList = User::getList("id");
        $itemList  = Item::getList("id");
        $complaintstatusList = config('constant.complaintstatus');

        $this->setForView(compact('partyList', 'userList', 'complaintstatusList', 'itemList'));
    }
    public function getCustomerDetails($id)
    {
        $party = Party::find($id);

        if ($party) {
            return response()->json([
                'contact_number' => $party->phone,
                'contact_person' => $party->contact_person
            ]);
        }

        return response()->json([], 404);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'date' => 'required|date',
            'party_id' => 'required|integer',
            'contact_number' => 'required|string|max:180',
            'contact_person' => 'required|string|max:180',
            'remarks' => 'nullable|string',
            'assign_to' => 'required',
            'amount' => 'required_if:is_free,0|nullable|integer|min:1',
            'sale_bill_no' => 'required_if:is_free,1|nullable|integer',
            'is_free' => 'nullable|integer',
            'is_new_party' => 'nullable|integer',

            'complaint_items' => 'nullable|array',
            'complaint_items.item_id.*' => 'required_with:complaint_items.*.qty|integer|exists:items,id',
            'complaint_items.qty.*' => 'required_with:complaint_items.*.item_id|numeric|min:1',
        ], [
            'amount.required_if' => 'The amount field is required',
            'sale_bill_no.required_if' => 'The sale bill number is required',
            'complaint_items.item_id.*.required_with' => 'The item field is required when quantity is entered',
            'complaint_items.qty.*.required_with' => 'The quantity field is required when item is selected',
        ]);

        // dd($validated);
        try {
            $complaint_items = $request->complaint_items ?? [];
            unset($validated['complaint_items']);
            
            $validated['status'] = $validated['status'] ?? 'pending';
            $validated['complaint_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_COMPLAINT);
            $complaint = NewComplaint::create($validated);
            // dd($complaint);
            
            if (!empty($complaint_items)) {
                foreach ($complaint_items['item_id'] as $index => $item) {
                    NewComplaintItem::create([
                        'item_id' => $item ?? null,
                        'qty' => $complaint_items['qty'][$index] ?? null,
                        'complaint_id' => $complaint->id,
                    ]);
                }
                // $new_items = array_map(fn($item) => [
                //     'complaint_id' => $complaint->id,
                //     'item_id' => $item['item_id'],
                //     'qty' => $item['qty'],
                // ], $complaint_items);

                // NewComplaintItem::insert($new_items);
            }

            DB::commit();
            AutoIncreament::increaseCounter(AutoIncreament::TYPE_COMPLAINT);
            return redirect()->back()->with('success', 'Complaint recorded successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('fail', $e->getMessage());
        }
    }
    public function edit($id)
    {
        $model = $this->modelClass::with([
            "newComplaintItem",
        ])->findOrFail($id);
        // dd($model);
        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $complaint_items = $model->newComplaintItem->toArray();

        $this->_set_list_for_form($model);

        $this->setForView(compact('model', 'form', 'complaint_items'));

        return $this->view('form');
    }
    public function update(Request $request, $id)
    {
        $complaint = NewComplaint::findOrFail($id);
        $validated = $request->validate([
            'date' => 'required|date',
            'party_id' => 'required|integer',
            'contact_number' => 'required|string|max:180',
            'contact_person' => 'required|string|max:180',
            'remarks' => 'nullable|string',
            'assign_to' => 'required',
            'amount' => 'required_if:is_free,0|nullable|integer|min:1',
            'sale_bill_no' => 'required_if:is_free,1|nullable|integer',
            'is_free' => 'nullable|integer',
            'is_new_party' => 'nullable|integer',

            'complaint_items' => 'nullable|array',
            'complaint_items.item_id.*' => 'required_with:complaint_items.*.qty|integer|exists:items,id',
            'complaint_items.qty.*' => 'required_with:complaint_items.*.item_id|numeric|min:1',
        ], [
            'amount.required_if' => 'The amount field is required',
            'sale_bill_no.required_if' => 'The sale bill number is required',
            'complaint_items.item_id.*.required_with' => 'The item field is required when quantity is entered',
            'complaint_items.qty.*.required_with' => 'The quantity field is required when item is selected',
        ]);
        // dd($validated);
        try {
            $validate_data['complaint_items'] ?? [];

            unset($validated['complaint_items']);

            $complaint->update($validated);

            $existingItems = NewComplaintItem::where('complaint_id', $id)->get()->keyBy('id');

            $complaintitems = $request->complaint_items ?? [];


            if (!empty($complaintitems['item_id']) && is_array($complaintitems['item_id'])) {
                foreach ($complaintitems['item_id'] as $index => $itemId) {
                    if (empty($itemId)) {
                        continue;
                    }

                    $qty = $complaintitems['qty'][$index] ?? 0;

                    if (!empty($complaintitems['id'][$index])) {
                        $item = NewComplaintItem::find($complaintitems['id'][$index]);
                        if ($item) {
                            $item->update([
                                'item_id' => $itemId,
                                'qty' => $qty
                            ]);
                            unset($existingItems[$item->id]);
                        }
                    } else {
                        NewComplaintItem::create([
                            'complaint_id' => $id,
                            'item_id' => $itemId,
                            'qty' => $qty
                        ]);
                    }
                }
            }

            if ($existingItems->isNotEmpty()) {
                NewComplaintItem::destroy($existingItems->keys());
            }
            return redirect()->route($this->routePrefix . ".index")->with('success', 'Complaint updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('fail', $e->getMessage());
        }
    }
    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }
}
