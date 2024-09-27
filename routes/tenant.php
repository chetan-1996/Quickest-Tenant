<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
//use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\App\Auth\LoginController;
use App\Http\Controllers\App\Auth\RegisterController;
use App\Http\Controllers\App\Auth\ForgotPasswordController;
use App\Http\Controllers\App\Auth\ResetPasswordController;
use App\Http\Middleware\CheckStatus;
use App\Http\Middleware\CheckPlanValidity;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// Route::middleware([
//     'web',
//     InitializeTenancyByDomain::class,
//     PreventAccessFromCentralDomains::class,
//     //InitializeTenancyByPath::class,
// ])->group([
//     'prefix' => '/{tenant}', 'middleware' => [InitializeTenancyByPath::class],
// ], function () {
//     Route::get('/', function () {
//         return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
//     });

Route::group([
    'prefix' => '/{tenant}',
    //'name' => 'tenant',
    'middleware' => [
        'web', 
        InitializeTenancyByPath::class,
        //InitializeTenancyByDomain::class,
        //PreventAccessFromCentralDomains::class,
        //InitializeTenancyByRequestData::$header = null,
        //InitializeTenancyByRequestData::$queryParameter = null,
    ],
], function () {
    //dd(\App\Models\User::all());
    // Route::get('/', function () {
    //     return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    // });
    
    Route::controller(\App\Http\Controllers\App\AuthOtpController::class)->group(function () {//dd('{tenant}.');
        Route::get('/login', 'login')->name('tenant.login');
        Route::post('/otp/generate', 'generate')->name('tenant.otp.generate');
        //Route::get('/otp/verification/{user_id}', 'verification')->name('tenant.otp.verification');
        Route::get('/otp/verification/{user_id}/{email}', 'generateVerification')->name('tenant.otp.verification');
        Route::post('/otp/login', 'loginWithOtp')->name('tenant.otp.getlogin');
        Route::post('/logout', 'logout')->name('tenant.logout');
    });

    Route::resource('users', App\Http\Controllers\App\UserController::class)->middleware('auth')->names('tenant.users');
    Route::get('/verify-invite-account/{id}', [App\Http\Controllers\App\UserController::class, 'verify_account'])->name('tenant.verify-invite-account');

    //Route::get('/home', [App\Http\Controllers\App\HomeController::class, 'index'])->name('home');
    Route::middleware(['auth'])->controller(App\Http\Controllers\App\UnitController::class)->name('tenant.unit.')->group(function () {
        Route::get('unit', 'index')->name('index');
        Route::get('unit-show', 'show')->name('show');
        Route::post('store-unit', 'store')->name('store');
        Route::post('edit-unit-status', 'editStatus')->name('edit-status');
        Route::post('delete-unit', 'destroy')->name('delete');
    });

    Route::controller(App\Http\Controllers\App\PlanController::class)->name('tenant.plan.')->group(function () {
        Route::get('plan', 'index')->name('user.index');
        Route::get('/upgrade', 'upgrade')->name('upgrade');
        Route::get('/check-promo-code', 'promo_code')->name('check_promo_code');
    });

    Route::controller(App\Http\Controllers\App\PaymentHistoryController::class)->name('tenant.')->group(function () {
        Route::post('/payment-checkout', 'paymentCheckoutPage')->name('payment.CheckoutPage');
        Route::post('/pay', 'pay')->name('user.pay');
    });

    Route::middleware(['auth', CheckPlanValidity::class])->group(function () {

        Route::post('/upload-csv', [App\Http\Controllers\App\CustomerController::class, 'csvUpload'])->name('tenant.upload');

        Route::controller(App\Http\Controllers\App\ProfileController::class)->name('tenant.')->group(function () {
            Route::get('/user/account', 'userAccount')->name('user-account');
            Route::post('/user/account', 'postUserAccount')->name('user-account');
            Route::post('update-image','update_image')->name('update-image');
            Route::post('/user/reset-password', 'postResetPassword')->name('user-reset-password');
        });
        
        Route::middleware(['auth', 'verified', CheckStatus::class])->group(function () {
            Route::controller(App\Http\Controllers\App\RazorpayPaymentController::class)->name('tenant.')->group(function () {
                Route::get('razorpay-payment', 'index');
                Route::post('razorpay-payment', 'store')->name('razorpay.payment.store');
            });

            Route::controller(App\Http\Controllers\App\DashboardController::class)->name('tenant.')->group(function () {
                Route::get('/dashboard', 'index')->name('dashboard');
                Route::get('/sp-data', 'spData')->name('sp-data');
                Route::get('donut-chart', 'donutChart')->name('event.donutChart');
                Route::get('bar-chart', 'barChart')->name('event.barChart');
                Route::get('sales-performance-chart', 'salesPerformanceChart')->name('chart.salesPerformanceChart');
                Route::post('/dashboard-setting', 'settingStore')->name('dashboard.setting-store');
                Route::get('/get-widget', 'getWidget')->name('dashboard.widget');
                Route::get('/get-lead-stage', 'getLeadStage')->name('dashboard.lead-stage');
                Route::post('/dashboard-setting', 'settingStore')->name('dashboard.setting-store');
                Route::get('/get-open-opr-dashboard', 'getOpenOprDashboard')->name('dashboard.get-open-opr-dashboard');
                Route::get('/get-result-opr-dashboard', 'getResultOprDashboard')->name('dashboard.get-result-opr-dashboard');
                Route::get('/get-periodic-opr-dashboard', 'getPeriodicOprDashboard')->name('dashboard.get-periodic-opr-dashboard');
            });

            Route::controller(App\Http\Controllers\App\FollowUpHistoryController::class)->name('tenant.')->group(function () {
                Route::get('follow-up-history-dashboard', 'dashboardIndex')->name('follow-up-history.dashboard.index');
            });
        });
    });

    Route::controller(\App\Http\Controllers\App\UserController::class)->name('tenant.user.')->group(function () {
        Route::get('userdata', 'getUserdata')->name('userdata');
        Route::get('user-edit-new', 'editNew')->name('user-edit-new');
        Route::post('user-create-new', 'storeNew')->name('user-create-new');
        Route::post('user-resend-mail', 'resentMail')->name('resend-mail');
        Route::post('delete-user', 'destroy')->name('delete');
        Route::get('edit-user-status', 'editStatus')->name('edit-status');
    });

    Route::controller(\App\Http\Controllers\App\LeadGroupController::class)->name('tenant.lead-groups.')->group(function () {
        Route::get('lead-groups', 'index')->name('index');
        Route::get('lead-groups-show', 'show')->name('show');
        Route::post('store-lead-groups', 'store')->name('store');
        Route::post('edit-lead-groups-status', 'editStatus')->name('edit-status');
        Route::post('delete-lead-groups', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\CustomerCategoryController::class)->name('tenant.customer-category.')->group(function () {
        Route::get('customer-category', 'index')->name('index');
        Route::get('customer-category-show', 'show')->name('show');
        Route::post('store-customer-category', 'store')->name('store');
        Route::post('edit-customer-category-status', 'editStatus')->name('edit-status');
        Route::post('delete-customer-category', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\CustomerLeadController::class)->name('tenant.customer-lead.')->group(function () {
        Route::get('lead-source', 'index')->name('index');
        Route::get('lead-source-show', 'show')->name('show');
        Route::post('store-lead-source', 'store')->name('store');
        Route::post('edit-lead-source-status', 'editStatus')->name('edit-status');
        Route::post('delete-lead-source', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\TaxController::class)->name('tenant.tax.')->group(function () {
        Route::get('tax', 'index')->name('index');
        Route::get('tax-show', 'show')->name('show');
        Route::post('store-tax', 'store')->name('store');
        Route::post('edit-tax-status', 'editStatus')->name('edit-status');
        Route::post('delete-tax', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\LostReasonController::class)->name('tenant.lost.reason.')->prefix('lost/')->group(function () {
        Route::get('reason', 'index')->name('index');
        Route::get('reason-show', 'show')->name('show');
        Route::post('store-reason', 'store')->name('store');
        Route::post('edit-reason-status', 'editStatus')->name('edit-status');
        Route::post('delete-reason', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\LeadStageController::class)->name('tenant.lead.stage.')->prefix('lead/')->group(function () {
        Route::get('stage', 'index')->name('index');
        Route::get('stage-show', 'show')->name('show');
        Route::post('store-stage', 'store')->name('store');
        Route::post('edit-stage-status', 'editStatus')->name('edit-status');
        Route::post('delete-stage', 'destroy')->name('delete');
        Route::post('sortable-stage', 'sortableLeadStage')->name('sortable');
    });

    // Route::get('lead', [\App\Http\Controllers\App\CustomerController::class, 'index'])->name('tenant.customer.index');

    Route::controller(\App\Http\Controllers\App\SettingController::class)->name('tenant.settings.')->prefix('settings/')->group(function () { //middleware(['permissionCheck:unit_view'])->
        Route::get('general', 'index')->name('general.index');
        // Route::post('follow-up-notes-flag/update', 'followUpNotesFlagUpdate')->name('follow-up-notes-flag.index');
        // Route::post('indiamart-flag/update', 'indiamartFlagUpdate')->name('indiamart-flag.index');
        // Route::post('facebook-flag/update', 'facebookFlagUpdate')->name('facebook-flag.index');
        // Route::post('tradeindia-flag/update', 'tradeindiaFlagUpdate')->name('tradeindia-flag.index');
        // Route::post('lead-merge-flag/update', 'leadMergeFlagUpdate')->name('lead-merge-flag.index');
    });

    Route::get('/expired-plan', function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $segment = $user->domain;
        $companyExp = \Illuminate\Support\Facades\DB::table('users')->where("id", $company_id)->select(["id", "plan_end_date"])->first();
        return view('app.expired-plan', compact('companyExp', 'segment'));
    });
});
