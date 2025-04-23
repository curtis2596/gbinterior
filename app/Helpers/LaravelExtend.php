<?php 

namespace App\Helpers;

class LaravelExtend
{
    public static function getRoutes()
    {
        $routeCollections = \Illuminate\Support\Facades\Route::getRoutes();

        $routes = [];
        foreach ($routeCollections as $routeObj) {
            
            // d(get_class_methods($routeObj));
            // d($routeObj->getActionName()); 
            // d($routeObj->getActionMethod()); 
            // d($routeObj->getName()); 
            // d($routeObj->uri); 
            
            // exit;

            $route = [
                'url' => $routeObj->uri,
                'route_name' => $routeObj->getName(),
                "action" => $routeObj->getActionName(),                
            ];

            $routes[] = $route;
        }

        //d($routes); exit;

        return $routes;
    }
}