<?php

namespace App\Acl;

use App\Helpers\RoleType;
use App\Models\Role;
use App\Models\RouteName;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AccessControl
{
    private static $instance = null;

    public static function init()
    {
        if (is_null(self::$instance)) {
            self::$instance = new AccessControl();
        }

        return self::$instance;
    }

    public function isAllow(String $route_name, array $role_id_list)
    {
        $route_name = trim($route_name);

        foreach(SectionRoutes::ALLOW_ROUTES_FOR_ANY_LOGIN_USER as $allowed_route)
        {
            if ($route_name == $allowed_route)
            {
                return true;
            }

            $pos = strpos($allowed_route, "*.");
            if ($pos >= 0)
            {
                $str = substr($allowed_route, $pos + 2);
                
                $matched = preg_match("/$str/", $route_name);

                if ($matched)
                {
                    return true;
                }
            }
        }

        if ($role_id_list)
        {
            $role_ids = implode(",", $role_id_list);

            $q = "
                SELECT
                    COUNT(1) AS c
                FROM
                    role_route_names RRN
                    INNER JOIN route_names RR ON RR.id = RRN.route_name_id
                WHERE
                    RRN.role_id IN ($role_ids) AND RR.name = '$route_name';
            ";

            $record = DB::select($q);

            if ($record && $record[0]->c > 0) {
                return true;
            }

            $system_role_count = Role::where("id", $role_id_list)->where('code', Role::TYPE_SYSTEM_ADMIN)->count();

            if ($system_role_count > 0 && in_array($route_name, SectionRoutes::ALLOW_ROUTES_FOR_SYSTEM_ADMIN)) {
                return true;
            }
        }

        return false;
    }

    public function getListOfAllowedRouteNames(array $role_id_list)
    {
        // by default allow 
        $allow_route_list = SectionRoutes::ALLOW_ROUTES_FOR_ANY_LOGIN_USER;

        // check for system admin
        foreach($role_id_list as $role_id)
        {
            $role = Role::select('id', 'is_system_admin')->findOrFail($role_id);

            if ($role->is_system_admin)
            {
                $allow_route_list = array_merge($allow_route_list, SectionRoutes::ALLOW_ROUTES_FOR_SYSTEM_ADMIN);
            }
        }

        $role_ids = implode(",", $role_id_list);

        $q = "
            SELECT DISTINCT
                RR.name
            FROM
                role_route_names RRN
                INNER JOIN route_names RR ON RR.id = RRN.route_name_id
            WHERE
                RRN.role_id IN ($role_ids)
        ";

        $records = DB::select($q);


        foreach ($records as $record) {
            $allow_route_list[] = $record->name;
        }

        return $allow_route_list;
    }

    public function getMenuCacheKey($user_id)
    {
        return "menu_user_" . $user_id;
    }

    public function clearMenuCache(array $user_id_list)
    {
        foreach ($user_id_list as $user_id) {
            $cache_key = $this->getMenuCacheKey($user_id);

            if (Cache::has($cache_key)) {
                Cache::forget($cache_key);
            }
        }
    }

    public function syncRouteNamesToDatabase()
    {
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();

        $route_list = [];
        foreach ($routeCollection as $route) {
            $name = trim($route->getName());
            if ($name) {
                $route_list[] = $name;
            }
        }

        sort($route_list);

        $routeNameModel = new RouteName();

        $old_id_list = RouteName::pluck('name', 'id')->toArray();

        $saved_route_name_list = [];

        foreach ($route_list as $route_name) {
            if (
                in_array($route_name, SectionRoutes::ALLOW_ROUTES_FOR_ANY_LOGIN_USER)
                && in_array($route_name, SectionRoutes::ALLOW_ROUTES_FOR_SYSTEM_ADMIN)
            ) {
                continue;
            }


            $id = $routeNameModel->insertIgnoreIfExist(["name" => $route_name]);

            unset($old_id_list[$id]);

            $saved_route_name_list[$route_name] = $id;
        }

        if ($old_id_list) {
            RouteName::destroy(array_keys($old_id_list));
        }

        return $saved_route_name_list;
    }
}
