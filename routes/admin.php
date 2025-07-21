<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['web', 'auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/roles', \App\Livewire\Roles::class);
        Route::get('/permissions', \App\Livewire\Permissions::class);
        Route::get('/users', \App\Livewire\Users::class);
    });