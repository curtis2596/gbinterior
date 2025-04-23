<?php

namespace App\Http\Controllers;

use App\Helpers\DateUtility;
use App\Models\BaseModel;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{
    /**
     * variable require for view
     */
    protected String $routePrefix, $viewPrefix, $tableName, $layout;

    protected $modelClass;

    protected int $paginationLimit = 10;

    /**
     * variable to store data which will pass to view
     */
    private $data = [];

    public function __construct()
    {
        if (isset($this->modelClass)) {
            $model = new $this->modelClass();

            $this->tableName = $model->getTable();
        }
    }

    protected function setForView(array $array)
    {
        $this->data = array_merge($this->data, $array);
    }

    protected function beforeViewRender()
    {
        $this->viewPrefix = $this->getControllerName();

        $this->data['page_title'] = $this->getPageTitle();

        $this->data['yes_no_list'] = laravel_constant("yes_no_list");


        $this->data['success_fail_list'] = laravel_constant("success_fail_list");

        return true;
    }

    protected function view($view_name)
    {
        $this->beforeViewRender();

        if (isset($this->viewPrefix)) {
            $this->data['viewPrefix'] = $this->viewPrefix;
            $view_name = $this->viewPrefix . "." . $view_name;
        }

        if (isset($this->layout)) {
            $this->data['layout'] = $this->layout;
        }

        if (isset($this->routePrefix)) {
            $this->data['routePrefix'] = $this->routePrefix;
        }

        return view($view_name, $this->data);
    }

    protected function viewIndex($view_name)
    {
        if (request()->ajax()) {
            $view_name = "ajax_" . $view_name;
        }

        return $this->view($view_name);
    }

    protected function getControllerName(): string
    {
        $action = request()->route()->getAction();

        $action_arr = explode("@", $action['controller']);

        $controller_name = $action_arr[0];

        if (strpos($controller_name, "\\") >= 0) {
            $arr = explode("\\", $controller_name);

            if ($arr) {
                $controller_name = end($arr);
            }
        }

        $controller_name = str_replace("Controller", "", $controller_name);

        return $controller_name;
    }

    protected function getModuleName(): string
    {
        return str_space_before_every_capital_letter($this->getControllerName());
    }

    protected function getMethodName(): string
    {
        $action = request()->route()->getAction();

        $action_arr = explode("@", $action['controller']);

        if (isset($action_arr[1])) {
            $method_name = $action_arr[1];

            return $method_name;
        }

        return "";
    }

    protected function getPageTitle(): string
    {
        $method_name = $this->getMethodName();

        $sub_title = null;

        if ($method_name == "index") {
        } else if ($method_name == "show") {
            $sub_title = "View";
        } else {
            $sub_title = str_replace("index", "Summary", $method_name);

            $sub_title = str_function_name_to_human_text($sub_title);
        }

        $module_name = $this->getModuleName();

        $page_title = trim($module_name);

        if (isset($sub_title) && $sub_title) {
            $page_title .= " | " . $sub_title;
        }

        $page_title = ucwords($page_title);

        return $page_title;
    }

    protected function getRequestData($cache_prefix, array $array, bool $is_cache_clear = false)
    {
        if ($cache_prefix) {
            $cache_key = BaseModel::optimizeCacheKey(get_cache_prefix() . "-request_" . $cache_prefix . "_" . auth()->id());

            if (Cache::has($cache_key)) {
                $cache_params = Cache::get($cache_key);
            }
        }

        $request_params = request()->all();

        $params = [];

        foreach ($array as $row) {
            if (!isset($row['key'])) {
                throw_exception("key in not set in argument array");
            }

            $key = $row['key'];

            if (isset($row['default'])) {
                $params[$key] = $row['default'];
            }

            if (!$is_cache_clear && isset($cache_params[$key])) {
                $params[$key] = $cache_params[$key];
            }

            if (isset($request_params[$key])) {
                $params[$key] = $cache_params[$key] = $request_params[$key];
            }
        }

        if (isset($cache_key)) {
            if ($is_cache_clear) {
                Cache::forget($cache_key);
            } else {
                if (!Cache::put($cache_key, $params, laravel_constant("cache_time.summary_search"))) {
                    throw_exception("Fail to put cache");
                }
            }
        }

        return $params;
    }

    protected function getConditions($cache_prefix, array $array, bool $return_key_value_pair_conditions = false)
    {
        $conditions = [];
        $search_variables = [];

        if ($cache_prefix) {
            $cache_key = BaseModel::optimizeCacheKey(get_cache_prefix() . "-search_" . $cache_prefix . "_" . auth()->id());
        }

        $request_params = request()->all();

        if (empty($request_params) && isset($cache_key)) {
            if (Cache::has($cache_key)) {
                $request_params = Cache::get($cache_key);
            }
        }

        foreach ($array as $row) {
            if (!isset($row['field'])) {
                throw_exception("field key in not set in argument array");
            }

            if (!isset($row['type'])) {
                throw_exception("type key in not set in argument array");
            }

            $field = $row['field'];
            $view_field = $row['view_field'] ?? $row['field'];
            $search_variables[$view_field] = "";
            $value = null;

            if (isset($row['default']) && !empty($row['default'])) {
                $value = $row['default'];
            }

            if (isset($request_params[$view_field])) {
                $value = $request_params[$view_field];
            }

            if (!isset($value)) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $search_variables[$view_field] = $value;

            if (is_array($value)) {
                if (!empty($value)) {
                    $op = "IN";

                    foreach ($value as $k => $v) {
                        if (is_string($v)) {
                            $v = trim($v);
                        }

                        $arr = $this->parseConditionValue($v, $row['type']);
                        $value[$k] = $arr['value'];
                    }

                    $parse_value = implode(",", $value);
                    if ($return_key_value_pair_conditions) {
                        if ($op && $op != "=") {
                            $conditions[$field . " " . $op] = $parse_value;
                        } else {
                            $conditions[$field] = $parse_value;
                        }
                    } else {
                        $conditions[] = [$field, $op, $parse_value];
                    }
                }
            } else if (strlen($value) > 0) {
                $arr = $this->parseConditionValue($value, $row['type']);

                $parse_value = $arr['value'];
                $op = $arr['op'];

                if ($return_key_value_pair_conditions) {
                    if ($op && $op != "=") {
                        $conditions[$field . " " . $op] = $parse_value;
                    } else {
                        $conditions[$field] = $parse_value;
                    }
                } else {
                    $conditions[] = [$field, $op, $parse_value];
                }
            }
        }

        if (isset($cache_key)) {
            if (!Cache::put($cache_key, $search_variables, laravel_constant("cache_time.summary_search"))) {
                throw_exception("Fail to put cache");
            }
        }

        if (!isset($this->data['search'])) {
            $this->data['search'] = [];
        }

        $this->data['search'] = array_merge($this->data['search'], $search_variables);

        return $conditions;
    }

    protected function parseConditionValue($value, String $type)
    {
        $data_type = gettype($value);

        $type = strtolower(trim($type));

        if (in_array($type, ["string", "date", "from_date", "to_date", "datetime", "from_datetime", "to_datetime"])) {
            if ($data_type != "string") {
                throw_exception("input value is not typeof string");
            }
        }

        $parse_value = null;
        $op = '=';

        switch ($type) {
            case "string":
                $parse_value = "%" . $value . "%";
                $op = 'LIKE';
                break;

            case "date":
                $parse_value = DateUtility::getDate($value, DateUtility::DATE_FORMAT);
                break;

            case "from_date":
                $parse_value = DateUtility::getDate($value, DateUtility::DATE_FORMAT);
                $op = '>=';
                break;

            case "to_date":
                $parse_value = DateUtility::getDate($value, DateUtility::DATE_FORMAT);
                $op = '<=';
                break;

            case "from_datetime":
                $parse_value = DateUtility::getDate($value);
                $op = '>=';
                break;

            case "to_datetime":
                $parse_value = DateUtility::getDate($value);
                $op = '<=';
                break;

            case "int":
                $parse_value = (int) $value;
                break;

            case "from_int":
                $parse_value = (int) $value;
                $op = '>=';
                break;

            case "to_int":
                $parse_value = (int) $value;
                $op = '<=';
                break;

            case "float":
                $parse_value = (float) $value;
                break;

            case "from_float":
                $parse_value = (float) $value;
                $op = '>=';
                break;

            case "to_float":
                $parse_value = (float) $value;
                $op = '<=';
                break;

            default:
                $parse_value = $value;
                break;
        }

        return [
            "value" => $parse_value,
            "op" => $op
        ];
    }

    protected function getPaginagteRecords(Builder $builder, $cache_key_prefix)
    {
        $cache_key = BaseModel::optimizeCacheKey(get_cache_prefix() . "-pagination_limit-" . $cache_key_prefix . "_" . auth()->id());

        $params = $this->getRequestData($cache_key, [
            ["key" => "pagination_limit", "default" => $this->paginationLimit],
        ]);

        if ($params['pagination_limit'] < 1) {
            throw_exception("pagination_limit can not be less than 1");
        }

        $this->setForView([
            "pagination_limit" => $params['pagination_limit']
        ]);

        // d($params['pagination_limit']); exit;

        return $builder->paginate($params['pagination_limit']);
    }

    protected function beforeCreate() {}

    protected function beforeEdit($model)
    {
        if (BaseModel::tableHaveField($model->getTable(), "is_pre_defined")) {
            if ($model->is_pre_defined) {
                abort(ACTION_NOT_PROCEED, "Pre-Defined Record can not be edit");
            }
        }

        if (BaseModel::tableHaveField($model->getTable(), "can_edit")) {
            if (!$model->can_edit) {
                $msg = "Record can not be edit";
                if (BaseModel::tableHaveField($model->getTable(), "cannot_edit_reason_array") || array_key_exists("cannot_edit_reason_array", $model->attributes)) {
                    if ($model->cannot_edit_reason_array) {
                        $msg .= "Reason : " . implode(", ", $model->cannot_edit_reason_array);
                    }
                }

                abort(ACTION_NOT_PROCEED, $msg);
            }
        }
    }

    protected function updateUserInfoAfterSave(Model $model)
    {
        if (App::environment('production')) {
            return false;
        }

        $auth_user = Auth::user();

        if ($auth_user) {
            // dd($model->wasRecentlyCreated);
            if ($model->wasRecentlyCreated) {
                $auth_user->changeCreatedByUsage($model, 1);
            } else {
                $auth_user->changeUpdatedByUsage($model, 1);
            }

            // d($this->getQueryLog());
            // exit;
        }
    }

    protected function updateUserInfoAfterDelete(Model $model)
    {
        $auth_user = Auth::user();
        if ($auth_user) {
            die("Under Development");
        }
    }

    protected function updateUserInfoForCreateBulk(array $models)
    {
        if (App::environment('production')) {
            return false;
        }

        $auth_user = Auth::user();

        if ($auth_user) {
            $auth_user->changeCreatedByUsageBulk($models);
        }
    }

    protected function updateUserInfoForUpdateBulk(array $models)
    {
        if (App::environment('production')) {
            return false;
        }

        $auth_user = Auth::user();

        if ($auth_user) {
            $auth_user->changeUpdatedByUsageBulk($models);
        }
    }

    protected function updateUserInfoForDeleteBulk(array $models)
    {
        if (App::environment('production')) {
            return false;
        }

        $auth_user = Auth::user();
        // $auth_user = (User) $auth_user;

        if ($auth_user) {
            die("Under Development");
        }
    }

    protected function afterSave($model, $prefix = null, $msg = null)
    {
        $method_name = $this->getMethodName();

        if (is_null($prefix)) {
            $prefix = str_class_name_to_human_text(get_class($model));
        }

        if (!$msg) {
            if ($method_name == "store") {
                $msg = "$prefix Created successfully";
            } else if ($method_name == "update") {
                $msg = "$prefix Updated successfully";
            } else {
                $msg = "$prefix Saved successfully";
            }

            $msg = trim($msg);
        }

        if (!$msg) {
            throw_exception("msg variable required");
        }

        $redirect_to_index = (bool) request("redirect_to_index", false);

        if ($redirect_to_index) {
            return redirect()->route($this->routePrefix . ".index")->with('success', $msg);
        }

        return back()->with('success', $msg);
    }

    protected function beforeDelete($model)
    {
        if (BaseModel::tableHaveField($model->getTable(), "is_pre_defined")) {
            if ($model->is_pre_defined) {
                abort(ACTION_NOT_PROCEED, "Pre-Defined Record can not be delete");
            }
        }

        if (BaseModel::tableHaveField($model->getTable(), "can_delete")) {
            if (!$model->can_delete) {
                $msg = "Record can not be delete";
                if (BaseModel::tableHaveField($model->getTable(), "cannot_delete_reason_array") || array_key_exists("cannot_delete_reason_array", $model->attributes)) {
                    if ($model->cannot_delete_reason_array) {
                        $msg .= "Reason : " . implode(", ", $model->cannot_delete_reason_array);
                    }
                }

                abort(ACTION_NOT_PROCEED, $msg);
            }
        }
    }

    protected function delete(BaseModel $model)
    {
        try {
            DB::beginTransaction();

            $this->beforeDelete($model);

            $this->deleteCascade($model);

            DB::commit();

            return true;
        } catch (\Exception $ex) {
            DB::rollBack();

            throw $ex;
        }
    }

    protected function afterDestroy(Model $model, $prefix = null, $msg = null)
    {
        $request = request();

        if (is_null($prefix)) {
            $prefix = str_class_name_without_namespace(get_class($model));
        }

        if (!$msg) {
            $msg = "$prefix deleted successfully";

            $msg = trim($msg);
        }

        if (!$msg) {
            throw_exception("msg variable required");
        }

        if ($request->ajax()) {
            return response()->json(['status' => 1, 'msg' => $msg]);
        }

        $redirect_to = request("redirect_to", "index");

        return redirect()->route($this->routePrefix . "." . $redirect_to)->with('success', $msg);
    }

    protected function beforeCsvImport()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(60 * 60);
    }

    protected function beforeCsvExport(Builder $builder)
    {
        $max_count = Setting::getValueOrFail("export_csv_max_record_count");

        $record_count = $builder->count();

        if ($record_count > $max_count) {
            abort(ACTION_NOT_PROCEED, "Record Count is $record_count which is more than Max Limit $max_count");
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(60 * 10);
    }

    protected function beforePdfExport(Builder $builder)
    {
        $max_count = Setting::getValueOrFail("export_pdf_max_record_count");

        $record_count = $builder->count();

        if ($record_count > $max_count) {
            abort(ACTION_NOT_PROCEED, "Record Count is $record_count which is more than Max Limit $max_count");
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(60 * 10);
    }

    protected function _destroy($model)
    {
        $request = request();
        try {
            $model->delete();
            $this->saveSqlLog();

            if ($request->ajax()) {
                return response()->json(['status' => 1, 'msg' => 'Record deleted successfully.']);
            } else {
                return back()->with('success', 'Record deleted successfully.');
            }
        } catch (\Exception $ex) {
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'msg' => 'Fail To Delete']);
            } else {
                return back()->with('fail', $ex->getMessage());
            }
        }
        // \Log::info('Deleting model:', ['id' => $model->id]);
        // return $model->delete();
    }
    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::findOrFail($id);

            $response['data'] = $model->toArray();
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }
}
