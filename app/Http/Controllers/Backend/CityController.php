<?php

namespace App\Http\Controllers\Backend;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class CityController extends BackendController
{
    public String $routePrefix = "cities";

    protected $modelClass = City::class;

    public function index()
    {
        $cache_key_prefix = Route::currentRouteName();

        $builder = $this->_getBuildeForIndex($cache_key_prefix);

        $records = $this->getPaginagteRecords($builder, $cache_key_prefix);
        // dd($records->toArray());

        $this->_set_data_for_add_and_edit(null);

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuildeForIndex($cache_key_prefix, $apply_sort = true)
    {
        $cache_key = $cache_key_prefix . "-index";

        $conditions = $this->getConditions($cache_key, [
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "state_id", "type" => "", "view_field" => "state_id"],
        ]);

        $builder = $this->modelClass::where($conditions);
        $builder->with("state");

        if ($apply_sort) {
            $cache_key_for_sort = $cache_key_prefix . "-index-extra-params";

            $clear_cache = request('is_sort_clear', false);

            $sort_params = $this->getRequestData($cache_key_for_sort, [
                ["key" => "sort_by", "default" => "id"],
                ["key" => "sort_dir", "default" => "DESC"],
            ], $clear_cache);

            $builder->orderBy($sort_params['sort_by'], $sort_params['sort_dir']);
        }

        return $builder;
    }

    public function create()
    {
        $this->beforeCreate();

        $model = new $this->modelClass();

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $state_list = State::getList('id', 'name');

        $this->_set_data_for_add_and_edit($model);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_data_for_add_and_edit($model)
    {
        $state_list = State::getList('id', 'name');

        $this->setForView(compact('state_list'));
    }

    private function _common_validation_rules()
    {
        return [
            'state_id' => "required",
        ];
    }

    private function _common_validation_messages()
    {
        return [
            "name.required" => "City is required",
            "name.unique" => "City already exist",
        ];
    }

    public function store(Request $request)
    {
        $this->beforeCreate();

        $rules = $this->_common_validation_rules();

        $messages = $this->_common_validation_messages();

        $rules = array_merge($rules, [
            "name" => [
                "required",
                "min:2",
                Rule::unique($this->tableName, "name")->where("state_id", $request->state_id)
            ]
        ]);

        $messages = array_merge($messages, []);

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $model = $this->modelClass::create($validatedData);

            $this->updateUserInfoAfterSave($model);

            DB::commit();

            $this->saveSqlLog();

            return $this->afterSave($model);
        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $this->beforeEdit($model);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_data_for_add_and_edit($model);

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $this->beforeEdit($model);

        $rules = $this->_common_validation_rules();

        $messages = $this->_common_validation_messages();

        $messages = array_merge($messages, []);

        $table_name = $this->tableName;

        $rules = array_merge($rules, [
            "name" => [
                "required",
                "min:2",
                Rule::unique($this->tableName, "name")->where("state_id", $request->state_id)->whereNot("id", $id)
            ]
        ]);

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $model = $this->modelClass::findOrFail($id);

            $model->fill($validatedData);

            $model->save();

            $this->updateUserInfoAfterSave($model);

            DB::commit();

            $this->saveSqlLog();

            return $this->afterSave($model);
        } catch (Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }
    public function ajax_get_list($state_id)
    {
        $response = ["status" => 1, "data" => []];
        
        $builder = $this->modelClass::where("state_id", $state_id);
        
        $records = $builder->orderBy("name", "ASC")->get();

        $response['data'] = $records;

        return $this->responseJson($response);
    }
}
