<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});




Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/products', \App\Livewire\Products::class);
Route::get('/roles', \App\Livewire\Roles::class);

Route::get('/profiles', \App\Livewire\Profiles::class);
Route::get('/users', \App\Livewire\Users::class);
