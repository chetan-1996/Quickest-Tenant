@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title','Lead')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <style>
        #customer-datatable tbody tr {
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

        @media (min-width: 1400px) {
            .container, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
                max-width: 979px;
            }
        }
    </style>
@endpush
@section('content')
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">
            <div class="container">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-left pt-2">
                                <h2 class="page-titles text-dark">Integrations</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-sm-6 col-xl-5 mb-3">
                        <div class="card mb-0 h-100 border" style="border-color: #d5d5d5 !important;">
                            <div class="card-body">
                                <a href="{{route('tenant.integration.facebook-leads-routing', ['tenant' => $segment])}}">
                                    <div class="d-flex align-items-center">
                                        {{-- <img class="me-1 rounded-circle"
                                            src="{{Storage::disk('s3')->temporaryUrl('template/facebook.png',Carbon\Carbon::now()->addMinutes(20));}}"
                                            width="40" alt="Generic placeholder image">--}}
                                        <img class="me-1 rounded-circle"
                                            src="{{Storage::url('template/facebook.png')}}"
                                            width="30" alt="Generic placeholder image">
                                        <div class="d-flex justify-content-between align-items-center card-body p-0">
                                            <h5 class="d-flex text-dark align-items-center">
                                                Facebook Integration
                                            </h5>
                                            @if($user_data->facebook_id)
                                                <a class="text-primary" href="javascript:void(0);"
                                                onclick="openModal('#facebook-modal','Facebook','#facebook-form','.modal-title',id=0)"><i
                                                        class="uil uil-bright font-18" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" aria-label="Lead Assignment"
                                                        data-bs-original-title="Lead Assignment"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                    {{--                    <h4 class="header-title text-capitalize text-dark"></h4>--}}
                                    <h5 class="text-muted fw-normal mt-2" title="Campaign Sent">Connect your facebook
                                        account pages and get leads from your facebook pages</h5>
                                </a>
                                <div class="d-flex align-items-center mt-2">
                                    <a href="{{route('tenant.integration.facebook-leads-routing', ['tenant' => $segment])}}">
                                        <div class="flex-shrink-0">
                                            <h5 class="font-13 text-dark my-0">{{($user_data->facebook_id)? 'Connected':'Not Connected'}}</h5>
                                        </div>
                                    </a>
                                    <div class="flex-grow-1 ms-2"></div>

                                    <div class="text-end">
                                        <a class="text-primary"
                                        href="{{route('tenant.integration.facebook-leads-routing', ['tenant' => $segment])}}">{{($user_data->facebook_id)? 'Configure':'Connect'}}
                                            <i class="uil uil-angle-right-b ms-1"></i></a>

                                        {{-- <a class="text-primary" href="javascript:void(0);"  onclick="openModal('#facebook-modal','Facebook','#facebook-form','.modal-title',id=0)">Lead Assign <i class="uil uil-angle-right-b ms-1"></i></a>--}}

                                    </div>
                                </div>

                            </div> <!-- end card-body -->
                        </div> <!-- end card -->
                    </div>
                    <div class="col-lg-4 d-none">
                        <div class="card">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-3 text-center">
                                    <img src="{{asset('images/facebook-logo.png')}}" class="img-fluid rounded-start"
                                        width="100"
                                        alt="...">
                                </div>
                                <div class="col-md-9">
                                    {{--                        @if($user_data->facebook_id)--}}
                                    <a href="{{route('tenant.integration.facebook-leads-routing', ['tenant' => $segment])}}"
                                    style="text-decoration: none !important;color: black;">
                                        {{--@else
                                    <a href="{{url('auth/facebook') }}" target="popup"  onclick="window.open('{{url('auth/facebook') }}','popup','width=600,height=600'); return false;" style="text-decoration: none !important;color: black;">
                                        @endif--}}
                                        <div class="card-body">
                                            <h5 class="card-title">Facebook Integration</h5>
                                            <p class="card-text">Connect your facebook account pages and get leads from your
                                                facebook pages</p>
                                        </div>
                                    </a> <!-- end card-body-->
                                </div> <!-- end col -->
                            </div> <!-- end row-->
                        </div> <!-- end card-->
                    </div>

                    <div class="col-sm-6 col-xl-5 mb-3" id="indiamart_integration_model"
                        onclick="openModal('#india-mart-modal','IndiaMART','#india-mart-form','.modal-title',id=0)">
                        <div class="card mb-0 h-100 border" style="border-color: #d5d5d5 !important;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <img class="me-1 rounded-circle"
                                        src="{{asset('images/indiamart.png')}}"
                                        width="30" alt="Generic placeholder image">
                                    <div>
                                        <h5 class="d-flex text-dark align-items-center">
                                            IndiaMART Integration
                                        </h5>
                                    </div>
                                </div>
                                {{--                    <h4 class="header-title text-capitalize text-dark"></h4>--}}
                                <h5 class="text-muted fw-normal mt-2" title="Campaign Sent">Connect your IndiaMART account
                                    and get leads from your IndiaMART account</h5>

                                <div class="d-flex align-items-center mt-2">
                                    <div class="flex-shrink-0">
                                        <h5 class="font-13 text-dark my-0 india-mart-info">{{(isset($india_mart_data->indiamart_token))? 'Connected':'Not Connected'}}</h5>
                                    </div>

                                    <div class="flex-grow-1 ms-2"></div>

                                    <div class="text-end">
                                        <a class="text-primary india-mart-infos">{{(isset($india_mart_data->indiamart_token))? 'Configure':'Connect'}}
                                            <i class="uil uil-angle-right-b ms-1"></i></a>

                                    </div>
                                </div>

                            </div> <!-- end card-body -->
                        </div> <!-- end card -->
                    </div>


                    <div class="col-sm-6 col-xl-5 mb-3" id="tradeindia_integration_model"
                        onclick="openModal('#tradeindia-modal','Tradeindia','#tradeindia-form','.modal-title',id=0)">
                        <div class="card mb-0 h-100 border" style="border-color: #d5d5d5 !important;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <img class="me-1 rounded-circle"
                                        src="{{asset('images/tradeindia.png')}}"
                                        width="30" alt="Generic placeholder image">
                                    <div>
                                        <h5 class="d-flex text-dark align-items-center">
                                            Tradeindia Integration
                                        </h5>
                                    </div>
                                </div>
                                {{--                    <h4 class="header-title text-capitalize text-dark"></h4>--}}
                                <h5 class="text-muted fw-normal mt-2" title="Campaign Sent">Connect your Tradeindia account
                                    and get leads from your Tradeindia account</h5>

                                <div class="d-flex align-items-center mt-2">
                                    <div class="flex-shrink-0">
                                        <h5 class="font-13 text-dark my-0 india-mart-info">{{(isset($tradeindia_data->tradeindia_token))? 'Connected':'Not Connected'}}</h5>
                                    </div>

                                    <div class="flex-grow-1 ms-2"></div>

                                    <div class="text-end">
                                        <a class="text-primary india-mart-infos">{{(isset($tradeindia_data->tradeindia_token))? 'Configure':'Connect'}}
                                            <i class="uil uil-angle-right-b ms-1"></i></a>

                                    </div>
                                </div>

                            </div> <!-- end card-body -->
                        </div> <!-- end card -->
                    </div>

                    <div class="col-sm-6 col-xl-5 mb-3 d-none" id="whatsapp_integration_model"
                        onclick="openModal('#whatsapp-modal','Whatsapp','#whatsapp-form','.modal-title',id=0)">
                        <div class="card mb-0 h-100 border" style="border-color: #d5d5d5 !important;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <img class="me-1 rounded-circle"
                                        src="{{asset('images/whatsapp.png')}}"
                                        width="30" alt="Generic placeholder image">
                                    <div>
                                        <h5 class="d-flex text-dark align-items-center">
                                            Whatsapp Integration
                                        </h5>
                                    </div>
                                </div>
                                {{--                    <h4 class="header-title text-capitalize text-dark"></h4>--}}
                                <h5 class="text-muted fw-normal mt-2" title="Campaign Sent">Connect your 11za account
                                    and send message from your Whatsapp account</h5>

                                <div class="d-flex align-items-center mt-2">
                                    <div class="flex-shrink-0">
                                        <h5 class="font-13 text-dark my-0 india-mart-info">{{(isset($user_data->whatsapp_open_chat_token))? 'Connected':'Not Connected'}}</h5>
                                    </div>

                                    <div class="flex-grow-1 ms-2"></div>

                                    <div class="text-end">
                                        <a class="text-primary india-mart-infos">{{(isset($$user_data->whatsapp_open_chat_token))? 'Configure':'Connect'}}
                                            <i class="uil uil-angle-right-b ms-1"></i></a>

                                    </div>
                                </div>

                            </div> <!-- end card-body -->
                        </div> <!-- end card -->
                    </div>


                    {{--<div class="col-lg-4" id="indiamart_integration_model"
                        onclick="openModal('#india-mart-modal','IndiaMART','#india-mart-form','.modal-title',id=0)">
                        <div class="card">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-3 text-center">
                                    <img src="{{asset('assets/images/indiamart.png')}}" class="img-fluid rounded-start" alt="..."
                                        width="80">
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body" style="text-decoration: none !important;color: black;">
                                        <h5 class="card-title">IndiaMART Integration</h5>
                                        <p class="card-text">Connect your IndiaMART account and get leads from your IndiaMART
                                            account</p>
                                    </div> <!-- end card-body-->
                                </div> <!-- end col -->
                            </div> <!-- end row-->
                        </div> <!-- end card-->
                    </div>--}}
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal -->
    <div id="india-mart-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Import Lead</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="india-mart-form" id="india-mart-form" action="#" method="POST">
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
                                        {{--<div class="form-floating mb-1">
                                            <input class="form-control bg-light text-dark" type="text"
                                                   id="indiamart_token" name="indiamart_token"
                                                   placeholder="xYZadxxxxxxxxxxxxxxxabc">
                                            <label for="indiamart_token" class="form-label">API Token <span
                                                    class="text-danger">*</span></label>
                                        </div>--}}
                                        <div class="input-group mb-0">
                                            <div class="form-floating form-floating-group flex-grow-1">
                                                <input type="text" class="form-control" name="indiamart_token"
                                                       id="indiamart_token" placeholder="API Token" required=""
                                                       data-parsley-errors-container="#apiTokenError">
                                                <label for="code1">API Token</label>
                                            </div>
                                            <button
                                                class="btn btn-primary input-group-text group_action_one {!! (isset($india_mart_data->indiamart_token))? 'd-none':'' !!}"
                                                form="india-mart-form" id="indiamart_lead_button" type="submit">
                                                <i class="mdi mdi-floppy fs-5"></i> Save
                                            </button>
                                            <div
                                                class="input-group-text group_action_two {!! (isset($india_mart_data->indiamart_token))? '':'d-none' !!}">
                                                <i class="mdi mdi-check-decagram mdi-24px text-primary"></i>
                                            </div>

                                        </div>
                                        <span id="apiTokenError" style="background-color:blue;"></span>
                                        <a href="javascript:void(0);"
                                           class="disconnect_indiamart float-end {!! (isset($india_mart_data->indiamart_token))  ? '':'d-none' !!}"><small>Disconnect
                                                IndiaMART</small></a>

                                    </div>

                                    <div
                                        class="col-12 indiamart-radio {!! (isset($india_mart_data->indiamart_token))? '':'d-none' !!}">
                                        <div id="sidebar-user">
                                            <div class="justify-content-between align-items-center">
                                                <input type="hidden" value="{{$user_data->indiamart_integration}}"
                                                       id="indiamart_checkbox">
                                                {{--                                                    <label class="font-16 fw-bold m-0" for="customRadio1">India Mart Lead assigne</label>--}}
                                                <h6 class="form-label font-16">IndiaMart Lead assigne</h6>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="customRadio1" name="customRadio"
                                                           value="Unassigned"
                                                           class="form-check-input" {{(isset($user_data->indiamart_integration) && $user_data->indiamart_integration=='Unassigned')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="customRadio1" {{(isset($user_data->indiamart_integration) && $user_data->indiamart_integration=='Unassigned')? 'checked':''}}>Unassigned</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="customRadio2" name="customRadio"
                                                           value="Round-Robin"
                                                           class="form-check-input" {{(isset($user_data->indiamart_integration) && $user_data->indiamart_integration=='Round-Robin')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="customRadio2" {{(isset($user_data->indiamart_integration) && $user_data->indiamart_integration=='Round-Robin')? 'checked':''}}>Round-Robin</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="customRadio3" name="customRadio"
                                                           value="Others"
                                                           class="form-check-input" {{(isset($user_data->indiamart_integration) && $user_data->indiamart_integration=='Others')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="customRadio3" {{(isset($user_data->indiamart_integration) && $user_data->indiamart_integration=='Others')? 'checked':''}}>Advance
                                                        Round-Robin <a href="#" class="text-black" id="customer_info"
                                                                       data-bs-toggle="tooltip" title=""
                                                                       data-bs-html="true" style=""
                                                                       data-bs-original-title="1. Auto detect existing lead and assign it to existing sales person. <br/> 2. Auto assign lead to sales person who receive the call."
                                                                       aria-label="<ol style='padding-left: 1rem !importaqnt;'>
  <li>Coffee</li>
  <li>Tea</li>
  <li>Milk</li>
</ol>"><i class="mdi mdi-information"></i></a></label>
                                                </div>
                                                {{--@if ($user_data->indiamart_integration=='Round-Robin')
                                                    <a href="{{ url('indiamart-user-list') }}" >Select Users</a>
                                                @endif--}}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="col-12 mt-2 indiamart-data indiamart_setting assign-india-mart-user-list {!! (isset($india_mart_data->indiamart_token) && ($user_data->indiamart_integration=='Round-Robin' || $user_data->indiamart_integration=='Others'))? '':'d-none' !!}">
                                        {{--<div class="page-title-right text-end">
                                            <div class="dropdown btn-group mb-2">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="select_count">0</span>Bulk Action
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-animated">
                                                    <a href="javascript:void(0);"
                                                       class="dropdown-item active_status_all"><i
                                                            class="mdi mdi-update"></i> Assign</a>
                                                    <a href="javascript:void(0);"
                                                       class="dropdown-item deactive_status_all"><i
                                                            class="mdi mdi-update"></i> Unassign</a>
                                                </div>
                                            </div>
                                        </div>--}}
                                        <table id="user-datatable" class="table dt-responsive table-sm nowrap w-100">
                                            <thead class="table-light">
                                            <tr>
                                                <th><input type="checkbox" class="form-check-input" id="select_all">
                                                </th>
                                                <th>Name</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                        {{--                                    <a href="{{ url('settings/general') }}" >Go to indiamart settings</a>--}}
                                    </div>
                                </div>
                            </form>
                        </div>
                        {{--<div class="col-sm-3" style="margin-top: 1.2rem !important;">
                           <button class="btn btn-primary" form="india-mart-form" id="indiamart_lead_button" data-bs-toggle="tooltip" data-bs-html="true" title="Preview and validate your excel" type="submit">
                               <i class="mdi mdi-floppy fs-5"></i> Save
                           </button>
                       </div>--}}
                    </div>
                    <div class="row error_row" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
                                 role="alert">
                                <strong id="error_reporting_section"></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer indiamart-data indiamart_setting assign-india-mart-user-list">
                    <div class="text-end">
                        <a class="btn btn-primary" id="round-robin-button">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </a>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div id="facebook-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Facebook</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="facebook-form" id="facebook-form" action="#" method="POST">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="sidebar-user">
                                            <div class="justify-content-between align-items-center">
                                                <input type="hidden" value="{{$user_data->facebook_integration}}"
                                                       id="facebook_checkbox">
                                                <h6 class="form-label font-16">Facebook Lead assigne</h6>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="fbRadio1" name="fbRadio"
                                                           value="Unassigned"
                                                           class="form-check-input" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Unassigned')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="fbRadio1" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Unassigned')? 'checked':''}}>Unassigned</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="fbRadio2" name="fbRadio"
                                                           value="Round-Robin"
                                                           class="form-check-input" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Round-Robin')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="fbRadio2" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Round-Robin')? 'checked':''}}>Round-Robin</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="col-12 mt-2 facebook-data facebook_setting assign-facebook-user-list {!! (isset($india_mart_data->indiamart_token) && ($user_data->indiamart_integration=='Round-Robin'))? '':'d-none' !!}">
                                        <table id="user-facebook-datatable"
                                               class="table dt-responsive table-sm nowrap w-100">
                                            <thead class="table-light">
                                            <tr>
                                                <th><input type="checkbox" class="form-check-input" id="select_all_fb">
                                                </th>
                                                <th>Name</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                        {{--                                    <a href="{{ url('settings/general') }}" >Go to indiamart settings</a>--}}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer facebook-data facebook_setting assign-facebook-user-list">
                    <div class="text-end">
                        <a class="btn btn-primary" id="round-robin-facebook-button">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </a>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="tradeindia-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Import Lead</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="tradeindia-form" id="tradeindia-form" action="#" method="GET">
                                <div class="row import_error_meesage d-none">
                                    <div class="col-md-12 col-md-offset-1">
                                        <div class="alert alert-danger alert-dismissible">
                                            <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                            <div class="import_excel_error"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-floating mb-3">
                                            <input class="form-control text-dark" type="text"
                                                   id="tradeindia_user_id" name="tradeindia_user_id"
                                                   placeholder="User Id" required="">
                                            <label for="tradeindia_user_id" class="form-label">User Id <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-floating mb-3">
                                            <input class="form-control text-dark" type="text"
                                                   id="tradeindia_profile_id" name="tradeindia_profile_id"
                                                   placeholder="Profile Id" required="">
                                            <label for="tradeindia_profile_id" class="form-label">Profile Id <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group mb-0">
                                            <div class="form-floating form-floating-group flex-grow-1">
                                                <input type="text" class="form-control" name="tradeindia_token"
                                                       id="tradeindia_token" placeholder="Key" required=""
                                                       data-parsley-errors-container="#apiTokenError">
                                                <label for="code1">Key</label>
                                            </div>
                                            <button
                                                class="btn btn-primary input-group-text group_action_one {!! (isset($tradeindia_data->tradeindia_token))? 'd-none':'' !!}"
                                                form="tradeindia-form" id="tradeindia_lead_button" type="submit">
                                                <i class="mdi mdi-floppy fs-5"></i> Save
                                            </button>
                                            <div
                                                class="input-group-text group_action_two {!! (isset($tradeindia_data->tradeindia_token))? '':'d-none' !!}">
                                                <i class="mdi mdi-check-decagram mdi-24px text-primary"></i>
                                            </div>

                                        </div>
                                        <span id="apiTokenError" style="background-color:blue;"></span>
                                        <a href="javascript:void(0);"
                                           class="disconnect_tradeindia float-end {!! (isset($tradeindia_data->tradeindia_token))  ? '':'d-none' !!}"><small>Disconnect
                                                Tradeindia</small></a>

                                    </div>

                                    <div
                                        class="col-12 tradeindia-radio {!! (isset($tradeindia_data->tradeindia_token))? '':'d-none' !!}">
                                        <div id="sidebar-user">
                                            <div class="justify-content-between align-items-center">
                                                <input type="hidden" value="{{$user_data->tradeindia_integration}}"
                                                       id="tradeindia_checkbox">
                                                <h6 class="form-label font-16">Tradeindia Lead assigne</h6>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="ticustomRadio1" name="ticustomRadio"
                                                           value="Unassigned"
                                                           class="form-check-input" {{(isset($user_data->tradeindia_integration) && $user_data->tradeindia_integration=='Unassigned')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="ticustomRadio1" {{(isset($user_data->tradeindia_integration) && $user_data->tradeindia_integration=='Unassigned')? 'checked':''}}>Unassigned</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" id="ticustomRadio2" name="ticustomRadio"
                                                           value="Round-Robin"
                                                           class="form-check-input" {{(isset($user_data->tradeindia_integration) && $user_data->tradeindia_integration=='Round-Robin')? 'checked':''}}>
                                                    <label class="form-check-label"
                                                           for="ticustomRadio2" {{(isset($user_data->tradeindia_integration) && $user_data->tradeindia_integration=='Round-Robin')? 'checked':''}}>Round-Robin</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="col-12 mt-2 tradeindia-data tradeindia_setting assign-tradeindia-user-list {!! (isset($tradeindia_data->tradeindia_token) && ($user_data->tradeindia_integration=='Round-Robin' || $user_data->tradeindia_integration=='Others'))? '':'d-none' !!}">
                                        {{--<div class="page-title-right text-end">
                                            <div class="dropdown btn-group mb-2">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="select_count">0</span>Bulk Action
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-animated">
                                                    <a href="javascript:void(0);"
                                                       class="dropdown-item active_status_all"><i
                                                            class="mdi mdi-update"></i> Assign</a>
                                                    <a href="javascript:void(0);"
                                                       class="dropdown-item deactive_status_all"><i
                                                            class="mdi mdi-update"></i> Unassign</a>
                                                </div>
                                            </div>
                                        </div>--}}
                                        <table id="user-tradeindia-datatable" class="table dt-responsive table-sm nowrap w-100">
                                            <thead class="table-light">
                                            <tr>
                                                <th><input type="checkbox" class="form-check-input" id="tradeindia_select_all">
                                                </th>
                                                <th>Name</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row error_row" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
                                 role="alert">
                                <strong id="error_reporting_section"></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer tradeindia-data tradeindia_setting assign-tradeindia-user-list">
                    <div class="text-end">
                        <a class="btn btn-primary" id="round-robin-tradeindia-button">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </a>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="whatsapp-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Import Lead</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="whatsapp-form" id="whatsapp-form" action="#" method="POST">
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
                                        <div class="form-floating mb-3">
                                            <input class="form-control text-dark" type="text"
                                                   id="whatsapp_auth_token" name="whatsapp_auth_token"
                                                   placeholder="Auth Token" required="" value="{{$user_data->whatsapp_auth_token}}">
                                            <label for="whatsapp_auth_token" class="form-label">Auth Token <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-12 d-none">
                                        <div class="form-floating mb-3">
                                            <input class="form-control text-dark" type="text"
                                                   id="whatsapp_open_chat_token" name="whatsapp_open_chat_token"
                                                   placeholder="Open Chat Token" value="{{$user_data->whatsapp_open_chat_token}}">
                                            <label for="whatsapp_open_chat_token" class="form-label">Open Chat Token <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row error_row" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
                                 role="alert">
                                <strong id="error_reporting_section"></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer tradeindia-data">
                    <div class="text-end">
                        <button
                            class="btn btn-primary input-group-text group_action_one {!! (isset($tradeindia_data->tradeindia_token))? 'd-none':'' !!}"
                            form="whatsapp-form" id="whatsapp_lead_button" type="submit">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script> -->

    <!-- third party js -->
    @include('app.layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>

    <!-- App js -->
    <script src="{{ asset('js/app.min.js')}}"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
        $(document).ready(function () {

                $(".disconnect_indiamart").click(function (e) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('tenant.integration.disconnected-indiamart', ['tenant' => $segment]) }}',
                        data: {
                            indiamart_token: $("#indiamart_token").val()
                        },
                        dataType: "json",
                        /* beforeSend: function () {
                             $("#indiamart_lead_button").prop('disabled', true);
                             $("#indiamart_lead_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                         },*/
                        success: function (reponse) {
                            /*$("#indiamart_lead_button").prop('disabled', false);
                            $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');*/

                            toastrSuccess('Successfully Disconnected...', 'Success');
                            location.reload(true)
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
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $(".error_row").show();
                            $("#error_reporting_section").text(json.errors);
                            $("#indiamart_lead_button").prop('disabled', false);
                            $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                        },
                        complete: function (data) {
                            $("#indiamart_lead_button").prop('disabled', false);
                            $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                        }
                    });
                    return false;
                });

                const userIntegration = $("#indiamart_checkbox").val();
                if (userIntegration === "Unassigned") {
                    $("#customRadio1").prop("checked", true);
                    // $(".assign-india-mart-user-list").hide();
                    $(".assign-india-mart-user-list").addClass('d-none');

                } else if (userIntegration === "Round-Robin" || userIntegration === "Others") {
                    if (userIntegration === "Round-Robin")
                        $("#customRadio2").prop("checked", true);
                    if (userIntegration === "Others")
                        $("#customRadio3").prop("checked", true);
                    /*$("#customRadio2").prop("checked", true);
                    $("#customRadio3").prop("checked", true);*/
                    // $(".assign-india-mart-user-list").show();
                    $(".assign-india-mart-user-list").removeClass('d-none');
                }

                const userFbIntegration = $("#facebook_checkbox").val();
                if (userFbIntegration === "Unassigned") {
                    $("#fbRadio1").prop("checked", true);
                    $(".assign-facebook-user-list").addClass('d-none');

                } else if (userFbIntegration === "Round-Robin") {
                    $("#fbRadio2").prop("checked", true);
                    $(".assign-facebook-user-list").removeClass('d-none');
                }
                const userTiIntegration = $("#tradeindia_checkbox").val();
                if (userTiIntegration === "Unassigned") {
                    $("#ticustomRadio1").prop("checked", true);
                    $(".assign-tradeindia-user-list").addClass('d-none');

                } else {
                    $("#ticustomRadio2").prop("checked", true);
                    $(".assign-tradeindia-user-list").removeClass('d-none');
                }


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var table = $("#user-datatable").DataTable({
                    // dom: 'Bfrtp',
                    /* dom: "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-5'i><'col-sm-7'p>>",*/
                    responsive: false,
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    lengthChange: !1,
                    pageLength: 50,
                    // paging: false,
                    bFilter: false,
                    // ordering: false,
                    searching: false,
                    bInfo: false,
                    // "paging": false,//Dont want paging
                    // "bPaginate": false,//Dont want paging  ,
                    buttons: [{
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    ],
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    },
                    stateSaveParams: function (settings, data) {
                        data.fil_status = $('#fil_status').val();
                        data.fil_name = $('#fil_name').val();
                    },
                    stateLoadParams: function (settings, data) {
                        $('#fil_status').val(data.fil_status);
                        $('#fil_name').val(data.fil_name);
                    },
                    stateSaveCallback: function (settings, data) {
                        localStorage.setItem(settings.sInstance, JSON.stringify(data))
                    },
                    stateLoadCallback: function (settings) {
                        return JSON.parse(localStorage.getItem(settings.sInstance))
                    },
                    ajax: {
                        url: "{{ route('tenant.integration.user-list', ['tenant' => $segment]) }}",
                        data: function (d) {
                            d.status = $('#fil_status').val(),
                                d.name = $('#fil_name').val(),
                                d.search = $('input[type="search"]').val()
                        }
                    },
                    columns: [
                        {
                            data: 'id', name: 'u1.id', orderable: false,
                            render: function (data, type, row) {
                                var is_checked = "";
                                var is_disable = "";
                                if (row.is_checked == "checked") {
                                    is_checked = "checked";
                                }
                                if (row.status == 2) {
                                    is_disable = "disabled";
                                }
                                return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' + row.action + '" ' + is_checked + ' ' + is_disable + '>';
                            }
                        },
                        // {data: 'user_role', name: 'u1.user_role', orderable: true},
                        // {data: 'assign_user', name: 'u1.assign_user'},
                        {
                            data: 'name',
                            name: 'u1.name',
                            orderable: true,
                            render: function (data, type, row) {
                                let st = '';
                                if (row.company_id == null) {
                                    st = ' (1st Register)';
                                }
                                return '<td>' +
                                    '<h5 class="font-15 mb-1 fw-normal text-dark fw-bold">' + row.name +
                                    st +
                                    '</h5>' +
                                    '<span class="text-dark font-14">' + row.email + '</span>' +
                                    '</td>';
                            }
                        },

                        /* {
                             data: 'role_name',
                             name: 'u1.role_name'
                         },
                         {
                             data: 'mobile_no',
                             name: 'u1.mobile_no',
                             render: function (data, type, row) {
                                 return '<td>' +
                                     '<h5 class="font-15 mb-1 fw-normal text-dark">' + row.mobile_no +
                                     '</h5>' +
                                     // '<span class="text-dark font-14">' + row.email + '</span>'+
                                     '</td>';
                             }
                         },
                         {
                             data: 'status',
                             name: 'status',
                             render: function (data, type, row) {
                                 if (data == 0)
                                     return '<span class="badge badge-danger-lighten">Invited</span>';
                                 if (data == 1) {
@if (in_array('give-access-to-delete-other-users', $user_perm))
                        fun_status = "change_status('" + row.action + "', 2,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                @else
                        fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif

                        return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Activate</span>';
                }

                if (data == 2) {
@if (in_array('give-access-to-delete-other-users', $user_perm))
                        fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                @else
                        fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif
                        return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                }
            }
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            render: function (data, type, row) {

@if (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                        var edit_fun = "edit_id('" + row.action + "')";
@else
                        var edit_fun =
                            "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif

                        @if (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                        var delete_fun = "remove_id('" + row.action +
                            "','{{ route('tenant.user.delete', ['tenant' => $segment]) }}','#user-datatable')";
                            @else
                        var delete_fun =
                            "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif
                        var resend_fun = "resend_id('" + row.action + "')";
{{-- var delete_fun = "remove_id('" + row.action + "','{{route('tenant.user.delete', ['tenant' => $segment])}}','#user-datatable')"; --}}
                        var editlink = "{{ url('user-edit') }}/" + row.action;

                            let action_str =
                                '<a href="javascript:void(0);" class="action-icon mr-1" onclick="' +
                                resend_fun + '" id="resed_' + row.action +
                                '"  title="Resend Invite">' +
                                '<i class="mdi mdi-mail"></i>' +
                                '</a>';
                            if (row.company_id == null) {
                                action_str = '';
                            }
                            return '<div class="invoice-action">' +
                                '<a href="javascript:void(0);" class="action-icon mr-1" onclick="' +
                                edit_fun + '" id="edit_' + row.action + '" >' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
                                action_str +
                                '</div>';
                        }
                    },*/
                    ],
                    drawCallback: function () {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                    }
                });

                var tableFb = $("#user-facebook-datatable").DataTable({
                    // dom: 'Bfrtp',
                    /* dom: "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-5'i><'col-sm-7'p>>",*/
                    responsive: false,
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    lengthChange: !1,
                    pageLength: 50,
                    // paging: false,
                    bFilter: false,
                    // ordering: false,
                    searching: false,
                    bInfo: false,
                    // "paging": false,//Dont want paging
                    // "bPaginate": false,//Dont want paging  ,
                    buttons: [{
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    ],
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    },
                    stateSaveParams: function (settings, data) {
                        data.fil_status = $('#fil_status').val();
                        data.fil_name = $('#fil_name').val();
                    },
                    stateLoadParams: function (settings, data) {
                        $('#fil_status').val(data.fil_status);
                        $('#fil_name').val(data.fil_name);
                    },
                    stateSaveCallback: function (settings, data) {
                        localStorage.setItem(settings.sInstance, JSON.stringify(data))
                    },
                    stateLoadCallback: function (settings) {
                        return JSON.parse(localStorage.getItem(settings.sInstance))
                    },
                    ajax: {
                        url: "{{ route('tenant.integration.facebook-user-list', ['tenant' => $segment]) }}",
                        data: function (d) {
                            d.status = $('#fil_status').val(),
                                d.name = $('#fil_name').val(),
                                d.search = $('input[type="search"]').val()
                        }
                    },
                    columns: [
                        {
                            data: 'id', name: 'u1.id', orderable: false,
                            render: function (data, type, row) {
                                var is_checked = "";
                                var is_disable = "";
                                if (row.is_checked == "checked") {
                                    is_checked = "checked";
                                }
                                if (row.status == 2) {
                                    is_disable = "disabled";
                                }
                                return '<input type="checkbox" class="single_checkbox_fb form-check-input" data-id="' + row.action + '" ' + is_checked + ' ' + is_disable + '>';
                            }
                        },
                        // {data: 'user_role', name: 'u1.user_role', orderable: true},
                        // {data: 'assign_user', name: 'u1.assign_user'},
                        {
                            data: 'name',
                            name: 'u1.name',
                            orderable: true,
                            render: function (data, type, row) {
                                let st = '';
                                if (row.company_id == null) {
                                    st = ' (1st Register)';
                                }
                                return '<td>' +
                                    '<h5 class="font-15 mb-1 fw-normal text-dark fw-bold">' + row.name +
                                    st +
                                    '</h5>' +
                                    '<span class="text-dark font-14">' + row.email + '</span>' +
                                    '</td>';
                            }
                        },

                        /* {
                             data: 'role_name',
                             name: 'u1.role_name'
                         },
                         {
                             data: 'mobile_no',
                             name: 'u1.mobile_no',
                             render: function (data, type, row) {
                                 return '<td>' +
                                     '<h5 class="font-15 mb-1 fw-normal text-dark">' + row.mobile_no +
                                     '</h5>' +
                                     // '<span class="text-dark font-14">' + row.email + '</span>'+
                                     '</td>';
                             }
                         },
                         {
                             data: 'status',
                             name: 'status',
                             render: function (data, type, row) {
                                 if (data == 0)
                                     return '<span class="badge badge-danger-lighten">Invited</span>';
                                 if (data == 1) {
@if (in_array('give-access-to-delete-other-users', $user_perm))
                        fun_status = "change_status('" + row.action + "', 2,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                @else
                        fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif

                        return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Activate</span>';
                }

                if (data == 2) {
@if (in_array('give-access-to-delete-other-users', $user_perm))
                        fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                @else
                        fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif
                        return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                }
            }
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            render: function (data, type, row) {

@if (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                        var edit_fun = "edit_id('" + row.action + "')";
@else
                        var edit_fun =
                            "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif

                        @if (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                        var delete_fun = "remove_id('" + row.action +
                            "','{{ route('tenant.user.delete', ['tenant' => $segment]) }}','#user-datatable')";
                            @else
                        var delete_fun =
                            "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
@endif
                        var resend_fun = "resend_id('" + row.action + "')";
{{-- var delete_fun = "remove_id('" + row.action + "','{{route('tenant.user.delete', ['tenant' => $segment])}}','#user-datatable')"; --}}
                        var editlink = "{{ url('user-edit') }}/" + row.action;

                            let action_str =
                                '<a href="javascript:void(0);" class="action-icon mr-1" onclick="' +
                                resend_fun + '" id="resed_' + row.action +
                                '"  title="Resend Invite">' +
                                '<i class="mdi mdi-mail"></i>' +
                                '</a>';
                            if (row.company_id == null) {
                                action_str = '';
                            }
                            return '<div class="invoice-action">' +
                                '<a href="javascript:void(0);" class="action-icon mr-1" onclick="' +
                                edit_fun + '" id="edit_' + row.action + '" >' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
                                action_str +
                                '</div>';
                        }
                    },*/
                    ],
                    drawCallback: function () {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                    }
                });
                /* table.buttons().container().appendTo("#user-datatable_wrapper .col-md-6:eq(0)"), $(
                     "#alternative-page-datatable").DataTable({
                     pagingType: "full_numbers",
                     drawCallback: function () {
                         $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                     }
                 })*/
                $('input[name="customRadio"]').change(function () {
                    const selectedValue = $(this).val();
                    console.log(selectedValue);
                    $.ajax({
                        url: SITEURL + "/settings/indiamart-flag/update",
                        method: 'POST',
                        data: {
                            indiamart_integration: selectedValue
                        },
                        success: function (msg) {
                            if (selectedValue === "Unassigned") {
                                $("#customRadio1").prop("checked", true);
                                // $(".assign-india-mart-user-list").hide();
                                $(".assign-india-mart-user-list").addClass('d-none');
                            }
                            if (selectedValue === "Round-Robin" || selectedValue === "Others") {
                                if (selectedValue === "Round-Robin")
                                    $("#customRadio2").prop("checked", true);
                                if (selectedValue === "Others")
                                    $("#customRadio3").prop("checked", true);

                                // $(".assign-india-mart-user-list").show();
                                $(".assign-india-mart-user-list").removeClass('d-none');
                                table
                                    .search('')
                                    .columns().search('')
                                    .draw();
                            }
                            toastrSuccess('Successfully saved...', 'Success');
                        }
                    });
                });

                $('input[name="fbRadio"]').change(function () {
                    const selectedValue = $(this).val();
                    console.log(selectedValue);
                    $.ajax({
                        url: SITEURL + "/settings/facebook-flag/update",
                        method: 'POST',
                        data: {
                            facebook_integration: selectedValue
                        },
                        success: function (msg) {
                            if (selectedValue === "Unassigned") {
                                $("#fbRadio1").prop("checked", true);
                                // $(".assign-india-mart-user-list").hide();
                                $(".assign-facebook-user-list").addClass('d-none');
                            }
                            if (selectedValue === "Round-Robin") {
                                $("#fbRadio2").prop("checked", true);


                                // $(".assign-india-mart-user-list").show();
                                $(".assign-facebook-user-list").removeClass('d-none');
                                tableFb
                                    .search('')
                                    .columns().search('')
                                    .draw();
                            }
                            toastrSuccess('Successfully saved...', 'Success');
                        }
                    });
                });

                var excel_validate_final_array = [];
                formValition('#india-mart-form');
                formValition('#whatsapp-form');
                $('.india-mart-form').on('submit', function (e) {
                    e.preventDefault();
                    if ($(this).parsley().isValid()) {
                        $(".error_row").hide();
                        $.ajax({
                            type: 'GET',
                            url: '{{ route('tenant.integration.india-mart', ['tenant' => $segment]) }}',
                            data: {
                                indiamart_token: $("#indiamart_token").val()
                            },
                            dataType: "json",
                            beforeSend: function () {
                                $("#indiamart_lead_button").prop('disabled', true);
                                $("#indiamart_lead_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                            },
                            success: function (reponse) {
                                $("#indiamart_lead_button").prop('disabled', false);
                                $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                                toastrSuccess('Successfully Integrated...', 'Success');
                                $(".group_action_two").removeClass("d-none");
                                $(".group_action_one").addClass("d-none");
                                $(".indiamart-radio").removeClass("d-none");
                                // $(".indiamart-data").removeClass("d-none");
                                $(".disconnect_indiamart").removeClass("d-none");

                                const userIntegration = $("#indiamart_checkbox").val();
                                if (userIntegration === "Unassigned") {
                                    $("#customRadio1").prop("checked", true);
                                    // $(".assign-india-mart-user-list").hide();
                                    $(".assign-india-mart-user-list").addClass('d-none');

                                } else if (userIntegration === "Round-Robin" || userIntegration === "Others") {
                                    if (userIntegration === "Round-Robin")
                                        $("#customRadio2").prop("checked", true);
                                    if (userIntegration === "Others")
                                        $("#customRadio3").prop("checked", true);
                                    /*$("#customRadio2").prop("checked", true);
                                    $("#customRadio3").prop("checked", true);*/
                                    // $(".assign-india-mart-user-list").show();
                                    $(".assign-india-mart-user-list").removeClass('d-none');
                                }
                                $(".india-mart-info").html('Connected');
                                $(".india-mart-infos").html('Configure <i class="uil uil-angle-right-b ms-1"></i>');

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
                                        break;
                                    case 409:
                                        toastrInfo('Phone no already exist.', 'Warning');
                                        break;
                                    default:
                                        toastrError('Error - ' + errorMessage, 'Error');
                                }
                                $(".error_row").show();
                                $("#error_reporting_section").text(json.errors);
                                $("#indiamart_lead_button").prop('disabled', false);
                                $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            },
                            complete: function (data) {
                                $("#indiamart_lead_button").prop('disabled', false);
                                $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            }
                        });
                    }
                });

                $('#indiamart_integration_model').on('click', function (e) {
                    if ($(this).parsley().isValid()) {
                        $(".error_row").hide();
                        $.ajax({
                            type: 'GET',
                            url: '{{ route('tenant.integration.get-indiamart-api-token', ['tenant' => $segment]) }}',
                            dataType: "json",
                            success: function (reponse) {
                                $("#indiamart_lead_button").prop('disabled', false);

                                $("#indiamart_token").val('');
                                // $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                                $(".indiamart_setting").addClass('d-none');
                                if (reponse.data && reponse.data.indiamart_token != "") {
                                    $("#indiamart_token").val(reponse.data.indiamart_token);
                                    // $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verified');
                                    // $(".indiamart_setting").removeClass('d-none');

                                    const userIntegration = $("#indiamart_checkbox").val();
                                    if (userIntegration === "Unassigned") {
                                        $("#customRadio1").prop("checked", true);
                                        // $(".assign-india-mart-user-list").hide();
                                        $(".assign-india-mart-user-list").addClass('d-none');

                                    } else if (userIntegration === "Round-Robin" || userIntegration === "Others") {
                                        if (userIntegration === "Round-Robin")
                                            $("#customRadio2").prop("checked", true);
                                        if (userIntegration === "Others")
                                            $("#customRadio3").prop("checked", true);
                                        /*$("#customRadio2").prop("checked", true);
                                        $("#customRadio3").prop("checked", true);*/
                                        // $(".assign-india-mart-user-list").show();
                                        $(".assign-india-mart-user-list").removeClass('d-none');
                                    }
                                }
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
                                        break;
                                    case 409:
                                        toastrInfo('Phone no already exist.', 'Warning');
                                        break;
                                    default:
                                        toastrError('Error - ' + errorMessage, 'Error');
                                }
                                $(".error_row").show();
                                $("#error_reporting_section").text(json.errors);
                                $("#indiamart_lead_button").prop('disabled', false);
                                $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                            },
                        });
                    }
                });
                $('#round-robin-button').on('click', function (e) {
                    var allVals = [];
                    $(".single_checkbox:checked").each(function () {
                        allVals.push($(this).attr('data-id'));
                    });
                    var join_selected_values = allVals.join(",");


                    if (join_selected_values.length <= 0) {
                        toastrWarning('Please select at least one record', 'Warning');
                        return false;
                    }
                    Swal.fire({
                        title: "Are you sure ?",
                        // text: "You won't be able to revert this!",
                        // text: "Are you sure ?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonColor: "#3085D6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, change it!",
                        confirmButtonClass: "btn btn-primary",
                        cancelButtonClass: "btn btn-danger ml-1",
                        buttonsStyling: !1,
                        preConfirm: function () {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('tenant.integration.indiamart-update-user-list', ['tenant' => $segment]) }}",
                                data: {id: join_selected_values, status: 0},
                                dataType: "json",
                                success: function (data, textStatus, jqXHR) {
                                    // toastrSuccess('Successfully updated');
                                    $('#user-datatable').DataTable().ajax.reload();
                                    $('#select_all').prop('checked', false);
                                    $("#select_count").html(0);
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
                                            $('#user-datatable').DataTable().ajax.reload();

                                            toastrErrorWithText(xhr.responseJSON, 'Warning');
                                    }
                                },
                                complete: function (data) {
                                }
                            });
                        }
                    }).then(function (t) {
                        if (t.isConfirmed)
                            t.value && Swal.fire({
                                title: "Success",
                                text: "Your record has been updated.",
                                type: "success",
                                showConfirmButton: !1,
                                timer: 1500,
                                confirmButtonClass: "btn btn-success",
                                showConfirmButton: false
                            })
                        toastrSuccess('Successfully saved...', 'Success');
                        $('#india-mart-modal').modal('toggle');
                        // var oldValueArr = old_value.split('_');
                        // $('#example-select_' + oldValueArr[1]).val(oldValueArr[0]);
                    });

                });

                $('#round-robin-facebook-button').on('click', function (e) {
                    var allVals = [];
                    $(".single_checkbox_fb:checked").each(function () {
                        allVals.push($(this).attr('data-id'));
                    });
                    var join_selected_values = allVals.join(",");


                    if (join_selected_values.length <= 0) {
                        toastrWarning('Please select at least one record', 'Warning');
                        return false;
                    }
                    Swal.fire({
                        title: "Are you sure ?",
                        // text: "You won't be able to revert this!",
                        // text: "Are you sure ?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonColor: "#3085D6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, change it!",
                        confirmButtonClass: "btn btn-primary",
                        cancelButtonClass: "btn btn-danger ml-1",
                        buttonsStyling: !1,
                        preConfirm: function () {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('tenant.integration.facebook-update-user-list', ['tenant' => $segment]) }}",
                                data: {id: join_selected_values, status: 0},
                                dataType: "json",
                                success: function (data, textStatus, jqXHR) {
                                    // toastrSuccess('Successfully updated');
                                    $('#user-datatable').DataTable().ajax.reload();
                                    $('#select_all').prop('checked', false);
                                    $("#select_count").html(0);
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
                                            $('#user-datatable').DataTable().ajax.reload();

                                            toastrErrorWithText(xhr.responseJSON, 'Warning');
                                    }
                                },
                                complete: function (data) {
                                }
                            });
                        }
                    }).then(function (t) {
                        if (t.isConfirmed)
                            t.value && Swal.fire({
                                title: "Success",
                                text: "Your record has been updated.",
                                type: "success",
                                showConfirmButton: !1,
                                timer: 1500,
                                confirmButtonClass: "btn btn-success",
                                showConfirmButton: false
                            })
                        toastrSuccess('Successfully saved...', 'Success');
                        $('#facebook-modal').modal('toggle');
                        // var oldValueArr = old_value.split('_');
                        // $('#example-select_' + oldValueArr[1]).val(oldValueArr[0]);
                    });

                });

                $('.deactive_status_all').on('click', function (e) {
                    var allVals = [];
                    $(".single_checkbox:checked").each(function () {
                        allVals.push($(this).attr('data-id'));
                    });
                    var join_selected_values = allVals.join(",");
                    change_status(join_selected_values, 1, '{{ route('tenant.integration.indiamart-update-user-list', ['tenant' => $segment]) }}', '#user-datatable');
                });

            var table_tradeindia = $("#user-tradeindia-datatable").DataTable({
                responsive: false,
                processing: true,
                serverSide: true,
                stateSave: true,
                lengthChange: !1,
                pageLength: 50,
                bFilter: false,
                searching: false,
                bInfo: false,
                buttons: [{
                    extend: 'pageLength',
                    attr: {
                        class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                    },
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function (settings, data) {
                    data.fil_status = $('#fil_status').val();
                    data.fil_name = $('#fil_name').val();
                },
                stateLoadParams: function (settings, data) {
                    $('#fil_status').val(data.fil_status);
                    $('#fil_name').val(data.fil_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.integration.tradeindia-user-list', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                columns: [
                    {
                        data: 'id', name: 'u1.id', orderable: false,
                        render: function (data, type, row) {
                            var is_checked = "";
                            var is_disable = "";
                            if (row.is_checked == "checked") {
                                is_checked = "checked";
                            }
                            if (row.status == 2) {
                                is_disable = "disabled";
                            }
                            return '<input type="checkbox" class="tradeindia_single_checkbox form-check-input" data-id="' + row.action + '" ' + is_checked + ' ' + is_disable + '>';
                        }
                    },
                    {
                        data: 'name',
                        name: 'u1.name',
                        orderable: true,
                        render: function (data, type, row) {
                            let st = '';
                            if (row.company_id == null) {
                                st = ' (1st Register)';
                            }
                            return '<td>' +
                                '<h5 class="font-15 mb-1 fw-normal text-dark fw-bold">' + row.name +
                                st +
                                '</h5>' +
                                '<span class="text-dark font-14">' + row.email + '</span>' +
                                '</td>';
                        }
                    }
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });

                formValition('#tradeindia-form');
                $('.tradeindia-form').on('submit', function (e) {
                    e.preventDefault();
                    if ($(this).parsley().isValid()) {
                        $(".error_row").hide();
                        $.ajax({
                            type: 'GET',
                            url: '{{ route('tenant.integration.tradeindia', ['tenant' => $segment]) }}',
                            data: {
                                tradeindia_token: $("#tradeindia_token").val(),
                                tradeindia_user_id: $("#tradeindia_user_id").val(),
                                tradeindia_profile_id: $("#tradeindia_profile_id").val(),
                            },
                            dataType: "json",
                            beforeSend: function () {
                                $("#tradeindia_lead_button").prop('disabled', true);
                                $("#tradeindia_lead_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                            },
                            success: function (reponse) {
                                $("#tradeindia_lead_button").prop('disabled', false);
                                $("#tradeindia_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                                toastrSuccess('Successfully Integrated...', 'Success');
                                $(".group_action_two").removeClass("d-none");
                                $(".group_action_one").addClass("d-none");
                                $(".tradeindia-radio").removeClass("d-none");
                                // $(".tradeindia-data").removeClass("d-none");
                                $(".disconnect_tradeindia").removeClass("d-none");

                                const userIntegration = $("#tradeindia_checkbox").val();
                                if (userIntegration === "Unassigned") {
                                    $("#customRadio1").prop("checked", true);
                                    // $(".assign-india-mart-user-list").hide();
                                    $(".assign-india-mart-user-list").addClass('d-none');

                                } else if (userIntegration === "Round-Robin" || userIntegration === "Others") {
                                    if (userIntegration === "Round-Robin")
                                        $("#customRadio2").prop("checked", true);
                                    if (userIntegration === "Others")
                                        $("#customRadio3").prop("checked", true);
                                    /*$("#customRadio2").prop("checked", true);
                                    $("#customRadio3").prop("checked", true);*/
                                    // $(".assign-india-mart-user-list").show();
                                    $(".assign-india-mart-user-list").removeClass('d-none');
                                }
                                $(".india-mart-info").html('Connected');
                                $(".india-mart-infos").html('Configure <i class="uil uil-angle-right-b ms-1"></i>');

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
                                        break;
                                    case 409:
                                        toastrInfo('Phone no already exist.', 'Warning');
                                        break;
                                    default:
                                        toastrError('Error - ' + errorMessage, 'Error');
                                }
                                // $(".error_row").show();
                                // $("#error_reporting_section").text(json.errors);
                                // $("#tradeindia_lead_button").prop('disabled', false);
                                $("#tradeindia_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            },
                            complete: function (data) {
                                $("#tradeindia_lead_button").prop('disabled', false);
                                $("#tradeindia_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            }
                        });
                    }
                });

            $('.whatsapp-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $(".error_row").hide();
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('tenant.integration.whatsapp', ['tenant' => $segment]) }}',
                        data: {
                            whatsapp_auth_token: $("#whatsapp_auth_token").val(),
                            whatsapp_open_chat_token: $("#whatsapp_open_chat_token").val(),
                        },
                        dataType: "json",
                        beforeSend: function () {
                            $("#whatsapp_lead_button").prop('disabled', true);
                            $("#whatsapp_lead_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (reponse) {
                            $("#whatsapp_lead_button").prop('disabled', false);
                            $("#whatsapp_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                            toastrSuccess('Successfully Integrated...', 'Success');

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
                                    break;
                                case 409:
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            // $(".error_row").show();
                            // $("#error_reporting_section").text(json.errors);
                            // $("#whatsapp_lead_button").prop('disabled', false);
                            $("#whatsapp_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#whatsapp_lead_button").prop('disabled', false);
                            $("#whatsapp_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                        }
                    });
                }
            });

                $('input[name="ticustomRadio"]').change(function () {
                    const selectedValue = $(this).val();
                    console.log(selectedValue);
                    $.ajax({
                        url: SITEURL + "/settings/tradeindia-flag/update",
                        method: 'POST',
                        data: {
                            tradeindia_integration: selectedValue
                        },
                        success: function (msg) {
                            if (selectedValue === "Unassigned") {
                                $("#ticustomRadio1").prop("checked", true);
                                $(".assign-tradeindia-user-list").addClass('d-none');
                            }
                            if (selectedValue === "Round-Robin") {
                                $("#ticustomRadio2").prop("checked", true);
                                $(".assign-tradeindia-user-list").removeClass('d-none');
                                table_tradeindia
                                    .search('')
                                    .columns().search('')
                                    .draw();
                            }
                            toastrSuccess('Successfully saved...', 'Success');
                        }
                    });
                });

                $('#tradeindia_integration_model').on('click', function (e) {
                    if ($(this).parsley().isValid()) {
                        $(".error_row").hide();
                        $.ajax({
                            type: 'GET',
                            url: '{{ route('tenant.integration.get-tradeindia-api-token', ['tenant' => $segment]) }}',
                            dataType: "json",
                            success: function (reponse) {
                                $("#tradeindia_lead_button").prop('disabled', false);

                                $("#tradeindia_token").val('');
                                $(".tradeindia_setting").addClass('d-none');
                                if (reponse.data && reponse.data.indiamart_token != "") {
                                    $("#tradeindia_token").val(reponse.data.tradeindia_token);
                                    $("#tradeindia_user_id").val(reponse.data.tradeindia_user_id);
                                    $("#tradeindia_profile_id").val(reponse.data.tradeindia_profile_id);

                                    const userTiIntegration = $("#tradeindia_checkbox").val();
                                    if (userIntegration === "Unassigned") {
                                        $("#ticustomRadio1").prop("checked", true);
                                        $(".assign-tradeindia-user-list").addClass('d-none');

                                    } else if (userTiIntegration === "Round-Robin") {
                                        $("#ticustomRadio2").prop("checked", true);
                                        $(".assign-tradeindia-user-list").removeClass('d-none');
                                    }
                                }
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
                                        break;
                                    case 409:
                                        toastrInfo('Phone no already exist.', 'Warning');
                                        break;
                                    default:
                                        toastrError('Error - ' + errorMessage, 'Error');
                                }
                                $(".error_row").show();
                                $("#error_reporting_section").text(json.errors);
                                $("#tradeindia_lead_button").prop('disabled', false);
                                $("#tradeindia_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                            },
                        });
                    }
                });

                $(".disconnect_tradeindia").click(function (e) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('tenant.integration.disconnected-tradeindia', ['tenant' => $segment]) }}',
                    data: {
                        tradeindia_token: $("#tradeindia_token").val()
                    },
                    dataType: "json",
                    /* beforeSend: function () {
                         $("#indiamart_lead_button").prop('disabled', true);
                         $("#indiamart_lead_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                     },*/
                    success: function (reponse) {
                        /*$("#indiamart_lead_button").prop('disabled', false);
                        $("#indiamart_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');*/

                        toastrSuccess('Successfully Disconnected...', 'Success');
                        location.reload(true)
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
                                break;
                            case 409:
                                toastrInfo('Phone no already exist.', 'Warning');
                                break;
                            default:
                                toastrError('Error - ' + errorMessage, 'Error');
                        }
                        $(".error_row").show();
                        $("#error_reporting_section").text(json.errors);
                        $("#tradeindia_lead_button").prop('disabled', false);
                        $("#tradeindia_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                    },
                    complete: function (data) {
                        $("#tradeindia_lead_button").prop('disabled', false);
                        $("#tradeindia_lead_button").html('<i class="mdi mdi-floppy fs-5"></i> Verify');
                    }
                });
                return false;
            });

                $('.tradeindia_deactive_status_all').on('click', function (e) {
                    var allVals = [];
                    $(".tradeindia_single_checkbox:checked").each(function () {
                        allVals.push($(this).attr('data-id'));
                    });
                    var join_selected_values = allVals.join(",");
                    change_status(join_selected_values, 1, '{{ route('tenant.integration.tradeindia-update-user-list', ['tenant' => $segment]) }}', '#user-tradeindia-datatable');
                });

            $('#round-robin-tradeindia-button').on('click', function (e) {
                var allVals = [];
                $(".tradeindia_single_checkbox:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });
                var join_selected_values = allVals.join(",");


                if (join_selected_values.length <= 0) {
                    toastrWarning('Please select at least one record', 'Warning');
                    return false;
                }
                Swal.fire({
                    title: "Are you sure ?",
                    // text: "You won't be able to revert this!",
                    // text: "Are you sure ?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonColor: "#3085D6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, change it!",
                    confirmButtonClass: "btn btn-primary",
                    cancelButtonClass: "btn btn-danger ml-1",
                    buttonsStyling: !1,
                    preConfirm: function () {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('tenant.integration.tradeindia-update-user-list', ['tenant' => $segment]) }}",
                            data: {id: join_selected_values, status: 0},
                            dataType: "json",
                            success: function (data, textStatus, jqXHR) {
                                // toastrSuccess('Successfully updated');
                                $('#user-tradeindia-datatable').DataTable().ajax.reload();
                                $('#tradeindia_select_all').prop('checked', false);
                                $("#select_count").html(0);
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
                                        $('#user-datatable').DataTable().ajax.reload();

                                        toastrErrorWithText(xhr.responseJSON, 'Warning');
                                }
                            },
                            complete: function (data) {
                            }
                        });
                    }
                }).then(function (t) {
                    if (t.isConfirmed)
                        t.value && Swal.fire({
                            title: "Success",
                            text: "Your record has been updated.",
                            type: "success",
                            showConfirmButton: !1,
                            timer: 1500,
                            confirmButtonClass: "btn btn-success",
                            showConfirmButton: false
                        })
                    toastrSuccess('Successfully saved...', 'Success');
                    $('#tradeindia-modal').modal('toggle');
                    // var oldValueArr = old_value.split('_');
                    // $('#example-select_' + oldValueArr[1]).val(oldValueArr[0]);
                });

            });
            });
    </script>
@endpush
