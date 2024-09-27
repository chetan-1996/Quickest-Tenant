@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
    $user = Auth::user();
    $companyId = $user->company_id ? $user->company_id : $user->id;
    $user = App\Models\User::where('id', $companyId)->first();
    $userCount = App\Models\User::where('company_id', $companyId)->count();
    $planData = App\Models\admin\Plans::where('id', json_decode($data['plan'])->id)->first();
@endphp
@push('styles')
    {{-- <link href="{{ asset('assets/css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
        integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous"> --}}
    <style>
        .total_user_price {
            font-size: 19px;
        }

        .upgradePlans {
            border: 1px solid #7db9e8;
            background: white;
            background: white;
            padding: 15px;
            border-radius: 10px;
        }

        .upgradePlanLabel {
            font-size: 15px;
        }

        .upgradePlanRow {
            padding-bottom: 10px;
        }

        .upgradeValue {
            text-align: end;
        }

        .pay_now_btn {
            BORDER: none;
            background: #7db9e8;
            color: white;
            border-radius: 5px;
            padding: 8px 9px;
        }

        .pay_now_btn:hover {
            border: 1px solid #7db9e8;
            background: white;
            color: #7db9e8;
        }

        .hide {
            display: none;
        }

        .unhide {
            display: block;
        }

        .error_msgs {
            border: 1px solid green;
            border-radius: 5px;
            padding: 2px;
            margin-left: 8px;
        }

        .card-pricing-recommended .card-pricing-plan-tag {
            margin: unset !important;
        }

        .card-pricing-recommended .card-pricing-plan-tag {
            background-color: rgb(114 124 245);
            color: #ffffff;
        }

        .label_color {
            background: -webkit-linear-gradient(#eb6294, #4f3c82);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .container {
            justify-content: center;
            align-items: center;
            display: flex;
            height: 100%;
            text-align: center;
        }

        .button:hover {
            background-color: #9da2dd;
            color: grey;
        }

        /*apply css properties to h2 tag*/

        h2 {
            color: black;
            margin: 0 50px;
            font-size: 45px;
        }

        /*apply css properties to h1 tag*/

        h1 {
            font-size: 35px;
            color: green;
            text-align: center;
            padding-left: 10%;
        }

        /* add user popup css */
        .increment_decrement_btn {
            border: none;
            width: 28px;
            height: 28px;
            margin: 10px 9px;
            border-radius: 50px;
            padding: 2px 6px;
            font-size: 17px;
            background-color: #727cf5;
            color: honeydew;
        }

        .increment_decrement_div {
            justify-content: center;
            align-items: center;
            display: flex;
            height: 100%;
            text-align: center;
        }


        .increment_decrement_btn:hover {
            color: #727cf5;
            border: 1px solid #727cf5;
            background: white;
        }

        h2 {
            color: black;
            margin: 0 50px;
            font-size: 45px;
        }

        h1 {
            font-size: 35px;
            color: #727cf5;
            text-align: center;
            padding-left: 10%;
        }

        .increment_label {
            margin: 10px 10px 10px 10px;
        }

        .decrement_label {
            margin: 10px 10px 10px 0px;
        }

        .counting_users {
            font-weight: 900;
            font-size: 20px;
        }

        .add_user_label_price {
            font-size: 19px;
            border-radius: 9px;
            height: 36px;
            /* width: 138px; */
            height: 39px;
            text-align: center;
            display: flex;
            justify-content: center;
        }

        .fw-16 {
            font-size: 16px;
        }

        .promo_code_price input {
            background: #F1F2FF;
            border: none;
            border-radius: 10px;
            width: 52%;
            padding-left: 10px;
        }

        .btn_save_popup {
            border: none;
            background: #727CF5;
            color: white;
            padding: 10px 30px;
            border-radius: 10px;
            font-size: 17px;

        }

        .btn_final_price_upgrade_plan {
            border: none;
            /* background: #727CF5; */
            background-color: #f1f2ff;
            border-color: #727cf5;
            color: black;
            /* padding: 10px 30px; */
            border-radius: 10px;
            font-size: 17px;
            text-align: center;
            justify-content: center;
        }

        .btn_close_popup {
            border: none;
            background: #F1F2FF;
            padding: 10px 30px;
            border-radius: 10px;
            font-size: 17px;

        }

        input:focus-visible {
            outline: none;
        }

        #popup_model_user {
            background: #F1F2FF;
        }

        #popup_model_user div {
            font-weight: 700;
            font-size: 18px;
        }

        .total_label {
            font-size: 19px;
            width: 138px;
            height: 39px;
            border-radius: 9px;
            text-align: center;
            justify-content: center;
            background: #f1f2ff;
            padding: 10px 30px;
        }

        .mdi-currency-inr {
            font-size: 20px;
        }
    </style>
@endpush
<div class="border p-3 mt-4 mt-lg-0 rounded">
    <h4 class="header-title mb-3">Order Summary</h4>

    <div class="table-responsive">
        <table class="table table-nowrap table-centered mb-0">
            <tbody>
                @if ($data['add_user'] == 1)
                    <tr>
                        <td>
                            <div class="add_users">
                                <div class="text-dark fw-bold fw-16">Pro Plan</div>
                                <div class="d-flex">
                                    <label for="" class="first_label">(
                                        {{ $planData->user_limit ? $planData->user_limit : 1 }}
                                        users * 1
                                        year)</label>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label
                                    class="add_user_label_price d-flex align-items-center first_value">{{ $planData->yearly_price }}
                                </label>

                            </div>
                        </td>
                    </tr>
                    <input type="hidden" name="addUsers" id="addUsers" value="0">
                    {{-- <input type="hidden" id="plan_id" name="plan_id" value="0"> --}}


                    <tr>
                        <td>
                            <div class="mb-1">
                                <div class="text-dark fw-bold fw-16">Additional Users <label style="color: #6c757d;">(1
                                        Year)</label></div>
                                <div class="promo_code_price d-flex">
                                    <div class="add_users">
                                        <div class="d-flex">
                                            <div class="increment_decrement_div">
                                                <button type="button" class="increment_decrement_btn decrement_label"
                                                    onclick="decrement()">-</button>
                                                <span id="counting" class="counting_users">0</span>
                                                <button type="button" class="increment_decrement_btn increment_label"
                                                    onclick="increment()">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-dark fw-bold fw-16">
                                    <small>( <i class="mdi mdi-currency-inr" style="font-size: 0.75rem;"></i> 10.96 /
                                        user / day ) </small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="add_user_label_price d-flex align-items-center" id="add_user_price_show">0
                                </label>

                            </div>
                        </td>
                    </tr>

                    <tr class="text-end">
                        <td>
                            <h6 class="m-0">
                                <div class="input-group mt-3">
                                    <input type="text" class="form-control" id="promo_code_check"
                                        placeholder="Coupon code" aria-label="Recipient's username">
                                    <button class="input-group-text btn-light" onclick="checkPromoCode()"
                                        type="button">Apply</button>
                                </div>
                            </h6>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="add_user_label_price d-flex align-items-center promoCode mr-2">0 </label>

                            </div>
                        </td>
                    </tr>
                    <tr class="text-end">
                        <td>
                            <h5 class="m-0">Sub Total:</h5>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="d-flex align-items-center total_price">{{ $planData->yearly_price }}
                                </label>

                            </div>
                        </td>
                    </tr>

                    <tr class="text-end">
                        <td>
                            <h5 class="m-0">GST 18% :</h6>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="add_user_label_price d-flex align-items-center" id="gst_value">0 </label>

                            </div>
                        </td>
                    </tr>
                    <tr class="text-end">
                        <td>
                            <h5 class="m-0 ">Total:</h5>
                        </td>
                        <td class="d-flex justify-content-end text-end fw-semibold">
                            <div class="d-flex justify-content-end align-items-center mr-2 total_label">
                                <i class="mdi mdi-currency-inr"></i>
                                <label
                                    class="d-flex align-items-center btn_final_price_upgrade_plan final_price">{{ $planData->yearly_price }}
                                </label>

                            </div>
                        </td>
                    </tr>
                @else
                    <input type="hidden" name="addUsers" id="addUsers" value="1">


                    @php
                        $planEndDate = strtotime($user->plan_end_date);
                        $difference = $planEndDate - time();
                    @endphp
                    <tr>
                        <td>
                            <div class="mb-1">
                                <div class="text-dark fw-bold fw-16">Add Users</div>
                                <div class="d-flex">
                                    <div class="increment_decrement_div">
                                        <button type="button"
                                            class="increment_decrement_btn decrement_user decrement_label"
                                            onclick="decrementusers()" disabled> - </button>

                                        <span id="countingUsers" class="counting_users">1</span>
                                        <button type="button"
                                            class="increment_decrement_btn increment_user increment_label"
                                            onclick="incrementUsers()">
                                            + </button>

                                        <small> <i class="mdi mdi-close" style="font-size: 0.75rem;"></i>
                                            {{ round($difference / 86400) }}
                                            remaining days</small>
                                    </div>
                                </div>

                                <div class="text-dark fw-bold fw-16">
                                    <small>( <i class="mdi mdi-currency-inr" style="font-size: 0.75rem;"></i> 10.96 /
                                        user / day )</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="add_user_label_price d-flex align-items-center" id="userPrice">0
                                </label>

                            </div>
                        </td>
                    </tr>

                    <tr class="text-end">
                        <td>
                            <h5 class="m-0">
                                <div class="input-group mt-3">
                                    <input type="text" class="form-control" id="add_user_promo_code"
                                        placeholder="Promo code">
                                    <button class="input-group-text btn-light" onclick="addUserPromoCodeCheck()"
                                        type="button">Apply</button>
                                </div>
                            </h5>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="add_user_label_price d-flex align-items-center userPromoCode">0 </label>

                            </div>
                        </td>
                    </tr>
                    <tr class="text-end">
                        <td>
                            <h5 class="m-0">Sub Total:</h5>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="d-flex align-items-center total_user_price">0 </label>

                            </div>
                        </td>
                    </tr>
                    <tr class="text-end">
                        <td>
                            <h5 class="m-0">GST 18% :</h5>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label class="add_user_label_price d-flex align-items-center gstUserAmount">0 </label>

                            </div>
                        </td>
                    </tr>
                    <tr class="text-end">
                        <td>
                            <h5 class="m-0 ">Total:</h5>
                        </td>
                        <td class="text-end fw-semibold">
                            <div class="d-flex justify-content-end align-items-center mr-2">
                                <i class="mdi mdi-currency-inr"></i>
                                <label
                                    class="add_user_label_price d-flex align-items-center usersFinalPrice total_label">0
                                </label>

                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <input type="hidden" name="userCount" id="userCount" value="{{ $userCount }}">
    <input type="hidden" name="user" id="userData" value="{{ $user }}">
    <input type="hidden" name="plan_id" id="plan_id" value="{{ $planData->id }}">
    <input type="hidden" name="plan" id="plan" value="{{ $planData }}">
    <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
    <input type="hidden" id="addUser" name="add_user" value="0">
    <input type="hidden" id="payment" name="payment" value="10">
    <!-- end table-responsive -->
</div> <!-- end .border-->
@push('scripts')
    <!-- third party js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script>
        // upgrade plan calculation
        var data = 0;
        calculatePrice();

        function increment() {
            data = data + 1;
            if (data >= 1) {
                $('.decrement_label').attr("disabled", false);
            }
            document.getElementById("counting").innerText = data;
            var add_users_price = data * 4000;
            $('#add_user_price_show').text(add_users_price);
            $('#addUser').val(data);
            calculatePrice();

        }
        //creation of decrement function
        function decrement() {
            if (data <= 0) {
                $('.decrement_label').attr("disabled", true);
            } else {
                data = data - 1;
                document.getElementById("counting").innerText = data;
                var add_users_price = data * 4000;
                $('#add_user_price_show').text(add_users_price);
                $('#addUser').val(data);
                calculatePrice();
            }
        }

        function checkPromoCode() {
            var promo_code_check = $('#promo_code_check').val();
            let planPrice = parseFloat($('.first_value').text()).toFixed(2);
            let userprice = parseFloat($('#add_user_price_show').text()).toFixed(2);
            var totalAmount = parseFloat(planPrice) + parseFloat(userprice);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "GET",
                url: '{{ url('plan/check-promo-code') }}',
                data: {
                    'promo_code_check': promo_code_check,
                    'firth_value': totalAmount,
                },
                dataType: 'json',
                success: function(response_msg) {
                    if (response_msg.success == 1) {
                        toastrSuccess('Promo Code Apply Succefully...', 'Success');
                    }
                    if (response_msg.success == 2) {
                        toastrError('Promo Code Invalid...', 'Error');
                    }
                    var promoAmount = response_msg.price_total;
                    $('.promoCode').text(promoAmount);
                    calculatePrice();
                }
            });
        }

        function calculatePrice() {
            let planPrice = parseFloat($('.first_value').text()).toFixed(2);
            let userprice = parseFloat($('#add_user_price_show').text()).toFixed(2);
            let country = $('#billing-country').val();
            let promoCodeAmount = parseFloat($('.promoCode').text()).toFixed(2);
            let totalPrice = parseFloat(planPrice) + parseFloat(userprice);
            let subTotal = totalPrice;
            let taxAmount = 0;
            let finalAmount = 0;
            if (promoCodeAmount > 0) {
                subTotal = (totalPrice - promoCodeAmount).toFixed(2);
            }
            if (country == '101') {
                taxAmount = parseFloat((subTotal * 18) / 100).toFixed(2);
            }
            finalAmount = parseFloat(subTotal) + parseFloat(taxAmount);
            console.log(finalAmount, subTotal, taxAmount);
            $('.total_price').text(subTotal);
            $('#gst_value').text(taxAmount);
            $('.final_price').text(finalAmount.toFixed(2));
            console.log("Final AMount" + finalAmount.toFixed(2));
            $('#payment').val(finalAmount.toFixed(2));
            $('#addUser').val(data);
        }

        // add more users feature
        var user = 1;
        var userData = JSON.parse($('#userData').val());
        var startDate = new Date(new Date().getFullYear(), 0, 1);
        var endDate = new Date(new Date().getFullYear() + 1, 0, 1);
        var totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
        var perDayPrice = 4000 / totalDays;
        var futureDate = new Date(userData.plan_end_date);
        var currentDate = new Date();
        var differenceMs = futureDate - currentDate;
        var differenceDays = Math.ceil(differenceMs / (1000 * 60 * 60 * 24));
        var totalPerUserPrice = perDayPrice * differenceDays;
        var add_users_price = user * parseInt(totalPerUserPrice);
        $('#userPrice').text(add_users_price);
        var checkUser = $('#addUsers').val();
        if (checkUser == 1) {
            calculateUser();
        }


        function incrementUsers() {
            if (user >= 1) {
                $('.decrement_user').attr("disabled", false);
            }
            user = user + 1;
            document.getElementById("countingUsers").innerText = user;
            var add_users_price = user * parseInt(totalPerUserPrice);
            $('#userPrice').text(add_users_price.toFixed(2));
            $('#addUser').val(user);
            calculateUser();
        }

        function decrementusers() {
            if (user <= 0) {
                $('.decrement_user').attr("disabled", true);
            }
            if (user > 0) {
                user = user - 1;
                document.getElementById("countingUsers").innerText = user;
                var add_users_price = user * parseInt(totalPerUserPrice);
                $('#userPrice').text(add_users_price.toFixed(2));
                $('#addUser').val(user);
                calculateUser();
            }
        }

        function addUserPromoCodeCheck() {
            var promo_code_check = $('#add_user_promo_code').val();
            var finalAmount = parseFloat($('#userPrice').text()).toFixed(2);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "GET",
                url: '{{ url('plan/check-promo-code') }}',
                data: {
                    'promo_code_check': promo_code_check,
                    'firth_value': finalAmount,
                },
                dataType: 'json',
                success: function(response_msg) {
                    if (response_msg.success == 1) {
                        toastrSuccess('Promo Code Apply Succefully...', 'Success');
                    }
                    if (response_msg.success == 2) {
                        toastrError('Promo Code Invalid...', 'Error');
                    }
                    promoPrice = response_msg.price_total;
                    $('.userPromoCode').text(promoPrice.toFixed(2));
                    calculateUser();
                }
            });
        }

        function calculateUser() {
            let price = parseFloat($('#userPrice').text()).toFixed(2);
            let country = $('#billing-country').val();
            let promoCodeAmount = parseFloat($('.userPromoCode').text()).toFixed(2);
            let subTotal = price;
            let taxAmount = 0;
            let finalAmount = 0;
            if (promoCodeAmount > 0) {
                subTotal = (price - promoCodeAmount).toFixed(2);
            }
            if (country == '101') {
                taxAmount = parseFloat((subTotal * 18) / 100).toFixed(2);
            }
            finalAmount = parseFloat(subTotal) + parseFloat(taxAmount);
            $('.total_user_price').text(subTotal);
            $('.gstUserAmount').text(taxAmount);
            $('.usersFinalPrice').text(finalAmount.toFixed(2));
            $('#payment').val(finalAmount.toFixed(2));
            $('#addUser').val(user);
        }
    </script>
@endpush
