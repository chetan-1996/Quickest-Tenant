@php
    $expData = App\Helpers\PermissionCheck::plan_details_check();
    $t_company_id = (auth()->user()->company_id==null)? auth()->user()->id:auth()->user()->company_id;
@endphp
@extends('app.layouts.app')
@section('title','Follow up history')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <style>
        table tbody tr td:not(:first-child) {
            cursor: pointer;
        }
        .clockpicker-popover {
            z-index: 9999999999 !important;
        }

        .table-responsive-stack tr {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: horizontal;
            -webkit-box-direction: normal;
            -ms-flex-direction: row;
            flex-direction: row;
        }


        .table-responsive-stack td,
        .table-responsive-stack th {
            display: block;
            /*
               flex-grow | flex-shrink | flex-basis   */
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
        }

        .table-responsive-stack .table-responsive-stack-thead {
            font-weight: bold;
        }

        @media screen and (max-width: 768px) {
            .table-responsive-stack tr {
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                -ms-flex-direction: column;
                flex-direction: column;
                border-bottom: 3px solid #ccc;
                display: block;

            }

            .table-responsive-stack .table-action {
                background: #f2f3f7 !important;
                text-align: center !important;
            }

            /*  IE9 FIX   */
            .table-responsive-stack td {
                float: left \9;
                width: 100%;
            }
        }

        .nav-pills > li > a {
            color: #000000;
            font-weight: 600;
        }

        /*.duetoday-tbody, .upcoming-tbody, .overdue-tbody, .nofollowup-tbody, .someday-tbody {
            cursor: pointer;
        }*/

        #customer-datatable tbody tr{
            cursor: pointer;
        }
        .form-check-inline {
            margin-right: 0.5rem !important;
        }
        tbody .label_td{
            /*max-width:10px;*/
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            /*white-space: inherit !important;*/
        }

       /* #content-message-datatable tbody tr, #content-file-datatable tbody tr {
            cursor: pointer;
        }*/
        /*
        .form-check-inline {
            margin-right: 0.5rem !important;
        }*/

        /*#upcoming-datatable,#duetoday-datatable {
            min-width: 100% !important;
            max-width: 100% !important;
        }*/

        tbody .overdue_date_td {color:red;font-weight: 600 }
    </style>
    <style>

        #customer-datatable tbody tr td:not(:first-child) {
            cursor: pointer;
        }

        .form-check-inline {
            margin-right: 0.5rem !important;
        }

        tbody .label_td {
            /*max-width:10px;*/
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            /*white-space: inherit !important;*/
        }

        .blink {
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

        .description_td,
        .label_td {
            cursor: pointer;
        }

        #lead-table > :not(caption) > * > * {
            padding: 0rem 0rem;
        }

        /*.select2 .select2-container .select2-container--default .select2-container--above .select2-container--focus {
            height: 59px !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            height: 56px !important;
            line-height: 76px !important;
            padding-left: 12px;
            !*color: var(--ct-input-color);*!
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
        }*/

        .offcanvas-backdrop.show {
            opacity: 0.3 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color:#555 !important;
        }

        .checkboxbtn {
            position: relative;
            display: block;
        }

        .checkboxbtn label {
            display: block;
            background: #eef2f7;
            color: #444;
            border-radius: 2px;
            padding: 10px 20px;
            border: 1px solid #d5d5d5;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }

        .checkboxbtn label:after,
        .checkboxbtn label:before {
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

        .checkboxbtn label:before {
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

        .checkboxbtn input[type=checkbox] {
            display: none;
            position: absolute;
            width: 100%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .checkboxbtn input[type=checkbox]:checked + label {
            background: #eaf5ff;
            -webkit-animation-name: blink;
            animation-name: blink;
            -webkit-animation-duration: 1s;
            animation-duration: 1s;
            border-color: #727cf5;
        }

        .checkboxbtn input[type=checkbox]:checked + label:after {
            /*background: #d5d5d5;*/
            background: #727cf5;
        }

        .checkboxbtn input[type=checkbox]:checked + label:before {
            width: 20px;
            height: 20px;
        }

        .cl-btn-grp {
            display: flex;
            padding: 1px 6px !important;
            border-radius: 10px;
            align-items: center;
            font-size: 11px !important;
            box-shadow: rgba(2, 8, 3, .2) 0 0 3px, inset rgba(255, 255, 255, 0) 0 0 0;
            width: fit-content;
        }
        .cl-btn-grp.advance-search {
            display: flex;
            z-index: 0;
        }
        .cl-btn-grp {
            background: #fff;
        }
        .filtered-msg {
            color: #e74a3b;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-right: 2px;
        }

        element.style {
        }
        .icon-btngrp .btn, .icon-btngrp i {
            /*color: var(--icon-btn-i-color);*/
        }
        .icon-btngrp .btn {
            /*display: flex;*/
            align-items: center;
            border-radius: 2px !important;
        }
        /*.btn-group-sm>.btn, .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }*/
        /*.btn {
            display: inline-block;
            font-weight: 400;
            color: #858796;
            text-align: center;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .35rem;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }*/

    </style>
@endpush
@section('content')
    <div class="content-page">
        <div class="content">

            <!-- Start Content-->
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        {{--<div aria-live="polite" aria-atomic="true" class="bg-light position-relative bd-example-toasts" style="min-height:294px">--}}
                        {{--</div>--}}
                        {{--<div class="alert alert-dark alert-dismissible fade show mb-0" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <strong>OPR FILTER APPLIED</strong>
                        </div>--}}
                        <div class="page-title-box">
                            <div class="page-title-right">
                                {{--<div class="dropdown btn-group mb-2">
                                    <div class="category-filter">
                                        <select id="fil_team_member" name="fil_team_member" class="form-select form-select-sm {{(in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm))?'d-none' :''}}">
                                            <option value="0">All Teams</option>
                                            @foreach($teamUsers as $teamUser)
                                                <option value="{{$teamUser->id}}">{{ $teamUser->id == auth()->user()->id ? 'Myself' : $teamUser->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>--}}
                                @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('give-access-to-delete-leads', $user_perm))
                                    <div class="dropdown btn-group mb-2 me-1">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  title="Bulk Action">
                                                <span
                                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                    id="select_count" style="display:none;z-index: 1;width: 0.7rem;height: 0.7rem"></span><i class="mdi mdi-format-list-bulleted"></i>
                                            {{--                                                                <span class="badge badge-success-lighten" id="select_count">0</span>--}}
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-animated">
                                            @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                        in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) )
                                                <a href="javascript:void(0);"
                                                onclick="OpenModalAssignLead(0,'#assign-lead-modal','#assign-lead-formModalLabel','Assign Lead','#assign-lead-form');"
                                                class="dropdown-item"><i class="mdi mdi-account-check"></i> Assign</a>
                                            @endif
                                            <a href="javascript:void(0);"
                                            onclick="OpenModalAssignLeadStage(0,'#assign-lead-stage-modal','#assign-lead-stage-formModalLabel','Assign Lead Stage','#assign-lead-stage-form');"
                                            class="dropdown-item"><i class="mdi mdi-account-check"></i> Lead Stages</a>
                                                <a href="javascript:void(0);"
                                                onclick="OpenModalAssignLeadLabels(0,'#lead-label-modal','#lead-label-formModalLabel','Assign Lead Labels','#lead-label-form');"
                                                class="dropdown-item"><i class="mdi mdi-account-check"></i> Lead Labels</a>
                                            @if (in_array('give-access-to-delete-leads', $user_perm))
                                                <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                                        class="mdi mdi-delete-circle"></i> Delete Leads</a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                {{--<div class="dropdown btn-group mb-2">
                                    <div class="category-filter">
                                        <select id="fil_status" name="fil_status" class="form-select form-select-sm">
                                            <option value="">Label All</option>
                                            @foreach($leadGroups as $leadGroup)
                                                <option value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>--}}
                                <button class="btn btn-primary btn-sm mb-2" title="Filter" id="filter-btn"><span
                                        class="position-absolute translate-middle badge rounded-pill bg-danger"
                                        id="filter_count" style="left: 99.50% !important;top: 61px !important;display:nones;">0</span>
                                    <i class="mdi mdi-filter-outline"></i>
                                </button>
                                {{--<button data-bs-toggle="offcanvas" data-bs-toggle="offcanvas"
                                        data-bs-target="#theme-settings-offcanvas" class="d-none btn btn-primary btn-sm mb-2"><span
                                        class="position-absolute translate-middle badge rounded-pill bg-danger d-none"
                                        id="filter_count" style="left: 99.50% !important;top: 61px !important;">0</span>
                                    <i class="mdi mdi-filter-outline"></i> Filter
                                </button>--}}
                            </div>
                            <h4 class="page-title">Follow Up</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="card filter-container" style="display:none;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-3">
                                        <div class="align-items-center">
                                            <label for="fil_lead_stage" class="text-dark fw-bold me-2">Stage</label>
                                            <select class="form-select" id="fil_followup_lead_stage_id" name="fil_followup_lead_stage_id">
                                                <option value="">All</option>
                                                @foreach($leadStages as $leadStage)
                                                    <option value="{{$leadStage->id}}">{{$leadStage->name}}</option>
                                                @endforeach
                                                <option value="blank">Blank Stage</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="align-items-center">
                                            <label for="fil_lead_label" class="text-dark fw-bold me-2">Label</label>
                                            <select class="form-select" id="fil_followup_lead_label_id" name="fil_followup_lead_label_id[]" multiple>
                                                {{--                                            <option value="">All</option>--}}
                                                @foreach($leadGroups as $leadGroup)
                                                    <option
                                                        value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="align-items-center">
                                            <label for="fil_lead_category" class="text-dark fw-bold me-2">Category</label>
                                            <select class="form-select text-dark" id="fil_followup_customer_category_id" name="fil_followup_customer_category_id">
                                                <option value="">Choose</option>
                                                @foreach($customerCategories as $customerCategory)
                                                    <option
                                                        value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="align-items-center">
                                            <label for="fil_lead_orgin" class="text-dark fw-bold me-2">Source</label>
                                            <select class="form-select text-dark" id="fil_followup_customer_lead_id"
                                                    name="fil_followup_customer_lead_id">
                                                <option value="">Choose</option>
                                                @foreach($customerLeads as $customerLead)
                                                    <option
                                                        value="{{$customerLead->id}}">{{$customerLead->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="align-items-center">
                                            <label for="fil_created_user_id" class="text-dark fw-bold me-2">Created By</label>
                                            <select class="form-select text-dark" id="fil_followup_created_user_id"
                                                    name="fil_followup_created_user_id">
                                                <option value="">Choose</option>
                                                @foreach($teamUsers as $teamUser)
                                                    <option
                                                        value="{{$teamUser->id}}">{{$teamUser->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="align-items-center">
                                            <label for="fil_estimate_status_id" class="text-dark fw-bold me-2">Estimate Status</label>
                                            <select class="form-select text-dark" id="fil_followup_estimate_status_id"
                                                    name="fil_followup_estimate_status_id">
                                                <option value="">Choose</option>
                                                <option value="Draft">Draft</option>
                                                <option value="Inprogress">Inprogress</option>
                                                <option value="Accept">Accept</option>
                                                <option value="Decline">Decline</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="align-items-center">
                                            {{--<label for="fil_created_user_id" class="text-dark fw-bold me-2">Lead Created Date</label>
                                            <div
                                                class="d-flex justify-content-between xxx align-items-center text-primary">
                                                <div id="lead_date_range" class="form-control text-primary"
                                                    data-toggle="date-picker-range"
                                                    data-target-display="#selectedValue" data-cancel-class="btn-light"
                                                    style="max-width:100%;">
                                                    <i class="mdi mdi-calendar"></i>&nbsp;
                                                    <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                                </div>
                                            </div>--}}
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="align-items-center">
                                                    <label for="fil_lead_dtage" class="text-dark fw-bold me-2">Advance Filter</label><br>
                                                    <button class="btn btn-light" type="button" onclick="openFilterModal('#advance-filter-modal')"><i class="mdi mdi-format-list-bulleted"></i> Slice and dice your data <span
                                                            class="position-absolutes translate-middles badge rounded-pill bg-danger"
                                                            id="advance_filter_count" style="/*left: 87.4% !important;top: 106px !important;*/">0</span> </button>

                                                    <button type="submit" class="btn btn-light fullscreen ms-2" id="filter_reset_button">
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-xl-3">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="align-items-center">
                                            <label for="fil_lead_dtage" class="text-dark fw-bold me-2">Advance Filter</label><br>
                                            <button class="btn btn-light" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-format-list-bulleted"></i> Slice and dice your data</button>
                                        </div>
                                    </div>
                                </div>
                            </div>--}}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 col-xxl-12">
                        <div class="card">

                            <div class="card-body">
                                @if (request()->has('q') && (request()->q == 'opr_overdue' || request()->q == 'opr_lead_without_followup'))
                                <div class="row">
                                    <div class="col-5"></div>
                                    <div class="col-6">
                                        <div class="cl-btn-grp icon-btngrp advance-search mb-2 d-flex align-items-center">
                                            <span class="filtered-msg"><b>OPR filter applied</b></span>
                                            <a class="btn btn-default btn-sm" tabindex="0" aria-controls="accounts" href="#" title="Clear OPR Search" onclick="clearOPRSearch()">
                                                <span><i class="uil uil-search-minus"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <!-- Checkout Steps -->
                                <ul class="nav nav-pills bg-nav-pills nav-justified mb-3 text-dark" role="tablist">
                                    <li class="nav-item" role="presentation" data-id="duetoday-datatable">
                                        <a href="#due-today-tab" data-bs-toggle="tab" aria-expanded="false"
                                        class="nav-link rounded-0 active" aria-selected="true" role="tab" data-id="duetoday-datatable">
                                            <i class="mdi mdi-calendar-star font-18"></i>
                                            <span class="d-none d-lg-block">Due Today <span id="cnt_duetoday"></span></span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation" data-id="upcoming-datatable">
                                        <a href="#upcomming-tab" data-bs-toggle="tab" aria-expanded="true"
                                        class="nav-link rounded-0" aria-selected="false" role="tab" tabindex="-1" data-id="upcoming-datatable">
                                            <i class="mdi mdi-calendar-today font-18"></i>
                                            <span class="d-none d-lg-block">Upcoming <span id="cnt_upcoming"></span></span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation" data-id="overdue-datatable">
                                        <a href="#overdue-tab" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0"
                                        aria-selected="false" role="tab" tabindex="-1" data-id="overdue-datatable">
                                            <i class="mdi mdi-calendar-alert font-18"></i>
                                            <span class="d-none d-lg-block">Overdue <span id="cnt_overdue"></span></span>
                                        </a>
                                    </li>
                                    <li class="nav-item d-none" role="presentation" data-id="someday-datatable">
                                        <a href="#someday-tab" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0"
                                        aria-selected="false" role="tab" tabindex="-1" data-id="someday-datatable">
                                            <i class="mdi mdi-calendar-blank font-18"></i>
                                            <span class="d-none d-lg-block">Someday <span id="cnt_someday"></span></span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation" data-id="never-followup-datatable">
                                        <a href="#nofollowup-tab" data-bs-toggle="tab" aria-expanded="false"
                                        class="nav-link rounded-0"
                                        aria-selected="false" role="tab" tabindex="-1" data-id="never-followup-datatable">
                                            <i class="mdi mdi-calendar-blank-multiple font-18"></i>
                                            <span
                                                class="d-none d-lg-block">Never Follow Up <span id="cnt_never_followup"></span></span>
                                        </a>
                                    </li>

                                </ul>

                                <!-- Steps Information -->
                                <div class="tab-content">

                                    <!-- Billing Content-->
                                    <div class="tab-pane active show" id="due-today-tab" role="tabpanel" data-id="duetoday-datatable">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="duetoday-datatable" class="table table-centered table-hover table-sm nowrap w-100">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th><input type="checkbox" class="form-check-input" id="select_all_today"></th>
                                                            <th>Follow Up</th>
                                                            <th>Name</th>
                                                            <th>Assign To</th>
                                                            <th>Stage</th>
                                                            <th>Last Activity</th>
                                                            <th>Labels</th>
                                                            <th>Status</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody class="duetoday-tbody">

                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- end table-responsive -->
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                    </div>
                                    <!-- End Billing Information Content-->

                                    <!-- Shipping Content-->
                                    <div class="tab-pane" id="upcomming-tab" role="tabpanel" data-id="upcoming-datatable">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">

                                                    <table id="upcoming-datatable" class="table table-centered table-hover table-sm w-100 nowrap">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th><input type="checkbox" class="form-check-input" id="select_all_upcoming"></th>
                                                            <th>Follow Up</th>
                                                            <th>Name</th>
                                                            <th>Assign To</th>
                                                            <th>Stage</th>
                                                            <th>Last Activity</th>
                                                            <th>Labels</th>
                                                            <th>Status</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody class="upcoming-tbody">

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                    </div>
                                    <!-- End Shipping Information Content-->

                                    <!-- Payment Content-->
                                    <div class="tab-pane" id="overdue-tab" role="tabpanel" data-id="overdue-datatable">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="overdue-datatable" class="table table-centered table-hover table-sm w-100 nowrap">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th><input type="checkbox" class="form-check-input" id="select_all_overdue"></th>
                                                            <th>Follow Up</th>
                                                            <th>Name</th>
                                                            <th>Assign To</th>
                                                            <th>Stage</th>
                                                            <th>Last Activity</th>
                                                            <th>Labels</th>
                                                            <th>Status</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody class="overdue-tbody">

                                                        </tbody>
                                                    </table>


                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                    </div>
                                    <!-- End Payment Information Content-->

                                    <!-- Payment Content-->
                                    <div class="tab-pane" id="nofollowup-tab" role="tabpanel" data-id="never-followup-datatable">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="never-followup-datatable" class="table table-centered table-hover table-sm w-100 nowrap">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th><input type="checkbox" class="form-check-input" id="select_all_never"></th>
                                                            <th>Name</th>
                                                            <th>Assign To</th>
                                                            <th>Stage</th>
                                                            <th>Last Activity</th>
                                                            <th>Labels</th>
                                                            <th>Status</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody class="nofollowup-tbody">

                                                        </tbody>
                                                    </table>

                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                    </div>
                                    <!-- End Payment Information Content-->

                                    <!-- Payment Content-->
                                    <div class="tab-pane d-none" id="someday-tab" role="tabpanel" data-id="someday-datatable">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="someday-datatable" class="table table-centered table-hover table-sm w-100 nowrap">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th><input type="checkbox" class="form-check-input" id="select_all_someday"></th>
                                                            <th>Name</th>
                                                            <th>Assign To</th>
                                                            <th>Stage</th>
                                                            <th>Last Activity</th>
                                                            <th>Labels</th>
                                                            <th>Status</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody class="nofollowup-tbody">

                                                        </tbody>
                                                    </table>

                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                    </div>
                                    <!-- End Payment Information Content-->

                                </div> <!-- end tab content-->

                            </div>


                        </div>
                    </div>
                </div>
                <!-- end row -->

                <!-- <div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas">
                    <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
                        <h5 class="text-white m-0">Filter</h5>
                        <button type="button" class="btn-close btn-close-white ms-auto d-none" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body p-0">
                        <div data-simplebar class="h-100">
                            <div class="card mb-0 p-0">
                                {{--<div class="dropdown     mb-2">
                                    <div class="category-filter">--}}


                                <div class="accordion" id="accordionExample">
                                    {{--<div class="accordion-item">
                                        <h2 class="accordion-header m-0" id="headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseOne"
                                                    aria-expanded="true" aria-controls="collapseOne">
                                                <small
                                                    class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_labels"></small>
                                                Labels
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                @foreach($leadGroups as $leadGroup)
                                                    <div class="col-12">
                                                        <div class="form-check mb-1">
                                                            <div class="d-flex align-items-center">
                                                                <table class="table table-sm table-borderless table-wrap mb-1 p-0"
                                                                    width="100%" id="lead-table">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="width:90%;">
                                                                            <div class="flex-shrink-0">
                                                                                <input type="checkbox" class="form-check-input"
                                                                                    id="chk_{{ $leadGroup->id }}"
                                                                                    name="fil_status[]"
                                                                                    value="{{ $leadGroup->id }}"
                                                                                    --}}{{--                                                                    {{ in_array($leadGroup->id, $leadArr) ? 'checked' : '' }}--}}{{--
                                                                                >
                                                                                <label class="form-check-label text-dark"
                                                                                    for="chk_{{ $leadGroup->id }}">{{ $leadGroup->name }}</label>

                                                                            </div>
                                                                        </td>

                                                                        <td style="width:10%;">
                                                                            <div class="flex-grow-1 ms-2 text-dark text-capitalize">
                                                                                <i class="widget-icon rounded"
                                                                                style="background-color:{{ $leadGroup->color_code }} !important;height:18px;width:18px;"></i>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                                --}}{{-- <select id="fil_status" name="fil_status" class="form-select">
                                                    <option value="">Label All</option>
                                                    @foreach($leadGroups as $leadGroup)
                                                        <option value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                                    @endforeach
                                                </select>--}}{{--
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header m-0" id="headingTwo">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                <small
                                                    class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_category"></small>
                                                Lead Category
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <select class="form-select text-dark" id="fil_customer_category_id"
                                                        name="fil_customer_category_id">
                                                    <option value="">Choose</option>
                                                    @foreach($customerCategories as $customerCategory)
                                                        <option
                                                            value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header m-0" id="headingThree">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseThree" aria-expanded="false"
                                                    aria-controls="collapseThree">
                                                <small
                                                    class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_origin"></small>
                                                Lead Source
                                            </button>
                                        </h2>
                                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <select class="form-select text-dark" id="fil_customer_lead_id"
                                                        name="fil_customer_lead_id">
                                                    <option value="">Choose</option>
                                                    @foreach($customerLeads as $customerLead)
                                                        <option
                                                            value="{{$customerLead->id}}">{{$customerLead->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header m-0" id="headingFour">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseFour" aria-expanded="false"
                                                    aria-controls="collapseFour">
                                                <small
                                                    class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_created_by"></small>
                                                Lead Created By
                                            </button>
                                        </h2>
                                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <select class="form-select text-dark" id="fil_created_user_id"
                                                        name="fil_created_user_id">
                                                    <option value="">Choose</option>
                                                    @foreach($teamUsers as $teamUser)
                                                        <option
                                                            value="{{$teamUser->id}}">{{$teamUser->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header m-0" id="headingFive">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseFive" aria-expanded="false"
                                                    aria-controls="collapseFive">
                                                <small
                                                    class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_estimate_status"></small>
                                                Estimate Status
                                            </button>
                                        </h2>
                                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <select class="form-select text-dark" id="fil_estimate_status_id"
                                                        name="fil_estimate_status_id">
                                                    <option value="">Choose</option>
                                                    <option value="Draft">Draft</option>
                                                    <option value="Inprogress">Inprogress</option>
                                                    <option value="Accept">Accept</option>
                                                    <option value="Decline">Decline</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>--}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header m-0" id="headingsix">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapsesix" aria-expanded="false" aria-controls="collapsesix">
                                                <small
                                                    class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_created_date"></small>
                                                Follow Up Date
                                            </button>
                                        </h2>
                                        <div id="collapsesix" class="accordion-collapse collapse" aria-labelledby="headingsix"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div
                                                    class="d-flex justify-content-between xxx align-items-center mt-2 text-primary">
                                                    <div id="lead_date_range" class="form-controls text-primary"
                                                        data-toggle="date-picker-range"
                                                        data-target-display="#selectedValue" data-cancel-class="btn-light"
                                                        style="max-width:100%;">
                                                        <i class="mdi mdi-calendar"></i>&nbsp;
                                                        <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                {{--<div class="accordion" id="accordionPanelsStayOpenExample">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true"
                                                    aria-controls="panelsStayOpen-collapseOne">
                                                Label
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show"
                                            aria-labelledby="panelsStayOpen-headingOne">
                                            <div class="accordion-body">
                                                <select id="fil_status" name="fil_status" class="form-select form-select-sm">
                                                    <option value="">Label All</option>
                                                    @foreach($leadGroups as $leadGroup)
                                                        <option value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseTwo">
                                                Accordion Item #2
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse"
                                            aria-labelledby="panelsStayOpen-headingTwo">
                                            <div class="accordion-body">
                                                <strong>This is the second item's accordion body.</strong> It is hidden by default, until the
                                                collapse plugin adds the appropriate classes that we use to style each element. These classes
                                                control the overall appearance, as well as the showing and hiding via CSS transitions. You can
                                                modify any of this with custom CSS or overriding our default variables. It's also worth noting that
                                                just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit
                                                overflow.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseThree">
                                                Accordion Item #3
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse"
                                            aria-labelledby="panelsStayOpen-headingThree">
                                            <div class="accordion-body">
                                                <strong>This is the third item's accordion body.</strong> It is hidden by default, until the
                                                collapse plugin adds the appropriate classes that we use to style each element. These classes
                                                control the overall appearance, as well as the showing and hiding via CSS transitions. You can
                                                modify any of this with custom CSS or overriding our default variables. It's also worth noting that
                                                just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit
                                                overflow.
                                            </div>
                                        </div>
                                    </div>
                                </div>--}}




                                {{-- </div>
                            </div>--}}
                            </div>
                        </div>

                    </div>
                    <div class="offcanvas-footer border-top p-3 text-center">
                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-light w-100" id="reset-layout">Reset</button>
                            </div>
                            <div class="col-6">
                                {{--<a target="_blank" role="button" class="btn btn-primary w-100" data-bs-dismiss="offcanvas"
                                aria-label="Close">Save</a> --}}
                                <a target="_blank" role="button" class="btn btn-primary w-100" id="offcanvas-btn">Save</a>
                            </div>
                        </div>
                    </div>
                </div> -->

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
                                    <form class="assign-lead-form" id="assign-lead-form" action="#" novalidate="">
                                        <div class="col-12">
                                            <input type="hidden" id="id" name="id" value="">

                                            {{--<input type="hidden" id="follow_up_date_assign_user" name="follow_up_date_assign_user"
                                                value="{{ !empty($customers->last_follow_up_datetime) && $customers->last_follow_up_datetime != '0000-00-00 00:00:00' ? $customers->last_follow_up_datetime : '' }}">--}}
                                            @foreach ($leads as $lead)
                                                <div class="radiobtn">
                                                    <input type="radio" id="assigned_to_{{ $lead->id }}"
                                                        name="assigned_to_user" value="{{ $lead->id }}"
                                                        {{ $lead->id == auth()->user()->id ? 'checked' : '' }} />
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

                <div id="advance-filter-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-light border-bottom-1">
                                <h3 class="modal-title text-dark" id="assign-lead-stage-formModalLabel">Advance Filter</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-2">
                                <h5 class="mb-1 text-uppercase text-dark bg-light p-2"><i class="mdi mdi-office-building me-1"></i> By Location</h5>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="align-items-center">
                                            <label for="fil_city_name" class="text-dark fw-bold me-2">City Name</label>
                                            <input class="form-control" type="text" id="fil_city_name" name="fil_city_name" placeholder="City name">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="align-items-center" id="sel_st">
                                            <label for="fil_state_id" class="text-dark fw-bold me-2">State</label>
                                            <select class="form-select" id="fil_state_id" name="fil_state_id">
                                                <option value="">All</option>
                                                @foreach($fil_states as $val_state)
                                                    <option value="{{$val_state->id}}">{{$val_state->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="align-items-center" id="sel_co">
                                            <label for="fil_country_id" class="text-dark fw-bold me-2">Country</label>
                                            <select class="form-select" id="fil_country_id" name="fil_country_id">
                                                <option value="">All</option>
                                                @foreach($countries as $val_countries)
                                                    <option value="{{$val_countries->id}}">{{$val_countries->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    {{-- <div class="col-12">
                                        <div class="d-grid d-block">
                                            <button type="submit" class="btn btn-lg font-16 btn-primary lead_stage_button"
                                                    id="lead_stage_button">
                                                <i class="uil-arrow-circle-right"></i> Save
                                            </button>
                                        </div>
                                    </div>--}}
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-light fullscreen me-2" id="advance_filter_reset_button">
                                        Reset
                                    </button>
                                    <button class="btn btn-primary fullscreen" id="filter_button" type="button">
                                        {{--<i class="uil-cloud-upload fs-5"></i>--}} Apply
                                    </button>

                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <div id="assign-lead-stage-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-light border-bottom-1">
                                <h3 class="modal-title text-dark" id="assign-lead-stage-formModalLabel">Lead Stage</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 p-2">
                                <div class="row">
                                    <form class="assign-lead-stage-form" id="assign-lead-stage-form" action="#" novalidate="">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <div class="form-floating">
                                                    <input type="hidden" id="lead_id" name="lead_id"
                                                        value="{{ Request::segment(3) }}">
                                                    {{-- <textarea class="form-control bg-light" id="lead_description"
                                                            name="lead_description"
                                                            placeholder="Add optional details here..."
                                                            style="height: 250px"></textarea>--}}


                                                    <select class="form-select bg-light text-dark" id="leads_stages_id"
                                                            name="leads_stages_id" required>
                                                        {{--                                            <option value="">Choose</option>--}}
                                                        @foreach($leadStages as $leadStage)
                                                            <option value="{{$leadStage->id}}" data-id="{{$leadStage->is_default}}">{{$leadStage->name}}</option>
                                                        @endforeach
                                                    </select>


                                                    <label for="notes">Lead Stage</label>
                                                </div>
                                                <div class="form-floating mt-2 lost_reason_div"
                                                    style="display:none;">
                                                    <select class="form-select bg-light text-dark" id="lost_reason_id"
                                                            name="lost_reason_id" required>
                                                        <option value="">Choose</option>
                                                        @foreach($lostReasons as $lostReason)
                                                            <option value="{{$lostReason->id}}" data-id="{{$lostReason->priority}}">{{$lostReason->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="lost_reason_id" class="form-label">Lost Reason <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="form-floating mt-2 lost_reason_others_div"
                                                    style="display:none;">

                                                    <textarea class="form-control bg-light" id="lost_reason_others"
                                                            name="lost_reason_others"
                                                            placeholder="Add reason here..."
                                                            style="height: 250px"></textarea>
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

                {{--<div id="assign-lead-stage-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-light border-bottom-1">
                                <h3 class="modal-title text-dark" id="assign-lead-stage-formModalLabel">Lead Stage</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 p-2">
                                <div class="row">
                                    <form class="assign-lead-stage-form" id="assign-lead-stage-form" action="#" novalidate="">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <div class="form-floating">
                                                    <input type="hidden" id="id" name="id" value="">
                                                    <select class="form-select bg-light text-dark" id="leads_stages_id"
                                                            name="leads_stages_id" required>
                                                        @foreach($leadStages as $leadStage)
                                                            <option value="{{$leadStage->id}}">{{$leadStage->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="notes">Lead Stage</label>
                                                </div>
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
                </div><!-- /.modal -->--}}

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
                                                                            id="chks_{{ $leadLabel->id }}"
                                                                            name="selected_lead_id[]"
                                                                            value="{{ $leadLabel->id }}">
                                                                        <label class="form-check-label text-dark"
                                                                            for="chks_{{ $leadLabel->id }}">{{ $leadLabel->name }}</label>

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
                                    <input type="hidden" id="lead_id" name="lead_id" value="{{ Request::segment(3) }}">


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

                <div id="today-column-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content">
                            <div class="modal-header bg-light border-bottom-1">
                                <h3 class="modal-title text-dark" id="today-column-formModalLabel">Assign Lead</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4 p-2">
                                <div class="row">
                                    <form class="today-column-form" id="today-column-form" action="#" novalidate="">
                                        <div class="col-12">
                                            <input type="hidden" id="id" name="id" value="">

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="last_followup_datetime"
                                                    name="manage_column[]" value="1"/>
                                                <label for="last_followup_datetime">Follow Up</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="name"
                                                    name="manage_column[]" value="2"/>
                                                <label for="name">Name</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="assign_user_name"
                                                    name="manage_column[]" value="3"/>
                                                <label for="assign_user_name">Assign To</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="last_stage_name"
                                                    name="manage_column[]" value="4"/>
                                                <label for="last_stage_name">Stage</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="last_activity"
                                                    name="manage_column[]" value="5"/>
                                                <label for="last_activity">Last activity</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="label_name"
                                                    name="manage_column[]" value="6"/>
                                                <label for="label_name">Labels</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="estimate_status"
                                                    name="manage_column[]" value="7"/>
                                                <label for="estimate_status">Status</label>
                                            </div>

                                            <div class="checkboxbtn">
                                                <input type="checkbox" id="net_amount"
                                                    name="manage_column[]" value="8"/>
                                                <label for="net_amount">Amount</label>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-grid d-block">
                                                <button type="submit" class="btn btn-lg font-16 btn-primary" id="today_column_button">
                                                    <i class="mdi mdi-check"></i> Update
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script async src="{{ asset('js/app.min.js')}}"></script> -->
    <!-- third party js -->
    @include('layouts.partials.datatable-script')
    <!-- end demo js-->
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>

    <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js')}}"></script>
    <link href="{{ asset('vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <script>
        function clearOPRSearch(){
            window.location.href = '{{url('follow-up-history-new')}}';
        }

        function OpenModalColumn(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function OpenModalAssignLeadStage(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            var allVals = [];
            $(".single_checkbox_today:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_upcoming:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_overdue:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_someday:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_never:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            var join_selected_values = allVals.join(",");
            if (join_selected_values.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
                return false;
            }
            $(".assign-lead-stage-form #lead_id").val(join_selected_values);
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function OpenModalAssignLeadLabels(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            var allVals = [];
            $(".single_checkbox_today:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_upcoming:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_overdue:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_someday:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_never:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            var join_selected_values = allVals.join(",");
            if (join_selected_values.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
                return false;
            }
            $(".lead-label-form #lead_id").val(join_selected_values);
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function OpenModalAssignLead(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            var allVals = [];
            $(".single_checkbox_today:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_upcoming:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_overdue:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_someday:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            $(".single_checkbox_never:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            var join_selected_values = allVals.join(",");
            if (join_selected_values.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
                return false;
            }
            $(".assign-lead-form #id").val(join_selected_values);
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function fun_today_followup(fil_lead_date_start,fil_lead_date_end){
            var table = $("#duetoday-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                info: true,
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                    @if($t_company_id!=1408),
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf-box fs-4"></i>',
                        attr: {
                            title: 'PDF',
                            class: 'btn btn-light buttons-html5 buttons-pdf',
                        },
                        title: 'Lead List',
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
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }@endif,
                    {
                        text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                        action: function (e, dt, node, config) {
                            OpenModalColumn(0,'#today-column-modal','#today-column-formModalLabel','Manage Columns','#today-column-form');
                        },
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                        },
                    }
                    /*{
                        extend: 'colvis',
                        text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                        },
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }*/
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    data.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                        data.fil_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                    data.fil_customer_category_id = $('#fil_followup_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_followup_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                    // data.fil_status = $('#fil_status').val();
                    data.fil_type = $('#fil_type').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_team_member = localStorage.getItem('fil_user_id');
                    data.fil_country_id = $('#fil_country_id').val();
                    data.fil_state_id = $('#fil_state_id').val();
                    data.fil_city_name = $('#fil_city_name').val();
                    data.columns[1].visible==data.columns[1].visible;
                    data.columns[2].visible==data.columns[2].visible;
                    data.columns[3].visible==data.columns[3].visible;
                    data.columns[4].visible==data.columns[4].visible;
                    data.columns[5].visible==data.columns[5].visible;
                    data.columns[6].visible==data.columns[6].visible;
                    data.columns[7].visible==data.columns[7].visible;
                    data.columns[8].visible==data.columns[8].visible;
                    var select_data='';
                    if(data.columns[1].visible==true){
                        select_data += 'cv.last_followup_datetime,';
                        $("#last_followup_datetime").prop("checked", true);
                    }
                    else
                        $("#last_followup_datetime").prop("checked", false);

                    if(data.columns[2].visible==true) {
                        select_data += 'cv.name,';
                        $("#name").prop("checked", true);
                    }
                    else
                        $("#name").prop("checked", false);

                    if(data.columns[3].visible==true){
                        select_data += 'cv.assign_user_name,';
                        $("#assign_user_name").prop("checked", true);
                    }
                    else
                        $("#assign_user_name").prop("checked", false);

                    if(data.columns[4].visible==true){
                        select_data += 'cv.last_stage_name,';
                        $("#last_stage_name").prop("checked", true);
                    }
                    else
                        $("#last_stage_name").prop("checked", false);

                    if(data.columns[5].visible==true){
                        select_data += 'cv.last_activity,';
                        $("#last_activity").prop("checked", true);
                    }
                    else
                        $("#last_activity").prop("checked", false);

                    if(data.columns[6].visible==true){
                        select_data += 'cv.label_name,';
                        $("#label_name").prop("checked", true);
                    }
                    else
                        $("#label_name").prop("checked", false);

                    if(data.columns[7].visible==true){
                        select_data += 'cv.estimate_status,';
                        $("#estimate_status").prop("checked", true);
                    }
                    else
                        $("#estimate_status").prop("checked", false);

                    if(data.columns[8].visible==true) {
                        select_data += 'net_amount';
                        $("#net_amount").prop("checked", true);
                    }
                    else
                        $("#net_amount").prop("checked", false);
                    data.select_data=select_data;
                    localStorage.setItem('today_followup_select_data',select_data);

                    $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_created_user_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_city_name").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    if(data.fil_lead_stage_id){
                        $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_followup_lead_label_id){
                        $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #727cf5 !important;');
                        // $("#fil_followup_lead_label_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_category_id){
                        $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_lead_id){
                        $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_created_user_id){
                        $("#fil_followup_created_user_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_estimate_status_id){
                        $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_city_name){
                        $("#fil_city_name").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_state_id){
                        $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                    if(data.fil_country_id){
                        $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                },
                stateLoadParams: function (settings, data) {
                  if(data.columns[1].visible==true)
                      $("#last_followup_datetime").prop("checked", true);
                  else
                      $("#last_followup_datetime").prop("checked", false);

                    if(data.columns[2].visible==true)
                        $("#name").prop("checked", true);
                    else
                        $("#name").prop("checked", false);

                    if(data.columns[3].visible==true)
                        $("#assign_user_name").prop("checked", true);
                    else
                        $("#assign_user_name").prop("checked", false);

                    if(data.columns[4].visible==true)
                        $("#last_stage_name").prop("checked", true);
                    else
                        $("#last_stage_name").prop("checked", false);

                    if(data.columns[5].visible==true)
                        $("#last_activity").prop("checked", true);
                    else
                        $("#last_activity").prop("checked", false);

                    if(data.columns[6].visible==true)
                        $("#label_name").prop("checked", true);
                    else
                        $("#label_name").prop("checked", false);

                    if(data.columns[7].visible==true)
                        $("#estimate_status").prop("checked", true);
                    else
                        $("#estimate_status").prop("checked", false);

                    if(data.columns[8].visible==true)
                        $("#net_amount").prop("checked", true);
                    else
                        $("#net_amount").prop("checked", false);

                    var str = localStorage.getItem('fil_followup_lead_label_id');
                    var arr =[];
                    if(str){
                        arr = $.map(str.split(","), function(value) {
                            return parseInt(value, 10);
                        });
                    }
                    $("#fil_followup_lead_label_id").val(arr).trigger('change');
                    $('#fil_followup_lead_stage_id').val(data.fil_lead_stage_id);
                    $('#fil_followup_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_followup_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_followup_created_user_id').val(data.fil_created_user_id);
                    $('#fil_followup_estimate_status_id').val(data.fil_estimate_status_id);
                    // $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                    $('#fil_team_member').val(localStorage.getItem('fil_user_id'));
                    $('#fil_country_id').val(data.fil_country_id);
                    $("#fil_country_id option[value='" + data.fil_country_id + "']").prop("selected", true);
                    $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                    $('#fil_state_id').val(data.fil_state_id);
                    $("#fil_state_id option[value='" + data.fil_state_id + "']").prop("selected", true);
                    $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                    $('#fil_city_name').val(data.fil_city_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.follow-up-history.index', ['tenant' => $segment]) }}",
                    data: function (d) {

                        let i = 0;
                        // $(".small_labels").hide();
                        if (localStorage.getItem('fil_followup_lead_label_id')>0) {
                            // $(".small_labels").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_origin").hide();
                        if ($('#fil_followup_customer_lead_id').val()) {
                            // $(".small_origin").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_category").hide();
                        if ($('#fil_followup_customer_category_id').val()) {
                            // $(".small_category").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_created_by").hide();
                        if ($('#fil_followup_created_user_id').val()) {
                            // $(".small_created_by").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_estimate_status_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_lead_stage_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        let j = 0;
                        if ($('#fil_country_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_state_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_city_name').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        $("#filter_count").html(i);
                        $("#advance_filter_count").html(j);
                        d.today_followup_select_data = (typeof localStorage.today_followup_select_data === 'undefined') ? "cv.last_followup_datetime,cv.name,cv.assign_user_name,cv.last_stage_name,cv.last_activity,cv.label_name,cv.estimate_status,net_amount" : localStorage.getItem('today_followup_select_data');
                        d.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id');
                            d.fil_followup_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                            d.fil_followup_customer_category_id = $('#fil_followup_customer_category_id').val();
                            d.fil_followup_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                            d.fil_followup_created_user_id = $('#fil_followup_created_user_id').val();
                            d.fil_followup_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                            // d.status = $('#fil_status').val();
                            d.assigned_to_user = $('#fil_team_member').val();
                            d.customer_type = $('#fil_type').val();
                            d.name = $('#fil_name').val();
                            d.search = $('#duetoday-datatable_filter input[type="search"]').val();
                            d.fil_followup_country_id = $('#fil_country_id').val();
                            d.fil_followup_state_id = $('#fil_state_id').val();
                            d.fil_followup_city_name = $('#fil_city_name').val();
                    }
                },
                "order": [[1, "desc"]],
                "columnDefs": [{ "className": "label_td",
                    "targets": [5]
                }],


                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, visible: true,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox_today form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {data: 'last_follow_up_datetime', name: 'last_follow_up_datetime'},
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let country_code ='';
                            if(row.country_code){
                                country_code = row.country_code;
                            }
                            let new_lead = '';
                            if (row.new_lead_flag == 1) {
                                new_lead = '<small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1" title="New Lead"></small>';
                            }
                            return '<td>' +
                                '<h5 class="font-19 mb-1 fw-bold">' + new_lead + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                '<span class="text-muted font-10">' + country_code+row.phone_no + '</span>' +
                                '</td>';
                        }
                    },
                    {
                        data: 'cv.user_name', name: 'assign_user_name',
                        render: function (data, type, row) {
                            let currnt_user_id = {{auth()->user()->id}};
                            var assign_color = 'bg-primary';
                            var assign_icon = 'mdi-account-check';
                            if (currnt_user_id != row.assigned_to_user) {
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-lock';
                            }
                            let tmp_usr_name = row.assign_user_name;
                            if(row.assigned_to_user==0){
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-off';
                                tmp_usr_name ='Unassigned';
                            }

                            if (currnt_user_id == row.assigned_to_user) {
                                tmp_usr_name = '';
                            }
                            var ss='';

                            // if(row.assigned_to_user) {
                            ss = '<td>' +
                                '<span class=" fs-6 badge ' + assign_color + '"> <i class="pe-1 mdi ' + assign_icon + '"></i>' + tmp_usr_name + '</span>' +
                                '</td>';
                            // }

                            return ss;
                        }
                    },
                    {
                        data: 'lead_stage_name', name: 'lead_stage_name',
                        render: function (data, type, row) {
                            return '<span class="fs-6 badge me-1" style="background-color:' + row.lead_stage_color_code + '">' +
                                row.lead_stage_name +
                                '</span>';
                        }

                    },
                    {data: 'last_activity', name: 'last_activity',
                        render: function (data, type, row) {
                            let ficon = '';
                            let last_activity_type = row.last_activity_type;
                            let last_activity_name = row.last_activity_name;
                            let last_activity = row.last_activity;
                            let last_internal_remarks = row.last_internal_remarks;
                            let time_ago_string = row.time_ago_string;

                            if (last_activity_type == 1){
                                ficon = '<i class="mdi mdi-phone text-success timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } //call

                            if (last_activity_type == 2) {
                                ficon = '<i class="mdi mdi-chat-outline text-warning timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            }// message

                            if (last_activity_type == 3){
                                ficon = '<i class="mdi mdi-calendar text-dark timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // meeting

                            if (last_activity_type == 19) {
                                ficon = '<i class="mdi mdi-map-marker-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // Site Visit

                            if (last_activity_type == 4){
                                ficon = '<i class="mdi mdi-file-document-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // notes

                            if (last_activity_type == 5){
                                ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // estimate

                            if (last_activity_type == 6){
                                ficon = '<i class="mdi mdi-account-plus-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // create leads

                            if (last_activity_type == 7){
                                ficon = '<i class="mdi mdi-pencil text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // edit leads

                            if (last_activity_type == 8){
                                ficon = '<i class="mdi mdi-arrow-top-right text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // assigned leads

                            if (last_activity_type == 9){
                                ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                if(row.last_is_modified==1 && row.last_is_follow_up==1){
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                let tmp_last_activity='';
                                if(last_activity){
                                    tmp_last_activity = ' - '+last_activity;
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+tmp_last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // follow up

                            if (last_activity_type == 10){
                                ficon = '<i class="mdi mdi-book-edit-outline text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated


                            if (last_activity_type == 12){
                                ficon = '<i class="mdi mdi-message-text-outline text-primary timeline-icon"></i> Message Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 13){
                                ficon = '<i class="mdi mdi-file-document-outline text-primary timeline-icon"></i> File Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 14) {
                                ficon = '<i class="mdi mdi-calendar-blank-multiple text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // remove follow up

                            if (last_activity_type == 15) {
                                ficon = '<i class="mdi mdi-calendar-blank text-secondary timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // someday follow up

                            if (last_activity_type == 16) {
                                ficon = '<i class="mdi mdi-checkbox-marked-circle-outline text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated client side

                            if (last_activity_type == 17) {
                                ficon = '<i class="mdi mdi mdi-close text-danger timeline-icon"></i>';
                                last_activity = 'Lead Lost -' + last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // lead lost Reason

                            if (last_activity_type == 18) {
                                ficon = '<i class="mdi mdi-trophy-outline text-success timeline-icon"></i>';
                                last_activity = ' Lead Won - '+ time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead won

                            if (last_activity_type == 20) {
                                ficon = '<i class="mdi mdi-call-merge text-primary timeline-icon"></i>';
                                let tmp_last_activity = '';
                                if (last_internal_remarks) {
                                    tmp_last_activity = ' - ' + last_internal_remarks;
                                }
                                last_activity = last_activity_name + tmp_last_activity + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead merge

                            // return funcStrLimits(last_activity,112,1);
                            // return funcStrLimits(last_activity, 50, 1);
                            return last_activity;
                        }
                    },

                    {data: 'label_name', name: 'label_name'},
                    {data: 'estimate_status', name: 'estimate_status', visible: true,
                        render: function (data, type, row) {
                            let tmp_status='';
                            if(row.estimate_status=='Draft'){
                                tmp_status = '<span class="text-secondary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Sent'){
                                tmp_status = '<span class="text-primary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Inprogress'){
                                tmp_status = '<span class="text-warning fw-bold">In Progress</span>';
                            }
                            if(row.estimate_status=='Accept'){
                                tmp_status = '<span class="text-success fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Decline'){
                                tmp_status = '<span class="text-danger fw-bold">'+row.estimate_status+'</span>';
                            }
                            return tmp_status;
                        }
                    },
                    {data: 'net_amount', name: 'net_amount'},

                ],
                drawCallback: function () {
                    $("#cnt_duetoday").text('('+table.page.info().recordsTotal+')');
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);
                }
            });
            /*$(document).on('click', 'body #duetoday-datatable tbody tr td:not(:first-child)', function (event) {
                alert();
                event.preventDefault(); // Prevent the default action
                var id = $(this).attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });*/
            $("#duetoday-datatable").on('click', 'tbody tr td:not(:first-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });
        }

        function fun_upcomming_followup(fil_lead_date_start,fil_lead_date_end){
            var table_upcoming = $("#upcoming-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                info: true,
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    }@if($t_company_id!=1408),
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf-box fs-4"></i>',
                        attr: {
                            title: 'PDF',
                            class: 'btn btn-light buttons-html5 buttons-pdf',
                        },
                        title: 'Lead List',
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
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }@endif
                    ,{
                        extend: 'colvis',
                        text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                        },
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    data.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                        data.fil_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                    data.fil_customer_category_id = $('#fil_followup_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_followup_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                    // data.fil_status = $('#fil_status').val();
                    data.fil_type = $('#fil_type').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_team_member = localStorage.getItem('fil_user_id');
                    data.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD");
                    data.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD");
                    data.fil_country_id = $('#fil_country_id').val();
                    data.fil_state_id = $('#fil_state_id').val();
                    data.fil_city_name = $('#fil_city_name').val();

                    $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_created_user_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_city_name").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    if(data.fil_lead_stage_id){
                        $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_followup_lead_label_id){
                        $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #727cf5 !important;');
                        // $("#fil_followup_lead_label_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_category_id){
                        $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_lead_id){
                        $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_created_user_id){
                        $("#fil_followup_created_user_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_estimate_status_id){
                        $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_city_name){
                        $("#fil_city_name").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_state_id){
                        $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                    if(data.fil_country_id){
                        $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                },
                stateLoadParams: function (settings, data) {
                    var str = localStorage.getItem('fil_followup_lead_label_id');
                    var arr =[];
                    if(str){
                        arr = $.map(str.split(","), function(value) {
                            return parseInt(value, 10);
                        });
                    }
                    $("#fil_followup_lead_label_id").val(arr).trigger('change');
                    $('#fil_followup_lead_stage_id').val(data.fil_lead_stage_id);
                    $('#fil_followup_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_followup_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_followup_created_user_id').val(data.fil_created_user_id);
                    $('#fil_followup_estimate_status_id').val(data.fil_estimate_status_id);

                    // $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                    $('#fil_team_member').val(localStorage.getItem('fil_user_id'));
                    $('#lead_date_range span').html(moment(fil_lead_date_start, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY') + ' - ' + moment(fil_lead_date_end, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY'));
                    $('#fil_country_id').val(data.fil_country_id);
                    $("#fil_country_id option[value='" + data.fil_country_id + "']").prop("selected", true);
                    $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                    $('#fil_state_id').val(data.fil_state_id);
                    $("#fil_state_id option[value='" + data.fil_state_id + "']").prop("selected", true);
                    $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                    $('#fil_city_name').val(data.fil_city_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.follow-up-history.upcomingIndex', ['tenant' => $segment]) }}",
                    data: function (d) {
                        let i = 0;
                        // $(".small_labels").hide();
                        if (localStorage.getItem('fil_followup_lead_label_id')>0) {
                            // $(".small_labels").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_origin").hide();
                        if ($('#fil_followup_customer_lead_id').val()) {
                            // $(".small_origin").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_category").hide();
                        if ($('#fil_followup_customer_category_id').val()) {
                            // $(".small_category").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_created_by").hide();
                        if ($('#fil_followup_created_user_id').val()) {
                            // $(".small_created_by").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_estimate_status_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_lead_stage_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        let j = 0;
                        if ($('#fil_country_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_state_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_city_name').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        $("#filter_count").html(i);
                        $("#advance_filter_count").html(j);
                        d.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                            d.fil_followup_lead_stage_id = $('#fil_followup_lead_stage_id').val(),
                            d.fil_followup_customer_category_id = $('#fil_followup_customer_category_id').val(),
                            d.fil_followup_customer_lead_id = $('#fil_followup_customer_lead_id').val(),
                            d.fil_followup_created_user_id = $('#fil_followup_created_user_id').val(),
                            d.fil_followup_estimate_status_id = $('#fil_followup_estimate_status_id').val(),
                            // d.status = $('#fil_status').val(),
                            d.assigned_to_user = $('#fil_team_member').val(),
                            d.customer_type = $('#fil_type').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('#upcoming-datatable_filter input[type="search"]').val(),
                            d.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD"),
                            d.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD"),
                            d.fil_followup_country_id = $('#fil_country_id').val(),
                            d.fil_followup_state_id = $('#fil_state_id').val(),
                            d.fil_followup_city_name = $('#fil_city_name').val()
                    }
                },
                "order": [[0, "asc"]],
                "columnDefs": [{ "className": "label_td",
                    "targets": [4]
                }],


                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, visible: true,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox_upcoming form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {data: 'last_follow_up_datetime', name: 'last_follow_up_datetime'},
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let country_code ='';
                            if(row.country_code){
                                country_code = row.country_code;
                            }
                            let new_lead = '';
                            if (row.new_lead_flag == 1) {
                                new_lead = '<small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1" title="New Lead"></small>';
                            }
                            return '<td>' +
                                '<h5 class="font-19 mb-1 fw-bold">' + new_lead + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                '<span class="text-muted font-10">' + country_code+row.phone_no + '</span>' +
                                '</td>';
                        }
                    },
                    {
                        data: 'cv.user_name', name: 'assign_user_name',
                        render: function (data, type, row) {
                            let currnt_user_id = {{auth()->user()->id}};
                            var assign_color = 'bg-primary';
                            var assign_icon = 'mdi-account-check';
                            if (currnt_user_id != row.assigned_to_user) {
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-lock';
                            }
                            let tmp_usr_name = row.assign_user_name;
                            if(row.assigned_to_user==0){
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-off';
                                tmp_usr_name ='Unassigned';
                            }

                            if (currnt_user_id == row.assigned_to_user) {
                                tmp_usr_name = '';
                            }
                            var ss='';

                            // if(row.assigned_to_user) {
                            ss = '<td>' +
                                '<span class=" fs-6 badge ' + assign_color + '"> <i class="pe-1 mdi ' + assign_icon + '"></i>' + tmp_usr_name + '</span>' +
                                '</td>';
                            // }

                            return ss;
                        }
                    },
                    {
                        data: 'lead_stage_name', name: 'lead_stage_name',
                        render: function (data, type, row) {
                            return '<span class="fs-6 badge me-1" style="background-color:' + row.lead_stage_color_code + '">' +
                                row.lead_stage_name +
                                '</span>';
                        }

                    },
                    {data: 'last_activity', name: 'last_activity',
                        render: function (data, type, row) {
                            let ficon = '';
                            let last_activity_type = row.last_activity_type;
                            let last_activity_name = row.last_activity_name;
                            let last_activity = row.last_activity;
                            let last_internal_remarks = row.last_internal_remarks;
                            let time_ago_string = row.time_ago_string;

                            if (last_activity_type == 1){
                                ficon = '<i class="mdi mdi-phone text-success timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } //call

                            if (last_activity_type == 2) {
                                ficon = '<i class="mdi mdi-chat-outline text-warning timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            }// message

                            if (last_activity_type == 3){
                                ficon = '<i class="mdi mdi-calendar text-dark timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // meeting

                            if (last_activity_type == 19) {
                                ficon = '<i class="mdi mdi-map-marker-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // Site Visit

                            if (last_activity_type == 4){
                                ficon = '<i class="mdi mdi-file-document-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // notes

                            if (last_activity_type == 5){
                                ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // estimate

                            if (last_activity_type == 6){
                                ficon = '<i class="mdi mdi-account-plus-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // create leads

                            if (last_activity_type == 7){
                                ficon = '<i class="mdi mdi-pencil text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // edit leads

                            if (last_activity_type == 8){
                                ficon = '<i class="mdi mdi-arrow-top-right text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // assigned leads

                            if (last_activity_type == 9){
                                ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                if(row.last_is_modified==1 && row.last_is_follow_up==1){
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                let tmp_last_activity='';
                                if(last_activity){
                                    tmp_last_activity = ' - '+last_activity;
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+tmp_last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // follow up

                            if (last_activity_type == 10){
                                ficon = '<i class="mdi mdi-book-edit-outline text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated


                            if (last_activity_type == 12){
                                ficon = '<i class="mdi mdi-message-text-outline text-primary timeline-icon"></i> Message Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 13){
                                ficon = '<i class="mdi mdi-file-document-outline text-primary timeline-icon"></i> File Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 14) {
                                ficon = '<i class="mdi mdi-calendar-blank-multiple text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // remove follow up

                            if (last_activity_type == 15) {
                                ficon = '<i class="mdi mdi-calendar-blank text-secondary timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // someday follow up

                            if (last_activity_type == 16) {
                                ficon = '<i class="mdi mdi-checkbox-marked-circle-outline text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated client side

                            if (last_activity_type == 17) {
                                ficon = '<i class="mdi mdi mdi-close text-danger timeline-icon"></i>';
                                last_activity = 'Lead Lost -' + last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // lead lost Reason

                            if (last_activity_type == 18) {
                                ficon = '<i class="mdi mdi-trophy-outline text-success timeline-icon"></i>';
                                last_activity = ' Lead Won - '+ time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead won

                            if (last_activity_type == 20) {
                                ficon = '<i class="mdi mdi-call-merge text-primary timeline-icon"></i>';
                                let tmp_last_activity = '';
                                if (last_internal_remarks) {
                                    tmp_last_activity = ' - ' + last_internal_remarks;
                                }
                                last_activity = last_activity_name + tmp_last_activity + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead merge

                            // return funcStrLimits(last_activity,112,1);
                            // return funcStrLimits(last_activity, 50, 1);
                            return last_activity;
                        }
                    },

                    {data: 'label_name', name: 'label_name'},
                    {data: 'estimate_status', name: 'estimate_status', visible: true,
                        render: function (data, type, row) {
                            let tmp_status='';
                            if(row.estimate_status=='Draft'){
                                tmp_status = '<span class="text-secondary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Sent'){
                                tmp_status = '<span class="text-primary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Inprogress'){
                                tmp_status = '<span class="text-warning fw-bold">In Progress</span>';
                            }
                            if(row.estimate_status=='Accept'){
                                tmp_status = '<span class="text-success fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Decline'){
                                tmp_status = '<span class="text-danger fw-bold">'+row.estimate_status+'</span>';
                            }
                            return tmp_status;
                        }
                    },
                    {data: 'net_amount', name: 'net_amount'},

                ],
                drawCallback: function () {
                    $("#cnt_upcoming").text('('+table_upcoming.page.info().recordsTotal+')');
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);

                }
            });
            table_upcoming.on('click', 'tbody tr td:not(:first-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });
        }

        function fun_overdue_followup(fil_lead_date_start,fil_lead_date_end){
            var table_overdue = $("#overdue-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                info: true,
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                    @if($t_company_id!=1408),
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf-box fs-4"></i>',
                        attr: {
                            title: 'PDF',
                            class: 'btn btn-light buttons-html5 buttons-pdf',
                        },
                        title: 'Lead List',
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
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                        @endif
                    ,{
                        extend: 'colvis',
                        text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                        },
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    data.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                        data.fil_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                    data.fil_customer_category_id = $('#fil_followup_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_followup_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                    // data.fil_status = $('#fil_status').val();
                    data.fil_type = $('#fil_type').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_team_member = localStorage.getItem('fil_user_id');
                    data.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD");
                    data.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD");
                    data.fil_country_id = $('#fil_country_id').val();
                    data.fil_state_id = $('#fil_state_id').val();
                    data.fil_city_name = $('#fil_city_name').val();

                    $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_created_user_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_city_name").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    if(data.fil_lead_stage_id){
                        $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_followup_lead_label_id){
                        $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #727cf5 !important;');
                        // $("#fil_followup_lead_label_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_category_id){
                        $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_lead_id){
                        $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_created_user_id){
                        $("#fil_followup_created_user_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_estimate_status_id){
                        $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_city_name){
                        $("#fil_city_name").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_state_id){
                        $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                    if(data.fil_country_id){
                        $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                },
                stateLoadParams: function (settings, data) {
                    var str = localStorage.getItem('fil_followup_lead_label_id');
                    var arr =[];
                    if(str){
                        arr = $.map(str.split(","), function(value) {
                            return parseInt(value, 10);
                        });
                    }
                    $("#fil_followup_lead_label_id").val(arr).trigger('change');
                    $('#fil_followup_lead_stage_id').val(data.fil_lead_stage_id);
                    $('#fil_followup_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_followup_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_followup_created_user_id').val(data.fil_created_user_id);
                    $('#fil_followup_estimate_status_id').val(data.fil_estimate_status_id);
                    // $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                    $('#fil_team_member').val(localStorage.getItem('fil_user_id'));
                    $('#lead_date_range span').html(moment(fil_lead_date_start, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY') + ' - ' + moment(fil_lead_date_end, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY'));
                    $('#fil_country_id').val(data.fil_country_id);
                    $("#fil_country_id option[value='" + data.fil_country_id + "']").prop("selected", true);
                    $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                    $('#fil_state_id').val(data.fil_state_id);
                    $("#fil_state_id option[value='" + data.fil_state_id + "']").prop("selected", true);
                    $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                    $('#fil_city_name').val(data.fil_city_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    type: 'POST',
                    url: "{{ route('tenant.follow-up-history.overdueIndex', ['tenant' => $segment]) }}",
                    data: function (d) {
                        let i = 0;
                        // $(".small_labels").hide();
                        if (localStorage.getItem('fil_followup_lead_label_id')>0) {
                            // $(".small_labels").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_origin").hide();
                        if ($('#fil_followup_customer_lead_id').val()) {
                            // $(".small_origin").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_category").hide();
                        if ($('#fil_followup_customer_category_id').val()) {
                            // $(".small_category").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_created_by").hide();
                        if ($('#fil_followup_created_user_id').val()) {
                            // $(".small_created_by").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_estimate_status_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_lead_stage_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        let j = 0;
                        if ($('#fil_country_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_state_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_city_name').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        $("#filter_count").html(i);
                        $("#advance_filter_count").html(j);
                        d.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id');
                            d.fil_followup_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                            d.fil_followup_customer_category_id = $('#fil_followup_customer_category_id').val();
                            d.fil_followup_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                            d.fil_followup_created_user_id = $('#fil_followup_created_user_id').val();
                            d.fil_followup_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                            // d.status = $('#fil_status').val();
                            d.assigned_to_user = $('#fil_team_member').val();
                            d.customer_type = $('#fil_type').val();
                            d.name = $('#fil_name').val();
                            d.search = $('#overdue-datatable_filter input[type="search"]').val();
                            d.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD");
                            d.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD");
                            d.fil_followup_country_id = $('#fil_country_id').val();
                            d.fil_followup_state_id = $('#fil_state_id').val();
                            d.fil_followup_city_name = $('#fil_city_name').val();
                            d.opr_id_1 = $('#fil_city_name').val();
                        var tmp_opr_id_1;
                        @if (request()->has('q') && request()->q == 'opr_overdue')
                            tmp_opr_id_1 = localStorage.getItem('opr_id_1');
                        @else
                            tmp_opr_id_1 = '';
                        @endif
                            d.opr_id_1= tmp_opr_id_1;

                    }
                },
                "order": [[1, "desc"]],
                "columnDefs": [{ "className": "label_td",
                    "targets": [4]
                },{ "className": "overdue_date_td",
                    "targets": [0]
                }],

                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, visible: true,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox_overdue form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {data: 'last_follow_up_datetime', name: 'last_follow_up_datetime'},
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let country_code ='';
                            if(row.country_code){
                                country_code = row.country_code;
                            }
                            let new_lead = '';
                            if (row.new_lead_flag == 1) {
                                new_lead = '<small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1" title="New Lead"></small>';
                            }
                            return '<td>' +
                                '<h5 class="font-19 mb-1 fw-bold">' +  new_lead + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                '<span class="text-muted font-10">' + country_code+row.phone_no + '</span>' +
                                '</td>';
                        }
                    },
                    {
                        data: 'cv.user_name', name: 'assign_user_name',
                        render: function (data, type, row) {
                            let currnt_user_id = {{auth()->user()->id}};
                            var assign_color = 'bg-primary';
                            var assign_icon = 'mdi-account-check';
                            if (currnt_user_id != row.assigned_to_user) {
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-lock';
                            }
                            let tmp_usr_name = row.assign_user_name;
                            if(row.assigned_to_user==0){
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-off';
                                tmp_usr_name ='Unassigned';
                            }

                            if (currnt_user_id == row.assigned_to_user) {
                                tmp_usr_name = '';
                            }
                            var ss='';

                            // if(row.assigned_to_user) {
                            ss = '<td>' +
                                '<span class=" fs-6 badge ' + assign_color + '"> <i class="pe-1 mdi ' + assign_icon + '"></i>' + tmp_usr_name + '</span>' +
                                '</td>';
                            // }

                            return ss;
                        }
                    },
                    {
                        data: 'lead_stage_name', name: 'lead_stage_name',
                        render: function (data, type, row) {
                            return '<span class="fs-6 badge me-1" style="background-color:' + row.lead_stage_color_code + '">' +
                                row.lead_stage_name +
                                '</span>';
                        }

                    },
                    {data: 'last_activity', name: 'last_activity',
                        render: function (data, type, row) {
                            let ficon = '';
                            let last_activity_type = row.last_activity_type;
                            let last_activity_name = row.last_activity_name;
                            let last_activity = row.last_activity;
                            let last_internal_remarks = row.last_internal_remarks;
                            let time_ago_string = row.time_ago_string;

                            if (last_activity_type == 1){
                                ficon = '<i class="mdi mdi-phone text-success timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } //call

                            if (last_activity_type == 2) {
                                ficon = '<i class="mdi mdi-chat-outline text-warning timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            }// message

                            if (last_activity_type == 3){
                                ficon = '<i class="mdi mdi-calendar text-dark timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // meeting

                            if (last_activity_type == 19) {
                                ficon = '<i class="mdi mdi-map-marker-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // Site Visit

                            if (last_activity_type == 4){
                                ficon = '<i class="mdi mdi-file-document-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // notes

                            if (last_activity_type == 5){
                                ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // estimate

                            if (last_activity_type == 6){
                                ficon = '<i class="mdi mdi-account-plus-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // create leads

                            if (last_activity_type == 7){
                                ficon = '<i class="mdi mdi-pencil text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // edit leads

                            if (last_activity_type == 8){
                                ficon = '<i class="mdi mdi-arrow-top-right text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // assigned leads

                            if (last_activity_type == 9){
                                ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                if(row.last_is_modified==1 && row.last_is_follow_up==1){
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                let tmp_last_activity='';
                                if(last_activity){
                                    tmp_last_activity = ' - '+last_activity;
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+tmp_last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // follow up

                            if (last_activity_type == 10){
                                ficon = '<i class="mdi mdi-book-edit-outline text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated


                            if (last_activity_type == 12){
                                ficon = '<i class="mdi mdi-message-text-outline text-primary timeline-icon"></i> Message Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 13){
                                ficon = '<i class="mdi mdi-file-document-outline text-primary timeline-icon"></i> File Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 14) {
                                ficon = '<i class="mdi mdi-calendar-blank-multiple text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // remove follow up

                            if (last_activity_type == 15) {
                                ficon = '<i class="mdi mdi-calendar-blank text-secondary timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // someday follow up

                            if (last_activity_type == 16) {
                                ficon = '<i class="mdi mdi-checkbox-marked-circle-outline text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated client side

                            if (last_activity_type == 17) {
                                ficon = '<i class="mdi mdi mdi-close text-danger timeline-icon"></i>';
                                last_activity = 'Lead Lost -' + last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // lead lost Reason

                            if (last_activity_type == 18) {
                                ficon = '<i class="mdi mdi-trophy-outline text-success timeline-icon"></i>';
                                last_activity = ' Lead Won - '+ time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead won

                            if (last_activity_type == 20) {
                                ficon = '<i class="mdi mdi-call-merge text-primary timeline-icon"></i>';
                                let tmp_last_activity = '';
                                if (last_internal_remarks) {
                                    tmp_last_activity = ' - ' + last_internal_remarks;
                                }
                                last_activity = last_activity_name + tmp_last_activity + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead merge

                            // return funcStrLimits(last_activity,112,1);
                            // return funcStrLimits(last_activity, 50, 1);
                            return last_activity;
                        }
                    },

                    {data: 'label_name', name: 'label_name'},
                    {data: 'estimate_status', name: 'estimate_status', visible: true,
                        render: function (data, type, row) {
                            let tmp_status='';
                            if(row.estimate_status=='Draft'){
                                tmp_status = '<span class="text-secondary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Sent'){
                                tmp_status = '<span class="text-primary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Inprogress'){
                                tmp_status = '<span class="text-warning fw-bold">In Progress</span>';
                            }
                            if(row.estimate_status=='Accept'){
                                tmp_status = '<span class="text-success fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Decline'){
                                tmp_status = '<span class="text-danger fw-bold">'+row.estimate_status+'</span>';
                            }
                            return tmp_status;
                        }
                    },
                    {data: 'net_amount', name: 'net_amount'},

                ],
                drawCallback: function () {
                    $("#cnt_overdue").text('('+table_overdue.page.info().recordsTotal+')');
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);

                }
            });
            table_overdue.on('click', 'tbody tr td:not(:first-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });
        }

        /*function fun_someday_followup(fil_lead_date_start,fil_lead_date_end){
            var table_someday = $("#someday-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                info: true,
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf-box fs-4"></i>',
                        attr: {
                            title: 'PDF',
                            class: 'btn btn-light buttons-html5 buttons-pdf',
                        },
                        title: 'Lead List',
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
                        title: 'Lead List',
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
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    data.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                        data.fil_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                    data.fil_customer_category_id = $('#fil_followup_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_followup_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                    // data.fil_status = $('#fil_status').val();
                    data.fil_type = $('#fil_type').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_team_member = localStorage.getItem('fil_user_id');
                    data.fil_country_id = $('#fil_country_id').val();
                    data.fil_state_id = $('#fil_state_id').val();
                    data.fil_city_name = $('#fil_city_name').val();
                },
                stateLoadParams: function (settings, data) {
                    var str = localStorage.getItem('fil_followup_lead_label_id');
                    var arr =[];
                    if(str){
                        arr = $.map(str.split(","), function(value) {
                            return parseInt(value, 10);
                        });
                    }
                    $("#fil_followup_lead_label_id").val(arr).trigger('change');
                    $('#fil_followup_lead_stage_id').val(data.fil_lead_stage_id);
                    $('#fil_followup_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_followup_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_followup_created_user_id').val(data.fil_created_user_id);
                    $('#fil_followup_estimate_status_id').val(data.fil_estimate_status_id);
                    // $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                    $('#fil_team_member').val(localStorage.getItem('fil_user_id'));
                    $('#fil_country_id').val(data.fil_country_id);
                    $("#fil_country_id option[value='" + data.fil_country_id + "']").prop("selected", true);
                    $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                    $('#fil_state_id').val(data.fil_state_id);
                    $("#fil_state_id option[value='" + data.fil_state_id + "']").prop("selected", true);
                    $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                    $('#fil_city_name').val(data.fil_city_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.follow-up-history.somedayIndex', ['tenant' => $segment]) }}",
                    data: function (d) {
                        let i = 0;
                        // $(".small_labels").hide();
                        if (localStorage.getItem('fil_followup_lead_label_id')>0) {
                            // $(".small_labels").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_origin").hide();
                        if ($('#fil_followup_customer_lead_id').val()) {
                            // $(".small_origin").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_category").hide();
                        if ($('#fil_followup_customer_category_id').val()) {
                            // $(".small_category").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_created_by").hide();
                        if ($('#fil_followup_created_user_id').val()) {
                            // $(".small_created_by").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_estimate_status_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_lead_stage_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        let j = 0;
                        if ($('#fil_country_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_state_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_city_name').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        $("#filter_count").html(i);
                        $("#advance_filter_count").html(j);
                        d.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                            d.fil_followup_lead_stage_id = $('#fil_followup_lead_stage_id').val(),
                            d.fil_followup_customer_category_id = $('#fil_followup_customer_category_id').val(),
                            d.fil_followup_customer_lead_id = $('#fil_followup_customer_lead_id').val(),
                            d.fil_followup_created_user_id = $('#fil_followup_created_user_id').val(),
                            d.fil_followup_estimate_status_id = $('#fil_followup_estimate_status_id').val(),
                            // d.status = $('#fil_status').val(),
                            d.assigned_to_user = $('#fil_team_member').val(),
                            d.customer_type = $('#fil_type').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('#someday-datatable_filter input[type="search"]').val(),
                            d.fil_followup_country_id = $('#fil_country_id').val(),
                            d.fil_followup_state_id = $('#fil_state_id').val(),
                            d.fil_followup_city_name = $('#fil_city_name').val()
                    }
                },
                "order": [[1, "desc"]],
                "columnDefs": [{ "className": "label_td",
                    "targets": [4]
                }],

                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, visible: true,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox_someday form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let country_code ='';
                            if(row.country_code){
                                country_code = row.country_code;
                            }
                            let new_lead = '';
                            // if (row.new_lead_flag == 1) {
                            //     new_lead = '<small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1" title="New Lead"></small>';
                            // }
                            return '<td>' +
                                '<h5 class="font-19 mb-1 fw-bold">' + new_lead + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                '<span class="text-muted font-10">' + country_code+row.phone_no + '</span>' +
                                '</td>';
                        }
                    },
                    {
                        data: 'cv.user_name', name: 'assign_user_name',
                        render: function (data, type, row) {
                            let currnt_user_id = {{auth()->user()->id}};
                            var assign_color = 'bg-primary';
                            var assign_icon = 'mdi-account-check';
                            if (currnt_user_id != row.assigned_to_user) {
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-lock';
                            }
                            let tmp_usr_name = row.assign_user_name;
                            if(row.assigned_to_user==0){
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-off';
                                tmp_usr_name ='Unassigned';
                            }

                            if (currnt_user_id == row.assigned_to_user) {
                                tmp_usr_name = '';
                            }
                            var ss='';

                            // if(row.assigned_to_user) {
                            ss = '<td>' +
                                '<span class=" fs-6 badge ' + assign_color + '"> <i class="pe-1 mdi ' + assign_icon + '"></i>' + tmp_usr_name + '</span>' +
                                '</td>';
                            // }

                            return ss;
                        }
                    },
                    {
                        data: 'lead_stage_name', name: 'lead_stage_name',
                        render: function (data, type, row) {
                            return '<span class="fs-6 badge me-1" style="background-color:' + row.lead_stage_color_code + '">' +
                                row.lead_stage_name +
                                '</span>';
                        }

                    },
                    {data: 'last_activity', name: 'last_activity',
                        render: function (data, type, row) {
                            let ficon = '';
                            let last_activity_type = row.last_activity_type;
                            let last_activity_name = row.last_activity_name;
                            let last_activity = row.last_activity;
                            let last_internal_remarks = row.last_internal_remarks;
                            let time_ago_string = row.time_ago_string;

                            if (last_activity_type == 1){
                                ficon = '<i class="mdi mdi-phone text-success timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } //call

                            if (last_activity_type == 2) {
                                ficon = '<i class="mdi mdi-chat-outline text-warning timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            }// message

                            if (last_activity_type == 3){
                                ficon = '<i class="mdi mdi-calendar text-dark timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // meeting

                            if (last_activity_type == 19) {
                                ficon = '<i class="mdi mdi-map-marker-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // Site Visit

                            if (last_activity_type == 4){
                                ficon = '<i class="mdi mdi-file-document-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // notes

                            if (last_activity_type == 5){
                                ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // estimate

                            if (last_activity_type == 6){
                                ficon = '<i class="mdi mdi-account-plus-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // create leads

                            if (last_activity_type == 7){
                                ficon = '<i class="mdi mdi-pencil text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // edit leads

                            if (last_activity_type == 8){
                                ficon = '<i class="mdi mdi-arrow-top-right text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // assigned leads

                            if (last_activity_type == 9){
                                ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                if(row.last_is_modified==1 && row.last_is_follow_up==1){
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                let tmp_last_activity='';
                                if(last_activity){
                                    tmp_last_activity = ' - '+last_activity;
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+tmp_last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // follow up

                            if (last_activity_type == 10){
                                ficon = '<i class="mdi mdi-book-edit-outline text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated


                            if (last_activity_type == 12){
                                ficon = '<i class="mdi mdi-message-text-outline text-primary timeline-icon"></i> Message Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 13){
                                ficon = '<i class="mdi mdi-file-document-outline text-primary timeline-icon"></i> File Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 14) {
                                ficon = '<i class="mdi mdi-calendar-blank-multiple text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // remove follow up

                            if (last_activity_type == 15) {
                                ficon = '<i class="mdi mdi-calendar-blank text-secondary timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // someday follow up

                            if (last_activity_type == 16) {
                                ficon = '<i class="mdi mdi-checkbox-marked-circle-outline text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated client side

                            if (last_activity_type == 17) {
                                ficon = '<i class="mdi mdi mdi-close text-danger timeline-icon"></i>';
                                last_activity = 'Lead Lost -' + last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // lead lost Reason

                            if (last_activity_type == 18) {
                                ficon = '<i class="mdi mdi-trophy-outline text-success timeline-icon"></i>';
                                last_activity = ' Lead Won - '+ time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead won

                            if (last_activity_type == 20) {
                                ficon = '<i class="mdi mdi-call-merge text-primary timeline-icon"></i>';
                                let tmp_last_activity = '';
                                if (last_internal_remarks) {
                                    tmp_last_activity = ' - ' + last_internal_remarks;
                                }
                                last_activity = last_activity_name + tmp_last_activity + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead merge

                            // return funcStrLimits(last_activity,112,1);
                            // return funcStrLimits(last_activity, 50, 1);
                            return last_activity;
                        }
                    },

                    {data: 'label_name', name: 'label_name'},
                    {data: 'estimate_status', name: 'estimate_status', visible: true,
                        render: function (data, type, row) {
                            let tmp_status='';
                            if(row.estimate_status=='Draft'){
                                tmp_status = '<span class="text-secondary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Sent'){
                                tmp_status = '<span class="text-primary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Inprogress'){
                                tmp_status = '<span class="text-warning fw-bold">In Progress</span>';
                            }
                            if(row.estimate_status=='Accept'){
                                tmp_status = '<span class="text-success fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Decline'){
                                tmp_status = '<span class="text-danger fw-bold">'+row.estimate_status+'</span>';
                            }
                            return tmp_status;
                        }
                    },
                    {data: 'net_amount', name: 'net_amount'},

                ],
                drawCallback: function () {
                    $("#cnt_someday").text('('+table_someday.page.info().recordsTotal+')');
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);

                }
            });
            table_someday.on('click', 'tbody tr td:not(:first-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });
        }*/

        function fun_never_followup_followup(fil_lead_date_start,fil_lead_date_end){
            var table_never_followup = $("#never-followup-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                info: true,
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                    @if($t_company_id!=1408),
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf-box fs-4"></i>',
                        attr: {
                            title: 'PDF',
                            class: 'btn btn-light buttons-html5 buttons-pdf',
                        },
                        title: 'Lead List',
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
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                    @endif
                    ,{
                        extend: 'colvis',
                        text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                        },
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    /*data.fil_customer_category_id = $('#fil_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_estimate_status_id').val();
                    data.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD");
                    data.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD");*/
                    data.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id'),
                        data.fil_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                    data.fil_customer_category_id = $('#fil_followup_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_followup_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                    // data.fil_status = $('#fil_status').val();
                    /* var array = [];
                     $("input:checkbox[name='fil_status[]']:checked").each(function () {
                         if ($(this).is(":checked"))
                             array.push($(this).val());

                     });*/
                    // data.fil_status_new = array,
                    // data.fil_status = $('#fil_status').val();
                    data.fil_type = $('#fil_type').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_team_member = localStorage.getItem('fil_user_id');
                    data.fil_country_id = $('#fil_country_id').val();
                    data.fil_state_id = $('#fil_state_id').val();
                    data.fil_city_name = $('#fil_city_name').val();

                    $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_followup_created_user_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_city_name").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    if(data.fil_lead_stage_id){
                        $("#fil_followup_lead_stage_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_followup_lead_label_id){
                        $('#fil_followup_lead_label_id').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #727cf5 !important;');
                        // $("#fil_followup_lead_label_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_category_id){
                        $("#fil_followup_customer_category_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_customer_lead_id){
                        $("#fil_followup_customer_lead_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_created_user_id){
                        $("#fil_followup_created_user_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_estimate_status_id){
                        $("#fil_followup_estimate_status_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_city_name){
                        $("#fil_city_name").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if(data.fil_state_id){
                        $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                    if(data.fil_country_id){
                        $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                },
                stateLoadParams: function (settings, data) {
                    var str = localStorage.getItem('fil_followup_lead_label_id');
                    var arr =[];
                    if(str){
                        arr = $.map(str.split(","), function(value) {
                            return parseInt(value, 10);
                        });
                    }
                    $("#fil_followup_lead_label_id").val(arr).trigger('change');
                    $('#fil_followup_lead_stage_id').val(data.fil_lead_stage_id);
                    $('#fil_followup_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_followup_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_followup_created_user_id').val(data.fil_created_user_id);
                    $('#fil_followup_estimate_status_id').val(data.fil_estimate_status_id);
                    /*$.each(data.fil_status_new, function (index, value) {
                        $("body #chk_" + value).prop("checked", true);
                    });*/
                    /*$('#fil_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_created_user_id').val(data.fil_created_user_id);
                    $('#fil_estimate_status_id').val(data.fil_estimate_status_id);
                    $('#lead_date_range span').html(moment(fil_lead_date_start, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY') + ' - ' + moment(fil_lead_date_end, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY'));*/

                    $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                    $('#fil_team_member').val(localStorage.getItem('fil_user_id'));
                    $('#fil_country_id').val(data.fil_country_id);
                    $("#fil_country_id option[value='" + data.fil_country_id + "']").prop("selected", true);
                    $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                    $('#fil_state_id').val(data.fil_state_id);
                    $("#fil_state_id option[value='" + data.fil_state_id + "']").prop("selected", true);
                    $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                    $('#fil_city_name').val(data.fil_city_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    type: 'POST',
                    url: "{{ route('tenant.follow-up-history.neverFollowUpIndex', ['tenant' => $segment]) }}",
                    data: function (d) {
                        /*var array = [];
                        $("body input:checkbox[name='fil_status[]']").each(function () {
                            if ($(this).is(":checked"))
                                array.push($(this).val());
                        });*/

                        let i = 0;
                        // $(".small_labels").hide();
                        if (localStorage.getItem('fil_followup_lead_label_id')>0) {
                            // $(".small_labels").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_origin").hide();
                        if ($('#fil_followup_customer_lead_id').val()) {
                            // $(".small_origin").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_category").hide();
                        if ($('#fil_followup_customer_category_id').val()) {
                            // $(".small_category").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_created_by").hide();
                        if ($('#fil_followup_created_user_id').val()) {
                            // $(".small_created_by").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_estimate_status_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_followup_lead_stage_id').val()) {
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        let j = 0;
                        if ($('#fil_country_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_state_id').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_city_name').val()) {
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        $("#filter_count").html(i);
                        $("#advance_filter_count").html(j);

                        /*$(".small_created_date").hide();
                        if($('#fil_estimate_status_id').val()){
                            $(".small_created_date").show();
                        }*/
                        // d.status = array,
                        /*d.fil_customer_category_id = $('#fil_customer_category_id').val(),
                        d.fil_customer_lead_id = $('#fil_customer_lead_id').val(),
                        d.fil_created_user_id = $('#fil_created_user_id').val(),
                        d.fil_estimate_status_id = $('#fil_estimate_status_id').val(),
                        d.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD"),
                        d.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD")*/
                        d.fil_followup_lead_label_id = localStorage.getItem('fil_followup_lead_label_id');
                            d.fil_followup_lead_stage_id = $('#fil_followup_lead_stage_id').val();
                            d.fil_followup_customer_category_id = $('#fil_followup_customer_category_id').val();
                            d.fil_followup_customer_lead_id = $('#fil_followup_customer_lead_id').val();
                            d.fil_followup_created_user_id = $('#fil_followup_created_user_id').val();
                            d.fil_followup_estimate_status_id = $('#fil_followup_estimate_status_id').val();
                            // d.status = $('#fil_status').val();
                            d.assigned_to_user = $('#fil_team_member').val();
                            d.customer_type = $('#fil_type').val();
                            d.name = $('#fil_name').val();
                            d.search = $('#never-followup-datatable_filter input[type="search"]').val();
                            d.fil_followup_country_id = $('#fil_country_id').val();
                            d.fil_followup_state_id = $('#fil_state_id').val();
                            d.fil_followup_city_name = $('#fil_city_name').val();
                            var tmp_opr_id_3;
                            @if (request()->has('q') && request()->q == 'opr_lead_without_followup')
                                tmp_opr_id_3 = localStorage.getItem('opr_id_3');
                            @else
                                tmp_opr_id_3 = '';
                            @endif
                                d.opr_id_3= tmp_opr_id_3;
                    }
                },
                "order": [[1, "desc"]],
                "columnDefs": [{ "className": "label_td",
                    "targets": [4]
                }],

                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, visible: true,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox_never form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let country_code ='';
                            if(row.country_code){
                                country_code = row.country_code;
                            }
                            let new_lead = '';
                            if (row.new_lead_flag == 1) {
                                // new_lead = '<span class="badge bg-secondary text-light float-end blinks" style="background:#0acf97 !important;">New</span>';
                                new_lead = '<small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1" title="New Lead"></small>';
                            }
                            return '<td>' +
                                '<h5 class="font-19 mb-1 fw-bold">' + new_lead + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                '<span class="text-muted font-10">' + country_code+row.phone_no + '</span>' +
                                '</td>';
                        }
                    },
                    {
                        data: 'cv.user_name', name: 'assign_user_name',
                        render: function (data, type, row) {
                            let currnt_user_id = {{auth()->user()->id}};
                            var assign_color = 'bg-primary';
                            var assign_icon = 'mdi-account-check';
                            if (currnt_user_id != row.assigned_to_user) {
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-lock';
                            }
                            let tmp_usr_name = row.assign_user_name;
                            if(row.assigned_to_user==0){
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-off';
                                tmp_usr_name ='Unassigned';
                            }

                            if (currnt_user_id == row.assigned_to_user) {
                                tmp_usr_name = '';
                            }
                            var ss='';

                            // if(row.assigned_to_user) {
                            ss = '<td>' +
                                '<span class=" fs-6 badge ' + assign_color + '"> <i class="pe-1 mdi ' + assign_icon + '"></i>' + tmp_usr_name + '</span>' +
                                '</td>';
                            // }

                            return ss;
                        }
                    },
                    {
                        data: 'lead_stage_name', name: 'lead_stage_name',
                        render: function (data, type, row) {
                            return '<span class="fs-6 badge me-1" style="background-color:' + row.lead_stage_color_code + '">' +
                                row.lead_stage_name +
                                '</span>';
                        }

                    },
                    {data: 'last_activity', name: 'last_activity',
                        render: function (data, type, row) {
                            let ficon = '';
                            let last_activity_type = row.last_activity_type;
                            let last_activity_name = row.last_activity_name;
                            let last_activity = row.last_activity;
                            let last_internal_remarks = row.last_internal_remarks;
                            let time_ago_string = row.time_ago_string;

                            if (last_activity_type == 1){
                                ficon = '<i class="mdi mdi-phone text-success timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } //call

                            if (last_activity_type == 2) {
                                ficon = '<i class="mdi mdi-chat-outline text-warning timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            }// message

                            if (last_activity_type == 3){
                                ficon = '<i class="mdi mdi-calendar text-dark timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // meeting

                            if (last_activity_type == 19) {
                                ficon = '<i class="mdi mdi-map-marker-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // Site Visit

                            if (last_activity_type == 4){
                                ficon = '<i class="mdi mdi-file-document-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // notes

                            if (last_activity_type == 5){
                                ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                if(last_activity){
                                    last_activity = ' - '+last_activity;
                                }
                                if(last_activity==null){
                                    last_activity='';
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // estimate

                            if (last_activity_type == 6){
                                ficon = '<i class="mdi mdi-account-plus-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // create leads

                            if (last_activity_type == 7){
                                ficon = '<i class="mdi mdi-pencil text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // edit leads

                            if (last_activity_type == 8){
                                ficon = '<i class="mdi mdi-arrow-top-right text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // assigned leads

                            if (last_activity_type == 9){
                                ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                if(row.last_is_modified==1 && row.last_is_follow_up==1){
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                let tmp_last_activity='';
                                if(last_activity){
                                    tmp_last_activity = ' - '+last_activity;
                                }
                                last_activity = last_activity_name+' - '+time_ago_string+tmp_last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // follow up

                            if (last_activity_type == 10){
                                ficon = '<i class="mdi mdi-book-edit-outline text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated


                            if (last_activity_type == 12){
                                ficon = '<i class="mdi mdi-message-text-outline text-primary timeline-icon"></i> Message Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 13){
                                ficon = '<i class="mdi mdi-file-document-outline text-primary timeline-icon"></i> File Sent - ';
                                last_activity = time_ago_string+' - '+last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // message sent

                            if (last_activity_type == 14) {
                                ficon = '<i class="mdi mdi-calendar-blank-multiple text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // remove follow up

                            if (last_activity_type == 15) {
                                ficon = '<i class="mdi mdi-calendar-blank text-secondary timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // someday follow up

                            if (last_activity_type == 16) {
                                ficon = '<i class="mdi mdi-checkbox-marked-circle-outline text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks+' - '+time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // status updated client side

                            if (last_activity_type == 17) {
                                ficon = '<i class="mdi mdi mdi-close text-danger timeline-icon"></i>';
                                last_activity = 'Lead Lost -' + last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } // lead lost Reason

                            if (last_activity_type == 18) {
                                ficon = '<i class="mdi mdi-trophy-outline text-success timeline-icon"></i>';
                                last_activity = ' Lead Won - '+ time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead won

                            if (last_activity_type == 20) {
                                ficon = '<i class="mdi mdi-call-merge text-primary timeline-icon"></i>';
                                let tmp_last_activity = '';
                                if (last_internal_remarks) {
                                    tmp_last_activity = ' - ' + last_internal_remarks;
                                }
                                last_activity = last_activity_name + tmp_last_activity + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead merge

                            // return funcStrLimits(last_activity,112,1);
                            // return funcStrLimits(last_activity, 50, 1);
                            return last_activity;
                        }
                    },

                    {data: 'label_name', name: 'label_name'},
                    {data: 'estimate_status', name: 'estimate_status', visible: true,
                        render: function (data, type, row) {
                            let tmp_status='';
                            if(row.estimate_status=='Draft'){
                                tmp_status = '<span class="text-secondary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Sent'){
                                tmp_status = '<span class="text-primary fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Inprogress'){
                                tmp_status = '<span class="text-warning fw-bold">In Progress</span>';
                            }
                            if(row.estimate_status=='Accept'){
                                tmp_status = '<span class="text-success fw-bold">'+row.estimate_status+'</span>';
                            }
                            if(row.estimate_status=='Decline'){
                                tmp_status = '<span class="text-danger fw-bold">'+row.estimate_status+'</span>';
                            }
                            return tmp_status;
                        }
                    },
                    {data: 'net_amount', name: 'net_amount'},

                ],
                drawCallback: function () {
                    $("#cnt_never_followup").text('('+table_never_followup.page.info().recordsTotal+')');
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);
                }
            });
            table_never_followup.on('click', 'tbody tr td:not(:first-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });
        }


        $(document).ready(function () {
            @if (request()->has('q') && (request()->q == 'opr_overdue' || request()->q == 'opr_lead_without_followup'))
            localStorage.removeItem('overdue-datatable');
            localStorage.removeItem('never-followup-datatable');
            localStorage.removeItem('upcoming-datatable');
            localStorage.removeItem('duetoday-datatable');
            @endif
            $('#fil_state_id').select2({
                dropdownParent: $('#sel_st')
            });
            $('#fil_country_id').select2({
                dropdownParent: $('#sel_co')
            });
            $("#filter-btn").click(function(){
                $(".filter-container").slideToggle('slow');
            });
            $("#fil_followup_lead_label_id").select2().on("select2:select select2:unselect", function (e) {

                //this returns all the selected item
                var items= $(this).val();
                //Gets the last selected item
                var lastSelectedItem = e.params.data.id;


                if (!localStorage.hasOwnProperty("fil_followup_lead_label_id")) {
                    localStorage.setItem('fil_followup_lead_label_id', items);
                }

                if (localStorage.hasOwnProperty("fil_followup_lead_label_id")) {
                    localStorage.setItem('fil_followup_lead_label_id', items);
                }


               /* table.draw();
                table_upcoming.draw();
                table_overdue.draw();
                table_someday.draw();
                table_never_followup.draw();*/
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();
            })

            $('#leads_stages_id').on('change', function () {

                var selectedOption = $(this).find(":selected");

                // Get the data-id attribute value
                var dataIdValue = selectedOption.data("id");
                //alert(dataIdValue);
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
                    $('#lost_reason_others').text('');
                } else {
                    $(".lost_reason_others_div").hide();
                    $('#lost_reason_others').prop('required', false);
                    $('#lost_reason_others').text('');
                }
            });

            $('.assign-lead-stage-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('assign-lead-stage-form'));
                    formData.append('lost_reason_name', $('#lost_reason_id').find(":selected").text());
                    formData.append('lead_stage_data_id', $('#leads_stages_id').find(":selected").data('id'));
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.multiple-lead-stage', ['tenant' => $segment]) }}',
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
                            $('#assign-lead-stage-modal').modal('toggle');
                            $("#lead_stage_button").prop('disabled', false);
                            $("#lead_stage_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                           /* table.draw();
                            table_upcoming.draw();
                            table_overdue.draw();
                            table_someday.draw();
                            table_never_followup.draw();*/
                            var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                            $('#'+initialActiveTabId).DataTable().ajax.reload();
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

            $('.lead-label-form').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serializeArray();

                if ($(this).parsley().isValid()) {

                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.label-save-multiple', ['tenant' => $segment]) }}',
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
                                '<i class="mdi mdi-plus-circle-outline"></i> Save');
                            // location.reload();
                            var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                            $('#'+initialActiveTabId).DataTable().ajax.reload();
                            $('input[name="selected_lead_id[]"]').prop('checked', false);
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
                                '<i class="mdi mdi-plus-circle-outline"></i> Save');
                        },
                        complete: function (data) {
                            $("#lead_label_button").html(
                                '<i class="mdi mdi-plus-circle-outline"></i> Save');
                            $("#lead_label_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.assign-lead-form').on('submit', function (e) {
                e.preventDefault();
                // console.log(new FormData(this)); return false;
                if ($(this).parsley().isValid()) {
                    $.ajax({
// async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.multiple-lead-assigned-to-user', ['tenant' => $segment]) }}',
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
            $('#theme-settings-offcanvas').on('shown.bs.offcanvas', function () {

                // $('body').attr('offcanvas-open');
                // var backdropElements = document.querySelectorAll('.offcanvas-backdrop');
                var backdropElements = $('.offcanvas-backdrop');
                // alert(backdropElements.length);
                if (backdropElements.length > 2) {
                    backdropElements[0].parentNode.removeChild(backdropElements[0]);
                    backdropElements[1].parentNode.removeChild(backdropElements[1]);
                    // $('#theme-settings-offcanvas').css('visibility', 'visible');
                }
                $('#theme-settings-offcanvas').css('visibility', 'visible');
            });
            $('#theme-settings-offcanvas').on('hidden.bs.offcanvas', function () {
                $('body').removeClass('offcanvas-open');
            });
            //filter Start
            var fil_lead_date_start = moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days');
            var fil_lead_date_end = moment();

            if (localStorage.hasOwnProperty("fil_lead_date_start")) {
                fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start'));
            } else {
                localStorage.setItem('fil_lead_date_start', fil_lead_date_start);
            }

            if (localStorage.hasOwnProperty("fil_lead_date_end")) {
                fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end'));
            } else {
                localStorage.setItem('fil_lead_date_end', fil_lead_date_end);
            }

            function cbt(fil_lead_date_start, fil_lead_date_end) {
                $('#lead_date_range span').html(fil_lead_date_start.format('MMMM D, YYYY') + ' - ' + fil_lead_date_end.format('MMMM D, YYYY'));
                let date_range = fil_lead_date_start.format('YYYY-MM-DD') + '_' + fil_lead_date_end.format('YYYY-MM-DD');
                localStorage.setItem('fil_lead_date_start', moment(fil_lead_date_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                localStorage.setItem('fil_lead_date_end', moment(fil_lead_date_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                // let fil_sp_user_id = localStorage.getItem('fil_user_id')
                // table.draw();
            }

            $('#lead_date_range').daterangepicker({
                /*startDate: fil_lead_date_start,
                endDate: fil_lead_date_end,*/
// "drops": "up",
                parentEl: "#theme-settings-offcanvas .xxx",
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days'), moment()],
                }
            }, cbt);
            cbt(fil_lead_date_start, fil_lead_date_end);


            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e){
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
           /* $(".duetoday-tbody, .upcoming-tbody, .overdue-tbody, .nofollowup-tbody, .someday-tbody").on('click', 'tr', function () {
                var id = $(this).attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });*/

            var fil_user_id = {{auth()->user()->id}};
            if (localStorage.hasOwnProperty("fil_user_id")) {
                fil_user_id = localStorage.getItem('fil_user_id');
                $('#fil_team_member').val(fil_user_id);
            } else {
                localStorage.setItem('fil_user_id', fil_user_id);

            }
            $('#fil_team_member').val(fil_user_id);
            "use strict";

            @if (!request()->has('q'))
            fun_today_followup();
            @endif
            @if (request()->has('q') && request()->q == 'opr_overdue')
            $('a[data-bs-toggle="tab"]').removeClass('active');
            $("a[data-id='overdue-datatable']").addClass('active');
            $('.tab-pane').removeClass('active');
            $("#overdue-tab").addClass("active");
            $("a[data-id='overdue-datatable']").click();
            // Destroy and reinitialize the DataTable for 'overdue-datatable' tab
            $('#overdue-datatable').DataTable().destroy();
            fun_overdue_followup(fil_lead_date_start, fil_lead_date_end);
            @endif
            @if (request()->has('q') && request()->q == 'opr_lead_without_followup')
            $('a[data-bs-toggle="tab"]').removeClass('active');
            $("a[data-id='never-followup-datatable']").addClass('active');
            $('.tab-pane').removeClass('active');
            $("#nofollowup-tab").addClass("active");
            $("a[data-id='never-followup-datatable']").click();
            // Destroy and reinitialize the DataTable for 'overdue-datatable' tab
            $('#never-followup-datatable').DataTable().destroy();
            fun_never_followup_followup(fil_lead_date_start, fil_lead_date_end);
            @endif
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Get the ID of the active tab
                var activeTabId = $(e.target).attr('data-id');
                switch (activeTabId) {
                    case 'duetoday-datatable':
                        $('#duetoday-datatable').DataTable().destroy();
                        fun_today_followup();
                        break;
                    case 'upcoming-datatable':
                        $('#upcoming-datatable').DataTable().destroy();
                        fun_upcomming_followup(fil_lead_date_start, fil_lead_date_end);
                        break;
                    case 'overdue-datatable':
                        $('#overdue-datatable').DataTable().destroy();
                        fun_overdue_followup(fil_lead_date_start, fil_lead_date_end);
                        break;
                    case 'someday-datatable':
                        $('#someday-datatable').DataTable().destroy();
                        fun_someday_followup(fil_lead_date_start, fil_lead_date_end);
                        break;
                    case 'never-followup-datatable':
                        $('#never-followup-datatable').DataTable().destroy();
                        fun_never_followup_followup(fil_lead_date_start, fil_lead_date_end);
                        break;
                    // Add more cases if needed

                    // Default case
                    default:
                        // Handle default case if necessary
                        break;
                }
            });
            /*fun_upcomming_followup(fil_lead_date_start,fil_lead_date_end);
            fun_overdue_followup(fil_lead_date_start,fil_lead_date_end);
            fun_someday_followup(fil_lead_date_start,fil_lead_date_end);
            fun_never_followup_followup(fil_lead_date_start,fil_lead_date_end);*/







            $(document).on('click', '#reset-layout', function () {
                /*$('body #lead-table input:checkbox').prop('checked', false);
                $("#fil_customer_category_id").val('');
                $("#fil_customer_lead_id").val('');
                $("#fil_created_user_id").val('');
                $("#fil_estimate_status_id").val('');*/
               /* localStorage.removeItem("overdue-datatable");
                localStorage.removeItem("upcoming-datatable");*/

                var fil_lead_date_start = moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days');
                var fil_lead_date_end = moment();
                $('#lead_date_range span').html(fil_lead_date_start.format('MMMM D, YYYY') + ' - ' + fil_lead_date_end.format('MMMM D, YYYY'));

                localStorage.setItem('fil_lead_date_start', moment(fil_lead_date_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                localStorage.setItem('fil_lead_date_end', moment(fil_lead_date_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                location.reload();
            });

            $(document).on('click', '#offcanvas-btn', function () {
                let closeCanvas = document.querySelector('[data-bs-dismiss="offcanvas"]');

                if (typeof (Storage) !== "undefined") {
                    // Retrieve the existing data from localStorage
                    var data = localStorage.getItem('overdue-datatable');
                    var dataA = localStorage.getItem('upcoming-datatable');

                    // Parse the data from string to object
                    var parsedData = JSON.parse(data);
                    var parsedDataA = JSON.parse(dataA);

                    parsedData.fil_lead_date_start = localStorage.getItem('fil_lead_date_start');
                    parsedData.fil_lead_date_end = localStorage.getItem('fil_lead_date_end');

                    parsedDataA.fil_lead_date_start = localStorage.getItem('fil_lead_date_start');
                    parsedDataA.fil_lead_date_end = localStorage.getItem('fil_lead_date_end');

                    var updatedData = JSON.stringify(parsedData);
                    var updatedDataA = JSON.stringify(parsedDataA);


                    // Store the updated data back in localStorage
                    localStorage.setItem('overdue-datatable', updatedData);
                    localStorage.setItem('upcoming-datatable', updatedDataA);

                    // Confirmation message
                    console.log('Value updated successfully!');
                } else {
                    console.log('Browser does not support localStorage');
                }
                // $("#customer-datatable").DataTable().ajax.reload();
               /* table_upcoming.draw();
                table_overdue.draw();*/
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();

                closeCanvas.click();
            });

            $('#fil_followup_lead_stage_id,#fil_followup_customer_category_id,#fil_followup_customer_lead_id,#fil_followup_created_user_id,#fil_followup_estimate_status_id').change(function () {
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();
                /*table.draw();
                table_upcoming.draw();
                table_overdue.draw();
                table_someday.draw();
                table_never_followup.draw();*/
            });
            // $('#fil_status,#fil_team_member').change(function () {
            $('#fil_team_member').change(function () {
                localStorage.setItem('fil_user_id', $('#fil_team_member').val());
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();
                /*table.draw();
                table_upcoming.draw();
                table_overdue.draw();
                table_someday.draw();
                table_never_followup.draw();*/

                if (typeof(Storage) !== "undefined") {
                    // Retrieve the existing data from localStorage
                    var data = localStorage.getItem('duetoday-datatable-dashboard'); // Replace 'your_key' with the actual key name
                    var dataA = localStorage.getItem('customer-datatable'); // Replace 'your_key' with the actual key name
                    var dataB = localStorage.getItem('estimate-datatable'); // Replace 'your_key' with the actual key name

                    // Parse the data from string to object
                    var parsedData = JSON.parse(data);
                    var parsedDataA = JSON.parse(dataA);
                    var parsedDataB = JSON.parse(dataB);

                    // Update the value of "fil_team_member"
                    parsedData.fil_team_member = $('#fil_team_member').val();
                    parsedDataA.fil_team_member = $('#fil_team_member').val();
                    parsedDataB.fil_team_member = $('#fil_team_member').val();

                  /*  parsedData.fil_status = $('#fil_status').val();
                    parsedDataA.fil_status = $('#fil_status').val();
                    parsedDataB.fil_status = $('#fil_status').val();*/

                    // Convert the updated object back to string
                    var updatedData = JSON.stringify(parsedData);
                    var updatedDataA = JSON.stringify(parsedDataA);
                    var updatedDataB = JSON.stringify(parsedDataB);

                    // Store the updated data back in localStorage
                    localStorage.setItem('duetoday-datatable-dashboard', updatedData); // Replace 'your_key' with the actual key name
                    localStorage.setItem('customer-datatable', updatedDataA); // Replace 'your_key' with the actual key name
                    localStorage.setItem('estimate-datatable', updatedDataB); // Replace 'your_key' with the actual key name

                    // Confirmation message
                    console.log('Value updated successfully!');
                } else {
                    console.log('Browser does not support localStorage');
                }


            });
            /*table.buttons().container().appendTo("#duetoday-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            })

            table_upcoming.buttons().container().appendTo("#upcoming-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            })

            table_overdue.buttons().container().appendTo("#overdue-datatable_wrapper .col-md-6:eq(0)"), $("#overdue-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })

            table_someday.buttons().container().appendTo("#someday-datatable_wrapper .col-md-6:eq(0)"), $("#someday-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })

            table_never_followup.buttons().container().appendTo("#never-followup-datatable_wrapper .col-md-6:eq(0)"), $("#never-followup-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })*/

            $('.today-column-form').on('submit', function (e) {
                e.preventDefault();

                var formData = $('#today-column-form').serializeArray();

                var all_column = [1, 2, 3, 4,5, 6, 7,8];

                var remove_column = formData
                    .filter(obj => obj.name === "manage_column[]") // Filter objects with name "manage_column[]"
                    .map(obj => parseInt(obj.value));

                var remaining_column = all_column.filter(function(obj) { return remove_column.indexOf(obj) == -1; });

                $('#duetoday-datatable').DataTable().columns(remaining_column).visible(false);

                $('#duetoday-datatable').DataTable().columns(remove_column).visible(true);

                $('#today-column-modal').modal('hide');

            });

            $(document).on('click', '#filter_button', function () {
                /*table.draw();
                table_upcoming.draw();
                table_overdue.draw();
                table_someday.draw();
                table_never_followup.draw();*/
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();
                $('#advance-filter-modal').modal('toggle');
            });

            //Remove multiple record
            $('.delete_all').on('click', function (e) {
                var allVals = [];
                $(".single_checkbox_today:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });

                $(".single_checkbox_upcoming:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });

                $(".single_checkbox_overdue:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });

                $(".single_checkbox_someday:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });

                $(".single_checkbox_never:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });
                var join_selected_values = allVals.join(",");
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                remove_id_followup(join_selected_values, '{{route('tenant.customer.delete', ['tenant' => $segment])}}', '#'+initialActiveTabId);
            });

            $(document).on('click', '#filter_reset_button', function () {
                $('#fil_followup_lead_stage_id').val('');
                $('#fil_followup_lead_label_id').val('');
                $('#fil_followup_customer_category_id').val('');
                $('#fil_followup_customer_lead_id').val('');
                $('#fil_followup_created_user_id').val('');
                $('#fil_followup_estimate_status_id').val('');
                /*$('#lead_date_range span').html(moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
                localStorage.setItem('fil_lead_date_start', moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('YYYY-MM-DD'));
                localStorage.setItem('fil_lead_date_end', moment().format('YYYY-MM-DD'));*/
                localStorage.setItem('fil_followup_lead_label_id', '');
                $("#fil_followup_lead_label_id").val('').trigger('change');
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();
                /*table.draw();
                table_upcoming.draw();
                table_overdue.draw();
                table_someday.draw();
                table_never_followup.draw();*/
            });

            $(document).on('click', '#advance_filter_reset_button', function () {

                $('#fil_country_id').val('');
                $("#fil_country_id option[value='']").prop("selected", true);
                $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                $('#fil_state_id').val('');
                $("#fil_state_id option[value='']").prop("selected", true);
                $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                $('#fil_city_name').val('');
                /*table.draw();
                table_upcoming.draw();
                table_overdue.draw();
                table_someday.draw();
                table_never_followup.draw();*/
                var initialActiveTabId = $('.tab-pane.active').attr('data-id');
                $('#'+initialActiveTabId).DataTable().ajax.reload();
                $('#advance-filter-modal').modal('toggle');
                // $('#advance-filter-modal').modal('toggle');
            });
        });

        function remove_id_followup(id, url, tableName = '') {
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
                            url: url,
                            data: { id: id },
                            dataType: "json",
                            success: function (data, textStatus, jqXHR) {
                                if (tableName) {
                                    $(tableName).DataTable().ajax.reload();
                                    $("#remove_" + id).closest("tr").hide('slow');
                                    $('#select_all').prop('checked', false);
                                    //$("#select_count").html(0);
                                    // toastrSuccess('Successfully removed');
                                }

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
                                        if (tableName) {
                                            $(tableName).DataTable().ajax.reload();
                                        }
                                        toastrErrorWithText(xhr.responseJSON, 'Warning');
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
                        type: "success", showConfirmButton: !1,
                        timer: 1500,
                        confirmButtonClass: "btn btn-success",
                        showConfirmButton: false
                    })
                });
            }
        }

       /* $(document).ready(function() {
            // document is loaded and DOM is ready
            alert("document is ready");
        });

        $(window).on('load', function() {
            // page is fully loaded, including all frames, objects, and images
            alert("Window is loaded");
        });*/

    </script>
    {{--<style>
        .dataTables_scrollHeadInner,.dataTables_scrollBody {
            width: 100% !important;
        }
    </style>--}}
    <style>
        .select2-search__field{
            height:26px !important;
        }
        .select2-container--default .select2-selection--single {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 37px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 37px !important;
    </style>
@endpush
