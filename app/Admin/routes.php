<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();
Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('auth/login', 'AuthController@getLogin');//login
    $router->post('auth/login', 'AuthController@postLogin');//login
    Route::get('api/stock', 'StockController@apiStock');
    Route::get('api/client', 'ClientController@apiClient');
    Route::get('client/{id}/salesrecord', 'ClientController@clientSalesRecord');
    $router->resource('stock',StockController::class);
    $router->resource('salerecord',SalesRecordController::class);
    $router->resource('client',ClientController::class);
    $router->resource('staff',StaffController::class);
});
