<?php

namespace App\Http\Controllers\Backend;

use App\Acl\AccessControl;
use App\Acl\SectionRoutes;
use App\Models\Role;
use App\Models\RoleRouteName;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class PermissionController extends BackendController
{
    public String $routePrefix = "permissions";

    public function index()
    {
        $conditions = $this->getConditions(Route::currentRouteName(), [
            ["field" => "role_id", "type" => "int", "view_field" => "role_id"],
        ], true);

        $section_conditions = $this->getConditions(Route::currentRouteName() . "-section", [
            ["field" => "section_name", "type" => "int", "view_field" => "section_name"],
            ["field" => "action_name", "type" => "int", "view_field" => "action_name"],
        ], true);

        $sections = SectionRoutes::get();

        if ($conditions || $section_conditions) {

            $records = RoleRouteName::where($conditions)->with([
                "role" => function ($query) {
                    $query->select(["id", "name", "code"]);
                },
                "routeName" => function ($query) {
                    $query->select(["id", "name"]);
                }
            ])->get()->toArray();

            //d($section_conditions); exit;

            foreach ($records as $k => $record) {
                if (isset($record['role']) && isset($record['route_name'])) {

                    $records[$k]['section'] = "Section is not set in SectionRoutes.php";
                    $records[$k]['can_be_delete'] = true;                    

                    if (in_array($record['route_name']['name'], SectionRoutes::ALLOW_ROUTES_FOR_ANY_LOGIN_USER)) {
                        $records[$k]['can_be_delete'] = false;
                        $records[$k]['role']['name'] = "Allow For All Login User";
                    }

                    if ($record['role']['code'] == Role::TYPE_SYSTEM_ADMIN && in_array($record['route_name']['name'], SectionRoutes::ALLOW_ROUTES_FOR_SYSTEM_ADMIN)) {
                        $records[$k]['can_be_delete'] = false;
                        $records[$k]['role']['name'] = "Allow For All System Admin";
                    }

                    foreach ($sections as $section_name => $actions) {
                        foreach ($actions as $action_name => $route_list) {
                            if (in_array($record['route_name']['name'], $route_list)) {
                                $records[$k]['section'] = $section_name;
                                $records[$k]['action'] = $action_name;
                            }
                        }
                    }

                    if (isset($section_conditions['section_name']) && $section_conditions['section_name'] != $records[$k]['section']) {
                        unset($records[$k]);
                    }

                    if (isset($section_conditions['action_name']) && $section_conditions['action_name'] != $records[$k]['action']) {
                        unset($records[$k]);
                    }

                } else {
                    unset($records[$k]);
                }

            }

            usort($records, function ($a, $b) {
                return strcmp($a['role']["name"], $b['role']["name"]);
            });

            foreach(SectionRoutes::ALLOW_ROUTES_FOR_ANY_LOGIN_USER as $route_name)
            {
                $records[] = [
                    'section' => '',
                    'action' => $route_name,
                    'can_be_delete' => false,                    
                    'role' => [
                        'name' => 'Allow For All Login User'
                    ]
                ];
            }

            //d($conditions); exit;
            if ($conditions && isset($conditions["role_id"]) && $conditions["role_id"])
            {
                $role = Role::findOrFail($conditions["role_id"]); 
                if ($role->code)
                {
                    foreach(SectionRoutes::ALLOW_ROUTES_FOR_SYSTEM_ADMIN as $route_name)
                    {
                        $records[] = [
                            'section' => '',
                            'action' => $route_name,
                            'can_be_delete' => false,                    
                            'role' => [
                                'name' => 'Allow For All System Admin User'
                            ]
                        ];
                    }
                }
            }


            $this->setForView(compact("records"));
        }

        $role_list = Role::getList();

        $keys = array_keys($sections);
        $section_list = array_combine($keys, $keys);

        $this->setForView(compact("role_list", "section_list"));

        return $this->view(__FUNCTION__);
    }

    public function assign(Request $request)
    {
        if ($request->isMethod('post')) {
            $sections = SectionRoutes::get();

            $accessControl = AccessControl::init();
            $synced_route_name_list = $accessControl->syncRouteNamesToDatabase();

            $data = $request->all();

            $role = Role::findOrFail($data['role_id']);

            //d($sections);

            $choosen_route_list = [];
            foreach ($data['data'] as $section_name => $data_actions) {
                if (isset($sections[$section_name])) {
                    foreach ($data_actions as $action_name) {
                        if (isset($sections[$section_name][$action_name])) {
                            $choosen_route_list = array_merge($choosen_route_list, $sections[$section_name][$action_name]);
                        }
                    }
                }
            }

            $choosen_route_list = array_unique($choosen_route_list);

            $roleRouteName = new RoleRouteName();

            $old_save_id_list = RoleRouteName::where("role_id", "=", $role->id)->pluck("id", "id");
            foreach ($choosen_route_list as $route_name) {
                if (!isset($synced_route_name_list[$route_name])) {
                    die("$route_name not found. may be you forgot to add in SectionRoutes.php");
                }

                $saved_id = $roleRouteName->insertIgnoreIfExist([
                    "role_id" => $data['role_id'],
                    "route_name_id" => $synced_route_name_list[$route_name],
                ]);

                unset($old_save_id_list[$saved_id]);
            }

            RoleRouteName::destroy($old_save_id_list);

            $user_id_list = User::pluck("id")->toArray();

            $accessControl->clearMenuCache($user_id_list);

            $this->saveSqlLog();

            Session::flash('success', 'Permission are saved');
        }

        $role_list = Role::getList();

        $this->setForView(compact("role_list"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_get_permissions($role_id)
    {
        $role_route_names = RoleRouteName::select(['role_id', 'route_name_id'])->where("role_id", "=", $role_id)
            ->with(['routeName' => function ($query) {
                $query->select(['id', 'name']);
            }])->get();

        $saved_route_list = [];

        foreach ($role_route_names->toArray() as $role_route) {
            $saved_route_list[] = $role_route['route_name']['name'];
        }

        $sections = SectionRoutes::get();

        foreach ($sections as $section_name => $actions) {
            foreach ($actions as $action_name => $route_list) {
                unset($actions[$action_name]);

                $is_checked = false;
                foreach ($route_list as $route_name) {
                    if (in_array($route_name, $saved_route_list)) {
                        $is_checked = true;
                    }
                }

                $actions[$action_name]['is_checked'] = $is_checked;
            }

            $sections[$section_name] = $actions;
        }

        $this->setForView(compact("sections"));

        $this->layout = "backend.layouts.ajax";

        return $this->view(__FUNCTION__);
    }

    public function ajax_delete(Request $request)
    {
        $respone = ["status" => 0, "msg" => "Unkown Error"];

        try {
            $id = $request->get("id");

            $record = RoleRouteName::select('id')->findOrFail($id);

            $record->delete();

            $respone["status"] = 1;
            $respone["msg"] = "Success";
        } catch (Exception $ex) {
            $respone['msg'] = $ex->getMessage();
        }

        return $this->responseJson($respone);
    }
}
