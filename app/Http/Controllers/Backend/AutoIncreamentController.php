<?php

namespace App\Http\Controllers\Backend;

use App\Models\AutoIncreament;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class AutoIncreamentController extends BackendController
{
    public String $routePrefix = "auto-increaments";
    public $modelClass = AutoIncreament::class;

    public function __construct()
    {
        parent::__construct();

        Validator::extend('pattern_check_counter_word', function ($attribute, $value, $parameters, $validator) {
            if (strpos($value, "counter") == false)
            {
                return false;
            }

            return true;
        });
    }

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions),Route::currentRouteName());

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "type", "type" => "", "view_field" => "type"],
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

        $this->_set_list();

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_list()
    {

    }

    private function _get_comman_validation_rules()
    {
        return [
            'pattern' => 'required|pattern_check_counter_word',
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            'pattern.pattern_check_counter_word' => 'Patten should contain word counter (in lower Case)',
        ];
    }

    public function store(Request $request)
    {
        $rules = array_merge($this->_get_comman_validation_rules(), [
            'type' => [
                'required',
                'unique:' . $this->tableName . ',type'
            ],
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        try
        {   
            $validatedData['counter'] = 0; 

            $this->modelClass::create($validatedData);

            return back()->with('success', 'Record created successfully');
        }
        catch(Exception $ex)
        {
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

        $this->_set_list();

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {

        $rules = array_merge($this->_get_comman_validation_rules(), [
            'type' => [
                'required',
                'unique:' . $this->tableName . ',type,' . $id
            ],                
        ]);

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        try
        {
            $model = $this->modelClass::findOrFail($id);

            $model->fill($validatedData);
            $model->save();

            $this->saveSqlLog();

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        }
        catch(Exception $ex)
        {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        abort(\ACTION_NOT_PROCEED, "You can not delete Auto Increament");
    }
}
