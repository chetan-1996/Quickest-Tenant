@php
    $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', $uri_path);
    $tmp = $uri_segments[1] == 'admin' ? 'admin' : 'web';
    $tmpNav = $uri_segments[1] == 'admin' ? 'layouts.partials.admin-nav' : 'layouts.partials.nav';
@endphp

@auth
@php
    $user = Auth::user();
    $id = isset($user->company_id) ? $user->company_id : $user->id;
    $month = Carbon\Carbon::now()->format('m');
    $estimateCount = App\Models\Estimate::where('company_id', $id)
        ->whereMonth('created_at', Carbon\Carbon::now()->month)
        ->count();
    $plan = App\Models\PlanHistory::where([['user_id', $id], ['status', 1]])->first();

    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');//echo '<pre>';print_r($user_perm);exit;

    $expData = App\Helpers\PermissionCheck::plan_details_check();


    $remaining_days = \Carbon\Carbon::parse($expData->plan_start_date)->diffInDays();
    $totalExp = \Carbon\Carbon::parse($expData->plan_start_date)->diffInDays(Carbon\Carbon::parse($expData->plan_end_date));
    $progress = 0;
    if ($totalExp > 0) {
        $progress = number_format(($remaining_days / $totalExp) * 100, 0);
    }
    $company = App\Models\User::where('id', $id)->first();
    $activePlan = App\Models\admin\Plans::where('id', $company->plan_id)->first();
@endphp
@endauth
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .side-nav .side-nav-link{
            padding:7px 12px !important;
        }
        .page-title-box .page-title-right {

            margin-top: 10px !important;
        }
    </style>
    @include('app.layouts.partials.head')
</head>
<body>
    <div id="app">
        @include('app.layouts.partials.header')
        @include('app.layouts.partials.nav')

        <main class="py-0">
            @yield('content')
        </main>
    </div>
    @include('app.layouts.partials.footer')
    @include('app.layouts.partials.footer-script')
</body>
</html>
