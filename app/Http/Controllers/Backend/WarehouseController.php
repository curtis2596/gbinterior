<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Models\Party;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class WarehouseController extends BackendController
{
    public String $routePrefix = "warehouses";
    public $modelClass = Warehouse::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "party" => function ($q) {
                $q->select("id", "name");
            },
            "state" => function ($q) {
                $q->select("id", "name");
            },
            "city" => function ($q) {
                $q->select("id", "name");
            }
        ]),Route::currentRouteName());

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    protected function beforeViewRender()
    {
        parent::beforeViewRender();

        $typeList = Warehouse::TYPE_LIST;
        
        $stateList = State::getListCache('id','name');
        
        $this->setForView(compact("typeList", "stateList"));
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "type", "type" => "", "view_field" => "type"],
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "is_active", "type" => "", "view_field" => "is_active"],
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

        $this->_set_form_list($model);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_form_list($model)
    {
        $conditions = [
            "is_job_worker" => 1,
            "or_id" => []
        ];

        if ($model && $model->party_id)
        {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        $this->setForView(compact("partyList"));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'type' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'address' => 'required',
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

        if ($validatedData['type'] == Warehouse::TYPE_PARTY)
        {
            $rules = [
                'party_id' => ['required'],
                'name' => [
                    'required',
                    Rule::unique($this->tableName)->where(function ($query) use($request) {
                        return $query
                                ->where('type', $request->input("type"))
                                ->where('party_id', $request->input("party_id"))
                                ->where('name', $request->input('name'));
                    })
                ]
            ];

            $data = $request->validate($rules, $messages);

            $validatedData = array_merge($data, $validatedData);
        }
        else
        {
            $rules = [
                'name' => [
                    'required',
                    Rule::unique($this->tableName)->where(function ($query) use($request) {
                        return $query
                            ->where('type', $request->input("type"))
                            ->where('name', $request->input('name'));
                    })
                ]
            ];

            $data = $request->validate($rules, $messages);

            $validatedData = array_merge($data, $validatedData);
        }

        try
        {
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

        $this->_set_form_list($model);

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    public function update($id, Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        if ($validatedData['type'] == Warehouse::TYPE_PARTY)
        {
            $rules = [
                'party_id' => ['required'],
                'name' => [
                    'required',
                    Rule::unique($this->tableName)->where(function ($query) use($request, $id) {
                        return $query
                                ->where("id", "<>", $id)
                                ->where('type', $request->input("type"))
                                ->where('party_id', $request->input("party_id"))
                                ->where('name', $request->input('name'));
                    })
                ]
            ];

            $data = $request->validate($rules, $messages);

            $validatedData = array_merge($data, $validatedData);
        }
        else
        {
            $rules = [
                'name' => [
                    'required',
                    Rule::unique($this->tableName)->where(function ($query) use($request, $id) {
                        return $query
                            ->where("id", "<>", $id)
                            ->where('type', $request->input("type"))
                            ->where('name', $request->input('name'));
                    })
                ]
            ];

            $data = $request->validate($rules, $messages);

            $validatedData = array_merge($data, $validatedData);
        }

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
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }


    public function csv()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $count = $this->modelClass::where($conditions);

        $this->beforeCSVExport($count);

        $records = $this->modelClass::with([
                "party" => function ($q) {
                    $q->select("id", "name");
                },
                "state" => function ($q) {
                    $q->select("id", "name");
                },
                "city" => function ($q) {
                    $q->select("id", "name");
                }
            ])->where($conditions)->get()->toArray();

        //d($records); exit;

        $csv_records = [];

        $yes_no_list = config('constant.yes_no');
        $user_list = User::getListCache();

        foreach($records as $record)
        {
            $csv_records[] = [
                'ID' => $record['id'],
                'Type' => Warehouse::TYPE_LIST[$record['type']],
                'Name' => $record['name'],
                'Party' => $record['party']['name'] ?? "",
                'State' => $record['state']['name'] ?? "",
                'City' => $record['city']['name'] ?? "",
                'Address' => $record['address'],
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
