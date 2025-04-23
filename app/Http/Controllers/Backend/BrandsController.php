<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\Brand;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class BrandsController extends BackendController
{
    public String $routePrefix = "brands";
    public $modelClass = Brand::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([]),Route::currentRouteName());
        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "name", "type" => "string"],
            ["field" => "short_name", "type" => "string"],
            ["field" => "is_active", "type" => ""],
        ]);

        return $conditions;
    }

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
        $categoryList = Brand::getList('id', 'name', []);
        $this->setForView(compact("categoryList"));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'is_active' => 'required',
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        $rules = [
            'name' => [
                'required',
                "min:2",
                "max:45",
                Rule::unique($this->tableName)->where(function ($query) use ($request) {
                    return $query
                        ->where('name', $request->input('name'));
                })
            ],
            'short_name' => [
                'required',
                "min:1",
                "max:20",
                Rule::unique($this->tableName)->where(function ($query) use ($request) {
                    return $query
                        ->where('short_name', $request->input('short_name'));
                })
            ]
        ];

        $data = $request->validate($rules, $messages);

        $validatedData = array_merge($data, $validatedData);


        try {
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

        $this->_set_form_list();

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        $rules = array_merge($rules, [
            'name' => [
                'required',
                "min:2",
                "max:45",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {
                    return $query
                        ->where("id", "<>", $id)
                        ->where('name', $request->input('name'));
                })
            ],
            'short_name' => [
                'required',
                "min:1",
                "max:20",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {
                    return $query
                        ->where("id", "<>", $id)
                        ->where('short_name', $request->input('short_name'));
                })
            ]
        ]);

        $data = $request->validate($rules, $messages);

        $validatedData = array_merge($data, $validatedData);

        try {
            $model = $this->modelClass::findOrFail($id);

            $model->fill($validatedData);
            $model->save();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        } catch (Exception $ex) {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }
}
