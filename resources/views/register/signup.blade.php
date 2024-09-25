@extends('layouts.guest')
@section('title','Register')
@section('content')
    <link href="{{ asset('assets/vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .select2 .select2-container .select2-container--default .select2-container--above .select2-container--focus {
            height: 59px !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            height: 56px !important;
            line-height: 76px;
            padding-left: 12px;
            /*color: var(--ct-input-color);*/
            background-color: #eef2f7;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 58px !important;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .select2-container .select2-selection--single {
            height: 58px !important;
            border: 1px solid var(--ct-input-border-color);
            height: calc(1.5em + 0.9rem + 2px);
            background-color: var(--ct-input-bg);
            outline: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: 4px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #727cf5 !important;
            color: #fff !important;
        }
    </style>
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
                <div class="card-header pt-2 pb-2 text-center bg-default" style="border-bottom: 1px solid var(--ct-card-border-color);">
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

                            <form class="customer-form" id="register-form" method="POST" action="{{ route('register.tenants') }}">
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

                                <div class="col-12 cust_email_div">
                                    <div class="form-floating mb-3">
                                        <table class="table table-centered table-borderless mb-0">
                                            <tbody>
                                            <tr>
                                                <td style="padding: 0px;">
                                                    <div class="form-floating input-group-append" style="width: 100%;" id="sel_cc">
                                                        <select class="text-left input-group form-select bg-light text-dark select2" id="country_code" name="country_code" required="" data-toggle="select2">
                                                            @php
                                                                // $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                            @endphp

                                                            @foreach($countries as $country)
                                                                <option class="text-left"
                                                                        value="{{$country->phonecode}}"
                                                                        data-id="{{$country->id}}"
                                                                        @if($country->id==101) selected @endif>
                                                                    +{{$country->phonecode}} {{$country->sortname}}</option>
                                                            @endforeach
                                                        </select>
                                                        <label for="country_code" class="form-label">Code <span class="text-danger">*</span></label>
                                                    </div>
                                                </td>
                                                <td style="padding: 0px;">
                                                    <div class="form-floating input-group-append" style="width: 100%;">
                                                        <input type="text" class="form-control bg-light text-dark" id="mobile_no" name="mobile_no" required="" placeholder="Mobile no" data-parsley-type="digits" data-parsley-errors-container="#mobileError" style="border-top-left-radius: 0; border-bottom-left-radius: 0; !important;" {{--data-parsley-minlength="10" data-parsley-maxlength="15"--}} >
                                                        <label for="mobile_no" class="form-label">Mobile no <span class="text-danger">*</span></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <span id="mobileError" style="background-color:blue;"></span>
                                    </div>
                                </div>

                                <div class="col-12 cust_email_div">
                                    <div class="form-floating mb-3">
                                        <select class="form-select bg-light text-dark" id="company_category"
                                            name="company_category" required="">
                                            <option value="">Choose</option>
                                            @foreach ($company_categories as $bus_category)
                                                <option value="{{ $bus_category->id }}">
                                                    {{ $bus_category->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="company_category" class="form-label">Business Category<span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>

                                <div class="col-12 cust_email_div">
                                    <div class="form-floating mb-3">
                                        <select class="form-select bg-light text-dark" id="country_id" name="country_id" required="">
                                            <option value="">Choose</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ $country->id == 101 ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="country_id" class="form-label">Country <span class="text-danger">*</span></label>
                                    </div>
                                </div>

                                <div class="col-12 cust_email_div">
                                    <div class="form-floating mb-3">
                                        <select class="form-select bg-light text-dark" id="state_id" name="state_id">
                                            <option value="0">Choose</option>
                                        </select>
                                        <label for="state_id" class="form-label">State <span class="text-danger"></span></label>
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
                                    <button class="btn btn-primary" type="submit"> <i class="mdi mdi-login"></i> Create Account </button>
                                </div>

                                <div class="mb-3 mb-0  text-center">
                                    <p class="text-muted">Already have account? <a href="{{ route('login') }}"
                                            class="text-muted ms-1"><b>Log In</b></a></p>
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







    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5" style="display:none;">
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
                                <h5 class="text-muted mb-4">Don't have an account? Create your account, it takes less than a minute </h5>
                            </div>
                            <form class="customer-form" id="register-form" method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="row">
                                    {{-- <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>
                                            <div class="form-check form-check-inline">
                                                <input class="form-control" type="hidden" id="id" name="id" value="0">
                                                <input type="radio" id="customer_type_business" name="customer_type" class="form-check-input" value="Business">
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
                                            <input class="form-control bg-light text-dark @error('name') is-invalid @enderror" type="text" id="name" name="name" required autocomplete="name" autofocus placeholder="Enter your name" value="{{ old('name') }}">
                                            <label for="name" class="form-label">Full Name<span class="text-danger">*</span></label>
                                            @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 cust_company_name_div">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark @error('company_name') is-invalid @enderror" type="text" id="company_name" name="company_name" required autocomplete="company_name" autofocus placeholder="Company name" value="{{ old('company_name') }}">
                                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                            @error('company_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 cust_email_div">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark @error('email') is-invalid @enderror" type="email" id="email" name="email" required autocomplete="email" autofocus placeholder="Enter your email" value="{{ old('email') }}">
                                            <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
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
                                                --}}
                                            {{-- <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>--}}{{--
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
                                <p class="text-muted">Already have account? <a href="{{ route('login') }}" class="text-muted ms-1"><b>Log In</b></a></p>
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
@push('scripts')
<script src="{{ asset('assets/js/vendor.min.js') }}"></script>

    {{-- <script src="{{ asset('assets/js/app.min.js') }}"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    {{-- <script src="{{ asset('assets/js/custom.js') }}"></script> --}}
    <script src="{{ asset('assets/vendor/select2/js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#country_code').select2({
                dropdownParent: $('#sel_cc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
                var selectedOption = $(this).find(':selected');
                var dataId = selectedOption.data('id');
                console.log("Selected data-id: " + dataId);
                $("#country_id").val($('option:selected', this).data('id'));
                getStatesList($('option:selected', this).data('id'));
            });

            getStatesList(101);

            $('#country_id').on('change', function(e) {
                e.preventDefault();
                var country_id = jQuery(this).val();
                getStatesList(country_id);
            });

            $('#state_id').on('change', function(e) {
                e.preventDefault();
                var state_id = jQuery(this).val();
                // getCityList(state_id);
            });
        });
    </script>
    <script>
        function getStatesList(country_id, seleted_id = 0) {
            $.ajax({
                async: false,
                url: "{{ url('get-states-by-country') }}",
                type: "POST",
                data: {
                    country_id: country_id
                },
                dataType: 'json',
                beforeSend: function() {
                    jQuery('select#state_id').find("option:eq(0)").html("Please wait..");
                },
                success: function(result) {
                    var options = '';
                    options += '<option value="0">Choose</option>';
                    $.each(result.states, function(key, value) {
                        var selected = "";
                        if (value.id == seleted_id || value.id == 12)
                            selected = 'selected';
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name +
                            '</option>';
                    });
                    $("#state_id").html(options);
                    // $('#city_id').html('<option value="">Choose</option>');
                },
                complete: function() {
                    // code
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    </script>
@endpush
