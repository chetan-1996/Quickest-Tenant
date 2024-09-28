@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
    use Carbon\Carbon;
    use App\Models\{Estimate, PlanHistory};
@endphp
@extends('app.layouts.app')
@section('title', 'Lead Timeline')
@push('styles')
    <link href="{{ asset('vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/virtual-select.min.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /*section {*/
        /*    margin: 2rem auto;*/
        /*    padding: 0.5rem 2.5rem;*/
        /*}*/
        #timeline-info {
            height: 530px; /* Set the height of the div */
            overflow-y: auto; /* Enable vertical scrolling */
        }
        .swatch {
            position: relative;
            margin: 0.5rem;
            width: 60px;
            height: 60px;
            /*border-radius: 60px;*/
            line-height: 60px;
            display: inline-block;
        }

        .swatch > [type=radio],
        .swatch > [type=checkbox] {
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .swatch > [type=radio] + label,
        .swatch > [type=checkbox] + label {
            width: 60px;
            height: 60px;
            /*border-radius: 60px;*/
            line-height: 60px;
            text-align: center;
            position: absolute;
            transition: all 0.5s ease-in-out;
        }

        .swatch > [type=radio] + label i,
        .swatch > [type=checkbox] + label i {
            opacity: 0;
            font-size: 2rem;
            transition: opacity 0.5s;
        }

        .swatch > [type=radio]:checked + label i,
        .swatch > [type=checkbox]:checked + label i {
            opacity: 1;
        }

        .swatch.orange > [type=radio] + label,
        .swatch.orange > [type=checkbox] + label {
            background-color: #e67e22;
            color: #fff;
        }

        .swatch.red > [type=radio] + label,
        .swatch.red > [type=checkbox] + label {
            background-color: #e74c3c;
            color: #fff;
        }

        .swatch.yellow > [type=radio] + label,
        .swatch.yellow > [type=checkbox] + label {
            background-color: #f1c40f;
            color: #fff;
        }

        .swatch.purple > [type=radio] + label,
        .swatch.purple > [type=checkbox] + label {
            background-color: #9b59b6;
            color: #fff;
        }

        .swatch.green > [type=radio] + label,
        .swatch.green > [type=checkbox] + label {
            background-color: #2ecc71;
            color: #fff;
        }

        .swatch.blue > [type=radio] + label,
        .swatch.blue > [type=checkbox] + label {
            background-color: #3498db;
            color: #fff;
        }

        .swatch > [type=radio]:checked + label,
        .swatch > [type=checkbox]:checked + label {
            /*width: 72px;*/
            /*height: 72px;*/
            /*border-radius: 72px;*/
            /*line-height: 72px;*/
            /*top: -6px;*/
            /*left: -6px;*/
            transition: all 0.5s ease-in-out;
        }

        .swatch > [type=radio]:checked + label i,
        .swatch > [type=checkbox]:checked + label i {
            opacity: 1;
            transition: opacity 0.5s;
        }


        form {
            /*max-width: 250px;*/
            position: relative;
            /*margin: 50px auto 0;*/
            font-size: 15px;
        }

        .radiobtn {
            position: relative;
            display: block;
        }

        .radiobtn label {
            display: block;
            background: #eef2f7;
            color: #444;
            border-radius: 2px;
            padding: 10px 20px;
            border: 1px solid #d5d5d5;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .radiobtn label:after,
        .radiobtn label:before {
            content: "";
            position: absolute;
            right: 11px;
            top: 14px;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            /*background: #d5d5d5;*/
            background: #eef2f7;
        }

        .radiobtn label:before {
            background: transparent;
            transition: 0.1s width cubic-bezier(0.075, 0.82, 0.165, 1) 0s, 0.3s height cubic-bezier(0.075, 0.82, 0.165, 2) 0.1s;
            z-index: 2;
            overflow: hidden;
            background-repeat: no-repeat;
            background-size: 13px;
            background-position: center;
            width: 0;
            height: 0;
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNS4zIDEzLjIiPiAgPHBhdGggZmlsbD0iI2ZmZiIgZD0iTTE0LjcuOGwtLjQtLjRhMS43IDEuNyAwIDAgMC0yLjMuMUw1LjIgOC4yIDMgNi40YTEuNyAxLjcgMCAwIDAtMi4zLjFMLjQgN2ExLjcgMS43IDAgMCAwIC4xIDIuM2wzLjggMy41YTEuNyAxLjcgMCAwIDAgMi40LS4xTDE1IDMuMWExLjcgMS43IDAgMCAwLS4yLTIuM3oiIGRhdGEtbmFtZT0iUGZhZCA0Ii8+PC9zdmc+);
        }

        .radiobtn input[type=radio] {
            display: none;
            position: absolute;
            width: 100%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .radiobtn input[type=radio]:checked + label {
            background: #eaf5ff;
            -webkit-animation-name: blink;
            animation-name: blink;
            -webkit-animation-duration: 1s;
            animation-duration: 1s;
            border-color: #727cf5;
        }

        .radiobtn input[type=radio]:checked + label:after {
            /*background: #d5d5d5;*/
            background: #727cf5;
        }

        .radiobtn input[type=radio]:checked + label:before {
            width: 20px;
            height: 20px;
        }

        #sample-select {
            max-width: 100% !important;
        }

        @-webkit-keyframes blink {
            0% {
                background-color: #eaf5ff;
            }

            10% {
                background-color: #eaf5ff;
            }

            11% {
                background-color: #eaf5ff;
            }

            29% {
                background-color: #eaf5ff;
            }

            30% {
                background-color: #eaf5ff;
            }

            50% {
                background-color: #eaf5ff;
            }

            45% {
                background-color: #eaf5ff;
            }

            50% {
                background-color: #eaf5ff;
            }

            100% {
                background-color: #eaf5ff;
            }
        }

        @keyframes blink {
            0% {
                background-color: #eaf5ff;
            }

            10% {
                background-color: #eaf5ff;
            }

            11% {
                background-color: #eaf5ff;
            }

            29% {
                background-color: #eaf5ff;
            }

            30% {
                background-color: #eaf5ff;
            }

            50% {
                background-color: #eaf5ff;
            }

            45% {
                background-color: #eaf5ff;
            }

            50% {
                background-color: #eaf5ff;
            }

            100% {
                background-color: #eaf5ff;
            }
        }


        .timeline-alt .timeline-item:before {
            background-color: #f1f3fa;
            bottom: 0;
            content: "";
            left: 14px;
            position: absolute;
            top: 31px;
            width: 2px;
            z-index: 0;
        }

        .timeline-alt .timeline-item .timeline-icon {
            float: left;
            height: 2rem;
            width: 2rem;
            border-radius: 50%;
            border: 2px solid transparent;
            font-size: 20px;
            text-align: center;
            line-height: 28px;
            background-color: #fff;
        }

        .timeline-alt .timeline-item .timeline-item-info {
            margin-left: 45px;
        }

        .parsley-error {
            animation: shake 0.8s;
            border-color: #B94A48;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }

        .attach-div,
        .description_td,
        .label_td,
        .lead_stage_td,.phone_no_copy,.email_copy {
            cursor: pointer;
        }

        #lead-table > :not(caption) > * > * {
            padding: 0rem 0rem;
        }

        /*.blink {
            animation: blink 2s steps(1, end) infinite;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }*/

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

        .accordion .accordion-button {
            font-weight: 500
        }

        .accordion .accordion-body {
            color: var(--vz-secondary-color)
        }

        .accordion.accordion-icon-none .accordion-button::after {
            content: "";
            background-image: none !important
        }

        .accordion.accordion-icon-none .accordion-button:not(.collapsed)::after {
            content: ""
        }

        .custom-accordionwithicon .accordion-button::after {
            background-image: none !important;
            font-family: "Material Design Icons";
            content: "\f0142";
            font-size: 1.1rem;
            vertical-align: middle;
            line-height: .8
        }

        .custom-accordionwithicon .accordion-button:not(.collapsed)::after {
            background-image: none !important;
            content: "\f0140";
            margin-right: -3px
        }

        .custom-accordionwithicon-plus .accordion-button::after {
            background-image: none !important;
            font-family: "Material Design Icons";
            content: "\f0415";
            font-size: 1.1rem;
            vertical-align: middle;
            line-height: .8
        }

        .custom-accordionwithicon-plus .accordion-button:not(.collapsed)::after {
            background-image: none !important;
            content: "\f0374";
            margin-right: -3px
        }

        .lefticon-accordion .accordion-button {
            padding-left: 2.75rem
        }

        .lefticon-accordion .accordion-button::after {
            position: absolute;
            left: 1.25rem;
            top: 14px
        }

        .lefticon-accordion .accordion-button:not(.collapsed)::after {
            top: 10px
        }

        .accordion-border-box .accordion-item {
            border-top: var(--vz-border-width) solid var(--vz-border-color);
            border-radius: var(--vz-border-radius)
        }

        .accordion-border-box .accordion-item:not(:first-of-type) {
            margin-top: 8px
        }

        .accordion-border-box .accordion-item .accordion-button {
            border-radius: var(--vz-border-radius)
        }

        .accordion-border-box .accordion-item .accordion-button:not(.collapsed) {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0
        }

        .custom-accordion-border .accordion-item {
            border-left: 3px solid var(--vz-border-color)
        }

        .accordion-primary .accordion-item {
            border-color: rgba(75, 56, 179, .6)
        }

        .accordion-primary .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-primary .accordion-item .accordion-button:not(.collapsed) {
            color: #4b38b3;
            background-color: rgba(75, 56, 179, .1) !important
        }

        .accordion-primary .accordion-item .accordion-button::after {
            color: #4b38b3
        }

        .accordion-fill-primary .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-primary .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #4b38b3 !important
        }

        .accordion-secondary .accordion-item {
            border-color: rgba(53, 119, 241, .6)
        }

        .accordion-secondary .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-secondary .accordion-item .accordion-button:not(.collapsed) {
            color: #3577f1;
            background-color: rgba(53, 119, 241, .1) !important
        }

        .accordion-secondary .accordion-item .accordion-button::after {
            color: #3577f1
        }

        .accordion-fill-secondary .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-secondary .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #3577f1 !important
        }

        .accordion-success .accordion-item {
            border-color: rgba(69, 203, 133, .6)
        }

        .accordion-success .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-success .accordion-item .accordion-button:not(.collapsed) {
            color: #45cb85;
            background-color: rgba(69, 203, 133, .1) !important
        }

        .accordion-success .accordion-item .accordion-button::after {
            color: #45cb85
        }

        .accordion-fill-success .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-success .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #45cb85 !important
        }

        .accordion-info .accordion-item {
            border-color: rgba(41, 156, 219, .6)
        }

        .accordion-info .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-info .accordion-item .accordion-button:not(.collapsed) {
            color: #299cdb;
            background-color: rgba(41, 156, 219, .1) !important
        }

        .accordion-info .accordion-item .accordion-button::after {
            color: #299cdb
        }

        .accordion-fill-info .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-info .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #299cdb !important
        }

        .accordion-warning .accordion-item {
            border-color: rgba(255, 190, 11, .6)
        }

        .accordion-warning .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-warning .accordion-item .accordion-button:not(.collapsed) {
            color: #ffbe0b;
            background-color: rgba(255, 190, 11, .1) !important
        }

        .accordion-warning .accordion-item .accordion-button::after {
            color: #ffbe0b
        }

        .accordion-fill-warning .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-warning .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #ffbe0b !important
        }

        .accordion-danger .accordion-item {
            border-color: rgba(240, 101, 72, .6)
        }

        .accordion-danger .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-danger .accordion-item .accordion-button:not(.collapsed) {
            color: #f06548;
            background-color: rgba(240, 101, 72, .1) !important
        }

        .accordion-danger .accordion-item .accordion-button::after {
            color: #f06548
        }

        .accordion-fill-danger .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-danger .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #f06548 !important
        }

        .accordion-light .accordion-item {
            border-color: rgba(243, 246, 249, .6)
        }

        .accordion-light .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-light .accordion-item .accordion-button:not(.collapsed) {
            color: #f3f6f9;
            background-color: rgba(243, 246, 249, .1) !important
        }

        .accordion-light .accordion-item .accordion-button::after {
            color: #f3f6f9
        }

        .accordion-fill-light .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-light .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #f3f6f9 !important
        }

        .accordion-dark .accordion-item {
            border-color: rgba(33, 37, 41, .6)
        }

        .accordion-dark .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-dark .accordion-item .accordion-button:not(.collapsed) {
            color: #212529;
            background-color: rgba(33, 37, 41, .1) !important
        }

        .accordion-dark .accordion-item .accordion-button::after {
            color: #212529
        }

        .accordion-fill-dark .accordion-item .accordion-button {
            -webkit-box-shadow: none;
            box-shadow: none
        }

        .accordion-fill-dark .accordion-item .accordion-button:not(.collapsed) {
            color: #fff;
            background-color: #212529 !important
        }

        [dir=rtl] .custom-accordionwithicon .accordion-button::after {
            -webkit-transform: rotate(180deg);
            transform: rotate(180deg)
        }

        .border-double {
            border-style: double !important
        }

        .border-top-double {
            border-top-style: double !important
        }

        .border-bottom-double {
            border-bottom-style: double !important
        }

        .border-end-double {
            border-right-style: double !important
        }

        .border-start-double {
            border-left-style: double !important
        }

        .list-group-flush.border-double {
            border: none !important
        }

        .list-group-flush.border-double .list-group-item {
            border-style: double !important
        }

        .border-dashed {
            border-style: dashed !important
        }

        .border-top-dashed {
            border-top-style: dashed !important
        }

        .border-bottom-dashed {
            border-bottom-style: dashed !important
        }

        .border-end-dashed {
            border-right-style: dashed !important
        }

        .border-start-dashed {
            border-left-style: dashed !important
        }

        .list-group-flush.border-dashed {
            border: none !important
        }

        .list-group-flush.border-dashed .list-group-item {
            border-style: dashed !important
        }

        .border-groove {
            border-style: groove !important
        }

        .border-top-groove {
            border-top-style: groove !important
        }

        .border-bottom-groove {
            border-bottom-style: groove !important
        }

        .border-end-groove {
            border-right-style: groove !important
        }

        .border-start-groove {
            border-left-style: groove !important
        }

        .list-group-flush.border-groove {
            border: none !important
        }

        .list-group-flush.border-groove .list-group-item {
            border-style: groove !important
        }

        .border-outset {
            border-style: outset !important
        }

        .border-top-outset {
            border-top-style: outset !important
        }

        .border-bottom-outset {
            border-bottom-style: outset !important
        }

        .accordion-button:not(.collapsed) {
            background-color: #fff;
            -webkit-box-shadow: inset 0 -1px 0 #6c757d;
            box-shadow: inset 0 -1px 0 rgb(108 117 125 / 21%);
        }

        /*.action_btn2 {
            display: flex;
            list-style: none;
            box-shadow: 0px 0px 5px #888;
            padding: 0;
            border-radius: 5px;
            z-index: 1;
        }
        .list2 {
            margin: 10px 0 10px 16px;
        }*/
        .lead-detail-icon {
            box-shadow: 0px 0px 3px #888;

        }

        .lead-detail-icon .widget-icon {
            font-size: 15px !important;
            height: 32px !important;
            width: 32px !important;
            line-height: 32px !important;
        }
    </style>
