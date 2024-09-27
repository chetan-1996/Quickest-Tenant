<!-- Theme Config Js -->
<script src="{{asset('js/hyper-config.js')}}"></script>

<!-- App css -->
<link href="{{asset('css/app-saas.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
{{--<link href="https://themesbrand.com/velzon/html/material/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="app-style" />--}}

<!-- Icons css -->
<link href="{{asset('css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/icons.min1.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/custom.css')}}" rel="stylesheet" type="text/css" />
<script>
    var SITEURL = "{{url('/'.$segment)}}";
</script>
@stack('styles')
