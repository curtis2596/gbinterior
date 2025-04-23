<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Models\Employee;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\Party;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class LedgerAccountController extends BackendController
{
    public String $routePrefix = "ledger-accounts";

    protected $modelClass = LedgerAccount::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        //dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            'party' => function ($q) {
                return $q->select("id", "name");
            },            
        ]),Route::currentRouteName());

        $partyList = Party::getListCache();

        $this->_set_common_list();

        $this->setForView(compact("records", 'partyList'));

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
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "type", "type" => "", "view_field" => "type"],
            ["field" => "party_id", "type" => "int", "view_field" => "party_id"],
            ["field" => "ledger_category_id", "type" => "int", "view_field" => "ledger_category_id"],
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

        $this->_set_list_for_form(null);

        $is_add_screen = true;

        $this->setForView(compact("model", 'form', 'is_add_screen'));

        return $this->view(__FUNCTION__);
    }

    private function _set_list_for_form($model)
    {
        $this->_set_common_list();

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->party_id)
        {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        $empList = Employee::getListCache('id','name');

        $this->setForView(compact('partyList', 'empList'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'bank_name' => '',
            'bank_branch_name' => '',
            'bank_branch_address' => '',
            'bank_branch_ifsc' => '',
            'bank_account_no' => '',
            'comments' => '',
            'is_active' => '',
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();

        // dd($rules);

        $validatedData = $request->validate(array_merge($rules, [
            'ledger_category_id' => 'required',
            'name' => [
                'required',
                'min:2',
                'max:100',
                'unique:' . $this->tableName . ',name',
            ],
            'opening_balance_type' => ["required"],
            'opening_balance' => [
                'required',
                'numeric'
            ]
        ]));

        // dd($validatedData);

        $validatedData['type'] = LedgerAccount::TYPE_SIMPLE;

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

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", "form"));

        return $this->view(__FUNCTION__);
    }

    public function update($id, Request $request)
    {
        $model = $this->modelClass::findOrFail($id);

        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate($rules);

        if ($model->type == LedgerAccount::TYPE_PARTY) {
            
        } 
        else {
            $data = $request->validate(array_merge($rules, [
                'ledger_category_id' => 'required',
                'name' => [
                    'required',
                    'min:2',
                    'max:100',
                    'unique:' . $this->tableName . ',name,' . $model->id,
                ],        
                'opening_balance_type' => ["sometimes", "required"],
                'opening_balance' => [
                    "sometimes",
                    'required',
                    'numeric'
                ]        
            ]));

            $validatedData = array_merge($validatedData, $data);
        }

        $model->fill($validatedData);
        $model->save();

        $this->saveSqlLog();


        return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
    }

    public function set_opening_balance(Request $request)
    {
        if ($request->isMethod("post"))
        {
            // dd($request->all());

            $validateData = $request->validate([
                'accounts.*.id' => 'required',
                'accounts.*.opening_balance_type' => '',
                'accounts.*.opening_balance' => 'required|numeric|min:0'
            ], [
                "accounts.*.opening_balance_type.required" => "Opening Balance Type is required",
                "accounts.*.opening_balance.required" => "Opening Balance is required",
                "accounts.*.opening_balance.required" => "Opening Balance is required",
                "accounts.*.opening_balance.numeric" => "Opening Balance should be numeric",
                "accounts.*.opening_balance.min" => "Opening Balance should be more than or equal to 0",
            ]);

            try
            {
                $update_counter = 0;

                foreach($validateData['accounts'] as $account)
                {
                    $model = $this->modelClass::findOrFail($account['id']);

                    $model->fill($account);

                    if ($model->isDirty() && $model->save())
                    {
                        $update_counter++;
                    }
                }

                if ($update_counter > 0)
                {
                    $msg = "$update_counter records updated";

                    Session::flash("success", $msg);
                }
            }
            catch(Exception $ex)
            {
                Session::flash("fail", $ex->getMessage());
            }

            $this->saveSqlLog();

            return back();
        }

        $accounts = $this->modelClass::get();

        foreach($accounts as $k => $account)
        {
            $accounts[$k]->name = $account->getDisplayName();
            
            if ($account->code == LedgerAccount::CODE_stock)
            {
                unset($accounts[$k]);                
            }
        }

        $accounts = $accounts->toArray();

        // dd($accounts);

        $this->_set_common_list();

        $this->setForView(compact("accounts"));

        return $this->view(__FUNCTION__);
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

        $records = $this->modelClass::where($conditions)->with([
            'party' => function ($q) {
                return $q->select("id", "name");
            },
            'employee' => function ($q) {
                return $q->select("id", "name");
            },
        ])->get()->toArray();

        //d($records); exit;

        $csv_records = [];
        $user_list = User::getListCache();

        $yes_no_list = config('constant.yes_no');
        $ledger_types = LedgerAccount::TYPE_LIST;

        foreach ($records as $record) {
            $csv_records[] = [
                'ID' => $record['id'],
                'Type' => $ledger_types[$record['type']] ?? "",
                'Name' => $record['account_name'] ?? "",
                'Party' => $record['party']['name'] ?? "",

                'Bank Name' => $record['bank_name'] ?? "",
                'Bank Branch Name' => $record['bank_branch_name'] ?? "",
                'Bank Branch Addres' => $record['bank_branch_address'] ?? "",
                'Bank Branch IFSC' => $record['bank_branch_ifsc'] ?? "",
                'Bank Account No.' => $record['bank_account_no'] ?? "",
                'Comments' => $record['comments'] ?? "",
                'Opening Balance' => $record['opening_balance'],
                'Current Balance' => $record['current_balance'],
                'Net Balance' => $record['opening_balance'] + $record['current_balance'],

                'Active' => $yes_no_list[$record['is_active']] ?? "",
                'Pre Defined' => $yes_no_list[$record['is_pre_defined']] ?? "",
                'Created' => if_date_time($record['created_at']),
                'Created By' => $user_list[$record['created_by']] ?? "",
                'Updated' => if_date_time($record['updated_at']),
                'Updated By' => $user_list[$record['updated_by']] ?? "",
            ];
        }

        $path = config('constant.path.temp');
        FileUtility::createFolder($path);
        $file = $path . $this->tableName . "_" . date(DateUtility::DATETIME_OUT_FORMAT_FILE) . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        download_start($file, "application/octet-stream");
    }

    public function ajax_get_list($party_id = 0, $employee_id = 0)
    {
        $response = ["status" => 1, "data" => []];

        $builder = $this->modelClass::where('is_active', 1);

        if ($party_id) {
            $builder->where("party_id", $party_id);
        }

        if ($employee_id) {
            $builder->where("employee_id", $employee_id);
        }

        $records = $builder->with([
            "party" => function ($q) {
                $q->select(["id", "name"]);
            },
            "employee" => function ($q) {
                $q->select(["id", "name"]);
            },
        ])->orderBy("id", "ASC")->get();

        foreach ($records as $k => $record) {
            $records[$k]->name = $record->getNameId();
        }

        $response['data'] = $records;

        return $this->responseJson($response);
    }
}
