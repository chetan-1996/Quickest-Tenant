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
