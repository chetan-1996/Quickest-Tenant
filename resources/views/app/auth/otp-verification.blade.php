@extends('app.layouts.guest')
@section('title', 'OTP-Verification')
@push('styles')
    <style>

        /* h3.title {
             font-size: 28px;
             font-weight: 700;
             color: #093030;
             margin-bottom: 25px;
         }*/

        /*p.sub-title {
            color: #B5BAB8;
            font-size: 14px;
            margin-bottom: 25px;
        }

        p span.phone-number {
            display: block;
            color: #093030;
            font-weight: 600;
        }*/
        /*.wrapper {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            justify-items: space-between;
        }*/
        .opt-input-css {
            width: 41px;
            line-height: 52px;
            font-size: 32px;
            background-color: #EAF5F6;
            border-radius: 5px;
            text-align: center;
            text-transform: uppercase;
            color: #093030;
            padding: 0px !important;
            margin-bottom: 15px;
            margin-top: 15px;
        }

        /* .wrapper input.field {
             width: 41px;
             line-height: 52px;
             font-size: 32px;
             !*border: none;*!
             background-color: #EAF5F6;
             border-radius: 5px;
             text-align: center;
             !*border: 1px solid var(--ct-input-border-color);*!
             text-transform: uppercase;
             color: #093030;
             padding: 0px !important;
             margin-bottom: 15px;
             margin-top: 15px;
         }

         .wrapper input.field:focus {
             outline: none;
         }*/

        button.resend {
            background-color: transparent;
            border: none;
            font-weight: 600;
            color: #727cf5;
            cursor: pointer;
        }

        /* button.verify {
             background-color: transparent;
             border: none;
             font-weight: 600;
             cursor: pointer;
             background: #727cf5;
             color: white;
             border-radius: 5px;
             padding: 6px 11px;
         }*/

        /* div {
                                                                                                                                                                                                                                                                                                                                                                                    border: 5px solid #004853;
                                                                                                                                                                                                                                                                                                                                                                                    display: inline;
                                                                                                                                                                                                                                                                                                                                                                                    padding: 5px;
                                                                                                                                                                                                                                                                                                                                                                                    color: #004853;
                                                                                                                                                                                                                                                                                                                                                                                    font-family: Verdana, sans-serif, Arial;
                                                                                                                                                                                                                                                                                                                                                                                    font-size: 40px;
                                                                                                                                                                                                                                                                                                                                                                                    font-weight: bold;
                                                                                                                                                                                                                                                                                                                                                                                    text-decoration: none;
                                                                                                                                                                                                                                                                                                                                                                                }

                                                                                                                                                                                                                                                                                                                                                                                body {
                                                                                                                                                                                                                                                                                                                                                                                    padding: 20px;
                                                                                                                                                                                                                                                                                                                                                                                    text-align: center;
                                                                                                                                                                                                                                                                                                                                                                                } */
    </style>
