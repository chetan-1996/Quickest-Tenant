<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::resource('tenants', App\Http\Controllers\TenantController::class);

Route::get('/clear-cache-all', function() {

    Artisan::call('optimize:clear');


    dd("Cache Clear All");
});
