<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TenantController;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [App\Http\Controllers\LoginController::class, 'login'])->name('login');
Route::post('otp/generate', [App\Http\Controllers\LoginController::class, 'generate'])->name('otp.generate');
Route::get('/otp/verification/{user_id}', [App\Http\Controllers\LoginController::class, 'verification'])->name('otp.verification');
Route::post('/otp/login', [App\Http\Controllers\LoginController::class,'loginWithOtp'])->name('otp.getlogin');
Route::post('/logout', [App\Http\Controllers\LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\RegisterController::class, 'register'])->name('register');
Route::post('/register-tenants', [App\Http\Controllers\RegisterController::class, 'store'])->name('register.tenants');
Route::post('get-states-by-country', [App\Http\Controllers\RegisterController::class, 'getState'])->name('bind-state');
Route::post('get-cities-by-state', [App\Http\Controllers\RegisterController::class, 'getCity'])->name('bind-city');
Route::get('/rotp/verification/{user_id}', [App\Http\Controllers\RegisterController::class, 'verification'])->name('rotp.verification');

Route::prefix('admin/')->name('admin.')->group(function () {
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
});


Route::get('/clear-cache-all', function() {

    Artisan::call('optimize:clear');


    dd("Cache Clear All");
});
