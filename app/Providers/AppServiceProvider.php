<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path('define_constants.php');
        require_once app_path('basic_functions.php');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Paginator::useBootstrap();
        // Schema::defaultStringLength(180);
        if (Schema::hasTable('companies')) {
            $company = Company::first();
            View::share('company', $company);
        }
        if (!App::environment('production')) {
            DB::enableQueryLog();
        }
    }
}
