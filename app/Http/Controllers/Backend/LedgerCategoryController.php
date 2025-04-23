<?php

namespace App\Http\Controllers\Backend;

use App\Models\LedgerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class LedgerCategoryController extends BackendController
{
    public String $routePrefix = "ledger-category";
    
    protected $modelClass = LedgerCategory::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions),Route::currentRouteName());

        $this->_set_common_list();

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _set_common_list()
    {
        $typeList = $this->modelClass::TYPE_LIST;

        $ledgerCategoryList = LedgerCategory::getListCache();

        $this->setForView(compact("typeList", "ledgerCategoryList"));
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "type", "type" => "", "view_field" => "type"],            
            ["field" => "ledger_category_id", "type" => "", "view_field" => "ledger_category_id"],            
            ["field" => "name", "type" => "string", "view_field" => "name"],            
            ["field" => "is_pre_defined", "type" => "int", "view_field" => "is_pre_defined"],
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

        $this->_set_list_for_form();

        $is_add_screen = true;

        $this->setForView(compact("model", 'form', 'is_add_screen'));

        return $this->view("form");
    }

    private function _set_list_for_form()
    {
        $this->_set_common_list();
    }

    private function _get_comman_validation_rules()
    {
        return [
            'type' => 'required',
            'is_active' => '',
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        //dd($rules);

        $validatedData = $request->validate(array_merge($rules, [
           'name' => [
                'required',
                'min:2',
                'max:45',
                'unique:' . $this->tableName . ',name',
            ],            
        ]));

        $model = $this->modelClass::create($validatedData);

        $this->saveSqlLog();

        return back()->with('success', 'Record created successfully');
    }

    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);
        
        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_list_for_form();

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => [
                'required',
                'min:2',
                'max:45',
                'unique:' . $this->tableName . ',name,' . $model->id,
            ],
        ]));

        $model->fill($validatedData);
        $model->save();

        $this->saveSqlLog();

        return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }
}
