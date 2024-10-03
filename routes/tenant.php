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

    Route::match(['get', 'post'],'/webhook/facebook', 'FacebookController@handleWebhook')->withoutMiddleware(['csrf'])->name('webhook'); ///{user_id}
    Route::get('auth/facebook', [App\Http\Controllers\App\FacebookAuthController::class,'redirectToProvider'])->name('auth-facebook');
    Route::get('callback/facebook', [App\Http\Controllers\App\FacebookAuthController::class,'handleProviderCallback']);

    //Route::get('/home', [App\Http\Controllers\App\HomeController::class, 'index'])->name('home');
    Route::middleware(['auth'])->controller(App\Http\Controllers\App\UnitController::class)->name('tenant.unit.')->group(function () {
        Route::get('unit', 'index')->name('index');
        Route::get('unit-show', 'show')->name('show');
        Route::post('store-unit', 'store')->name('store');
        Route::post('edit-unit-status', 'editStatus')->name('edit-status');
        Route::post('delete-unit', 'destroy')->name('delete');
    });

    Route::controller(App\Http\Controllers\App\PlanController::class)->name('tenant.plan.')->group(function () {
        Route::get('plan', 'index')->name('planindex');
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
                Route::get('follow-up-history', 'index')->name('follow-up-history.index');
                Route::get('follow-up-history-upcoming', 'upcomingIndex')->name('follow-up-history.upcomingIndex');
                Route::post('follow-up-history-overdue', 'overdueIndex')->name('follow-up-history.overdueIndex');
                Route::get('follow-up-history-someday', 'somedayIndex')->name('follow-up-history.somedayIndex');
                
                Route::post('follow-up-history-never-follow-up', 'neverFollowUpIndex')->name('follow-up-history.neverFollowUpIndex');
            });

            Route::controller(App\Http\Controllers\App\EventController::class)->name('tenant.')->group(function () {
                Route::get('event', 'index')->name('event.index');
                Route::post('store-event', [EventController::class, 'store'])->name('event.store');
                Route::get('follow-up-history-new', 'followUpHistoryIndexNew')->name('event.follow-up-history-new');
            });

            Route::controller(App\Http\Controllers\App\CustomerController::class)->name('tenant.')->group(function () {
                Route::get('leads-export-index', 'leadExportIndex')->name('leads.export-index');
                Route::get('leads-export', 'export')->name('leads.export');

                Route::post('import-lead', 'import_lead')->name('customer.import');
                Route::get('follow-up-history-new', 'getFollowup')->name('lead.get-followup');
                
                Route::post('activity-follow-up-save', 'activityFollowupSave')->name('lead.activity-follow-up-save');
                Route::get('lead', 'index')->name('customer.index');
                Route::post('lead-post', 'customerindex')->name('customer.index-post');
                Route::get('lead-show', 'show')->name('customer.show');
                Route::post('store-lead', 'store')->name('customer.store');
                Route::post('edit-lead-status', 'editStatus')->name('customer.edit-status');
                Route::post('delete-lead', 'destroy')->name('customer.delete');
                Route::post('lead-autocomplete', 'customerAutocomplete')->name('customerAutocomplete');

                Route::post('lead-description', 'updateLeadDescription')->name('lead.lead-description');
                Route::post('lead-stage', 'updateLeadStage')->name('lead.lead-stage');
                Route::post('label-save', 'updateLabelSave')->name('lead.label-save');
                Route::get('lead/timeline/{id}', 'leadTimeline')->name('lead.lead-timeline');

                Route::get('show-customer-timeline', 'showCustomerTimeline')->name('lead.show-customer-timeline');
                Route::post('activity-save', 'activitySave')->name('lead.activity-save');
                Route::post('lead-assigned-to-user', 'LeadAssignedToUser')->name('lead.lead-assigned-to-user');
                Route::post('multiple-lead-assigned-to-user', 'MultipleLeadAssignedToUser')->name('lead.multiple-lead-assigned-to-user');
                Route::post('multiple-lead-stage', 'MultipleLeadStage')->name('lead.multiple-lead-stage');

                Route::get('lead-timeline-activity', 'leadTimelineActivity')->name('lead.lead-timeline-activity');

                Route::get('lead-activity-show', 'activityShow')->name('lead.activity-show');
                Route::post('lead-activity-delete','activityDestroy')->name('lead.lead-activity-delete');
                Route::post('preview-import-lead', 'preview_import_lead')->name('customer.import_preview');
                Route::post('label-save-multiple', 'multipleLabelToCustomers')->name('lead.label-save-multiple');
                Route::post('activity-change-estimate-status-save', 'activityChangeEstimateStatusSave')->name('lead.activity-change-estimate-status-save');
                Route::post('remove-follow-up-date', 'removeFollowUpDate')->name('lead.remove-follow-up-date');
                Route::post('set-someday-follow-up', 'setSomedayFollowUp')->name('lead.set-someday-follow-up');

                Route::get('/get-activity-colunts', 'getActivityColunts')->name('lead.get-activity-colunts');
            });
            
            Route::controller(App\Http\Controllers\App\EstimateController::class)->name('tenant.')->group(function () {
                Route::get('quotes', 'index')->name('quotes.index');
                Route::get('quotes-by-customer', 'getEstimateListByCustomer')->name('quotes.index-by-customer');
                Route::get('quotes/new',  'create')->name('quotes.new');
                Route::post('delete-quotes', 'destroy')->name('quotes.delete');
                Route::post('quotes/new-store', 'store')->name('quotes.new-store');

                Route::get('quotes-get-estimate-number', 'getEstimateNumber')->name('quotes.getEstimateNumber');
                Route::post('quotes/update-estimate-number', 'updateEstimateNumber')->name('quotes.updateEstimateNumber');
                Route::get('estimate-pdf-info', 'estimatePdfInfo')->name('quotes.estimatePdfInfo');
                Route::post('activity-change-estimate-status-saves', 'activityChangeEstimateStatusSaves')->name('lead.activity-change-estimate-status-saves');
                Route::post('estimate-duplicate', 'estimateDuplicate')->name('quotes.estimateDuplicate');
            });

            Route::controller(App\Http\Controllers\App\FolderController::class)->prefix('folder/')->name('tenant.folder.')->group(function () {
                Route::post('store', 'store')->name('index');
                Route::get('get-nested-directories-with-files', 'getNestedDirectoriesWithFiles')->name('get-nested-directories-with-files');
                Route::post('delete', 'destroy')->name('delete');
                Route::post('file-upload', 'fileUpload')->name('file-upload');
                Route::post('folder-file-upload', 'folderFileUpload')->name('folder-file-upload');
                Route::get('download-file', 'downloadFile')->name('download-file');
            });

            Route::controller(App\Http\Controllers\App\ProposalController::class)->name('tenant.')->group(function () {
                Route::get('template/proposal', 'index')->name('proposal.index');
                Route::get('template/proposal/create', 'create')->name('proposal.create');
                Route::get('template/proposal/new-create/{id?}', 'newCreate')->name('proposal.new-create');
                Route::get('template/proposal/pdf-preview', 'pdfPreview')->name('proposal.pdf-preview');
                Route::post('template/proposal/store', 'store')->name('proposal.store');
                Route::post('delete-signature-image', 'deleteSignImage')->name('proposal.delete-signature-image');
                Route::post('delete-aboutus-image', 'deleteAboutusImage')->name('proposal.delete-aboutus-image');
                Route::post('delete-cover-image', 'deleteCoverImage')->name('proposal.delete-cover-image');

                Route::post('crop-cover-image-upload', 'uploadCropCoverImage')->name('croImg.crop-cover-image-upload');
                Route::post('crop-aboutus-image-upload', 'uploadAboutusCoverImage')->name('croImg.crop-aboutus-image-upload');
            });

            Route::controller(App\Http\Controllers\App\ProductController::class)->name('tenant.')->group(function () {
                Route::get('product/add', 'create')->name('product.create');
                Route::get('product', 'index')->name('product.index');
                Route::get('product-show', 'show')->name('product.show');
                Route::post('store-product', 'store')->name('product.store');
                Route::post('edit-product-status', 'editStatus')->name('product.edit-status');
                Route::post('delete-product', 'destroy')->name('product.delete');
                Route::post('product-autocomplete', 'productAutocomplete')->name('productAutocomplete');
                Route::post('estimate-product-store', 'EstimateProductStore')->name('EstimateProductStore');
                Route::get('product/copy/{company_id}/{id?}', 'copytothumbimg')->name('product.copytothumbimg');
            });

            Route::controller(App\Http\Controllers\App\TestimonialController::class)->name('tenant.')->group(function () {
                Route::get('testimonial', 'index')->name('testimonial.index');
                Route::get('testimonial-show', 'show')->name('testimonial.show');
                Route::post('store-testimonial', 'store')->name('testimonial.store');
                Route::post('edit-testimonial-status', 'editStatus')->name('testimonial.edit-status');
                Route::post('delete-testimonial', 'destroy')->name('testimonial.delete');
                Route::post('testimonial-autocomplete', 'testimonialAutocomplete')->name('testimonialAutocomplete');
            });

            Route::controller(App\Http\Controllers\App\ItemController::class)->name('tenant.')->group(function () {
                Route::get('item', 'index')->name('item.index');
                Route::get('item/create', 'create')->name('item.create');
                Route::get('item/edit/{id}', 'edit')->name('item.edit');
                Route::get('item-show', 'show')->name('item.show');
                Route::post('store-item', 'store')->name('item.store');
                Route::post('edit-item-status', 'editStatus')->name('item.edit-status');
                Route::post('delete-item', 'destroy')->name('item.delete');
                Route::post('item-autocomplete', 'itemAutocomplete')->name('itemAutocomplete');
            });

            Route::controller(App\Http\Controllers\App\TermConditionController::class)->name('tenant.term-condition.')->group(function () { 
                //middleware(['permissionCheck:unit_view'])->
                Route::get('term-condition', 'index')->name('index');
                Route::get('term-condition/new', 'create')->name('new');
                Route::get('term-condition/edit/{id}', 'edit')->name('edit');
                Route::get('term-condition-show', 'show')->name('show');
                Route::post('store-term-condition', 'store')->name('store');
                Route::post('edit-term-condition-status', 'editStatus')->name('edit-status');
                Route::post('delete-term-condition', 'destroy')->name('delete');
                Route::get('term-ajax', 'termAjax')->name('termAjax');
            });

            Route::controller(App\Http\Controllers\App\ContentController::class)->name('tenant.content.')->prefix('content/')->group(function () {
                Route::get('messages/', 'messagesIndex')->name('messages.index');
                Route::post('messages/store', 'messagesStore')->name('messages.store');
                Route::get('messages/timeline/{id}', 'messagesTimeline')->name('messages.lead-timeline');
                Route::get('messages/show/', 'messagesShow')->name('messages.show');
                Route::get('messages/timeline-activity', 'messagesTimelineActivity')->name('messages.timeline-activity');
                Route::post('messages/delete', 'messagesDestroy')->name('messages.delete');
    
                Route::get('files/', 'filesIndex')->name('files.index');
                Route::post('files/store', 'filesStore')->name('files.store');
                Route::get('files/timeline/{id}', 'filesTimeline')->name('files.lead-timeline');
                Route::get('files/show/', 'filesShow')->name('files.show');
                Route::get('files/timeline-activity', 'filesTimelineActivity')->name('files.timeline-activity');
                Route::post('files/delete', 'filesDestroy')->name('files.delete');
            });

            Route::controller(App\Http\Controllers\App\SalesPersonPerformanceController::class)->name('tenant.')->group(function () {
                Route::get('report/sales-person-performance', 'index')->name('sales-person-performance.index');

            });

            Route::controller(App\Http\Controllers\App\ReportController::class)->name('tenant.report.')->prefix('report/')->group(function () {
                Route::get('sales-person', 'index')->name('index');
                Route::get('sales-person-pdf', 'GenerateReportPdf')->name('sales-person-pdf');
                Route::get('sales-person-pdf-report', 'salesPersonPdfReport')->name('sales-person-pdf-report');
                Route::post('weekly-mail-notification-flag', 'postWeeklyMailNotificationFlag')->name('weekly-mail-notification-flag');
                Route::post('monthly-mail-notification-flag', 'postMonthlyMailNotificationFlag')->name('monthly-mail-notification-flag');
            });

            Route::controller(App\Http\Controllers\App\IntegrationController::class)->name('tenant.')->group(function () {
                Route::get('integration', 'index')->name('integration.index');
                Route::get('facebook-integration', 'facebook_integration')->name('facebook-integration.index');
                Route::get('indiamart-integration', 'india_mart_lead_verify')->name('integration.india-mart');
                Route::get('tradeindia-integration', 'tradeindia_lead_verify')->name('integration.tradeindia');
                Route::post('whatsapp-integration', 'whatsapp_auth_verify')->name('integration.whatsapp');
                Route::get('indiamart-integration-cron', 'india_mart_lead')->name('integration.india-mart-cron');
                Route::get('get-indiamart-api-token', 'get_india_mart_apitoken')->name('integration.get-indiamart-api-token');
                Route::get('get-tradeindia-api-token', 'get_tradeindia_apitoken')->name('integration.get-tradeindia-api-token');
                Route::get('indiamart-user-list', 'update_users_roundrobin')->name('integration.user-list');
                Route::get('tradeindia-user-list', 'update_users_roundrobin_tradeindia')->name('integration.tradeindia-user-list');
                Route::get('facebook-user-list', 'update_users_roundrobin_facebook')->name('integration.facebook-user-list');
                Route::post('indiamart-update-user-list', 'update_users_roundrobin_status')->name('integration.indiamart-update-user-list');
                Route::post('tradeindia-update-user-list', 'update_users_roundrobin_tradeindia_status')->name('integration.tradeindia-update-user-list');
                Route::post('facebook-update-user-list', 'update_users_roundrobin_facebook_status')->name('integration.facebook-update-user-list');
                Route::post('disconnected-indiamart', 'disconnectedIndiamart')->name('integration.disconnected-indiamart');
                Route::post('disconnected-tradeindia', 'disconnectedTradeindia')->name('integration.disconnected-tradeindia');
                Route::get('integration/facebook-leads-routing', 'facebookLeadsRouting')->name('integration.facebook-leads-routing');
                Route::post('integration/facebook-diconnected', 'facebookDiconnected')->name('integration.facebook-diconnected');
            });
    
        });
    });

    Route::post('get-states-by-country', [App\Http\Controllers\UserController::class, 'getState'])->name('bind-state');
    Route::post('get-cities-by-state', [App\Http\Controllers\UserController::class, 'getCity'])->name('bind-city');

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
        Route::get('customer-lead', 'index')->name('index');
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
