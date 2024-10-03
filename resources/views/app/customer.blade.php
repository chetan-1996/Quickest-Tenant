@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
$t_company_id = (auth()->user()->company_id==null)? auth()->user()->id:auth()->user()->company_id;
@endphp
@extends('app.layouts.app')
@section('title','Lead')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.css"
          type="text/css">
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
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

        /* .input-group > .select2-container--bootstrap {
             width: auto;
             flex: 1 1 auto;
         }

         .input-group > .select2-container--bootstrap .select2-selection--single {
             height: 100%;
             line-height: inherit;
             padding: 0.5rem 1rem;
         }

         .select2-container--default .select2-selection--single {
             !*height: 46px !important;*!
             !*padding: 10px 16px;
             font-size: 18px;
             line-height: 1.33;
             border-radius: 6px;
             bg-light text-dark*!
         }
         .select2-container--default .select2-selection--single .select2-selection__arrow b {
             top: 85% !important;
         }
         .select2-container--default .select2-selection--single .select2-selection__rendered {
             line-height: 26px !important;
         }
         .select2-container--default .select2-selection--single {
             !*border: 1px solid #CCC !important;*!
             box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.075) inset;
             transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
         }*/
        #customer-modal .select2 .select2-container .select2-container--default .select2-container--above .select2-container--focus {
            height: 59px !important;
        }

        #customer-modal .select2-container .select2-selection--single .select2-selection__rendered {
            height: 56px !important;
            line-height: 76px !important;
            padding-left: 12px;
            /*color: var(--ct-input-color);*/
            background-color: #eef2f7;
        }

        #customer-modal .select2-container .select2-selection--single .select2-selection__arrow {
            height: 58px !important;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #customer-modal .select2-container .select2-selection--single {
            height: 58px !important;
            border: 1px solid var(--ct-input-border-color);
            height: calc(1.5em + 0.9rem + 2px);
            background-color: var(--ct-input-bg);
            outline: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #customer-modal .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: 4px;
        }

        #customer-modal .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #727cf5 !important;
            color: #fff !important;
        }

        .offcanvas-backdrop.show {
            opacity: 0.3 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #555 !important;
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
                            {{-- <div class="dropdown btn-group mb-2">
                                <div class="category-filter">
                                    <select id="fil_status" name="fil_status" class="form-select form-select-sm">
                                        <option value="">Label All</option>
                                        @foreach($leadGroups as $leadGroup)
                                            <option value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>--}}
                            {{--                    @if(in_array('add-customer', $user_perm) || auth()->user()->company_id==null)--}}
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2" title="Add Lead"
                            onclick="openModalCustomer('#customer-modal','Create Lead','#customer-form','.modal-title',id=0,flag=3)"><i
                                    class="mdi mdi-plus-thick"></i></a>

                            <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2 open_lead_modal"
                            title="Import Lead"
                            onclick="openModal('#customer-import-modal','Import Lead','#customer-import-form','.modal-title',id=0,flag=3)"><i
                                    class="mdi mdi-file-import"></i></a>
                            {{--                    @endif--}}

                            @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('give-access-to-delete-leads', $user_perm))
                                <div class="dropdown btn-group mb-2">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            title="Bulk Action">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                id="select_count" style="display:none;">0</span><i
                                            class="mdi mdi-format-list-bulleted"></i>
                                        {{--                                                                <span class="badge badge-success-lighten" id="select_count">0</span>--}}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-animated">
                                        {{--<a href="javascript:void(0);" class="dropdown-item active_status_all"><i
                                                class="mdi mdi-update"></i> Active All</a>
                                        <a href="javascript:void(0);" class="dropdown-item deactive_status_all"><i
                                                class="mdi mdi-update"></i> Deactive All</a>--}}
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

                                        @if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm) ||
                                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) )
                                            <a href="{{ route('tenant.leads.export-index', ['tenant' => $segment]) }}"
                                            class="dropdown-item"><i class="mdi mdi-cloud-download-outline"></i> Export Leads</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <button class="btn btn-primary btn-sm mb-2 position-relative" title="Filter" id="filter-btn"><span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    id="filter_count">0</span>
                                <i class="mdi mdi-filter-outline"></i>
                            </button>
                            {{--<button data-bs-toggle="offcanvas" data-bs-toggle="offcanvas"
                                    data-bs-target="#theme-settings-offcanvas" class="btn btn-primary btn-sm mb-2" title="Filter"><span
                                    class="position-absolute translate-middle badge rounded-pill bg-danger"
                                    id="filter_count" style="left: 99.50% !important;top: 61px !important;">0</span>
                                <i class="mdi mdi-filter-outline"></i>
                            </button>--}}
                        </div>
                        <div class="page-title-left pt-2">
                            {{-- <select class="form-select bg-light text-dark" id="fil_lead_stage_id"
                                    name="fil_lead_stage_id" required
                                    style="width: 250px;background-color: #fff0 !important;border: 0px solid #fff !important;font-size: 18px;margin: 0;white-space: nowrap;font-weight: 700;padding: 0.0rem 0.0rem 0rem 0.5rem;">
                                <option value="">Choose</option>
                                @foreach($leadStages as $leadStage)
                                    <option
                                        value="{{$leadStage->id}}">{{$leadStage->name}}</option>
                                @endforeach
                            </select>--}}
                            <h4 class="page-title fs-4 d-nones">Leads (<span id="cnt_lead">0</span>)</h4>
                            {{--<select class="form-select" id="fil_status" name="fil_status"
                                    style="width: 200px;background-color: #fff0 !important;border: 0px solid #fff !important;font-size: 18px;margin: 0;white-space: nowrap;font-weight: 700;padding: 0.0rem 0.0rem 0rem 0.5rem;">
                                <option value="">All Leads</option>
                                <option value="0">Active Leads</option>
                                <option value="1">Deactive Leads</option>
                            </select>--}}
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            {{--    <div class="row">--}}
            {{--        <div class="col-12">--}}
            <div class="card filter-container" style="display:none;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-3">
                                    <div class="align-items-center">
                                        <label for="fil_lead_stage" class="text-dark fw-bold me-2">Stage</label>
                                        <select class="form-select" id="fil_lead_stage_id" name="fil_lead_stage_id">
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
                                        <select class="form-select" id="fil_status" name="fil_status[]" multiple>
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

                                <div class="col-3">
                                    <div class="align-items-center">
                                        <label for="fil_lead_orgin" class="text-dark fw-bold me-2">Source</label>
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

                                <div class="col-3">
                                    <div class="align-items-center">
                                        <label for="fil_created_user_id" class="text-dark fw-bold me-2">Created By</label>
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

                                <div class="col-3">
                                    <div class="align-items-center">
                                        <label for="fil_estimate_status_id" class="text-dark fw-bold me-2">Estimate
                                            Status</label>
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

                                <div class="col-3">
                                    <div class="align-items-center">
                                        <label for="fil_created_user_id" class="text-dark fw-bold me-2">Lead Created
                                            Date</label>
                                        <div
                                            class="d-flex justify-content-between xxx align-items-center text-primary">
                                            <div id="lead_date_range" class="form-control text-primary"
                                                data-toggle="date-picker-range"
                                                data-target-display="#selectedValue" data-cancel-class="btn-light"
                                                style="max-width:100%;">
                                                <i class="mdi mdi-calendar"></i>&nbsp;
                                                <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-3">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="align-items-center">
                                                <label for="fil_lead_dtage" class="text-dark fw-bold me-2">Advance
                                                    Filter</label><br>
                                                <button class="btn btn-light" type="button"
                                                        onclick="openFilterModal('#advance-filter-modal')"><i
                                                        class="mdi mdi-format-list-bulleted"></i> Slice and dice your data <span
                                                        class="position-absolutes translate-middles badge rounded-pill bg-danger"
                                                        id="advance_filter_count"
                                                        style="/*left: 87.4% !important;top: 106px !important;*/">0</span>
                                                </button>

                                                <button type="submit" class="btn btn-light fullscreen ms-2"
                                                        id="filter_reset_button">
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
            {{--        </div>--}}
            {{--    </div>--}}

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- <div class="row mb-2">
                                <div class="col-xl-7">
                                    <div
                                        class=" row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                                        <div class="col-auto">
                                            <div class="d-flex align-items-center">
                                                <label for="fil_name" class="visually-hidden">Name</label>
                                                <input type="text" class="form-control" id="fil_name" name="fil_name"
                                                        placeholder="name...">
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="d-flex align-items-center">
                                                <label for="fil_type" class="me-2">Type</label>
                                                <select class="form-select" id="fil_type" name="fil_type">
                                                    <option value="">Choose...</option>
                                                    <option>Business</option>
                                                    <option>Individual</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="d-flex align-items-center">
                                                <label for="fil_status" class="me-2">Status</label>
                                                <select class="form-select" id="fil_status" name="fil_status">
                                                    <option value="">Choose...</option>
                                                    <option value="0">Active</option>
                                                    <option value="1">Deactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-xl-5">
                                    <div class="text-xl-end mt-xl-0 mt-2">
                                        <button type="button" class="btn btn-secondary waves-effect waves-light mr-1 mb-2"
                                                id="resetFilter">
                                            <i class="mdi mdi-filter"></i> Reset Filters
                                        </button>
                                        <a href="javascript:void(0);" class="btn btn-info mb-2"
                                            onclick="openModal('#customer-modal','Create Lead','#customer-form','.modal-title',id=0,flag=3)"><i
                                                class="mdi mdi-plus-circle"></i> Add Lead</a>

                                        <div class="dropdown btn-group mb-2">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                id="select_count">0</span>Bulk Action
                                                --}}{{--                                    <span class="badge badge-success-lighten" id="select_count">0</span> --}}{{--
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-animated">
                                                <a href="javascript:void(0);" class="dropdown-item active_status_all"><i
                                                        class="mdi mdi-update"></i> Active All</a>
                                                <a href="javascript:void(0);" class="dropdown-item deactive_status_all"><i
                                                        class="mdi mdi-update"></i> Deactive All</a>
                                                <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                                        class="mdi mdi-delete-circle"></i> Delete All</a>
                                            </div>
                                        </div>

                                    </div>
                                </div><!-- end col-->
                            </div> <!-- end row -->--}}
                            @if (request()->has('q') && (request()->q == 'opr_new_leads' || request()->q == 'opr_new_lead' || request()->q == 'opr_call' || request()->q == 'opr_message' || request()->q == 'opr_lead_won' || request()->q == 'opr_lead_lost' || request()->q == 'opr_meeting' || request()->q == 'opr_followup_completed'))
                                <div class="row">
                                    <div class="col-5"></div>
                                    <div class="col-6">
                                        <div class="cl-btn-grp icon-btngrp advance-search mb-2 d-flex align-items-center">
                                            <span class="filtered-msg">OPR filter applied</span>
                                            <a class="btn btn-default btn-sm" tabindex="0" aria-controls="accounts" href="#"
                                            title="Clear OPR Search" onclick="clearOPRSearch()">
                                                <span><i class="uil uil-search-minus"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <table id="customer-datatable" class="table table-centered table-hover table-sm nowrap w-100">
                                <thead class="table-light">
                                <tr>
                                    <th id="th-a" class="th-a"><input type="checkbox" class="form-check-input" id="select_all">
                                    </th>
                                    <th id="th-b" class="th-b">Name</th>
                                    <th id="th-b" class="th-b">Company</th>
                                    <th id="th-b" class="th-b">Mobile</th>
                                    <th id="th-" class="th-r">Stage</th>
                                    <th id="th-c" class="th-c">Assigned to</th>
                                    <th id="th-d" class="th-d">Labels</th>
                                    <th id="th-e" class="th-e">Last Activity</th>
                                    {{--                            <th>Phone</th>--}}
                                    <th id="th-f" class="th-f">Source</th>
                                    <th id="th-g" class="th-g">Category</th>
                                    <th id="th-h" class="th-h">Amount</th>
                                    <th>Estimate No</th>
                                    <th id="th-i" class="th-i">Estimate Status</th>
                                    <th id="th-j" class="th-j">Date Added</th>
                                    <th id="th-k" class="th-k">Type</th>
                                    <th id="th-l" class="th-l">Email</th>
                                    <th id="th-m" class="th-m">Address</th>
                                    <th id="th-n" class="th-n">Pincode</th>
                                    <th id="th-o" class="th-o">Country</th>
                                    <th id="th-p" class="th-p">State</th>
                                    <th id="th-q" class="th-q">City</th>
                                    {{--<th id="th-r" class="th-r">Description</th>
                                    <th id="th-s" class="th-s">Status</th>
                                    <th id="th-t" class="th-t">Action</th>--}}
                                </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->

            <!-- Modal -->
            <div id="customer-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header border-1 bg-light">
                            <h3 class="modal-title text-dark">Create Lead</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3 p-0">
                            <form class="customer-form" id="customer-form" action="#">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-floating mb-3">
                                            <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>
                                            <div class="form-check form-check-inline">
                                                <input class="form-control" type="hidden" id="id" name="id" value="0">
                                                <input type="radio" id="customer_type_business" name="customer_type"
                                                    class="form-check-input" value="Business">
                                                <label class="form-check-label"
                                                    for="customer_type_business">Business</label>
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
                                                name="company_name"
                                                placeholder="Company name">
                                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-2">
                                                    <input type="text" class="form-control bg-light text-dark" id="name"
                                                        name="name"
                                                        required=""
                                                        placeholder="Name">
                                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 ps-1 pe-1">
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
                                                                        <option class="text-left"
                                                                                value="{{$country->phonecode}}"
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
                                                                <input type="text" class="form-control bg-light text-dark"
                                                                    id="phone_no"
                                                                    name="phone_no" required=""
                                                                    placeholder="Phone no" data-parsley-type="digits"
                                                                    data-parsley-errors-container="#mobileError" style="border-top-left-radius: 0;
                    border-bottom-left-radius: 0; !important;"
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
                                                                <div class="form-floating input-group-append"
                                                                    style="width: 100%;" id="sel_wcc">
                                                                    <select
                                                                        class="text-left input-group form-select bg-light text-dark select2"
                                                                        id="whatsapp_country_code"
                                                                        name="whatsapp_country_code"
                                                                        required="" data-toggle="select2">
                                                                        @php
                                                                            $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                                        @endphp

                                                                        @foreach($countries as $country)
                                                                            <option class="text-left"
                                                                                    value="{{$country->phonecode}}"
                                                                                    data-id="{{$country->id}}"
                                                                                    @if($country->id==$expData->country_id) selected @endif>
                                                                                +{{$country->phonecode}} {{$country->sortname}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <label for="whatsapp_country_code" class="form-label">Code
                                                                        <span class="text-danger">*</span></label>
                                                                </div>
                                                            </td>
                                                            <td style="padding: 0px;">
                                                                <div class="form-floating input-group-append"
                                                                    style="width: 100%;">
                                                                    <input type="text" class="form-control bg-light text-dark"
                                                                        id="whatsapp_no"
                                                                        name="whatsapp_no"
                                                                        placeholder="Whatsapp no" data-parsley-type="digits"
                                                                        data-parsley-errors-container="#whatsappNoError"
                                                                        style="border-top-left-radius: 0;
                    border-bottom-left-radius: 0; !important;"
                                                                        {{--data-parsley-minlength="10"
                                                                        data-parsley-maxlength="15"--}}
                                                                    >
                                                                    <label for="whatsapp_no" class="form-label">Whatsapp no
                                                                        <span class="text-danger"></span></label>
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        </tbody>
                                                    </table>
                                                    <span id="whatsappNoError" style="background-color:blue;"></span>
                                                </div>
                                            </div>

                                            <div class="col-6 d-none">
                                                <div class="form-floating input-group-append mb-2"
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
                                                                {{$country->currency_code}}
                                                                - {{$country->currency_name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="currency_name" class="form-label">Currency</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-2">
                                                    <select class="form-select bg-light text-dark" id="lead_stage_id"
                                                            name="lead_stage_id" required>
                                                        {{--                                                <option value="">Choose</option>--}}
                                                        @foreach($leadStages as $leadStage)
                                                            <option
                                                                value="{{$leadStage->id}}" {{($leadStage->is_default==1)?'selected':''}}>{{$leadStage->name}}</option>
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
                                                            <select class="form-select bg-light text-dark"
                                                                    id="customer_category_id" name="customer_category_id">
                                                                <option value=0>Choose</option>
                                                                @foreach($customerCategories as $customerCategory)
                                                                    <option
                                                                        value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="customer_category_id" class="form-label">Lead
                                                                Category</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-2">
                                                            <select class="form-select bg-light text-dark" id="customer_lead_id"
                                                                    name="customer_lead_id">
                                                                <option value=0>Choose</option>
                                                                @foreach($customerLeads as $customerLead)
                                                                    <option
                                                                        value="{{$customerLead->id}}">{{$customerLead->name}}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="customer_lead_id" class="form-label">Lead Source</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-floating">
                                            <textarea class="form-control bg-light text-dark" id="description"
                                                    name="description"
                                                    placeholder="Enter description" style="height: 80px;"></textarea>
                                                    <label for="description" class="form-label">Description</label>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="text-capitalize">Advance Options <a href="javascript: void(0);"
                                                                                    class="advance-option text-primary"
                                                                                    style="text-transform: initial !important;">Click
                                                to show </a></h5>
                                        {{--                                <span class="form-text text-dark text-uppercase mb-1 mt-1" style="font-weight: bold !important;font-size: 1rem !important;"></span>--}}
                                    </div>


                                </div>

                                <div class="row advance-option-div d-none">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="email" id="email" name="email"
                                                placeholder="Enter email">
                                            <label for="email" class="form-label">Email</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control bg-light text-dark" id="address" name="address"
                                                    placeholder="Enter address" style="height: 100px;"></textarea>
                                            <label for="address" class="form-label">Address</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input class="form-control bg-light text-dark" type="text" id="pincode"
                                                        name="pincode"
                                                        placeholder="Enter pincode">
                                                    <label for="pincode" class="form-label">Pincode</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select bg-light text-dark" id="country_id"
                                                            name="country_id"
                                                            required="">
                                                        <option value="">Choose</option>
                                                        @php
                                                            $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                        @endphp

                                                        @foreach($countries as $country)
                                                            <option value="{{$country->id}}" data-id="{{$country->phonecode}}"
                                                                    @if($country->id==$expData->country_id) selected @endif>{{$country->name}}</option>
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
                                                    <select class="form-select bg-light text-dark" id="state_id" name="state_id"
                                                    >
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
                                                        name="city_name"
                                                        placeholder="Enter city">
                                                    <label for="city_name" class="form-label">City</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="text" id="gst_no" name="gst_no"
                                                placeholder="Enter gstin">
                                            <label for="gst_no" class="form-label">GSTIN (TAX No.)</label>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                </button>
                                <button class="btn btn-primary customer_button" form="customer-form" id="customer_button"
                                        type="submit" value="0">
                                    <i class="mdi mdi-floppy fs-5"></i> Save
                                </button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <!-- Modal -->
            <div id="customer-import-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-full-width modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-1 bg-light">
                            <h3 class="modal-title text-dark">Import Lead</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3 p-0">
                            <div class="row">
                                <div class="col-md-3">
                                    <form class="customer-import-form" id="customer-import-form" action="#">
                                        <div class="row import_error_meesage d-none">
                                            <div class="col-md-12 col-md-offset-1">
                                                <div class="alert alert-danger alert-dismissible">
                                                    <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                                    <div class="import_excel_error"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <a href="{{Storage::url('document/import_sample_file.xlsx');}}"
                                                id="excel_download" download> Download Sample File</a>
                                                <div class="form-floating">
                                                    <div class="form-group">
                                                        <!-- <div class="custom-file text-left">
                                                            <input type="file" name="file" class="custom-file-input" id="customFile">
                                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                                        </div> -->
                                                        <input type="file" class="form-control image_one"
                                                            data-parsley-trigger="change" name="file" id="customFile"
                                                            data-parsley-required="false"
                                                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class='text-danger'>You can import maximum 200 lead at a time.</div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-3" style="margin-top: 1.2rem !important;">
                                    <button class="btn btn-primary" form="customer-import-form" id="customer_import_button"
                                            data-bs-toggle="tooltip" data-bs-html="true" title="Preview and validate your excel"
                                            type="submit">
                                        <i class="mdi mdi-floppy fs-5"></i> Preview
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="excel_preview"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="text-end">
                                <a href="#" id="download_error_report" download data-bs-toggle="tooltip" data-bs-html="true"
                                title="Download your error data excel file."> Download error report</a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                </button>
                                <button class="btn btn-primary" id="customer_import__final_button" type="button">
                                    <i class="mdi mdi-floppy fs-5"></i> import
                                </button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            {{--    <div id="customer-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">--}}
            {{--        <div class="modal-dialog modal-lg modal-center">--}}
            {{--            <div class="modal-content">--}}
            {{--                <div class="modal-header border-1 bg-light">--}}
            {{--                    <h4 class="modal-title">Create Lead</h4>--}}
            {{--                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
            {{--                </div>--}}
            {{--                <div class="modal-body">--}}
            {{--                    <form class="ps-3 pe-3 customer-form" id="customer-form" action="#">--}}
            {{--                        <div class="row">--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>--}}
            {{--                                    <div class="form-check form-check-inline">--}}
            {{--                                        <input type="radio" id="customer_type_business" name="customer_type"--}}
            {{--                                               class="form-check-input" value="Business">--}}
            {{--                                        <label class="form-check-label"--}}
            {{--                                               for="customer_type_business">Business</label>--}}
            {{--                                    </div>--}}
            {{--                                    <div class="form-check form-check-inline">--}}
            {{--                                        <input type="radio" id="customer_type_individual" name="customer_type"--}}
            {{--                                               class="form-check-input" value="Individual" checked>--}}
            {{--                                        <label class="form-check-label"--}}
            {{--                                               for="customer_type_individual">Individual</label>--}}
            {{--                                    </div>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="name" class="form-label">Name <span--}}
            {{--                                            class="text-danger">*</span></label>--}}
            {{--                                    <input class="form-control" type="text" id="name" name="name" required=""--}}
            {{--                                           placeholder="Enter name" autofocus>--}}
            {{--                                    <input class="form-control" type="hidden" id="id" name="id" value="0">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}

            {{--                            <div class="col-md-12 cust_company_name_div">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="name" class="form-label">Company Name <span--}}
            {{--                                            class="text-danger">*</span></label>--}}
            {{--                                    <input class="form-control" type="text" id="company_name" name="company_name" placeholder="Enter company name">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="customer_category_id" class="form-label">Lead Category</label>--}}
            {{--                                    <select class="form-select" id="customer_category_id" name="customer_category_id">--}}
            {{--                                        <option value=0>Choose</option>--}}
            {{--                                        @foreach($customerCategories as $customerCategory)--}}
            {{--                                            <option value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>--}}
            {{--                                        @endforeach--}}
            {{--                                    </select>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="customer_lead_id" class="form-label">Lead Source</label>--}}
            {{--                                    <select class="form-select" id="customer_lead_id" name="customer_lead_id">--}}
            {{--                                        <option value=0>Choose</option>--}}
            {{--                                        @foreach($customerLeads as $customerLead)--}}
            {{--                                            <option value="{{$customerLead->id}}">{{$customerLead->name}}</option>--}}
            {{--                                        @endforeach--}}
            {{--                                    </select>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="email" class="form-label">Email</label>--}}
            {{--                                    <input class="form-control" type="email" id="email" name="email"--}}
            {{--                                           placeholder="Enter email">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="phone_no" class="form-label">Phone no <span--}}
            {{--                                            class="text-danger">*</span></label>--}}
            {{--                                    <input class="form-control" type="text" id="phone_no" name="phone_no"--}}
            {{--                                           required="" placeholder="Enter phone no">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-12">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="address" class="form-label">Address</label>--}}
            {{--                                    <textarea class="form-control" id="address" name="address" placeholder="Enter address"></textarea>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="pincode" class="form-label">Pincode</label>--}}
            {{--                                    <input class="form-control" type="text" id="pincode" name="pincode"--}}
            {{--                                           placeholder="Enter pincode">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="country_id" class="form-label">Country <span--}}
            {{--                                            class="text-danger">*</span></label>--}}
            {{--                                    <select class="form-select" id="country_id" name="country_id" required="">--}}
            {{--                                        <option value="">Choose</option>--}}
            {{--                                        @php--}}
            {{--                                           $expData = App\Helpers\PermissionCheck::plan_details_check();--}}
            {{--                                        @endphp--}}

            {{--                                        @foreach($countries as $country)--}}
            {{--                                            <option value="{{$country->id}}"--}}
            {{--                                                    @if($country->id==$expData->country_id) selected @endif>{{$country->name}}</option>--}}
            {{--                                        @endforeach--}}
            {{--                                    </select>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="state_id" class="form-label">State <span--}}
            {{--                                            class="text-danger">*</span></label>--}}
            {{--                                    <select class="form-select" id="state_id" name="state_id" required="">--}}
            {{--                                        <option value="">Choose</option>--}}
            {{--                                    </select>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-6">--}}
            {{--                                <div class="mb-1 d-none">--}}
            {{--                                    <label for="city_id" class="form-label">City</label>--}}
            {{--                                    <select class="form-select" id="city_id" name="city_id">--}}
            {{--                                        <option value="0">Choose</option>--}}
            {{--                                    </select>--}}
            {{--                                </div>--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="city_name" class="form-label">City</label>--}}
            {{--                                    <input class="form-control" type="text" id="city_name" name="city_name" placeholder="Enter city">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                            <div class="col-md-12">--}}
            {{--                                <div class="mb-1">--}}
            {{--                                    <label for="gst_no" class="form-label">GSTIN</label>--}}
            {{--                                    <input class="form-control" type="text" id="gst_no" name="gst_no" placeholder="Enter gstin">--}}
            {{--                                </div>--}}
            {{--                            </div>--}}

            {{--                        </div>--}}

            {{--                        <div class="mb-3">--}}
            {{--                            <label for="description" class="form-label">Description</label>--}}
            {{--                            <textarea class="form-control" id="description" name="description"--}}
            {{--                                      placeholder="Enter description"></textarea>--}}
            {{--                        </div>--}}

            {{--                        <div class="text-end">--}}
            {{--                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close--}}
            {{--                            </button>--}}
            {{--                            <button class="btn btn-secondary" id="customer_button" type="submit"><i--}}
            {{--                                    class="mdi mdi-floppy fs-5"></i> Save--}}
            {{--                            </button>--}}
            {{--                        </div>--}}

            {{--                    </form>--}}
            {{--                </div>--}}
            {{--            </div><!-- /.modal-content -->--}}
            {{--        </div><!-- /.modal-dialog -->--}}
            {{--    </div><!-- /.modal -->--}}
            {{--    <button type="button" class="btn btn-secondary" data-bs-container="#tooltip-container3" data-bs-toggle="tooltip"--}}
            {{--            data-bs-html="true" data-bs-title="<em>Tooltip</em> <u>with</u> <b>HTML</b>">--}}
            {{--        Tooltip with HTML--}}
            {{--    </button>--}}

            {{-- <a type="button" data-bs-toggle="tooltip" data-bs-html="true" title="<em>Tooltip</em> <u>with</u> <b>HTML</b>">
                Tooltip with HTML
            </a>--}}

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

            <!-- Theme Settings -->

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
                                <div class="accordion-item">
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
                                                                                {{--                                                                    {{ in_array($leadGroup->id, $leadArr) ? 'checked' : '' }}--}}
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
                                            {{-- <select id="fil_status" name="fil_status" class="form-select">
                                                <option value="">Label All</option>
                                                @foreach($leadGroups as $leadGroup)
                                                    <option value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                                @endforeach
                                            </select>--}}
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
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header m-0" id="headingsix">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapsesix" aria-expanded="false" aria-controls="collapsesix">
                                            <small
                                                class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1 small_created_date"></small>
                                            Lead Created Date
                                        </button>
                                    </h2>
                                    <div id="collapsesix" class="accordion-collapse collapse" aria-labelledby="headingsix"
                                        data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            {{--<div
                                                class="d-flex justify-content-between xxx align-items-center mt-2 text-primary">
                                                <div id="lead_date_range" class="form-controls text-primary"
                                                    data-toggle="date-picker-range"
                                                    data-target-display="#selectedValue" data-cancel-class="btn-light"
                                                    style="max-width:100%;">
                                                    <i class="mdi mdi-calendar"></i>&nbsp;
                                                    <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                                </div>
                                            </div>--}}
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
                                                        placeholder="Add Discussion Summary.."
                                                        style="height: 250px"></textarea>--}}


                                                <select class="form-select bg-light text-dark" id="leads_stages_id"
                                                        name="leads_stages_id" required>
                                                    {{--                                            <option value="">Choose</option>--}}
                                                    @foreach($leadStages as $leadStage)
                                                        <option value="{{$leadStage->id}}"
                                                                data-id="{{$leadStage->is_default}}">{{$leadStage->name}}</option>
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
                                                        <option value="{{$lostReason->id}}"
                                                                data-id="{{$lostReason->priority}}">{{$lostReason->name}}</option>
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
                                                        <option value="{{$leadStage->id}}" data-id="{{$leadStage->is_default}}">{{$leadStage->name}}</option>
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

            <div id="advance-filter-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light border-bottom-1">
                            <h3 class="modal-title text-dark" id="assign-lead-stage-formModalLabel">Advance Filter</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <h5 class="mb-1 text-uppercase text-dark bg-light p-2"><i class="mdi mdi-office-building me-1"></i>
                                By Location</h5>
                            <div class="row">
                                <div class="col-4">
                                    <div class="align-items-center">
                                        <label for="fil_city_name" class="text-dark fw-bold me-2">City Name</label>
                                        <input class="form-control" type="text" id="fil_city_name" name="fil_city_name"
                                            placeholder="City name">
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


            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light border-0">
                            <h3 class="modal-title" id="staticBackdropLabel">Duplicate Leads</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                        </div> <!-- end modal header -->
                        <div class="modal-body duplicate-table-info">
                            ...
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary customer_button" id="duplicate_customer_button"
                                    form="customer-form" value="1">Continue
                            </button>
                        </div> <!-- end modal footer -->
                    </div> <!-- end modal content-->
                </div> <!-- end modal dialog-->
            </div> <!-- end modal-->
        </div>
    </div>
</div>

@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script> -->

    <!-- third party js -->
    @include('app.layouts.partials.datatable-script')

    <!-- Daterangepicker js -->
    <script src="{{ asset('vendor/daterangepicker/moment.min.js')}}"></script>
    <script src="{{ asset('vendor/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js')}}"></script>
    <link href="{{ asset('vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>--}}
    <!-- App js -->
    <script src="{{ asset('js/app.min.js')}}"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script>
        function clearOPRSearch() {
            window.location.href = '{{url('lead')}}';
        }

        function OpenModalAssignLeadStage(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            if (join_selected_values.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
                return false;
            }
            if (allVals.length > 50) {
                toastrWarning('Please select maximum 50 records', 'Warning');
                return false;
            }

            $(".assign-lead-stage-form #leads_stages_id").find('option:eq(0)').prop('selected', true);
            $(".assign-lead-stage-form #lead_id").val(join_selected_values);
            $(".assign-lead-stage-form #lost_reason_id").val('');
            $(".lost_reason_div").hide();
            $(".lost_reason_others_div").hide();
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function OpenModalAssignLeadLabels(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            if (join_selected_values.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
                return false;
            }
            if (allVals.length > 50) {
                toastrWarning('Please select maximum 50 records', 'Warning');
                return false;
            }
            $(".lead-label-form #lead_id").val(join_selected_values);
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function OpenModalAssignLead(id = 0, modalName, modalTitleName, modalTitle, modalForm) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            if (join_selected_values.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
                return false;
            }
            if (allVals.length > 50) {
                toastrWarning('Please select maximum 50 records', 'Warning');
                return false;
            }
            $(".assign-lead-form #id").val(join_selected_values);
            $(modalTitleName).text(modalTitle);
            $(modalName).modal('show');
        }

        function openModalCustomer(modalName, modalTitle, modalForm, modalTitleName, id = 0, flag = 0) {
            $("#customer-modal " + modalTitleName).text(modalTitle);
            $(modalForm).parsley().reset();
            $(modalName).modal('show');


            if (flag == 3) {
                $('.cust_company_name_div').hide();
                $('#city_id').html('<option value="">Choose</option>');
                $('#state_id').html('<option value="0">Choose</option>');
                getStatesList({{ $expData->country_id }});
                $("#customer-modal .advance-option").removeClass('d-none');
                $("#customer-modal .advance-option-div").addClass('d-none');
                $('#whatsapp_country_code').val({{ \App\Models\Country::where(['id' => $expData->country_id])->pluck('phonecode')[0] }});
                $('#whatsapp_country_code').select2({dropdownParent: $('#sel_wcc')}).trigger('change');
                $('#country_code').val({{ \App\Models\Country::where(['id' => $expData->country_id])->pluck('phonecode')[0] }});
                $('#country_code').select2({dropdownParent: $('#sel_cc')}).trigger('change');
            }

            resetForm(modalForm)
            $('#id').val(id);
        }

        /*  $(function () {
              function valueChanged()
              {
                  var array = [];
                  $("input:checkbox[name='fil_status[]']:checked").each(function() {
                      array.push($(this).val());
                  });

                  console.log(array);
              }
          });*/
        $(function () {
            $('#fil_state_id').select2({
                dropdownParent: $('#sel_st')
            });
            $('#fil_country_id').select2({
                dropdownParent: $('#sel_co')
            });
            /* $('.js-example-basic-multiple').select2().on('select2:select', function(e) {

                 var data = e.params.data;
                 var selectedOption = $(this).find(':selected');
                 var dataId = selectedOption.data('id');

                 alert(dataId);
                 // alert(text);
                     //I create a var data and works it like an Array
                    /!* var data = $(this).select2('data');
                     //Then I take the values like if I work with an array
                     var value = data.id;
                     var text = data.text;

                     alert(value);
                     alert(text);*!/
                     //If I use console.log(var) the values are displayed but not with an alert
                 });*/

            $("#filter-btn").click(function () {
                $(".filter-container").slideToggle('slow');
            });
            $('#theme-settings-offcanvas').on('shown.bs.offcanvas', function () {
                var backdropElements = $('.offcanvas-backdrop');
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

            $('[data-toggle="tooltip"]').tooltip();
            $('#country_code').select2({
                dropdownParent: $('#sel_cc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
                var selectedOption = $(this).find(':selected');
                var dataId = selectedOption.data('id');

                $("#country_id").val($('option:selected', this).data('id'));

                getStatesList($('option:selected', this).data('id'));


                $("#whatsapp_country_code option[data-id='" + dataId + "']").prop("selected", true);
                $('#whatsapp_country_code').select2({dropdownParent: $('#sel_wcc')}).trigger('change');
                console.log($('#country_id').find(':selected').data('id'));

                /* var option = $('#currency_name').find('option[data-id="' + dataId + '"]');
                 $('#currency_name').val(option.val()).trigger('change');*/

            });

            /* $('#currency_name').select2({
                 dropdownParent: $('#sel_cn')
             }).on('select2:select', function (e) {
                 var data = e.params.data;
             });*/

            $('#whatsapp_country_code').select2({
                dropdownParent: $('sel_wcc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
            });
        })
        $(document).ready(function () {
            @if (request()->has('q') && (request()->q == 'opr_new_leads' || request()->q == 'opr_new_lead' || request()->q == 'opr_call' || request()->q == 'opr_message' || request()->q == 'opr_lead_won' || request()->q == 'opr_lead_lost' || request()->q == 'opr_meeting' || request()->q == 'opr_followup_completed'))
            localStorage.removeItem('customer-datatable');
            @endif

            // Get today's date in the format DD-MM-YYYY
            var todayLocal = new Date().toLocaleDateString('en-GB').split('/').reverse().join('-');

            // Get the stored date from local storage
            var storedDate = localStorage.getItem('storedDate');

            // Check if the stored date is different from today's date
            if (storedDate !== todayLocal) {
                // Clear all items from local storage
                localStorage.clear();

                // Store today's date in local storage
                localStorage.setItem('storedDate', todayLocal);

                console.log('Local storage cleared and date updated.');
            } else {
                console.log('Local storage date is up to date.');
            }


            var tmpf = 'A';
            var fil_lead_date_start = moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days');
            var fil_lead_date_end = moment();
            // var fil_user_id =0;

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

            function cbt(fil_lead_date_start, fil_lead_date_end, tmpf = '') {
                $('#lead_date_range span').html(fil_lead_date_start.format('MMMM D, YYYY') + ' - ' + fil_lead_date_end.format('MMMM D, YYYY'));
                let date_range = fil_lead_date_start.format('YYYY-MM-DD') + '_' + fil_lead_date_end.format('YYYY-MM-DD');
                localStorage.setItem('fil_lead_date_start', moment(fil_lead_date_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                localStorage.setItem('fil_lead_date_end', moment(fil_lead_date_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                let fil_sp_user_id = localStorage.getItem('fil_user_id');

                if (tmpf != 'A') {
                    table.draw();
                }

            }

            $('#lead_date_range').daterangepicker({
                /*startDate: fil_lead_date_start,
                endDate: fil_lead_date_end,*/
                // "drops": "up",
                // parentEl: "#theme-settings-offcanvas .xxx",
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


            if (localStorage.hasOwnProperty("fil_user_id")) {
                fil_user_id = localStorage.getItem('fil_user_id');
                // $('#fil_team_member').val(fil_user_id);
                $("#fil_team_member option[value='" + fil_user_id + "']").prop('selected', true);
            } else {
                fil_user_id = {{auth()->user()->id}};
                localStorage.setItem('fil_user_id', fil_user_id);
                // $('#fil_team_member').val(fil_user_id);
                $("#fil_team_member option[value=" + fil_user_id + "]").prop('selected', true);
            }


            cbt(fil_lead_date_start, fil_lead_date_end, tmpf);
            // $('#country_code').select2();
            $(".advance-option").click(function () {
                //Do stuff when clicked
                $(".advance-option").addClass('d-none');
                $(".advance-option-div").removeClass('d-none');
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
            "use strict";
            var table = $("#customer-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                lengthMenu: [
                    [10, 25, 50, 100, 200],
                    ['10', '25', '50', '100', '200']
                ],
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
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
                    /*{
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
                    }, {
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
                    }*/
                    @endif
                    , {
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
                exportOptions: {
                    modifer: {
                        page: 'all',
                        search: 'none'
                    }
                },
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    @if (request()->get('fil_lead_stage_id'))
                        data.fil_lead_stage_id = {{ request()->get('fil_lead_stage_id') != 0 ? request()->get('fil_lead_stage_id') : '' }};
                    @else
                        data.fil_lead_stage_id = $('#fil_lead_stage_id').val();
                    @endif
                        data.fil_customer_category_id = $('#fil_customer_category_id').val();
                    data.fil_customer_lead_id = $('#fil_customer_lead_id').val();
                    data.fil_created_user_id = $('#fil_created_user_id').val();
                    data.fil_estimate_status_id = $('#fil_estimate_status_id').val();
                    data.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD");
                    data.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD");
                    data.fil_country_id = $('#fil_country_id').val();
                    data.fil_state_id = $('#fil_state_id').val();
                    data.fil_city_name = $('#fil_city_name').val();
                    // data.fil_status = $('#fil_status').val();
                    var array = [];
                    $("input:checkbox[name='fil_status[]']:checked").each(function () {
                        if ($(this).is(":checked"))
                            array.push($(this).val());

                    });


                    // data.fil_status_new = array,
                    data.fil_status_new = localStorage.getItem('fil_lead_label_id'),
                        /*  var array = [];
                          $("input:checkbox[name=fil_status[]]:checked").each(function() {
                              array.push($(this).val());
                          });*/
                        data.fil_type = $('#fil_type').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_team_member = localStorage.getItem('fil_user_id');
                    $("#fil_lead_stage_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_status').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_customer_category_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_customer_lead_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_created_user_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#fil_city_name").attr("style", "border: 1px solid #dee2e6 !important;");
                    $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #dee2e6 !important;');
                    $("#fil_estimate_status_id").attr("style", "border: 1px solid #dee2e6 !important;");
                    $("#lead_date_range").attr("style", "border: 1px solid #dee2e6 !important;");
                    if (data.fil_lead_stage_id) {
                        $("#fil_lead_stage_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_status_new) {
                        $('#fil_status').next('.select2-container').find('.select2-selection--multiple').attr('style', 'border: 1px solid #727cf5 !important;');
                        // $("#fil_lead_label_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_customer_category_id) {
                        $("#fil_customer_category_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_customer_lead_id) {
                        $("#fil_customer_lead_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_created_user_id) {
                        $("#fil_created_user_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_estimate_status_id) {
                        $("#fil_estimate_status_id").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_city_name) {
                        $("#fil_city_name").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                    if (data.fil_state_id) {
                        $('#fil_state_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }
                    if (data.fil_country_id) {
                        $('#fil_country_id').next('.select2-container').find('.select2-selection--single').attr('style', 'border: 1px solid #727cf5 !important;');
                    }

                    let tmp_date = moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days');
                    var tmp_fil_lead_date_start = moment(tmp_date, 'YYYY-MM-DD').format("YYYY-MM-DD");


                    if (data.fil_lead_date_start != tmp_fil_lead_date_start || data.fil_lead_date_end != '{{Carbon\Carbon::now()->format('Y-m-d')}}') {
                        $("#lead_date_range").attr("style", "border: 1px solid #727cf5 !important;");
                    }
                },
                stateLoadParams: function (settings, data) {
                    var str = localStorage.getItem('fil_lead_label_id');
                    var arr = [];
                    if (str) {
                        arr = $.map(str.split(","), function (value) {
                            return parseInt(value, 10);
                        });
                    }
                    $("#fil_status").val(arr).trigger('change');
                    /*  $('#fil_status').select2();
                      alert(+localStorage.getItem('fil_lead_label_id'));
                      $('#fil_status').val("["+localStorage.getItem('fil_lead_label_id')+"]").trigger("change");*/
                    // $("#fil_status").select2.select2("val", [localStorage.getItem('fil_lead_label_id')]);
                    // }
                    @if (request()->get('fil_lead_stage_id'))
                    $('#fil_lead_stage_id').val(
                        '{{ request()->get('fil_lead_stage_id') != 0 ? request()->get('fil_lead_stage_id') : '' }}'
                    );
                    @else
                    $('#fil_lead_stage_id').val(data.fil_lead_stage_id);
                    @endif
                    $('#fil_customer_category_id').val(data.fil_customer_category_id);
                    $('#fil_customer_lead_id').val(data.fil_customer_lead_id);
                    $('#fil_created_user_id').val(data.fil_created_user_id);
                    $('#fil_estimate_status_id').val(data.fil_estimate_status_id);
                    // $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                    $('#fil_country_id').val(data.fil_country_id);
                    $("#fil_country_id option[value='" + data.fil_country_id + "']").prop("selected", true);
                    $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                    $('#fil_state_id').val(data.fil_state_id);
                    $("#fil_state_id option[value='" + data.fil_state_id + "']").prop("selected", true);
                    $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                    $('#fil_city_name').val(data.fil_city_name);
                    $('#fil_team_member').val(localStorage.getItem('fil_user_id'));
                    $('#lead_date_range span').html(moment(fil_lead_date_start, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY') + ' - ' + moment(fil_lead_date_end, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY'));
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    type: 'POST',
                    url: "{{ route('tenant.customer.index-post', ['tenant' => $segment]) }}",
                    data: function (d) {
                        var array = [];
                        $("body input:checkbox[name='fil_status[]']").each(function () {
                            if ($(this).is(":checked"))
                                array.push($(this).val());
                        });

                        let tmp_date = moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days');
                        var tmp_fil_lead_date_start = moment(tmp_date, 'YYYY-MM-DD').format("YYYY-MM-DD");

                        let i = 0;
                        if (moment(localStorage.getItem('fil_lead_date_start'), 'YYYY-MM-DD').format("YYYY-MM-DD") != tmp_fil_lead_date_start || moment(localStorage.getItem('fil_lead_date_end'), 'YYYY-MM-DD').format("YYYY-MM-DD") != '{{Carbon\Carbon::now()->format('Y-m-d')}}') {
                            console.log('aa');
                            i = Number(i) + 1;
                        }
                        // $(".small_labels").hide();
                        if (localStorage.getItem('fil_lead_label_id') > 0) {
                            // $(".small_labels").show();
                            console.log('a');
                            i = Number(i) + 1;
                        }

                        // $(".small_origin").hide();
                        if ($('#fil_customer_lead_id').val()) {
                            console.log('b');
                            // $(".small_origin").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_category").hide();
                        if ($('#fil_customer_category_id').val()) {
                            console.log('c');
                            // $(".small_category").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_created_by").hide();
                        if ($('#fil_created_user_id').val()) {
                            console.log('d');
                            // $(".small_created_by").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_estimate_status_id').val()) {
                            console.log('e');
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }

                        // $(".small_estimate_status").hide();
                        if ($('#fil_lead_stage_id').val()) {
                            console.log('f');
                            // $(".small_estimate_status").show();
                            i = Number(i) + 1;
                        }
                        let j = 0;
                        if ($('#fil_country_id').val()) {
                            console.log('g');
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_state_id').val()) {
                            console.log('h');
                            i = Number(i) + 1;
                            j = Number(j) + 1;
                        }

                        if ($('#fil_city_name').val()) {
                            console.log('i');
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
                        d.q = '{{(request()->has('q'))?request()->q:''}}',
                            d.dashboard_lead_filter = '{{(request()->query('fil_lead_stage_id'))?'apply_filter':''}}',
                            d.status = localStorage.getItem('fil_lead_label_id'),
                            d.fil_lead_stage_id = $('#fil_lead_stage_id').val(),
                            d.assigned_to_user = $('#fil_team_member').val(),
                            d.customer_type = $('#fil_type').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('#customer-datatable_filter input[type="search"]').val(),
                            // d.search = $('input[type="search"]').val(),
                            d.fil_customer_category_id = $('#fil_customer_category_id').val(),
                            d.fil_customer_lead_id = $('#fil_customer_lead_id').val(),
                            d.fil_created_user_id = $('#fil_created_user_id').val(),
                            d.fil_estimate_status_id = $('#fil_estimate_status_id').val(),
                            d.fil_lead_date_start = moment(localStorage.getItem('fil_lead_date_start')).format("YYYY-MM-DD"),
                            d.fil_lead_date_end = moment(localStorage.getItem('fil_lead_date_end')).format("YYYY-MM-DD"),
                            d.fil_country_id = $('#fil_country_id').val();
                        d.fil_state_id = $('#fil_state_id').val();
                        d.fil_city_name = $('#fil_city_name').val();
                        var tmp_opr_id_2;
                        @if (request()->has('q') && request()->q == 'opr_new_leads')
                            tmp_opr_id_2 = localStorage.getItem('opr_id_2');
                        @else
                            tmp_opr_id_2 = '';
                        @endif
                            d.opr_id_2 = tmp_opr_id_2;

                        var tmp_popr_id_1;
                        @if (request()->has('q') && request()->q == 'opr_new_lead')
                            tmp_popr_id_1 = localStorage.getItem('popr_id_1');
                        @else
                            tmp_popr_id_1 = '';
                        @endif
                            d.popr_id_1 = tmp_popr_id_1;

                        var tmp_popr_id_3;
                        @if (request()->has('q') && request()->q == 'opr_call')
                            tmp_popr_id_3 = localStorage.getItem('popr_id_3');
                        @else
                            tmp_popr_id_3 = '';
                        @endif
                            d.popr_id_3 = tmp_popr_id_3;

                        var tmp_popr_id_4;
                        @if (request()->has('q') && request()->q == 'opr_message')
                            tmp_popr_id_4 = localStorage.getItem('popr_id_4');
                        @else
                            tmp_popr_id_4 = '';
                        @endif
                            d.popr_id_4 = tmp_popr_id_4;


                        var tmp_ropr_id_1;
                        @if (request()->has('q') && request()->q == 'opr_lead_won')
                            tmp_ropr_id_1 = localStorage.getItem('ropr_id_1');
                        @else
                            tmp_ropr_id_1 = '';
                        @endif
                            d.ropr_id_1 = tmp_ropr_id_1;

                        var tmp_ropr_id_2;
                        @if (request()->has('q') && request()->q == 'opr_lead_lost')
                            tmp_ropr_id_2 = localStorage.getItem('ropr_id_2');
                        @else
                            tmp_ropr_id_2 = '';
                        @endif
                            d.ropr_id_2 = tmp_ropr_id_2;

                        var tmp_ropr_id_3;
                        @if (request()->has('q') && request()->q == 'opr_meeting')
                            tmp_ropr_id_3 = localStorage.getItem('ropr_id_3');
                        @else
                            tmp_ropr_id_3 = '';
                        @endif
                            d.ropr_id_3 = tmp_ropr_id_3;

                        var tmp_ropr_id_5;
                        @if (request()->has('q') && request()->q == 'opr_site_visit')
                            tmp_ropr_id_5 = localStorage.getItem('ropr_id_5');
                        @else
                            tmp_ropr_id_5 = '';
                        @endif
                            d.ropr_id_5 = tmp_ropr_id_5;

                        var tmp_ropr_id_4;
                        @if (request()->has('q') && request()->q == 'opr_followup_completed')
                            tmp_ropr_id_4 = localStorage.getItem('ropr_id_4');
                        @else
                            tmp_ropr_id_4 = '';
                        @endif
                            d.ropr_id_4 = tmp_ropr_id_4;

                    }
                },
                "order": [[7, "desc"]],
                "columnDefs": [{
                    "className": "label_td",
                    "targets": [7]
                }],
                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, visible: true,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let country_code = '';
                            if (row.country_code) {
                                country_code = row.country_code;
                            }
                            let company_name = '';
                            if (row.company_name) {
                                company_name = ' (' + row.company_name + ')';
                            }
                            let new_lead = '';
                            if (row.new_lead_flag == 1) {
                                // new_lead = '<span class="badge bg-secondary text-light float-end blinks" style="background:#0acf97 !important;">New</span>';
                                new_lead = '<small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1" title="New Lead"></small>';
                            }
                            return '<td>' +

                                '<h5 class=" mb-1 fw-bold">' + new_lead + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                // '<span class="text-muted font-10">' + country_code+row.phone_no + '</span>' +
                                '</td>';
                        }
                    },
                    {
                        data: 'company_name', name: 'company_name',
                        render: function (data, type, row) {
                            let company_name = '';
                            if (row.company_name) {
                                company_name = row.company_name;
                            }
                            return '<td>' +
                                funcStrLimits(company_name, 15, 0) +
                                '</td>';
                        }
                    },
                    {
                        data: 'phone_no', name: 'phone_no',
                        render: function (data, type, row) {
                            let country_code = '';
                            if (row.country_code) {
                                country_code = row.country_code;
                            }

                            return '<td>' +
                                country_code + row.phone_no +
                                '</td>';
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
                            if (row.assigned_to_user == 0) {
                                assign_color = 'bg-secondary text-light';
                                assign_icon = 'mdi-account-off';
                                tmp_usr_name = 'Unassigned';
                            }

                            if (currnt_user_id == row.assigned_to_user) {
                                tmp_usr_name = '';
                            }
                            var ss = '';

                            // if(row.assigned_to_user) {
                            ss = '<td>' +
                                '<span class=" fs-6 badge ' + assign_color + '"> <i class="pe-1 mdi ' + assign_icon + '"></i>' + tmp_usr_name + '</span>' +
                                '</td>';
                            // }

                            return ss;
                        }
                    },
                    {data: 'label_name', name: 'label_name'},
                    // {data: 'assign_user_name', name: 'assign_user_name'},
                    {
                        data: 'last_activity', name: 'last_activity',
                        render: function (data, type, row) {
                            let ficon = '';
                            let last_activity_type = row.last_activity_type;
                            let last_activity_name = row.last_activity_name;
                            let last_activity = row.last_activity;
                            let last_internal_remarks = row.last_internal_remarks;
                            let time_ago_string = row.time_ago_string;

                            if (last_activity_type == 1) {
                                ficon = '<i class="mdi mdi-phone text-success timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity

                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            } //call

                            if (last_activity_type == 2) {
                                ficon = '<i class="mdi mdi-chat-outline text-warning timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);
                            }// message

                            if (last_activity_type == 3) {
                                ficon = '<i class="mdi mdi-calendar text-dark timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
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

                            if (last_activity_type == 4) {
                                ficon = '<i class="mdi mdi-file-document-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // notes

                            if (last_activity_type == 5) {
                                ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                if (last_activity) {
                                    last_activity = ' - ' + last_activity;
                                }
                                if (last_activity == null) {
                                    last_activity = '';
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // estimate

                            if (last_activity_type == 6) {
                                ficon = '<i class="mdi mdi-account-plus-outline text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string + ' - ' + last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // create leads

                            if (last_activity_type == 7) {
                                ficon = '<i class="mdi mdi-pencil text-info timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string + ' - ' + last_internal_remarks;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // edit leads

                            if (last_activity_type == 8) {
                                ficon = '<i class="mdi mdi-arrow-top-right text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // assigned leads

                            if (last_activity_type == 9) {
                                ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                if (row.last_is_modified == 1 && row.last_is_follow_up == 1) {
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                if (row.last_is_modified == 1 && row.last_is_follow_up == 1) {
                                    ficon = '<i class="mdi mdi-file-pdf-box text-danger timeline-icon rounded-circle widget-icon-md"></i>';
                                    last_activity_name = 'Estimate';
                                }
                                let tmp_last_activity = '';
                                if (last_activity) {
                                    tmp_last_activity = ' - ' + last_activity;
                                }
                                last_activity = last_activity_name + ' - ' + time_ago_string + tmp_last_activity;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // follow up

                            if (last_activity_type == 10) {
                                ficon = '<i class="mdi mdi-book-edit-outline text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                last_activity = time_ago_string + ' - ' + last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // status updated


                            if (last_activity_type == 12) {
                                ficon = '<i class="mdi mdi-message-text-outline text-primary timeline-icon"></i> Message Sent - ';
                                last_activity = time_ago_string + ' - ' + last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // message sent

                            if (last_activity_type == 13) {
                                ficon = '<i class="mdi mdi-file-document-outline text-primary timeline-icon"></i> File Sent - ';
                                last_activity = time_ago_string + ' - ' + last_activity_name;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // message sent

                            if (last_activity_type == 14) {
                                ficon = '<i class="mdi mdi-calendar-blank-multiple text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // remove follow up

                            if (last_activity_type == 15) {
                                ficon = '<i class="mdi mdi-calendar-blank text-secondary timeline-icon"></i>';
                                last_activity = last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // someday follow up

                            if (last_activity_type == 16) {
                                ficon = '<i class="mdi mdi-checkbox-marked-circle-outline text-dark timeline-icon"></i>';
                                last_activity = last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // status updated client side

                            if (last_activity_type == 17) {
                                ficon = '<i class="mdi mdi mdi-close text-danger timeline-icon"></i>';
                                last_activity = ' Lead Lost -' + last_internal_remarks + ' - ' + time_ago_string;
                                last_activity = ficon + ' ' + funcStrLimits(last_activity, 50, 1);

                            } // lead lost Reason

                            if (last_activity_type == 18) {
                                ficon = '<i class="mdi mdi-trophy-outline text-success timeline-icon"></i>';
                                last_activity = ' Lead Won - ' + time_ago_string;
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
                    // {data: 'phone_no', name: 'phone_no'},
                    {data: 'lead_origin', name: 'lead_origin', visible: false},
                    {data: 'lead_category', name: 'lead_category', visible: false},
                    {data: 'net_amount', name: 'net_amount'},
                    {data: 'estimate_no', name: 'estimate_no', visible: false},
                    {
                        data: 'estimate_status', name: 'estimate_status', visible: true,
                        render: function (data, type, row) {
                            let tmp_status = '';
                            if (row.estimate_status == 'Draft') {
                                tmp_status = '<span class="text-secondary fw-bold">' + row.estimate_status + '</span>';
                            }
                            if (row.estimate_status == 'Sent') {
                                tmp_status = '<span class="text-primary fw-bold">' + row.estimate_status + '</span>';
                            }
                            if (row.estimate_status == 'Inprogress') {
                                tmp_status = '<span class="text-warning fw-bold">In Progress</span>';
                            }
                            if (row.estimate_status == 'Accept') {
                                tmp_status = '<span class="text-success fw-bold">' + row.estimate_status + '</span>';
                            }
                            if (row.estimate_status == 'Decline') {
                                tmp_status = '<span class="text-danger fw-bold">' + row.estimate_status + '</span>';
                            }
                            return tmp_status;
                        }
                    },
                    {data: 'created_at', name: 'created_at', visible: false},
                    {data: 'customer_type', name: 'customer_type', orderable: true, visible: false},
                    {data: 'email', name: 'email', visible: false},
                    {data: 'address', name: 'address', visible: false},
                    {data: 'pincode', name: 'pincode', visible: false},
                    {data: 'country_name', name: 'country_name', visible: false},
                    {data: 'state_name', name: 'state_name', visible: false},
                    {data: 'city_name', name: 'city_name', visible: false},
                    /*{data: 'description', name: 'description', visible: false},
                    {
                        data: 'status', name: 'status', visible: false,
                        render: function (data, type, row) {
                            var fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.customer.edit-status', ['tenant' => $segment])}}','#customer-datatable')";
                            if (data == 0)
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Active</span>';
                            else {
                                fun_status = "change_status('" + row.action + "', 0,'{{route('tenant.customer.edit-status', ['tenant' => $segment])}}','#customer-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }

                        }
                    },
                    {
                        data: 'action', name: 'action', orderable: false, visible: false,
                        render: function (data, type, row) {

                            var edit_fun = "edit_id('" + row.action + "')";
                            var edit_fun = "{{url('lead/timeline')}}/" + row.action;
                            var delete_fun = "remove_id('" + row.action + "','{{route('tenant.customer.delete', ['tenant' => $segment])}}','#customer-datatable')";
                            return '<div class="invoice-action">' +
                                @if(in_array('edit-customer', $user_perm) || auth()->user()->company_id==null)
                    '<a href="' + edit_fun + '" class="action-icon mr-1" id="edit_' + row.action + '">' +
                '<i class="mdi mdi-square-edit-outline"></i>' +
                '</a>' +
@endif
                    {{--@if(in_array('remove-customer', $user_perm) || auth()->user()->company_id==null)
                    '<a href="javascript:void(0)" class="action-icon" id="remove_' + row.action + '"  onclick="' + delete_fun + '">' +
                    '<i class="mdi mdi-delete"></i>' +
                    '</a>' +
                    @endif--}}
                    '</div>';
        }
    },*/
                ],
                drawCallback: function () {
                    $("#cnt_lead").text(table.page.info().recordsTotal);
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);

                }
            });
            table.on('click', 'tbody tr td:not(:first-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/lead/timeline/' + id;
            });
            table.buttons().container().appendTo("#customer-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })
            $(document).on('click', '#reset-layout', function () {
                $('body #lead-table input:checkbox').prop('checked', false);
                $("#fil_customer_category_id").val('');
                $("#fil_customer_lead_id").val('');
                $("#fil_created_user_id").val('');
                $("#fil_estimate_status_id").val('');
                localStorage.removeItem("customer-datatable");

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
                    var data = localStorage.getItem('customer-datatable');


                    // Parse the data from string to object
                    var parsedData = JSON.parse(data);


                    // Update the value of "fil_team_member"
                    parsedData.fil_lead_date_start = localStorage.getItem('fil_lead_date_start');
                    parsedData.fil_lead_date_end = localStorage.getItem('fil_lead_date_end');
                    console.log(localStorage.getItem('fil_lead_date_start'));
                    console.log(localStorage.getItem('fil_lead_date_end'));
                    // Convert the updated object back to string
                    var updatedData = JSON.stringify(parsedData);


                    // Store the updated data back in localStorage
                    localStorage.setItem('customer-datatable', updatedData);

                    // Confirmation message
                    console.log('Value updated successfully!');
                } else {
                    console.log('Browser does not support localStorage');
                }
                // $("#customer-datatable").DataTable().ajax.reload();
                table.draw();

                closeCanvas.click();
            });

            $("input:checkbox[name='fil_status[]']").on("click", function () {
                table.draw();
            });

            $(document).on('click', '#filter_button', function () {
                table.draw();
                $('#advance-filter-modal').modal('toggle');
            });

            $(document).on('click', '#filter_reset_button', function () {
                $('#fil_lead_stage_id').val('');
                $('#fil_status').val('');
                $('#fil_customer_category_id').val('');
                $('#fil_customer_lead_id').val('');
                $('#fil_created_user_id').val('');
                $('#fil_estimate_status_id').val('');
                $('#lead_date_range span').html(moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
                localStorage.setItem('fil_lead_date_start', moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('YYYY-MM-DD'));
                localStorage.setItem('fil_lead_date_end', moment().format('YYYY-MM-DD'));
                localStorage.setItem('fil_lead_label_id', '');
                $("#fil_status").val('').trigger('change');
                table.draw();
            });

            $(document).on('click', '#advance_filter_reset_button', function () {

                $('#fil_country_id').val('');
                $("#fil_country_id option[value='']").prop("selected", true);
                $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                $('#fil_state_id').val('');
                $("#fil_state_id option[value='']").prop("selected", true);
                $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                $('#fil_city_name').val('');
                table.draw();
                $('#advance-filter-modal').modal('toggle');
            });

            $('#fil_customer_category_id,#fil_customer_lead_id,#fil_created_user_id,#fil_estimate_status_id').change(function () {
                table.draw();
            });

            $("#fil_status").select2().on("select2:select select2:unselect", function (e) {

                //this returns all the selected item
                var items = $(this).val();
                //Gets the last selected item
                var lastSelectedItem = e.params.data.id;


                if (!localStorage.hasOwnProperty("fil_lead_label_id")) {
                    localStorage.setItem('fil_lead_label_id', items);
                }

                if (localStorage.hasOwnProperty("fil_lead_label_id")) {
                    localStorage.setItem('fil_lead_label_id', items);
                }


                table.draw();

            })

            $('#fil_lead_stage_id,#fil_name,#fil_type,#fil_team_member').change(function () {
                table.draw();
                let fil_user_id = $('#fil_team_member').val();
                localStorage.setItem('fil_user_id', fil_user_id);
                if (typeof (Storage) !== "undefined") {
                    // Retrieve the existing data from localStorage
                    var data = localStorage.getItem('duetoday-datatable'); // Replace 'your_key' with the actual key name
                    var dataA = localStorage.getItem('upcoming-datatable'); // Replace 'your_key' with the actual key name
                    var dataB = localStorage.getItem('overdue-datatable'); // Replace 'your_key' with the actual key name
                    var dataC = localStorage.getItem('someday-datatable'); // Replace 'your_key' with the actual key name
                    var dataD = localStorage.getItem('never-followup-datatable'); // Replace 'your_key' with the actual key name
                    var dataE = localStorage.getItem('duetoday-datatable-dashboard'); // Replace 'your_key' with the actual key name
                    var dataF = localStorage.getItem('estimate-datatable'); // Replace 'your_key' with the actual key name

                    // Parse the data from string to object
                    var parsedData = JSON.parse(data);
                    var parsedDataA = JSON.parse(dataA);
                    var parsedDataB = JSON.parse(dataB);
                    var parsedDataC = JSON.parse(dataC);
                    var parsedDataD = JSON.parse(dataD);
                    var parsedDataE = JSON.parse(dataE);
                    var parsedDataF = JSON.parse(dataF);

                    // Update the value of "fil_team_member"
                    parsedData.fil_team_member = $('#fil_team_member').val();
                    parsedDataA.fil_team_member = $('#fil_team_member').val();
                    parsedDataB.fil_team_member = $('#fil_team_member').val();
                    parsedDataC.fil_team_member = $('#fil_team_member').val();
                    parsedDataD.fil_team_member = $('#fil_team_member').val();
                    parsedDataE.fil_team_member = $('#fil_team_member').val();
                    parsedDataF.fil_team_member = $('#fil_team_member').val();
                    parsedData.fil_status = $('#fil_status').val();
                    parsedDataA.fil_status = $('#fil_status').val();
                    parsedDataB.fil_status = $('#fil_status').val();
                    parsedDataC.fil_status = $('#fil_status').val();
                    parsedDataD.fil_status = $('#fil_status').val();
                    // parsedDataF.fil_status = $('#fil_status').val();

                    // Convert the updated object back to string
                    var updatedData = JSON.stringify(parsedData);
                    var updatedDataA = JSON.stringify(parsedDataA);
                    var updatedDataB = JSON.stringify(parsedDataB);
                    var updatedDataC = JSON.stringify(parsedDataC);
                    var updatedDataD = JSON.stringify(parsedDataD);
                    var updatedDataE = JSON.stringify(parsedDataE);
                    var updatedDataF = JSON.stringify(parsedDataF);

                    // Store the updated data back in localStorage
                    localStorage.setItem('duetoday-datatable', updatedData); // Replace 'your_key' with the actual key name
                    localStorage.setItem('upcoming-datatable', updatedDataA); // Replace 'your_key' with the actual key name
                    localStorage.setItem('overdue-datatable', updatedDataB); // Replace 'your_key' with the actual key name
                    localStorage.setItem('someday-datatable', updatedDataC); // Replace 'your_key' with the actual key name
                    localStorage.setItem('never-followup-datatable', updatedDataD); // Replace 'your_key' with the actual key name
                    localStorage.setItem('duetoday-datatable-dashboard', updatedDataE); // Replace 'your_key' with the actual key name
                    localStorage.setItem('estimate-datatable', updatedDataF); // Replace 'your_key' with the actual key name

                    // Confirmation message
                    console.log('Value updated successfully!');
                } else {
                    console.log('Browser does not support localStorage');
                }
            });

            $('#resetFilter').click(function () {
                $('input[type=text]').val('');
                $('#fil_status').val('');
                $('#fil_lead_stage_id').val('');
                $('#fil_type').val('');
                table
                    .search('')
                    .columns().search('')
                    .draw();
            });

            $('.open_lead_modal').click(function () {
                $("#customer_import_button").show();
                $("#customer_import__final_button").hide();
                $("#excel_preview").html("");
                $("#download_error_report").attr("href", "#");
                $("#download_error_report").hide();
            });

            formValition('#customer-form');

            var buttonValue = 0;

            $('.customer_button').on('click', function () {
                // Store the value of the clicked button
                buttonValue = $(this).val();
            });


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

                    formData.push({
                        name: 'button_value',
                        value: buttonValue
                    });

                    /*formData.push({
                        name: 'currency_name_country_id',
                        value: $("#currency_name").find(':selected').data('id')
                    });*/

                    $.ajax({
                        // async: false,
                        type: 'POST',
                        url: '{{route('tenant.customer.store', ['tenant' => $segment])}}',
                        /*contentType: false,
                        cache: false,
                        processData: false,*/
                        data: formData,
                        // data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#customer_button").prop('disabled', true);
                            $("#customer_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');

                            $("#duplicate_customer_button").prop('disabled', true);
                            $("#duplicate_customer_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#customer-modal').modal('toggle');
                            $('#staticBackdrop').modal('hide');
                            table.ajax.reload();
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');

                            $("#duplicate_customer_button").prop('disabled', false);
                            $("#duplicate_customer_button").html('Continue');
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            console.log(xhr.responseJSON.data);
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    $(".duplicate-table-info").html(xhr.responseJSON.data);
                                    $('#staticBackdrop').modal('toggle');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            $("#duplicate_customer_button").prop('disabled', false);
                            $("#duplicate_customer_button").html('Continue');
                        },
                        complete: function (data) {
                            $("#customer_button").html('Save');
                            $("#customer_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                            $("#duplicate_customer_button").prop('disabled', false);
                            $("#duplicate_customer_button").html('Continue');
                        }
                    });
                }
            });

            var excel_validate_final_array = [];
            formValition('#customer-import-form');
            $('.customer-import-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        //async: false,
                        type: 'POST',
                        url: '{{route('tenant.customer.import_preview', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#customer_import_button").prop('disabled', true);
                            $("#customer_import_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            //$('#customer-import-modal').modal('toggle');
                            table.ajax.reload();
                            //
                            var respons_data = data.data;
                            excel_validate_final_array = respons_data;
                            $("#excel_preview").html("");
                            var html = "";
                            html += "<row>";
                            html += "<div class='col-sm-3'>";
                            html += "<span class='badge' style='background-color: #fa5c7c !important; color:white !important'>Error</span> &nbsp;";
                            html += "<span class='badge ' style='background-color:#eefd00 !important; color:black !important;'>Duplicates</span></div></div>";
                            html += "<table class='table'>";
                            html += "<thead><tr>";
                            html += "<th><b>#</b></th>" +
                                "<th><b>Customer Type</b></th>" +
                                "<th><b>Company Name</b></th>" +
                                "<th><b>Name</b></th>" +
                                "<th><b>Email</b></th>" +
                                "<th><b>Country Code</b></th>" +
                                "<th><b>Phone No</b></th>" +
                                "<th><b>Address</b></th>" +
                                "<th><b>City</b></th>" +
                                "<th><b>State</b></th>" +
                                "<th><b>Country</b></th>" +
                                "<th><b>Pincode</b></th>" +
                                /*"<th><b>Customer Category</b></th>"+*/
                                "<th><b>Lead Source</b></th>" +
                                "<th><b>Description</b></th>" +
                                "<th><b>Gst No</b></th>" +
                                "<th><b>Action</b></th>";
                            html += "</tr></thead>";
                            var i = 1;
                            $.each(respons_data, function (key, val) {
                                var is_email_valid = "";
                                var is_duplicate = "";
                                if (val.email_valid == false) {
                                    is_email_valid = "background-color: #fa5c7c !important; color:white !important";
                                }
                                if (val.duplicate == true) {
                                    is_duplicate = "background-color:#eefd00 !important; color:black !important";
                                }
                                html += "<tr class=" + key + " style='border-top: 1px solid gray;'>" +
                                    "<td><b>" + i + "</b></td>" +
                                    "<td>" + if_null(val.customer_type) + "</td>" +
                                    "<td>" + if_null(val.company_name) + "</td>" +
                                    "<td style='" + if_validation_fail(val.name) + "' >" + if_null(val.name) + "</td>" +
                                    "<td style='" + is_email_valid + "'>" + if_null(val.email) + "</td>" +
                                    "<td style='" + if_validation_fail(val.country_code) + "'>" + if_null(val.country_code) + "</td>" +
                                    "<td style='" + if_validation_fail(val.phone_no) + " " + is_duplicate + "'>" + if_null(val.phone_no) + "</td>" +
                                    "<td>" + if_null(val.address) + "</td>" +
                                    "<td>" + if_null(val.city) + "</td>" +
                                    "<td>" + if_null(val.state) + "</td>" +
                                    "<td>" + if_null(val.country) + "</td>" +
                                    "<td>" + if_null(val.pincode) + "</td>" +
                                    /*"<td style='"+if_validation_fail(val.customer_category)+"'>"+if_null(val.customer_category)+"</td>"+*/
                                    "<td>" + if_null(val.lead_origin) + "</td>" +
                                    "<td>" + if_null(val.description) + "</td>" +
                                    "<td>" + if_null(val.gst_no) + "</td>" +
                                    "<td><i class='mdi mdi-delete delete_array_value' data-delete_id=" + key + "></i></td>" +
                                    "</tr>";
                                if (val.errors != "" || val.errors != null) {
                                    $.each(val.errors, function (error_key, error_val) {
                                        html += "<tr class=" + key + "><td></td><td colspan='14' class='text-danger'>" + error_val + "</td></tr>";
                                    });
                                }
                                i++;
                            });
                            html += "</table>";
                            $("#excel_preview").html(html);
                            $("#customer_import__final_button").show();
                            $("#customer_import_button").prop('disabled', false);
                            $("#customer_import_button").html('<i class="mdi mdi-floppy fs-5"></i> Preview');

                        },
                        error: function (xhr, status, error) {

                            var json = JSON.parse(xhr.responseText);
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 400:
                                    toastrError(json.errors);
                                    break;
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Error');
                                    $(".import_error_meesage").removeClass("d-none");
                                    var html = "";
                                    $.each(json.errors, function (name, val) {
                                        html += val[0] + "<br>";
                                    });
                                    $(".import_excel_error").html(html);
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#customer_import_button").prop('disabled', false);
                            $("#customer_import_button").html('<i class="mdi mdi-floppy fs-5"></i> Preview');
                        },
                        complete: function (data) {
                            $("#customer_import_button").html('<i class="mdi mdi-floppy fs-5"></i> Preview');
                            $("#customer_import_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
                }
            });

            function if_null(value) {
                return (value != null ? value : '-');
            }

            function if_validation_fail(value, email = false) {
                return (value != null ? '' : 'background-color: #fa5c7c !important; color:white !important; text-align: center !important;');
            }

            $(document).on('click', '.delete_array_value', function (e) {
                var key_value = $(this).attr('data-delete_id');
                excel_validate_final_array.splice(key_value, 1);
                $('.' + key_value).remove();
            });
            formValition('#customer-import-form');
            $('#customer_import__final_button').on('click', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        //async: false,
                        type: 'POST',
                        url: '{{route('tenant.customer.import', ['tenant' => $segment])}}',
                        contentType: 'application/json',
                        data: JSON.stringify(excel_validate_final_array),
                        dataType: "json",
                        beforeSend: function () {
                            $("#customer_import__final_button").prop('disabled', true);
                            $("#customer_import__final_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            table.ajax.reload();
                            $("#customer_import__final_button").prop('disabled', false);
                            $("#customer_import__final_button").html('<i class="mdi mdi-floppy fs-5"></i> Import');
                            if (data.data.failures_data != "") {
                                var day = new Date();
                                day = 'lead_export_error_report_' + day.getDate() + '-' + (day.getMonth() + 1) + '-' + day.getFullYear() + '_' + day.getHours() + '_' + day.getMinutes() + '_' + day.getSeconds();
                                const url = data.data.faile_name;
                                $("#excel_download").attr("href", url);
                                // const link = document.createElement('a');
                                // link.setAttribute('href', url);
                                // link.setAttribute('download', day+'.xlsx');
                                // link.click();
                                $("#download_error_report").attr("href", url);
                                $("#download_error_report").attr("data-file_name", data.data.file_name);
                                $("#download_error_report").show();
                                /*;*/
                            } else {
                                $('#customer-import-modal').modal('toggle');
                                $("#download_error_report").hide();
                            }
                        },
                        error: function (xhr, status, error) {

                            var json = JSON.parse(xhr.responseText);
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Error');
                                    $(".import_error_meesage").removeClass("d-none");
                                    var html = "";
                                    $.each(json.errors, function (name, val) {
                                        html += val[0] + "<br>";
                                    });
                                    $(".import_excel_error").html(html);
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#customer_import__final_button").prop('disabled', false);
                            $("#customer_import__final_button").html('<i class="mdi mdi-floppy fs-5"></i> Import');
                        },
                        complete: function (data) {
                            $("#customer_import__final_button").html('Import');
                            $("#customer_import__final_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
                }
            });

            $('#download_error_report').on('click', function (e) {

                e.preventDefault();
                var day = new Date();
                day = 'lead_export_error_report_' + day.getDate() + '-' + (day.getMonth() + 1) + '-' + day.getFullYear() + '_' + day.getHours() + '_' + day.getMinutes() + '_' + day.getSeconds();
                const url = $(this).attr("href");
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', day + '.xlsx');
                link.click();
                setTimeout(() => {
                    var post_data = new FormData();
                    post_data.append('url', $(this).attr('data-file_name'));
                    jQuery.ajax({
                        type: "POST",
                        datatype: "json",
                        url: SITEURL + '/delete-file',
                        data: post_data,
                        mimeType: "multipart/form-data",
                        contentType: false,
                        cache: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (data) {
                            $('#customer-import-modal').modal('toggle');
                            table.ajax.reload();
                        },
                        error: function (xhr, status, error) {
                        }
                    });
                }, 2000);
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

            $('.assign-lead-stage-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('assign-lead-stage-form'));
                    formData.append('lost_reason_name', $('#lost_reason_id').find(":selected").text());
                    formData.append('lead_stage_data_id', $('#leads_stages_id').find(":selected").data('id'));
                    $.ajax({
                        // async: false,
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
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            // $(".description_small").html(data.lead_description);
                            $('#assign-lead-stage-modal').modal('toggle');
                            $("#lead_stage_button").prop('disabled', false);
                            $("#lead_stage_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                            $("#select_count").html(0).hide();
                            $("#select_all").prop('checked', false);
                            table.draw();
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

            $('.lead-label-form').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serializeArray();

                if ($(this).parsley().isValid()) {

                    $.ajax({
                        // async: false,
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
                            $('input[name="selected_lead_id[]"]').prop('checked', false);
                            $("#select_count").html(0).hide();
                            $("#select_all").prop('checked', false);
                            // location.reload();
                            table.draw();
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
        });

        $(function () { //DOM Ready
            getStatesList({{$expData->country_id}});

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        function edit_id(id) {
            $.ajax({
                async: false,
                type: "GET",
                url: "{{route('tenant.customer.show', ['tenant' => $segment])}}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#customer-form");
                    resetForm("#customer-form");
                    $('#id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $(".cust_company_name_div").hide();
                    if (res.data.customer_type == "Business") {
                        $(".cust_company_name_div").show();
                    }
                    $('#company_name').val(res.data.company_name);
                    $('#email').val(res.data.email);
                    $('#phone_no').val(res.data.phone_no);
                    $('#whatsapp_no').val(res.data.whatsapp_no);
                    $('#address').val(res.data.address);
                    $('#pincode').val(res.data.pincode);
                    $('#country_id').val(res.data.country_id);
                    $('#customer_category_id').val(res.data.customer_category_id);
                    $('#customer_lead_id').val(res.data.customer_lead_id);
                    $('#gst_no').val(res.data.gst_no);
                    $('#city_name').val(res.data.city_name);
                    getStatesList(res.data.country_id, res.data.state_id);
                    getCityList(res.data.state_id, res.data.city_id);
                    $("input[name=customer_type][value=" + res.data.customer_type + "]").prop('checked', true);

                    $('#description').val(res.data.description);
                    $("#customer-modal .advance-option").removeClass('d-none');
                    $("#customer-modal .advance-option-div").addClass('d-none');
                    $('.modal-title').text('Edit Lead');
                    $('#customer-modal').modal('toggle');
                }
            });
        }

        //Remove multiple record
        $('.delete_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            if (allVals.length > 50) {
                toastrWarning('Please select maximum 50 records', 'Warning');
                return false;
            }
            var join_selected_values = allVals.join(",");
            remove_id(join_selected_values, '{{route('tenant.customer.delete', ['tenant' => $segment])}}', '#customer-datatable');
        });

        $('.active_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{route('tenant.customer.edit-status', ['tenant' => $segment])}}', '#customer-datatable');
        });

        $('.deactive_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{route('tenant.customer.edit-status', ['tenant' => $segment])}}', '#customer-datatable');
        });

        $('#country_id').on('change', function (e) {
            e.preventDefault();
            let country_code = jQuery(this).find(':selected').attr('data-id')
            // $("#country_code_div").text('+'+country_code);
            $("#country_code").val('+' + country_code);
            let country_id = jQuery(this).val();
            getStatesList(country_id);

        });

        $('#state_id').on('change', function (e) {
            e.preventDefault();
            var state_id = jQuery(this).val();
            getCityList(state_id);

        });

        // function get All States
        function getStatesList(country_id, seleted_id = 0) {
            if (seleted_id == 0) {
                seleted_id = {{$expData->state_id}};
            }
            $.ajax({
                async: false,
                url: "{{url('/get-states-by-country')}}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
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
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>';
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

        // function get All Cities
        function getCityList(state_id, seleted_id = 0) {
            $.ajax({
                async: false,
                url: "{{url('get-cities-by-state')}}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
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
        }
    </script>
    <style>
        /*.th-a,.th-b,.th-c,.th-d,.th-e,.th-f,.th-g,.th-h,.th-i,.th-j,.th-k,.th-l,.th-m,.th-n,.th-o,.th-p,.th-q,.th-r,.th-s,.th-t{
            width:250px !important;
        }*/
        .select2-search__field {
            height: 26px !important;
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
