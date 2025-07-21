<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Apis\OdooAPIController;
use App\Http\Controllers\OdooReadController;
use App\Http\Controllers\OdooSyncController;

Route::middleware(['web', 'auth'])
    ->name('api.')
    ->group(function () {
        /*
        Route::get('/odoo_start', [OdooAPIController::class, 'start']);
        Route::get('/odoo_load_products', [OdooAPIController::class, 'loadProducts']);
        Route::get('/odoo_load_categories', [OdooAPIController::class, 'loadCategories']);
        Route::get('/odoo_sync_products', [OdooAPIController::class, 'syncOdooProducts']);
        */
        Route::get('/odoo/customers', [OdooReadController::class, 'readCustomers']);
        Route::get('/odoo/products', [OdooReadController::class, 'readProducts']);
        Route::get('/odoo/sync/products', [OdooSyncController::class, 'syncProducts']);
        Route::get('/odoo/sync/customers', [OdooSyncController::class, 'syncCustomers']);        
    });


//Route::get('/odoo/categories', [OdooReadController::class, 'categories']);
//Route::get('/odoo/customers', [OdooReadController::class, 'customers']);

