<?php

use App\Http\Controllers\Backend\AllowanceTypeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Backend\AutoIncreamentController;
use App\Http\Controllers\Backend\BrandsController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\CityController;
use App\Http\Controllers\Backend\CompanyController;
use App\Http\Controllers\Backend\ComplaintTypeController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\DesignationController;
use App\Http\Controllers\Backend\DeveloperController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\InHouseManufacturingController;
use App\Http\Controllers\Backend\ItemCategoryController;
use App\Http\Controllers\Backend\ItemController;
use App\Http\Controllers\Backend\ItemGroupsController;
use App\Http\Controllers\Backend\JobOrderController;
use App\Http\Controllers\Backend\JobOrderReceiveController;
use App\Http\Controllers\Backend\JobWorkManufacturingController;
use App\Http\Controllers\Backend\LeadController;
use App\Http\Controllers\Backend\LedgerAccountController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\ProcessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\PublicController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\StateController;
use App\Http\Controllers\Backend\TypeController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Backend\TransportController;
use App\Http\Controllers\Backend\PurposeController;
use App\Http\Controllers\Backend\SourceController;
use App\Http\Controllers\Backend\PartyController;
use App\Http\Controllers\Backend\LedgerCategoryController;
use App\Http\Controllers\Backend\MailController;
use App\Http\Controllers\Backend\NewComplaintController;
use App\Http\Controllers\Backend\NewQuotationController;
use App\Http\Controllers\Backend\PartyMovementController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\PurchaseBillController;
use App\Http\Controllers\Backend\PurchaseBillItemMovementController;
use App\Http\Controllers\Backend\PurchaseOrderController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\SaleBillController;
use App\Http\Controllers\Backend\SaleBillItemMovementController;
use App\Http\Controllers\Backend\SaleOrderController;
use App\Http\Controllers\Backend\StaffTypeController;
use App\Http\Controllers\Backend\WarehouseController;
use App\Http\Controllers\Backend\WarehouseMovementController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use App\Models\City;


Route::get('/', function () {
    return redirect()->route("home");
});

Route::get('/phpinfo', function () {
    phpinfo();
});

Auth::routes();

Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('/theme', [HomeController::class, 'theme']);
Route::get('/developer-components', [HomeController::class, 'developer_components']);
Route::get('/test', [HomeController::class, 'test']);



