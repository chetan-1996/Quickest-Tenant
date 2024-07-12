@extends('app.layouts.guest')
@section('title','Login')
@push('styles')
    <style>
        .auth-one-bg {
            background-image: url({{ asset('images/auth-one-bg.jpg') }});
            background-position: center;
            background-size: cover;
        }

        .auth-one-bg .bg-overlay {
            background: -webkit-gradient(linear, left top, right top, from(#000), to(#000));
            background: linear-gradient(to right, #000, #000);
            opacity: .9;
        }

        .auth-one-bg .bg-overlay {
            position: absolute;
            height: 100%;
            width: 50%;
            right: 0;
            bottom: 0;
            left: 0;
            top: 0;
            opacity: .7;
            background-color: #000;
        }

        .carousel-item{
            text-shadow: 0 0 3px #632c7f, 0 0 5px #632c7f;
            font-weight: bold;
            font-size: 1rem;
        }
    </style>
@endpush
@section('content')
    <div class="position-absolute start-0 end-0 start-0 bottom-0 w-100 h-100">
        <svg xmlns='http://www.w3.org/2000/svg' width='100%' height='100%' viewBox='0 0 800 800'>
            <g fill-opacity='0.22'>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.1);" cx='400' cy='400' r='600' />
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.2);" cx='400' cy='400' r='500' />
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.3);" cx='400' cy='400' r='300' />
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.4);" cx='400' cy='400' r='200' />
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.5);" cx='400' cy='400' r='100' />
            </g>
        </svg>
    </div>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">


            <div class="row">
                <div class="col-lg-12">
                    <div class="card overflow-hidden">
                        <div class="row g-0">
                            <div class="col-lg-6">
                                <div class="p-lg-2 p-2 auth-one-bg h-100">
                                    {{--                                    <div class="bg-overlay"></div> --}}
                                    <div class="position-relative h-100 d-flex flex-column">
                                        <div class="mb-4">
                                            {{--                                            <a href="index.html" class="d-block"> --}}
                                            {{--                                                <img src="{{asset('assets/images/logo.png')}}" alt="" height="80"> --}}
                                            {{--                                            </a> --}}
                                        </div>
                                        <div class="mt-auto">
                                            {{--                                            <div class="mb-1"> --}}
                                            {{--                                                <i class="mdi mdi-format-quote-open display-4 text-primary"></i> --}}
                                            {{--                                            </div> --}}

                                            <div id="qoutescarouselIndicators" class="carousel slide"
                                                 data-bs-ride="carousel">
                                                <div class="carousel-indicators">
                                                    <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="0" class="active" aria-label="Slide 1"
                                                            aria-current="true"></button>
                                                    <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="1" aria-label="Slide 2" class=""></button>
                                                    <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="2" aria-label="Slide 3" class=""></button>
                                                </div>
                                                <div class="carousel-inner text-center text-white pb-4">
                                                    <div class="carousel-item active">
                                                        <p class="fs-15 fst-italic">" Build Trust with Customers with first
                                                            impression "</p>
                                                    </div>
                                                    <div class="carousel-item">
                                                        <p class="fs-15 fst-italic">" We remind you for every follow up! "
                                                        </p>
                                                    </div>
                                                    <div class="carousel-item">
                                                        <p class="fs-15 fst-italic">" Monitor Your Sales Performance! "</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end carousel -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-lg-6">
                                <div class="p-lg-5 p-4">
                                    <div>
                                        <h3 class="text-primary">Welcome Back !</h3>
                                            <h5 class="text-muted">Sign in to continue to
                                                {{ config('app.name', 'Laravel') }}.
                                            </h5>
                                    </div>
                                    @if ($message = Session::get('pending'))
                                        <div class="alert alert-danger" role="alert">
                                            <i class="dripicons-wrong me-2"></i> {{ $message }}
                                        </div>
                                    @endif
                                    <div class="mt-4">
                                        <form method="POST" action="{{ route('otp.generate') }}">
                                            @csrf
                                            <div class="col-12 cust_email_div">
                                                <div class="form-floating mb-3">
                                                    <input
                                                        class="form-control bg-light text-dark @error('email') is-invalid @enderror"
                                                        type="email" id="email" name="email" required
                                                        autocomplete="email" autofocus placeholder="Enter your email"
                                                        value="{{ old('email') }}">
                                                    <label for="email" class="form-label">Email address <span
                                                            class="text-danger">*</span></label>
                                                    @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                @if (session('error'))
                                                    <div class="alert alert-danger" role="alert">
                                                        {{ session('error') }}
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- <div class="mb-1">
                                                <a href="{{ route('password.request') }}"
                                                    class="text-muted float-end"><small>Forgot your password?</small></a>
                                                <label for="password" class="form-label">Password</label>
                                                <div class="input-group input-group-merge">
                                                    <input id="password" type="password"
                                                        class="form-control @error('password') is-invalid @enderror"
                                                        name="password" required autocomplete="current-password"
                                                        placeholder="Enter your password">
                                                    <div class="input-group-text" data-password="false">
                                                        <span class="password-eye"></span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('otp.login') }}"
                                                    class="text-muted float-end pt-1"><small>Log in using OTP</small></a>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div> --}}

                                            <div class="mb-3">
                                                {{-- <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remember"
                                                        id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="remember">Remember me</label>
                                                </div> --}}
                                            </div>

                                            <div class="text-center">
                                                <button class="btn btn-primary w-100" id="loginSubmit" type="submit"><i
                                                        class="mdi mdi-login"></i> Next </button>
                                            </div>

                                            {{-- <div class="text-center mt-4">
                                                <p class="text-muted font-16">Sign in with</p>
                                                <ul class="social-list list-inline mt-3">
                                                    <li class="list-inline-item">
                                                        <a href="{{ url('auth/google') }}" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                                                    </li>
                                                </ul>
                                            </div> --}}
                                        </form>

                                    </div>
                                    <div class="mt-5 text-center">
                                        <p class="mb-0">Don't have an account ? <a href="{{ route('register') }}"
                                                                                   class="fw-semibold text-primary"> Signup</a> </p>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end card -->
                    <div class="row mt-3">
                        <div class="col-12 text-center">
{{--                            <p class="text-muted"><a class="text-primary" href="{{route('privacy-policy')}}"><b>Privacy policy</b></a> | <a class="text-primary" href="{{route('term-conditions')}}"> <b>Terms & conditions</b></a> | <a class="text-primary" href="{{route('refund-and-cancellation-policy')}}"> <b>Refund & Cancellation Policies</b></a></p>--}}
                        </div> <!-- end col-->
                    </div>
                </div>
                <!-- end col -->

            </div>
            <!-- end row -->








            {{-- <div class="row justify-content-center">
             <div class="col-xxl-4 col-lg-5">
                 <div class="card">

                     <!-- Logo -->
                     <div class="card-header pt-4 pb-4 text-center bg-default">
                         <a href="index.html">
                             <span><img src="{{asset('assets/images/logo.png')}}" alt="" height="100"></span>
                         </a>
                     </div>

                     <div class="card-body p-4">

                         <div class="text-center w-75 m-auto">
                             <h4 class="text-dark-50 text-center pb-0 fw-bold">Sign In</h4>
                             <p class="text-muted mb-4">Enter your email address and password to access admin panel.</p>
                         </div>
                         @if ($message = Session::get('pending'))
                             <div class="alert alert-danger" role="alert">
                                 <i class="dripicons-wrong me-2"></i> {{$message}}
                             </div>
                         @endif

                         <form method="POST" action="{{ route('login') }}">

                         @csrf
                             <div class="mb-1">
                                 <label for="emailaddress" class="form-label">Mobile/Email address</label>
                                 <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email or mobile">
                                 @error('email')
                                     <span class="invalid-feedback" role="alert">
                                         <strong>{{ $message }}</strong>
                                     </span>
                                 @enderror
                             </div>

                             <div class="mb-1">
                                 <a href="{{ route('password.request') }}" class="text-muted float-end"><small>Forgot your password?</small></a>
                                 <label for="password" class="form-label">Password</label>
                                 <div class="input-group input-group-merge">
                                     <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">
                                     <div class="input-group-text" data-password="false">
                                         <span class="password-eye"></span>
                                     </div>
                                 </div>
                                 @error('password')
                                     <span class="invalid-feedback" role="alert">
                                         <strong>{{ $message }}</strong>
                                     </span>
                                 @enderror
                             </div>

                             <div class="mb-1 mb-1">
                                 <div class="form-check">
                                 <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                     <label class="form-check-label" for="remember">Remember me</label>
                                 </div>
                             </div>

                             <div class="mb-1 mb-0 text-center">
                                 <button class="btn btn-primary" type="submit"><i class="mdi mdi-login"></i> Log In </button>
                             </div>

                             <div class="text-center mt-4">
                                 <p class="text-muted font-16">Sign in with</p>
                                 <ul class="social-list list-inline mt-3">
                                     <li class="list-inline-item">
                                         <a href="{{ url('auth/google') }}" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                                     </li>
                                 </ul>
                             </div>
 --}}{{--                            <a href="{{url('/redirect')}}" class="btn btn-primary">Login with Facebook</a> --}}{{--
                         </form>
                     </div> <!-- end card-body -->
                 </div>
                 <!-- end card -->

                 <div class="row mt-3">
                     <div class="col-12 text-center">
                         <p class="text-muted">Don't have an account? <a href="{{ route('register') }}" class="text-muted ms-1"><b>Sign Up</b></a></p>
                     </div> <!-- end col -->
                 </div>
                 <!-- end row -->

             </div> <!-- end col -->
         </div> --}}
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script>
        localStorage.clear();
    </script>
    <script>
        $('form').submit(function() {
            $('button[type="submit"]').prop('disabled', true);
        });
    </script>
@endpush
{{--
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
--}}
