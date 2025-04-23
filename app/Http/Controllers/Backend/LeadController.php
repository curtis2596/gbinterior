<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Models\Item;
use App\Models\Lead;
use App\Models\LeadItem;
use App\Models\Party;
use App\Models\Source;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadController extends BackendController
{
    public String $routePrefix = "leads";
    public $modelClass = Lead::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "leadItem",
            "party",
            "user"
        ]), Route::currentRouteName());
        // dd($records);

        $partyList = Party::getListCache();
        $sourceList = Source::pluck('resources', 'id')->toArray();


        $this->setForView(compact("records", "partyList", "sourceList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field' => 'is_new', 'type' => 'int'],
            ['field' => 'lead_source_id', 'type' => 'int'],
            ['field' => 'party_id', 'type' => ''],
            ['field' => 'level', 'type' => ''],
            ['field' => 'status', 'type' => ''],
            ['field' => 'follow_up_user_id', 'type' => ''],
            ['field' => 'follow_up_date', 'type' => 'date'],
            ['field' => 'follow_up_type', 'type' => ''],
            ['field' => 'comments', 'type' => 'string'],
            ['field' => 'customer_name', 'type' => 'string'],
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
        $model->date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $sourceList = Source::pluck('resources', 'id')->toArray();

        $this->setForView(compact("model", 'form', 'sourceList'));

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
            "or_id" => []
        ];

        if ($model && $model->saleOrderItem) {
            foreach ($model->saleOrderItem as $saleOrderItem) {
                $conditions["or_id"][] = $saleOrderItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);
        $userList = User::getList("id");


        $this->setForView(compact('partyList', 'itemList', 'userList'));
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
        // Validate the request data
        $validate_data = $request->validate([
            'date' => 'required|date',
            'level' => 'required|string',
            'party_id' => 'nullable|integer',
            'is_new' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'status' => 'nullable|string',
            'lead_source_id' => 'required|string',
            'not_in_interested_reason' => 'nullable|string',
            'follow_up_user_id' => 'required|integer',
            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable|string',
            'mature_action_type' => 'nullable|string',
            'comments' => 'nullable|string',
            'is_include_items' => 'nullable|integer',

            'lead_items' => 'nullable|array',
            'lead_items.item_id.*' => 'nullable|integer',
            'lead_items.qty.*' => 'nullable|numeric|min:1',
        ]);
        try {
            $lead_items = $validate_data['lead_items'] ?? [];
            // dd($lead_items);
            // dd($lead_items);

            unset($validate_data['lead_items']);

            $leads = Lead::create($validate_data);
            // dd($lead_items);
            if (!empty($lead_items)) {
                // $combined_items = [];
                foreach ($lead_items['item_id'] as $index => $item) {
                    LeadItem::create([
                        'item_id' => $item ?? null,
                        'qty' => $lead_items['qty'][$index] ?? null,
                        'lead_id' => $leads->id,
                    ]);
                }
                // dd($combined_items);

                // LeadItem::insert($combined_items);
            }
            return back()->with('success', 'Lead created successfully');
        } catch (\Exception $ex) {
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
            "party",
            "leadItem"
        ])->findOrFail($id);
        // ś
        // dd($model->party->name);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $lead_items = $model->leadItem->toArray();
        // dd($lead_items);


        $this->_set_list_for_form($model);

        $sourceList = Source::pluck('resources', 'id')->toArray();
        $partyList = Party::getList('id');


        $this->setForView(compact("model", "form", "lead_items", "sourceList", "partyList"));


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
        $lead = Lead::findOrFail($id);
        // dd($lead);
        $validate_data = $request->validate([
            'date' => 'required|date',
            'level' => 'required|string',
            'party_id' => 'nullable|integer',
            'is_new' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'status' => 'required|string',
            'lead_source_id' => 'required|string',
            'not_in_interested_reason' => 'nullable|string',
            'follow_up_user_id' => 'required|integer',
            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable|string',
            'mature_action_type' => 'nullable|string',
            'comments' => 'nullable|string',
            'is_include_items' => 'nullable|integer',

            'lead_items' => 'nullable|array',
            'lead_items.*.item_id' => 'nullable|integer',
            'lead_items.*.qty' => 'nullable|numeric|min:1',
        ]);
        try {
            $validate_data['lead_items'] ?? [];
            // dd($lead_items);

            unset($validate_data['lead_items']);

            // Create the lead
            $lead->update($validate_data);


            $existingItems = LeadItem::where('lead_id', $id)->get()->keyBy('id');

            $leadItems = $request->lead_items ?? [];

            if (!empty($leadItems['item_id']) && is_array($leadItems['item_id'])) {
                foreach ($leadItems['item_id'] as $index => $itemId) {
                    if (empty($itemId)) {
                        continue;
                    }

                    $qty = $leadItems['qty'][$index] ?? 0;

                    if (!empty($leadItems['id'][$index])) {
                        $item = LeadItem::find($leadItems['id'][$index]);
                        if ($item) {
                            $item->update([
                                'item_id' => $itemId,
                                'qty' => $qty
                            ]);
                            unset($existingItems[$item->id]);
                        }
                    } else {
                        LeadItem::create([
                            'lead_id' => $id,
                            'item_id' => $itemId,
                            'qty' => $qty
                        ]);
                    }
                }
            }

            if ($existingItems->isNotEmpty()) {
                LeadItem::destroy($existingItems->keys());
            }
            return redirect()->route($this->routePrefix . ".index")->with('success', 'Lead updated successfully');
        } catch (\Exception $ex) {
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
        try {
            $lead = Lead::findOrFail($id);

            $leadItems = LeadItem::where('lead_id', $id);

            if ($leadItems->exists()) {
                $leadItems->delete();
            }

            $lead->delete();

            return back()->with('success', 'Lead deleted successfully');
        } catch (Exception $ex) {
            return back()->with('fail', 'Error: ' . $ex->getMessage());
        }
    }

    protected function beforeViewRender()
    {
        parent::beforeViewRender();

        $levelList = config('constant.level');
        $statusList = config('constant.status');
        $followtypeList = config('constant.followuptype');
        $maturefieldList = config('constant.maturefield');
        $quotationstatusList = config('constant.newquotationstatus');

        $this->setForView(compact(
            'levelList',
            'statusList',
            'followtypeList',
            'maturefieldList',
            'quotationstatusList'
        ));
    }
}