@endpush
@section('content')
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">
        <!-- start page title -->

            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h5 class="page-title fw-bold text-dark text-capitalize">
                                <small><a class="page-title" href="{{ route('tenant.customer.index', ['tenant' => $segment]) }}">Leads</a>
                                <i class="mdi mdi-greater-than"></i></small> Details{{--{{ $customers->name }}--}}
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @if (count($duplicateLeads) > 0)
                        <div class="col-12 mt-1 mb-2">
                            <h4 class="page-title fw-bold text-danger text-uppercase">Duplicate leads found
                                <small class="text-capitalize">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#duplicate-lead-modal"
                                    class="text-danger text-decoration-underline mb-4">View Details <i
                                            class="mdi mdi-greater-than"></i></a>
                                </small>
                            </h4>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <!-- Profile -->
                        <div class="card">
                            <div class="card-body profile-user-box">
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar-xl">
                                                <span class="avatar-title bg-light text-dark font-22 rounded-circle text-uppercase customer-sort-name">
                                                    @php
                                                        // Assuming $customers->name contains the input string
                                                        $words = preg_split('/\s+/u', $customers->name, -1, PREG_SPLIT_NO_EMPTY);

                                                        // Extract the first character of each word and concatenate them
                                                        $strArr = implode('', array_map(function ($word) {
                                                            return mb_substr($word, 0, 1, 'UTF-8');
                                                        }, array_slice($words, 0, 2)));
                                                    @endphp
                                                    {{$strArr}}
                                                </span>
                                                </div>
                                                {{-- <div class="avatar-lg">
                                                    <img src="{{asset('images/users/placeholder.png')}}" alt=""
                                                        class="rounded-circle img-thumbnail">
                                                </div>--}}
                                            </div>
                                            <div class="col">
                                                <div>
                                                    <h4 class="mt-1 mb-1 text-black font-20 name_small">{{ $customers->name }}</h4>
                                                    {{-- @if($customers->company_name)--}}
                                                    <p class="font-15 text-black-50 mb-1 first_company_name_small">
                                                        {{ $customers->company_name }}
                                                    </p>
                                                    {{-- @endif--}}
                                                    <p class="font-14 text-dark mb-1 csc_small">
                                                        {!!  $customers->city_name? $customers->city_name.' | ':'' !!}{!!  $customers->state_name? $customers->state_name.' | ':'' !!} {{ $customers->country_name }}

                                                    </p>

                                                    <p class="font-14 text-dark mb-1 merge_address_small">
                                                        <i class="mdi mdi-cellphone"></i> {{ $customers->country_code." ".$customers->phone_no }} {!!$customers->email?  '| <i class="mdi mdi-email-outline"></i> '.$customers->email:'' !!}  {!!  $customers->address? ' | <i class="mdi mdi-map-marker"></i> '.$customers->address:'' !!}
                                                    </p>

                                                    <ul class="mb-0 list-inline text-light">
                                                        <li class="list-inline-item me-2 w-25 border-end">
                                                            <h5 class="mb-1 text-black">Lead Source</h5>
                                                            <p class="mb-0 font-15 text-dark">{{ $customers->lead_origin ? $customers->lead_origin : 'Manually' }}</p>
                                                        </li>
                                                    {{-- <li class="list-inline-item">
                                                            <h5 class="mb-1 text-black">Date</h5>
                                                            <p class="mb-0 font-14 text-black-50">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->created_at)->format('d M Y  h:i A') }}</p>
                                                        </li>--}}
                                                        <li class="list-inline-item lead_stage_td me-2 w-25 border-end">
                                                            <h5 class="mb-1 text-black">Lead Stage</h5>
                                                            <p class="mb-0 font-13 text-black-50">
                                                                <small class="text-body lead_stage_small" title="Lead Stage">
                                                                    <span class="fs-6 badge me-1" style="background-color: {{ ($customers->lead_stage_color_code)?$customers->lead_stage_color_code:"#fff"; }};color: @if(!$customers->lead_stage_color_code)#000;@endif">
                                                                        {{ $customers->lead_stage_name ? $customers->lead_stage_name : '-' }}
                                                                    </span>
                                                                </small>
                                                            </p>
                                                        </li>
                                                        <li class="list-inline-item label_td">
                                                            <h5 class="mb-1 text-black">Labels</h5>
                                                            <p class="mb-0 font-13 text-black-50 first-label-span">
                                                                @foreach ($label_color as $key => $labelColor)
                                                                <span class="fs-6 badge me-1"
                                                                style="color:{{ $labelColor->color_code }};
                                                                    border: 1px solid {{ $labelColor->color_code }};
                                                                    background-color: transparent">
                                                                    {{ $labelColor->name }}</span>
                                                                @endforeach
                                                                @if(!$label_color) - @endif
                                                            </p>
                                                        </li>
                                                    </ul>
                                                    <p class="font-12 text-dark mb-0 mt-1">
                                                        Lead Created on <b class="text-dark">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->created_at)->format('d M Y  h:i A') }}</b>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- end col-->

                                    <div class="col-sm-3">
                                        <div class="page-title-box d-flex justify-content-between align-items-center float-end ">
                                            <div class="d-flex">
                                                <div class="d-flex align-items-center border border-light rounded p-1 mb-1 lead-detail-icon me-2">
                                                    <div class="flex-shrink-0 me-2 phone_no_copy" data-url="{{ $customers->country_code.$customers->phone_no }}">
                                                        <i class="mdi mdi-phone widget-icon rounded-circle bg-warning-lighten text-warning border border-warning"></i>
                                                    </div>
                                                    <div class="flex-shrink-0 me-2">
                                                        <a target="_blank" href="https://wa.me/{{ $customers->country_code.$customers->phone_no }}">
                                                        <i class="mdi mdi-whatsapp widget-icon rounded-circle bg-success-lighten text-success border border-success"></i>
                                                        </a>
                                                    </div>
                                                    <div class="flex-shrink-0 email_copy d-none" data-url="{{ $customers->email }}">
                                                        <i class="mdi mdi-email-outline widget-icon rounded-circle bg-secondary-lighten text-secondary border border-secondary"></i>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center border border-light rounded p-1 mb-1 lead-detail-icon">

                                                    @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                                                        {{--||auth()->user()->company_id == null--}}

                                                        @if ((in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) && $customers->assigned_to_user == auth()->user()->id)
                                                            {{--||auth()->user()->company_id == null--}}

                                                            <div class="flex-shrink-0 me-2">
                                                                <button type="button" class="btn btn-sm btn-primary" title="Edit" onclick="edit_id('{{ Request::segment(4) }}')">
                                                                    <i class="mdi mdi-lead-pencil"></i>
                                                                </button>
                                                            </div>
                                                            <!-- item-->
                                                            @if (in_array('give-access-to-delete-leads', $user_perm))

                                                                <div class="flex-shrink-0">
                                                                    <button type="button" class="btn btn-sm btn-dark" title="Delete Lead" onclick="remove_id('{{ Request::segment(4) }}','{{ route('tenant.customer.delete', ['tenant' => $segment]) }}')">
                                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <a href="javascript:void(0);"
                                                            onclick="OpenModalAssignLead(0,'#assign-lead-modal','#assign-lead-formModalLabel','Assign Lead','#assign-lead-form');"
                                                            class="btn btn-dark btn-sm">
                                                                Reassign
                                                            </a>
                                                        @endif
                                                    @else

                                                        @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                                                        in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('give-access-to-delete-leads', $user_perm))
                                                            {{--||auth()->user()->company_id == null--}}

                                                            @if ((in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('give-access-to-delete-leads', $user_perm)) && $customers->assigned_to_user == auth()->user()->id)
                                                            {{--||auth()->user()->company_id == null--}}
                                                            <div class="flex-shrink-0 me-2">
                                                                <button type="button" class="btn btn-sm btn-primary" title="Edit" onclick="edit_id('{{ Request::segment(4) }}')">
                                                                    <i class="mdi mdi-lead-pencil"></i>
                                                                </button>
                                                            </div>
                                                            <!-- item-->
                                                                @if (in_array('give-access-to-delete-leads', $user_perm))
                                                            <div class="flex-shrink-0">
                                                                <button type="button" class="btn btn-sm btn-dark" title="Delete Lead" onclick="remove_id('{{ Request::segment(4) }}','{{ route('tenant.customer.delete', ['tenant' => $segment]) }}')">
                                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                                </button>
                                                            </div>
                                                                @endif
                                                            @else
                                                                <div class="flex-shrink-0 me-2">
                                                                    <button type="button" class="btn btn-sm btn-primary" title="Edit" onclick="OpenModalAssignLead(0,'#assign-self-lead-modal','#assign-self-lead-formModalLabel','Assign to Self Lead','#assign-self-lead-form');">
                                                                        <i class="mdi mdi-lead-pencil"></i>
                                                                    </button>
                                                                </div>
                                                                <!-- item-->

                                                                @if (in_array('give-access-to-delete-leads', $user_perm))
                                                                <div class="flex-shrink-0">
                                                                    <button type="button" class="btn btn-sm btn-dark" title="Delete Lead" onclick="OpenModalAssignLead(0,'#assign-self-lead-modal','#assign-self-lead-formModalLabel','Assign to Self Lead','#assign-self-lead-form');">
                                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                                    </button>
                                                                </div>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @endif


                                                    {{--`
                                                    <div class="flex-shrink-0 me-2">
                                                        <button type="button" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="mdi mdi-lead-pencil"></i>
                                                        </button>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <button type="button" class="btn btn-sm btn-dark" title="Delete">
                                                            <i class="mdi mdi-trash-can-outline"></i>
                                                        </button>
                                                    </div>--}}
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- end col-->
                                    <hr class="mt-2">
                                    <div class="cols-12">
                                        <table class="table table-sm table-centered table-nowrap mb-0 p-0" id="lead-table" style="width:100px;">
                                            <tbody>
                                            <tr>
                                                <td style="max-width:30%;">

                                                    @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                                                        {{--|| auth()->user()->company_id == null--}}

                                                        @if (
                                                        (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) &&
                                                            $customers->assigned_to_user == auth()->user()->id)
                                                            )
                                                            {{--|| auth()->user()->company_id == null--}}
                                                            <a href="javascript:void(0);"
                                                            onclick="OpenModalAssignLead(0,'#assign-lead-modal','#assign-lead-formModalLabel','Assign Lead','#assign-lead-form');"
                                                            class="btn {{ $customers->assigned_to_user == auth()->user()->id ? 'btn-primary' : 'btn-secondary' }} text-uppercase text-left fw-bold mb-1 btn-sm"
                                                            style="text-align: left !important;width: fit-content;">{{ $customers->assigned_to_user == auth()->user()->id ? 'ASSIGNED TO YOU' : 'ASSIGNED TO ' . $customers->user_name }}
                                                                <i class="mdi mdi-greater-than"></i></a>
                                                        @else
                                                            <a href="javascript:void(0);"
                                                            onclick="OpenModalAssignLead(0,'#assign-self-lead-modal','#assign-self-lead-formModalLabel','Assign to Self Lead','#assign-self-lead-form');"
                                                            class="btn {{ $customers->assigned_to_user == auth()->user()->id ? 'btn-primary' : 'btn-secondary' }} text-uppercase text-left fw-bold mb-1 btn-sm"
                                                            style="text-align: left !important;width: fit-content;">{{ $customers->assigned_to_user == auth()->user()->id ? 'ASSIGNED TO YOU' : 'ASSIGNED TO ' . $customers->user_name }}
                                                                <i class="mdi mdi-greater-than"></i></a>
                                                        @endif
                                                    @else
                                                        <a href="javascript:void(0);"
                                                        onclick="accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the user assigned.');"
                                                        class="btn {{ $customers->assigned_to_user == auth()->user()->id ? 'btn-primary' : 'btn-secondary' }} text-uppercase text-left fw-bold mb-1 btn-sm"
                                                        style="text-align: left !important;width: fit-content;">{{ $customers->assigned_to_user == auth()->user()->id ? 'ASSIGNED TO YOU' : 'ASSIGNED TO ' . $customers->user_name }}
                                                            <i class="mdi mdi-greater-than"></i></a>
                                                    @endif

                                                </td>
                                                <td style="max-width:10%;">
                                                    @php
                                                        $day_cnt_flp = '';
                                                        $follow_up_msg = 'No follow up scheduled';
                                                        $tmp_style = 'width: fit-content;background: #ededed;border-color: #6c757d !important;color:#6c757d;display: block;border-radius: 2px;padding: 0.25rem 0.4rem;border: 1px solid #d5d5d5;cursor: pointer;margin-bottom:0.4rem';

                                                        if (!empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && $customers->last_follow_up_datetime && $customers->some_day_flg == 0) {
                                                        $day_cnt_flp = \Carbon\Carbon::parse($customers->last_follow_up_datetime)->diffInDays(date('Y-m-d'));
                                                        }

                                                        if (!empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && $customers->some_day_flg == 0) {
                                                        $follow_up_msg = 'Follow Up in ' . $day_cnt_flp . ' Days';
                                                        $tmp_style = 'width: fit-content;background: #eaf5ff;border-color: #727cf5 !important;color:#727cf5;display: block;border-radius: 2px;padding: 0.25rem 0.4rem;border: 1px solid #d5d5d5;cursor: pointer;margin-bottom:0.4rem';
                                                        }

                                                        if ($day_cnt_flp == 0 && $customers->some_day_flg == 0) {
                                                        $follow_up_msg = 'Follow Up Today';
                                                        }
                                                        if ($day_cnt_flp == 1 && $customers->some_day_flg == 0) {
                                                        $follow_up_msg = 'Follow Up Tomorrow';
                                                        }

                                                        if (!empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && strtotime(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('Y-m-d')) < strtotime(date('Y-m-d')) && $customers->some_day_flg == 0) {
                                                        $follow_up_msg = 'Follow Up Overdue';

                                                        $tmp_style = 'width: fit-content;background: #fce7e5;border-color: #fa5c7c !important;color:#fa5c7c;display: block;border-radius: 2px;padding: 0.25rem 0.4rem;border: 1px solid #d5d5d5;cursor: pointer;margin-bottom:0.4rem';
                                                        }

                                                        if ($customers->some_day_flg == 1) {
                                                        $follow_up_msg = 'Follow Up Someday';
                                                        }

                                                    @endphp
                                                    @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                    in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                                                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                                                        {{--|| auth()->user()->company_id == null--}}

                                                        @if ((in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                                                            $customers->assigned_to_user == auth()->user()->id)
                                                            {{--|| auth()->user()->company_id == null--}}
                                                            <div class="radiobtn">
                                                                <a href="javascript:void(0);"
                                                                onclick="OpenModalAssignLead(0,'#follow-up-modal','#follow-up-formModalLabel','Schedule Follow up','#follow-up-form');"/>
                                                                    <span class="flex-grow-1 ms-2 text-capitalize" style="{{ $tmp_style }}">{{ $follow_up_msg }} 
                                                                        <span class="font-normal ml-3">{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && $customers->some_day_flg == 0 ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d M Y - h:i A') : '' }}</span>
                                                                    </span>
                                                                    {{-- <label for="dewey" class="">Dewey</label> --}}
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="radiobtn">
                                                                <a href="javascript:void(0);"
                                                                onclick="OpenModalAssignLead(0,'#assign-self-lead-modal','#assign-self-lead-formModalLabel','Assign to Self Lead','#assign-self-lead-form');">
                                                                    <span class="flex-grow-1 ms-2 text-capitalize" style="{{ $tmp_style }}">{{ $follow_up_msg }} 
                                                                        <span class="font-normal ml-3">{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d M Y - h:i A') : '' }}</span>
                                                                    </span>
                                                                    {{-- <label for="dewey" class="">Dewey</label> --}}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="radiobtn">
                                                            <a href="javascript:void(0);"
                                                            onclick="accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the user assigned.');" >
                                                                <span class="flex-grow-1 ms-2 text-capitalize" style="{{ $tmp_style }}">{{ $follow_up_msg }} 
                                                                    <span class="font-normal ml-3"> {{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d M Y - h:i A') : '' }}</span>
                                                                </span>
                                                                {{-- <label for="dewey" class="">Dewey</label> --}}
                                                            </a>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style=max-width:40%;{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? '' : 'display:none;' }}">
                                                    <div class="col-md-12">
                                                        @php
                                                            $tmp_status = '';
                                                            if ($customers->estimate_status == 'Draft') {
                                                                $tmp_status = 'btn-secondary';
                                                            }

                                                            if ($customers->estimate_status == 'Sent') {
                                                                $tmp_status = 'btn-primary';
                                                            }

                                                            if ($customers->estimate_status == 'Inprogress') {
                                                                $tmp_status = 'btn-warning';
                                                            }

                                                            if ($customers->estimate_status == 'Accept') {
                                                                $tmp_status = 'btn-success';
                                                            }

                                                            if ($customers->estimate_status == 'Decline') {
                                                                $tmp_status = 'btn-danger';
                                                            }
                                                        @endphp
                                                        @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                                                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))

                                                            {{--|| auth()->user()->company_id == null--}}
                                                            @if (
                                                                (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                                                    in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                                                                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                                                                    $customers->assigned_to_user == auth()->user()->id)
                                                                {{--|| auth()->user()->company_id == null--}}
                                                                <a href="javascript:void(0);"
                                                                onclick="remove_follow_up_date({{ $customers->last_activity_id }})"
                                                                class="btn btn-outline-secondary btn-sm text-left fw-bold mb-1 ms-2 removeFollowUpDate">Remove
                                                                    follow up</a>

                                                                <a href="javascript:void(0);"
                                                                onclick="set_someday_follow_up('{{ Request::segment(4) }}')"
                                                                class="btn btn-outline-info btn-sm text-left fw-bold mb-1 ms-2 d-none setSomedayFollowUp">Set
                                                                    to someday</a>
                                                            @else
                                                                <a href="javascript:void(0);"
                                                                onclick="OpenModalAssignLead(0,'#assign-self-lead-modal','#assign-self-lead-formModalLabel','Assign to Self Lead','#assign-self-lead-form');"
                                                                class="btn btn-outline-secondary btn-sm text-left fw-bold mb-1 ms-2 removeFollowUpDate">Remove
                                                                    follow up</a>
                                                            @endif
                                                        @else
                                                            <a href="javascript:void(0);"
                                                            onclick="accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the user assigned.');"
                                                            class="btn btn-outline-secondary btn-sm text-left fw-bold mb-1 ms-2 removeFollowUpDate">Remove
                                                                follow up</a>
                                                        @endif
                                                        {{-- <span
                                                        class="btn {{$tmp_status}} text-uppercase text-left fw-bold mb-1 ms-2">{{ $customers->estimate_status}}</span> --}}
                                                    </div>
                                                </td>

                                                {{--<td>
                                                @if (count($duplicateLeads) > 0)
                                                    <a href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#duplicate-lead-modal"
                                                    class="btn btn-outline-dark btn-sm text-left fw-bold mb-1 ms-2">Duplicate leads found</a>
                                                @endif
                                                </td>--}}

                                                <td>
                                                    <a href="#" class="btn btn-outline-primary btn-sm view_on_mobile ms-2 mb-1" aria-expanded="false"
                                                    onclick="viewOnMobile({{$customers->assigned_to_user}},'{{$customers->name}}',{{$customers->id}});">
                                                        View on App <i class="mdi mdi-arrow-right-bold-box-outline"
                                                                    style="vertical-align: inherit;"></i> <i class="mdi mdi-cellphone"
                                                                                                                style="vertical-align: inherit;"></i>
                                                    </a>
                                                </td>
                                            <tr>
                                            <tbody>
                                        </table>
                                    </div>
                                </div> <!-- end row -->
                            </div> <!-- end card-body/ profile-user-box-->
                        </div><!--end profile/ card -->

                    </div> <!-- end col-->
                </div>
                <!-- end row -->

                <div class="row">
                    <div class="col-xxl-4 col-lg-4 order-lg-1 order-xxl-1">
                        <!-- video -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="header-title text-capitalize text-dark font-20">Timeline</h4>
                                </div>

                                <div class="timeline-alt py-0" id="timeline-info">
                                    <div class="timeline-item pb-4">
                                        <i class="mdi mdi-plus bg-info-lighten text-info timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-info fw-bold mb-1 pt-1 d-block"
                                            onclick="openModalActivity(id=0,'#activity-modal','#activity-formModalLabel','Add Activity','#activity-form')">Add
                                                Activity</a>
                                            {{--  <small>Send you message
                                                <span class="fw-bold">"Are you there?"</span>
                                            </small> --}}
                                            {{-- <p class="mb-0 pb-2">
                                                <small class="text-muted">2 days ago</small>
                                            </p> --}}
                                        </div>
                                    </div>

                                    {{--<div class="timeline-item">
                                        <i
                                            class="mdi mdi-check-decagram-outline bg-secondary-lighten text-secondary timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">You sold an
                                                item</a>
                                            <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">5 minutes ago</small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <i class="mdi mdi-chat-outline bg-warning-lighten text-warning timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Product on the
                                                Bootstrap Market</a>
                                            <small>Dave Gamache added
                                                <span class="fw-bold">Admin Dashboard</span>
                                            </small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">30 minutes ago</small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <i class="mdi mdi-phone bg-success-lighten text-success timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Robert
                                                Delaney</a>
                                            <small>Send you message
                                                <span class="fw-bold">"Are you there?"</span>
                                            </small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">2 hours ago</small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <i class="mdi mdi-arrow-top-right bg-primary-lighten text-primary timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Audrey
                                                Tobey</a>
                                            <small>Uploaded a photo
                                                <span class="fw-bold">"Error.jpg"</span>
                                            </small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">14 hours ago</small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <i class="mdi mdi-file-document-outline bg-danger-lighten text-danger timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">You sold an
                                                item</a>
                                            <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">16 hours ago</small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <i class="mdi mdi-calendar bg-dark-lighten text-dark timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Product on the
                                                Bootstrap Market</a>
                                            <small>Dave Gamache added
                                                <span class="fw-bold">Admin Dashboard</span>
                                            </small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">22 hours ago</small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <i class="mdi mdi-account-plus-outline bg-info-lighten text-info timeline-icon"></i>
                                        <div class="timeline-item-info">
                                            <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Robert
                                                Delaney</a>
                                            <small>Send you message
                                                <span class="fw-bold">"Are you there?"</span>
                                            </small>
                                            <p class="mb-0 pb-2">
                                                <small class="text-muted">2 days ago</small>
                                            </p>
                                        </div>
                                    </div>--}}
                                </div>
                            </div> <!-- end card-body -->
                        </div>
                        <!-- end video -->
                    </div>
                    <div class="col-xxl-8 col-lg-8 order-lg-2 order-xxl-2">
                        <!-- Left Icon Accordions -->
                        <div class="accordion lefticon-accordion custom-accordionwithicon accordion-border-box"
                            id="accordionlefticon" style="background: var(--ct-card-bg);">
                            <div class="accordion-item">
                                <h2 class="accordion-header mt-0 lead-detail-icon" style="box-shadow: 0px 0px 4px #d4d6dd; !important;">
                                    <div class="d-flex align-items-center p-1 rounded activity-count">

                                        @foreach ($activity_counts as $key => $activity_count)

                                            @if($activity_count->activity_name=="Call")
                                                <div class="flex-shrink-0 me-2 w-25" title="Call" style="margin-bottom: 0.175rem !important;">
                                                    <i class="mdi mdi-phone widget-icon rounded-circle bg-success-lighten text-success border border-success"></i>
                                                    <span class="font-15 text-dark">{{ $activity_count->activity_type_count }}</span>
                                                </div>
                                            @endif

                                            @if($activity_count->activity_name=="Message")
                                                <div class="flex-shrink-0 me-2 w-25" title="Message" style="margin-bottom: 0.175rem !important;">
                                            <i class="mdi mdi-chat-outline widget-icon rounded-circle bg-warning-lighten text-warning border border-warning"></i>
                                                    <span class="font-15 text-dark">{{ $activity_count->activity_type_count }}</span>
                                                </div>
                                            @endif

                                            @if($activity_count->activity_name=="Meeting" && $main_company->company_category > 1)
                                                <div class="flex-shrink-0 me-2 w-25" title="Meeting" style="margin-bottom: 0.175rem !important;">
                                            <i class="mdi mdi-calendar widget-icon rounded-circle bg-secondary-lighten text-secondary border border-secondary"></i>
                                                    <span class="font-15 text-dark">{{ $activity_count->activity_type_count }}</span>
                                                </div>
                                            @endif

                                                @if($activity_count->activity_name=="Site Visit" && $main_company->company_category == 1)
                                                    <div class="flex-shrink-0 me-2 w-25" title="Meeting" style="margin-bottom: 0.175rem !important;">
                                                        <i class="mdi mdi-map-marker-outline widget-icon rounded-circle bg-secondary-lighten text-secondary border border-secondary"></i>
                                                        <span class="font-15 text-dark">{{ $activity_count->activity_type_count }}</span>
                                                    </div>
                                                @endif
                                        @endforeach

                                    {{-- <div class="flex-shrink-0 me-2 w-25">
                                            <i class="mdi mdi-phone widget-icon rounded-circle bg-success-lighten text-success border border-success"></i>
                                            <span class="font-15 text-dark">1</span>
                                        </div>

                                        <div class="flex-shrink-0 me-2 w-25">
                                            <i class="mdi mdi-chat-outline widget-icon rounded-circle bg-warning-lighten text-warning border border-warning"></i>
                                            <span class="font-15 text-dark">1</span>
                                        </div>

                                        <div class="flex-shrink-0 me-2 w-25">
                                            <i class="mdi mdi-calendar widget-icon rounded-circle bg-secondary-lighten text-secondary border border-secondary"></i>
                                            <span class="font-15 text-dark">1</span>
                                        </div>

                                        <div class="flex-shrink-0 w-25">
                                            <i class="mdi mdi-file-pdf-box widget-icon rounded-circle bg-danger-lighten text-danger border border-danger"></i>
                                            <span class="font-15 text-dark">1</span>
                                        </div>--}}
                                    </div>
                                </h2>
                            </div>

                            <div class="accordion-item shadows" style="box-shadow: 0px 0px 4px #d4d6dd; !important;">
                                <h2 class="accordion-header" id="accordionlefticonExample2">
                                    <button class="accordion-button collapsed text-dark fw-bold" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#accor_lefticonExamplecollapse2"
                                            aria-expanded="true" aria-controls="accor_lefticonExamplecollapse2">
                                        Lead Information
                                    </button>
                                </h2>
                                <div id="accor_lefticonExamplecollapse21" class="accordion-collapse collapse show"
                                    aria-labelledby="accordionlefticonExample2" data-bs-parent="#accordionlefticon">
                                    <div class="accordion-body">
                                        <table class="table table-wrap table-centered table-sm mb-0">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Type</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 customer_type_small">{{ $customers->customer_type }}</small>
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Company Name</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 company_name_small">{{ $customers->company_name ? $customers->company_name : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Category</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 lead_category_small">{{ $customers->lead_category ? $customers->lead_category : '-' }}</small>
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Source</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 lead_origin_small">{{ $customers->lead_origin ? $customers->lead_origin : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Name</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 name_small">{{ $customers->name ? $customers->name : '-' }}</small>
                                                    </p>
                                                </td>

                                                <td class="lead_stage_td">
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Lead Stage</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 lead_stage_small">
                                                    <span class="fs-6 badge me-1"
                                                        style="background-color: {{ $customers->lead_stage_color_code }}">
                        {{ $customers->lead_stage_name ? $customers->lead_stage_name : '-' }}</span>
                                                        </small>
                                                    </p>
                                                </td>
                                            </tr>


                                            <tr>
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Phone no</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 phone_no_small">@php $abc = ($customers->country_code)? $customers->country_code:''; @endphp {{ $customers->phone_no ? $abc.$customers->phone_no : '-' }}</small>
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Whatsapp no</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 whatsapp_no_small">@php $abc = ($customers->whatsapp_country_code)? $customers->whatsapp_country_code:''; @endphp {{ $customers->whatsapp_no ? $abc.$customers->whatsapp_no : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>


                                            <tr>
                                                <td class="description_td" colspan="0">
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Description</span>
                                                        <br>
                                                        <small
                                                            class="text-body description_small font-16" style="word-break: break-all !important;">{!! $customers->description ? nl2br($customers->description) : '-' !!}</small>
                                                    </p>
                                                </td>


                                                <td class="label_td">
                                                    <div class="col-md-12 d-nones">
                                                        <div class="input-group label-div">
                                                            {{-- <select class="selectpicker" multiple aria-label="Search Labels" data-live-search="true">
                                                        @foreach ($leadLabels as $leadLabel)
                                                            <option value="{{$leadLabel->id}}"><div class="flex-shrink-0"><span class="widget-icon rounded" style="background-color:{{$leadLabel->color_code}} !important;"></span></div> {{$leadLabel->name}}</option>
                                                        @endforeach
                                                    </select> --}}


                                                            {{-- <a href="javascript:void(0);" class="btn btn-primary btn-sm align-items-center"
                                                        onclick="openModal('#lead-groups-modal','Create New Labels','#lead-groups-form','.modal-title',id=0)"><i class="mdi mdi-plus-circle"></i> New</a> --}}

                                                        </div>

                                                    </div>
                                                    <p class="overflow-hidden text-black mb-0 label-span" style="white-space: initial;">
                                                        <span class="fw-bold">Labels</span>
                                                        <br>
                                                        @foreach ($label_color as $key => $labelColor)
                                                            <span class="fs-6 badge me-1"
                                                                style="color:{{ $labelColor->color_code }};
                                                                    border: 1px solid {{ $labelColor->color_code }};
                                                                    background-color: transparent">
                        {{ $labelColor->name }}</span>
                                                        @endforeach
                                                        @if(!$label_color) - @endif
                                                    </p>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td colspan="2" class="border-0">
                                                    <h5 class="text-capitalize text-dark m-0">Advance Options
                                                        <a href="javascript: void(0);" class="advance-options-td text-primary"
                                                        style="text-transform: initial !important;">Click to show </a>
                                                    </h5>
                                                </td>
                                            </tr>

                                            <tr class="advance-options-tr d-none">
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Email</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 email_small">{{ $customers->email ? $customers->email : '-' }}</small>
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Address</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 address_small">{{ $customers->address ? $customers->address : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr class="advance-options-tr d-none">
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Pincode</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 pincode_small">{{ $customers->pincode ? $customers->pincode : '-' }}</small>
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">Country</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 country_name_small">{{ $customers->country_name ? $customers->country_name : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr class="advance-options-tr d-none">
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">State</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 state_name_small">{{ $customers->state_name ? $customers->state_name : '-' }}</small>
                                                    </p>
                                                </td>

                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">City</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 city_name_small">{{ $customers->city_name ? $customers->city_name : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr class="advance-options-tr d-none">
                                                <td>
                                                    <p class="overflow-hidden text-black mb-0">
                                                        <span class="fw-bold">GSTIN (TAX No.)</span>
                                                        <br>
                                                        <small
                                                            class="text-body font-16 gst_no_small">{{ $customers->gst_no ? $customers->gst_no : '-' }}</small>
                                                    </p>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item shadows" style="box-shadow: 0px 0px 4px #d4d6dd; !important;">
                                <h2 class="accordion-header" id="accordionlefticonExample1">
                                    <button class="accordion-button flex-grow-1 collapsed text-dark d-block fw-bold"
                                            type="button"
                                            data-bs-toggle="collapse" data-bs-target="#accor_lefticonExamplecollapse1"
                                            aria-expanded="true" aria-controls="accor_lefticonExamplecollapse1">
                                        Attachments (<span id="attachment_count">0</span>)
                                    </button>
                                </h2>
                                <div id="accor_lefticonExamplecollapse11" class="accordion-collapse collapse"
                                    aria-labelledby="accordionlefticonExample1" data-bs-parent="#accordionlefticon">
                                    <div class="accordion-body">
                                        <div class="row pe-0 mb-2">
                                            <div class="col-12 text-end">
                                                <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                                onclick="openModal('#attachment-modal','Upload Attachment','#attachment-form','.attachment-modal-title',id=0,flag=3)">
                                                    <i class="mdi mdi-link-variant"></i> Upload Attachment
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row attachment-info" id="attachment-info">
                                            <div class="col-xl-6">
                                                <div class="card mb-1 shadow-none border primary">
                                                    <div class="p-2">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <div class="avatar-sm">
                                                                    <span
                                                                        class="avatar-title bg-light bg-primary-lighten text-primary text-reset rounded">
                                                                        <i class="mdi mdi-folder text-primary font-16"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col ps-0">
                                                                <a href="javascript:void(0);" class="text-dark fw-bold"
                                                                onclick="openModal('#attachment-file-modal','Attachments','#attachment-file-form','.modal-title',id=0,flag=3)">Hyper-admin-design</a>
                                                                <p class="mb-0">2 Files</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <!-- Button -->
                                                                {{--<a href="javascript:void(0);"
                                                                class="btn btn-link btn-sm text-dark">
                                                                    <i class="mdi mdi-dots-vertical"></i>
                                                                </a>--}}
                                                                <a href="javascript:void(0);" class="text-danger"
                                                                title="Delete"><i
                                                                        class="mdi mdi-delete-outline mdi-18px me-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                            <div class="col-xl-6">
                                                <div class="card mb-1 shadow-none border">
                                                    <div class="p-2">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <div class="avatar-sm">
                                                                    <span
                                                                        class="avatar-title bg-light bg-primary-lighten text-primary text-reset rounded">
                                                                        <i class="mdi mdi-folder text-primary font-16"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col ps-0">
                                                                <a href="javascript:void(0);" class="text-dark fw-bold">Dashboard-design</a>
                                                                <p class="mb-0">3 Files</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <a href="javascript:void(0);" class="text-danger"
                                                                title="Delete"><i
                                                                        class="mdi mdi-delete-outline mdi-18px me-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                            <div class="col-xl-6">
                                                <div class="card mb-0 shadow-none border">
                                                    <div class="p-2">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <div class="avatar-sm">
                                                                    <span
                                                                        class="avatar-title bg-light bg-primary-lighten text-primary text-reset rounded">
                                                                        <i class="mdi mdi-folder text-primary font-16"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col ps-0">
                                                                <a href="javascript:void(0);" class="text-dark fw-bold">Admin-bug-report</a>
                                                                <p class="mb-0">7 Files</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <a href="javascript:void(0);" class="text-danger"
                                                                title="Delete"><i
                                                                        class="mdi mdi-delete-outline mdi-18px me-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item shadows mt-2" style="box-shadow: 0px 0px 4px #d4d6dd; !important;">
                                <h2 class="accordion-header" id="accordionlefticonExample3">
                                    <button class="accordion-button collapsed text-dark fw-bold" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#accor_lefticonExamplecollapse3"
                                            aria-expanded="false" aria-controls="accor_lefticonExamplecollapse3">
                                        Estimates
                                    </button>
                                </h2>
                                <div id="accor_lefticonExamplecollapse31" class="accordion-collapse collapse"
                                    aria-labelledby="accordionlefticonExample3" data-bs-parent="#accordionlefticon">
                                    <div class="accordion-body">
                                        <div class="table-responsives">
                                        <table id="lead-estimate-datatable" class="table table-centered table-sm w-100">
                                            <thead class="table-light w-100">
                                                <tr>
                                                    <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                                    <th>Date</th>
                                                    <th>Estimate Number</th>
                                                    <th>Total</th>
                                                    <th>Created by</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal -->
    <div id="activity-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="activity-formModalLabel">Add Activity</h3>
                    {{--                    <p class="text-muted">Drag and drop your event or click in the calendar</p> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <h5 class="mt-0 text-dark">Please select the type of activity you want to add</h5>
                    <div class="row activity-type-div">
                        <div class="col-xxl-3 col-lg-6 col-6">
                            <a href="javascript:void(0);" class="text-dark" data-id="1" data-type="Call"
                               onclick="addActivityForm(1,'Call')">
                                <div class="card card-body text-center bg-light border p-1">
                                    <div class="avatar-sm mx-auto mb-2">
                                        <div
                                            class="avatar-title bg-success-lighten text-success border border-success fs-17 avatar-md rounded-circle">
                                            <i class="mdi mdi-phone fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="card-title mb-0">Call</h4>
                                </div>
                            </a>
                        </div>

                        <div class="col-xxl-3 col-lg-6 col-6">
                            <a href="javascript:void(0);" class="text-dark" data-id="2" data-type="Message"
                               onclick="addActivityForm(2,'Message')">
                                <div class="card card-body text-center bg-light border p-1">
                                    <div class="avatar-sm mx-auto mb-2">
                                        <div
                                            class="avatar-title bg-warning-lighten text-warning border border-warning fs-17 avatar-md rounded-circle">
                                            <i class="mdi mdi-chat-outline fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="card-title mb-0">Message</h4>
                                </div>
                            </a>
                        </div>

                        @if($main_company->company_category > 1)
                        <div class="col-xxl-3 col-lg-6 col-6">
                            <a href="javascript:void(0);" class="text-dark" data-id="3" data-type="Meeting"
                               onclick="addActivityForm(3,'Meeting')">
                                <div class="card card-body text-center bg-light border p-1">
                                    <div class="avatar-sm mx-auto mb-2">
                                        <div
                                            class="avatar-title bg-secondary-lighten text-secondary border border-secondary fs-17 avatar-md rounded-circle">
                                            <i class="mdi mdi-calendar fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="card-title mb-0">Meeting</h4>
                                </div>
                            </a>
                        </div>
                        @endif
                        @if($main_company->company_category == 1)
                        <div class="col-xxl-3 col-lg-6 col-6">
                            <a href="javascript:void(0);" class="text-dark" data-id="19" data-type="Site Visit"
                               onclick="addActivityForm(19,'Site Visit')">
                                <div class="card card-body text-center bg-light border p-1">
                                    <div class="avatar-sm mx-auto mb-2">
                                        <div
                                            class="avatar-title bg-secondary-lighten text-secondary border border-secondary fs-17 avatar-md rounded-circle">
                                            <i class="mdi mdi-map-marker-outline fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="card-title mb-0">Site Visit</h4>
                                </div>
                            </a>
                        </div>
                        @endif
                        {{-- <div class="col-xxl-3 col-lg-6 col-6">
                        <a href="javascript:void(0);" class="text-dark" data-id="4" data-type="Note"
                        onclick="addActivityForm(4,'Note')">
                        <div class="card card-body text-center bg-light border p-1">
                        <div class="avatar-sm mx-auto mb-2">
                            <div
                                class="avatar-title bg-info-lighten text-info border border-info fs-17 avatar-md rounded-circle">
                                <i class="mdi mdi-file-document-outline fs-4"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-0">Note</h4>
                        </div>
                        </a>
                        </div> --}}
                        @php
                            $user = Auth::user();
                            $id = isset($user->company_id) ? $user->company_id : $user->id;
                            $month = Carbon::now()->format('m');
                            $estimateCount = Estimate::where('company_id', $id)
                            ->whereMonth('created_at', $month)
                            ->count();
                            $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
                        @endphp
                        <div class="col-xxl-3 col-lg-6 col-6">
                            @if (isset($plan->estimate_limit) && $plan->estimate_limit <= $estimateCount)
                                <a class="text-dark" data-toggle="modal" id="mediumButton"
                                   data-target="#estimateLimitModal">
                                    <div class="card card-body text-center bg-light border p-1">
                                        <div class="avatar-sm mx-auto mb-2">
                                            <div
                                                class="avatar-title bg-danger-lighten text-danger border border-danger fs-17 avatar-md rounded-circle">
                                                <i class="mdi mdi-file-pdf-box fs-4"></i>
                                            </div>
                                        </div>
                                        <h4 class="card-title mb-0">Estimate</h4>
                                    </div>
                                </a>
                            @else
                                <a href="{{ url('/quotes/new?cid=' . Request::segment(4)) }}" class="text-dark"
                                   data-id="5" data-type="Estimate">
                                    <div class="card card-body text-center bg-light border p-1">
                                        <div class="avatar-sm mx-auto mb-2">
                                            <div
                                                class="avatar-title bg-danger-lighten text-danger border border-danger fs-17 avatar-md rounded-circle">
                                                <i class="mdi mdi-file-pdf-box fs-4"></i>
                                            </div>
                                        </div>
                                        <h4 class="card-title mb-0">Estimate</h4>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="row activity-form-div d-none">
                        <form class="activity-form" id="activity-form" action="#" novalidate="">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-4 d-none">
                                        <div class="form-floating">
                                            <select class="form-select bg-light" id="activity_type" name="activity_type"
                                                    onchange="getActivityName(this);">
                                                <option value="1">Call</option>
                                                <option value="2">Message</option>
                                                @if($main_company->company_category > 1)
                                                <option value="3">Meeting</option>
                                                @endif
                                                @if($main_company->company_category == 1)
                                                <option value="19">Site Visit</option>
                                                @endif
                                                {{--                                                <option value="4">Note</option> --}}
                                                {{--                                                <option value="5">Estimate</option> --}}
                                            </select>
                                            <label for="activity_type">Type</label>
                                            <input type="hidden" id="id" name="id" value="0">
                                            <input type="hidden" id="customer_id" name="customer_id"
                                                   value="{{ Request::segment(4) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-8 d-none">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control bg-light" id="activity_name"
                                                   name="activity_name" placeholder="Name" required>
                                            <label for="activity_name">Name <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <textarea {{($main_company->follow_up_note_req_flg==1)?'required':''}} class="form-control bg-light" id="activity_notes" name="activity_notes" placeholder="Add Discussion Summary.." style="height: 150px"></textarea>
                                        <label for="activity_notes">Add Discussion Summary..</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 visit_address_div" style="display:none;">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control bg-light" id="visit_address" name="visit_address" placeholder="Add address here..." style="height: 150px"></textarea>
                                        <label for="activity_notes">Add address here...</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating mb-3">
                                        <input type="text" id="follow_up_datetime" class="form-control bg-light"
                                               placeholder="Date and Time" name="follow_up_datetime" placeholder="Date"
                                               readonly>
                                        {{-- <input type="text" class="form-control bg-light" id="follow_up_datetime"
                                               name="follow_up_datetime" placeholder="Date" required> --}}
                                        <label for="follow_up_datetime">Follow Up Date </label>
                                        <input type="hidden" id="old_follow_up_date_at" name="old_follow_up_date_at"
                                               value="{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? $customers->last_follow_up_datetime : '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="hidden" id="lead_id" name="lead_id"
                                               value="{{ Request::segment(4) }}">
                                        {{-- <textarea class="form-control bg-light" id="lead_description"
                                                   name="lead_description"
                                                   placeholder="Add Discussion Summary.."
                                                   style="height: 150px"></textarea>--}}


                                        <select class="form-select bg-light text-dark" id="leads_stages_id_followup"
                                                name="leads_stages_id_followup" required>
                                            {{--                                            <option value="">Choose</option>--}}
                                            @foreach($leadStages as $leadStage)
                                                <option
                                                    value="{{$leadStage->id}}"
                                                    {{($customers->lead_stage_id == $leadStage->id)?'selected':''}} data-id="{{$leadStage->is_default}}">{{$leadStage->name}}</option>
                                            @endforeach
                                        </select>


                                        <label for="leads_stages_id_followup">Lead Stage</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-floating mt-2 lost_reason_div_followup" style="{{($customers->lost_reason_id > 0)?'':'display:none;'}}">
                                        <select class="form-select bg-light text-dark" id="lost_reason_id_followup"
                                                name="lost_reason_id_followup">
                                            <option value="">Choose</option>
                                            @foreach($lostReasons as $lostReason)
                                                <option value="{{$lostReason->id}}"
                                                        data-id="{{$lostReason->priority}}" {{($customers->lost_reason_id == $lostReason->id)?'selected':''}}>{{$lostReason->name}}</option>
                                            @endforeach
                                        </select>
                                        <label for="lost_reason_id_followup" class="form-label">Lost Reason <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-floating mt-2 lost_reason_others_div_followup"
                                         style="{{($customers->others_reason != '')?'':'display:none;'}}">

                                        <textarea class="form-control bg-light" id="lost_reason_others_followup"
                                                  name="lost_reason_others_followup"
                                                  placeholder="Add reason here..."
                                                  style="height: 150px">{{$customers->others_reason}}</textarea>
                                        <label for="lost_reason_others" class="form-label">Enter Reason <span
                                                class="text-danger">*</span></label>
                                    </div>
                                    <input type="hidden" name="lost_reason_name_followup" id="lost_reason_name_followup"/>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-grid d-block">
                                    <button type="submit" class="btn btn-lg font-16 btn-primary"
                                            id="activity_form_buttons">
                                        <i class="mdi mdi-floppy fs-5"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>
                {{-- <div class="modal-footer d-block">
                <div class="d-grid">
                <button class="btn btn-lg font-16 btn-info" id="btn-new-event">
                <i class="mdi mdi-floppy fs-5"></i> Create New Event
                </button>
                </div>
                <button type="button" class="btn btn-primary" data-bs-target="#multiple-two" data-bs-toggle="modal"
                data-bs-dismiss="modal">Next
                </button>
                </div> --}}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div class="modal fade" id="estimateLimitModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
         data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="model-body p-0">
                        <div id="screen_1" class="tabcontent" style="display: block;">
                            <div class="image">
                                <img class="welcome_image" style="width: 100%;" alt=""
                                     src="{{ asset('images/model_image1.png') }}">
                            </div>
                            <div class="welcome_main d-flex justify-content-center align-items-center flex-column">
                                <div class="welcome_part ">
                                    <h3 class="p-3 fw-bold text-dark ">OOPS !! Your New Estimate Generate limit is over
                                    </h3>
                                </div>
                                <p class="px-5 pt-2 text-dark">Thank you for using Quickest. You can generate a maximum
                                    up
                                    to {{ $estimateCount }} per month with a free plan. To get full benefits of
                                    Quickest,
                                    upgrade your plan now! Check your email for Discount Promo Code.
                                </p>
                                <div class="w-75 my-4">
                                    <button type="button"
                                            class="btn btn-primary btn-block btn-lg btn-primary continue_btn w-100"
                                            data-bs-dismiss="modal">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="customer-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Create Customer</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="customer-form" id="customer-form" action="#">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>
                                    <div class="form-check form-check-inline">
                                        <input class="form-control" type="hidden" id="id" name="id"
                                               value="0">
                                        <input type="radio" id="customer_type_business" name="customer_type"
                                               class="form-check-input" value="Business">
                                        <label class="form-check-label" for="customer_type_business">Business</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="customer_type_individual" name="customer_type"
                                               class="form-check-input" value="Individual" checked>
                                        <label class="form-check-label"
                                               for="customer_type_individual">Individual</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 cust_company_name_div">
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="text" id="company_name"
                                           name="company_name" placeholder="Company name">
                                    <label for="company_name" class="form-label">Company Name <span
                                            class="text-danger">*</span></label>
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control bg-light text-dark" id="name"
                                                   name="name"
                                                   required=""
                                                   placeholder="Name">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 ps-2 pe-1 mb-3">
                                <table class="table table-centered table-borderless mb-0">
                                    <tbody>
                                    <tr>
                                        <td style="padding: 0px;">
                                            <div class="form-floating input-group-append" style="width: 100%;"
                                                 id="sel_cc">
                                                <select
                                                    class="text-left input-group form-select bg-light text-dark select2"
                                                    id="country_code"
                                                    name="country_code"
                                                    required="" data-toggle="select2">
                                                    @php
                                                        $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                    @endphp

                                                    @foreach($countries as $country)
                                                        <option class="text-left" value="{{$country->phonecode}}"
                                                                data-id="{{$country->id}}"
                                                                @if($country->id==$expData->country_id) selected @endif>
                                                            +{{$country->phonecode}} {{$country->sortname}}</option>
                                                    @endforeach
                                                </select>
                                                <label for="country_code" class="form-label">Code <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                        </td>
                                        <td style="padding: 0px;">
                                            <div class="form-floating input-group-append" style="width: 100%;">
                                                <input type="text" class="form-control bg-light text-dark" id="phone_no"
                                                       name="phone_no" required=""
                                                       placeholder="Phone no" data-parsley-type="digits"
                                                       data-parsley-errors-container="#mobileError" style="border-top-left-radius: 0;border-bottom-left-radius: 0; !important;"
                                                    {{--data-parsley-minlength="10"
                                                    data-parsley-maxlength="15"--}}
                                                >
                                                <label for="phone_no" class="form-label">Phone no <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                                <span id="mobileError" style="background-color:blue;"></span>
                            </div>

                            <div class="col-md-6 ps-1">
                                <div class="col-md-12 mb-2">
                                    <table class="table table-centered table-borderless mb-0">
                                        <tbody>
                                        <tr>
                                            <td style="padding: 0px;">
                                                <div class="form-floating input-group-append" style="width: 100%;"
                                                     id="sel_wcc">
                                                    <select
                                                        class="text-left input-group form-select bg-light text-dark select2"
                                                        id="whatsapp_country_code"
                                                        name="whatsapp_country_code"
                                                        required="" data-toggle="select2">
                                                        @php
                                                            $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                        @endphp

                                                        @foreach($countries as $country)
                                                            <option class="text-left" value="{{$country->phonecode}}"
                                                                    data-id="{{$country->id}}"
                                                                    @if($country->id==$expData->country_id) selected @endif>
                                                                +{{$country->phonecode}} {{$country->sortname}}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="whatsapp_country_code" class="form-label">Code <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                            </td>
                                            <td style="padding: 0px;">
                                                <div class="form-floating input-group-append" style="width: 100%;">
                                                    <input type="text" class="form-control bg-light text-dark"
                                                           id="whatsapp_no"
                                                           name="whatsapp_no"
                                                           placeholder="Whatsapp no" data-parsley-type="digits"
                                                           data-parsley-errors-container="#whatsappNoError" style="border-top-left-radius: 0;border-bottom-left-radius: 0; !important;" {{--data-parsley-minlength="10" data-parsley-maxlength="15"--}}>
                                                    <label for="whatsapp_no" class="form-label">Whatsapp no <span
                                                            class="text-danger"></span></label>
                                                </div>
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                    <span id="whatsappNoError" style="background-color:blue;"></span>
                                </div>

                            </div>

                            <div class="col-6 mb-3 d-none">
                                <div class="form-floating input-group-append"
                                     style="width: 100%;" id="sel_cn">
                                    <select class="text-left form-select bg-light text-dark select2"
                                            id="currency_name"
                                            name="currency_name"
                                            data-toggle="select2">
                                        @php
                                            $expData = App\Helpers\PermissionCheck::plan_details_check();
                                        @endphp

                                        @foreach($countries as $country)
                                            <option class="text-left" value="{{$country->currency_code}}"
                                                    data-id="{{$country->id}}"
                                                    @if($country->id==$expData->country_id) selected @endif>
                                                {{$country->currency_code}} - {{$country->currency_name}}</option>
                                        @endforeach
                                    </select>
                                    <label for="currency_name" class="form-label">Currency</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating mb-2">
                                    <select class="form-select bg-light text-dark" id="lead_stage_id"
                                            name="lead_stage_id" required>
                                        <option value="">Choose</option>
                                        @foreach($leadStages as $leadStage)
                                            <option
                                                value="{{$leadStage->id}}">{{$leadStage->name}}</option>
                                        @endforeach
                                    </select>
                                    <label for="lead_stage_id" class="form-label">Lead Stage <span
                                            class="text-danger">*</span></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">

                                    <div class="col-md-6">
                                        <div class="form-floating mb-2">
                                            <select class="form-select bg-light text-dark" id="customer_category_id"
                                                    name="customer_category_id">
                                                <option value=0>Choose</option>
                                                @foreach ($customerCategories as $customerCategory)
                                                    <option value="{{ $customerCategory->id }}">
                                                        {{ $customerCategory->name }}</option>
                                                @endforeach
                                            </select>
                                            <label for="customer_category_id" class="form-label">Category</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-2">
                                            <select class="form-select bg-light text-dark" id="customer_lead_id"
                                                    name="customer_lead_id">
                                                <option value=0>Choose</option>
                                                @foreach ($customerLeads as $customerLead)
                                                    <option value="{{ $customerLead->id }}">{{ $customerLead->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="customer_lead_id" class="form-label">Source</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <textarea class="form-control bg-light text-dark" id="description" name="description" placeholder="Enter description" style="height: 80px;"></textarea>
                                            <label for="description" class="form-label">Description</label>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="text-capitalize">Advance Options <a href="javascript: void(0);" class="advance-option text-primary" style="text-transform: initial !important;">Click to show </a></h5>
                                {{--                                <span class="form-text text-dark text-uppercase mb-1 mt-1" style="font-weight: bold !important;font-size: 1rem !important;"></span> --}}
                            </div>
                        </div>

                        <div class="row advance-option-div d-none">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="email" id="email"
                                           name="email" placeholder="Enter email">
                                    <label for="email" class="form-label">Email</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control bg-light text-dark" id="address" name="address" placeholder="Enter address" style="height: 100px;"></textarea>
                                    <label for="address" class="form-label">Address</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="text" id="pincode"
                                                   name="pincode" placeholder="Enter pincode">
                                            <label for="pincode" class="form-label">Pincode</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select bg-light text-dark" id="country_id"
                                                    name="country_id" required="">
                                                <option value="">Choose</option>
                                                @php
                                                    $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                @endphp

                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" data-id="{{$country->phonecode}}"
                                                            @if ($country->id == $expData->country_id) selected @endif>
                                                        {{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                            <label for="country_id" class="form-label">Country <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select bg-light text-dark" id="state_id"
                                                    name="state_id">
                                                <option value="0">Choose</option>
                                            </select>
                                            <label for="state_id" class="form-label">State <span
                                                    class="text-danger"></span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating bg-light mb-3 d-none">
                                            <select class="form-select text-dark" id="city_id" name="city_id">
                                                <option value="0">Choose</option>
                                            </select>
                                            <label for="city_id" class="form-label">City</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="text" id="city_name"
                                                   name="city_name" placeholder="Enter city">
                                            <label for="city_name" class="form-label">City</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="text" id="gst_no"
                                           name="gst_no" placeholder="Enter gstin">
                                    <label for="gst_no" class="form-label">GSTIN (TAX No.)</label>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        {{--                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close --}}
                        {{--                        </button> --}}
                        <button class="btn btn-primary fullscreen" form="customer-form" id="customer_button"
                                type="submit">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="lead-description-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="lead-description-formModalLabel">Lead Description</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-2">
                    <div class="row">
                        <form class="lead-description-form" id="lead-description-form" action="#" novalidate="">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="hidden" id="lead_id" name="lead_id"
                                               value="{{ Request::segment(4) }}">
                                        <textarea class="form-control bg-light" id="lead_description"
                                                  name="lead_description"
                                                  placeholder="Add Discussion Summary.."
                                                  style="height: 250px"></textarea>
                                        <label for="notes">Add Discussion Summary..</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-grid d-block">
                                    <button type="submit" class="btn btn-lg font-16 btn-primary lead_description_button"
                                            id="lead_description_button">
                                        <i class="mdi mdi-floppy fs-5"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="lead-stage-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="lead-stage-formModalLabel">Lead Stage</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-2">
                    <div class="row">
                        <form class="lead-stage-form" id="lead-stage-form" action="#" novalidate="">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="hidden" id="lead_id" name="lead_id"
                                               value="{{ Request::segment(4) }}">
                                        {{-- <textarea class="form-control bg-light" id="lead_description"
                                                   name="lead_description"
                                                   placeholder="Add Discussion Summary.."
                                                   style="height: 150px"></textarea>--}}


                                        <select class="form-select bg-light text-dark" id="leads_stages_id"
                                                name="leads_stages_id" required>
                                            {{--                                            <option value="">Choose</option>--}}
                                            @foreach($leadStages as $leadStage)
                                                <option
                                                    value="{{$leadStage->id}}"
                                                    {{($customers->lead_stage_id == $leadStage->id)?'selected':''}} data-id="{{$leadStage->is_default}}">{{$leadStage->name}}</option>
                                            @endforeach
                                        </select>


                                        <label for="notes">Lead Stage</label>
                                    </div>
                                    <div class="form-floating mt-2 lost_reason_div"
                                         style="{{($customers->lost_reason_id > 0)?'':'display:none;'}}">
                                        <select class="form-select bg-light text-dark" id="lost_reason_id"
                                                name="lost_reason_id" required>
                                            <option value="">Choose</option>
                                            @foreach($lostReasons as $lostReason)
                                                <option value="{{$lostReason->id}}"
                                                        data-id="{{$lostReason->priority}}" {{($customers->lost_reason_id == $lostReason->id)?'selected':''}}>{{$lostReason->name}}</option>
                                            @endforeach
                                        </select>
                                        <label for="lost_reason_id" class="form-label">Lost Reason <span
                                                class="text-danger">*</span></label>
                                    </div>

                                    <div class="form-floating mt-2 lost_reason_others_div"
                                         style="{{($customers->others_reason != '')?'':'display:none;'}}">

                                        <textarea class="form-control bg-light" id="lost_reason_others"
                                                  name="lost_reason_others"
                                                  placeholder="Add reason here..."
                                                  style="1">{{$customers->others_reason}}</textarea>
                                        <label for="lost_reason_others" class="form-label">Enter Reason <span
                                                class="text-danger">*</span></label>
                                    </div>
                                    <input type="hidden" name="lost_reason_name" id="lost_reason_name"/>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-grid d-block">
                                    <button type="submit" class="btn btn-lg font-16 btn-primary lead_stage_button"
                                            id="lead_stage_button">
                                        <i class="uil-arrow-circle-right"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="assign-lead-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="assign-lead-formModalLabel">Assign Lead</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 p-2">
                    <div class="row">
                        <p class="text-sm text-dark mb-2">
                            Select a team member to assign this lead to
                        </p>
                        <form class="assign-lead-form" id="lead-lead-form" action="#" novalidate="">
                            <div class="col-12">
                                <input type="hidden" id="id" name="id" value="{{ Request::segment(4) }}">

                                <input type="hidden" id="follow_up_date_assign_user" name="follow_up_date_assign_user"
                                       value="{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? $customers->last_follow_up_datetime : '' }}">
                                @foreach ($leads as $lead)
                                    <div class="radiobtn">
                                        <input type="radio" id="assigned_to_{{ $lead->id }}"
                                               name="assigned_to_user" value="{{ $lead->id }}"
                                            {{ $lead->id == $customers->assigned_to_user ? 'checked' : '' }} />
                                        <label for="assigned_to_{{ $lead->id }}">
                                            <table class="table table-sm table-centered table-nowrap mb-0 p-0"
                                                   id="lead-table">
                                                <tr>
                                                    <td style="width:40%;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <img class="rounded-circle"
                                                                     src="{{ asset('images/users/placeholder.png') }}"
                                                                     alt="Avtar image" width="28">
                                                            </div>
                                                            <div class="flex-grow-1 ms-2 text-dark text-capitalize">
                                                                {{ $lead->id == auth()->user()->id ? 'Myself' : $lead->name }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style="width:55%;" class="fw-normal">
                                                        {{ $lead->email }}
                                                    </td>
                                                </tr>
                                            </table>

                                        </label>

                                    </div>
                                @endforeach
                                {{-- <div class="radiobtn">
                                <input type="radio" id="dewey"
                                       name="drone" value="dewey" checked/>
                                <label for="dewey">Dewey</label>
                                </div>

                                <div class="radiobtn">
                                <input type="radio" id="louie"
                                       name="drone" value="louie"/>
                                <label for="louie">Louie</label>
                                </div> --}}
                            </div>

                            <div class="col-12">
                                <div class="d-grid d-block">
                                    <button type="submit" class="btn btn-lg font-16 btn-primary"
                                            id="assign_lead_button">
                                        <i class="mdi mdi-check"></i> CONFIRM
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="duplicate-lead-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="assign-lead-formModalLabel">{{ count($duplicateLeads) }}
                        Duplicate Leads Found</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-2">
                    <div class="row">
                        <div class="col-12">
                            <p class="text-sm text-dark mb-2">
                                Multiple Leads with a matching phone number and/or email address already exist in your
                                lead list. You can click a lead below to view their details.
                            </p>
                            <table class="table table-centered table-nowrap">
                                <thead class="table-light text-uppercase">
                                <tr>
                                    <th>Lead Name</th>
                                    <th>Matching Field(s)</th>
                                    <th>Date Added</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($duplicateLeads as $duplicateLead)
                                    <tr>
                                        <td class="text-dark">

                                            @if($duplicateLead->new_lead_flag==1)
                                                <span class="badge bg-secondary text-light float-end blinks"
                                                      style="background:#0acf97 !important;">New</span>
                                            @endif
                                            <h5 class="font-14 my-1">{{ $duplicateLead->name }}</h5>

                                            {{--                                                @if ($duplicateLead->assigned_to_user)--}}
                                            @if ($duplicateLead->assigned_to_user == auth()->user()->id)
                                                <span class=" fs-6 badge bg-primary"> <i
                                                        class="pe-1 mdi mdi-account-check"></i>{{ $duplicateLead->assigned_user_name }}</span>
                                            @elseif ($duplicateLead->assigned_to_user == 0)
                                                <span class=" fs-6 badge bg-secondary text-light"> <i
                                                        class="pe-1 mdi mdi-account-off"></i>Unassigned</span>
                                            @else
                                                <span class=" fs-6 badge bg-secondary text-light"> <i
                                                        class="pe-1 mdi mdi-account-lock"></i>{{ $duplicateLead->assigned_user_name }}</span>
                                            @endif
                                            {{--                                                @endif--}}
                                        </td>

                                        <td class="text-danger">
                                            {{ $duplicateLead->phone_no }}
                                        </td>
                                        <td class="text-dark">
                                            {{ \Carbon\Carbon::parse($duplicateLead->created_at)->format('d M Y') }}
                                        </td>
                                        <td>
                                            @if (
                                                $duplicateLead->create_lead_user_id == auth()->user()->id ||
                                                    $duplicateLead->assigned_to_user == auth()->user()->id ||
                                                    in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm))
                                                {{--|| auth()->user()->company_id == null--}}
                                                <a href="{{ url('lead/timeline/' . Crypt::encrypt($duplicateLead->id)) }}"
                                                   class="action-icon text-dark"> <i
                                                        class="mdi mdi-arrow-top-right-bold-box-outline"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{-- <div class="radiobtn">
                            <input type="radio" id="dewey"
                               name="drone" value="dewey" checked/>
                            <label for="dewey">Dewey</label>
                            </div>

                            <div class="radiobtn">
                            <input type="radio" id="louie"
                               name="drone" value="louie"/>
                            <label for="louie">Louie</label>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="lead-groups-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="width: 100%;">
            <div class="modal-content" style="height: 100%;">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Create New Labels</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="lead-groups-form" id="lead-groups-form" action="#">

                        <div class="mb-1">
                            <div class="form-floating mb-3">
                                <input class="form-control bg-light text-dark" type="text" id="name"
                                       name="name" placeholder="Name" required="">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            </div>

                            <input class="form-control" type="hidden" id="id" name="id" value="0">
                        </div>

                        {{-- <div class="mb-1">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="name" name="name" required=""
                        placeholder="Enter name" autofocus>
                        </div> --}}


                        <div class="mb-3 d-none">
                            <div class="form-floating">
                                <textarea class="form-control bg-light text-dark" id="description" name="description" placeholder="Enter description" style="height: 80px;"></textarea>
                                <label for="description" class="form-label">Description</label>
                            </div>
                        </div>

                        <div class="mb-3 bg-light p-2">
                            <label for="color" class="form-label text-dark">Colors</label>

                            <section>
                                <div class="swatch green">
                                    <input type="radio" name="color_code" id="swatch_2" value="#006398" checked/>
                                    <label for="swatch_2" style="background-color: #006398;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>
                                <div class="swatch blue">
                                    <input type="radio" name="color_code" id="swatch_3" value="#13a764"/>
                                    <label for="swatch_3" style="background-color: #13a764;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>
                                <div class="swatch purple">
                                    <input type="radio" name="color_code" id="swatch_1" value="#fdac64"/>
                                    <label for="swatch_1" style="background-color: #fdac64;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>
                                <div class="swatch red">
                                    <input type="radio" name="color_code" id="swatch_5" value="#f678c3"/>
                                    <label for="swatch_5" style="background-color: #f678c3;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>
                                <div class="swatch orange">
                                    <input type="radio" name="color_code" id="swatch_4" value="#fa4e64"/>
                                    <label for="swatch_4" style="background-color: #fa4e64;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>
                                <div class="swatch yellow">
                                    <input type="radio" name="color_code" id="swatch_6" value="#43516c"/>
                                    <label for="swatch_6" style="background-color: #43516c;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>

                                <div class="swatch yellow">
                                    <input type="radio" name="color_code" id="swatch_7" value="#3d7a44"/>
                                    <label for="swatch_7" style="background-color: #3d7a44;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>

                                <div class="swatch yellow">
                                    <input type="radio" name="color_code" id="swatch_8" value="#c47933"/>
                                    <label for="swatch_8" style="background-color: #c47933;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>

                                <div class="swatch yellow">
                                    <input type="radio" name="color_code" id="swatch_9" value="#ab408b"/>
                                    <label for="swatch_9" style="background-color: #ab408b;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>

                                <div class="swatch yellow">
                                    <input type="radio" name="color_code" id="swatch_10" value="#ab4040"/>
                                    <label for="swatch_10" style="background-color: #ab4040;"><i
                                            class="mdi mdi-check"></i></label>
                                </div>
                            </section>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-secondary" id="lead-groups_button" type="submit"><i
                                    class="mdi mdi-floppy fs-5"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="lead-label-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="width: 100%;">
            <div class="modal-content" style="height: 100%;">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark" id="lead-label-formModalLabel">EditLabel</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="lead-label-form" id="lead-label-form" action="#">


                        {{-- <select multiple id="sample-select" name="label_id[]" placeholder="Native Select"
                         data-search="true" data-silent-initial-value-set="true" data-keep-always-open="false"
                         data-show-selected-options-first="true" data-selected-value="[2, 4]"
                         data-mark-search-results="true">--}}
                        <div class="row">
                            @foreach ($leadLabels as $leadLabel)
                                {{--                                        <div class="form-check form-check-inline">--}}
                                <div class="col-6">
                                    <div class="form-check mb-1">
                                        <div class="d-flex align-items-center">
                                            <table class="table table-sm table-borderless table-wrap mb-1 p-0"
                                                   width="100%" id="lead-table">
                                                <tbody>
                                                <tr>
                                                    <td style="width:90%;">
                                                        <div class="flex-shrink-0">
                                                            <input type="checkbox" class="form-check-input"
                                                                   id="chk_{{ $leadLabel->id }}"
                                                                   name="selected_lead_id[]"
                                                                   value="{{ $leadLabel->id }}"
                                                                {{ in_array($leadLabel->id, $leadArr) ? 'checked' : '' }}>
                                                            <label class="form-check-label text-dark"
                                                                   for="chk_{{ $leadLabel->id }}">{{ $leadLabel->name }}</label>

                                                        </div>
                                                    </td>

                                                    <td style="width:10%;">
                                                        <div class="flex-grow-1 ms-2 text-dark text-capitalize">
                                                            <i class="widget-icon rounded"
                                                               style="background-color:{{ $leadLabel->color_code }} !important;height:18px;width:18px;"></i>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                                {{-- <option value="{{ $leadLabel->id }}"
                                     {{ in_array($leadLabel->id, $leadArr) ? 'selected' : '' }}>
                                     {{ $leadLabel->name }}</option>--}}
                            @endforeach
                        </div>
                        {{--</select>--}}
                        <input type="hidden" id="lead_id" name="lead_id" value="{{ Request::segment(4) }}">


                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-primary" id="lead_label_button" type="submit"><i
                                    class="mdi mdi-floppy fs-5"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div class="modal fade assign-self-lead-modal" id="assign-self-lead-modal" aria-hidden="true" aria-labelledby="..."
         tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <lord-icon src="https://cdn.lordicon.com/zpxybbhl.json" trigger="loop"
                               colors="primary:#21297a,secondary:#ca6d9e" style="width:200px;height:200px">
                    </lord-icon>

                    <div class="mt-0 pt-2">
                        <h2 class="text-dark pb-3">This lead is currently assigned to someone else</h2>
                        <p class="text-dark pb-3" id="content-p"> You can only contact or edit leads that are assigned
                            to you. Please assign the lead to yourself if you wish to take action on them.</p>
                        <form class="assign-self-lead-form" id="assign-self-lead-form" action="#">
                            <input type="hidden" id="id" name="id" value="{{ Request::segment(4) }}">
                            <input type="hidden" name="assigned_to_user" value="{{ auth()->user()->id }}"/>
                            <input type="hidden" id="follow_up_date_assign_user" name="follow_up_date_assign_user"
                                   value="{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? $customers->last_follow_up_datetime : '' }}">
                        </form>

                        <!-- Toogle to second dialog -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" id="lead-assign_self_lead_button" form="assign-self-lead-form"
                                type="submit"><i class="mdi mdi-floppy fs-5"></i> Assign Client to Myself
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="follow-up-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="follow-up-formModalLabel">Schedule Follow Up</h3>
                    {{--                    <p class="text-muted">Drag and drop your event or click in the calendar</p> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2">


                    <div class="row">
                        <form class="follow-up-form" id="follow-up-form" action="#" novalidate="">
                            @if ($customers->estimate_status)
                                <div class="col-12 d-none">
                                    <div class="mb-3">
                                        <div class="mt-2">
                                            <div class="form-check form-radio-secondary form-check-inline">
                                                <input type="radio" id="status_draft_est" name="customRadio1"
                                                       class="form-check-input" value="Draft"
                                                    {{ $customers->estimate_status == 'Draft' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="status_draft_est">Draft</label>
                                            </div>
                                            <div class="form-check form-radio-info form-check-inline">
                                                <input type="radio" id="status_sent_est" name="customRadio1"
                                                       class="form-check-input" value="Sent"
                                                    {{ $customers->estimate_status == 'Sent' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="status_sent_est">Sent</label>
                                            </div>
                                            <div class="form-check form-radio-warning form-check-inline">
                                                <input type="radio" id="status_inprogress_est" name="customRadio1"
                                                       class="form-check-input" value="Inprogress"
                                                    {{ $customers->estimate_status == 'Inprogress' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="status_inprogress_est">In
                                                    Progress</label>
                                            </div>
                                            <div class="form-check form-radio-success form-check-inline">
                                                <input type="radio" id="status_accept_est" name="customRadio1"
                                                       class="form-check-input" value="Accept"
                                                    {{ $customers->estimate_status == 'Accept' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="status_accept_est">Accept</label>
                                            </div>
                                            <div class="form-check form-radio-danger form-check-inline">
                                                <input type="radio" id="status_decline_est" name="customRadio1"
                                                       class="form-check-input" value="Decline"
                                                    {{ $customers->estimate_status == 'Decline' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="status_decline_est">Decline</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating mb-3">
                                        <input type="text" id="follow_up_datetime_status"
                                               class="form-control bg-light" placeholder="Follow Up Date"
                                               name="follow_up_datetime_status" placeholder="Date" required>
                                        <label for="follow_up_datetime">Follow Up Date <span
                                                class="text-danger">*</span></label>
                                    </div>
                                    <input type="hidden" id="id" name="id" value="0">
                                    <input type="hidden" id="estimate_id" name="estimate_id"
                                           value="{{ $customers->estimate_id }}">
                                    <input type="hidden" id="old_status" name="old_status"
                                           value="{{ $customers->estimate_status }}">
                                    <input type="hidden" id="old_follow_up_date" name="old_follow_up_date"
                                           value="{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? $customers->last_follow_up_datetime : '' }}">
                                    <input type="hidden" id="customer_id" name="customer_id"
                                           value="{{ Request::segment(4) }}">
                                    <input type="hidden" id="est_flag" name="est_flag" value="u">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating">
                                    <textarea
                                        {{($main_company->follow_up_note_req_flg==1)?'required':''}} class="form-control bg-light activity_notes_flp"
                                        id="activity_notes"
                                        name="activity_notes"
                                        placeholder="Add Discussion Summary.."
                                        style="height: 150px"></textarea>
                                        <label for="activity_notes">Add Discussion Summary..</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-grid d-block">
                                    <button type="submit" class="btn btn-lg font-16 btn-primary"
                                            id="follow_up_form_buttons">
                                        <i class="mdi mdi-floppy fs-5"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>
                {{-- <div class="modal-footer d-block">
                <div class="d-grid">
                <button class="btn btn-lg font-16 btn-info" id="btn-new-event">
                <i class="mdi mdi-floppy fs-5"></i> Create New Event
                </button>
                </div>
                <button type="button" class="btn btn-primary" data-bs-target="#multiple-two" data-bs-toggle="modal"
                data-bs-dismiss="modal">Next
                </button>
                </div> --}}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="activity-change-estimate-status-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-1">
                    <h3 class="modal-title text-dark" id="activity-change-estimate-status-formModalLabel">Change
                        Status</h3>
                    {{--                    <p class="text-muted">Drag and drop your event or click in the calendar</p> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2">


                    <div class="row">
                        <form class="activity-change-estimate-status-form" id="activity-change-estimate-status-form"
                              action="#" novalidate="">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="mt-2">
                                        <div class="form-check form-radio-secondary form-check-inline">
                                            <input type="radio" id="status_draft" name="activity_estimate_status"
                                                   class="form-check-input" value="Draft">
                                            <label class="form-check-label" for="status_draft">Draft</label>
                                        </div>
                                        <div class="form-check form-radio-info form-check-inline d-none">
                                            <input type="radio" id="status_sent" name="activity_estimate_status"
                                                   class="form-check-input" value="Sent">
                                            <label class="form-check-label" for="status_sent">Sent</label>
                                        </div>
                                        <div class="form-check form-radio-warning form-check-inline">
                                            <input type="radio" id="status_inprogress"
                                                   name="activity_estimate_status" class="form-check-input"
                                                   value="Inprogress">
                                            <label class="form-check-label" for="status_inprogress">In
                                                Progress</label>
                                        </div>
                                        <div class="form-check form-radio-success form-check-inline">
                                            <input type="radio" id="status_accept" name="activity_estimate_status"
                                                   class="form-check-input" value="Accept">
                                            <label class="form-check-label" for="status_accept">Accept</label>
                                        </div>
                                        <div class="form-check form-radio-danger form-check-inline">
                                            <input type="radio" id="status_decline" name="activity_estimate_status"
                                                   class="form-check-input" value="Decline">
                                            <label class="form-check-label" for="status_decline">Decline</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <input type="hidden" id="activity_id" name="activity_id" value="0">
                                    <input type="hidden" id="activity_estimate_id" name="activity_estimate_id"
                                           value="">
                                    <input type="hidden" id="activity_estimate_no" name="activity_estimate_no"
                                           value="">
                                    <input type="hidden" id="old_activity_status" name="old_activity_status"
                                           value="">
                                    <input type="hidden" id="old_activity_follow_up_date"
                                           name="old_activity_follow_up_date"
                                           value="{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? $customers->last_follow_up_datetime : '' }}">
                                    <input type="hidden" id="activity_customer_id" name="activity_customer_id"
                                           value="{{ Request::segment(4) }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control bg-light" id="activity_estimate_notes" name="activity_estimate_notes" placeholder="Add Discussion Summary.." style="height: 150px"></textarea>
                                        <label for="activity_estimate_notes">Add Discussion Summary..</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-grid d-block">
                                    <button type="submit" class="btn btn-lg font-16 btn-primary"
                                            id="activity_change_estimate_status_form_buttons">
                                        <i class="mdi mdi-floppy fs-5"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>
                {{-- <div class="modal-footer d-block">
                <div class="d-grid">
                <button class="btn btn-lg font-16 btn-info" id="btn-new-event">
                <i class="mdi mdi-floppy fs-5"></i> Create New Event
                </button>
                </div>
                <button type="button" class="btn btn-primary" data-bs-target="#multiple-two" data-bs-toggle="modal"
                data-bs-dismiss="modal">Next
                </button>
                </div> --}}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="attachment-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark attachment-modal-title">Create Customer</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="attachment-form" id="attachment-form" action="#" accept-charset="utf-8"
                          enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control bg-light text-dark" id="name"
                                           name="name"
                                           required=""
                                           placeholder="Name">
                                    <label for="name" class="form-label">Folder Name <span class="text-danger">*</span></label>
                                </div>
                            </div>


                            <div class="col-12 mb-2">
                                <input class="form-control" type="hidden" id="folder_id" name="folder_id"
                                       value="{{ Request::segment(4) }}">
                                <div class="form-floating">
                                    <textarea class="form-control bg-light text-dark" id="description"
                                              name="description" placeholder="Enter description"
                                              style="height: 80px;"></textarea>
                                    <label for="description" class="form-label">Folder Description</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="files_name" class="form-label">Files</label>
                                <input type="file" class="form-control" data-parsley-trigger="change"
                                       name="files_name[]"
                                       id="files_name" data-parsley-required="true" multiple>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button class="btn btn-primary fullscreen" form="attachment-form" id="attachment_button"
                                type="submit">
                            <i class="uil-cloud-upload fs-5"></i> Upload
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal -->
    <div id="attachment-file-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Attachment Files</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="attachment-file-upload-form" id="attachment-file-upload-form" action="#"
                          accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-10">
                                <input class="form-control" type="hidden" id="file_folder_id" name="file_folder_id"
                                       value="{{ Request::segment(4) }}">
                                <input class="form-control" type="hidden" id="file_attachment_id"
                                       name="file_attachment_id" value="0">

                                {{--                            <div class="col-12">--}}
                                {{--                                <label for="files_name" class="form-label">Upload Files</label>--}}
                                <input type="file" class="form-control" data-parsley-trigger="change"
                                       name="only_files_name[]"
                                       id="only_files_name" data-parsley-required="true" multiple>
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary" id="attachment_file_button"><i
                                        class="uil-cloud-upload fs-5"></i></button>
                            </div>

                        </div>
                    </form>
                    {{--                    <div class="row">--}}
                    {{--                        <div class="col-12">--}}
                    <h5 class="bg-light pt-3 ps-2 pb-1 pe-2">
                        <p class="font-14">
                            <strong>Folder Name: </strong>
                            <span class="attachment_name_span"></span>
                        </p>
                        <p class="font-14">
                            <strong>Folder Description: </strong>
                            <span class="attachment_description_span"></span>
                        </p>
                    </h5>
                    {{--                        </div>--}}
                    {{--                    </div>--}}

                    <div class="row">
                        <div class="col-12 attachment-file-info" id="attachment-file-info">
                        </div>
                    </div>
                    {{--<div class="card mb-2 shadow-none border">
                        <div class="p-1">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="{{ asset('assets/images/projects/project-1.jpg')}}"
                                         class="avatar-sm rounded" alt="file-image">
                                </div>
                                <div class="col ps-0">
                                    <a href="javascript:void(0);" class="text-dark fw-bold">new-contarcts.docx</a>
                                    <p class="mb-0">1.25 MB</p>
                                </div>
                                <div class="col-auto" id="tooltip-container10">
                                    <!-- Button -->
                                    <a href="javascript:void(0);" class="btn btn-link text-muted btn-lg p-0"
                                       aria-label="Download" data-bs-original-title="Download">
                                        <i class="uil uil-cloud-download"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-link text-danger btn-lg p-0"
                                       aria-label="Delete" data-bs-original-title="Delete">
                                        <i class="uil uil-multiply"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-2 shadow-none border">
                        <div class="p-1">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="{{ asset('assets/images/projects/project-1.jpg')}}"
                                         class="avatar-sm rounded" alt="file-image">
                                </div>
                                <div class="col ps-0">
                                    <a href="javascript:void(0);" class="text-dark fw-bold">new-contarcts.docx</a>
                                    <p class="mb-0">1.25 MB</p>
                                </div>
                                <div class="col-auto" id="tooltip-container10">
                                    <!-- Button -->
                                    <a href="javascript:void(0);" class="btn btn-link text-muted btn-lg p-0"
                                       aria-label="Download" data-bs-original-title="Download">
                                        <i class="uil uil-cloud-download"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-link text-danger btn-lg p-0"
                                       aria-label="Delete" data-bs-original-title="Delete">
                                        <i class="uil uil-multiply"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-2 shadow-none border">
                        <div class="p-1">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="{{ asset('assets/images/projects/project-1.jpg')}}"
                                         class="avatar-sm rounded" alt="file-image">
                                </div>
                                <div class="col ps-0">
                                    <a href="javascript:void(0);" class="text-dark fw-bold">new-contarcts.docx</a>
                                    <p class="mb-0">1.25 MB</p>
                                </div>
                                <div class="col-auto" id="tooltip-container10">
                                    <!-- Button -->
                                    <a href="javascript:void(0);" class="btn btn-link text-muted btn-lg p-0"
                                       aria-label="Download" data-bs-original-title="Download">
                                        <i class="uil uil-cloud-download"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-link text-danger btn-lg p-0"
                                       aria-label="Delete" data-bs-original-title="Delete">
                                        <i class="uil uil-multiply"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                </div>
                {{--<div class="modal-footer">
                    <div class="text-end">
                        <button class="btn btn-primary fullscreen" form="attachment-form" id="attachment_button"
                                type="submit">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </button>
                    </div>
                </div>--}}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script> -->
    @include('layouts.partials.datatable-script')
    <script src="{{ asset('vendor/select2/js/select2.min.js')}}"></script>
    <script src="{{ asset('js/virtual-select.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    {{--    <script src="https://raw.githubusercontent.com/sa-si-dev/tooltip/master/dist/tooltip.min.js"></script> --}}
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('assets/js/pages/demo.datatable-init.js')}}"></script> --}}
    <!-- end demo js-->
    <script>
        $(document).on('click', '#mediumButton', function (event) {
            event.preventDefault();
            let href = $(this).attr('data-attr');
            $('#estimateLimitModal').modal("show");
        });
        /* $(document).on('click', 'tr a.copy_text', function(e) {
        // $("a.copy_text").click(function(e){


        });*/
        VirtualSelect.init({
            ele: '#sample-select',
            selectedValue: [10, 11],
            dropboxWidth: '100%'
            /* options: [
            {label: 'Options 1', value: '1'},
            {label: 'Options 2', value: '2'},
            {label: 'Options 3', value: '3'},
            ],*/
        });
        $(document).ready(function () {
            $("#accordionlefticonExample2").click(function(){
                $("#accor_lefticonExamplecollapse21").slideToggle('slow');
            });

            $("#accordionlefticonExample1").click(function(){
                $("#accor_lefticonExamplecollapse11").slideToggle('slow');
            });

            $("#accordionlefticonExample3").click(function(){
                $("#accor_lefticonExamplecollapse31").slideToggle('slow');
            });

            var start = 10;
            localStorage.setItem('timeline_per_page', start);
           /* $(window).scroll(function() {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 20) {
                    timelineActivity('{{ Request::segment(4) }}');
                    start = start + 10;
                    localStorage.setItem('timeline_per_page', start);
                }
            });*/
            $('#timeline-info').scroll(function() {
                var div = $(this);
                if (div.scrollTop() + div.innerHeight() >= div.prop('scrollHeight') - 40) {
                    // If scrolled to the bottom of the div within a 20-pixel tolerance
                    timelineActivity('{{ Request::segment(4) }}');
                    start = start + 10;
                    localStorage.setItem('timeline_per_page', start);
                }
            });

            $(document).on('click', '.phone_no_copy', function(e) {
                e.preventDefault();
                var copyText = $(this).attr('data-url');

                document.addEventListener('copy', function(e) {
                    e.clipboardData.setData('text/plain', copyText);
                    e.preventDefault();
                }, true);
                document.execCommand('copy');
                toastrSuccess('Successfully copied phone no');
            });

            $(document).on('click', '.email_copy', function(e) {
                e.preventDefault();
                var copyText = $(this).attr('data-url');
                if(!copyText){
                    toastrError('email does not available');
                    return false;
                }
                document.addEventListener('copy', function(e) {
                    e.clipboardData.setData('text/plain', copyText);
                    e.preventDefault();
                }, true);
                document.execCommand('copy');
                toastrSuccess('Successfully copied email');
            });

            "use strict";
            var table = $("#lead-estimate-datatable").DataTable({
                // dom: 'Bfrtip',
                // dom: "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                //     "<'row'<'col-sm-12'tr>>" +
                //     "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'Show all']
                ],
                "paging": false,
                searching:false,
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                lengthChange: !1,
                /*buttons: [{
                    extend: 'pageLength',
                    attr: {
                        class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                    },
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    text: '<i class="mdi mdi-microsoft-excel fs-4"></i>',
                    attr: {
                        title: 'Excel',
                        class: 'btn btn-light buttons-html5 buttons-excel',
                    },
                    title: 'Estimate List',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                    attr: {
                        title: 'Column visibility',
                        class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                    },
                    title: 'Estimate List',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    text: '<i class="mdi mdi-refresh fs-4"></i>',
                    attr: {
                        title: 'Refresh',
                        class: 'btn btn-light buttons-collection',
                    },
                    action: function ( e, dt, node, config ) {
                        localStorage.setItem('fil_estimate_end','{{date("Y-m-d")}}');
                        localStorage.removeItem('lead-estimate-datatable');
                        location.reload();
                        /!*dt.clear().draw();
                        dt.ajax.reload();*!/
                    }
                }],*/
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function(settings, data) {

                },
                stateLoadParams: function(settings, data) {

                },
                stateSaveCallback: function(settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.quotes.index-by-customer', ['tenant' => $segment]) }}",
                    data: function(d) {
                        d.id ='{{ Request::segment(4) }}'
                    }
                },
                "order": [
                    [1, 'desc'],

                ],
                // "order": [[ 1, "asc" ]],
                columns: [{
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' +
                            row.action + '">';
                    },
                    "visible": false,
                },
                    {
                        data: 'estimate_date',
                        name: 'estimate_date'
                    },
                    {
                        data: 'estimate_no',
                        name: 'estimate_no',
                        render: function(data, type, row) {
                            var edit_fun = "{{ url('lead/timeline') }}/" + row.customer_id_decode;
                            return '<a class="fw-bold" href="' + edit_fun + '" id="edit_' + row
                                .action + '">' + row.estimate_no + '</a>'
                        }
                    },
                    {
                        data: 'net_amount',
                        name: 'total'
                    },
                    {
                        data: 'sales_person_name',
                        name: 'sales_person_name'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            let fun_status = "'" + row.action + "'";
                            let old_status = "'" + row.status + "'";
                            let sel_draft = '';
                            let sel_sent = '';
                            let sel_inprogress = '';
                            let sel_accept = '';
                            let sel_decline = '';
                            let sel_bg_color = '';
                            let sel_new_name = '';

                            if (row.status == 'Draft') {
                                sel_new_name = row.status;
                                sel_bg_color = 'text-secondary';
                            }
                            if (row.status == 'Sent') {
                                sel_new_name = row.status;
                                sel_bg_color = 'text-primary';
                            }

                            if (row.status == 'Inprogress') {
                                sel_new_name = 'In Progress';
                                sel_bg_color = 'text-warning';
                            }

                            if (row.status == 'Accept') {
                                sel_new_name = row.status;
                                sel_bg_color = 'text-success';
                            }

                            if (row.status == 'Decline') {
                                sel_new_name = row.status;
                                sel_bg_color = 'text-danger';
                            }
                            let follow_action = "'" + row.action + "','" + row.estimate_no + "','" +
                                row.customer_name + "','" + row.mobile_no + "'";
                            return '<span class="'+sel_bg_color+' fw-bold">'+sel_new_name+'</span>';
                        }
                    },
                   /* {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        render: function(data, type, row) {

                            var edit_fun = "{{ url('quotes/edit') }}/" + row.action;
                            var delete_fun = "remove_id('" + row.action +
                                "','{{ route('tenant.quotes.delete', ['tenant' => $segment]) }}','#lead-estimate-datatable')";
                            {{-- var follow_up_fun = "follow_up_list('" + row.action + "','{{route('tenant.event.index', ['tenant' => $segment])}}','enc',0,1)"; --}}
                            var duplicate_est_fun = "estimate_duplicate('" + row.action + "')";
                            var follow_up_fun =
                                " openFollowUpModal('#follow-up-modal','Schedule Follow Up','#follow-up-form','.modal-title','" +
                                row.action + "','0','" + row.estimate_no + "','" + row.status +
                                "','{{ route('tenant.event.index', ['tenant' => $segment]) }}',1,'" + row.customer_name + "','" +
                                row.mobile_no + "')";
                            var view_fun = "{{ url('quotes/show') }}/" + row.action;
                            var status_fun = "activity_change_status_fun('"+row.status+"','"+row.estimate_no+"','"+row.action+"',"+row.customer_id+")";


                            return (row.status!='')?'<div class="btn-group dropdown btn-group-sm">' +
                                '<a href="#" class="table-action-btn dropdown-toggle arrow-none btn btn-light btn-xs" data-bs-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-dots-horizontal"></i></a>' +
                                ' <div class="dropdown-menu dropdown-menu-end" style="">' +
                                ' <a class="dropdown-item" href="' + edit_fun + '" id="edit_' + row
                                    .action +
                                '"><i class="mdi mdi-square-edit-outline me-2 text-muted vertical-middle"></i>Revise Estimate</a>' +
                                '<a href="javascript:void(0);" onclick="'+status_fun+'" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-2 text-muted vertical-middle"></i>Change Status</a>'+
                                '<a href="' + row.download_action +
                                '" title="Download" class="dropdown-item me-1" target="_blank" download>' +
                                '<i class="mdi mdi-download me-2 text-muted vertical-middle"></i>Download' +
                                '</a>' +

                                '<a href="javascript:void(0)" title="Share" class="dropdown-item copy_text" data-url="{!! url('/quotes/generate-link') !!}/' +
                                row.action +
                                '"><i class="mdi mdi-share-variant me-2 text-muted fs-4"></i>Share</a>' +

                                ' </div>' +
                                ' </div>' : '';
                        }
                    },*/
                ],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                "fnCreatedRow": function(nRow, data, iDataIndex) {
                    $(nRow).attr('id', iDataIndex + 1);
                }
            });
            table.buttons().container().appendTo("#estimate-datatable_wrapper .col-md-6:eq(0)"), $(
                "#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })
            $('#leads_stages_id').on('change', function () {

                var selectedOption = $(this).find(":selected");

                // Get the data-id attribute value
                var dataIdValue = selectedOption.data("id");

                if (dataIdValue == 6) {
                    $(".lost_reason_div").show();
                    $('#lost_reason_id').prop('required', true);
                    // $('#lost_reason_name').val(selectedOption.text());
                } else {
                    $(".lost_reason_others_div").hide();
                    $(".lost_reason_div").hide();
                    $('#lost_reason_id').prop('required', false);
                    $('#lost_reason_others').prop('required', false);
                    // $('#lost_reason_name').val('');
                }
            });

            $('#lost_reason_id').on('change', function () {

                var selectedOption = $(this).find(":selected");

                // Get the data-id attribute value
                var dataIdValue = selectedOption.data("id");

                if (dataIdValue == 1) {
                    $(".lost_reason_others_div").show();
                    $('#lost_reason_others').prop('required', true);
                    $('#lost_reason_others').text('{{$customers->others_reason}}');
                } else {
                    $(".lost_reason_others_div").hide();
                    $('#lost_reason_others').prop('required', false);
                    $('#lost_reason_others').text('');
                }
            });

            $('#leads_stages_id_followup').on('change', function () {

                var selectedOption = $(this).find(":selected");

                // Get the data-id attribute value
                var dataIdValue = selectedOption.data("id");

                if (dataIdValue == 6) {
                    $(".lost_reason_div_followup").show();
                    $('#lost_reason_id_followup').prop('required', true);
                    // $('#lost_reason_name').val(selectedOption.text());
                } else {
                    $(".lost_reason_others_div_followup").hide();
                    $(".lost_reason_div_followup").hide();
                    $('#lost_reason_id_followup').prop('required', false);
                    $('#lost_reason_others_followup').prop('required', false);
                    // $('#lost_reason_name').val('');
                }
            });

            $('#lost_reason_id_followup').on('change', function () {

                var selectedOption = $(this).find(":selected");

                // Get the data-id attribute value
                var dataIdValue = selectedOption.data("id");

                if (dataIdValue == 1) {
                    $(".lost_reason_others_div_followup").show();
                    $('#lost_reason_others_followup').prop('required', true);
                    $('#lost_reason_others_followup').text('{{$customers->others_reason}}');
                } else {
                    $(".lost_reason_others_div_followup").hide();
                    $('#lost_reason_others_followup').prop('required', false);
                    $('#lost_reason_others_followup').text('');
                }
            });

            /*$('#country_code').select2({
                dropdownParent: $('body')
            }).on('select2:select', function (e) {
                var data = e.params.data;

                console.log(data.id);
                $("#country_id").val($('option:selected', this).data('id'));

                getStatesList($('option:selected', this).data('id'));

                $('#whatsapp_country_code').val(data.id);
                $('#whatsapp_country_code').select2({dropdownParent: $('body')}).trigger('change');
            });*/

            $('#country_code').select2({
                dropdownParent: $('#sel_cc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
                var selectedOption = $(this).find(':selected');
                var dataId = selectedOption.data('id');
                // console.log("Selected data-id: " + dataId);
                $("#country_id").val($('option:selected', this).data('id'));

                getStatesList($('option:selected', this).data('id'));

                // $('#whatsapp_country_code').val(data.id).change();
                $("#whatsapp_country_code option[data-id='" + dataId + "']").prop("selected", true);
                $('#whatsapp_country_code').select2({dropdownParent: $('#sel_wcc')}).trigger('change');
                console.log($('#country_id').find(':selected').data('id'));

                var option = $('#currency_name').find('option[data-id="' + dataId + '"]');
                $('#currency_name').val(option.val()).trigger('change');
            });

            $('#whatsapp_country_code').select2({
                dropdownParent: $('#sel_wcc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
            });
            /*$('#currency_name').select2({
                dropdownParent: $('#sel_cn')
            }).on('select2:select', function (e) {
                var data = e.params.data;
            });*/
            $("#follow_up_datetime").flatpickr({
                enableTime: true,
                dateFormat: "d-m-Y",
                disable: [
                    {
                        from: "01-01-1970",
                        to: "{{ (!empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && Carbon::now()->gt($customers->last_follow_up_datetime)) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->subDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}"
                    }
                    /* ,
                     {
                         from: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->addDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}",
                        to: "{{Carbon::now()->subDays(1)->format('d-m-Y')}}"
                    }*/
                ],
// inline:true, moment().format("DD-MM-YYYY hh:mm:ss a")
                defaultDate: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y h:i A') : '' }}",
                dateFormat: 'd-m-Y h:i K',
// dateFormat: "m-d-Y H:i",
// time_24hr: true,

// minuteIncrement: 1
// dateFormat:d-m-Y
            });
            $("#follow_up_datetime_status").flatpickr({
                enableTime: true,
                dateFormat: "d-m-Y",
                disable: [
                    {
                        from: "01-01-1970",
                        to: "{{ (!empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && Carbon::now()->gt($customers->last_follow_up_datetime)) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->subDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}"
                    },
                    {
                        from: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->addDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}",
                        to: "{{Carbon::now()->subDays(1)->format('d-m-Y')}}"
                    }
                ],
                /* enable: [
                     "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y') : '' }}"
                ],*/
                // minDate: "today",
                // allowInput: true,
// inline:true, moment().format("DD-MM-YYYY hh:mm:ss a")
                defaultDate: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00'
? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y h:i A')
: \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', date('d-m-Y H:i:s'))->format('d-m-Y H:i') }}",
                dateFormat: 'd-m-Y h:i K',
// dateFormat: "m-d-Y H:i",
// time_24hr: true,

// minuteIncrement: 1
// dateFormat:d-m-Y
            });
            $(".advance-option").click(function () {
//Do stuff when clicked
                $(".advance-option").addClass('d-none');
                $(".advance-option-div").removeClass('d-none');
            });

            $(".advance-options-td").click(function () {
//Do stuff when clicked
                $(".advance-options-td").addClass('d-none');
                $(".advance-options-tr").removeClass('d-none');
            });
            $("input[name='customer_type']").click(function () {
                if ($("#customer_type_business").is(":checked")) {
                    $(".cust_company_name_div").show();
                    $("#company_name").prop("required", true);
                } else {
                    $(".cust_company_name_div").hide();
                    $("#company_name").prop("required", false);
                }
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            timelineActivity('{{ Request::segment(4) }}');

            $('.customer-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = $('.customer-form').serializeArray();
                    formData.push({
                        name: 'phone_no_country_id',
                        value: $("#country_code").find(':selected').data('id')
                    });
                    formData.push({
                        name: 'whatsapp_no_country_id',
                        value: $("#whatsapp_country_code").find(':selected').data('id')
                    });
                    /*formData.push({
                        name: 'currency_name_country_id',
                        value: $("#currency_name").find(':selected').data('id')
                    });*/
                    $.ajax({
                        async: true,
                        type: 'POST',
                        url: '{{ route('tenant.customer.store', ['tenant' => $segment]) }}',
                        /*contentType: false,
                        cache: false,
                        processData: false,*/
                        data: formData,
                        // data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#customer_button").prop('disabled', true);
                            $("#customer_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');

                            timelineActivity('{{ Request::segment(4) }}');
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            view_id('{{ Request::segment(4) }}');
                            $('#customer-modal').modal('toggle');
                            $("#customer_button").html('Save');
                            $("#customer_button").prop(
                                '<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
                }
            });

            $('.lead-description-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.lead-description', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#lead_description_button").prop('disabled', true);
                            $("#lead_description_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $(".description_small").html(data.lead_description);
                            $('#lead-description-modal').modal('toggle');
                            $("#lead_description_button").prop('disabled', false);
                            $("#lead_description_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#lead_description_button").prop('disabled', false);
                            $("#lead_description_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            view_id('{{ Request::segment(4) }}');
                            $("#customer_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            $("#customer_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.lead-stage-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('lead-stage-form'));
                    formData.append('lost_reason_name', $('#lost_reason_id').find(":selected").text());
                    formData.append('lead_stage_data_id', $('#leads_stages_id').find(":selected").data('id'));
                    $.ajax({
                        //async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.lead-stage', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        // data: new FormData(this),
                        data: formData,
                        dataType: "json",
                        beforeSend: function () {
                            $("#lead_stage_button").prop('disabled', true);
                            $("#lead_stage_button").html(
                                '<i class="uil-arrow-circle-right"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            // $(".description_small").html(data.lead_description);
                            view_id('{{ Request::segment(4) }}');
                            timelineActivity('{{ Request::segment(4) }}');
                            $('#lead-stage-modal').modal('toggle');
                            $("#lead_stage_button").prop('disabled', false);
                            $("#lead_stage_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                            // location.reload();
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#lead_stage_button").prop('disabled', false);
                            $("#lead_stage_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#lead_stage_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                            $("#lead_stage_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.activity-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('activity-form'));
                    formData.append('lost_reason_name', $('#lost_reason_id_followup').find(":selected").text());
                    formData.append('lead_stage_data_id', $('#leads_stages_id_followup').find(":selected").data('id'));
                    $.ajax({
// async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.activity-save', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: formData,
                        // data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#activity_form_buttons").prop('disabled', true);
                            $("#activity_form_buttons").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            let xz= $('#leads_stages_id_followup').val();
                            toastrSuccess('Successfully saved...', 'Success');
                            timelineActivity('{{ Request::segment(4) }}');
                            view_id('{{ Request::segment(4) }}');
                            $('#activity-modal').modal('toggle');
                            $("#activity_form_buttons").prop('disabled', false);
                            $("#activity_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                            resetFormValidation("#activity-form");
                            resetForm("#activity-form");
                            $('#leads_stages_id_followup').val(xz)
                            // location.reload();
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#activity_form_buttons").prop('disabled', false);
                            $("#activity_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            getActivitycounts('{{ Request::segment(4) }}');
                            $("#activity_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                            $("#activity_form_buttons").prop('disabled', false);
                        }
                    });
                }
            });

            $('.follow-up-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
// async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.activity-follow-up-save', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#follow_up_form_buttons").prop('disabled', true);
                            $("#follow_up_form_buttons").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            location.reload();
                            /*timelineActivity('{{ Request::segment(4) }}');
$('#follow-up-modal').modal('toggle');
$("#follow_up_form_buttons").prop('disabled', false);
$("#follow_up_form_buttons").html('<i class="mdi mdi-floppy fs-5"></i> Save');
resetFormValidation("#follow-up-form");
resetForm("#follow-up-form");*/
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#follow_up_form_buttons").prop('disabled', false);
                            $("#follow_up_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#follow_up_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                            $("#follow_up_form_buttons").prop('disabled', false);
                        }
                    });
                }
            });

            $('.assign-lead-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
// async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.lead-assigned-to-user', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#assign_lead_button").prop('disabled', true);
                            $("#assign_lead_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#lead-description-modal').modal('toggle');
                            $("#assign_lead_button").prop('disabled', false);
                            $("#assign_lead_button").html(
                                '<i class="mdi mdi-check"></i> CONFIRM');
                            location.reload();
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#assign_lead_button").prop('disabled', false);
                            $("#assign_lead_button").html(
                                '<i class="mdi mdi-check"></i> CONFIRM');
                        },
                        complete: function (data) {
                            $("#assign_lead_button").html(
                                '<i class="mdi mdi-check"></i> CONFIRM');
                            $("#assign_lead_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.assign-self-lead-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
// async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.lead-assigned-to-user', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#assign_self_lead_button").prop('disabled', true);
                            $("#assign_self_lead_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $("#assign_self_lead_button").prop('disabled', false);
                            $("#assign_self_lead_button").html(
                                '<i class="mdi mdi-check"></i> Assign Client to Myself');
                            location.reload();
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#assign_self_lead_button").prop('disabled', false);
                            $("#assign_self_lead_button").html(
                                '<i class="mdi mdi-check"></i> Assign Client to Myself');
                        },
                        complete: function (data) {
                            $("#assign_self_lead_button").html(
                                '<i class="mdi mdi-check"></i> Assign Client to Myself');
                            $("#assign_self_lead_button").prop('disabled', false);
                        }
                    });
                }
            });

            getStatesList({{ $expData->country_id }});
            formValition('#activity-form');
            formValition('#customer-form');
            formValition('#assign-lead-form');
            formValition('#lead-groups-form');

            $('.lead-groups-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead-groups.store', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
// data: $('.category-form').serialize(),
                        dataType: "json",
                        beforeSend: function () {
                            $("#lead-groups_button").prop('disabled', true);
                            $("#lead-groups_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            console.log(data.name);
                            var options = {
                                label: data.name,
                                value: data.id
                            };
                            document.querySelector('#sample-select').addOption(options);
// document.querySelector('#example-select').setValue(value);
// console.log(tmp);
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#lead-groups-modal').modal('toggle');

                            $("#lead-groups_button").prop('disabled', false);
                            $("#lead-groups_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#lead-groups_button").prop('disabled', false);
                            $("#lead-groups_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#lead-groups_button").html('Save');
                            $("#lead-groups_button").prop(
                                '<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
                }
            });

            $('.lead-label-form').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serializeArray();
// formData.push({
//     name: 'selected_lead_id',
//     value: document.querySelector('#sample-select').value
// });

// console.log(formData);
                if ($(this).parsley().isValid()) {

                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.label-save', ['tenant' => $segment]) }}',
                        /* contentType: false,
                        cache: false,
                        processData: false,*/
                        data: formData,
                        dataType: "json",
                        beforeSend: function () {
                            $("#lead_label_button").prop('disabled', true);
                            $("#lead_label_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
// $(".description_small").html(data.lead_description);
                            $('#lead-label-modal').modal('toggle');
                            $("#lead_label_button").prop('disabled', false);
                            $("#lead_label_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                            view_id('{{ Request::segment(4) }}');
                            // location.reload();
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#lead_label_button").prop('disabled', false);
                            $("#lead_label_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#lead_label_button").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                            $("#lead_label_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.description_td').click(function () {
                @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                /*|| auth()->user()->company_id == null*/
                @if (
                (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                $customers->assigned_to_user == auth()->user()->id)
                {{-- || auth()->user()->company_id == null--}}
                let description = $(".description_small").text();
                if (description == '-') {
                    description = '';
                }

                $("#lead_description").focus();
                $("#lead_description").val(description);
                $("#lead-description-form").parsley().reset();
                $("#lead-description-formModalLabel").html("Lead Description");
                $("#lead-description-modal").modal('show');
                @else
                OpenModalAssignLead(0, '#assign-self-lead-modal',
                    '#assign-self-lead-formModalLabel', 'Assign to Self Lead',
                    '#assign-self-lead-form');
                @endif
                @else
                accessDeniedOpenModal('#access-denied-modal',
                    'Oops! You don’t have permission to access on the user assigned.');
                @endif

            });

            $('.lead_stage_td').click(function () {
                @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                /*|| auth()->user()->company_id == null*/
                @if (
                (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                $customers->assigned_to_user == auth()->user()->id)
                {{-- || auth()->user()->company_id == null--}}
                let description = $(".lead_stage_small").text();
                if (description == '-') {
                    description = '';
                }

                // $("#lead_description").focus();
                // $("#lead_description").val(description);
                $("#lead-stage-form").parsley().reset();
                $("#lead-stage-formModalLabel").html("Lead Stage");
                $("#lead-stage-modal").modal('show');
                @else
                OpenModalAssignLead(0, '#assign-self-lead-modal',
                    '#assign-self-lead-formModalLabel', 'Assign to Self Lead',
                    '#assign-self-lead-form');
                @endif
                @else
                accessDeniedOpenModal('#access-denied-modal',
                    'Oops! You don’t have permission to access on the user assigned.');
                @endif

            });

            $('.label_td').click(function () {
                @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                /*|| auth()->user()->company_id == null*/
                @if (
                (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                $customers->assigned_to_user == auth()->user()->id)
                /*|| auth()->user()->company_id == null*/
                {{--   let description = $(".description_small").text();
                if (description == '-') {
                description = '';
                } --}}

                /*$("#lead_description").focus();
                $("#lead_description").val(description);*/
                $("#lead-label-form").parsley().reset();
                $("#lead-label-formModalLabel").html("Labels");
                $("#lead-label-modal").modal('show');
                @else
                OpenModalAssignLead(0, '#assign-self-lead-modal',
                    '#assign-self-lead-formModalLabel', 'Assign to Self Lead',
                    '#assign-self-lead-form');
                @endif
                @else
                accessDeniedOpenModal('#access-denied-modal',
                    'Oops! You don’t have permission to access on the user assigned.');
                @endif

            });

            $('.activity-change-estimate-status-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
// async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.activity-change-estimate-status-save', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#activity_change_estimate_status_form_buttons").prop('disabled',
                                true);
                            $("#activity_change_estimate_status_form_buttons").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            location.reload();
                            /*timelineActivity('{{ Request::segment(4) }}');
$('#follow-up-modal').modal('toggle');
$("#activity_change_estimate_status_form_buttons").prop('disabled', false);
$("#activity_change_estimate_status_form_buttons").html('<i class="mdi mdi-floppy fs-5"></i> Save');
resetFormValidation("#follow-up-form");
resetForm("#follow-up-form");*/
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#activity_change_estimate_status_form_buttons").prop('disabled',
                                false);
                            $("#activity_change_estimate_status_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#activity_change_estimate_status_form_buttons").html(
                                '<i class="mdi mdi-floppy fs-5"></i> Save');
                            $("#activity_change_estimate_status_form_buttons").prop('disabled',
                                false);
                        }
                    });
                }
            });

            getAttachment('{{ Request::segment(4) }}');

            $('.attachment-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('attachment-form'));
                    $.ajax({
                        // async: false,
                        type: 'POST',
                        url: '{{route('tenant.folder.file-upload', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        // data: new FormData(this),
                        data: formData,
                        dataType: "json",
                        beforeSend: function () {
                            $("#attachment_button").prop('disabled', true);
                            $("#attachment_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#attachment-modal').modal('toggle');
                            getAttachment('{{ Request::segment(4) }}');
                            $("#attachment_button").prop('disabled', false);
                            $("#attachment_button").html('<i class="uil-cloud-upload fs-5"></i> Upload');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#attachment_button").prop('disabled', false);
                            $("#attachment_button").html('<i class="uil-cloud-upload fs-5"></i> Upload');
                        },
                        complete: function (data) {
                            $("#attachment_button").html('Save');
                            $("#attachment_button").prop('disabled', false);
                        }
                    });
                }
            });
            $('.attachment-file-upload-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('attachment-file-upload-form'));
                    $.ajax({
                        // async: false,
                        type: 'POST',
                        url: '{{route('tenant.folder.folder-file-upload', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        // data: new FormData(this),
                        data: formData,
                        dataType: "json",
                        beforeSend: function () {
                            $("#attachment_file_button").prop('disabled', true);
                            $("#attachment_file_button").html('<i class="mdi mdi-spin mdi-loading"></i>');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            // $('#attachment-modal').modal('toggle');
                            resetFormValidation("#attachment-file-upload-form");
                            resetForm("#attachment-file-upload-form");
                            get_attachment_file($("#file_attachment_id").val());
                            getAttachment('{{ Request::segment(4) }}');
                            $("#attachment_file_button").prop('disabled', false);
                            $("#attachment_file_button").html('<i class="uil-cloud-upload fs-5"></i>');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#attachment_file_button").prop('disabled', false);
                            $("#attachment_file_button").html('<i class="uil-cloud-upload fs-5"></i>');
                        },
                        complete: function (data) {
                            $("#attachment_file_button").html('<i class="uil-cloud-upload fs-5"></i>');
                            $("#attachment_file_button").prop('disabled', false);
                        }
                    });
                }
            });
        });

        function remove_follow_up_date(id) {

            /*const { value: text } = Swal.fire({
                title: 'Are you sure remove follow up',
                input: 'textarea',
                inputLabel: 'Message',
                inputPlaceholder: 'Type your message here...',
                inputAttributes: {
                    'aria-label': 'Type your message here'
                },
                showCancelButton: true
            })

            if (text) {
                Swal.fire(text)
            }
            return false;*/
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonColor: "#3085D6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, remove follow up!",
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-danger ml-1",
                buttonsStyling: !1,
                preConfirm: function () {
                    Swal.fire({
                        // title: 'Remove Follow Up',
                        input: 'textarea',
                        inputPlaceholder: 'Type your message here...',
                        inputAttributes: {
                            autocapitalize: 'off',
                            'aria-label': 'Type your message here'
                        },
                        confirmButtonColor: "#3085D6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Remove Follow Up",
                        cancelButtonText: "Cancel",
                        confirmButtonClass: "btn btn-primary",
                        cancelButtonClass: "btn btn-danger ml-1",
                        buttonsStyling: !1,
                        showCancelButton: true,
                        /* showCancelButton: true,
                         confirmButtonText: 'Look up',*/
                        showLoaderOnConfirm: true,
                        preConfirm: function (textarea) {
                            if (textarea == '') {
                                Swal.showValidationMessage("Required failed");
                                // $(".swal2-validation-message").hide();
                            } else {
                                $.ajax({
// async: false,
                                    type: 'POST',
                                    url: '{{ route('tenant.lead.remove-follow-up-date', ['tenant' => $segment]) }}',
                                    data: {
                                        id: id, textarea: textarea
                                    },
                                    dataType: "json",
                                    beforeSend: function () {
                                        $(".removeFollowUpDate").prop('disabled', true);
                                        $(".removeFollowUpDate").html(
                                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                                    },
                                    success: function (data) {
                                        toastrSuccess('Successfully saved...', 'Success');
                                        location.reload();
                                    },
                                    error: function (xhr, status, error) {
                                        var errorMessage = xhr.status + ': ' + xhr.statusText
                                        switch (xhr.status) {
                                            case 401:
                                                toastrError('Error in saving...', 'Error');
                                                break;
                                            case 422:
                                                toastrInfo('The category is invalid.', 'Info');
                                                break;
                                            case 409:
                                                toastrInfo('Phone no already exist.', 'Warning');
                                                break;
                                            default:
                                                toastrError('Error - ' + errorMessage, 'Error');
                                        }
                                        $(".removeFollowUpDate").prop('disabled', false);
                                        $(".removeFollowUpDate").html('Remove follow up');
                                    },
                                    complete: function (data) {
                                        $(".removeFollowUpDate").html('Remove follow up');
                                        $(".removeFollowUpDate").prop('disabled', false);
                                    }
                                });
                            }
                            /*  return new Promise(function (resolve, reject) {

                                      if (textarea == '') {
                                          Swal.showValidationMessage("")
                                      } else {
                                          // resolve()
                                      }

                              })*/
                        },
                        allowOutsideClick: () => !Swal.isLoading(),
                        allowOutsideClick: false
                    }).then(function (textarea) {
                        swal({
                            type: 'success',
                            title: 'Ajax request finished!',
                            html: 'Submitted email: ' + textarea
                        })
                    })
                    /*preConfirm: (login) => {
                        return fetch(`//api.github.com/users/${login}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(response.statusText)
                                }
                                return response.json()
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                   `Request failed: ${error}`
                                )
                            })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: `${result.value.login}'s avatar`,
                            imageUrl: result.value.avatar_url
                        })
                    }
                })*/

                    return false;

                }
            }).then(function (t) {
                t.value && Swal.fire({
                    title: "Success",
                    text: "Your record has been updated.",
                    type: "success",
                    showConfirmButton: !1,
                    timer: 1500,
                    confirmButtonClass: "btn btn-success",
                    showConfirmButton: false
                })
            });
        }

        function set_someday_follow_up(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonColor: "#3085D6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, set to someday!",
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-danger ml-1",
                buttonsStyling: !1,
                preConfirm: function () {
                    Swal.fire({
                        // title: 'Remove Follow Up',
                        input: 'textarea',
                        inputPlaceholder: 'Type your message here...',
                        inputAttributes: {
                            autocapitalize: 'off',
                            'aria-label': 'Type your message here'
                        },
                        confirmButtonColor: "#3085D6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Someday Follow Up",
                        cancelButtonText: "Cancel",
                        confirmButtonClass: "btn btn-primary",
                        cancelButtonClass: "btn btn-danger ml-1",
                        buttonsStyling: !1,
                        showCancelButton: true,
                        /* showCancelButton: true,
                         confirmButtonText: 'Look up',*/
                        showLoaderOnConfirm: true,
                        preConfirm: function (textarea) {
                            if (textarea == '') {
                                Swal.showValidationMessage("Required failed");
                                // $(".swal2-validation-message").hide();
                            } else {
                                $.ajax({
// async: false,
                                    type: 'POST',
                                    url: '{{ route('tenant.lead.set-someday-follow-up', ['tenant' => $segment]) }}',
                                    data: {
                                        id: id, textarea: textarea
                                    },
                                    dataType: "json",
                                    beforeSend: function () {
                                        $(".setSomedayFollowUp").prop('disabled', true);
                                        $(".setSomedayFollowUp").html(
                                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                                    },
                                    success: function (data) {
                                        toastrSuccess('Successfully saved...', 'Success');
                                        location.reload();
                                    },
                                    error: function (xhr, status, error) {
                                        var errorMessage = xhr.status + ': ' + xhr.statusText
                                        switch (xhr.status) {
                                            case 401:
                                                toastrError('Error in saving...', 'Error');
                                                break;
                                            case 422:
                                                toastrInfo('The category is invalid.', 'Info');
                                                break;
                                            case 409:
                                                toastrInfo('Phone no already exist.', 'Warning');
                                                break;
                                            default:
                                                toastrError('Error - ' + errorMessage, 'Error');
                                        }
                                        $(".setSomedayFollowUp").prop('disabled', false);
                                        $(".setSomedayFollowUp").html('Set to someday');
                                    },
                                    complete: function (data) {
                                        $(".setSomedayFollowUp").html('Set to someday');
                                        $(".setSomedayFollowUp").prop('disabled', false);
                                    }
                                });
                            }
                            /*  return new Promise(function (resolve, reject) {

                                      if (textarea == '') {
                                          Swal.showValidationMessage("")
                                      } else {
                                          // resolve()
                                      }

                              })*/
                        },
                        allowOutsideClick: () => !Swal.isLoading(),
                        allowOutsideClick: false
                    }).then(function (textarea) {
                        swal({
                            type: 'success',
                            title: 'Ajax request finished!',
                            html: 'Submitted email: ' + textarea
                        })
                    })
                    return false;
                }
            }).then(function (t) {
                t.value && Swal.fire({
                    title: "Success",
                    text: "Your record has been updated.",
                    type: "success",
                    showConfirmButton: !1,
                    timer: 1500,
                    confirmButtonClass: "btn btn-success",
                    showConfirmButton: false
                })
            });
        }

        function openModalActivity(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            $(modalTitleName).text(modalTitle);
            $(".activity-type-div").show();
            $("#activity-form #id").val(0);
            $("#activity-form #activity_notes").val('');
            $(".activity-form-div").addClass("d-none").removeClass('d-block');
            $(modalForm).parsley().reset();
// $("#follow_up_datetime").val(moment(new Date($.now())).format('DD-MM-YYYY hh:mm A'));
            $("#follow_up_datetime").val(
                "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y h:i A') : '' }}"
            );
            $("#follow_up_datetime").flatpickr({
                enableTime: true,
                dateFormat: "d-m-Y",
                disable: [
                    {
                        from: "01-01-1970",
                        to: "{{ (!empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' && Carbon::now()->gt($customers->last_follow_up_datetime)) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->subDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}"
                    },
                    {
                        from: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->addDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}",
                        to: "{{Carbon::now()->subDays(1)->format('d-m-Y')}}"
                    }
                ],
// inline:true, moment().format("DD-MM-YYYY hh:mm:ss a")
                defaultDate: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y h:i A') : '' }}",
                dateFormat: 'd-m-Y h:i K',
// dateFormat: "m-d-Y H:i",
// time_24hr: true,

// minuteIncrement: 1
// dateFormat:d-m-Y
            });
            $(".lost_reason_div_followup").hide();
            $(".lost_reason_others_div_followup").hide();
            $("#lost_reason_others_followup").val('');
            $('#lost_reason_id_followup').prop('required', false);
            $('#lost_reason_others_followup').prop('required', false);
           /* var $option = $('#leads_stages_id_followup option[data-id="1"]');
            if ($option.length) {
                $option.prop('selected', true);
            }*/
            // $("#leads_stages_id_followup").val('');
            $(modalName).modal('show');
        }

        function OpenModalAssignLead(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            $(modalTitleName).text(modalTitle);
            if (modalForm == '#activity-form' || modalForm == '#follow-up-form')
                $(modalForm).parsley().reset();
            @if($main_company->follow_up_note_req_flg==1)
            $(".activity_notes_flp").prop('required', true);
            @endif
            $(modalName).modal('show');
        }

        function addActivityForm(id, name) {
            if(id==19){
                $('.visit_address_div').show();
            }
            if(id!=19){
                $('.visit_address_div').hide();
            }
            $("#activity_type").val(id);
            $("#activity_name").val(name);
            $(".activity-type-div").hide().fadeOut("3000");
            $(".activity-form-div").removeClass("d-none").addClass('d-block').fadeIn("3000");
        }

        function getActivityName(sel) {

            if(sel.value == 19){
                $('.visit_address_div').show();
            }
            if(sel.value != 19){
                $('.visit_address_div').hide()
            }
            $("#activity_name").val($("#activity_type option:selected").text());
        }

        function view_id(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.lead.show-customer-timeline', ['tenant' => $segment]) }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#customer-form");
                    resetForm("#customer-form");
                    let country_code = '';
                    if (res.data.country_code) {
                        country_code = res.data.country_code;
                    }

                    let whatsapp_country_code = '';
                    if (res.data.whatsapp_country_code) {
                        whatsapp_country_code = res.data.whatsapp_country_code;
                    }
                    $('.customer_type_small').html(res.data.customer_type);
                    $('.company_name_small').html((res.data.company_name)?res.data.company_name:'-');
                    $('.lead_category_small').html((res.data.lead_category)?res.data.lead_category:'-');
                    $('.lead_origin_small').html((res.data.lead_origin)?res.data.lead_origin:'-');
                    $('.name_small').html((res.data.name)?res.data.name:'-');
                    $('.phone_no_small').html(country_code + res.data.phone_no);
                    $('.whatsapp_no_small').html(whatsapp_country_code + res.data.whatsapp_no);
                    $('.description_small').html((res.data.description)?res.data.description:'-');

                    $('.lead_stage_small').html((res.data.lead_stage_name)?'<span class="fs-6 badge me-1" style="background-color: '+res.data.lead_stage_color_code+';color: ">'+res.data.lead_stage_name+'</span>':'-');
                    $('#leads_stage_id').val(res.data.lead_stage_id);
                    $('.email_small').html((res.data.email)?res.data.email:'-');
                    $('.email_copy').attr("data-url",res.data.email);
                    $('.address_small').html((res.data.address)?res.data.address:'');
                    $('.pincode_small').html((res.data.pincode)?res.data.pincode:'-');

                    $('.country_name_small').html((res.data.country_name)?res.data.country_name:'-');
                    $('.state_name_small').html((res.data.state_name)?res.data.state_name:'-');
                    $('.city_name_small').html((res.data.city_name)?res.data.city_name:'-');
                    $('.gst_no_small').html((res.data.gst_no)?res.data.gst_no:'-');
                    let label_span ='<span class="fw-bold">Labels</span><br>';
                    let first_label_span ='';
                    if(res.data.lead_label_data.length > 0){
                        $.each(res.data.lead_label_data, function (key, value) {
                            label_span += '<span class="fs-6 badge me-1" style="color:'+value.color_code+';border: 1px solid '+value.color_code+';background-color: transparent">'+value.name+'</span>';
                            first_label_span += '<span class="fs-6 badge me-1" style="color:'+value.color_code+';border: 1px solid '+value.color_code+';background-color: transparent">'+value.name+'</span>';
                        });
                    }
                    else{
                        label_span +='-';
                    }

                    $(".label-span").html(label_span);
                    $(".first-label-span").html(first_label_span);

                    var name = res.data.name || '';  // Ensure 'name' is not undefined or null
                    var matches = name.match(/\b\w/g) || [];

                    var strArr = matches.join('');
                    var str = strArr.length > 1 ? strArr[1] : '';
                    var firstLetters = strArr.length > 0 ? strArr[0] + str : '';

                    $(".customer-sort-name").html(firstLetters);
                    if(res.data.company_name)
                    $('.first_company_name_small').html(res.data.company_name);

                    let str_csc='';
                    if(res.data.city_name){
                        str_csc += res.data.city_name+' | ';
                    }
                    if(res.data.state_name){
                        str_csc += res.data.state_name+' | ';
                    }
                    str_csc += res.data.country_name;

                    $('.csc_small').html(str_csc);

                    let str_addr='<i class="mdi mdi-cellphone"></i> '+res.data.country_code+' '+res.data.phone_no;
                    if(res.data.email){
                        str_addr += ' | <i class="mdi mdi-email-outline"></i> '+res.data.email;
                    }
                    if(res.data.address){
                        str_addr += ' | <i class="mdi mdi-map-marker"></i> '+res.data.address;
                    }
                    $('.merge_address_small').html(str_addr);
                }
            });
        }

        function timelineActivity(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.lead.lead-timeline-activity', ['tenant' => $segment]) }}",
                data: {
                    id: id,start:localStorage.getItem('timeline_per_page')
                },
                dataType: "json",
                success: function (res) {
                    var res = res.data;

                    var activity_add_action = "";

                    @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                    in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                        {{-- || auth()->user()->company_id == null--}}
                        @if (
                        (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                        in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                        in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                        $customers->assigned_to_user == auth()->user()->id)
                        {{--|| auth()->user()->company_id == null--}}
                        activity_add_action =
                        "openModalActivity(0,'#activity-modal','#activity-formModalLabel','Add Activity','#activity-form')";
                    @else
                        activity_add_action =
                        "openModalActivity(0,'#activity-modal','#activity-formModalLabel','Add Activity','#activity-form')";

                    activity_add_action =
                        "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                    @endif
                        @else
                        activity_add_action =
                        "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                    @endif


                    var html_temp = '';
                    html_temp += '<div class="timeline-item pb-4">' +
                        '<a href="javascript:void(0);" class="text-info fw-bold d-block" onclick="' +
                        activity_add_action + '">' +
                        '<i class="mdi mdi-plus bg-info-lighten text-info timeline-icon"></i>' +
                        '</a>' +
                        '<div class="timeline-item-info">' +
                        '<a href="javascript:void(0);" class="text-info fw-bold mb-1 pt-1 d-block" onclick="' +
                        activity_add_action + '">Add Activity</a>' +
                        '</div>' +
                        '</div>';
                    $.each(res, function (key, value) {

                        if (!(value.is_modified == 0 && value.is_follow_up == 0 && value.entry_type == 'followup') && !(value.is_modified == 1 && value.is_follow_up == 1 && value.entry_type == 'followup')) { //activity merge


                            var activity_edit_action = "";
                            var activity_delete_action = "";
                            var copy_est_action = "";

                            @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                            /*||auth()->user()->company_id == null*/
                            @if (
                            (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                            $customers->assigned_to_user == auth()->user()->id)
                            /*||auth()->user()->company_id == null*/
                            activity_edit_action = "activity_edit_id(" + value.id + ")";
                            activity_delete_action = "activity_remove_id(" + value.id + ")";
                            @else
                                activity_edit_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_delete_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            @endif
                                @else

                                activity_edit_action =
                                "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";
                            activity_delete_action =
                                "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";
                            @endif

                            // var activity_edit_action = "activity_edit_id(" + value.id + ")";
                            // var activity_delete_action = "activity_remove_id(" + value.id + ")";
                            var ficon = '';
                            if (value.activity_type == 1)
                                ficon =
                                    '<i class="mdi mdi-phone bg-success-lighten text-success timeline-icon"></i>';
                            if (value.activity_type == 2)
                                ficon =
                                    '<i class="mdi mdi-chat-outline bg-warning-lighten text-warning timeline-icon"></i>';
                            if (value.activity_type == 3)
                                ficon =
                                    '<i class="mdi mdi-calendar bg-dark-lighten text-dark timeline-icon"></i>';
                            if (value.activity_type == 19)
                                ficon =
                                    '<i class="mdi mdi-map-marker-outline bg-dark-lighten text-dark timeline-icon"></i>';
                            if (value.activity_type == 4)
                                ficon =
                                    '<i class="mdi mdi-file-document-outline bg-info-lighten text-info timeline-icon"></i>';
                            if (value.activity_type == 5)
                                ficon =
                                    '<i class="mdi mdi-file-pdf-box bg-danger-lighten text-danger timeline-icon"></i>';
                            if (value.activity_type == 6)
                                ficon =
                                    '<i class="mdi mdi-account-plus-outline bg-info-lighten text-info timeline-icon"></i>';
                            if (value.activity_type == 7)
                                ficon =
                                    '<i class="mdi mdi-pencil bg-info-lighten text-info timeline-icon"></i>';
                            if (value.activity_type == 8)
                                ficon =
                                    '<i class="mdi mdi-arrow-top-right bg-primary-lighten text-primary timeline-icon"></i>';
                            if (value.activity_type == 20)
                                ficon =
                                    '<i class="mdi mdi-call-merge bg-primary-lighten text-primary timeline-icon"></i>';
                            if (value.activity_type == 9)
                                ficon =
                                    '<i class="mdi mdi-calendar bg-primary-lighten text-primary timeline-icon"></i>';

                            if (value.activity_type == 10)
                                ficon =
                                    '<i class="mdi mdi-book-edit-outline bg-primary-lighten text-primary timeline-icon"></i>';
                            if (value.activity_type == 11)
                                ficon =
                                    '<i class="mdi mdi-file-remove-outline bg-danger-lighten text-danger timeline-icon"></i>';

                            if (value.activity_type == 12)
                                ficon =
                                    '<i class="mdi mdi-send bg-info-lighten text-info timeline-icon mdi-rotate-315"></i>';

                            if (value.activity_type == 13)
                                ficon =
                                    '<i class="mdi mdi-send bg-info-lighten text-info timeline-icon mdi-rotate-315"></i>';

                            if (value.activity_type == 14)
                                ficon =
                                    '<i class="mdi mdi-calendar-blank-multiple bg-dark-lighten text-dark timeline-icon"></i>';

                            if (value.activity_type == 15)
                                ficon =
                                    '<i class="mdi mdi-calendar-blank bg-secondary-lighten text-secondary timeline-icon"></i>';
                            if (value.activity_type == 16)
                                ficon =
                                    '<i class="mdi mdi-checkbox-marked-circle-outline bg-dark-lighten text-dark timeline-icon"></i>';

                            if (value.activity_type == 17)
                                ficon =
                                    '<i class="mdi mdi-close bg-danger-lighten text-danger timeline-icon"></i>';

                            if (value.activity_type == 18)
                                ficon =
                                    '<i class="mdi mdi-trophy-outline bg-success-lighten text-success timeline-icon"></i>';
                            var activity_name = '';
                            let dblock = "d-block";
                            let editaction_name = 'Edit';

                            if (value.activity_type == 5) {
                                editaction_name = 'Revise Estimate';
                                dblock = "";
                            }

                            let activity_name_icon = '';
                            if (value.activity_type == 12) {
                                activity_name_icon = '<i class="mdi mdi-message-text-outline"></i> ';

                            }

                            if (value.activity_type == 13) {
                                activity_name_icon = '<i class="mdi mdi-paperclip mdi-rotate-45"></i> ';
                            }

                            if (value.activity_name)
                                activity_name = '<span class="text-black fw-bold ' + dblock + '">' + activity_name_icon + value
                                    .activity_name + '</span>';

                            var activities_notes = value.activity_notes;

                            var activity_view_edit_action = '';
                            var activity_view_edit_action_tmp = activity_edit_action;
                            var activity_view_edit_action_href = 'href="javascript:void(0);"';
// if (value.is_modified == 0 || value.activity_type == 9) {
                            if (value.is_modified == 0 || value.activity_type == 9 || value.activity_type ==
                                10) {
                                activities_notes = value.internal_remarks;

                                if (value.is_follow_up == 1) {
                                    activities_notes = value.activity_notes;
                                }
                                activity_view_edit_action = 'd-none';
                                activity_view_edit_action_tmp = "";
                                activity_view_edit_action_href = "";
                            }

                            if (value.is_modified == 1 || value.activity_type == 2 || value.activity_type == 3 || value.activity_type == 19) { //activity merge
                                activities_notes = value.internal_remarks;
                            }
                            let pdf_download = '';
                            let activity_estimate_status = '';
                            change_status_action = '';
                            if (value.activity_type == 5) {
                                activity_view_edit_action = '';
                                activity_view_edit_action_href = 'href="' + SITEURL + '/quotes/edit/' +
                                    value.estimate_id + '/1?cid=' + value.customer_id + '"';
                                activity_view_edit_action_tmp = 'javascript:void(0);';
                                activity_edit_action = 'javascript:void(0);';
                                var tmp_amt = '';
                                if (value.net_amount > 0) {
                                    tmp_amt =
                                        ' <small class="text-dark">' + value.currency_symbol +
                                        value.net_amount + '</small>';
                                }
                                /*pdf_download = '<a href="' + value.aws_path +
                                    '.pdf" class="d-block text-primary" download><i class="mdi mdi-cloud-download-outline"></i> <small>' +
                                    value.estimate_version_no + tmp_amt + '</small></a>'*/
                                pdf_download = '<a href="' + value.aws_path + '" class="d-block text-primary pdf-download" target="_blank" download><i class="mdi mdi-cloud-download-outline"></i> <small>' +
                                    value.estimate_version_no + tmp_amt + '</small></a>'


                                var tmp_activity_estimate_status = '';
                                if (value.activity_estimate_status == 'Draft') {
                                    tmp_activity_estimate_status = 'bg-secondary';
                                }

                                if (value.activity_estimate_status == 'Sent') {
                                    tmp_activity_estimate_status = 'bg-primary';
                                }

                                if (value.activity_estimate_status == 'Inprogress') {
                                    tmp_activity_estimate_status = 'bg-warning';
                                }

                                if (value.activity_estimate_status == 'Accept') {
                                    tmp_activity_estimate_status = 'bg-success';
                                }

                                if (value.activity_estimate_status == 'Decline') {
                                    tmp_activity_estimate_status = 'bg-danger';
                                }

                                activity_estimate_status = ' <span class="badge ' +
                                    tmp_activity_estimate_status +
                                    ' text-white text-left fw-bold mb-1 ms-2">' + value
                                        .activity_estimate_status + '</span>';

                                activity_change_status_action = "activity_change_status_fun(" + value
                                        .activity_timeline_id + ",'" + value.activity_estimate_status + "','" +
                                    value.estimate_version_no + "','" + value.estimate_id + "')";
                                change_status_action = '<a href="javascript:void(0);" onclick="' +
                                    activity_change_status_action +
                                    '" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-1"></i>Change Status</a>';

                                activity_copy_est_action = "copy_est_action_fun('{!! url('/quotes/generate-link') !!}/" +
                                    value.estimate_id + "')";
                                copy_est_action = '<a href="javascript:void(0)" title="Share" onclick="' +
                                    activity_copy_est_action +
                                    '" class="copy_text dropdown-item"><i class="mdi mdi-share-variant me-1"></i>Share Estimate</a>';
                                if (!activity_estimate_status) {
                                    activity_estimate_status = '';
                                }
                            }

                            /*var dis_activity_date = value.display_created_at;
                            if(value.activity_type == 9){
                            dis_activity_date = value.display_follow_up_datetime;
                            }*/
                            if (!activities_notes) {
                                activities_notes = '';
                            }

// let content_message_url='';
                            if (value.activity_type == 12) {
                                activity_view_edit_action_href = 'href="' + SITEURL + '/content/messages/timeline/' + value.content_id + '"';
                            }

                            if (value.activity_type == 13) {
                                activity_view_edit_action_href = 'href="' + SITEURL + '/content/files/timeline/' + value.content_id + '"';
                            }

                            @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                            /*||auth()->user()->company_id == null*/
                            @if (
                            (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                            $customers->assigned_to_user == auth()->user()->id)
                            /*||auth()->user()->company_id == null*/
                            @else
                                activity_view_edit_action_href = "href='javascript:void(0);'";
                            activity_view_edit_action_tmp =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_edit_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_change_status_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            change_status_action = '<a href="javascript:void(0);" onclick="' +
                                activity_change_status_action +
                                '" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-1"></i>Change Status</a>';

                            activity_copy_est_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            copy_est_action =
                                '<a href="javascript:void(0)" title="Share" onclick="' +
                                activity_copy_est_action +
                                '" class="copy_text dropdown-item" data-url="{!! url('/quotes/generate-link') !!}/' +
                                value.estimate_id +
                                '"><i class="mdi mdi-share-variant me-1"></i>Share Estimate</a>';
                            @endif
                            {{--@else
                            activity_view_edit_action_href = "href='javascript:void(0);'";
                            activity_view_edit_action_tmp =
                            "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_edit_action =
                            "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";
                            activity_change_status_action =
                            "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            /*=
                            "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";*/

                            change_status_action = '<a href="javascript:void(0);" onclick="' +
                            activity_change_status_action +
                            '" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-1"></i>Change Status</a>';

                            activity_copy_est_action =
                            "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            copy_est_action = '<a href="javascript:void(0)" title="Share" onclick="' +
                            activity_copy_est_action +
                            '" class="copy_text dropdown-item" data-url="{!! url('/quotes/generate-link') !!}/' +
                            value.estimate_id +
                            '"><i class="mdi mdi-share-variant me-1"></i>Share Estimate</a>';--}}
                            @endif

                            let delete_action = '';
                            if (value.activity_type != 5) {
                                delete_action = '<a href="javascript:void(0);" onclick="' + activity_delete_action + '" class="dropdown-item"><i class="mdi mdi-delete me-1"></i>Delete</a>';
                                change_status_action = '';
                                copy_est_action = '';

                                if (value.activity_type != 12 && value.activity_type != 13) {
                                    activity_view_edit_action_tmp = '';
                                    activity_view_edit_action_href = '';
                                }
                            }

                            if (value.activity_type == 11) {
                                activities_notes = value.estimate_version_no;
                                activity_name = '<span class="text-black fw-bold ' + dblock + '">' + value
                                    .internal_remarks + '</span>';
// activity_name = value.internal_remarks+" : "+value.estimate_version_no;
                            }

                            if (value.activity_type == 12) {
                                activities_notes = value.activity_notes;
                            }
                            let cr_by = '<small class="text-muted"><i class="mdi mdi-account-circle-outline"></i> by ' + value.created_by_name + '</small>';
                            if (value.activity_type == 16) {
                                cr_by = '';
                            }

                            if (value.activity_type == 5 && value.activity_estimate_status == '') {
                                activity_view_edit_action = 'd-none';
                            }

                            if (value.activity_type == 1 || value.activity_type == 2 || value.activity_type == 3 || value.activity_type == 19) { //activity merge
                                activities_notes = value.activity_notes;
                            }
                            let xc =' <a href="#" class="dropdown-toggle arrow-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">' +
                                '<i class="mdi mdi-dots-vertical"></i>' +
                                '</a>';
                            if(value.activity_type == 19){
                                xc =' <a target="_blank" title="'+value.visit_address+'" href="https://maps.google.com/maps?q='+value.visit_latitude+','+value.visit_longitude+'&hl=es;z=14&address='+ encodeURIComponent(value.visit_address)+'" class="text-dark" aria-expanded="false">' +
                                    '<i class="mdi mdi-google-maps text-danger" style="font-size: 25px !important;"></i>' +
                                    '</a>';
                            }

                            html_temp += '<div class="timeline-item">' +
                                ficon +
                                '<div class="timeline-item-info">' +
                                '<div class="dropdown card-widgets ' + activity_view_edit_action + '">' +
                                xc+
                                /*' <a href="#" class="dropdown-toggle arrow-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">' +
                                '<i class="mdi mdi-dots-vertical"></i>' +
                                '</a>' +*/
                                '<div class="dropdown-menu dropdown-menu-end" style="">' +
                                '<a ' + activity_view_edit_action_href + ' onclick="' +
                                activity_edit_action +
                                '" class="dropdown-item"><i class="mdi mdi-pencil me-1"></i>' +
                                editaction_name + '</a>' +
                                change_status_action +

                                copy_est_action +

                                delete_action +
                                '</div>' +
                                '</div>' +
                                '<a ' + activity_view_edit_action_href + ' onclick="' +
                                activity_view_edit_action_tmp +
                                '" class="text-black fw-bold mb-1 d-block">' + value.display_created_at +
                                '</a>' +
                                activity_name + activity_estimate_status + pdf_download +

                                '<small>' + activities_notes + '</small>' +

                                '<p class="mb-0 pb-2">' +
                                cr_by +
                                '</p>' +
                                '</div>' +
                                '</div>';
                        } //activity merge
                    });
                    $("#timeline-info").html(html_temp);
                }
            });
        }

        function getActivitycounts(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.lead.get-activity-colunts', ['tenant' => $segment]) }}",
                data: {
                    id: id
                },
                dataType: "json",
                beforeSend: function () {
                    $(".activity-count").html('<i class="mdi mdi-spin mdi-loading text-primary"></i>');
                },
                success: function (res) {
                    var html_temp = '';
                    $.each(res.data, function (key, value) {

                        if(value.activity_name=="Call"){
                            html_temp += '<div class="flex-shrink-0 me-2 w-25" title="Call" style="margin-bottom: 0.175rem !important;">' +
                                '<i class="mdi mdi-phone widget-icon rounded-circle bg-success-lighten text-success border border-success"></i>' +
                                '<span class="font-15 text-dark">  '+value.activity_type_count+'</span>' +
                                '</div>';
                        }

                        if(value.activity_name=="Message") {
                            html_temp += '<div class="flex-shrink-0 me-2 w-25" title="Message" style="margin-bottom: 0.175rem !important;">' +
                                '<i class="mdi mdi-chat-outline widget-icon rounded-circle bg-warning-lighten text-warning border border-warning"></i>' +
                                '<span class="font-15 text-dark">  '+value.activity_type_count+'</span>' +
                            '</div>';
                        }

                        @if($main_company->company_category > 1)
                        if(value.activity_name=="Meeting") {
                            html_temp += '<div class="flex-shrink-0 me-2 w-25" title="Meeting" style="margin-bottom: 0.175rem !important;">' +
                                '<i class="mdi mdi-calendar widget-icon rounded-circle bg-secondary-lighten text-secondary border border-secondary"></i>' +
                                '<span class="font-15 text-dark">  '+value.activity_type_count+'</span>' +
                            '</div>';
                        }
                        @endif
                        @if($main_company->company_category == 1)
                        if(value.activity_name=="Site Visit") {
                            html_temp += '<div class="flex-shrink-0 me-2 w-25" title="Meeting" style="margin-bottom: 0.175rem !important;">' +
                                '<i class="mdi mdi-map-marker-outline widget-icon rounded-circle bg-secondary-lighten text-secondary border border-secondary"></i>' +
                                '<span class="font-15 text-dark">  '+value.activity_type_count+'</span>' +
                                '</div>';
                        }
                        @endif
                    });
                    $(".activity-count").html(html_temp);
                }
            });
        }

        function activity_change_status_fun(id, status, estimate_no, estimate_id) {

            $('input[name="activity_estimate_status"][value="' + status + '"]').prop("checked", true);
            $("#activity-change-estimate-status-modal #activity_estimate_notes").focus();
            $("#activity-change-estimate-status-modal #activity_id").val(id);
            $("#activity-change-estimate-status-modal #old_activity_status").val(status);
            $("#activity-change-estimate-status-modal #activity_estimate_id").val(estimate_id);
            $("#activity-change-estimate-status-modal #activity_estimate_no").val(estimate_no);
            $('#activity-change-estimate-status-modal').modal('toggle');
        }

        function edit_id(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.customer.show', ['tenant' => $segment]) }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#customer-form");
                    resetForm("#customer-form");
                    $('#customer-form #id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $(".cust_company_name_div").hide();
                    if (res.data.customer_type == "Business") {
                        $("#company_name").prop("required", true);
                        $(".cust_company_name_div").show();
                    }
                    $('#company_name').val(res.data.company_name);
                    $('#email').val(res.data.email);
                    $('#phone_no').val(res.data.phone_no);
                    $('#whatsapp_no').val(res.data.whatsapp_no);
                    $("#country_code option[data-id='" + res.data.phone_no_country_id + "']").prop("selected", true);
                    $("#whatsapp_country_code option[data-id='" + res.data.whatsapp_no_country_id + "']").prop("selected", true);
                    $("#currency_name option[data-id='" + res.data.currency_name_country_id + "']").prop("selected", true);
                    $('#country_code').select2({dropdownParent: $('#sel_cc')}).trigger('change');
                    $('#whatsapp_country_code').select2({dropdownParent: $('#sel_wcc')}).trigger('change');
                    $('#currency_name').select2({dropdownParent: $('#sel_cn')}).trigger('change');

                    /*$('#country_code').val(res.data.country_code.replace('+', '')).change();
                    $('#country_code').select2({dropdownParent: $('body')}).trigger('change');
                    $('#whatsapp_country_code').val(res.data.whatsapp_country_code.replace('+', '')).change();
                    $('#whatsapp_country_code').select2({dropdownParent: $('body')}).trigger('change');*/
                    $('#address').val(res.data.address);
                    $('#pincode').val(res.data.pincode);
                    $('#lead_stage_id').val(res.data.lead_stage_id);
                    $('#country_id').val(res.data.country_id);

                    let dataId = $('#country_id option[value=' + res.data.country_id + ']').attr('data-id');
                    // $("#country_code_div").text('+' + dataId);
                    // $("#country_code").val('+' + dataId);
                    $('#customer_category_id').val(res.data.customer_category_id);
                    $('#customer_lead_id').val(res.data.customer_lead_id);
                    $('#gst_no').val(res.data.gst_no);
                    $('#city_name').val(res.data.city_name);
                    getStatesList(res.data.country_id, res.data.state_id);
// getCityList(res.data.state_id, res.data.city_id);
                    $("input[name=customer_type][value=" + res.data.customer_type + "]").prop('checked', true);

                    $('#description').val(res.data.description);

                    $('.modal-title').text('Edit Lead');
                    $("#customer-modal .advance-option").removeClass('d-none');
                    $("#customer-modal .advance-option-div").addClass('d-none');
                    $("#customer-modal #email").focus();
                    $('#customer-modal').modal('toggle');
                }
            });
        }

        function activity_remove_id(id) {
            if (id.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
            } else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonColor: "#3085D6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!",
                    confirmButtonClass: "btn btn-primary",
                    cancelButtonClass: "btn btn-danger ml-1",
                    buttonsStyling: !1,
                    preConfirm: function () {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('tenant.lead.lead-activity-delete', ['tenant' => $segment]) }}",
                            data: {
                                id: id
                            },
                            dataType: "json",
                            success: function (data, textStatus, jqXHR) {
                                timelineActivity('{{ Request::segment(4) }}');

// toastrSuccess('Successfully removed');
// location.href = SITEURL+'/lead'


                            },
                            error: function (xhr, status, error) {
                                var errorMessage = xhr.status + ': ' + xhr.statusText
                                switch (xhr.status) {
                                    case 401:
                                        toastrError('Error in saving...', 'Error');
                                        break;
                                    case 422:
                                        toastrInfo('Please contact developer.', 'Info');
                                        break;
                                    case 409:
                                        toastrInfo('Name already exist.', 'Warning');
                                        break;
                                    default:
                                        toastrError('Error - ' + errorMessage, 'Error');
                                }
                            },
                            complete: function (data) {
                            }
                        });
                    }
                }).then(function (t) {
                    t.value && Swal.fire({
                        title: "Success",
                        text: "Your record has been updated.",
                        type: "success",
                        showConfirmButton: !1,
                        timer: 1500,
                        confirmButtonClass: "btn btn-success",
                        showConfirmButton: false
                    })
                });
            }
        }

        function activity_edit_id(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.lead.activity-show', ['tenant' => $segment]) }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#activity-form");
                    resetForm("#activity-form");


                    $(".activity-type-div").hide();
                    $(".activity-form-div").removeClass("d-none").addClass("d-block");

                    $('#activity-form #id').val(res.data.id);
                    $('#activity_type').val(res.data.activity_type);
                    if(res.data.activity_type==19){
                        $('.visit_address_div').show();
                    }
                    if(res.data.activity_type!=19){
                        $('.visit_address_div').hide();
                    }
                    $('#visit_address').val(res.data.visit_address);
                    $('#activity_name').val(res.data.activity_name);
                    $('#activity_notes').val(res.data.activity_notes);
// $('#follow_up_datetime').val(res.data.follow_up_datetime);
                    $('#follow_up_datetime').val(
                        '{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y H:i A') : '' }}'
                    );

                    $("#follow_up_datetime").flatpickr({
                        enableTime: false,
                        dateFormat: "d-m-Y",
                        disable: [
                            {
                                from: "01-01-1970",
                                to: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->subDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}"
                            },
                            {
                                from: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->addDays(1)->format('d-m-Y') : Carbon::now()->subDays(1)->format('d-m-Y') }}",
                                to: "{{Carbon::now()->subDays(1)->format('d-m-Y')}}"
                            }
                        ],
                        enable: [
                            "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y') : '' }}"
                        ],
