<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Incluir rutas de autenticación
require __DIR__.'/auth.php';

// Incluir rutas de administrador
require __DIR__.'/admin.php';

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::middleware(['web', 'auth'])
    ->prefix('odoo_masters')
    ->group(function () {
        Route::get('/products', \App\Livewire\Products::class);
        Route::get('/customers', \App\Livewire\Customers::class);
    });


