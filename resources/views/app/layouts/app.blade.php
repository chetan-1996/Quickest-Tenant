<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('app.layouts.partials.head')
</head>
<body>
    <div id="app">
        @include('app.layouts.partials.header')
        @include('app.layouts.partials.nav')

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @include('app.layouts.partials.footer')
    @include('app.layouts.partials.footer-script')
</body>
</html>