// inline:true, moment().format("DD-MM-YYYY hh:mm:ss a")
                        defaultDate: "{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customers->last_follow_up_datetime)->format('d-m-Y h:i A') : '' }}",
                        dateFormat: 'd-m-Y h:i K',
// dateFormat: "m-d-Y H:i",
// time_24hr: true,

// minuteIncrement: 1
// dateFormat:d-m-Y
                    });
                    $('.modal-title').text('Edit Activity');

                    $("#activity-modal #activity_name").focus();
                    $('#activity-modal').modal('toggle');

                }
            });
        }

        $('#country_id').on('change', function (e) {
            e.preventDefault();
            let country_code = jQuery(this).find(':selected').attr('data-id')
            $("#country_code_div").text('+' + country_code);
            $("#country_code").val('+' + country_code);
            var country_id = jQuery(this).val();
            getStatesList(country_id);

        });

        /* $('#state_id').on('change', function (e) {
        e.preventDefault();
        var state_id = jQuery(this).val();
        getCityList(state_id);

        });*/

        // function get All States
        function getStatesList(country_id, seleted_id = 0) {
            if (seleted_id == 0) {
                seleted_id = {{ $expData->state_id }};
            }
            $.ajax({
                async: false,
                url: "{{ url('get-states-by-country') }}",
                type: "POST",
                data: {
                    country_id: country_id
                },
                dataType: 'json',
                beforeSend: function () {
                    jQuery('select#state_id').find("option:eq(0)").html("Please wait..");
                },
                success: function (result) {
                    var options = '';
                    options += '<option value="0">Choose</option>';
                    $.each(result.states, function (key, value) {
                        var selected = "";
                        if (value.id == seleted_id)
                            selected = 'selected';
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name +
                            '</option>';
                    });
                    $("#state_id").html(options);
                    $('#city_id').html('<option value="0">Choose</option>');
                },
                complete: function () {
// code
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }

        function copy_est_action_fun(url) {
// document.preventDefault();
            var copyText = $(this).attr('data-url');
            var copyText = url;

            document.addEventListener('copy', function (document) {
                document.clipboardData.setData('text/plain', copyText);
                document.preventDefault();
            }, true);
            document.execCommand('copy');
            toastrSuccess('Successfully copied url');
        }

        function viewOnMobile(lead_assign_id, lead_name, lead_id) {
            $.ajax({
                type: "POST",
                // async: false,
                url: SITEURL + "/view-on-mobile",
                data: {lead_id: lead_id, lead_name: lead_name, lead_assign_id: lead_assign_id},
                dataType: "json",
                beforeSend: function () {
                    $(".view_on_mobile").prop('disabled', true);
                    $(".view_on_mobile").html('<i class="mdi mdi-dots-circle mdi-spin font-14 text-muted"></i>');

                },
                success: function (res) {
                    toastrSuccess('Sent to Phone!');
                    $(".view_on_mobile").html('View on App <i class="mdi mdi-arrow-right-bold-box-outline" style="vertical-align: inherit;"></i> <i class="mdi mdi-cellphone" style="vertical-align: inherit;"></i>');
                    $(".view_on_mobile").prop('disabled', false);
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    switch (xhr.status) {
                        case 401:
                            toastrError('Error in estimate coping...', 'Error');
                            break;
                        case 422:
                            toastrInfo('Please contact developer.', 'Info');
                            break;
                        case 409:
                            toastrInfo('Name already exist.', 'Warning');
                            break;
                        default:
                            toastrError('Error - ' + errorMessage, 'Error');
                    }
                    $(".view_on_mobile").html('View on App <i class="mdi mdi-arrow-right-bold-box-outline" style="vertical-align: inherit;"></i> <i class="mdi mdi-cellphone" style="vertical-align: inherit;"></i>');
                    $(".view_on_mobile").prop('disabled', false);
                },
            });
        }

        function formatBytes(bytes, precision = 2) {
            var units = ['B', 'KB', 'MB', 'GB', 'TB'];

            bytes = Math.max(bytes, 0);
            var pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
            pow = Math.min(pow, units.length - 1);

            bytes /= Math.pow(1024, pow);

            return (Math.round(bytes * Math.pow(10, precision)) / Math.pow(10, precision)) + ' ' + units[pow];
        }

        function getAttachment(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.folder.get-nested-directories-with-files', ['tenant' => $segment]) }}",
                data: {
                    id: id,
                    attachment_id: 0
                },
                dataType: "json",
                success: function (res) {
                    $("#attachment_count").html(res.length);
                    var html_temp = '';
                    $.each(res, function (key, value) {
                        let file_action = "get_attachment_file(" + value.id + ")";
                        let delete_folder_action = "attachment_remove_id('" + value.id + "')";
                        html_temp += '<div class="col-xl-6">' +
                            '<div class="card mb-1 shadow-none border primary">' +
                            '<div class="p-2">' +
                            '<div class="row align-items-center attach-div" onclick="' + file_action + '">' +
                            '<div class="col-auto">' +
                            '<div class="avatar-sm">' +
                            '<span class="avatar-title bg-light bg-primary-lighten text-primary text-reset rounded">' +
                            '<i class="mdi mdi-folder text-primary font-16"></i>' +
                            ' </span>' +
                            '</div>' +
                            '</div>' +
                            '<div class="col ps-0">' +
                            '<a href="javascript:void(0);" class="text-dark fw-bold">' + value.name + '</a>' +
                            '<p class="mb-0">' + value.files.length + ' Files</p>' +
                            '</div>' +
                            '<div class="col-auto">' +
                            '<a href="javascript:void(0);" class="text-danger" title="Delete" id="delete_folder_' + value.id + '" onclick="' + delete_folder_action + '"><i class="mdi mdi-delete-outline mdi-18px me-1"></i></a>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    $("#attachment-info").html(html_temp);
                    console.log(res);
                    return false;
                    var html_temp = '';
                    html_temp += '<div class="timeline-item pb-4">' +
                        '<a href="javascript:void(0);" class="text-info fw-bold d-block" onclick="' +
                        activity_add_action + '">' +
                        '<i class="mdi mdi-plus bg-info-lighten text-info timeline-icon"></i>' +
                        '</a>' +
                        '<div class="timeline-item-info">' +
                        '<a href="javascript:void(0);" class="text-info fw-bold mb-1 pt-1 d-block" onclick="' +
                        activity_add_action + '">Add Activity</a>' +
                        '</div>' +
                        '</div>';
                    $.each(res, function (key, value) {

                        if (!(value.is_modified == 0 && value.is_follow_up == 0 && value.entry_type == 'followup') && !(value.is_modified == 1 && value.is_follow_up == 1 && value.entry_type == 'followup')) { //activity merge


                            var activity_edit_action = "";
                            var activity_delete_action = "";
                            var copy_est_action = "";

                            @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                            /*||auth()->user()->company_id == null*/
                            @if (
                            (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                            $customers->assigned_to_user == auth()->user()->id)
                            /*||auth()->user()->company_id == null*/
                            activity_edit_action = "activity_edit_id(" + value.id + ")";
                            activity_delete_action = "activity_remove_id(" + value.id + ")";
                            @else
                                activity_edit_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_delete_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            @endif
                                @else

                                activity_edit_action =
                                "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";
                            activity_delete_action =
                                "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";
                            @endif

                            // var activity_edit_action = "activity_edit_id(" + value.id + ")";
                            // var activity_delete_action = "activity_remove_id(" + value.id + ")";
                            var ficon = '';
                            if (value.activity_type == 1)
                                ficon =
                                    '<i class="mdi mdi-phone bg-success-lighten text-success timeline-icon"></i>';
                            if (value.activity_type == 2)
                                ficon =
                                    '<i class="mdi mdi-chat-outline bg-warning-lighten text-warning timeline-icon"></i>';
                            if (value.activity_type == 3)
                                ficon =
                                    '<i class="mdi mdi-calendar bg-dark-lighten text-dark timeline-icon"></i>';
                            if (value.activity_type == 19)
                                ficon =
                                    '<i class="mdi mdi-map-marker-outline bg-dark-lighten text-dark timeline-icon"></i>';
                            if (value.activity_type == 4)
                                ficon =
                                    '<i class="mdi mdi-file-document-outline bg-info-lighten text-info timeline-icon"></i>';
                            if (value.activity_type == 5)
                                ficon =
                                    '<i class="mdi mdi-file-pdf-box bg-danger-lighten text-danger timeline-icon"></i>';
                            if (value.activity_type == 6)
                                ficon =
                                    '<i class="mdi mdi-account-plus-outline bg-info-lighten text-info timeline-icon"></i>';
                            if (value.activity_type == 7)
                                ficon =
                                    '<i class="mdi mdi-pencil bg-info-lighten text-info timeline-icon"></i>';
                            if (value.activity_type == 8)
                                ficon =
                                    '<i class="mdi mdi-arrow-top-right bg-primary-lighten text-primary timeline-icon"></i>';
                            if (value.activity_type == 9)
                                ficon =
                                    '<i class="mdi mdi-calendar bg-primary-lighten text-primary timeline-icon"></i>';

                            if (value.activity_type == 10)
                                ficon =
                                    '<i class="mdi mdi-book-edit-outline bg-primary-lighten text-primary timeline-icon"></i>';
                            if (value.activity_type == 11)
                                ficon =
                                    '<i class="mdi mdi-file-remove-outline bg-danger-lighten text-danger timeline-icon"></i>';

                            if (value.activity_type == 12)
                                ficon =
                                    '<i class="mdi mdi-send bg-info-lighten text-info timeline-icon mdi-rotate-315"></i>';

                            if (value.activity_type == 13)
                                ficon =
                                    '<i class="mdi mdi-send bg-info-lighten text-info timeline-icon mdi-rotate-315"></i>';

                            if (value.activity_type == 14)
                                ficon =
                                    '<i class="mdi mdi-calendar-blank-multiple bg-dark-lighten text-dark timeline-icon"></i>';

                            if (value.activity_type == 15)
                                ficon =
                                    '<i class="mdi mdi-calendar-blank bg-secondary-lighten text-secondary timeline-icon"></i>';
                            if (value.activity_type == 16)
                                ficon =
                                    '<i class="mdi mdi-checkbox-marked-circle-outline bg-dark-lighten text-dark timeline-icon"></i>';
                            var activity_name = '';
                            let dblock = "d-block";
                            let editaction_name = 'Edit';

                            if (value.activity_type == 5) {
                                editaction_name = 'Revise Estimate';
                                dblock = "";
                            }

                            let activity_name_icon = '';
                            if (value.activity_type == 12) {
                                activity_name_icon = '<i class="mdi mdi-message-text-outline"></i> ';

                            }

                            if (value.activity_type == 13) {
                                activity_name_icon = '<i class="mdi mdi-paperclip mdi-rotate-45"></i> ';
                            }

                            if (value.activity_name)
                                activity_name = '<span class="text-black fw-bold ' + dblock + '">' + activity_name_icon + value
                                    .activity_name + '</span>';

                            var activities_notes = value.activity_notes;

                            var activity_view_edit_action = '';
                            var activity_view_edit_action_tmp = activity_edit_action;
                            var activity_view_edit_action_href = 'href="javascript:void(0);"';
// if (value.is_modified == 0 || value.activity_type == 9) {
                            if (value.is_modified == 0 || value.activity_type == 9 || value.activity_type ==
                                10) {
                                activities_notes = value.internal_remarks;

                                if (value.is_follow_up == 1) {
                                    activities_notes = value.activity_notes;
                                }
                                activity_view_edit_action = 'd-none';
                                activity_view_edit_action_tmp = "";
                                activity_view_edit_action_href = "";
                            }

                            if (value.is_modified == 1 || value.activity_type == 2 || value.activity_type == 3 || value.activity_type == 19) { //activity merge
                                activities_notes = value.internal_remarks;
                            }
                            let pdf_download = '';
                            let activity_estimate_status = '';
                            change_status_action = '';
                            if (value.activity_type == 5) {
                                activity_view_edit_action = '';
                                activity_view_edit_action_href = 'href="' + SITEURL + '/quotes/edit/' +
                                    value.estimate_id + '/1?cid=' + value.customer_id + '"';
                                activity_view_edit_action_tmp = 'javascript:void(0);';
                                activity_edit_action = 'javascript:void(0);';
                                var tmp_amt = '';
                                if (value.net_amount > 0) {
                                    tmp_amt =
                                        ' <small class="text-dark">' + value.currency_symbol +
                                        value.net_amount + '</small>';
                                }
                                /*pdf_download = '<a href="' + value.aws_path +
                                    '.pdf" class="d-block text-primary" download><i class="mdi mdi-cloud-download-outline"></i> <small>' +
                                    value.estimate_version_no + tmp_amt + '</small></a>'*/
                                pdf_download = '<a href="' + value.aws_path + '" class="d-block text-primary pdf-download" target="_blank" download><i class="mdi mdi-cloud-download-outline"></i> <small>' +
                                    value.estimate_version_no + tmp_amt + '</small></a>'


                                var tmp_activity_estimate_status = '';
                                if (value.activity_estimate_status == 'Draft') {
                                    tmp_activity_estimate_status = 'bg-secondary';
                                }

                                if (value.activity_estimate_status == 'Sent') {
                                    tmp_activity_estimate_status = 'bg-primary';
                                }

                                if (value.activity_estimate_status == 'Inprogress') {
                                    tmp_activity_estimate_status = 'bg-warning';
                                }

                                if (value.activity_estimate_status == 'Accept') {
                                    tmp_activity_estimate_status = 'bg-success';
                                }

                                if (value.activity_estimate_status == 'Decline') {
                                    tmp_activity_estimate_status = 'bg-danger';
                                }

                                activity_estimate_status = ' <span class="badge ' +
                                    tmp_activity_estimate_status +
                                    ' text-white text-left fw-bold mb-1 ms-2">' + value
                                        .activity_estimate_status + '</span>';

                                activity_change_status_action = "activity_change_status_fun(" + value
                                        .activity_timeline_id + ",'" + value.activity_estimate_status + "','" +
                                    value.estimate_version_no + "','" + value.estimate_id + "')";
                                change_status_action = '<a href="javascript:void(0);" onclick="' +
                                    activity_change_status_action +
                                    '" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-1"></i>Change Status</a>';

                                activity_copy_est_action = "copy_est_action_fun('{!! url('/quotes/generate-link') !!}/" +
                                    value.estimate_id + "')";
                                copy_est_action = '<a href="javascript:void(0)" title="Share" onclick="' +
                                    activity_copy_est_action +
                                    '" class="copy_text dropdown-item"><i class="mdi mdi-share-variant me-1"></i>Share Estimate</a>';
                                if (!activity_estimate_status) {
                                    activity_estimate_status = '';
                                }
                            }

                            /*var dis_activity_date = value.display_created_at;
                            if(value.activity_type == 9){
                            dis_activity_date = value.display_follow_up_datetime;
                            }*/
                            if (!activities_notes) {
                                activities_notes = '';
                            }

// let content_message_url='';
                            if (value.activity_type == 12) {
                                activity_view_edit_action_href = 'href="' + SITEURL + '/content/messages/timeline/' + value.content_id + '"';
                            }

                            if (value.activity_type == 13) {
                                activity_view_edit_action_href = 'href="' + SITEURL + '/content/files/timeline/' + value.content_id + '"';
                            }

                            @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm))
                            /*||auth()->user()->company_id == null*/
                            @if (
                            (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm) ||
                            in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm)) &&
                            $customers->assigned_to_user == auth()->user()->id)
                            /*||auth()->user()->company_id == null*/
                            @else
                                activity_view_edit_action_href = "href='javascript:void(0);'";
                            activity_view_edit_action_tmp =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_edit_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_change_status_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            change_status_action = '<a href="javascript:void(0);" onclick="' +
                                activity_change_status_action +
                                '" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-1"></i>Change Status</a>';

                            activity_copy_est_action =
                                "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            copy_est_action =
                                '<a href="javascript:void(0)" title="Share" onclick="' +
                                activity_copy_est_action +
                                '" class="copy_text dropdown-item" data-url="{!! url('/quotes/generate-link') !!}/' +
                                value.estimate_id +
                                '"><i class="mdi mdi-share-variant me-1"></i>Share Estimate</a>';
                            @endif
                            {{--@else
                            activity_view_edit_action_href = "href='javascript:void(0);'";
                            activity_view_edit_action_tmp =
                            "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            activity_edit_action =
                            "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";
                            activity_change_status_action =
                            "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            /*=
                            "accessDeniedOpenModal('#access-denied-modal', 'Oops! You don’t have permission to access on the user assigned.');";*/

                            change_status_action = '<a href="javascript:void(0);" onclick="' +
                            activity_change_status_action +
                            '" class="dropdown-item"><i class="mdi mdi-book-edit-outline me-1"></i>Change Status</a>';

                            activity_copy_est_action =
                            "OpenModalAssignLead(0, '#assign-self-lead-modal', '#assign-self-lead-formModalLabel', 'Assign to Self Lead', '#assign-self-lead-form')";
                            copy_est_action = '<a href="javascript:void(0)" title="Share" onclick="' +
                            activity_copy_est_action +
                            '" class="copy_text dropdown-item" data-url="{!! url('/quotes/generate-link') !!}/' +
                            value.estimate_id +
                            '"><i class="mdi mdi-share-variant me-1"></i>Share Estimate</a>';--}}
                            @endif

                            let delete_action = '';
                            if (value.activity_type != 5) {
                                delete_action = '<a href="javascript:void(0);" onclick="' + activity_delete_action + '" class="dropdown-item"><i class="mdi mdi-delete me-1"></i>Delete</a>';
                                change_status_action = '';
                                copy_est_action = '';

                                if (value.activity_type != 12 && value.activity_type != 13) {
                                    activity_view_edit_action_tmp = '';
                                    activity_view_edit_action_href = '';
                                }
                            }

                            if (value.activity_type == 11) {
                                activities_notes = value.estimate_version_no;
                                activity_name = '<span class="text-black fw-bold ' + dblock + '">' + value
                                    .internal_remarks + '</span>';
// activity_name = value.internal_remarks+" : "+value.estimate_version_no;
                            }

                            if (value.activity_type == 12) {
                                activities_notes = value.activity_notes;
                            }
                            let cr_by = '<small class="text-muted"><i class="mdi mdi-account-circle-outline"></i> by ' + value.created_by_name + '</small>';
                            if (value.activity_type == 16) {
                                cr_by = '';
                            }

                            if (value.activity_type == 5 && value.activity_estimate_status == '') {
                                activity_view_edit_action = 'd-none';
                            }

                            if (value.activity_type == 1 || value.activity_type == 2 || value.activity_type == 3 || value.activity_type == 19) { //activity merge
                                activities_notes = value.activity_notes;
                            }

                            html_temp += '<div class="timeline-item">' +
                                ficon +
                                '<div class="timeline-item-info">' +
                                '<div class="dropdown card-widgets ' + activity_view_edit_action + '">' +
                                ' <a href="#" class="dropdown-toggle arrow-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">' +
                                '<i class="mdi mdi-dots-vertical"></i>' +
                                '</a>' +
                                '<div class="dropdown-menu dropdown-menu-end" style="">' +
                                '<a ' + activity_view_edit_action_href + ' onclick="' +
                                activity_edit_action +
                                '" class="dropdown-item"><i class="mdi mdi-pencil me-1"></i>' +
                                editaction_name + '</a>' +
                                change_status_action +

                                copy_est_action +

                                delete_action +
                                '</div>' +
                                '</div>' +
                                '<a ' + activity_view_edit_action_href + ' onclick="' +
                                activity_view_edit_action_tmp +
                                '" class="text-black fw-bold mb-1 d-block">' + value.display_created_at +
                                '</a>' +
                                activity_name + activity_estimate_status + pdf_download +

                                '<small>' + activities_notes + '</small>' +

                                '<p class="mb-0 pb-2">' +
                                cr_by +
                                '</p>' +
                                '</div>' +
                                '</div>';
                        } //activity merge
                    });
                    $("#timeline-info").html(html_temp);
                }
            });
        }

        function get_attachment_file(folder_id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('tenant.folder.get-nested-directories-with-files', ['tenant' => $segment]) }}",
                data: {
                    attachment_id: folder_id,
                    id: '{{ Request::segment(4) }}'
                },
                dataType: "json",
                success: function (res) {
                    var html_temp = '';
                    $(".attachment_description_span").html(res[0].description);
                    $(".attachment_name_span").html(res[0].name);
                    $.each(res[0].files, function (key, value) {
                        let download_action = "attachment_file_download_id(" + res[0].lead_id + "," + folder_id + ",'" + value.name + "')";
                        let delete_file_action = "attachment_file_remove_id(" + value.id + "," + folder_id + ")";
                        let fs = formatBytes(value.file_size);
                        html_temp += '<div class="card mb-2 shadow-none border" id="attachment_file_div_' + value.id + '">' +
                            '<div class="p-1">' +
                            '<div class="row align-items-center">' +
                            '<div class="col-auto">' +
                            '<img src="{{ asset('assets/images/projects/project-1.jpg')}}" class="avatar-sm rounded" alt="file-image">' +
                            '</div>' +
                            '<div class="col ps-0 text-truncate">' +
                            ' <a href="javascript:void(0);" class="text-dark fw-bold" title="' + value.name + '">' + value.name + '</a>' +
                            '<p class="mb-0">' + fs + '</p>' +
                            '</div>' +
                            '<div class="col-auto" id="tooltip-container10">' +
                            '<a href="javascript:void(0);" class="btn btn-link text-muted btn-lg p-0" aria-label="Download" data-bs-original-title="Download" onclick="' + download_action + '">' +
                            '<i class="uil uil-cloud-download"></i>' +
                            '</a>' +
                            '<a href="javascript:void(0);" class="btn btn-link text-danger btn-lg p-0" aria-label="Delete" data-bs-original-title="Delete" onclick="' + delete_file_action + '">' +
                            '<i class="uil uil-multiply"></i>' +
                            '</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    $("#file_attachment_id").val(folder_id);
                    $("#attachment-file-info").html(html_temp);
                    $('#attachment-file-modal').modal('toggle');
                }
            });
        }

        function attachment_remove_id(id) {
            /*if (id.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
            } else {*/
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonColor: "#3085D6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-danger ml-1",
                buttonsStyling: !1,
                preConfirm: function () {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('tenant.folder.delete', ['tenant' => $segment]) }}",
                        data: {
                            id: id,
                            is_type: 1
                        },
                        dataType: "json",
                        success: function (data, textStatus, jqXHR) {
                            getAttachment('{{ Request::segment(4) }}');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('Please contact developer.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                        },
                        complete: function (data) {
                        }
                    });
                }
            }).then(function (t) {
                t.value && Swal.fire({
                    title: "Success",
                    text: "Your record has been deleted.",
                    type: "success",
                    showConfirmButton: !1,
                    timer: 1500,
                    confirmButtonClass: "btn btn-success",
                    showConfirmButton: false
                })
            });
            // }
        }

        function attachment_file_remove_id(id, folder_id) {
            /*if (id.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
            } else {*/
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonColor: "#3085D6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-danger ml-1",
                buttonsStyling: !1,
                preConfirm: function () {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('tenant.folder.delete', ['tenant' => $segment]) }}",
                        data: {
                            id: id,
                            is_type: 0
                        },
                        dataType: "json",
                        success: function (data, textStatus, jqXHR) {
                            $("#attachment_file_div_" + id).remove();
                            getAttachment('{{ Request::segment(4) }}');
                            // get_attachment_file(folder_id);
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('Please contact developer.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                        },
                        complete: function (data) {
                        }
                    });
                }
            }).then(function (t) {
                t.value && Swal.fire({
                    title: "Success",
                    text: "Your record has been deleted.",
                    type: "success",
                    showConfirmButton: !1,
                    timer: 1500,
                    confirmButtonClass: "btn btn-success",
                    showConfirmButton: false
                })
            });
            // }
        }

        function attachment_file_download_id(lead_id, folder_id, path) {
            $.ajax({
                url: "{{ route('tenant.folder.download-file', ['tenant' => $segment]) }}",
                type: 'GET',
                data: {file_path: path, folder_id: folder_id, lead_id: lead_id},
                xhrFields: {
                    responseType: 'blob' // This is important for handling binary data (like images)
                },
                success: function (data) {
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    // a.download = 'downloaded_file.png'; // Set the desired file name
                    a.download = path;
                    document.body.append(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    console.log('File downloaded successfully');
                },
                error: function (xhr, status, error) {
                    console.error('Error downloading file:', error);
                }
            });
        }

        // function get All Cities
        /*function getCityList(state_id, seleted_id = 0) {
        $.ajax({
        async: false,
        url: "{{ url('get-cities-by-state') }}",
type: "POST",
data: {
state_id: state_id
},
dataType: 'json',
beforeSend: function () {
jQuery('select#city_id').find("option:eq(0)").html("Please wait..");
},
success: function (result) {
var options = '';
options += '<option value="0">Choose</option>';
$.each(result.cities, function (key, value) {
var selected = "";
if (value.id == seleted_id)
selected = 'selected';
options += '<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>';

});
$("#city_id").html(options);
},
complete: function () {
// code
},
error: function (xhr, ajaxOptions, thrownError) {
console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
}
});
}*/
    </script>
    <style>
        .dataTables_scrollHeadInner {
            width: 100% !important;
        }
    </style>
@endpush
