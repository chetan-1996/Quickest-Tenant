<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TenantController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//Route::resource('tenants', App\Http\Controllers\TenantController::class)->middleware('auth');
// Middleware group for routes
Route::middleware('web')->group(function () {
    Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
});
Route::middleware('auth')->group(function () {
    // Index
    Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');

    // Create
//    Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');

    // Store
//    Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');

    // Show
    Route::get('tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');

    // Edit
    Route::get('tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');

    // Update
    Route::put('tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');

    // Destroy
    Route::delete('tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
});

Route::get('/clear-cache-all', function() {

    Artisan::call('optimize:clear');


    dd("Cache Clear All");
});
