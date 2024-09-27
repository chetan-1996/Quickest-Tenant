@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title', 'Plan')
@push('styles')
    <style>
        .mainheading {
            font-size: 35px;
            text-align: center;
            margin-bottom: 30px;
            margin-top: 20px;
            font-weight: 600;
        }

        .plan-main {
            margin-bottom: 30px;
        }

        .plan {
            border: 1px solid #d4d4d4;
            color: #94aab2;
            margin-bottom: 10%;
            background: white;
        }

        .plan-2 {
            border: 1px solid #7db9e8;
            /* color: #fff; */
            /* background: #7db9e8; */
            /* Old browsers */
            /* background: -moz-linear-gradient(top, #7db9e8 0%, #3399ff 57%, #00ccff 100%); */
            /* FF3.6-15 */
            /* background: -webkit-linear-gradient(top, #7db9e8 0%, #3399ff 57%, #00ccff 100%); */
            /* Chrome10-25,Safari5.1-6 */
            /* background: linear-gradient(to bottom, #7db9e8 0%, #3399ff 57%, #00ccff 100%); */
            /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db9e8', endColorstr='#00ccff', GradientType=0);
            margin-bottom: 10%;
            z-index: 3000;
            /* box-shadow: 0px 10px 40px 0px rgba(57, 11, 126, 0.2); */
            transition: 0.8s;
        }

        .plan-2 span p {
            background: #fca425;
        }

        .plan-3 span p {
            background: #fe3377;
        }

        .planheading {

            text-align: center;
            font-size: 18px;
            margin: 50px 0px 0px 0px;
        }

        .plansubheading {
            text-align: center;

        }

        .plansubheading p {
            margin: 0px 0px 15px 0px;
            padding: 13px;
            font-size: 20px;
        }

        .planfeatures {
            font-size: 18px;
            text-align: center;
        }

        .plan-2 .planbutton a {
            color: #fff;
        }

        .planbutton a {
            Display: inline-block;
            margin-bottom: 40px;
            margin-top: 25px;
            border: 1px solid #00ddff;
            padding: 10px 28px;
            color: #181124;
            font-size: 15px;
            border-radius: 3px;
            text-decoration: none;
        }

        .planbutton {
            text-align: center;
        }

        span p {
            background: #33dd88;
            width: auto;
            display: inline-block;
            padding: 8px 25px;
            border-radius: 43px;
            color: #fff;

        }

        .plan-2 .price {
            color: #fff;
        }

        .price {
            font-weight: 600;
            font-size: 25px;
            text-align: center;
            color: #181124;
        }

        .plan-2 .planbutton a:hover {
            background: #fff;
            color: #181124;
        }

        .promo-flag {
            position: absolute;
            top: 13.4rem;
            right: 39rem;
            width: 200px;
            height: 200px;
            overflow: hidden;
            margin-right: 15px;
            text-align: center;
        }

        .promo-flag div {
            background: #ffffff;
            color: #181124;
            font-size: 15px;
            width: 200px;
            padding: 4px;
            position: relative;
            right: -42px;
            top: 40px;
            transform: rotate(45deg);
        }

        .promo-flag1 {
            position: absolute;
            top: 13.5rem;
            right: 66.6rem;
            width: 200px;
            height: 200px;
            overflow: hidden;
            margin-right: 15px;
            text-align: center;
        }

        .promo-flag1 div {
            background: #ffffff;
            color: #181124;
            font-size: 15px;
            width: 200px;
            padding: 4px;
            position: relative;
            right: -42px;
            top: 40px;
            transform: rotate(45deg);
        }

        .plan-2-model {
            border: 1px solid #7db9e8;
            color: #fff;
            background: #7db9e8;
            /* Old browsers */
            background: -moz-linear-gradient(top, #7db9e8 0%, #3399ff 57%, #00ccff 100%);
            /* FF3.6-15 */
            background: -webkit-linear-gradient(top, #7db9e8 0%, #3399ff 57%, #00ccff 100%);
            /* Chrome10-25,Safari5.1-6 */
            background: linear-gradient(to bottom, #7db9e8 0%, #3399ff 57%, #00ccff 100%);
            /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db9e8', endColorstr='#00ccff', GradientType=0);
            z-index: 3000;
            box-shadow: 0px 10px 40px 0px rgba(57, 11, 126, 0.2);
            transition: 0.8s;
        }

        .plan-3-model {
            border: 1px solid #7db9e8;
            color: #fff;
            background: #33dd88;
            /* Old browsers */
            background: -moz-linear-gradient(top, #e59bb4 0%, #d65e86 57%, #8d3c57 100%);
            /* FF3.6-15 */
            background: -webkit-linear-gradient(top, #e59bb4 0%, #d65e86 57%, #8d3c57 100%);
            /* Chrome10-25,Safari5.1-6 */
            background: linear-gradient(to bottom, #e59bb4 0%, #d65e86 57%, #8d3c57 100%);
            /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db9e8', endColorstr='#00ccff', GradientType=0);
            z-index: 3000;
            box-shadow: 0px 10px 40px 0px rgba(57, 11, 126, 0.2);
            transition: 0.8s;
        }

        .plan-1-model {
            border: 1px solid #7db9e8;
            color: #fff;
            background: #33dd88;
            /* Old browsers */
            background: -moz-linear-gradient(top, #279e63 0%, #31c179 57%, #33dd88 100%);
            /* FF3.6-15 */
            background: -webkit-linear-gradient(top, #279e63 0%, #31c179 57%, #33dd88 100%);
            /* Chrome10-25,Safari5.1-6 */
            background: linear-gradient(to bottom, #279e63 0%, #31c179 57%, #33dd88 100%);
            /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db9e8', endColorstr='#00ccff', GradientType=0);
            z-index: 3000;
            box-shadow: 0px 10px 40px 0px rgba(57, 11, 126, 0.2);
            transition: 0.8s;
        }

        .plan-1-model .price {
            color: black;
        }

        .plan-2-model .price {
            color: black;
        }

        .plan-3-model .price {
            color: black;
        }

        .planheading_model {
            text-align: center;
            font-size: 18px;
            margin: 30px 0px 0px 0px;
        }

        .plansubheading_model {
            font-size: 20px;
            text-align: center;
        }

        .planfeatures_model {
            font-size: 18px;
            text-align: center;
        }

        .plan-3-model span p {
            background: #fe3377;
        }

        .plan-2-model span p {
            background: #fca425;
        }

        .planheading_model_3 {
            text-align: center;
            font-size: 18px;
            margin: 30px 0px 0px 0px;
        }

        .plan-model-toggle {
            position: absolute;
            top: 123px;
            left: 48px;
        }

        .btn.btn-primary.plan-model-toggle {
            background: none;
            border: 1px solid #00ddff;
            padding: 10px 28px;
            color: #181124;
            font-size: 15px;
            border-radius: 3px;
            font-weight: 700;
        }

        .btn.btn-primary.plan-model-toggle:hover {
            background: #fff;
            color: #181124;
        }

        .btn.btn-primary.plan-model-toggle {
            display: none;
        }

        p {
            color: black;
        }

        @media only screen and (max-width: 480px) {
            .mainheading {
                font-size: 30px;
                margin-bottom: 10px;
                margin-top: 10px;
            }

            .planfeatures {
                display: none;
            }

            .plansubheading {
                display: none;
            }

            .planheading {
                margin: 10px 0px 0px 0px;
            }

            .planbutton a {
                margin-top: 0px;
                margin-bottom: 10px;
            }

            .plan,
            plan-2,
            plan-3 {
                margin-bottom: 6%;
            }

            .plan-2 {
                box-shadow: 0px 2px 20px 0px rgba(57, 11, 126, 0.2);
            }

            .promo-flag div {
                font-size: 16px;
                position: relative;
                right: -42px;
                top: 30px;
                transform: rotate(45deg);
            }

            .promo-flag {
                position: absolute;
                top: 0px;
                right: 0px;
                width: 181px;
                height: 200px;
                overflow: hidden;
                margin-right: 15px;
                text-align: center;
                background-color: red;
            }

            .promo-flag1 div {
                font-size: 16px;
                position: relative;
                right: -42px;
                top: 30px;
                transform: rotate(45deg);
            }

            .promo-flag1 {
                position: absolute;
                top: 0px;
                right: 0px;
                width: 181px;
                height: 200px;
                overflow: hidden;
                margin-right: 15px;
                text-align: center;
            }

            .planbutton {
                text-align: right;
                margin-right: 30px;
            }

            .btn.btn-primary.plan-model-toggle {
                display: block;
            }
        }


        @media only screen and (max-width: 480px) {
            .faq-page {
                padding: 10px 10px;

                .quick-messege {
                    text-align: center;

                    h3 {
                        margin-bottom: 15px;
                        display: none;
                    }

                    h2 {
                        display: block !important;
                    }

                    h5 {
                        color: #6b5a5a;
                        text-align: left;
                        display: none;
                    }
                }

                .accordion {
                    margin-top: 20px;

                    .item {
                        height: auto;
                        margin-bottom: 10px;

                        i.fas.fa-question-circle {
                            font-size: 30px;
                        }

                        h3 {
                            margin-left: 58px;
                            margin-top: -80px;
                            font-size: 16px;
                        }
                    }

                    p {
                        padding-left: 20px;
                        padding-bottom: 10px;
                        padding-right: 10px;
                        font-size: 14px;
                        margin-top: -20px;
                        margin-bottom: 10px;
                    }

                }

                .message-area {
                    height: auto;

                    form {
                        padding: 25px;

                        select {
                            display: none;
                        }
                    }
                }
            }
        }

        .faq-page {
            padding: 50px 157px;
            background: #f5f9fd;

            .accordion {
                margin-top: 40px;
                /*width: 554px;*/
                cursor: pointer;
                margin-bottom: 40px;

                .item {
                    height: 100px;

                    i.fas.fa-question-circle {
                        font-size: 50px;
                        padding: 17px;
                        margin-top: 10px;
                        color: #2d54f2;

                    }

                    h3 {
                        display: inline-block;
                        vertical-align: middle;
                        height: 100%;
                        font-family: $mainfont;
                        font-size: 20px;
                        font-weight: 400;
                        margin-top: -17px;
                    }

                    img {
                        padding-left: 15px;
                        vertical-align: middle;
                    }

                    h3:before {
                        content: "";
                        display: inline-block;
                        vertical-align: middle;
                        height: 100%;
                    }
                }

                .active {
                    background: $bluegrd !important;
                    color: $white !important;
                    border-bottom: 1px solid #2d54f2 !important;

                    i.fas.fa-question-circle {
                        color: #fff !important;
                    }
                }

                .item {
                    background: $white;
                    color: #181124;
                    border-bottom: $border;
                }

                .p-active {
                    display: block;
                }

                p {
                    font-family: $mainfont;
                    font-size: 18px;
                    font-weight: 400;
                    padding-left: 90px;
                    padding-bottom: 30px;
                    padding-right: 10px;
                    display: none;
                    /* background: $bluegrd; */
                    color: $white;
                    margin-bottom: 0rem;
                }

            }

            .message-area {
                margin-top: 40px;
                background: #fff;
                height: 610px;

                form {
                    padding: 40px;

                    input[type=text],
                    select,
                    textarea {
                        width: 100%;
                        padding: 12px;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                        box-sizing: border-box;
                        margin-top: 6px;
                        margin-bottom: 16px;
                        resize: vertical;
                        background: #f5f9fd;
                    }

                }
            }
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

        /*apply css properties to button tag*/

        /* .button {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                width: 90px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                height: 60px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                font-size: 30px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                background-color: #727cf5;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                color: honeydew;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            } */

        /*apply hover effect to button tag*/

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
            color: black;
            display: flex;
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

        .increment_decrement_btn {
            border: none;
            width: 20px;
            height: 20px;
            font-size: 13px;
            border-radius: 5px;
            background-color: #727cf5;
            color: honeydew;
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
            margin: 10px 10px 10px 0;
        }

        .decrement_label {
            margin: 10px 10px 10px 10px;
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
            background-color: #727cf5;
            border-color: #727cf5;
            color: white;
            padding: 10px 30px;
            border-radius: 10px;
            font-size: 17px;
            width: 138px;
            height: 39px;
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

        .upgrade_plan_total_label {
            font-size: 19px;
            width: 138px;
            height: 39px;
            border-radius: 9px;
            text-align: center;
            justify-content: center;
            background: #f1f2ff;
        }
    </style>
@endpush
@section('content')
    <!-- start page title -->
    {{-- <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-left pt-2">
                    <h4 class="page-title">Plan</h4>
                </div>
            </div>
        </div>
    </div> --}}
    <!-- end page title -->
    <div class="container">

        @if (session('success'))
            <div class="alert alert-success" role="alert"> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert"> {{ session('error') }}
            </div>
        @endif
    </div>
    <div class="row justify-content-center">
        <div class="col-xxl-10">

            <!-- Pricing Title-->
            @if ($user->plan_id == 1)
                <div class="text-center">
                    <h3 class="mb-2">Our Plans and Pricing</h3>
                    <p class="text-muted w-50 m-auto">
                        We have plans and prices that fit your business perfectly. Make your client site a success with our
                        products.
                    </p>
                </div>
            @else
                <div class="text-center">
                    <h3 class="mb-2">Currently Active Plan</h3>
                </div>
            @endif
            <!-- Plans -->

            <div class="row mt-sm-5 mt-3 mb-3 justify-content-center">
                @if ($user->plan_id != 1 && $companyData->popupStatus == 2)
                    {{-- @if ($user->plan_id != 1 || isset($user->comapny_id)) --}}
                    <div class="col-md-3">
                        <div class="card card-pricing card-pricing-recommended ribbon-box">
                            <div class="card-body p-0 text-center">
                                <div style="background-color: #e0e3fd;padding: 21px 10px;">
                                    <p class="card-pricing-plan-name fs-3 fw-bold text-uppercase label_color"
                                        style="font: caption;">
                                        {{ $activePlan->name }}</p>
                                    <i
                                        class="card-pricing-icon avatar-title bg-primary-lighten text-primary rounded-circle mdi mdi-rocket-launch-outline text-primary"></i>
                                    {{-- <h2 class="card-pricing-price float-center text-dark mt-0 m-0">
                                        Rs.{{ $activePlan->yearly_price }}
                                        <span class="text-dark">/ Year</span>
                                    </h2> --}}
                                </div>
                                <div style="padding: 0 10px 10px 10px;">
                                    <ul class="card-pricing-features" style="text-align:left;">
                                        <li class="text-dark ">
                                            <i class="mdi mdi-check-decagram-outline text-primary"
                                                style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                            Unlimited lead access
                                        </li>
                                        <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                            Unlimited Estimate
                                        </li>
                                        <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                            Unlimited product
                                        </li>
                                        <li class="text-dark ">
                                            <i class="mdi mdi-check-decagram-outline text-primary"
                                                style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                            Unlimited items
                                        </li>
                                        <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                            Customise your template
                                        </li>
                                        <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                            Unlimited files / Message template
                                        </li>
                                    </ul>
                                </div>
                                <hr>
                                <div style="padding: 0 10px 10px 10px;">
                                    <ul class="card-pricing-features" style="text-align:left;">
                                        <li class="text-dark ">
                                            Expire On:
                                            {{ \Carbon\Carbon::parse($user->plan_end_date)->format('d M Y') }}
                                        </li>
                                    </ul>
                                </div>
                                <form action="{{ url($segment.'/payment-checkout') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $activePlan }}">
                                    <input type="hidden" name="plan_id" value="{{ $activePlan->id }}">
                                    <input type="hidden" name="add_user" value="0">
                                    <button type="submit" class="btn btn-outline-primary mt-4 mb-2 fw-bold">Add More
                                        Users</button>
                                </form>
                            </div>
                        </div> <!-- end Pricing_card -->
                    </div>
                @else
                    @foreach ($plans as $plan)
                        <input type="hidden" name="yearly_price" id="yearly_price" value="{{ $plan->yearly_price }}">
                        <div class="col-md-3">
                            <div
                                class="card card-pricing {{ $plan->id == 2 ? 'card-pricing-recommended' : 'card-pricing' }} {{ $user->plan_id == $plan->id ? 'ribbon-box' : '' }}">
                                <div class="card-body p-0 text-center">

                                    @if ($plan->id == 2)
                                        <div class="card-pricing-plan-tag">Recommended</div>
                                    @endif
                                    @if ($user->plan_id == $plan->id)
                                        <div class="ribbon ribbon-warning bg-warning text-light float-end"
                                            style="background: #3a966e !important;margin-right: -7px;margin-top: 0.9rem;">
                                            <i class="mdi mdi-checkbox-marked-circle-outline"></i><span> Active</span>
                                        </div>
                                    @endif
                                    <div style="background-color: #e0e3fd;padding: 21px 10px;">
                                        <p class="card-pricing-plan-name fs-3 fw-bold text-uppercase label_color"
                                            style="{{ $plan->id == $user->plan_id ? 'padding-left: 3.5rem;' : '' }}font: caption;">
                                            {{ $plan->name }}</p>
                                        <i
                                            class="card-pricing-icon avatar-title bg-primary-lighten text-primary rounded-circle mdi {{ $plan->id == 2 ? 'mdi-rocket-launch-outline' : 'mdi-send' }} text-primary"></i>
                                        <h3 class="card-pricing-price float-center text-dark mt-0 m-0">
                                            Rs.{{ $plan->id == 2 ? $plan->yearly_price : 0 }}
                                            <span class="text-dark">/ Year
                                                {{ $plan->id == 2 ? '(For 1 users)' : '' }}</span>
                                        </h3>
                                    </div>

                                    <div style="padding: 0 10px 10px 10px;">
                                        @if ($plan->id == 1)
                                            <ul class="card-pricing-features" style="text-align:left;height: 425px;">
                                                <li class="text-dark ">
                                                    <i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    10 estimate / month
                                                </li>
                                                <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Limited access to all feature
                                                </li>
                                            </ul>
                                        @else
                                            <ul class="card-pricing-features" style="text-align:left;">
                                                <li class="text-dark ">
                                                    <i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Unlimited estimate
                                                </li>
                                                <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Unlimited Leads
                                                </li>
                                                <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Unlimited Message/Files
                                                </li>
                                                <li class="text-dark ">
                                                    <i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Unlimited items
                                                </li>
                                                <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Customise Template
                                                </li>
                                                <li class="text-dark"><i class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Live Video Support
                                                </li>
                                                <li class="text-dark"><i
                                                        class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Access to your reports
                                                </li>
                                                <li class="text-dark"><i
                                                        class="mdi mdi-check-decagram-outline text-primary"
                                                        style="font-size: 1.2rem;margin: 0px 12px 0 7px;"> </i>
                                                    Integration with other app
                                                </li>
                                            </ul>
                                        @endif
                                    </div>
                                    @if ($user->plan_id == $plan->id && $user->plan_id == 2 && $user->popupStatus == 2)
                                        <form action="{{ url($segment.'/payment-checkout') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="plan" value="{{ $plan }}">
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <input type="hidden" name="add_user" value="0">
                                            <button type="submit" class="btn btn-outline-primary mt-4 mb-2 fw-bold">Add
                                                Users</button>
                                        </form>
                                    @elseif($plan->id == 2)
                                        <form action="{{ url($segment.'/payment-checkout') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="plan" value="{{ $plan }}">
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <input type="hidden" name="add_user" value="1">
                                            <button type="submit"
                                                class="btn btn-outline-primary mt-2 mb-4 fw-bold">Choose
                                                Plan</button>
                                        </form>
                                    @endif
                                </div>

                            </div> <!-- end Pricing_card -->
                        </div> <!-- end col -->
                    @endforeach
                @endif
            </div>
            <!-- end row -->

        </div> <!-- end col-->
    </div>

    <style>
        .qwer {
            display: none;
        }
    </style>
    <div id="plan-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light" id="popup_model_user">
                    <h3 class="modal-title text-dark">Upgrade Your Plan</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0 m-2">
                    <form action="{{ url('pay') }}" id="planUpgrade" method="post">
                        @csrf
                        <input type="hidden" name="addUsers" value="0">
                        <input type="hidden" id="plan_id" name="plan_id" value="0">
                        <input type="hidden" id="payment" name="payment" value="0">
                        <input type="hidden" id="addUser" name="add_user" value="0">
                        <div class="main_div_plan_upgrade">
                            <div class="d-flex justify-content-between">
                                <div class="add_users">
                                    <div class="text-dark fw-bold fw-16">Pro Plan</div>
                                    <div class="d-flex">
                                        <label for="" class="first_label">(3 users * 1 year)</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mr-2">
                                    <label class="add_user_label_price d-flex align-items-center first_value">00</label>
                                    <img class="d-flex align-items-center"
                                        src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                        style="height: 18px;">
                                </div>
                            </div>
                        </div>
                        <hr>

                        <div class="d-flex justify-content-between">
                            <div class="add_users">
                                <div class="text-dark fw-bold fw-16">Curren Active Users</div>
                                <div class="d-flex">
                                    <label for="" class="second_label">(7 users * 1 year)</label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="add_user_label_price d-flex align-items-center second_value">000</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <div class="text-dark fw-bold fw-16">Additional Users</div>
                            <div>(1 Year)</div>

                        </div>
                        <div class="promo_code_price d-flex justify-content-between">
                            <div class="add_users">

                                <div class="d-flex">
                                    <div class="increment_decrement_div">

                                        <button type="button" class="increment_decrement_btn increment_label"
                                            onclick="increment()">+</button>
                                        <span id="counting"></span>
                                        <button type="button" class="increment_decrement_btn decrement_label"
                                            onclick="decrement()">-</button>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="add_user_label_price d-flex align-items-center"
                                    id="add_user_price_show">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <div class="text-dark fw-bold fw-16 d-flex align-items-center">Total</div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="upgrade_plan_total_label d-flex align-items-center total_price">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <div class="text-dark fw-bold fw-16 d-flex align-items-center">GST 18%</div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="add_user_label_price d-flex align-items-center" id="gst_value">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        <hr>
                        <div class="text-dark fw-bold fw-16">Promo Code</div>
                        <div class="promo_code_price d-flex justify-content-between">
                            <input type="text" id="promo_code_check" placeholder="Enter Promo Code">
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="add_user_label_price d-flex align-items-center promoCode mr-2">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        <hr>
                        <div class="final_price_div d-flex justify-content-between">
                            <div class="text-dark fw-bold fw-16 d-flex align-items-center">Final Price</div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label
                                    class="d-flex align-items-center btn_final_price_upgrade_plan final_price">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        {{-- <hr> --}}
                        {{-- <div class="button_save_close d-flex justify-content-around">
                            <button type="submit" class="btn_save_popup">Save</button>
                            <button type="button" class="btn_close_popup" data-bs-dismiss="modal">Close</button>
                        </div> --}}
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary fullscreen" form="planUpgrade" id="user_button" type="submit">
                            <i class="uil-arrow-circle-right"></i> Pay
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <div id="user-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div id="popup_model_user" class="modal-header border-1">
                    <div class="modal-title text-dark">Increase Users
                        Limite</div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form action="{{ url('pay') }}" id="increaseUsers" method="post">
                        @csrf
                        <input type="hidden" name="addUsers" value="1">
                        <input type="hidden" id="planId" name="plan_id" value="0">
                        <input type="hidden" id="paymentUsers" name="payment" value="0">
                        <input type="hidden" id="users" name="user" value="0">
                        <div class="main_div_plan">
                            <div class="add_user_div d-flex justify-content-between">
                                <div class="add_users">
                                    <div class="text-dark fw-bold fw-16">Add Users</div>
                                    <div class="d-flex">
                                        <div class="increment_decrement_div">
                                            <button type="button"
                                                class="increment_decrement_btn increment_user increment_label"
                                                onclick="incrementUsers()">
                                                + </button>
                                            <span id="countingUsers">0</span>
                                            <button type="button"
                                                class="increment_decrement_btn decrement_user decrement_label"
                                                onclick="decrementusers()" disabled> - </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mr-2">
                                    <label class="add_user_label_price d-flex align-items-center"
                                        id="userPrice">00</label>
                                    <img class="d-flex align-items-center"
                                        src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                        style="height: 18px;">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="gst_main_div d-flex justify-content-between">
                            <div class="text-dark fw-bold fw-16 d-flex align-items-center">GST 18%</div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="add_user_label_price d-flex align-items-center gstUserAmount">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        <hr>
                        <div class="promo_code_div">
                            <div class="text-dark fw-bold fw-16 mb-2">
                                Promo Code
                            </div>
                            <div class="promo_code_price d-flex justify-content-between">
                                <input type="text" id="add_user_promo_code" placeholder="Enter Promo Code">
                                <div class="d-flex justify-content-between align-items-center mr-2">
                                    <label class="add_user_label_price d-flex align-items-center userPromoCode">00</label>
                                    <img class="d-flex align-items-center"
                                        src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                        style="height: 18px;">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="final_price_div d-flex justify-content-between">
                            <div class="text-dark fw-bold fw-16 d-flex align-items-center">Final Price</div>
                            <div class="d-flex justify-content-between align-items-center mr-2">
                                <label class="add_user_label_price d-flex align-items-center usersFinalPrice">00</label>
                                <img class="d-flex align-items-center"
                                    src="{{ asset('assets/images/payments/rupee-indian.png') }}" alt=""
                                    style="height: 18px;">
                            </div>
                        </div>
                        {{-- <hr> --}}
                        {{-- <div class="button_save_close d-flex justify-content-around">
                            <button type="submit" class="btn_save_popup">Save</button>
                            <button type="button" class="btn_close_popup" data-bs-dismiss="modal">Close</button>
                        </div> --}}
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary fullscreen" form="increaseUsers" id="user_button" type="submit">
                            <i class="uil-arrow-circle-right"></i> Pay
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <input type="hidden" name="userCount" id="userCount" value="{{ $userCount }}">
    <input type="hidden" name="user" id="userData" value="{{ $user }}">
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
@endpush
