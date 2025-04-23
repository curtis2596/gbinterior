<?php

namespace App\Http\Controllers\Backend;

use App\Models\Party;
use App\Models\City;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PartyRequest;
use App\Models\State;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;


class PartyController extends BackendController
{
    public String $routePrefix = "party";

    public $modelClass = Party::class;

    public function __construct()
    {
        parent::__construct();
        $this->viewPrefix = "parties";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "category",
            "city"
        ]), Route::currentRouteName());

        $this->_set_common_form_list();
        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "is_active", "type" => "int", "view_field" => "is_active"],
            ["field" => "city_id", "type" => "int", "view_field" => "city_id"],
            ["field" => "category_id", "type" => "int", "view_field" => "category_id"],
            ["field" => "user_id", "type" => "int", "view_field" => "user_id"],
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

        $this->_set_common_form_list();
        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_common_form_list()
    {
        $cities = City::getListCache('id', 'name');
        $categories = Category::getList('id', 'name');

        $users = User::getListCache();

        $this->setForView(compact("cities", "categories", "users"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     // dd($request->all());

    //     $validateData = $request->validated();

    //     if (!isset($validateData['opening_balance'])) {
    //         $validateData['opening_balance'] = 0;
    //     }

    //     if (!isset($validateData['is_active'])) {
    //         $validateData['is_active'] = 1;
    //     }

    //     $new_party = Party::create($validateData);

    //     if ($request->ajax()) {

    //         $parties = Party::getList();

    //         return view('parties.option', compact('parties', 'new_party'));
    //     }

    //     // If the request is an AJAX request, return the created party as JSON

    //     return back()->with('success', 'Party Created.');
    // }
    private function _get_comman_validation_rules()
    {
        return [
            "is_supplier" => "",
            "is_customer" => "",
            "is_job_worker" => "",
            "is_active" => "",
        ];
    }

    public function store(Request $request)
    {
        // Define validation rules
        $rules = $this->_get_comman_validation_rules();
        // Validate request data
        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|string|min:3|max:100|unique:parties,name',
            'opening_balance' => 'nullable|integer',
            'opening_balance_type' => 'nullable|string|min:2|max:50',
            'address' => 'nullable|string|max:200',
            'gstin' => 'nullable|string|max:50',
            'city_id' => 'nullable|integer|exists:cities,id',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'fax' => 'nullable|string|max:50',
            'url' => 'nullable|url|max:50',
            'tin_number' => 'nullable|string|max:50',
            'category_id' => 'nullable|integer|exists:categories,id',
            'note' => 'nullable|string',
            'type' => 'nullable|string|max:3',
            'user_id' => 'nullable|integer|exists:users,id',
            'state_id' => 'nullable|integer',
        ]));

        // dd($validatedData);
        // Set default values if not provided
        $validatedData['state_id'] = $validatedData['state_id'] ?? 0;
        $validatedData['opening_balance'] = $validatedData['opening_balance'] ?? 0;
        $validatedData['is_active'] = $validatedData['is_active'] ?? 1;

        // Create the record
        $newRecord = $this->modelClass::create($validatedData);

        // Handle AJAX requests
        if ($request->ajax()) {
            $records = $this->modelClass::all();
            return view('your_view_name.option', compact('records', 'newRecord'));
        }

        // Return success message
        return back()->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function show(Party $party)
    {
        return abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);
        // dd($model);as

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_common_form_list();
        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|string|min:3|max:100|unique:parties,name,' . $id,
            'opening_balance' => 'nullable|integer',
            'opening_balance_type' => 'nullable|string|min:2|max:50',
            'address' => 'nullable|string|max:200',
            'gstin' => 'nullable|string|max:50',
            'city_id' => 'nullable|integer|exists:cities,id',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'fax' => 'nullable|string|max:50',
            'url' => 'nullable|url|max:50',
            'tin_number' => 'nullable|string|max:50',
            'category_id' => 'nullable|integer|exists:categories,id',
            'note' => 'nullable|string',
            'type' => 'nullable|string|max:3',
            'user_id' => 'nullable|integer|exists:users,id',
            'state_id' => 'nullable|integer',
        ]));

        // dd($validatedData);

        $model->fill($validatedData);
        $model->save();

        $this->saveSqlLog();

        return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        if (!$model) {
            return response()->json(['status' => 0, 'msg' => 'Record not found.']);
        }

        return $this->_destroy($model);
    }

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::with("city.state")->findOrFail($id);

            $response['data'] = $model->toArray();
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }
}
