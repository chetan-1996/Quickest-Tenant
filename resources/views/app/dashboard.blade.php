@extends('app.layouts.app')
@section('title','Dashboard')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.css" type="text/css">
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <style>
        .duetoday-tbody,#opr_id_1,#opr_id_2,#opr_id_3,#popr_id_1,#popr_id_2,#popr_id_3,#popr_id_4,#ropr_id_1,#ropr_id_2,#ropr_id_3,#ropr_id_4,#ropr_id_5 {
            cursor: pointer;
        }

        .modal-backdrop {
            z-index: 999999999 !important;
        }

        .modal {
            z-index: 9999999999 !important;

        }

        .clockpicker-popover {
            z-index: 9999999999 !important;
        }

        .fc-scrollgrid-sync-table {
            width: 100% !important;
        }

        .fc-daygrid-body {
            width: 100% !important;
        }

        .card.card-fullscreen {
            display: block;
            z-index: 9999;
            position: fixed;
            width: 100% !important;
            height: 100% !important;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            overflow: auto;

        .fc-daygrid-event-dot {

            border: calc(var(--fc-daygrid-event-dot-width, 8px) / 2) solid #fff;

        }

        #calendar {
            /*width: 200px;*/
            margin: 0 auto;
            font-size: 10px;
        }

        .fc-toolbar {
            font-size: .9em;
        }

        .fc-toolbar h2 {
            font-size: 12px;
            white-space: normal !important;
        }

        /* click +2 more for popup */
        .fc-more-cell a {
            display: block;
            width: 85%;
            margin: 1px auto 0 auto;
            border-radius: 3px;
            background: grey;
            color: transparent;
            overflow: hidden;
            height: 4px;
        }

        .fc-more-popover {
            width: 100px;
        }

        .fc-view-month .fc-event, .fc-view-agendaWeek .fc-event, .fc-content {
            font-size: 0;
            overflow: hidden;
            height: 2px;
        }

        .fc-view-agendaWeek .fc-event-vert {
            font-size: 0;
            overflow: hidden;
            width: 2px !important;
        }

        .fc-agenda-axis {
            width: 20px !important;
            font-size: .7em;
        }

        .fc-button-content {
            padding: 0;
        }
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
                                <a href="javascript: void(0);" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#dashboard-setting-modal">
                                    <i class="mdi mdi-cog  mdi-18px mdi-spins" title="Dashboard Settings"></i>
                                </a>
                                {{-- <select id="fil_team_member" name="fil_team_member"
                                        class="form-select me-1 {{(in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm))?'d-none' :''}}">
                                    <option value="0">All Users</option>
                                    @foreach($users_list as $user_list)
                                        <option
                                            value="{{$user_list->id}}">{{ $user_list->id == auth()->user()->id ? 'Myself' : $user_list->name }}</option>
                                    @endforeach
                                </select>--}}
                            </div>
                            <h4 class="page-title">Dashboard</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    @if(in_array(1,$dashboard_settings))
                    <div class="col-xl-12 col-lg-12">
                        <div class="card">
                            <div class="card-body pb-0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="header-title mb-0 text-dark fw-bold">OPR Dashbaord</h4>
                                </div>
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 border-end">
                                        <div class="card mb-0 pb-0" style="box-shadow: 0 0px 0px rgb(54 64 67 / 30%), 0 0px 0px 0px rgb(54 64 67 / 15%) !important;">
                                            <div class="card-body p-0 mb-0 pb-0"><!--style="max-height: 430px!important;min-height: 430px!important;"-->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="header-title mb-0 text-dark fw-bold text-capitalizes">Open</h4>
                                                    <div class="form-control" data-toggle="date-picker-range" data-cancel-class="btn-light"
                                                        style="width:20%;!important;visibility: hidden;">
                                                        <i class="mdi mdi-calendar"></i>&nbsp;
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{-- <a class="text-muted" href="">--}}
                                                            <div class="card widget-flat border mb-2" style="border-left: 4px solid #fa5c7c !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="opr_id_1" data-id="">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="float-end"></div>
                                                                    <h5 class="text-dark fw-bold mt-0" title="Number of Overdue">Overdue</h5>
                                                                    <h4 class="mt-0 mb-0 text-primary open_opr_total_overdues">0</h4>
                                                                    <p class="mb-0 text-muted text-start"></p>
                                                                </div>
                                                            </div>
                                                        {{--</a>--}}
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{-- <a class="text-muted" href="">--}}
                                                            <div class="card widget-flat border mb-2" style="border-left: 4px solid #13a764 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="opr_id_2" data-id="">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="float-end"></div>
                                                                    <h5 class="text-dark fw-bold mt-0" title="Number of Leads">New Leads</h5>
                                                                    <h4 class="mt-0 mb-0 text-primary open_opr_total_leads">0</h4>
                                                                    <p class="mb-0 text-muted text-start"></p>
                                                                </div>
                                                            </div>
                                                        {{--  </a>--}}
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12">
                                                        {{--<a class="text-muted" href="">--}}
                                                            <div class="card widget-flat border mb-2" style="border-left: 4px solid #313a46 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="opr_id_3" data-id="">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="float-end"></div>
                                                                    <h5 class="text-dark fw-bold mt-0" title="Number of Task">Lead without Followup</h5>
                                                                    <h4 class="mt-0 mb-0 text-primary open_opr_total_tasks">0</h4>
                                                                    <p class="mb-0 text-muted text-start"></p>
                                                                </div>
                                                            </div>
                                                        {{--</a>--}}
                                                    </div>
                                                </div> <!-- end row -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 border-end">
                                        <div class="card" style="box-shadow: 0 0px 0px rgb(54 64 67 / 30%), 0 0px 0px 0px rgb(54 64 67 / 15%) !important;">
                                            <div class="card-body p-0 pb-0">  <!--style="max-height: 430px!important;min-height: 430px!important;"-->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="header-title mb-0 text-dark fw-bold text-capitalizes">Periodic</h4>
                                                    <div id="result_opr_date_range" class="form-control" data-toggle="date-picker-range"
                                                        data-target-display="#selectedValuess" data-cancel-class="btn-light"
                                                        style="width:81%;!important;">
                                                        <i class="mdi mdi-calendar"></i>&nbsp;
                                                        <span id="selectedValuess"></span> <i class="mdi mdi-menu-down"></i>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #5b63c4 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="popr_id_1" data-id="">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of New Leads">Leads Added</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_new_leads">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>

                                                    <div class="col-xl-6 col-lg-6">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #ab408b !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="popr_id_2" data-id="">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of New Leads">Estimate</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_est_count">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>

                                                    <div class="col-xl-6 col-lg-6">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #ffc107 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="popr_id_3" data-id="">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of Call">Call</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_call">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #198754 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="popr_id_4" data-id="">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of Message">Message</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_message">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>
                                                    {{--<div class="col-xl-6 col-lg-6">
                                                        --}}{{--                                            <a class="text-muted" href="">--}}{{--
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #6c757d !important;box-shadow: 0px 0px 4px #d4d6dd!important;">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of Meeting">Meeting</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_meeting">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        --}}{{--                                            </a>--}}{{--
                                                    </div>--}}

                                                    <div class="col-xl-12 col-lg-12 d-none">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #000 !important;box-shadow: 0px 0px 4px #d4d6dd!important;">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of Task">Task</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_task">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>
                                                </div> <!-- end row -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4">
                                        <div class="card mb-0 pb-0" style="box-shadow: 0 0px 0px rgb(54 64 67 / 30%), 0 0px 0px 0px rgb(54 64 67 / 15%) !important;">
                                            <div class="card-body p-0 mb-0 pb-0"><!--style="max-height: 430px!important;min-height: 430px!important;"-->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="header-title mb-0 text-dark fw-bold text-capitalizes">Result</h4>
                                                    <div id="periodic_opr_date_range" class="form-control" data-toggle="date-picker-range"
                                                        data-target-display="#selectedValuesa" data-cancel-class="btn-light"
                                                        style="width:81%;!important;">
                                                        <i class="mdi mdi-calendar"></i>&nbsp;
                                                        <span id="selectedValuesa"></span> <i class="mdi mdi-menu-down"></i>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{--  <a class="text-muted" href="">--}}
                                                            <div class="card widget-flat border mb-2" style="border-left: 4px solid #13a764 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="ropr_id_1" data-id="">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="float-end"></div>
                                                                    <h5 class="text-dark fw-bold mt-0" title="Number of Won Leads">Won Leads</h5>
                                                                    <h4 class="mt-0 mb-0 text-primary periodic_opr_won">0</h4>
                                                                    <p class="mb-0 text-muted text-start"></p>
                                                                </div>
                                                            </div>
                                                        {{--  </a>--}}
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{-- <a class="text-muted" href="">--}}
                                                            <div class="card widget-flat border mb-2" style="border-left: 4px solid #fa5c7c !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="ropr_id_2" data-id="">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="float-end"></div>
                                                                    <h5 class="text-dark fw-bold mt-0" title="Number of Lost Leads">Lost Leads</h5>
                                                                    <h4 class="mt-0 mb-0 text-primary periodic_opr_lost">0</h4>
                                                                    <p class="mb-0 text-muted text-start"></p>
                                                                </div>
                                                            </div>
                                                        {{-- </a>--}}
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 {{($main_company->company_category > 1)?'': 'd-none'}}">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #6c757d !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="ropr_id_3" data-id="">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of Meeting">Meeting</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_meeting">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 {{($main_company->company_category ==1)?'': 'd-none'}}">
                                                        {{--                                            <a class="text-muted" href="">--}}
                                                        <div class="card widget-flat border mb-2" style="border-left: 4px solid #6c757d !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="ropr_id_5" data-id="">
                                                            <div class="card-body p-2 text-center">
                                                                <div class="float-end"></div>
                                                                <h5 class="text-dark fw-bold mt-0" title="Number of Meeting">Site Visit</h5>
                                                                <h4 class="mt-0 mb-0 text-primary result_opr_site_visit">0</h4>
                                                                <p class="mb-0 text-muted text-start"></p>
                                                            </div>
                                                        </div>
                                                        {{--                                            </a>--}}
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        {{--<a class="text-muted" href="">--}}
                                                            <div class="card widget-flat border mb-2" style="border-left: 4px solid #000 !important;box-shadow: 0px 0px 4px #d4d6dd!important;" id="ropr_id_4" data-id="">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="float-end"></div>
                                                                    <h5 class="text-dark fw-bold mt-0" title="Number of Task Completed">Follow Up Completed</h5>
                                                                    <h4 class="mt-0 mb-0 text-primary periodic_opr_task">0</h4>
                                                                    <p class="mb-0 text-muted text-start"></p>
                                                                </div>
                                                            </div>
                                                        {{-- </a>--}}
                                                    </div>
                                                </div> <!-- end row -->
                                            </div>
                                        </div>

                                    </div>
                                </div> <!-- end row -->
                            </div>
                        </div>
                    </div> <!-- end col -->
                    @endif
                    @if(in_array(5,$dashboard_settings))
                    {{-- <div class="col-xl-6 col-lg-6">
                            <div class="row widget-list"></div> <!-- end row -->
                        </div> <!-- end col -->--}}
                        <div class="col-xl-6 col-lg-6">
                            <div class="card">
                                <div class="card-body pb-0" data-simplebar  style="max-height: 430px!important;min-height: 430px!important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title mb-0 text-dark fw-bold">Estimate Status</h4>

                                    </div>
                                    <div class="row widget-list"></div> <!-- end row -->
                                </div>
                            </div>
                        </div> <!-- end col -->

                    @endif

                    @if(in_array(6,$dashboard_settings))
                        <div class="col-xl-6 col-lg-6">
                            <div class="card">
                                <div class="card-body pb-0" data-simplebar  style="max-height: 430px!important;min-height: 430px!important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title mb-0 text-dark fw-bold">Lead Stages</h4>

                                    </div>
                                    <div class="row lead-stage-list"></div> <!-- end row -->
                                </div>
                            {{-- <div class="card-body py-0" data-simplebar
                                    style="max-height: 293px!important;min-height: 293px!important;">



                                </div>--}}
                            </div>
                        </div> <!-- end col -->
                    @endif

                    @if(in_array(2,$dashboard_settings))
                        <div class="col-xl-6 col-lg-6">
                            <div class="card">
                                <div class="card-body" data-simplebar style="max-height: 430px!important;min-height: 430px!important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title mb-0">Today Follow Up (<span id="cnt_duetoday"></span>)</h4>
                                        <a href="javascript:void(0);" class="text-primary"
                                        onclick="fn_follow_up_history('{{url('follow-up-history-new')}}')" class="text-info">View
                                            More<i
                                                class="uil uil-arrow-right ms-1"></i></a>
                                        </a>
                                    </div>
                            {{-- </div>

                                <div class="card-body py-0" data-simplebar  style="max-height: 435px!important;min-height: 435px!important;">--}}
                                    <div class="table-responsive">

                                        <table id="duetoday-datatable-dashboard"
                                            class="table table-centered table-hover table-sm nowrap w-100">
                                            <thead class="table-light">
                                            <tr>
                                                <th> Follow Up</th>
                                                <th>Name</th>
                                                <th>Assign To</th>
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
                                    {{--                    <div class="inbox-widget follow-up-list">--}}

                                    {{--                    </div>--}}
                                    {{--                    <input type="hidden" id="start" value="0">--}}
                                    {{--                    <input type="hidden" id="rowperpage" value="4">--}}
                                    {{--                    <div class="text-center loader">--}}
                                    {{--                        <i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div> <!-- end card-->

                        </div> <!-- end col -->
                    @endif
                    {{--</div>

                    <div class="row">--}}
                    @if(in_array(4,$dashboard_settings))
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-body" style="max-height: 454px !important;min-height: 454px !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="header-title me-1">Lead Follow Up</h4>

                                            {{--<select id="fil_user_id" name="fil_user_id" class="form-select me-1 {{(in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm))?'d-none' :''}}">
                                                <option value="0">All Users</option>
                                                @foreach($users_list as $user_list)
                                                    <option value="{{$user_list->id}}" {{ $user_list->id == auth()->user()->id ? 'selected' : ''; }}>{{ $user_list->id == auth()->user()->id ? 'Myself' : $user_list->name }} [{{$user_list->email}}]</option>
                                                @endforeach
                                            </select>--}}
                                            <div id="sales_performance_date_range" class="form-control" data-toggle="date-picker-range"
                                                data-target-display="#selectedValues" data-cancel-class="btn-light"
                                                style="width:50%;!important;">
                                                <i class="mdi mdi-calendar"></i>&nbsp;
                                                <span id="selectedValues"></span> <i class="mdi mdi-menu-down"></i>
                                            </div>
                                        </div>

                                        <div id="sales-performance-chart" class="apex-charts mt-3"
                                            data-colors="#727cf5,#0acf97,#ffbc00,#fa5c7c"></div>

                                        <div class="row text-center mt-2">
                                            <div class="col-6">
                                                <h4 class="fw-normal">
                                                    <span id="total_task_span">0</span>
                                                </h4>
                                                <p class="text-muted mb-0">Total Task</p>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="fw-normal">
                                                    <span id="completed_task_span">0</span>
                                                </h4>
                                                <p class="text-muted mb-0">Completed Task</p>
                                            </div>
                                        </div>
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        @endif
                    @if(in_array(3,$dashboard_settings))
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body" data-simplebar  style="max-height: 430px!important;min-height: 430px!important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title">Estimate</h4>
                                        <div>
                                            <select class="form-select form-select-sm" aria-label=".form-select-sm example"
                                                    onchange="barChart(this.value)">
                                                <option selected value="{{$bar_chart_filter['current_fiscal_year']}}">This Fiscal
                                                    Year
                                                </option>
                                                <option value="{{$bar_chart_filter['previous_fiscal_year']}}">Previous Fiscal Year
                                                </option>
                                                <option value="{{$bar_chart_filter['last_twelve_month']}}">Last 12 Months</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div dir="ltr">
                                        <div id="chart" class="apex-charts" data-colors="#ced1ff,#727cf5"></div>
                                    </div>
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    @endif

                        <form action="{{ route('tenant.upload', ['tenant' => $segment]) }}" method="post" enctype="multipart/form-data" class="d-none">
                        @csrf <!-- This is necessary for CSRF protection -->
                            <input type="file" name="file" id="file">
                            <input type="submit" name="submit" value="Upload">
                        </form>
                </div>

                @if(!(in_array(1,$dashboard_settings) || in_array(2,$dashboard_settings) || in_array(3,$dashboard_settings) || in_array(4,$dashboard_settings) || in_array(5,$dashboard_settings)|| in_array(6,$dashboard_settings)))
                    <div class="container-fluid">
                        <div class="row justify-content-center align-items-center" style="min-height: 82vh;">
                            <div class="col-lg-4">
                                <div class="text-center">
                                    <!-- Your content goes here -->
                                    <!-- For example: -->
                                    {{-- <h5 class="text-error mt-1">Empathy!</h5>--}}
                                    <h3 class="text-dark mt-1">Empty!</h3>
                                    <p class="text-dark mt-1">Please select dashboard settings</p>
                                    <a href="javascript: void(0);" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#dashboard-setting-modal">
                                        <i class="mdi mdi-cog mdi-18px mdi-spins"></i> Open Settings
                                    </a>
                                </div> <!-- end /.text-center-->
                            </div> <!-- end col-->
                        </div> <!-- end row -->
                    </div> <!-- end container-fluid -->
                @endif

                <div id="dashboard-setting-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content">
                            <div class="modal-header border-1 bg-light">
                                <h3 class="modal-title text-dark">Dashboard Settings</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="dashboard-setting-form bg-light  p-3 p-0" id="dashboard-setting-form" action="#">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ds_1"
                                                    name="data[0][permission_id]"
                                                    value="1" {{(in_array(1,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_1">OPR Dashboard</label>
                                            </div>
                                        </div>
                                        <hr class="bg-dark my-2">
                                        <div class="col-12">
                                            {{--<div class="form-check p-0">
                                                <input type="checkbox" class="form-check-input" id="ds_1"
                                                    name="data[0][permission_id]"
                                                    value="1" {{(in_array(1,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_1">OPR Dashboard</label>
                                            </div>--}}


                                            {{--<div class="form-check p-0">
                                                <table class="table table-centered table-borderless mb-0 table-sm">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" id="ds_5"
                                                                    name="data[1][permission_id]"
                                                                    value="5" {{(in_array(5,$dashboard_settings))? 'checked' : ''}}>
                                                                <label class="form-check-label" for="ds_5">Estimate Status</label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" id="ds_6"
                                                                    name="data[2][permission_id]"
                                                                    value="6" {{(in_array(6,$dashboard_settings))? 'checked' : ''}}>
                                                                <label class="form-check-label" for="ds_6">Lead Stages</label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    --}}{{--<tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" id="ds_6">
                                                                <label class="form-check-label" for="ds_6">Lead Stage</label>
                                                            </div>
                                                        </td>
                                                    </tr>--}}{{--
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>--}}


                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ds_5"
                                                    name="data[1][permission_id]"
                                                    value="5" {{(in_array(5,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_5">Estimate Status</label>
                                            </div>
                                        </div>
                                        <hr class="bg-dark my-2">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ds_6"
                                                    name="data[2][permission_id]"
                                                    value="6" {{(in_array(6,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_6">Lead Stages</label>
                                            </div>
                                        </div>


                                        <hr class="bg-dark my-2">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ds_2"
                                                    name="data[3][permission_id]"
                                                    value="2" {{(in_array(2,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_2">Today Follow-ups</label>
                                            </div>
                                        </div>
                                        <hr class="bg-dark my-2">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ds_3"
                                                    name="data[4][permission_id]" value="3" <input type="checkbox"
                                                                                                    class="form-check-input"
                                                                                                    id="ds_2"
                                                                                                    name="data[3][permission_id]"
                                                                                                    value="3" {{(in_array(3,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_3">Estimate Value vs Accept</label>
                                            </div>
                                        </div>
                                        <hr class="bg-dark my-2">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ds_4"
                                                    name="data[5][permission_id]" value="4" <input type="checkbox"
                                                                                                    class="form-check-input"
                                                                                                    id="ds_2"
                                                                                                    name="data[3][permission_id]"
                                                                                                    value="2" {{(in_array(4,$dashboard_settings))? 'checked' : ''}}>
                                                <label class="form-check-label" for="ds_4"> Lead Follow Up</label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                                    </button>
                                    <button class="btn btn-primary" form="dashboard-setting-form" id="ds_button" type="submit">
                                        <i class="mdi mdi-floppy fs-5"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            </div>
        </div>
    </div>
{{--    <div class="row">--}}
        @endsection
        @push('scripts')
            <!-- <script src="{{ asset('js/vendor.min.js')}}"></script> -->
            <!-- <script async src="{{ asset('js/app.min.js')}}"></script> -->
            <!-- third party js -->
            
        @include('layouts.partials.datatable-script')
        <!-- third party js ends -->
            <script src="{{ asset('vendor/chart.js/Chart.bundle.min.js')}}"></script>
            <!-- demo app -->
            <script src="{{ asset('vendor/apexcharts/apexcharts.min.js')}}"></script>
            <script src="{{ asset('vendor/demo/demo.chartjs.js')}}"></script>
            <script src="{{ asset('vendor/demo/demo.dashboard-analytics.js')}}"></script>
            <!-- end demo js-->
            <script src="{{ asset('js/custom.js')}}"></script>          
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>                                  
            <script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/jquery-clockpicker.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
            
            <script>

                function getOpenOprDashboard(fil_user_id) {
                    $.ajax({
                        // async: false,
                        type: "GET",
                        url: SITEURL + '/get-open-opr-dashboard',
                        data: {
                            fil_user_id: localStorage.getItem('fil_user_id')
                        },
                        dataType: "json",
                        beforeSend: function () {
                            /*$(".lead-stage-list").html('<div class="text-center">' +
                                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>' +
                                '</div>');*/
                            $(".open_opr_total_overdues").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".open_opr_total_leads").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".open_opr_total_tasks").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');

                        },
                        success: function (data, textStatus, jqXHR) {
                            console.log(data.response);
                            $(".open_opr_total_overdues").html(data.overdue_follow_up);
                            $(".open_opr_total_leads").html(data.total_lead);
                            $(".open_opr_total_tasks").html(data.total_task);
                            $("#opr_id_1").attr('data-id',data.overdue_follow_up_ids);
                            $("#opr_id_2").attr('data-id',data.total_lead_ids);
                            $("#opr_id_3").attr('data-id',data.total_task_ids);
                            // $(".lead-stage-list").html(htmlStr);

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
                            $(".open_opr_total_overdues").html(0);
                            $(".open_opr_total_leads").html(0);
                            $(".open_opr_total_tasks").html(0);
                        },
                        complete: function (data) {
                        }
                    });

                }

                function getResultOprDashboard(date_range,fil_user_id) {
                    $.ajax({
                        // async: false,
                        type: "GET",
                        url: SITEURL + '/get-result-opr-dashboard',
                        data: {
                            fil_user_id: localStorage.getItem('fil_user_id'),
                            date: date_range
                        },
                        dataType: "json",
                        beforeSend: function () {
                            /*$(".lead-stage-list").html('<div class="text-center">' +
                                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>' +
                                '</div>');*/

                            $(".result_opr_call").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".result_opr_message").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".result_opr_new_leads").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".result_opr_est_count").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".result_opr_task").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                        },
                        success: function (data, textStatus, jqXHR) {

                            $(".result_opr_call").html(data.response);
                            $(".result_opr_message").html(data.message_count);
                            // $(".result_opr_meeting").html((data.response.length >0)?data.response[0].activity_type_count:0);
                            $(".result_opr_new_leads").html(data.new_lead_count);
                            $(".result_opr_est_count").html(data.est_count);
                            $(".result_opr_task").html(data.total_task);

                            $("#popr_id_1").attr('data-id',data.new_lead_count_ids);
                            $("#popr_id_2").attr('data-id',data.total_lead_ids);
                            $("#popr_id_3").attr('data-id',data.response_ids);
                            $("#popr_id_4").attr('data-id',data.message_count_ids);
                            // $(".lead-stage-list").html(htmlStr);

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
                            $(".result_opr_call").html(0);
                            $(".result_opr_message").html(0);
                            $(".result_opr_new_leads").html(0);
                            $(".result_opr_est_count").html(0);
                            $(".result_opr_task").html(0);
                        },
                        complete: function (data) {
                        }
                    });

                }

                function getPeriodicOprDashboard(date_range,fil_user_id) {
                    $.ajax({
                        // async: false,
                        type: "GET",
                        url: SITEURL + '/get-periodic-opr-dashboard',
                        data: {
                            fil_user_id: localStorage.getItem('fil_user_id'),
                            date: date_range
                        },
                        dataType: "json",
                        beforeSend: function () {
                            /*$(".lead-stage-list").html('<div class="text-center">' +
                                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>' +
                                '</div>');*/
                            $(".periodic_opr_won").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".periodic_opr_lost").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".periodic_opr_task").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".result_opr_meeting").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                            $(".result_opr_site_visit").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>');
                        },
                        success: function (data, textStatus, jqXHR) {
                            $(".periodic_opr_won").html(data.lead_won_count);
                            $(".periodic_opr_lost").html(data.lead_lost_count);
                            $(".periodic_opr_task").html(data.total_task);
                            $(".result_opr_meeting").html(data.response);
                            $(".result_opr_site_visit").html(data.response_visit);

                            $("#ropr_id_1").attr('data-id',data.lead_won_count_ids);
                            $("#ropr_id_2").attr('data-id',data.lead_lost_count_ids);
                            $("#ropr_id_4").attr('data-id',data.total_task_ids);
                            $("#ropr_id_3").attr('data-id',data.response_ids);
                            $("#ropr_id_5").attr('data-id',data.response_visit_ids);
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
                            $(".periodic_opr_won").html(0);
                            $(".periodic_opr_lost").html(0);
                            $(".periodic_opr_task").html(0);
                            $(".result_opr_meeting").html(0);
                            $(".result_opr_site_visit").html(0);
                        },
                        complete: function (data) {
                        }
                    });

                }

                function getWidget(fil_estimate_start, fil_estimate_end, fil_user_id) {
                    $('#estimate_date_range span').html(moment(fil_estimate_start, "YYYY-MM-DD").format('MMMM D, YYYY') + ' - ' + moment(fil_estimate_end, "YYYY-MM-DD").format('MMMM D, YYYY'));
                    $.ajax({
                        // async: false,
                        type: "GET",
                        url: SITEURL + '/get-widget',
                        data: {
                            fil_estimate_start: moment(fil_estimate_start).format('YYYY-MM-DD'),
                            fil_estimate_end: moment(fil_estimate_end).format('YYYY-MM-DD'),
                            fil_user_id: fil_user_id
                        },
                        dataType: "json",
                        beforeSend: function () {
                            $('#preloader').show();
                            $('#status').show();
                            $(".widget-list").html('<div class="text-center">' +
                                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>' +
                                '</div>');
                        },
                        success: function (data, textStatus, jqXHR) {
                            let htmlStr = '';
                            $.each(data.widgets, function (i, val) {


                                let sel_bg_color = '';
                                let status = '';
                                let nickname = val.status
                                if (val.status == 'Accept') {
                                    status = '<i class="mdi mdi-checkbox-marked-circle-outline widget-icon rounded-circle"></i>';
                                    nickname = 'Accepted';
                                    sel_bg_color = '#0acf97';
                                }
                                if (val.status == 'Decline') {
                                    status = '<i class="mdi mdi-close-box-multiple-outline widget-icon rounded-circle"></i>';
                                    nickname = 'Declined';
                                    sel_bg_color = '#fa5c7c';
                                }
                                if (val.status == 'Inprogress'){
                                    status = '<i class="mdi mdi-progress-pencil widget-icon rounded-circle"></i>';
                                    sel_bg_color = '#ffbc00';
                                }

                                if (val.status == 'Sent'){
                                    status = '<i class="mdi mdi-email-check-outline widget-icon rounded-circle"></i>';
                                    sel_bg_color = 'text-primary';
                                }

                                if (val.status == 'Draft') {
                                    status = '<i class="mdi mdi-lead-pencil widget-icon rounded-circle"></i>';
                                    sel_bg_color = '#6c757d';
                                }

                                if (val.status == 'Total') {
                                    status = '<i class="mdi mdi-equal widget-icon rounded-circle"></i>';
                                    nickname = 'Total Estimates';
                                    sel_bg_color = '#313a46';
                                }

                                let widgetTotal = (data.total) ? ((val.widget_total * 100) / data.total).toFixed(2) : 0.00;
                                htmlStr += '<div class="col-sm-6">\n' +
                                    '<a class="text-muted" href="{{url('/quotes')}}?status=' + val.status + '">\n' +

                                    '<div class="card widget-flat border mb-2" style="border-left: 4px solid '+sel_bg_color+' !important;box-shadow: 0px 0px 4px #d4d6dd!important;">\n' +
                                    '<div class="card-body p-2">\n' +
                                    '<div class="float-end">\n' +
                                    '<p class="mb-0 text-muted text-start">\n' +
                                    '<span class="text-muted me-2">' + widgetTotal + '%</span>\n' +
                                    '</p>\n' +
                                    // status +
                                    '</div>\n' +
                                    '<h5 class="text-dark fw-bold mt-0" title="Number of Customers">' + nickname + '</h5>\n' +
                                    '<h4 class="mt-0 mb-0 text-primary">' + val.widget_total + '</h4>\n' +
                                   /* '<p class="mb-0 text-muted text-start">\n' +
                                    '<span class="text-primary me-2">' + widgetTotal + '%</span>\n' +
                                    '</p>\n' +*/
                                    '</div>\n' +
                                    '</div>\n' +
                                    '</a>\n' +
                                    '</div>';

                            });
                            $(".widget-list").html(htmlStr);
                            $('#preloader').hide();
                            $('#status').hide();

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

                function getLeadStage(fil_estimate_start, fil_estimate_end, fil_user_id) {
                    $('#estimate_date_range span').html(moment(fil_estimate_start, "YYYY-MM-DD").format('MMMM D, YYYY') + ' - ' + moment(fil_estimate_end, "YYYY-MM-DD").format('MMMM D, YYYY'));
                    $.ajax({
                        // async: false,
                        type: "GET",
                        url: SITEURL + '/get-lead-stage',
                        data: {
                            fil_estimate_start: moment(fil_estimate_start).format('YYYY-MM-DD'),
                            fil_estimate_end: moment(fil_estimate_end).format('YYYY-MM-DD'),
                            fil_user_id: fil_user_id
                        },
                        dataType: "json",
                        beforeSend: function () {
                            $('#preloader').show();
                            $('#status').show();
                            $(".lead-stage-list").html('<div class="text-center">' +
                                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-prrimary"></i>' +
                                '</div>');
                        },
                        success: function (data, textStatus, jqXHR) {
                            let htmlStr = '';
                            $.each(data.widgets, function (i, val) {

                                let status = '';
                                let nickname = val.name
                                /*if (val.status == 'Accept') {
                                    status = '<i class="mdi mdi-checkbox-marked-circle-outline widget-icon rounded-circle"></i>';
                                    nickname = 'Accepted';
                                }
                                if (val.status == 'Decline') {
                                    status = '<i class="mdi mdi-close-box-multiple-outline widget-icon rounded-circle"></i>';
                                    nickname = 'Declined';
                                }
                                if (val.status == 'Inprogress')
                                    status = '<i class="mdi mdi-progress-pencil widget-icon rounded-circle"></i>';

                                if (val.status == 'Sent')
                                    status = '<i class="mdi mdi-email-check-outline widget-icon rounded-circle"></i>';

                                if (val.status == 'Draft')
                                    status = '<i class="mdi mdi-lead-pencil widget-icon rounded-circle"></i>';

                                if (val.status == 'Total') {
                                    status = '<i class="mdi mdi-equal widget-icon rounded-circle"></i>';
                                    nickname = 'Total Estimates';
                                }*/

                                htmlStr += '<div class="col-sm-6">\n' +
                                    '<a class="text-muted" href="{{url('/lead')}}?fil_lead_stage_id=' + val.id + '">\n' + //href="{{url('/quotes')}}?status=' + val.name + '"
                                    '<div class="card widget-flat border mb-2" style="border-left: 4px solid '+val.color_code+' !important;box-shadow: 0px 0px 4px #d4d6dd!important;">\n' +
                                    '<div class="card-body p-2 text-center">\n' +
                                    '<div class="float-end">\n' +
                                    status +
                                    '</div>\n' +
                                    '<h5 class="text-dark fw-bold mt-0" title="Number of Customers">' + nickname + '</h5>\n' +
                                    '<h4 class="mt-0 mb-0 text-primary" style="color:'+val.color_code+'">' + val.widget_total + '</h4>\n' +
                                    '<p class="mb-0 text-muted text-start">\n' +
                                    //'<span class="text-primary me-2">%</span>\n' +
                                    '</p>\n' +
                                    '</div>\n' +
                                    '</div>\n' +
                                    '</a>\n' +
                                    '</div>';

                            });
                            $(".lead-stage-list").html(htmlStr);
                            $('#preloader').hide();
                            $('#status').hide();

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

                $(function () {
                    $("#opr_id_1").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let opr_id1 = $(this).attr('data-id');
                        localStorage.setItem('opr_id_1', opr_id1);
                        if (opr_id1 !== undefined && opr_id1 !== null && opr_id1 != 0 && opr_id1 != '') {
                            window.location.href = '{{url('follow-up-history-new')}}?q=opr_overdue';
                        }
                    });

                    $("#opr_id_2").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let opr_id2 = $(this).attr('data-id');
                        localStorage.setItem('opr_id_2', opr_id2);
                        if (opr_id2 !== undefined && opr_id2 !== null && opr_id2 != 0 && opr_id2 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_new_leads';
                        }
                    });

                    $("#opr_id_3").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let opr_id3 = $(this).attr('data-id');
                        localStorage.setItem('opr_id_3', opr_id3);
                        if (opr_id3 !== undefined && opr_id3 !== null && opr_id3 != 0 && opr_id3 != '') {
                            window.location.href = '{{url('follow-up-history-new')}}?q=opr_lead_without_followup';
                        }

                    });

                    $("#popr_id_1").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let popr_id1 = $(this).attr('data-id');
                        localStorage.setItem('popr_id_1', popr_id1);
                        if (popr_id1 !== undefined && popr_id1 !== null && popr_id1 != 0 && popr_id1 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_new_lead';
                        }
                    });

                    $("#popr_id_2").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let popr_id2 = $(this).attr('data-id');
                        localStorage.setItem('popr_id_2', popr_id2);
                    });

                    $("#popr_id_3").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let popr_id3 = $(this).attr('data-id');
                        localStorage.setItem('popr_id_3', popr_id3);
                        if (popr_id3 !== undefined && popr_id3 !== null && popr_id3 != 0 && popr_id3 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_call';
                        }

                    });

                    $("#popr_id_4").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let popr_id4 = $(this).attr('data-id');
                        localStorage.setItem('popr_id_4', popr_id4);
                        if (popr_id4 !== undefined && popr_id4 !== null && popr_id4 != 0 && popr_id4 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_message';
                        }

                    });

                    $("#ropr_id_1").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let ropr_id1 = $(this).attr('data-id');
                        localStorage.setItem('ropr_id_1', ropr_id1);
                        if (ropr_id1 !== undefined && ropr_id1 !== null && ropr_id1 != 0 && ropr_id1 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_lead_won';
                        }
                    });

                    $("#ropr_id_2").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let ropr_id2 = $(this).attr('data-id');
                        localStorage.setItem('ropr_id_2', ropr_id2);
                        if (ropr_id2 !== undefined && ropr_id2 !== null && ropr_id2 != 0 && ropr_id2 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_lead_lost';
                        }
                    });

                    $("#ropr_id_3").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let ropr_id3 = $(this).attr('data-id');
                        localStorage.setItem('ropr_id_3', ropr_id3);
                        if (ropr_id3 !== undefined && ropr_id3 !== null && ropr_id3 != 0 && ropr_id3 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_meeting';
                        }

                    });

                    $("#ropr_id_5").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let ropr_id5 = $(this).attr('data-id');
                        localStorage.setItem('ropr_id_5', ropr_id5);
                        if (ropr_id5 !== undefined && ropr_id5 !== null && ropr_id5 != 0 && ropr_id5 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_site_visit';
                        }

                    });

                    $("#ropr_id_4").on("click", function(event) {
                        event.preventDefault(); // Prevent default click handling
                        let ropr_id4 = $(this).attr('data-id');
                        localStorage.setItem('ropr_id_4', ropr_id4);
                        if (ropr_id4 !== undefined && ropr_id4 !== null && ropr_id4 != 0 && ropr_id4 != '') {
                            window.location.href = '{{url('lead')}}?q=opr_followup_completed';
                        }

                    });

                    $('#dashboard-setting-modal').on('shown.bs.modal', function () {

                        // $('body').attr('offcanvas-open');
                        // var backdropElements = document.querySelectorAll('.offcanvas-backdrop');
                        var backdropElements = $('.modal-backdrop');
                        if (backdropElements.length > 2) {
                            backdropElements[0].parentNode.removeChild(backdropElements[0]);
                            backdropElements[1].parentNode.removeChild(backdropElements[1]);
                            // $('#dashboard-setting-modal').css('visibility', 'visible');
                        }
                        $('#dashboard-setting-modal').css('visibility', 'visible');
                    });
                    $('#dashboard-setting-modal').on('hidden.bs.modal', function () {
                        $('body').removeClass('modal-open');
                    });
                });

                $(document).ready(function () {
                    $(".duetoday-tbody").on('click', 'tr', function () {
                        var id = $(this).attr('data-id');
                        location.href = SITEURL + '/lead/timeline/' + id;
                    });

                    var fil_estimate_start = moment().subtract(29, 'days');
                    var fil_estimate_end = moment();

                    if (localStorage.hasOwnProperty("fil_estimate_start")) {
                        fil_estimate_start = moment(localStorage.getItem('fil_estimate_start'));
                    }
                    if (localStorage.hasOwnProperty("fil_estimate_end")) {
                        fil_estimate_end = moment(localStorage.getItem('fil_estimate_end'));
                    }

                    var fil_sp_chart_start = moment();
                    var fil_sp_chart_end = moment();
                    // var fil_user_id = $('#fil_user_id').val();
                    var fil_user_id = {{(in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm))?auth()->user()->id :0}};

                    if (localStorage.hasOwnProperty("fil_sp_chart_start")) {
                        fil_sp_chart_start = moment(localStorage.getItem('fil_sp_chart_start'));
                    } else {
                        localStorage.setItem('fil_sp_chart_start', fil_sp_chart_start);
                    }

                    if (localStorage.hasOwnProperty("fil_sp_chart_end")) {
                        fil_sp_chart_end = moment(localStorage.getItem('fil_sp_chart_end'));
                    } else {
                        localStorage.setItem('fil_sp_chart_end', fil_sp_chart_end);
                    }

                    var fil_result_opr_start = moment();
                    var fil_result_opr_end = moment();

                    if (localStorage.hasOwnProperty("fil_result_opr_start")) {
                        fil_result_opr_start = moment(localStorage.getItem('fil_result_opr_start'));
                    } else {
                        localStorage.setItem('fil_result_opr_start', fil_result_opr_start);
                    }

                    if (localStorage.hasOwnProperty("fil_result_opr_end")) {
                        fil_result_opr_end = moment(localStorage.getItem('fil_result_opr_end'));
                    } else {
                        localStorage.setItem('fil_result_opr_end', fil_result_opr_end);
                    }

                    var fil_periodic_opr_start = moment();
                    var fil_periodic_opr_end = moment();

                    if (localStorage.hasOwnProperty("fil_periodic_opr_start")) {
                        fil_periodic_opr_start = moment(localStorage.getItem('fil_periodic_opr_start'));
                    } else {
                        localStorage.setItem('fil_periodic_opr_start', fil_periodic_opr_start);
                    }

                    if (localStorage.hasOwnProperty("fil_periodic_opr_end")) {
                        fil_periodic_opr_end = moment(localStorage.getItem('fil_periodic_opr_end'));
                    } else {
                        localStorage.setItem('fil_periodic_opr_end', fil_periodic_opr_end);
                    }

                    if (localStorage.hasOwnProperty("fil_user_id")) {
                        fil_user_id = localStorage.getItem('fil_user_id');
                        $('#fil_team_member').val(fil_user_id);
                    } else {
                        localStorage.setItem('fil_user_id', fil_user_id);

                    }


                    getOpenOprDashboard(fil_user_id);
                    // getResultOprDashboard(fil_user_id);
                    getWidget(moment(fil_estimate_start).format('YYYY-MM-DD'), moment(fil_estimate_end).format('YYYY-MM-DD'), fil_user_id);
                    getLeadStage(moment(fil_estimate_start).format('YYYY-MM-DD'), moment(fil_estimate_end).format('YYYY-MM-DD'), fil_user_id);
                    $('#followup_date').datepicker({
                        startDate: new Date(),
                        format: "dd/mm/yyyy",
                        autoclose: true,
                        daysOfWeekDisabled: [0, 7]
                    });


                    var fil_bar_chart_start = '{{$bar_chart_filter['fd']}}';
                    var fil_bar_chart_end = '{{$bar_chart_filter['ed']}}';

                    if (localStorage.hasOwnProperty("fil_bar_chart_start")) {
                        fil_bar_chart_start = moment(localStorage.getItem('fil_bar_chart_start'));
                    } else {
                        // localStorage.setItem('fil_bar_chart_start', fil_bar_chart_start.format('YYYY-MM-DD'));
                        localStorage.setItem('fil_bar_chart_start', moment(fil_bar_chart_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                    }
                    if (localStorage.hasOwnProperty("fil_bar_chart_end")) {
                        fil_bar_chart_end = moment(localStorage.getItem('fil_bar_chart_end'));
                    } else {
                        localStorage.setItem('fil_bar_chart_end', moment(fil_bar_chart_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                    }

                    function cb(fil_sp_chart_start, fil_sp_chart_end, flg = 0) {
                        $('#sales_performance_date_range span').html(fil_sp_chart_start.format('MMMM D, YYYY') + ' - ' + fil_sp_chart_end.format('MMMM D, YYYY'));
                        let date_range = fil_sp_chart_start.format('YYYY-MM-DD') + '_' + fil_sp_chart_end.format('YYYY-MM-DD');
                        localStorage.setItem('fil_sp_chart_start', moment(fil_sp_chart_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                        localStorage.setItem('fil_sp_chart_end', moment(fil_sp_chart_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                        let fil_sp_user_id = localStorage.getItem('fil_user_id')
                        salesPerformanceChart(date_range, fil_sp_user_id, '{{route('tenant.chart.salesPerformanceChart', ['tenant' => $segment])}}');
                    }

                    function cb_result_opr(fil_result_opr_start, fil_result_opr_end, flg = 0) {
                        $('#result_opr_date_range span').html(fil_result_opr_start.format('MMMM D, YYYY') + ' - ' + fil_result_opr_end.format('MMMM D, YYYY'));
                        let date_range = fil_result_opr_start.format('YYYY-MM-DD') + '_' + fil_result_opr_end.format('YYYY-MM-DD');
                        localStorage.setItem('fil_result_opr_start', moment(fil_result_opr_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                        localStorage.setItem('fil_result_opr_end', moment(fil_result_opr_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                        getResultOprDashboard(date_range, localStorage.getItem('fil_user_id'));
                    }
                    function cb_periodic_opr(fil_periodic_opr_start, fil_periodic_opr_end, flg = 0) {
                        $('#periodic_opr_date_range span').html(fil_periodic_opr_start.format('MMMM D, YYYY') + ' - ' + fil_periodic_opr_end.format('MMMM D, YYYY'));
                        let date_range = fil_periodic_opr_start.format('YYYY-MM-DD') + '_' + fil_periodic_opr_end.format('YYYY-MM-DD');
                        localStorage.setItem('fil_periodic_opr_start', moment(fil_periodic_opr_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                        localStorage.setItem('fil_periodic_opr_end', moment(fil_periodic_opr_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                        getPeriodicOprDashboard(date_range, localStorage.getItem('fil_user_id'));
                    }

                    // function cbBar(fil_bar_chart_start, fil_bar_chart_end) {
                    //     let date_range = fil_bar_chart_start.format('YYYY-MM-DD') + '_' + fil_bar_chart_end.format('YYYY-MM-DD');
                    //     localStorage.setItem('fil_bar_chart_start', fil_bar_chart_start.format('YYYY-MM-DD'));
                    //     localStorage.setItem('fil_bar_chart_end', fil_bar_chart_end.format('YYYY-MM-DD'));
                    //     barChart(date_range);
                    // }
                    barChart(moment(fil_bar_chart_start, 'YYYY-MM-DD').format("YYYY-MM-DD") + '_' + moment(fil_bar_chart_end, 'YYYY-MM-DD').format("YYYY-MM-DD"), fil_user_id);

                    $('#sales_performance_date_range').daterangepicker({
                        startDate: fil_sp_chart_start,
                        endDate: fil_sp_chart_end,
                        // "drops": "up",
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                            'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse(auth()->user()->created_at)->diffInDays())}}, 'days'), moment()],
                        }
                    }, cb);

                    $('#result_opr_date_range').daterangepicker({
                        startDate: fil_result_opr_start,
                        endDate: fil_result_opr_end,
                        // "drops": "up",
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                            'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse(auth()->user()->created_at)->diffInDays())}}, 'days'), moment()],
                        }
                    }, cb_result_opr);

                    $('#periodic_opr_date_range').daterangepicker({
                        startDate: fil_periodic_opr_start,
                        endDate: fil_periodic_opr_end,
                        // "drops": "up",
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                            'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse(auth()->user()->created_at)->diffInDays())}}, 'days'), moment()],
                        }
                    }, cb_periodic_opr);

                    cb(fil_sp_chart_start, fil_sp_chart_end);
                    cb_result_opr(fil_result_opr_start, fil_result_opr_end);
                    cb_periodic_opr(fil_periodic_opr_start, fil_periodic_opr_end);

                    "use strict";
                    var table = $("#duetoday-datatable-dashboard").DataTable({
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
                        searching: false,
                        info: true,
                        lengthChange: !1,
                        buttons: [
                            /*{
                                extend: 'pageLength',
                                attr: {
                                    class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                                },
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },*/
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
                            },*/
                            /*{
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
                            }*/
                        ],
                        language: {
                            paginate: {
                                previous: "<i class='mdi mdi-chevron-left'>",
                                next: "<i class='mdi mdi-chevron-right'>"
                            }
                        },
                        stateSaveParams: function (settings, data) {
                            data.fil_status = $('#fil_status').val();
                            data.fil_type = $('#fil_type').val();
                            data.fil_name = $('#fil_name').val();
                            data.fil_team_member = $('#fil_team_member').val();
                        },
                        stateLoadParams: function (settings, data) {
                            $('#fil_status').val(data.fil_status);
                            $('#fil_type').val(data.fil_type);
                            $('#fil_name').val(data.fil_name);
                            $('#fil_team_member').val(data.fil_team_member);
                        },
                        stateSaveCallback: function (settings, data) {
                            localStorage.setItem(settings.sInstance, JSON.stringify(data))
                        },
                        stateLoadCallback: function (settings) {
                            return JSON.parse(localStorage.getItem(settings.sInstance))
                        },
                        ajax: {
                            url: "{{ route('tenant.follow-up-history.dashboard.index', ['tenant' => $segment]) }}",
                            data: function (d) {
                                d.status = $('#fil_status').val(),
                                    d.assigned_to_user = localStorage.getItem('fil_user_id'),
                                    d.customer_type = $('#fil_type').val(),
                                    d.name = $('#fil_name').val(),
                                    d.search = $('#duetoday-datatable-dashboard_filter input[type="search"]').val()
                            }
                        },
                        "order": [[0, "desc"]],
                        "columnDefs": [{
                            "className": "label_td",
                            "targets": [3]
                        }],


                        columns: [
                            {data: 'last_follow_up_datetime', name: 'last_follow_up_datetime'},
                            {
                                data: 'name', name: 'name',
                                render: function (data, type, row) {
                                    let country_code = '';
                                    if (row.country_code) {
                                        country_code = row.country_code;
                                    }
                                    return '<td>' +
                                        '<h5 class="font-19 mb-1 fw-bold">' + funcStrLimits(row.name, 15, 0) + '</h5>' +
                                        '<span class="text-muted font-10">' + country_code + row.phone_no + '</span>' +
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
                            {
                                data: 'last_activity', name: 'last_activity', visible: true,
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
                                        last_activity =last_activity_name + ' - ' + time_ago_string + last_activity;
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

                                    /*if (last_activity_type == 9) {
                                        ficon = '<i class="mdi mdi-calendar text-primary timeline-icon rounded-circle widget-icon-md"></i>';
                                        let tmp_last_activity = '';
                                        if (last_activity) {
                                            tmp_last_activity = ' - ' + last_activity;
                                        }
                                        last_activity = ficon + ' ' + last_activity_name + ' - ' + time_ago_string + tmp_last_activity;
                                    } // follow up*/

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
                            {
                                data: 'estimate_status', name: 'estimate_status', visible: false,
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
                            {data: 'net_amount', name: 'net_amount', visible: false},

                        ],
                        drawCallback: function () {
                            $("#cnt_duetoday").text(table.page.info().recordsTotal);
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                        },
                        createdRow: function (row, data, dataIndex) {
                            // Set the data-status attribute, and add a class
                            $(row).attr('data-id', data.action);

                        }
                    });
                    table.on('click', 'tr', function () {
                        var id = $(this).attr('data-id');
                        location.href = SITEURL + '/lead/timeline/' + id;
                    });

                    table.buttons().container().appendTo("#duetoday-datatable-dashboard_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
                        pagingType: "full_numbers",
                        drawCallback: function () {
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                        }
                    })

                    $('#fil_team_member').on('change', function () {
                        let fil_user_id = $(this).val();
                        localStorage.setItem('fil_user_id', fil_user_id);
                        getOpenOprDashboard(fil_user_id);
                        // getResultOprDashboard(fil_user_id);
                        cb(moment(localStorage.getItem('fil_sp_chart_start')), moment(localStorage.getItem('fil_sp_chart_end')));
                        cb_result_opr(moment(localStorage.getItem('fil_result_opr_start')), moment(localStorage.getItem('fil_result_opr_end')));
                        cb_periodic_opr(moment(localStorage.getItem('fil_periodic_opr_start')), moment(localStorage.getItem('fil_periodic_opr_end')));
                        getWidget(moment(fil_estimate_start).format('YYYY-MM-DD'), moment(fil_estimate_end).format('YYYY-MM-DD'), fil_user_id);
                        getLeadStage(moment(fil_estimate_start).format('YYYY-MM-DD'), moment(fil_estimate_end).format('YYYY-MM-DD'), fil_user_id);
                        barChart(moment(fil_bar_chart_start, 'YYYY-MM-DD').format("YYYY-MM-DD") + '_' + moment(fil_bar_chart_end, 'YYYY-MM-DD').format("YYYY-MM-DD"), fil_user_id);
                        table.draw();

                        if (typeof (Storage) !== "undefined") {
                            // Retrieve the existing data from localStorage
                            var data = localStorage.getItem('duetoday-datatable'); // Replace 'your_key' with the actual key name
                            var dataA = localStorage.getItem('upcoming-datatable'); // Replace 'your_key' with the actual key name
                            var dataB = localStorage.getItem('overdue-datatable'); // Replace 'your_key' with the actual key name
                            var dataC = localStorage.getItem('someday-datatable'); // Replace 'your_key' with the actual key name
                            var dataD = localStorage.getItem('never-followup-datatable'); // Replace 'your_key' with the actual key name
                            var dataE = localStorage.getItem('customer-datatable'); // Replace 'your_key' with the actual key name
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
                            parsedDataE.fil_status = $('#fil_status').val();

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
                            localStorage.setItem('customer-datatable', updatedDataE); // Replace 'your_key' with the actual key name
                            localStorage.setItem('estimate-datatable', updatedDataF); // Replace 'your_key' with the actual key name

                            // Confirmation message
                            console.log('Value updated successfully!');
                        } else {
                            console.log('Browser does not support localStorage');
                        }

                    });
                    $('#fil_team_member').val(fil_user_id);

                    $('.dashboard-setting-form').on('submit', function (e) {
                        e.preventDefault();
                        var formData = $('.dashboard-setting-form').serializeArray();
                        $.ajax({
                            // async: false,
                            type: 'POST',
                            url: '{{route('tenant.dashboard.setting-store', ['tenant' => $segment])}}',
                            data: formData,
                            // data: new FormData(this),
                            dataType: "json",
                            beforeSend: function () {
                                $("#ds_button").prop('disabled', true);
                                $("#ds_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                            },
                            success: function (data) {
                                toastrSuccess('Successfully saved...', 'Success');
                                // $('#dashboard-setting-modal').modal('toggle');
                                // $("#ds_button").prop('disabled', false);
                                // $("#ds_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
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
                                $("#ds_button").prop('disabled', false);
                                $("#ds_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            },
                            complete: function (data) {
                                /*$("#ds_button").html('Save');
                                $("#ds_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);*/
                                location.reload();
                            }
                        });
                        // }
                    });
                });
            </script>
            <style>
                #sales-performance-chart .apexcharts-theme-light{
                    height: 271.7px !important;
                }
            </style>
    @endpush
