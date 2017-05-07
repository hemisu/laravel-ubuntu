<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    Route::get('api/stock', 'StockController@apiStock');
    $router->resource('stock',StockController::class);
    $router->resource('salerecord',SalesRecordController::class);
});
