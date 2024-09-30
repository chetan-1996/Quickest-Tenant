@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title', 'Estimate')
@push('styles')

    <link href="{{ asset('vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    {{--<link href="https://coderthemes.com/ubold/layouts/default/assets/libs/clockpicker/bootstrap-clockpicker.min.css"
          rel="stylesheet" type="text/css">--}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.css" type="text/css"/>
@endpush
@section('content')
    <style>
        .continue_btn {
            text-transform: uppercase;
            font-size: 0.9em;
            color: #fff;
            /* background-color: #17a2b8 !important; */
            border-radius: 0;
            padding: 0.75em 0em !important;
            font-weight: 500;
            font-weight: 800;
        }

        #estimateLimitModal {
            backdrop-filter: blur(4px);
        }

        /* .modal-backdrop.show {
                            opacity: 0 !important;
                        } */
    </style>
    <div class="content-page">
        <div class="content">

            <!-- Start Content-->
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                @if (isset($plan->estimate_limit) && $plan->estimate_limit <= $estimateCount)
                                    <a class="btn btn-primary btn-sm mb-2" data-toggle="modal" id="mediumButton"
                                        data-target="#estimateLimitModal">
                                        <i class="mdi mdi-plus-circle"></i>Add Estimate
                                    </a>
                                {{--  @elseif (in_array('add-estimate', $user_perm) || auth()->user()->company_id == null)--}}
                                @else
                                    <a href="{{ route('tenant.quotes.new', ['tenant' => $segment]) }}" class="btn btn-primary btn-sm mb-2"><i class="mdi mdi-plus-circle"></i> Add Estimate</a>
                                @endif
                            </div>
                            <div class="page-title-left pt-2">

                                {{--                        <h4 class="page-title">Estimate</h4> --}}

                                <select class="form-select" id="fil_status" name="fil_status"
                                    style="width: 250px;background-color: #fff0 !important;border: 0px solid #fff !important;font-size: 18px;margin: 0;white-space: nowrap;font-weight: 700;padding: 0.0rem 0.0rem 0rem 0.5rem;">
                                    <option value="" @if (request()->get('status') == 'Total') {{ 'selected' }} @endif>All Estimates
                                    </option>
                                    <option value="Draft" @if (request()->get('status') == 'Draft') {{ 'selected' }} @endif>Draft
                                        Estimates
                                    </option>
                                    {{--<option value="Sent" @if (request()->get('status') == 'Sent') {{ 'selected' }} @endif>Sent
                                        Estimates
                                    </option>--}}
                                    <option value="Inprogress" @if (request()->get('status') == 'Inprogress') {{ 'selected' }} @endif>
                                        Inprogress Estimates
                                    </option>
                                    <option value="Accept" @if (request()->get('status') == 'Accept') {{ 'selected' }} @endif>Accept
                                        Estimates
                                    </option>
                                    <option value="Decline" @if (request()->get('status') == 'Decline') {{ 'selected' }} @endif>Decline
                                        Estimates
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                {{-- <div class="row mb-2">
                                    <div class="col-xl-8">
                                        <div class=" row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                                            <div class="col-auto">
                                                <div class="d-flex align-items-center">
                                                    <label for="fil_name" class="visually-hidden">Name</label>
                                                    <input type="text" class="form-control" id="fil_name" name="fil_name"
                                                            placeholder="name...">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="d-flex align-items-center">
                                                    <label for="fil_name" class="visually-hidden">Estimate No</label>
                                                    <input type="text" class="form-control" id="fil_estimate_no" name="fil_estimate_no"
                                                            placeholder="estimate number...">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="d-flex align-items-center">
                                                    <label for="fil_status" class="me-2">Status</label>
                                                    <select class="form-select" id="fil_status" name="fil_status">
                                                        <option value="">Choose...</option>
                                                        <option>Draft</option>
                                                        <option>Sent</option>
                                                        <option>Inprogress</option>
                                                        <option>Accept</option>
                                                        <option>Decline</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="text-xl-end mt-xl-0 mt-2">
                                            <button type="button" class="btn btn-secondary waves-effect waves-light mr-1 mb-2"
                                                    id="resetFilter">
                                                <i class="mdi mdi-filter"></i> Reset Filters
                                            </button>
                                            <a href="{{route('tenant.quotes.new', ['tenant' => $segment])}}" class="btn btn-info mb-2"><i
                                                    class="mdi mdi-plus-circle"></i> Add Estimate</a>

                                        </div>
                                    </div><!-- end col-->
                                </div> --}}
                                @if (session('error'))
                                    <div class="alert alert-danger" role="alert"> {{ session('error') }}
                                    </div>
                                @endif
                                <table id="estimate-datatable" class="table table-centered table-sm w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                            <th>Date</th>
                                            <th>Estimate Number</th>
                                            <th>Reference#</th>
                                            <th>Customer Name</th>
                                            <th>Expiry Date</th>
                                            <th>Sub Total</th>
                                            <th>Total</th>
                                            <th>Created by</th>
                                            <th>Status</th>
                                            <th>Action</th>
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
                                                <h3 class="pt-3 fw-bold text-dark">OOPS !! Your New Estimate Generate limit is over</h3>
                                            </div>
                                            <p class="px-5 pt-2 text-dark">Thank you for using Quickest. You can generate a maximum up
                                                to {{ $estimateCount }} per month with a free plan. To get full benefits of Quickest,
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

                <div id="follow-up-modal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                    role="dialog" aria-labelledby="scrollableModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable modal-center" role="document">
                        <!--  modal-lg modal-center-->
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h4 class="modal-title">Next Follow Up</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h5 class="text-uppercase bg-light pt-3 ps-2 pb-1 pe-2">
                                    <p class="font-13"><strong>Customer/Mobile: </strong> <span
                                            class="float-end estimate_customer_span"></span></p>
                                    <p class="font-13"><strong>Estimate: </strong> <span class="float-end estimate_span"></span></p>
                                </h5>
                                <form class="follow-up-form" id="follow-up-form" action="#" autocomplete="off">
                                    <div class="row form-div">
                                        <div class="col-md-6 mb-2 form-error">
                                            <label for="notes" class="form-label">Estimate Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="estimate_status" name="estimate_status" required="">
                                                <option value="">Choose</option>
                                                <option value="Draft">Draft</option>
                                                <option value="Sent">Sent</option>
                                                <option value="Inprogress">Inprogress</option>
                                                <option value="Accept">Accept</option>
                                                <option value="Decline">Decline</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2 form-error">
                                            <h6 class="form-label font-14">Next follow up <span class="text-danger">*</span></h6>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" id="item_type_yes" name="next_follow_up"
                                                    class="form-check-input" value="Yes" checked="">
                                                <label class="form-check-label" for="item_type_yes">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" id="item_type_no" name="next_follow_up"
                                                    class="form-check-input" value="No">
                                                <label class="form-check-label" for="item_type_no">No</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row follow-up-div">
                                        <div class="col-md-6 mb-2 form-error form-div">
                                            <label for="name" class="form-label">Date <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" name="followup_date" class="form-control form-control-light"
                                                    id="followup_date" value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}" required>
                                                <input type="hidden" name="estimate_id" class="form-control" id="estimate_id"
                                                    value="">
                                                <input type="hidden" name="id" class="form-control" id="id"
                                                    value="">
                                                <input type="hidden" name="sp_flag" class="form-control" id="sp_flag"
                                                    value="u">
                                                <input type="hidden" name="event_id" class="form-control" id="event_id"
                                                    value="0">
                                                <span class="input-group-text bg-secondary border-secondary text-white">
                                                    <i class="mdi mdi-calendar-range font-13"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2 form-error form-div">
                                            <label for="followup_time" class="form-label">Time <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group" data-placement="bottom" data-align="bottom"
                                                data-autoclose="true" data-default='now'>
                                                <input id="followup_time" name="followup_time" type="text" class="form-control"
                                                    required value="">
                                                <span class="input-group-text bg-secondary border-secondary text-white"><i
                                                        class="dripicons-clock"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row form-div">
                                        <div class="col-md-12 mb-2 form-error">
                                            <label for="notes" class="form-label">Notes <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="notes" name="notes" placeholder="Enter note" maxlength="225"
                                                data-toggle="maxlength" required></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body pb-0">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h4 class="header-title mb-0">Follow up</h4>
                                                    </div>
                                                </div>

                                                <div class="card-body timeline-list py-0" data-simplebar="init"
                                                    style="max-height: 292px;overflow-y: auto;">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close
                                </button>
                                <button class="btn btn-secondary" id="follow_up_button" type="submit" form="follow-up-form"><i
                                        class="uil-arrow-circle-right"></i> Save
                                </button>
                            </div>
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
                                                    value="">
                                                <input type="hidden" id="activity_customer_id" name="activity_customer_id"
                                                    value="0">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <div class="form-floating">
                                                    <textarea class="form-control bg-light" id="activity_estimate_notes"
                                                            name="activity_estimate_notes"
                                                            placeholder="Add Discussion Summary.."
                                                            style="height: 150px"></textarea>
                                                    <label for="activity_estimate_notes">Add Discussion Summary..</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-grid d-block">
                                                <button type="submit" class="btn btn-lg font-16 btn-primary"
                                                        id="activity_change_estimate_status_form_buttons">
                                                    <i class="mdi mdi-plus-circle-outline"></i> Save
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>


                            </div>
                            {{-- <div class="modal-footer d-block">
                            <div class="d-grid">
                                    <button class="btn btn-lg font-16 btn-info" id="btn-new-event">
                                        <i class="mdi mdi-plus-circle-outline"></i> Create New Event
                                    </button>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-target="#multiple-two" data-bs-toggle="modal"
                                        data-bs-dismiss="modal">Next
                                </button>
                            </div> --}}
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script> -->

    <!-- third party js -->
    @include('layouts.partials.datatable-script')
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {{--    <script src="https://coderthemes.com/ubold/layouts/default/assets/libs/clockpicker/bootstrap-clockpicker.min.js"> --}}
    // <script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.js"></script>



    <!-- third party js ends -->
    <script>
        $(document).on('click', '#mediumButton', function(event) {
            event.preventDefault();
            let href = $(this).attr('data-attr');
            $('#estimateLimitModal').modal("show");
        });
        $(document).ready(function() {
            // var fil_user_id =0;
            var fil_estimate_start = moment().subtract(29, 'days');
            var fil_estimate_end = moment();

            if (localStorage.hasOwnProperty("fil_estimate_start")) {
                fil_estimate_start = moment(localStorage.getItem('fil_estimate_start'));
            }
            if (localStorage.hasOwnProperty("fil_estimate_end")) {
                fil_estimate_end = moment(localStorage.getItem('fil_estimate_end'));
            }
            $('#followup_date').datepicker({
                startDate: new Date(),
                format: "dd/mm/yyyy",
                autoclose: true,
                daysOfWeekDisabled: [0, 7]
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $("#follow-up-form").parsley({
                errorsContainer: function(el) {
                    return el.$element.closest('.form-error');
                },
            });

            $('#followup_time').clockpicker({
                placement: 'bottom',
                align: 'left',
                autoclose: true,
                'default': 'now'
            });

            if (localStorage.hasOwnProperty("fil_user_id")) {
                fil_user_id = localStorage.getItem('fil_user_id');
                $('#fil_team_member').val(fil_user_id);
            } else {

                var fil_user_id = {{auth()->user()->id}};
                localStorage.setItem('fil_user_id', fil_user_id);
                $('#fil_team_member').val(fil_user_id);
            }

            "use strict";
            var table = $("#estimate-datatable").DataTable({
                // dom: 'Bfrtip',
                dom: "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'Show all']
                ],
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                lengthChange: !1,
                buttons: [{
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
                        title: 'Estimate List',
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
                            localStorage.removeItem('estimate-datatable');
                            location.reload();
                            /*dt.clear().draw();
                            dt.ajax.reload();*/
                        }
                    }
                    /*  {
                          text: '<i class="mdi mdi-plus-circle"></i> New',
                          attr: {
                              id: 'btn-add-estimate',
                              // class:'btn-info',
                          },
                          action: function ( e, dt, node, config ) {
                              window.location.href = '{{ route('tenant.quotes.new', ['tenant' => $segment]) }}';
                        }
                    },*/
                    /* {
                         extend: 'print',
                         title: 'Estimate List',
                         customize: function (win) {
                             $(win.document.body)
                                 .css('font-size', '10pt')
                                 .prepend(
                                     '<img src="http://192.168.5.103:8080/assets/images/logo-dark.png" style="position:absolute; top:0; left:0;" />'
                                 );

                             $(win.document.body).find('table')
                                 .addClass('compact')
                                 .css('font-size', 'inherit');
                         },
                         exportOptions: {
                             columns: ':visible'
                         }
                     },*/

                    /* {
                         extend: 'csv',
                         title: 'Estimate List',
                         exportOptions: {
                             columns: ':visible'
                         }
                     },*/
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function(settings, data) {
                    @if (request()->get('status'))
                        data.fil_status =
                            '{{ request()->get('status') != 'Total' ? request()->get('status') : '' }}';
                    @else
                        data.fil_status = $('#fil_status').val();
                    @endif
                    data.fil_name = $('#fil_name').val();
                    data.fil_estimate_no = $('#fil_estimate_no').val();
                    data.fil_estimate_start = moment(fil_estimate_start).format("YYYY-MM-DD");
                    data.fil_estimate_end = moment(fil_estimate_end).format("YYYY-MM-DD");
                    data.fil_team_member = $('#fil_team_member').val();
                },
                stateLoadParams: function(settings, data) {
                    @if (request()->get('status'))
                        $('#fil_status').val(
                            '{{ request()->get('status') != 'Total' ? request()->get('status') : '' }}'
                        );
                    @else
                        $('#fil_status').val(data.fil_status);
                    @endif

                    $('#fil_name').val(data.fil_name);
                    $('#fil_estimate_no').val(data.fil_estimate_no);
                    $('#estimate_date_range span').html(moment(fil_estimate_start, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY') + ' - ' + moment(fil_estimate_end, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY'));
                    $('#fil_team_member').val(data.fil_team_member);
                },
                stateSaveCallback: function(settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.quotes.index', ['tenant' => $segment]) }}",
                    data: function(d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.estimate_no = $('#fil_estimate_no').val(),
                            d.search = $('input[type="search"]').val(),
                            d.fil_estimate_start = moment(fil_estimate_start).format("YYYY-MM-DD"),
                            d.fil_estimate_end = moment(fil_estimate_end).format("YYYY-MM-DD"),
                            d.fil_team_member = $('#fil_team_member').val()
                    }
                },
                "order": [
                    [0, 'desc'],

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
                        data: 'reference',
                        name: 'reference',
                        'visible': false
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'expiry_date',
                        name: 'expiry_date',
                        'visible': false
                    },
                    {
                        data: 'subtotal',
                        name: 'subtotal',
                        'visible': false
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
                           /* return '<select class=" disabled badge ' + sel_bg_color +
                                '" id="example-select_' + row.id + '" onchange="getval(this,' +
                                follow_action + ')" disabled><option ' + sel_draft +
                                '>Draft</option><option ' + sel_sent + '>Sent</option><option ' +
                                sel_inprogress + '>Inprogress</option><option ' + sel_accept +
                                '>Accept</option> <option ' + sel_decline +
                                '>Decline</option></select>';*/
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        render: function(data, type, row) {

                            var edit_fun = "{{ url('quotes/edit') }}/" + row.action;
                            var delete_fun = "remove_id('" + row.action +
                                "','{{ route('tenant.quotes.delete', ['tenant' => $segment]) }}','#estimate-datatable')";
                            {{-- var follow_up_fun = "follow_up_list('" + row.action + "','{{route('tenant.event.index', ['tenant' => $segment])}}','enc',0,1)"; --}}
                            var duplicate_est_fun = "estimate_duplicate('" + row.action + "')";
                            var follow_up_fun =
                                " openFollowUpModal('#follow-up-modal','Schedule Follow Up','#follow-up-form','.modal-title','" +
                                row.action + "','0','" + row.estimate_no + "','" + row.status +
                                "','{{ route('tenant.event.index', ['tenant' => $segment]) }}',1,'" + row.customer_name + "','" +
                                row.mobile_no + "')";
                            var view_fun = "{{ url('quotes/show') }}/" + row.action;
                            var status_fun = "activity_change_status_fun('"+row.status+"','"+row.estimate_no+"','"+row.action+"',"+row.customer_id+")";
                            /*return '<div class="invoice-action">' +
    {{--                                @if (in_array('edit-estimate', $user_perm) || auth()->user()->company_id == null) --}}
                                        '<a href="' + edit_fun +
                                            '" title="Edit" class="action-icon me-1" id="edit_' + row
                                            .action + '">' +
                                            '<i class="mdi mdi-square-edit-outline fs-4"></i>' +
                                            '</a>' +
    {{--                                @endif --}}
                                '<a href="' + row.download_action +
                                    '" title="Download" class="action-icon me-1" download>' +
                                    '<i class="mdi mdi-download fs-4"></i>' +
                                    '</a>' +
                                    /!* '<a href="'+view_fun+'" title="View" class="anctio-icon me-1 text-muted" id="view_' + row.action + '">' +
                                     '<i class="mdi mdi-eye fs-4"></i>' +
                                     '</a>' +*!/
                                    /!*' <a href="javascript:void(0)" title="Follow up" id="follow_up_' +
                                    row.action + '"  onclick="' + follow_up_fun +
                                    '"><i class="mdi mdi mdi-av-timer me-1 text-muted fs-4"></i></a>' +*!/
                                    '<a href="javascript:void(0)" title="Share" class="copy_text" data-url="{!! url('/quotes/generate-link') !!}/' +
                                    row.action +
                                    '"><i class="mdi mdi-share-variant me-1 text-muted fs-4"></i></a>' +
                                    ' <a href="javascript:void(0)" title="Duplicate" class="action-icon" id="duplicate_' +
                                    row.action + '" onclick="' + duplicate_est_fun + '">' +
                                    '<i class="mdi mdi-content-copy fs-4"></i>' +
                                    '</a>' +
    {{--                                @if (in_array('remove-estimate', $user_perm) || auth()->user()->company_id == null) --}}
                                        '<a href="javascript:void(0)" title="Delete" class="action-icon" id="remove_' +
                                        row.action + '" onclick="' + delete_fun + '">' +
                                            '<i class="mdi mdi-delete fs-4"></i>' +
                                            '</a>' +
    {{--                                @endif --}}
                                '</div>';*/

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
                               /* ' <a href="javascript:void(0)" title="Duplicate" class="dropdown-item" id="duplicate_' +
                                row.action + '" onclick="' + duplicate_est_fun + '">' +
                                '<i class="mdi mdi-content-copy me-2"></i>Duplicate</a>' +
                                '<a href="javascript:void(0)" title="Delete" class="dropdown-item" id="remove_' +
                                row.action + '" onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete me-2"></i>Remove' +
                                '</a>' +*/

                                /*  ' <a class="dropdown-item" href="javascript:void(0)" id="follow_up_' + row.action + '"  onclick="' + follow_up_fun + '"><i class="mdi mdi-timeline-clock me-2 text-muted vertical-middle"></i>Follow up</a>' +
                                  ' <a class="dropdown-item" href="' + view_fun + '" id="view_' + row.action + '"><i class="mdi mdi-eye me-2 text-muted vertical-middle"></i>View</a>' +

                                  ' <a class="dropdown-item" href="javascript:void(0)" id="remove_' + row.action + '" onclick="' + delete_fun + '"><i class="mdi mdi-delete me-2 text-muted vertical-middle"></i>Remove</a>' +
                                  ' <a class="dropdown-item" href="javascript:void(0)" id="remove_' + row.action + '" onclick="' + delete_fun + '"><i class="mdi mdi-content-copy me-2 text-muted vertical-middle"></i>Remove</a>' +*/
                                ' </div>' +
                                ' </div>' : '';
                        }
                    },
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

            table.on('click', 'tr a.copy_text', function(e) {
                e.preventDefault();
                var copyText = $(this).attr('data-url');

                document.addEventListener('copy', function(e) {
                    e.clipboardData.setData('text/plain', copyText);
                    e.preventDefault();
                }, true);
                document.execCommand('copy');
                toastrSuccess('Successfully copied url');
            });

            $('#fil_status,#fil_name,#fil_estimate_no,#fil_team_member').change(function() {
                let fil_user_id = $('#fil_team_member').val();
                localStorage.setItem('fil_user_id', fil_user_id);
                table.draw();
                if (typeof(Storage) !== "undefined") {
                    // Retrieve the existing data from localStorage
                    var data = localStorage.getItem('duetoday-datatable'); // Replace 'your_key' with the actual key name
                    var dataA = localStorage.getItem('upcoming-datatable'); // Replace 'your_key' with the actual key name
                    var dataB = localStorage.getItem('overdue-datatable'); // Replace 'your_key' with the actual key name
                    var dataC = localStorage.getItem('someday-datatable'); // Replace 'your_key' with the actual key name
                    var dataD = localStorage.getItem('never-followup-datatable'); // Replace 'your_key' with the actual key name
                    var dataE = localStorage.getItem('duetoday-datatable-dashboard'); // Replace 'your_key' with the actual key name
                    var dataF = localStorage.getItem('customer-datatable'); // Replace 'your_key' with the actual key name

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
                    localStorage.setItem('customer-datatable', updatedDataF); // Replace 'your_key' with the actual key name

                    // Confirmation message
                    console.log('Value updated successfully!');
                } else {
                    console.log('Browser does not support localStorage');
                }
            });

            $('#resetFilter').click(function() {
                $('input[type=text]').val('');
                $('#fil_status').val('');
                table
                    .search('')
                    .columns().search('')
                    .draw();
            });

            $('.follow-up-form').on('submit', function(e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.event.store', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function() {
                            $("#follow_up_button").prop('disabled', true);
                            $("#follow_up_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function(data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $("#follow_up_button").prop('disabled', false);
                            $("#follow_up_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                            $('#follow-up-modal').modal('toggle');
                            $("#estimate-datatable").DataTable().ajax.reload();
                        },
                        error: function(xhr, status, error) {
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
                            $("#follow_up_button").prop('disabled', false);
                            $("#follow_up_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function(data) {
                            $("#follow_up_button").html('Save');
                            $("#follow_up_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.activity-change-estimate-status-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        // async: false,
                        type: 'POST',
                        url: '{{ route('tenant.lead.activity-change-estimate-status-saves', ['tenant' => $segment]) }}',
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
                            /*timelineActivity('{{ Request::segment(3) }}');
                            $('#follow-up-modal').modal('toggle');
                            $("#activity_change_estimate_status_form_buttons").prop('disabled', false);
                            $("#activity_change_estimate_status_form_buttons").html('<i class="mdi mdi-plus-circle-outline"></i> Save');
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
                                '<i class="mdi mdi-plus-circle-outline"></i> Save');
                        },
                        complete: function (data) {
                            $("#activity_change_estimate_status_form_buttons").html(
                                '<i class="mdi mdi-plus-circle-outline"></i> Save');
                            $("#activity_change_estimate_status_form_buttons").prop('disabled',
                                false);
                        }
                    });
                }
            });
        });

        function getval(sel, action, estimate_no, customer_name, mobile_no) {
            openFollowUpModal('#follow-up-modal', 'Schedule Follow Up', '#follow-up-form', '.modal-title', action, 0,
                estimate_no, sel.value, '{{ route('tenant.event.index', ['tenant' => $segment]) }}', 1, customer_name, mobile_no)
        }

        function activity_change_status_fun(status, estimate_no, estimate_id,activity_customer_id) {
            $('input[name="activity_estimate_status"][value="' + status + '"]').prop("checked", true);
            $("#activity-change-estimate-status-modal #activity_estimate_notes").focus();
            // $("#activity-change-estimate-status-modal #activity_id").val(id);
            $("#activity-change-estimate-status-modal #old_activity_status").val(status);
            $("#activity-change-estimate-status-modal #activity_estimate_id").val(estimate_id);
            $("#activity-change-estimate-status-modal #activity_estimate_no").val(estimate_no);
            $("#activity-change-estimate-status-modal #activity_customer_id").val(activity_customer_id);
            $('#activity-change-estimate-status-modal').modal('toggle');
        }
    </script>
@endpush