Route::group(['middleware' => ['auth']], function () {
    
    $name = "dashboard";
    Route::get($name, [DashboardController::class, 'index'])->name($name);
    
    Route::get('dashboard-ajax_admin_role_counters/{date_type}', [DashboardController::class, "ajax_admin_role_counters"])->name('dashboard.ajax_admin_role_counters');
    
    
    $route_prefix = "user";
    $controllerClass = UserController::class;
    Route::resource($route_prefix, $controllerClass)->except("show");
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {
        
        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
        
        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
        
        Route::get("my-profile", [$controllerClass, 'my_profile'])->name("my.profile");
        
        Route::any("change-password", [$controllerClass, 'change_password'])->name("change.password");
    });
    
    $route_prefix = "role";
    $controllerClass = RoleController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {

        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });
    
    // state
    // Route::resource('states', Controllers\StateController::class);
    
    $route_prefix = "state";
    $controllerClass = StateController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {});
    
    // City
    // $route_prefix = "city";
    // $controllerClass = CityController::class;
    // Route::resource($route_prefix, $controllerClass);
    // Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {});
    
    // cities
    $controller_prefix = "cities";
    $controllerClass = CityController::class;
    Route::resource($controller_prefix, $controllerClass)->except(['show']);
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_list";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    Route::get('cities/options', function () {
        $cities = App\Models\City::where('state_id', request()->state_id)->pluck('name', 'id');
        return view('cities.options', compact('cities'));
    })->name('cities.options');
    
    // unit

    $controller_prefix = "units";
    $controllerClass = UnitController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Brands
    
    $controller_prefix = "brands";
    $controllerClass = BrandsController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Process
    $controller_prefix = "processes";   
    $controllerClass = ProcessController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // ItemGroup
    $controller_prefix = "item-groups";
    $controllerClass = ItemGroupsController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Item Category
    
    $controller_prefix = "item-categories";
    $controllerClass = ItemCategoryController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Items
    $controller_prefix = "items";
    $controllerClass = ItemController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "set_warehouse_opening_qty";
    Route::any($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    
    // Transport
    
    $controller_prefix = "transports";
    $controllerClass = TransportController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Purpose
    
    $controller_prefix = "purposes";
    $controllerClass = PurposeController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Source
    
    $controller_prefix = "sources";
    $controllerClass = SourceController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Party Category
    
    $controller_prefix = "categories";
    $controllerClass = CategoryController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Party
    $controller_prefix = "party";
    $controllerClass = PartyController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Warehouse
    $controller_prefix = "warehouses";
    $controllerClass = WarehouseController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    
    // Department
    $route_prefix = "department";
    $controllerClass = DepartmentController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {
        
        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
        
        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });
    
    // Designation
    $route_prefix = "designation";
    $controllerClass = DesignationController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {
        
        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
        
        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });
    
    // Type
    $route_prefix = "type";
    $controllerClass = TypeController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {
        
        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
        
        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });
    
    // Employee
    $route_prefix = "employee";
    $controllerClass = EmployeeController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {
        
        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });
    
    // Complaint Type
    
    $controller_prefix = "complaint-type";
    $controllerClass = ComplaintTypeController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Staff Type
    
    $controller_prefix = "staff-type";
    $controllerClass = StaffTypeController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Allowance Type
    
    $controller_prefix = "allowance-type";
    $controllerClass = AllowanceTypeController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    Route::group(['prefix' => 'permissions', 'as' => 'permissions.'], function () {
        
        $name = "index";
        Route::get($name, [PermissionController::class, $name])->name($name);
        
        $name = "assign";
        Route::any($name, [PermissionController::class, $name])->name($name);
        
        $name = "assign_to_many";
        Route::any($name, [PermissionController::class, $name])->name($name);
        
        $name = "ajax_get_permissions";
        Route::get("$name/{id}", [PermissionController::class, $name])->name($name);
        
        $name = "ajax_delete";
        Route::post($name, [PermissionController::class, $name])->name($name);
        
        $name = "ajax_request_access";
        Route::any($name . "/{route_name}", [PermissionController::class, $name])->name($name);
    });
    
    Route::group(['prefix' => 'logs', 'as' => 'logs.'], function () {
        $name = "email";
        Route::get($name, [DeveloperController::class, $name])->name($name);
    });
    
    Route::group(['prefix' => "developer", 'as' => "developer."], function () {
        
        $name = "sql_log";
        Route::get($name, [DeveloperController::class, $name])->name($name);
        
        $name = "laravel_routes_index";
        Route::get($name, [DeveloperController::class, $name])->name($name);
    });
    
    $controller_prefix = "settings";
    $controllerClass = SettingController::class;
    $name = "general";
    Route::any($controller_prefix . "/" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    
    // Ledger Category]
    $controller_prefix = "ledger-category";
    $controllerClass = LedgerCategoryController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // Lead 
    
    Route::resource('leads', LeadController::class);
    
    Route::get('/phpinfo', function () {
        phpinfo();
    });
    
    
    // Complaint
    
    Route::resource('new-complaint', NewComplaintController::class);
    Route::get('/get-customer-details/{id}', [NewComplaintController::class, 'getCustomerDetails']);
    
    
    // Quotation
    
    Route::resource('quotation', NewQuotationController::class);
    Route::delete('/quotations/attachments/{id?}', [NewQuotationController::class, 'deleteAttachment'])->name('quotations.attachment.delete');
    Route::post('/send-email', [MailController::class, 'sendEmail'])->name('send.email');
    
    Route::resource('companies', CompanyController::class)->only(['index', 'update']);
    // AutoIncrement
    
    $controller_prefix = "auto-increaments";
    $controllerClass = AutoIncreamentController::class;
    Route::resource($controller_prefix, $controllerClass);
    
    $controller_prefix = "purchase-orders";
    $controllerClass = PurchaseOrderController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "print";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_list";
    Route::get($controller_prefix . "-" . $name . "/{id}/{ignore_ids?}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_already_order_qty";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{item_id}/{id?}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    
    $controller_prefix = "purchase-bills";
    $controllerClass = PurchaseBillController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "create_with_po";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pdf";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_items";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{purchase_order_ids}/{id?}/", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "return_items";
    Route::any($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "purchase-bill-item-movement";
    $controllerClass = PurchaseBillItemMovementController::class;
    $name = "index";
    Route::get($controller_prefix . "/" . $name . "/{purchase_bill_id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "store";
    Route::post($controller_prefix . "/" . $name . "/{purchase_bill_id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "destroy";
    Route::delete($controller_prefix . "/" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_pending_qty";
    Route::get($controller_prefix . "-" . $name . "/{purchase_bill_item_id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "sale-orders";
    $controllerClass = SaleOrderController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "print";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_list";
    Route::get($controller_prefix . "-" . $name . "/{id}/{ignore_ids?}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_already_order_qty";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{item_id}/{id?}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "sale-bills";
    $controllerClass = SaleBillController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "create_with_so";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pdf";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "print";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_items";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{purchase_order_ids}/{id?}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "return_items";
    Route::any($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "sale-bill-item-movement";
    $controllerClass = SaleBillItemMovementController::class;
    $name = "index";
    Route::get($controller_prefix . "/" . $name . "/{sale_bill_id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "store";
    Route::post($controller_prefix . "/" . $name . "/{sale_bill_id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "destroy";
    Route::delete($controller_prefix . "/" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_pending_qty";
    Route::get($controller_prefix . "-" . $name . "/{sale_bill_item_id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "ledger-accounts";
    $controllerClass = LedgerAccountController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "set-opening-balance";
    Route::any($controller_prefix . "-" . $name, [$controllerClass, "set_opening_balance"])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "ledger-payments";
    $controllerClass = PaymentController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pay_for_purchase";
    Route::any($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_pending_payable_amount";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "receive_for_sale";
    Route::any($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_pending_receiveable_amount";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pay_for_job_work";
    Route::any($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    $controller_prefix = "reports";
    $controllerClass = ReportController::class;
    $name = "ledger";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    Route::get('reports-inventory', [ReportController::class, 'current_stock'])->name('reports.inventory');
    
    // Warehouse Movement
    Route::resource('warehouse-movements', WarehouseMovementController::class);
    
    Route::get('/warehouse-stock/available', [WarehouseMovementController::class, 'getAvailableStock'])->name('warehouse.stock.available');
    
    Route::resource('party-movements', PartyMovementController::class);
    
    // Inhouse Manufacturing
    Route::resource('in-house-manufacturing', InHouseManufacturingController::class);
    // Route::get('/warehouse-stock/available', [InHouseManufacturingController::class, 'getAvailableStock'])->name('warehouse.stock.available');

    Route::resource('job-work-manufacturing', JobWorkManufacturingController::class);

    // Job Order
    
    Route::resource('job-orders', JobOrderController::class);
    Route::get("job-orders.print/{id}", [JobOrderController::class, 'print'])->name("job-orders.print");
    
    
    // Job Order Receive
    
    Route::resource('job-orders-receive', JobOrderReceiveController::class)->except("show");
    Route::get('/job-orders-ajax_get/{party_id}', [JobOrderReceiveController::class, 'getJobOrders']);
    // Route::get('/get-job-orders/{id}', [JobOrderReceiveController::class, 'getJobOrders']);
    Route::get('/job-orders-ajax_get_list/{id}', [JobOrderReceiveController::class, 'getJobOrderItems']);
    Route::get('job-orders-receive/csv', [JobOrderReceiveController::class, 'csv'])->name('job-orders-receive.csv');
});
Route::get('verify-otp/{email}', [UserController::class, 'otp_verified_view'])->name('verify.otp')->middleware('auth');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// show cities based on selected state //
Route::get('/cities/{stateId}', function ($stateId) {
    
    $cities = City::where('state_id', $stateId)->pluck('name', 'id');
    
    return response()->json(['cities' => $cities]);
});
// // show cities based on selected state //

// // show cities based on Saved Records in edit case //
Route::get('/cityname/{cityId}', function ($cityId) {
    
    $cities = City::where('id', $cityId)->pluck('name', 'id');
    
    return response()->json(['cities' => $cities]);
});
// show cities based on Saved Records in edit case //
Route::group(['prefix' => 'public', 'as' => 'public.'], function () {
    Route::post('ajax_upload', [PublicController::class, 'ajax_upload']);
    Route::post('ajax_upload_base64', [PublicController::class, 'ajax_upload_base64']);
});