<?php

use App\Http\Controllers\API\V7\{
    AuthController,
    // CompanyCategoryController,
    // CustomerController,
    // EstimateController,
    // ItemController,
    // ProductController,
    // ProposalController,
    // RegionController,
    TermConditionController
};
use App\Http\Controllers\ExpirePlanController;
use App\Mail\ExtendedMail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is';
    });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
//API route for login user
Route::middleware(['tenant'])->group(function(){
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('{tenant}/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/verify-otp-register', [AuthController::class, 'verifyOtpRegister']);
    //Route::get('/business-category', [CompanyCategoryController::class, '__invoke']);

    Route::group(['middleware' => ['auth:sanctum', 'throttle:none']], function () {
        Route::get('/get-subscription-detail', [AuthController::class, 'getSubscriptionDetail']);
        Route::get('/is-check-user-status', [AuthController::class, 'isCheckUserStatus']);
        Route::get('/is-user-verified', [AuthController::class, 'isUserVerified']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::get('/plan-extend', [AuthController::class, 'planExtend']);
        Route::post('/company-profile-update', [AuthController::class, 'companyProfileUpdate']);
        Route::post('/update-device-key', [AuthController::class, 'postUpdateDeviceKey']);
        Route::post('/reset-password', [AuthController::class, 'postResetPassword']);
        Route::get('/proposal-template', [ProposalController::class, '__invoke']);
        Route::get('/single-proposal-template', [ProposalController::class, 'getSingleProposal']);

        Route::get('/header-notification/{assign_user}/{rowperpage}', [\App\Http\Controllers\API\V7\DashboardController::class, 'getHeaderNotification']);
        Route::get('/header-notification-count/{assign_user}', [\App\Http\Controllers\API\V7\DashboardController::class, 'getHeaderNotificationCount']);
        Route::post('/header-notification/update', [\App\Http\Controllers\API\V7\DashboardController::class, 'notificationUpdate']);
        Route::post('/header-notification/update-all', [\App\Http\Controllers\API\DashboardController::class, 'notificationAllUpdate']);

        Route::controller(API\V7\UnitController::class)->prefix('/unit')->group(function () {
            Route::get('/', '__invoke');
            Route::get('/show', 'show');
            Route::post('/store', 'store');
            Route::put('/edit-status', 'editStatus');
            Route::delete('/delete', 'destroy');
        });

        Route::post('/user-resend-mail', [UserController::class, 'resentMail']);

        Route::post('/extended-plan-first', [ExpirePlanController::class, 'extended_plan_first']);
        Route::post('/extended-plan-second', [ExpirePlanController::class, 'extended_plan_second']);

        Route::controller(API\V7\DashboardController::class)->prefix('/dashboard')->group(function () {
            Route::get('index/{date}/{fil_user_id}', '__invoke');
            // Route::get('/bar-chart/{date}/{assign_user}', 'barChart');
            Route::get('/bar-chart/{date}/{fil_user_id}', 'barChart');
            // Route::get('/sales-performance-chart/{date}/{assign_user}', 'salesPerformanceChart');
            Route::get('/sales-performance-chart/{date}/{fil_user_id}', 'salesPerformanceChart');
            Route::get('/calendar-data/{start}/{end}/{assign_user}', 'calendarData');
            Route::get('/get-date-wise-follow-up-list/{date}/{assign_user}', 'getDateWiseFollowUpList');
            Route::post('/create-next-folloup', 'createNextFolloup');
            Route::post('todo/create', 'createTodo');
            Route::post('todo/update', 'updateTodo');
            Route::delete('todo/delete', 'destroyTodo');
            Route::get('/opr-setting', 'getDashboardSetting');
            Route::post('/opr-store-setting', 'storeSetting');
            Route::get('/get-open-opr-dashboard/{fil_user_id}', 'getOpenOprDashboard');
            Route::get('/get-result-opr-dashboard/{fil_user_id}/{date}', 'getResultOprDashboard');
            Route::get('/get-periodic-opr-dashboard/{fil_user_id}/{date}', 'getPeriodicOprDashboard');
            Route::get('/get-lead-stage/{fil_user_id}', 'getLeadStage');
            Route::get('/get-new-open-opr-dashboard/{fil_user_id}', 'getNewOpenOprDashboard');
            Route::get('/get-new-result-opr-dashboard/{fil_user_id}/{date}', 'getNewResultOprDashboard');
            Route::get('/get-new-periodic-opr-dashboard/{fil_user_id}/{date}', 'getNewPeriodicOprDashboard');
        });

        Route::get('/get-country', [RegionController::class, 'getCountry']);
        Route::get('/get-state/{country_id}', [RegionController::class, 'getState']);
        Route::get('/get-city/{state_id}', [RegionController::class, 'getCity']);

        Route::get('/get-customer/{search?}', [CustomerController::class, 'customerAutocomplete']);
        Route::get('/get-single-customer/{id?}', [CustomerController::class, 'getSingleCustomer']);
        Route::post('/customer-store', [CustomerController::class, 'customerStore']);

        Route::get('/get-customer-category/{search?}', [CustomerController::class, 'customerCategoriesAutocomplete']);
        Route::get('/get-customer-lead/{search?}', [CustomerController::class, 'customerLeadsAutocomplete']);

        Route::get('/get-item/{search?}', [ItemController::class, 'itemAutocomplete']);
        Route::get('/get-item-single/{id}', [ItemController::class, 'itemAutocompleteSingle']);
        Route::post('/item-store', [ItemController::class, 'itemStore']);

        Route::get('/get-testimonial/{search?}', [EstimateController::class, 'testimonialAutocomplete']);
        Route::post('/testimonial-store', [EstimateController::class, 'testimonialStore']);

        Route::get('/get-product/{search?}', [EstimateController::class, 'productAutocomplete']);
        Route::post('/product-store', [EstimateController::class, 'productStore']);

        Route::get('/estimate-search/{search?}', [EstimateController::class, 'getEstimateSearch']);
        Route::get('/get-estimate/{fil_user_id}/{status}/{date}/{start}/{rowperpage}/{orderBy}/{search?}', [EstimateController::class, 'getEstimateList']);
        Route::get('/get-Followup-by-estimate-id/{id}', [EstimateController::class, 'getFollowUpByEstimateId']);
        Route::get('/generate-link/{id}', [EstimateController::class, 'getGenerateLink']);
        Route::get('/get-aws-generate-link/{company_id}/{pdf_name}', [EstimateController::class, 'getAwsGenerateLink']);
        Route::post('estimate-duplicate', [EstimateController::class, 'postEstimateDuplicate']);
        Route::post('estimate/create', [EstimateController::class, 'postEstimateCreate']);
        Route::post('estimate/update', [EstimateController::class, 'postEstimateUpdate']);
        Route::delete('estimate/delete', [EstimateController::class, 'postEstimateDelete']);
        Route::get('estimate/number', [EstimateController::class, 'getEstimateNumber']);
        Route::get('estimate/salesman', [EstimateController::class, 'getSalesman']);
        Route::get('estimate/edit/{id}', [EstimateController::class, 'getEstimateSingle']);
        Route::post('/user/account', [AuthController::class, 'postUserAccount']);
        Route::post('/user/profile-image', [AuthController::class, 'postUserProfile']);

        Route::get('/get-tax/{search?}', [EstimateController::class, 'getTaxAutocomplete']);
        Route::get('/get-term-condition/{search?}', [TermConditionController::class, 'termConditionAutocomplete']);
        Route::post('/user/delete', [AuthController::class, 'userDelete']);
        Route::post('/user/status', [AuthController::class, 'userStatus']);
        Route::delete('/team/delete', [AuthController::class, 'destroy']);
        Route::post('/user/send-otp-delete', [AuthController::class, 'sendOtpDelete']);
        Route::post('/user/verify-otp-delete', [AuthController::class, 'verifyOtpDelete']);
        Route::post('/user/create', [AuthController::class, 'storeNew']);
        Route::get('/user/show/{id}', [AuthController::class, 'editNew']);
        Route::post('/user/resend', [AuthController::class, 'resentMail']);
        Route::get('/user/list/{status}', [AuthController::class, 'userList']);

        Route::controller(API\V7\LeadController::class)->prefix('/lead')->group(function () {
            Route::get('/index/{start}/{rowperpage}/{orderBy}/{fil_user_id}', 'index');
            // Route::get('/', 'index');
            Route::post('/store', 'store');
            Route::get('/duplicate/{id}/{phone_no}', 'duplicateLead');
            Route::get('/get-lead/{id}', 'getLead');
            Route::delete('/delete', 'destroy');
            Route::get('/assign-user-list', 'assignUserList');
            Route::get('/team-user-list', 'teamUserList');
            Route::post('/lead-assigned-to-user', 'LeadAssignedToUser');
            Route::get('/lead-timeline-activity/{id}', 'leadTimelineActivity');
            Route::get('/get-lead-activity/{id}', 'activityShow');
            Route::get('/tms', 'userWithCategory');
            Route::delete('/activity/delete', 'activityDestroy');
            Route::post('/activity/store', 'activityStore');
            Route::post('/activity/followup/store', 'activityFollowupSave');
            Route::post('/activity/multiple/followup/store', 'activityMultipleFollowupSave');
            Route::post('/activity/estimate-status/save', 'activityChangeEstimateStatusSave');
            Route::post('/update-label', 'updateLabel');
            Route::post('/update-lead-description', 'updateLeadDescription');
            Route::post('/remove-follow-up-date', 'removeFollowUpDate');
            Route::post('/remove-multiple-follow-up-date', 'removeMultipleFollowUpDate');
            Route::post('/set-someday-follow-up', 'setSomedayFollowUp');
            Route::get('/follow-up-history/{start}/{rowperpage}', 'getFollowup');
            Route::get('/today-follow-up/{fil_user_id}', 'getTodayFollowup');
            Route::get('/get-lead-by-user/{user_id}/{start}/{rowperpage}/{orderBy}', 'getLeadByUser');
            Route::get('/get-upcoming-follow-up/{start}/{rowperpage}/{fil_user_id}', 'getUpcomingFollowup');
            Route::get('/get-overdue-follow-up/{start}/{rowperpage}/{fil_user_id}', 'getOverdueFollowup');
            Route::get('/get-someday-follow-up/{start}/{rowperpage}/{fil_user_id}', 'getSomedayFollowup');
            Route::get('/get-never-follow-up/{start}/{rowperpage}/{fil_user_id}', 'getNeverFollowup');
            Route::get('/lead-search/{fil_user_id}/{search?}', 'leadSearch');
            Route::get('/get-lead-by-label/{label_id}/{start}/{rowperpage}/{orderBy}/{fil_user_id}', 'getLeadByLabel');
            Route::get('/get-new-lead/{label_id}/{start}/{rowperpage}/{orderBy}/{fil_user_id}', 'getNewLead');
            Route::get('/get-new-lead-count/{label_id}/{fil_user_id}', 'getNewLeadCount');
            Route::post('/mark-as-unread-lead', 'markAsUnreadLead');
            Route::post('/multiple-lead-assigned-to-user', 'MultipleLeadAssignedToUser');
            Route::post('/multiple-label-to-customers', 'multipleLabelToCustomers');
            Route::get('/get-lead-stage', 'getLeadStages');
            Route::get('/get-lost-reason', 'getLostReasons');
            Route::post('/update-lead-stage', 'updateLeadStage');
            Route::post('/update-multiple-lead-stage', 'updateMultipleLeadStage');
            Route::post('/exclusive', 'postLeadExclusive');
            Route::get('/get-exclusive/{user_id}/{search?}', 'getLeadExclusive');
            Route::get('/get-exclusive-search/{user_id}/{company_id}/{search?}', 'getLeadExclusiveSearch');
            Route::delete('/remove-exclusive', 'removeLeadExclusive');
            Route::get('/get-lead-by-label-count/{label_id}/{fil_user_id}', 'getLeadByLabelCount');
            Route::get('/get-lead-by-user-count/{user_id}', 'getLeadByUserCount');
            Route::post('/get-opr-overdue-follow-up/{start}/{rowperpage}/{fil_user_id}', 'getOprOverdueFollowup');
            Route::post('/get-opr-never-follow-up/{start}/{rowperpage}/{fil_user_id}', 'getOprNeverFollowup');
            Route::post('/get-opr-lead-index/{start}/{rowperpage}/{fil_user_id}', 'getOprLeadIndex');
            /*Route::get('/show', 'show');

            Route::put('/edit-status', 'editStatus');
        */
        });

        Route::controller(API\V7\FollowUpHistoryCountController::class)->prefix('/lead/count')->group(function () {
            Route::get('/today-follow-up/{fil_user_id}', 'getTodayFollowupCount');
            Route::get('/upcoming-follow-up/{fil_user_id}', 'getUpcomingFollowupCount');
            Route::get('/overdue-follow-up/{fil_user_id}', 'getOverdueFollowupCount');
            Route::get('/someday-follow-up/{fil_user_id}', 'getSomedayFollowupCount');
            Route::get('/never-follow-up/{fil_user_id}', 'getNeverFollowupCount');
        });

        Route::controller(API\V7\LeadLabelController::class)->prefix('/lead/label')->group(function () { //middleware(['permissionCheck:unit_view'])->
            // Route::get('/', 'index');
            Route::get('/{fil_user_id}', 'index');
            Route::post('/store', 'store');
            Route::get('/show/{id}', 'show');
            Route::delete('/delete', 'destroy');
        });

        Route::controller(API\V7\ContentController::class)->prefix('/content')->group(function () {
            Route::get('/messages', 'messagesIndex');
            Route::get('/files', 'filesIndex');

            Route::get('/messages/{id}', 'messagesSingleIndex');
            Route::get('/files/{id}', 'filesSingleIndex');

            Route::post('/messages/store', 'messagesStore');
            Route::delete('/messages/delete', 'messagesDestroy');
            Route::delete('/files/delete', 'filesDestroy');
            Route::post('/files/store', 'filesStore');
            Route::post('/messages/share', 'messagesShare');
            Route::post('/files/share', 'filesShare');
        });

        Route::controller(API\V7\SettingController::class)->prefix('/settings')->group(function () {
            Route::post('/follow-up-notes-flag/update', 'followUpNotesFlagUpdate');
        });

        Route::get('/item/index', [ItemController::class, 'index']);
        Route::delete('/item/delete', [ItemController::class, 'destroy']);
        Route::post('/item/status', [ItemController::class, 'editStatus']);
        Route::get('/item/show/{id}', [ItemController::class, 'show']);
        Route::post('/item/store', [ItemController::class, 'store']);

        Route::get('/product/index', [ProductController::class, 'index']);
        Route::delete('/product/delete', [ProductController::class, 'destroy']);
        Route::post('/product/status', [ProductController::class, 'editStatus']);
        Route::get('/product/show/{id}', [ProductController::class, 'show']);
        Route::post('/product/store', [ProductController::class, 'store']);

        Route::controller(API\V7\FolderController::class)->prefix('/folder')->group(function () {
            Route::post('/store', 'store');
            Route::get('/get-nested-directories-with-files/{lead_id}/{attachment_id?}', 'getNestedDirectoriesWithFiles');
            Route::delete('/delete', 'destroy');
            Route::post('/file-upload', 'fileUpload');
            Route::post('/folder-file-upload', 'folderFileUpload');
        });
        //, 'throttle:540,1'
        // API route for logout user
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});