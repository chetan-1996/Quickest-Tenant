@extends('layouts.guest')

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


    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Logo -->
                <div class="card-header pt-4 pb-4 text-center bg-default" style="border-bottom: 1px solid var(--ct-card-border-color);">
                    <a href="{{ route('login') }}">
                        <span><img src="{{asset('assets/images/logo.png')}}" alt="logo" height="100"></span>
                    </a>
                </div>
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">

                        <div class="card-body p-4">

                            <div class="text-center m-auto">
                                <h3 class="text-dark-50 text-center pb-0 fw-bold">{{ __('Sign Up') }}</h3>
                                <p class="text-muted mb-4">{{ __("Don't have an account? Create your account, it takes less than a minute") }}</p>
                            </div>

                            <form class="customer-form" id="register-form" method="POST" action="{{ route('tenants.store') }}">
                                @csrf

                                <div class="col-12 cust_company_name_div">
                                    <div class="form-floating mb-3">
                                        <input
                                            class="form-control bg-light text-dark @error('name') is-invalid @enderror"
                                            type="text" id="name" name="name" required autocomplete="name"
                                            autofocus placeholder="Enter your name" value="{{ old('name') }}">
                                        <label for="name" class="form-label">Full Name<span
                                                class="text-danger">*</span></label>
                                        @error('name')
                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 cust_company_name_div">
                                    <div class="form-floating mb-3">
                                        <input
                                            class="form-control bg-light text-dark @error('company_name') is-invalid @enderror"
                                            type="text" id="company_name" name="company_name" required
                                            autocomplete="company_name" autofocus placeholder="Company name"
                                            value="{{ old('company_name') }}">
                                        <label for="company_name" class="form-label">Company Name <span
                                                class="text-danger">*</span></label>
                                        @error('company_name')
                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 cust_email_div">
                                    <div class="form-floating mb-3">
                                        <input
                                            class="form-control bg-light text-dark @error('email') is-invalid @enderror"
                                            type="email" id="email" name="email" required autocomplete="email"
                                            autofocus placeholder="Enter your email" value="{{ old('email') }}">
                                        <label for="email" class="form-label">Email address <span
                                                class="text-danger">*</span></label>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        {{-- <div class="form-group">
                                             <strong>ReCaptcha:</strong>
                                             <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
                                             @if ($errors->has('g-recaptcha-response'))
--}}{{--                                                    <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>--}}{{--
                                                 <span class="text-danger">{{ 'The google recaptcha is required.' }}</span>
                                             @endif
                                         </div>--}}
                                        <div class="form-group{{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
                                            <div class="col-md-12">
                                                {!! RecaptchaV3::field('register') !!}
                                                @if ($errors->has('g-recaptcha-response'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="is_accepted_terms_condition" value="1">

                                <div class="mb-3 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                                        <label class="form-check-label" for="checkbox-signin">{{ __('Remember me') }}</label>
                                    </div>
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit"> <i class="mdi mdi-login"></i> Log In </button>
                                </div>

                            </form>
                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>







    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Logo-->
                <div class="pt-4 text-center">
                    {{-- <a href="{{  }}"> --}}
                    <span><img src="{{ asset('assets/images/logo.png') }}" alt="" height="100"></span>
                    {{-- </a> --}}
                </div>
                {{-- <div class="col-xxl-4 col-lg-5"> --}}
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        {{-- <div class="modal-header border-1 bg-light">
                <h3 class="modal-title text-dark">Create Customer</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> --}}

                        <div class="modal-body p-3 p-0">
                            <div class="text-center w-75 m-auto">
                                <h3 class="text-dark-50 text-center mt-0 fw-bold">Sign Up</h3>
                                <h5 class="text-muted mb-4">Don't have an account? Create your account, it takes less
                                    than a
                                    minute </h5>
                            </div>
                            <form class="customer-form" id="register-form" method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="row">
                                    {{-- <div class="col-12">
                            <div class="form-floating mb-3">
                                <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>
                                <div class="form-check form-check-inline">
                                    <input class="form-control" type="hidden" id="id" name="id" value="0">
                                    <input type="radio" id="customer_type_business" name="customer_type"
                                        class="form-check-input" value="Business">
                                    <label class="form-check-label" for="customer_type_business">Business</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="customer_type_individual" name="customer_type"
                                        class="form-check-input" value="Individual" checked>
                                    <label class="form-check-label" for="customer_type_individual">Individual</label>
                                </div>
                            </div>
                        </div> --}}
                                    <div class="col-12 cust_company_name_div">
                                        <div class="form-floating mb-3">
                                            <input
                                                class="form-control bg-light text-dark @error('name') is-invalid @enderror"
                                                type="text" id="name" name="name" required autocomplete="name"
                                                autofocus placeholder="Enter your name" value="{{ old('name') }}">
                                            <label for="name" class="form-label">Full Name<span
                                                    class="text-danger">*</span></label>
                                            @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 cust_company_name_div">
                                        <div class="form-floating mb-3">
                                            <input
                                                class="form-control bg-light text-dark @error('company_name') is-invalid @enderror"
                                                type="text" id="company_name" name="company_name" required
                                                autocomplete="company_name" autofocus placeholder="Company name"
                                                value="{{ old('company_name') }}">
                                            <label for="company_name" class="form-label">Company Name <span
                                                    class="text-danger">*</span></label>
                                            @error('company_name')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 cust_email_div">
                                        <div class="form-floating mb-3">
                                            <input
                                                class="form-control bg-light text-dark @error('email') is-invalid @enderror"
                                                type="email" id="email" name="email" required autocomplete="email"
                                                autofocus placeholder="Enter your email" value="{{ old('email') }}">
                                            <label for="email" class="form-label">Email address <span
                                                    class="text-danger">*</span></label>
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            {{-- <div class="form-group">
                                                 <strong>ReCaptcha:</strong>
                                                 <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
                                                 @if ($errors->has('g-recaptcha-response'))
 --}}{{--                                                    <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>--}}{{--
                                                     <span class="text-danger">{{ 'The google recaptcha is required.' }}</span>
                                                 @endif
                                             </div>--}}
                                            <div class="form-group{{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
                                                <div class="col-md-12">
                                                    {!! RecaptchaV3::field('register') !!}
                                                    @if ($errors->has('g-recaptcha-response'))
                                                        <span class="help-block">
                                                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="is_accepted_terms_condition" value="1">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer" style="flex-direction:column;">
                            <div class="text-center">
                                <button class="btn btn-primary align-content-center fullscreen" form="register-form"
                                        id="register_button" type="submit">
                                    <i class="uil-arrow-circle-right"></i> Create Account
                                </button>
                            </div>

                            <div class="col-12 text-center mt-2">
                                <p class="text-muted">Already have account? <a href="{{ route('login') }}"
                                                                               class="text-muted ms-1"><b>Log In</b></a></p>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div>
                <!-- end card -->

                <div class="row">
                    <div class="col-12 text-center">
                        <p class="text-center font-size-sm">By creating a Quickest account, you are agreeing to
                            accept our <br><a href="https://quickestimate.co/terms-conditions/" target="_blank"
                                              class="text-primary">Terms &amp;
                                Conditions</a>
                        </p>
                    </div> <!-- end col-->
                </div>
                <!-- end row -->

                {{-- </div> <!-- end col --> --}}
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
@endsection
