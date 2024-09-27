@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
    $user = Auth::user();
    $companyId = $user->company_id ? $user->company_id : $user->id;
    $user_data = App\Models\User::where('id', $companyId)->first();
    $planData = App\Models\admin\Plans::where('id', $user_data)->first();
@endphp
@extends('app.layouts.app')
@section('title', 'Checkout')
@section('content')
    <link href="{{ asset('vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <div class="content-page">
        <div class="content">

            <!-- Start Content-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <!-- Checkout Steps -->
                                <ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
                                    <li class="nav-item">
                                        <a href="#billing-information" data-bs-toggle="tab" aria-expanded="false"
                                            class="nav-link rounded-0 active" id="billing-information-tab">
                                            <i class="mdi mdi-account-circle font-18"></i>
                                            <span class="d-none d-lg-block">Billing Info</span>
                                        </a>
                                    </li>
                                </ul>

                                <!-- Steps Information -->
                                <div class="row">
                                    <div class="col-md-12 col-sm-6 col-lg-6 col-xl-6">
                                        @include('app.plan.order-summary')
                                    </div>
                                    <div class="col-md-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="tab-content" style="padding: 10px 15px;margin-right: 11px;">

                                            <!-- Billing Content-->

                                            <div class="tab-pane show active" id="billing-information">
                                                <div class="row">
                                                    {{-- <div class="col-lg-8"> --}}
                                                    <h4 class="mt-2">Billing information</h4>

                                                    <p class="text-muted mb-4">Fill the form below in order to
                                                        send you the order's invoice.</p>

                                                        <form>
                                                            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                                                        <div class="row">
                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="billing-first-name" class="form-label">
                                                                        Name</label>
                                                                    <input class="form-control" type="text"
                                                                        placeholder="Enter your first name" id="billing-first-name"
                                                                        value="{{ $user_data->name }}" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="billing-email-address" class="form-label">Email
                                                                        Address <span class="text-danger">*</span></label>
                                                                    <input class="form-control" type="email"
                                                                        placeholder="Enter your email" id="billing-email-address"
                                                                        value="{{ $user_data->email }}" disabled/>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end row -->
                                                        <div class="row">
                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="billing-phone" class="form-label">Phone <span
                                                                            class="text-danger">*</span></label>
                                                                    <input class="form-control" type="tel"
                                                                        placeholder="(xx) xxx xxxx xxx" id="billing-phone"
                                                                        value="{{ $user_data->mobile_no }}" disabled/>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="billing-address" class="form-label">Address</label>
                                                                    <input class="form-control" type="text"
                                                                        value="{{ $user_data->address }}"
                                                                        placeholder="Enter full address" id="billing-address">
                                                                </div>
                                                            </div>
                                                        </div> <!-- end row -->
                                                        <div class="row">
                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="company_name" class="form-label">Company Name <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="company_name"
                                                                        name="company_name" placeholder="Enter company name"
                                                                        value="{{ $user_data->company_name }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="gst_no" class="form-label">Tax (GSTIN)</label>
                                                                    <input type="text" class="form-control" id="gst_no"
                                                                        name="gst_no" placeholder="Enter tax number"
                                                                        value="{{ $user_data->gst_no }}" >
                                                                </div>
                                                            </div>
                                                        </div> <!-- end row -->
                                                        <div class="row">
                                                            <div class="col-md-4 col-xl-4 col-lg-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Country</label>
                                                                    <select class="selectionClass" data-toggle="select2" title="Country" id="billing-country"
                                                                        onchange="selectCountry(this)">
                                                                        <option value="">Choose</option>
                                                                        @foreach ($countries as $country)
                                                                            <option value="{{ $country->id }}"
                                                                                {{ $country->id == $user_data->country_id || $country->id == 101 ? 'selected' : '' }}>
                                                                                {{ $country->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-xl-4 col-lg-4">
                                                                <div class="mb-3">
                                                                    <label for="billing-state" class="form-label">State</label>
                                                                    <select class="selectionClass" placeholder="Enter your state" data-toggle="select2"
                                                                        title="State" id="billing-state">
                                                                        <option value="">Choose</option>
                                                                        @foreach ($states as $state)
                                                                            <option value="{{ $state->id }}"
                                                                                {{ $state->id == $user_data->state_id || $state->id == 12 ? 'selected' : '' }}>
                                                                                {{ $state->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-xl-4 col-lg-4">
                                                                <div class="mb-3">
                                                                    <label for="billing-city" class="form-label">Town /
                                                                        City</label>
                                                                    <input class="form-control" type="text"
                                                                        placeholder="Enter your city name" id="billing-city"
                                                                        value="{{ $user_data->city_name }}" />
                                                                </div>
                                                            </div>


                                                        </div> <!-- end row -->
                                                        <div class="row">
                                                            <div class="col-md-6 col-xl-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="billing-zip-postal" class="form-label">Zip /
                                                                        Postal Code</label>
                                                                    <input class="form-control" type="text"
                                                                        placeholder="Enter your zip code" id="billing-zip-postal" value="{{ $user_data->pincode }}" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-sm-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="company_category" class="form-label">Business
                                                                        Category</label>
                                                                    <select class="selectionClass" data-toggle="select2" id="company_category"
                                                                        name="company_category" disabled>
                                                                        <option value="">Choose</option>
                                                                        @foreach ($company_categories as $bus_category)
                                                                            <option value="{{ $bus_category->id }}"
                                                                                {{ $bus_category->id == $user_data->company_category ? 'selected' : '' }}>
                                                                                {{ $bus_category->name }}</option>
                                                                        @endforeach
                                                                    </select>

                                                                </div>
                                                            </div>
                                                        </div> <!-- end row -->

                                                        <div class="row mt-4">
                                                            <div class="col-md-12 col-xl-12 col-lg-12">
                                                                <div class="d-flex justify-content-end">
                                                                    <div class="text-sm-end">
                                                                        <button type="button" class="btn btn-danger"
                                                                            id="process-to-pay">
                                                                            <i class="mdi mdi-truck-fast me-1"></i> Proceed to
                                                                            Pay </button>
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                    </form>
                                                </div> <!-- end row-->
                                            </div>
                                            <!-- End Billing Information Content-->
                                        </div>
                                    </div>

                                </div>
                            </div> <!-- end tab content-->

                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col -->
                <!-- end row-->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/select2/js/select2.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>                                                             
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script>
        $(document).ready(function() {
        var options = {
            "key": "{{ env('RAZORPAY_KEY') }}",
            "amount": "100", // Default amount
            "name": "quickestimate.co",
            "description": "Razorpay payment",
            "image": "https://app.quickestimate.co/assets/images/logo.png",
            "prefill": {
                "name": $('#billing-first-name').val(),
                "email": $('#billing-email-address').val()
            },
            "theme": {
                // "color": "#0F408F"
                "color": "#727cf5"
            },
            /*config: {
                display: {
                    blocks: {
                        utib: { //name for Axis block
                            name: "Pay using Axis Bank",
                            instruments: [
                                {
                                    method: "card",
                                },
                                {
                                    method: "netbanking",
                                },
                            ]
                        },
                        other: { //  name for other block
                            name: "Other Payment modes",
                            instruments: [
                                {
                                    method: "card",
                                },
                                {
                                    method: 'netbanking',
                                }
                            ]
                        }
                    },
                    hide: [
                        {
                            method: "emi"
                        }
                    ],
                    sequence: ["block.utib", "block.other"],
                    preferences: {
                        show_default_blocks: false // Should Checkout show its default blocks?
                    }
                }
            },*/
            config: {
                display: {
                    hide: [
                        {
                            method: 'emi'
                        }
                    ],
                    preferences: {
                        show_default_blocks: true,
                    },
                },
            },
            "handler": function(res) {
                console.log(res);
                var first_name = $('#billing-first-name').val();
                var email = $('#billing-email-address').val();
                var phone_no = $('#billing-phone').val();
                var address = $('#billing-address').val();
                var city = $('#billing-city').val();
                var state = $('#billing-state').val();
                var country = $('#billing-country').find(":selected").val();
                var pin_code = $('#billing-zip-postal').val();
                var gst_no = $('#gst_no').val();
                var plan_id = $('#plan_id').val();
                var user_id = $('#user_id').val();
                var payment = $('#payment').val();
                var add_users = $('#addUsers').val();
                var add_user = $('#addUser').val();
                $.ajax({
                    url: "{{ route('tenant.razorpay.payment.store',['tenant' => $segment]) }}",
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $("#process-to-pay").prop('disabled', true);
                        $("#process-to-pay").html(
                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    data: {
                        _token: "{{ csrf_token() }}", // CSRF token
                        response: {
                            razorpay_payment_id: res.razorpay_payment_id
                        },
                        'first_name': first_name,
                        'email': email,
                        'mobile_no': phone_no,
                        'address': address,
                        'city': city,
                        'state': state,
                        'country': country,
                        'pincode': pin_code,
                        'plan_id': plan_id,
                        'user_id': user_id,
                        'payment': payment,
                        'addUsers': add_users,
                        'add_user': add_user,
                        'gst_no': gst_no
                    },
                    success: function(res) {
                        toastrSuccess('Payment Succefully', 'Success');
                        console.log('Payment data sent to server', res);
                        if (res.success == true) {
                            window.location.href = '/plan'; // Redirect to success page
                            $("#process-to-pay").prop('disabled', false);
                            $("#process-to-pay").html(
                                '<i class="mdi mdi-truck-fast me-1"></i> Proceed to Pay');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error sending payment information:', textStatus, errorThrown);
                    }
                });
            },
            "modal": {
                "ondismiss": function() {
                    // This function is called when the user closes the modal
                    window.location.href = '/plan'; // Redirect to your failure page
                }
            }
        };

        $('.selectionClass').select2();

        var rzp = new Razorpay(options);

        document.getElementById('process-to-pay').onclick = function(e) {
            e.preventDefault();
            var amount = Math.floor(parseFloat($(".total_label").text()))*100;
            if (amount) {
                options.amount = amount; // Update the amount
                rzp = new Razorpay(options); // Reinitialize Razorpay with the updated amount
            }
            rzp.open();
        }

        rzp.on('payment.failed', function(response) {
            event.preventDefault();
            if (response.reason == "payment_failed") {
                const {
                    error,
                    reason
                } = response;
                $.ajax({
                    url: "/payment/failure",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}", // CSRF token
                        response: {
                            error,
                            reason
                        }
                    },
                    success: function(response) {
                        console.log('Payment failure data sent to server', response);
                        if (response.success == true) {
                            window.location.href = '/402'; // Redirect to failure page
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error sending payment failure information:', textStatus, errorThrown);
                    }
                });
            }
        });

        });


       /* $('#process-to-pay').on("click", function() {
            var first_name = $('#billing-first-name').val();
            var email = $('#billing-email-address').val();
            var phone_no = $('#billing-phone').val();
            var address = $('#billing-address').val();
            var city = $('#billing-city').val();
            var state = $('#billing-state').val();
            var country = $('#billing-country').find(":selected").val();
            var pin_code = $('#billing-zip-postal').val();
            var gst_no = $('#gst_no').val();
            var plan_id = $('#plan_id').val();
            var user_id = $('#user_id').val();
            var payment = $('#payment').val();
            var add_users = $('#addUsers').val();
            var add_user = $('#addUser').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'POST',
                url: '{{ route('tenant.user.pay', ['tenant' => $segment]) }}',
                data: {
                    'first_name': first_name,
                    'email': email,
                    'mobile_no': phone_no,
                    'address': address,
                    'city': city,
                    'state': state,
                    'country': country,
                    'pincode': pin_code,
                    'plan_id': plan_id,
                    'user_id': user_id,
                    'payment': payment,
                    'addUsers': add_users,
                    'add_user': add_user,
                    'gst_no': gst_no
                },
                dataType: 'json',
                success: function(response_msg) {
                    if (response_msg.success == true) {
                        toastrSuccess('Payment Succefully Done', 'Success');
                        var queryString = "?data=" + encodeURIComponent(JSON.stringify(
                            response_msg
                            .data));
                        var redirectUrl = SITEURL + '/payment' + queryString;
                        window.location.href = redirectUrl;
                    } else if (response_msg.success == 408) {
                        toastrError('Something Went Wrong', 'Error');
                    } else if (response_msg.errors) {
                        var errors = response_msg.errors;
                        console.log(errors);
                        jQuery.each(errors, function(key, message) {
                            toastrError(message, 'Error');
                            console.log(message);
                        });
                    }
                }
            });
            // }
        });*/

        function selectCountry(country, seleted_id = 0) {
            var selectedText = country.options[country.selectedIndex].innerHTML;
            var selectCountry = country.value;
            var planStatus = $('#addUsers').val();
            $.ajax({
                async: false,
                url: "{{ url('get-states-by-country') }}",
                type: "POST",
                data: {
                    country_id: selectCountry
                },
                dataType: 'json',
                beforeSend: function() {
                    jQuery('select#billing-state').find("option:eq(0)").html("Please wait..");
                },
                success: function(result) {
                    var options = '';
                    options += '<option value="">Choose</option>';
                    $.each(result.states, function(key, value) {
                        var selected = "";
                        options += '<option value="' + value.id + '">' + value
                            .name +
                            '</option>';
                    });
                    $("#billing-state").html(options);
                    if (planStatus == 1) {
                        calculateUser();
                    }
                    if (planStatus == 0) {
                        calculatePrice();
                    }
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
