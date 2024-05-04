<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <div id="app">
        @include('layouts.partials.header')
{{--        @include('layouts.partials.nav')--}}

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @include('layouts.partials.footer')
    @include('layouts.partials.footer-script')
</body>
</html>
