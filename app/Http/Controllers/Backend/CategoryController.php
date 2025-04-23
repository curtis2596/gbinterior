<?php

namespace App\Http\Controllers\Backend;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class CategoryController extends BackendController
{
    public String $routePrefix = "categories";

    protected $modelClass = Category::class;

    public function __construct()
    {
        parent::__construct();
        $this->viewPrefix = "categories";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([]), Route::currentRouteName());
        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "name", "type" => "string", "view_field" => "name"],
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

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|min:3|max:255|unique:' . $this->tableName,
        ]));


        $this->modelClass::create($validatedData);

        return back()->with('success', 'Record created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
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
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|min:3|max:180|unique:' . $this->tableName . ',name,' . $model->id,
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
     * @param  \App\Models\Category  $category
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
