<?php

namespace App\Http\Controllers\Backend;

use App\Acl\AccessControl;
use App\Helpers\FileUtility;
use App\Mail\NewUserRegistered;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends BackendController
{
    public String $routePrefix = "user";

    protected $modelClass = User::class;

    public function index()
    {
        $cache_key_prefix = Route::currentRouteName();

        $builder = $this->_getBuildeForIndex($cache_key_prefix);

        $records = $this->getPaginagteRecords($builder, $cache_key_prefix);

        $role_list = Role::getListCache();

        $this->setForView(compact("records", "role_list"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuildeForIndex($cache_key_prefix, $apply_sort = true)
    {
        $cache_key = $cache_key_prefix . "-index";

        $conditions = $this->getConditions($cache_key, [
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "email", "type" => "string", "view_field" => "email"],
            ["field" => "is_active", "type" => "", "view_field" => "is_active"],
        ]);

        $role_condition = $this->getConditions($cache_key . ".2", [
            ["field" => "role_id", "type" => "", "view_field" => "role_id"],
        ], true);

        $builder = $this->modelClass::where($conditions)->whereHas(
            'userRole',
            function ($q) use ($role_condition) {
                if (isset($role_condition['role_id'])) {
                    $q->where("role_id", $role_condition['role_id']);
                }
            }
        );

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

        $this->_set_for_create_and_edit($model);
        $this->setForView(compact("model", 'form'));
        return $this->view("add");
    }

    private function _set_for_create_and_edit($model)
    {
        $conditions = [];

        if ($model && $model->userRole) {
            $exist_role_ids = $model->userRole->pluck("role_id")->toArray();
            $conditions['or_id'] = $exist_role_ids;
        }
        $role_list = Role::getList("id", "display_name", $conditions);

        $this->setForView(compact("role_list"));
    }

    private function _common_validation_rules()
    {
        return [
            'name' => 'required',
            'roles' => "required",
            "dont_send_email" => "",
            "is_active" => "",
        ];
    }

    private function _common_validation_messages()
    {
        return [];
    }

    public function store(Request $request)
    {
        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        $rules = array_merge($rules, [
            'password' => 'required|min:5',
            'password_confirm' => ['required', 'same:password'],
            'email' => "required|email|unique:users",
        ]);

        $messages = array_merge($messages, [
            'confirm_password.same' => 'Confirm password is not matched with password',
            'email.email' => 'Email must be valid email address.',
            'email.unique' => 'Email already exist',
        ]);

        $validatedData = $request->validate($rules, $messages);

        $validatedData['password'] = bcrypt($validatedData['password']);

        DB::beginTransaction();
        try {
            $save_data = $validatedData;
            unset($save_data['roles']);
            $model = $this->modelClass::create($save_data);
            $this->_saveOthers($model, $validatedData);
            DB::commit();
            $this->saveSqlLog();
            return $this->afterSave($model);
        } catch (Exception $ex) {
            DB::rollBack();
            $this->saveSqlLog();
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    private function _saveOthers(User $model, $validatedData)
    {
        $model->assignRoles($validatedData['roles']);
    }


    public function edit($id)
    {
        $model = $this->modelClass::with("userRole")->findOrFail($id);

        $this->beforeEdit($model);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_for_create_and_edit($model);

        $this->setForView(compact("model", "form"));

        return $this->view("edit");
    }

    public function update($id, Request $request)
    {
        $model = $this->modelClass::with("userRole")->findOrFail($id);

        $this->beforeEdit($model);

        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        $rules = array_merge($rules, [
            'name' => 'required',
            'email' => "required|email|unique:users,email," . $model->id,
            'profile_image' => ''
        ]);

        $messages = array_merge($messages, [
            'email.email' => 'Email must be valid email address.',
            'email.unique' => 'Email already exist',
        ]);

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $validatedData['profile_image'] = trim($validatedData['profile_image']);
            if ($validatedData['profile_image']) {
                $ext = pathinfo($validatedData['profile_image'], PATHINFO_EXTENSION);
                $dest = laravel_constant("path.user") . $id . "." . $ext;

                $new_file = FileUtility::move($validatedData['profile_image'], $dest, TRUE);
                if ($new_file === false) {
                    throw_exception("Fail To move file from temp folder to users folder");
                }

                $validatedData['profile_image'] = $new_file . "?" . time();
            } else {
                unset($validatedData['profile_image']);
            }

            $save_data = $validatedData;
            unset($save_data['roles']);

            $model->fill($save_data);
            $model->save();

            $this->_saveOthers($model, $validatedData);

            DB::commit();

            $this->saveSqlLog();

            return $this->afterSave($model);
        } catch (\Exception $ex) {
            DB::rollBack();

            $this->saveSqlLog();

            return back()->with('fail', $ex->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        abort(ACTION_NOT_PROCEED, "Because many tables contain created_by and updated_by field, which refer to user, can not delete");

        $request = request();

        try {
            $model = $this->modelClass::findOrFail($id);

            DB::beginTransaction();

            $this->beforeDelete($model);

            $this->deleteCascade($model);

            DB::commit();

            $this->saveSqlLog();

            return $this->afterDestroy($model);
        } catch (\Exception $ex) {
            if ($request->ajax()) {
                return $this->responseJson(['status' => 0, 'msg' => $ex->getMessage()]);
            }

            $this->saveSqlLog();

            return back()->with('fail', $ex->getMessage());
        }
    }

    public function change_password(Request $request)
    {
        if ($request->isMethod("post")) {
            $validatedData = $request->validate([
                'old_password' => ['required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('The old password is incorrect.');
                    }
                }],
                'new_password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'same:new_password'],
            ]);
            $user = auth()->user();
            $user->password = bcrypt($validatedData['new_password']);
            $user->save();

            return back()->with('success', 'Password changed successfully!');
        }

        return $this->view(__FUNCTION__);
    }

    public function otp_verified_view($email) {}

    
}
