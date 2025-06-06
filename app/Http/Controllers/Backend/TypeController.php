<?php

namespace App\Http\Controllers\Backend;

use App\Models\Type;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class TypeController extends BackendController
{
    public String $routePrefix = "type";

    protected $modelClass = Type::class;

    public function index()
    {
        $cache_key_prefix = Route::currentRouteName();

        $builder = $this->_getBuildeForIndex($cache_key_prefix);

        $records = $this->getPaginagteRecords($builder, $cache_key_prefix);

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuildeForIndex($cache_key_prefix, $apply_sort = true)
    {
        $cache_key = $cache_key_prefix . "-index";

        $conditions = $this->getConditions($cache_key, [
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "type", "type" => "string", "view_field" => "type"],
            ["field" => "short_name", "type" => "string", "view_field" => "short_name"],
            ["field" => "is_active", "type" => "", "view_field" => "is_active"],
        ]);

        $builder = $this->modelClass::where($conditions);

        if ($apply_sort)
        {
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

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _common_validation_rules()
    {
        return [
            "is_active" => ""
        ];
    }

    private function _common_validation_messages()
    {
        return [
            "name.required" => "Name is required",
            "name.unique" => "Name already exist",
            "type.required" => "Source is required",
            "type.unique" => "Source already exist",
        ];
    }

    public function store(Request $request)
    {
        $this->beforeCreate();

        $rules = $this->_common_validation_rules();

        $messages = $this->_common_validation_messages();

        $rules = array_merge($rules, [
            'name' => 'required|min:2|unique:' . $this->tableName,
            'type' => 'required|unique:' . $this->tableName,
            'short_name' => 'required|unique:' . $this->tableName,
        ]);

        $messages = array_merge($messages, [

        ]);

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try
        {
            $model = $this->modelClass::create($validatedData);
            
            $this->updateUserInfoAfterSave($model);
            
            DB::commit();

            $this->saveSqlLog();

            return $this->afterSave($model);
        }
        catch(Exception $ex)
        {
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

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $this->beforeEdit($model);

        $rules = $this->_common_validation_rules();

        $messages = $this->_common_validation_messages();

        $messages = array_merge($messages, [

        ]);

        $table_name = $this->tableName;

        $rules = array_merge($rules, [
            'name' => "required|min:3|unique:$table_name,name,$model->id",
            'type' => "required|unique:$table_name,type,$model->id",
            'short_name' => "required|unique:$table_name,short_name,$model->id",
        ]);

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try
        {
            $model = $this->modelClass::findOrFail($id);

            $model->fill($validatedData);

            $model->save();

            $this->updateUserInfoAfterSave($model);

            DB::commit();

            $this->saveSqlLog();

            return $this->afterSave($model);
        }
        catch(Exception $ex)
        {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }
}

