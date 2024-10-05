<link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico')}}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Theme Config Js -->
<script src="{{asset('assets/js/hyper-config.js')}}"></script>
<link href="{{asset('assets/vendor/daterangepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css">
<!-- App css -->
<link href="{{asset('assets/css/app-saas.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
{{--<link href="https://themesbrand.com/velzon/html/material/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="app-style" />--}}

<!-- Icons css -->
<link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/custom.css')}}" rel="stylesheet" type="text/css" />
<script>
    var SITEURL = "{{url('/')}}";
</script>
@stack('styles')
