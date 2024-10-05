<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TenantController;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\admin\ClientController;
use App\Http\Controllers\admin\PlansController;
use App\Http\Controllers\admin\CompanyCategoryController;
use App\Http\Controllers\admin\PlanHistoryController;
use App\Http\Controllers\admin\PromoCodeController;
use App\Http\Controllers\admin\PaymentHistoryController;
use App\Http\Controllers\admin\PermissionController;

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
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/get-widget', [AdminDashboardController::class, 'getWidget'])->name('dashboard.widget');
    Route::post('/logout', [AdminDashboardController::class, 'loggedOut'])->name('logout');
    Route::get('/bar-chart', [AdminDashboardController::class, 'barChart'])->name('barChart');
    Route::get('/lead-bar-chart', [AdminDashboardController::class, 'leadBarChart'])->name('leadBarChart');

    Route::get('/client', [ClientController::class, 'index'])->name('client.index');
    Route::post('/edit-client-status', [ClientController::class, 'editStatus'])->name('client.edit-status');
    Route::post('/delete-client', [ClientController::class, 'destroy'])->name('client.delete');
    Route::get('/show-client', [ClientController::class, 'show'])->name('client.show');
    Route::post('/client-plan-update', [ClientController::class, 'update'])->name('client.plan.update');
    Route::get('/storage-show', [ClientController::class, 'storageShow'])->name('client.storage-show');
    Route::post('/client-storage-update', [ClientController::class, 'storageUpdate'])->name('client.storage.update');

    Route::get('/business-category', [CompanyCategoryController::class, 'index'])->name('business-category.index');
    Route::get('/business-category-show', [CompanyCategoryController::class, 'show'])->name('business-category.show');
    Route::post('/store-business-category', [CompanyCategoryController::class, 'store'])->name('business-category.store');
    Route::post('/edit-business-category-status', [CompanyCategoryController::class, 'editStatus'])->name('business-category.edit-status');
    Route::post('/delete-business-category', [CompanyCategoryController::class, 'destroy'])->name('business-category.delete');

    Route::get('/plans', [PlansController::class, 'index'])->name('plans.index');
    Route::get('/plans-show', [PlansController::class, 'show'])->name('plans.show');
    Route::post('/store-plans', [PlansController::class, 'store'])->name('plans.store');
    Route::post('/edit-plans-status', [PlansController::class, 'editStatus'])->name('plans.edit-status');
    Route::post('/delete-plans', [PlansController::class, 'destroy'])->name('plans.delete');

    Route::get('/plan-history', [PlanHistoryController::class, 'index'])->name('plan_history.index');
    Route::get('/plan-history-show', [PlanHistoryController::class, 'show'])->name('plan_history.show');
    Route::post('/store-plan-history', [PlanHistoryController::class, 'store'])->name('plan_history.store');
    Route::post('/edit-plan-history-status', [PlanHistoryController::class, 'editStatus'])->name('plan_history.edit-status');
    Route::post('/delete-plan-history', [PlanHistoryController::class, 'destroy'])->name('plan_history.delete');

    Route::get('/promo-code', [PromoCodeController::class, 'index'])->name('promo-code.index');
    Route::get('/promo-code-show', [PromoCodeController::class, 'show'])->name('promo-code.show');
    Route::post('/store-promo-code', [PromoCodeController::class, 'store'])->name('promo-code.store');
    Route::post('/edit-promo-code-status', [PromoCodeController::class, 'editStatus'])->name('promo-code.edit-status');
    Route::post('/delete-promo-code', [PromoCodeController::class, 'destroy'])->name('promo-code.delete');

    Route::get('/payment-history', [PaymentHistoryController::class, 'index'])->name('payment_history.index');
    Route::get('/payment-history-show', [PaymentHistoryController::class, 'show'])->name('payment_history.show');
    Route::post('/store-payment-history', [PaymentHistoryController::class, 'store'])->name('payment_history.store');
    Route::post('/edit-payment-history-status', [PaymentHistoryController::class, 'editStatus'])->name('payment_history.edit-status');
    Route::post('/delete-payment-history', [PaymentHistoryController::class, 'destroy'])->name('payment_history.delete');

    Route::get('/permission', [PermissionController::class, 'index'])->name('permission.index');
    Route::get('/permission-show', [PermissionController::class, 'show'])->name('permission.show');
    Route::post('/store-permission', [PermissionController::class, 'store'])->name('permission.store');
    Route::post('/delete-permission', [PermissionController::class, 'destroy'])->name('permission.delete');

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
