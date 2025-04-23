<?php

namespace App\Exceptions;

use App\Helpers\Menu;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request as HttpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (HttpException $e, HttpRequest $request) {

            $error_code = $e->getStatusCode();

            $error_msg = $e->getMessage();

            $request = request();

            $current_url = $request->url();

            $current_route_name = Route::currentRouteName();

            $data = compact("error_code", "error_msg", "current_url", "current_route_name");

            if ($request->ajax())
            {
                $view_name = "errors.ajax.$error_code";
                if (!view()->exists($view_name))
                {
                    $view_name = "errors.ajax.default";
                }

                return response()->view($view_name, $data, $e->getStatusCode());
            }
            else
            {
                $view_name = "errors.$error_code";
                if (!view()->exists($view_name))
                {
                    $view_name = "errors.default";
                }

                $auth_user = Auth::user();

                if ($auth_user)
                {
                    //below code if its is backend user
                    $data['layout'] = 'backend.layouts.main';

                    Menu::setCurrentRouteName($current_route_name);

                    $menus = Menu::get(Auth::user()->id);

                    $header_menu_list = Menu::getList($menus);

                    $partial_path = "backend.partials";

                    $breadcums = Menu::getBreadcums($menus);

                    $data = array_merge($data, compact("menus", "header_menu_list", "partial_path", "breadcums"));
                }
                else
                {
                    $data['layout'] = 'backend.layouts.default';
                }

                return response()->view($view_name, $data, $error_code);
            }
        });
    }
}