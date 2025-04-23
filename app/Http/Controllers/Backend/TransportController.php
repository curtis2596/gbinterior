<?php

namespace App\Http\Controllers\Backend;

use App\Models\Transport;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class TransportController extends BackendController
{
    public String $routePrefix = "transports";
    protected $modelClass = Transport::class;

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
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "phone_number", "type" => "int", "view_field" => "phone_number"],
            ["field" => "gst_number", "type" => "string", "view_field" => "gst_number"],
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
    // public function store(TransportRequest $request)
    // {
    //     Transport::create($request->validated());
    //     return  redirect()->route('transports.index')->with('success', 'Transport Created');
    // }

    private function _get_comman_validation_rules()
    {
        return [
            "is_active" => ""
        ];
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = $this->_get_comman_validation_rules();
        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|min:3|max:180|unique:' . (new $this->modelClass)->getTable(),
            'phone_number' => 'required|min:10|max:12',
            'gst_number' => 'required|min:10|max:15',
            'address' => 'required|min:10|max:255',
        ]));

        // dd($validatedData);

        $this->modelClass::create($validatedData);

        return back()->with('success', 'Record created successfully');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transport $transport
     * @return \Illuminate\Http\Response
     */
    public function show(Transport $transport)
    {
        return abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transport $transport
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);

        if ($model->is_pre_defined) {
            return back()->with("fail", "Pre-Defined Record can not be edit");
        }

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
     * @param  \App\Models\Transport $transport
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|min:3|max:180',
            'phone_number' => 'required|min:10|max:12',
            'gst_number' => 'required|min:10|max:15',
            'address' => 'required|min:10|max:255',
            Rule::unique((new $this->modelClass)->getTable())->ignore($id),
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
     * @param  \App\Models\Transport $transport
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
