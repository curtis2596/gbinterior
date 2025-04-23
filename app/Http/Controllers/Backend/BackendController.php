<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Menu;
use App\Http\Controllers\WebController;
use App\Models\BaseModel;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackendController extends WebController
{
    protected function beforeViewRender()
    {
        if (!parent::beforeViewRender())
        {
            return false;
        }

        $request = request();

        if ( $request->ajax() ) {
            $this->layout = "backend.layouts.ajax";
        }
        else {
            $this->layout = "backend.layouts.main";
        }
        
        $this->viewPrefix = "backend." . $this->getControllerName();

        $request = request();

        $current_route_name = Route::currentRouteName();

        if ($current_route_name)
        {
            Menu::setCurrentRouteName($current_route_name);
        }

        $auth_user = Auth::user();

        $menus = $header_menu_list = [];
        if ($auth_user)
        {
            $menus = Menu::get($auth_user->id);
        }

        // d($menus, true);

        $header_menu_list = Menu::getList($menus);
        $breadcums = Menu::getBreadcums($menus);

        $userListCache = User::getListCache();

        $partial_path = "backend.partials";

        $this->setForView(compact("current_route_name", "menus", "header_menu_list", "partial_path", "breadcums", "userListCache"));

        return true;
    }

    public function destroy($id)
    {
        $request = request();

        try
        {       
            $model = $this->modelClass::findOrFail($id); 

            if (!$this->delete($model))
            {
                throw new Exception("Fail to Delete");
            }

            $this->saveSqlLog();

            return $this->afterDestroy($model);
        }
        catch(\Exception $ex)
        {
            if ( $request->ajax() ) {
                return $this->responseJson(['status'=> 0, 'msg'=> $ex->getMessage()]);
            }

            $this->saveSqlLog();

            return back()->with('fail', $ex->getMessage());
        }
    }

    public function activate($id)
    {
        $response = ["status" => 1];
        try
        {
            $model = $this->modelClass::findOrFail($id);

            $model->activate();

            $response['url'] = route($this->routePrefix . ".de_activate", ['id' => $id]);
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function de_activate($id)
    {
        $response = ["status" => 1];
        try
        {
            $model = $this->modelClass::findOrFail($id);

            $model->deActivate();

            $response['url'] = route($this->routePrefix . ".activate", ['id' => $id]);
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try
        {
            $model = $this->modelClass::findOrFail($id);

            $response['data'] = $model->toArray();
        }
        catch(Exception $ex)
        {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }
}