@endpush
@section('content')
    <div class="position-absolute start-0 end-0 start-0 bottom-0 w-100 h-100 form">
        <svg xmlns='http://www.w3.org/2000/svg' width='100%' height='100%' viewBox='0 0 800 800'>
            <g fill-opacity='0.22'>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.1);" cx='400' cy='400' r='600'/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.2);" cx='400' cy='400' r='500'/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.3);" cx='400' cy='400' r='300'/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.4);" cx='400' cy='400' r='200'/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.5);" cx='400' cy='400' r='100'/>
            </g>
        </svg>
    </div>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="pt-2 pb-2 text-center">
                    <a href="{{ route('login') }}">
                        <span><img src="{{ asset('images/logo.png') }}" alt="logo" height="80"></span>
                    </a>
                </div>
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="container">
                                <h3 class="title">OTP Verification</h3>
                                @if (session('success'))
                                    <div class="alert alert-success" role="alert"> {{ session('success') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger" role="alert"> {{ session('error') }}
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('otp.getlogin') }}" id="opt-verify-form">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ \Crypt::decrypt($user_id) }}"/>
                                    <h5 class="sub-title text-dark">
                                        Enter your OTP to access the Quickest account.
                                    </h5>

                                    <span class="phone-number mb-2">{{ \Crypt::decrypt(Request::get('email')) }}</span>
                                    <div class="row">
                                        <div class="col-sm-2 col-3 ms-auto">
                                            <div class="mb-3">
                                                <input type="text"
                                                       class="opt-input opt-input-css form-control bg-light text-dark field 1 @error('otp') is-invalid @enderror"
                                                       id="field1" name="otp[]" maxlength="1">
                                            </div>
                                        </div>
                                        <div class="col-sm-2 col-3">
                                            <div class="mb-3">
                                                <input type="text"
                                                       class="opt-input opt-input-css form-control bg-light text-dark field 2 @error('otp') is-invalid @enderror"
                                                       id="field2" name="otp[]" maxlength="1">
                                            </div>
                                        </div>
                                        <div class="col-sm-2 col-3">
                                            <div class="mb-3">

                                                <input type="text"
                                                       class="opt-input opt-input-css form-control bg-light text-dark field 3 @error('otp') is-invalid @enderror"
                                                       id="field3" name="otp[]" maxlength="1">
                                            </div>
                                        </div>
                                        <div class="col-sm-2 col-3">
                                            <div class="mb-3">
                                                <input type="text"
                                                       class="opt-input opt-input-css form-control bg-light text-dark field 4 @error('otp') is-invalid @enderror"
                                                       id="field4" name="otp[]" maxlength="1">
                                            </div>
                                        </div>
                                        <div class="col-sm-2 col-3">
                                            <div class="mb-3">
                                                <input type="text"
                                                       class="opt-input opt-input-css form-control bg-light text-dark field 5 @error('otp') is-invalid @enderror"
                                                       id="field5" name="otp[]" maxlength="1">
                                            </div>
                                        </div>
                                        <div class="col-sm-2 col-3">
                                            <div class="mb-3">
                                                <input type="text"
                                                       class="opt-input opt-input-css form-control bg-light text-dark field 6 @error('otp') is-invalid @enderror"
                                                       id="field6" name="otp[]" maxlength="1">
                                            </div>
                                        </div>
                                    </div>
                                    {{--<input id="otp" type="hidden"
                                           class="form-control @error('otp') is-invalid @enderror" name="otp"
                                           value="{{ old('otp') }}" required autocomplete="otp" autofocus
                                           placeholder="Enter OTP">--}}
                                </form>

                                <div class="d-flex justify-content-md-between align-items-center">
                                    <div class="d-flex">
                                        <button class="resend" type="submit" class="btn btn-primary btn-dark"
                                                form="resend-otp-form"
                                                @if ($isGreaterThanSpecificDateTime == false) style="opacity:0.5;"
                                                disabled @endif>
                                            {{ __('Resend OTP') }}
                                        </button>
                                        @if ($isGreaterThanSpecificDateTime == false)
                                            <div id="countdown" style="color:#727cf5"></div>
                                        @endif
                                    </div>
                                    <button type="submit" class="btn btn-primary verify opt-input" form="opt-verify-form">
                                        {{ __('Verify') }}
                                    </button>
                                </div>


                                <form method="POST" action="{{ route('otp.generate') }}" id="resend-otp-form">
                                    @csrf
                                    <div class="mb-3">
                                        <input id="email" type="hidden" name="email"
                                               value="{{ \Crypt::decrypt(Request::get('email')) }}" required>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    @if (Route::has('login'))
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="text-muted">Return to <a href="{{ route('login') }}" class="text-muted"><b>
                                            {{ __('Login') }}</b></a></p>
                            </div> <!-- end col-->
                        </div>
                    @endif
                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
@endsection
@push('scripts')
    <script>
        const inputs = document.querySelectorAll('.opt-input');

        for (let i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('keydown', function (event) {
                if (event.key === "Backspace") {

                    if (inputs[i].value == '') {
                        if (i != 0) {
                            inputs[i - 1].focus();
                        }
                    } else {
                        inputs[i].value = '';
                    }

                } else if (event.key === "ArrowLeft" && i !== 0) {
                    inputs[i - 1].focus();
                } else if (event.key === "ArrowRight" && i !== inputs.length - 1) {
                    inputs[i + 1].focus();
                } else if (event.key != "ArrowLeft" && event.key != "ArrowRight") {
                    inputs[i].setAttribute("type", "text");
                    inputs[i].value = ''; // Bug Fix: allow user to change a random otp digit after pressing it
                    setTimeout(function () {
                        inputs[i].setAttribute("type", "password");
                    }, 1000); // Hides the text after 1.5 sec
                }
            });
            inputs[i].addEventListener('input', function () {
                inputs[i].value = inputs[i].value.toUpperCase(); // Converts to Upper case. Remove .toUpperCase() if conversion isnt required.
                if (i === inputs.length - 1 && inputs[i].value !== '') {

                    console.log(i)
                    inputs[i].closest('form')
                    // inputs[i].closest('form').querySelector('.btn-optin-confirm').disabled = false;
                    return true;
                } else if (inputs[i].value !== '') {
                    inputs[i + 1].focus();
                }


            });

            // console.log(inputs.length);

        }
        /*$(':input').keyup(function(e) {
            if (e.which == 8 || e.which == 46) {
                $(this).prev('input').focus();
            }
        });*/
    </script>
    <script>
        let timerOn = true;

        function timer(remaining) {
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;

            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;
            document.getElementById('countdown').innerHTML = m + ':' + s;
            remaining -= 1;

            if (remaining >= 0 && timerOn) {
                setTimeout(function () {
                    timer(remaining);
                }, 1000);
                return;
            }

            if (!timerOn) {
                // Do validate stuff here
                return;
            }

            // Do timeout stuff here
            $('.resend').attr("disabled", false);
            $('.resend').css("opacity", "1");
            $('#countdown').css('display', 'none');
        }

        timer({{ $second }});
    </script>
@endpush
