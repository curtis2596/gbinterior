<?php

namespace App\Http\Controllers\Backend;

use App\Models\AllowanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class AllowanceTypeController extends BackendController
{
    public String $routePrefix = "allowance-type";
    protected $modelClass = AllowanceType::class;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modelName = $this->modelClass;

        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = $this->getPaginagteRecords($this->modelClass::where($conditions), Route::currentRouteName());

        $this->setForView(compact("records", "modelName"));

        // dd("test");
        return $this->viewIndex(__FUNCTION__);
    }
    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "allowance_name", "type" => "string", "view_field" => "allowance_name"],
            ["field" => "description", "type" => "string", "view_field" => "description"],
            ["field" => "is_active", "type" => "int", "view_field" => "is_active"],

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

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function _get_comman_validation_rules()
    {
        return [
            "is_active" => ""
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'allowance_name' => 'required|min:3|max:180|unique:allowance_types,allowance_name',
            'description' => 'required|min:3|max:180',
        ]));
        $this->modelClass::create($validatedData);

        return back()->with('success', 'Record created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purpose  $purpose
     * @return \Illuminate\Http\Response
     */
    public function show(AllowanceType $allowance)
    {
        return abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purpose  $purpose
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purpose  $purpose
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'allowance_name' => [
                'required',
                'min:3',
                'max:180',
                Rule::unique((new $this->modelClass)->getTable())->ignore($id),
            ],
            'description' => 'required|min:3|max:180',

        ]));

        $model->fill($validatedData);
        $model->save();

        $this->saveSqlLog();

        return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purpose  $purpose
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
}
