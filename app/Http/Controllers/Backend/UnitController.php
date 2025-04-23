<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class UnitController extends BackendController
{

    public String $routePrefix = "units";
    protected $modelClass = Unit::class;

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
            ["field" => "code", "type" => "string", "view_field" => "code"],
            ["field" => "is_active", "type" => "int", "view_field" => "is_active"],
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

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

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
            'name' => 'required|min:3|max:180|unique:' . $this->tableName,
            'code' => 'required|min:2|max:12|unique:' . $this->tableName,
        ]));

        $this->modelClass::create($validatedData);

        return back()->with('success', 'Record created successfully');
    }

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

    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => 'required|min:3|max:180|unique:' . $this->tableName . ',name,' . $model->id,
            'code' => 'required|min:2|max:12|unique:' . $this->tableName . ',code,' . $model->id,
        ]));

        // dd($validatedData);

        $model->fill($validatedData);
        $model->save();

        $this->saveSqlLog();

        return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        if (!$model) {
            return response()->json(['status' => 0, 'msg' => 'Record not found.']);
        }

        return $this->_destroy($model);
    }


    public function csv()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $count = $this->modelClass::where($conditions);
        // d($records); exit;

        $this->beforeCSVExport($count);
        // dd("test");

        $records = $this->modelClass::where($conditions)->get()->toArray();


        $csv_records = [];

        $yes_no_list = config('constant.yes_no_list');
        $user_list = User::getListCache();

        foreach ($records as $record) {
            $csv_records[] = [
                'ID' => $record['id'],
                'Unit' => $record['name'],
                'Code' => $record['code'],
                'Pre Defined' => $yes_no_list[$record['is_pre_defined']] ?? "",
                'Active' => $yes_no_list[$record['is_active']] ?? "",
                'Created' => if_date_time($record['created_at']),
                'Created By' => $user_list[$record['created_by']] ?? "",
                'Updated' => if_date_time($record['updated_at']),
                'Updated By' => $user_list[$record['updated_by']] ?? "",
            ];
        }

        $path = config('constant.path.temp');
        FileUtility::createFolder($path);
        $file = $path . $this->tableName .  "_" . date(DateUtility::DATETIME_OUT_FORMAT_FILE) . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        download_start($file, "application/octet-stream");
    }
}
