@extends('layouts.app')
@section('title', 'Client')
@push('styles')
    <link href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .login-social-icon::before {
            content: "";
            position: absolute;
            width: 28%;
            height: 1px;
            left: 0;
            right: 0;
            background-color: #E9E9EF;
            top: 10px;
        }

        .login-social-icon::after {
            content: "";
            position: absolute;
            width: 28%;
            height: 1px;
            left: auto;
            right: 0;
            background-color: #E9E9EF;
            top: 10px;
        }
    </style>
@endpush
@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            {{-- <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Client List</li>
                            </ol>--}}

                            <div id="estimate_date_range_admin" class="form-control mb-2" data-toggle="date-picker-range"
                                data-target-display="#selectedValues" data-cancel-class="btn-light">
                                <i class="mdi mdi-calendar"></i>&nbsp;
                                <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                            </div>

                        </div>
                        <h4 class="page-title">Client</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-8">
                            <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                                <div class="col-auto">
                                    <div class="d-flex align-items-center">
                                        <label for="fil_name" class="visually-hidden">Name</label>
                                        <input type="text" class="form-control" id="fil_name" name="fil_name"
                                            placeholder="name...">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex align-items-center">
                                        <label for="fil_status" class="me-2">Status</label>
                                        <select class="form-select" id="fil_status" name="fil_status">
                                            <option value="">Choose...</option>
                                            <option value="Pending">Pending</option>
                                            <option value="New">New</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Rejected">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-xl-4">
                            <div class="text-xl-end mt-xl-0 mt-2">
                                <button type="button" class="btn btn-secondary waves-effect waves-light mr-1 mb-2"
                                        id="resetFilter">
                                    <i class="mdi mdi-filter"></i> Reset Filters
                                </button>
                                {{--                            <a href="javascript:void(0);" class="btn btn-info mb-2" --}}
                                {{--                               onclick="openModal('#client-modal','Create Client','#client-form','.modal-title',id=0)"><i --}}
                                {{--                                    class="mdi mdi-plus-circle"></i> Add Client</a> --}}

                                <div class="dropdown btn-group mb-2">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            id="select_count">0</span>Bulk Action

                                    </button>
                                    <div class="dropdown-menu dropdown-menu-animated">
                                        {{--                                    <a href="javascript:void(0);" class="dropdown-item active_status_all"><i --}}
                                        {{--                                            class="mdi mdi-update"></i> Active All</a> --}}
                                        {{--                                    <a href="javascript:void(0);" class="dropdown-item deactive_status_all"><i --}}
                                        {{--                                            class="mdi mdi-update"></i> Deactive All</a> --}}
                                        <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                                class="mdi mdi-delete-circle"></i> Delete All</a>
                                    </div>
                                </div>

                            </div>
                        </div><!-- end col-->
                    </div> <!-- end row -->
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            {{--                        <div class="table-responsive"> --}}
                            <table id="client-datatable" class="table table-centered table-striped table-sm nowrap w-100">
                                <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Company name</th>
                                    <th>Mobile no</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Pincode</th>
                                    <th>Country</th>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>Business Category</th>
                                    <th>Website</th>
                                    <th>Tax</th>
                                    <th>Expire on</th>
                                    <th>Remaining day</th>
                                    <th>Active plan</th>
                                    <th>Plan Status</th>
                                    <th>Total users</th>
                                    <th>Active users</th>
                                    <th>Total leads</th>
                                    <th>Tot Est</th>
                                    <th>Storage(GB)</th>
                                    {{-- <th>Status</th> --}}
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                            {{--                        </div> --}}
                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->
            <div id="client-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-right" style="width: 100%;">
                    <div class="modal-content" style="height: 100%;">
                        <div class="modal-header border-1">
                            <h4 class="modal-title">Create Client</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5 class="text-uppercase bg-light pt-3 ps-2 pb-1 pe-2">
                                <p class="font-13"><strong>Customer/Mobile: </strong> <span
                                        class="float-end customer_span"></span></p>
                                <p class="font-13"><strong>Email: </strong> <span
                                        class="float-end email_span text-lowercase"></span></p>
                            </h5>
                            <form class="ps-3 pe-3 client-form" id="client-form" action="#">
                                <div class="row">
                                    <div class="col-md-6 mb-2 form-error">
                                        <label for="plan_start_date" class="form-label">Start Date <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="plan_start_date"
                                                class="form-control form-control-light" id="plan_start_date"
                                                value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}">
                                            <input class="form-control" type="hidden" id="id" name="id"
                                                value="0">
                                            <span class="input-group-text bg-secondary border-secondary text-white">
                                                <i class="mdi mdi-calendar-range font-13"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3 form-error">
                                        <label for="plan_end_date" class="form-label">End Date <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="plan_end_date"
                                                class="form-control form-control-light" id="plan_end_date"
                                                value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}">
                                            <span class="input-group-text bg-secondary border-secondary text-white">
                                                <i class="mdi mdi-calendar-range font-13"></i>
                                            </span>
                                        </div>
                                        <span class="form-text text-muted">
                                            <small>Notes : <small class="day_count_small">0</small> days. <a
                                                    href="javascript: void(0);" onclick="clear_days();">clear</a></small>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <p class="or"><span
                                                style="width: 40px;display: inline-flex;aspect-ratio: 1;justify-content: center;background-color: #ffffff;align-items: center;border: 2px solid #00000033;border-radius: 50%;">or</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="remaining_days" class="form-label">Days</label>
                                    <input type="text" class="form-control" id="remaining_days" name="remaining_days"
                                        placeholder="Enter days"/>
                                    <span class="form-text text-muted">
                                        <small>Notes : <small class="day_date_small"> </small> <a
                                                href="javascript: void(0);"
                                                onclick="clear_dates();">clear</a></small>
                                    </span>
                                </div>
                                <div class="form-floating mb-3 mt-1">
                                    <select class="form-select bg-light text-dark" id="plans" name="plan_id"
                                            required="">
                                        <option value="0" id="selected">Choose</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}" id="{{ $plan->id }}">
                                                {{ $plan->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="plans" class="form-label">Plans<span class="text-danger">*</span></label>
                                </div>
                                <div class="mb-3" style="display: flex;">
                                    <div class="col-6" style="display: flex;">
                                        <label for="remaining_days" class="form-label">User Limit :- </label>
                                        <p id="userLimit"> 0</p>
                                    </div>
                                    <div class="col-6" style="display: flex;">
                                        <label for="remaining_days" class="form-label">Estimate Limit :- </label>
                                        <p id="estimateLimit"> 0</p>
                                    </div>
                                </div>
                                {{--                            <div class="mb-3"> --}}
                                {{--                                <label for="description" class="form-label">Description</label> --}}
                                {{--                                <textarea class="form-control" id="description" name="description" placeholder="Enter description"></textarea> --}}
                                {{--                            </div> --}}

                                <div class="mb-3 text-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" id="client_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <div id="storage-capacity-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-right" style="width: 100%;">
                    <div class="modal-content" style="height: 100%;">
                        <div class="modal-header border-1">
                            <h4 class="modal-title">Create Client</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5 class="text-uppercase bg-light pt-3 ps-2 pb-1 pe-2">
                                <p class="font-13"><strong>Customer/Mobile: </strong> <span
                                        class="float-end customer_span"></span></p>
                                <p class="font-13"><strong>Email: </strong> <span
                                        class="float-end email_span text-lowercase"></span></p>
                                <p class="font-13"><strong>Total Storage: </strong> <span
                                        class="float-end storage_capacity_span text-uppercase"></span></p>
                            </h5>
                            <form class="storage-capacity-form" id="storage-capacity-form" action="#">
                                @csrf
                                <div class="mb-3">
                                    <label for="storage_capacity" class="form-label">Storage</label>
                                    <input type="text" class="form-control" id="storage_capacity" name="storage_capacity"
                                        placeholder="Enter Storage Capacity" required/>
                                    <input class="form-control" type="hidden" id="id" name="id"
                                        value="0">
                                </div>

                                <div class="mb-3 text-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" id="storage_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->


        </div>
    </div>
</div>


@endsection
@push('scripts')
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>

    <!-- third party js -->
    @include('layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> 
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="{{ asset('assets/js/admin-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('assets/js/pages/demo.datatable-init.js')}}"></script> --}}
    <!-- end demo js-->
    <script>
        function clear_days() {
            $(".day_count_small").html(0);
            // $('#plan_end_date').datepicker("setDate", moment().format('DD/MM/YYYY'));
            $('#plan_start_date').datepicker("setDate", '');
            $('#plan_end_date').datepicker("setDate", '');
        }

        function clear_dates() {
            $(".day_date_small").html('');
            $("#remaining_days").val('');
            // $('#day_date_small').datepicker("setDate",moment().format('DD/MM/YYYY'));
        }

        function myfunc(start, end) {
            var plan_start_date = $("#plan_start_date").datepicker("getDate");
            var plan_end_date = $("#plan_end_date").datepicker("getDate");

            var millisecondsPerDay = 1000 * 60 * 60 * 24;

            var millisBetween = plan_end_date - plan_start_date;
            var days = millisBetween / millisecondsPerDay;
            $(".day_count_small").html(Math.round(days));
        }

        $(document).ready(function () {
            var fil_estimate_start = moment();
            var fil_estimate_end = moment();


            if (localStorage.hasOwnProperty("fil_estimate_start")) {
                fil_estimate_start = moment(localStorage.getItem('fil_estimate_start'));
            } else {
                localStorage.setItem('fil_estimate_start', fil_estimate_start.format('YYYY-MM-DD'));
            }
            if (localStorage.hasOwnProperty("fil_estimate_end")) {
                fil_estimate_end = moment(localStorage.getItem('fil_estimate_end'));
            } else {
                localStorage.setItem('fil_estimate_end', fil_estimate_end.format('YYYY-MM-DD'));
            }
            $('#estimate_date_range_admin span').html(fil_estimate_start.format('MMMM D, YYYY') + ' - ' +
                fil_estimate_end.format('MMMM D, YYYY'));
            $('#estimate_date_range_admin').daterangepicker({
                startDate: fil_estimate_start,
                endDate: fil_estimate_end,
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
            }, cbs);

            function cbs(fil_estimate_start, fil_estimate_end) {
                $('#estimate_date_range_admin span').html(fil_estimate_start.format('MMMM D, YYYY') + ' - ' +
                    fil_estimate_end.format('MMMM D, YYYY'));
                // let date_range = fil_estimate_start.format('YYYY-MM-DD') + '_' + fil_estimate_end.format('YYYY-MM-DD');
                localStorage.setItem('fil_estimate_start', fil_estimate_start.format('YYYY-MM-DD'));
                localStorage.setItem('fil_estimate_end', fil_estimate_end.format('YYYY-MM-DD'));
                location.reload();
            }

            $("#remaining_days").keyup(function () {
                let days = $(this).val();
                var start_date = moment().format("DD/MM/YYYY");
                var end = moment().format("DD/MM/YYYY");
                var end_date = moment(end, "DD/MM/YYYY").add(days, 'days');
                $('.day_date_small').html(start_date + ' - ' + moment(end_date).format("DD/MM/YYYY"));
            });
            $('#plan_start_date').datepicker({
                startDate: new Date(),
                format: "dd/mm/yyyy",
                autoclose: true,
                daysOfWeekDisabled: [0, 7],
                // onSelect: function (dateText, inst) {
                //     $(this).change();
                // }
            }).on("changeDate", function (e) {
                myfunc();
            });

            $('#plan_end_date').datepicker({
                startDate: new Date(),
                format: "dd/mm/yyyy",
                autoclose: true,
                daysOfWeekDisabled: [0, 7],
                // onSelect: function (dateText, inst) {
                //     $(this).change();
                // }
            }).on("changeDate", function (e) {
                myfunc();
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            "use strict";
            var table = $("#client-datatable").DataTable({
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
                        title: 'Client List',
                        exportOptions: {
                            columns: ':visible',
                            modifier : {
                                // DataTables core
                                page : 'all'
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="mdi mdi-microsoft-excel fs-4"></i>',
                        attr: {
                            title: 'Excel',
                            class: 'btn btn-light buttons-html5 buttons-excel',
                        },
                        title: 'Client List',
                        exportOptions: {
                            columns: ':visible',
                            modifier : {
                                page : 'all'
                            }
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="mdi mdi-format-list-bulleted fs-4"></i>',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis',
                        },
                        title: 'Client List',
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
                    data.fil_status = $('#fil_status').val();
                    data.fil_name = $('#fil_name').val();
                    data.fil_estimate_start = moment(fil_estimate_start).format("YYYY-MM-DD");
                    data.fil_estimate_end = moment(fil_estimate_end).format("YYYY-MM-DD");

                },
                stateLoadParams: function (settings, data) {
                    $('#fil_status').val(data.fil_status);
                    $('#fil_name').val(data.fil_name);
                    $('#estimate_date_range span').html(moment(fil_estimate_start, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY') + ' - ' + moment(fil_estimate_end, 'YYYY-MM-DD').format(
                        'MMMM D, YYYY'));

                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('admin.client.index') }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                        d.fil_estimate_start = moment(fil_estimate_start).format("YYYY-MM-DD"),
                            d.fil_estimate_end = moment(fil_estimate_end).format("YYYY-MM-DD")
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                    "targets": 0,
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    render: function (data, type, row) {
                        return '<div class="form-check"><input type="checkbox" class="single_checkbox form-check-input" data-id="' +
                            row.action +
                            '"><label class="form-check-label" for="customCheckcolor1">' + row
                                .id + '</label></div>';
                    }
                },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: true
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: true
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'mobile_no',
                        name: 'mobile_no'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        visible: false,
                    },
                    {
                        data: 'address',
                        name: 'address',
                        render: function (data, type, row) {
                            var str = row.address;
                            return funcStrLimit(str);
                        },
                        visible: false,
                    },
                    {
                        data: 'pincode',
                        name: 'pincode',
                        visible: false,
                    },
                    {
                        data: 'country_name',
                        name: 'country_name',
                        visible: false,
                    },
                    {
                        data: 'state_name',
                        name: 'state_name',
                        visible: false,
                    },
                    {
                        data: 'city_name',
                        name: 'city_name',
                        visible: false,
                    },
                    {
                        data: 'business_category_name',
                        name: 'business_category_name',
                        visible: false,
                    },
                    {
                        data: 'website_link',
                        name: 'website_link',
                        visible: false,
                    },
                    {
                        data: 'gst_no',
                        name: 'gst_no',
                        visible: false,
                    },
                    {
                        data: 'plan_end_date',
                        name: 'plan_end_date',
                    },
                    {
                        data: 'remaining_days',
                        name: 'remaining_days',
                    },
                    {
                        data: 'active_plan',
                        name: 'active_plan'
                    },
                    {
                        data: 'plan_status',
                        name: 'plan_status',
                        render: function (data, type, row) {
                            if (data == 0) {
                                return '<span class="badge badge-info-lighten">7 Days Trial</span>';
                            } else if (data == 1) {
                                return '<span class="badge badge-warning-lighten">7 Days Expired</span>';
                            } else if (data == 2) {
                                return '<span class="badge badge-primary-lighten">15 days Trial</span>';
                            } else if (data == 3) {
                                return '<span class="badge badge-danger-lighten">15 Days Expires</span>';
                            } else if (data == 4) {
                                return '<span class="badge badge-danger-lighten">Free plan</span>';
                            } else if (data == 5) {
                                return '<span class="badge badge-success-lighten">Paid</span>';
                            }

                        }
                    },
                    {
                        data: 'user_count',
                        name: 'user_count'
                    },
                    {
                        data: 'active_user',
                        name: 'active_user'
                    },
                    {
                        data: 'lead_count',
                        name: 'lead_count',
                        orderable: false,
                    },
                    {
                        data: 'estimate_count',
                        name: 'estimate_count'
                    },
                    {
                        data: 'total_file_size',
                        name: 'file_size'
                    },
                    // {
                    //     data: 'status',
                    //     name: 'status',
                    //     render: function(data, type, row) {
                    //         var fun_status = "client_status('" + row.action +
                    //             "','{{ route('admin.client.edit-status') }}','#client-datatable')";
                    //         if (data == 'Pending')
                    //             return '<span class="badge badge-info-lighten" onclick="' +
                    //                 fun_status + '">Pending</span>';
                    //         else if (data == 'New')
                    //             return '<span class="badge badge-primary-lighten" onclick="javascript:void(0);">New</span>';
                    //         else if (data == 'Approved')
                    //             return '<span class="badge badge-success-lighten" onclick="javascript:void(0);">Approved</span>';
                    //         else {
                    //             return '<span class="badge badge-danger-lighten" onclick="javascript:void(0);">Rejected</span>';
                    //         }

                    //     }
                    // },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        render: function (data, type, row) {

                            var sp_fun = "storage_capacity_id('" + row.action + "')";
                            var edit_fun = "view_id('" + row.action + "')";
                            var delete_fun = "remove_id('" + row.action +
                                "','{{ route('admin.client.delete') }}','#client-datatable')";

                            return '<div class="invoice-action">' +
                                '<a href="javascript:void(0)" class="action-icon mr-1" id="edit_' +
                                row.action + '" onclick="' + edit_fun + '">' +
                                '<i class="mdi mdi-eye"></i>' +
                                '</a>' +
                                '<a href="javascript:void(0)" class="action-icon mr-1" id="sp_' +
                                row.action + '" onclick="' + sp_fun + '">' +
                                '<i class="mdi mdi-layers" title="Upgrade Storage"></i>' +
                                '</a>' +
                                '<a href="javascript:void(0)" class="action-icon" id="remove_' + row
                                    .action + '"  onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete"></i>' +
                                '</a>' +
                                '</div>';
                        }
                    },
                ],

                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#client-datatable_wrapper .col-md-6:eq(0)"), $(
                "#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })

            $('#fil_status,#fil_name').change(function () {
                table.draw();
            });

            $('#resetFilter').click(function () {
                $('input[type=text]').val('');
                $('#fil_status').val('');
                table
                    .search('')
                    .columns().search('')
                    .draw();
            });


            formValition('#storage-capacity-form');
            $('.client-form').on('submit', function (e) {
                e.preventDefault();
                // if ( $(this).parsley().isValid() ) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin.client.plan.update') }}',
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: new FormData(this),
                    // data: $('.category-form').serialize(),
                    dataType: "json",
                    beforeSend: function () {
                        $("#client_button").prop('disabled', true);
                        $("#client_button").html(
                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    success: function (data) {
                        toastrSuccess('Successfully saved...', 'Success');
                        $('#client-modal').modal('toggle');
                        resetForm("#client-form")
                        $("#client-datatable").DataTable().ajax.reload();
                        $("#client_button").prop('disabled', false);
                        $("#client_button").html('<i class="uil-arrow-circle-right"></i> Save');
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText
                        console.log(xhr.status);
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
                                toastrError(xhr.responseJSON.success, 'Error');
                        }
                        $("#client_button").prop('disabled', false);
                        $("#client_button").html('<i class="uil-arrow-circle-right"></i> Save');
                    },
                    complete: function (data) {
                        $("#client_button").html('Save');
                        $("#client_button").prop(
                            '<i class="uil-arrow-circle-right"></i> disabled', false);
                    }
                });
                // }
            });

            $('.storage-capacity-form').on('submit', function (e) {
                e.preventDefault();
                // if ( $(this).parsley().isValid() ) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin.client.storage.update') }}',
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: new FormData(this),
                    // data: $('.category-form').serialize(),
                    dataType: "json",
                    beforeSend: function () {
                        $("#storage_button").prop('disabled', true);
                        $("#storage_button").html(
                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    success: function (data) {
                        toastrSuccess('Successfully saved...', 'Success');
                        $('#storage-capacity-modal').modal('toggle');
                        resetForm("#storage-capacity-form")
                        $("#client-datatable").DataTable().ajax.reload();
                        $("#storage_button").prop('disabled', false);
                        $("#storage_button").html('<i class="uil-arrow-circle-right"></i> Save');
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText
                        console.log(xhr.status);
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
                                toastrError(xhr.responseJSON.success, 'Error');
                        }
                        $("#storage_button").prop('disabled', false);
                        $("#storage_button").html('<i class="uil-arrow-circle-right"></i> Save');
                    },
                    complete: function (data) {
                        $("#storage_button").html('Save');
                        $("#storage_button").prop(
                            '<i class="uil-arrow-circle-right"></i> disabled', false);
                    }
                });
                // }
            });
        });

        function client_status(id, url, tableName) {
            Swal.fire({
                title: "Are you sure?",
                text: "Are you sure want to",
                type: "warning",
                showCancelButton: !0,
                confirmButtonColor: "#3085D6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Approved",
                cancelButtonText: "Rejected",
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-danger ml-1",
                buttonsStyling: !1,
            }).then((function (t) {
                if (t.value) {
                    $.ajax({
                        async: false,
                        type: "POST",
                        url: url,
                        data: {
                            id: id,
                            status: "Approved"
                        },
                        dataType: "json",
                        success: function (data, textStatus, jqXHR) {
                            toastrSuccess('Successfully updated');
                            $(tableName).DataTable().ajax.reload();
                        }
                    });
                } else if (t.dismiss === Swal.DismissReason.cancel) {
                    $.ajax({
                        async: false,
                        type: "POST",
                        url: url,
                        data: {
                            id: id,
                            status: "Rejected"
                        },
                        dataType: "json",
                        success: function (data, textStatus, jqXHR) {
                            toastrSuccess('Successfully updated');
                            $(tableName).DataTable().ajax.reload();
                        }
                    });
                }

            }))
        }

        function view_id(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('admin.client.show') }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    // resetFormValidation("#client-form");
                    $('#id').val(res.data.id);
                    $('.customer_span').html(res.data.name + '/' + res.data.mobile_no);
                    $('.email_span').html(res.data.email);
                    $('#description').val(res.data.description);
                    $('.modal-title').text('Upgrade Plan');
                    $('#client-modal').modal('toggle');
                    var planId = '#' + res.data.plan_id;
                    console.log(res.data);
                    $('#userLimit').text(res.data.planHistory.user_limit ? res.data.planHistory.user_limit :
                        'Unlimited');
                    $('#estimateLimit').text(res.data.planHistory.estimate_limit ?
                        res.data.planHistory.estimate_limit : 'Unlimited');
                    console.log(res.data.plan_id);
                    $("#plans option:selected").prop("selected", false);
                    if (res.data.plan_id !== null) {
                        $(planId).prop("selected", true);
                    } else {
                        $('#seleted').prop("selected", true);
                    }
                    getPlanDetails();
                }
            });
        }

        function storage_capacity_id(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('admin.client.storage-show') }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    resetForm("#storage-capacity-form")
                    // resetFormValidation("#client-form");
                    $('#storage-capacity-modal #id').val(res.data.id);
                    $('#storage-capacity-modal .customer_span').html(res.data.name + '/' + res.data.mobile_no);
                    $('#storage-capacity-modal .email_span').html(res.data.email);
                    let storage_capacity = '0 GB';
                    if(res.data.storage_capacity)
                        storage_capacity = res.data.storage_capacity+ ' GB'
                    $('#storage-capacity-modal .storage_capacity_span').html(storage_capacity);
                    $('#storage-capacity-modal .modal-title').text('Upgrade Storage');
                    $('#storage-capacity-modal').modal('toggle');
                }
            });
        }

        // function getPlanDetails() {
        //     var id = $("#plans option:selected").val();
        //     if (id == 0) {
        //         $('#userLimit').text(0);
        //         $('#estimateLimit').text(0);
        //     }
        //     $.ajax({
        //         type: "GET",
        //         url: "{{ route('admin.plans.show') }}",
        //         data: {
        //             id: id
        //         },
        //         dataType: "json",
        //         success: function(res) {
        //             console.log(res.data);
        //             if (res.data.users_limit === null) {
        //                 var user_limit = "Unlimited";
        //             } else {
        //                 var user_limit = res.data.users_limit;
        //             }
        //             if (res.data.estimate_limit === null) {
        //                 var estimate_limit = "Unlimited";
        //             } else {
        //                 var estimate_limit = res.data.estimate_limit;
        //             }
        //             $('#userLimit').text(user_limit);
        //             $('#estimateLimit').text(estimate_limit);
        //             // resetFormValidation("#client-form");
        //             // $('#id').val(res.data.id);
        //             // $('.customer_span').html(res.data.name + '/' + res.data.mobile_no);
        //             // $('.email_span').html(res.data.email);
        //             // $('#description').val(res.data.description);
        //             // $('.modal-title').text('Upgrade Plan');
        //             // $('#client-modal').modal('toggle');
        //             // var planId = '#' + res.data.plan_id;
        //             // console.log(res.data.plan_id);
        //             // $("#plans option:selected").prop("selected", false);
        //             // if (res.data.plan_id !== null) {
        //             //     console.log("called...");
        //             //     $(planId).prop("selected", true);
        //             // } else {
        //             //     $('#seleted').prop("selected", true);
        //             // }
        //         }
        //     });
        // }
        {{-- function edit_id(id) { --}}
        {{--    $.ajax({ --}}
        {{--        type: "GET", --}}
        {{--        url: "{{route('client.show')}}", --}}
        {{--        data: {id:id}, --}}
        {{--        dataType:"json", --}}
        {{--        success: function(res) { --}}
        {{--            resetFormValidation("#client-form"); --}}
        {{--            $('#id').val(res.data.id); --}}
        {{--            $('#name').val(res.data.name); --}}
        {{--            $('#description').val(res.data.description); --}}
        {{--            $('.modal-title').text('Edit Client'); --}}
        {{--            $('#client-modal').modal('toggle'); --}}
        {{--        } --}}
        {{--    }); --}}
        {{-- } --}}

        //Remove multiple record
        $('.delete_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            remove_id(join_selected_values, '{{ route('admin.client.delete') }}', '#client-datatable');
        });

        {{-- $('.active_status_all').on('click', function(e) { --}}
        {{--    var allVals = []; --}}
        {{--    $(".single_checkbox:checked").each(function() { --}}
        {{--        allVals.push($(this).attr('data-id')); --}}
        {{--    }); --}}
        {{--    var join_selected_values = allVals.join(","); --}}
        {{--    change_status(join_selected_values,0,'{{route('client.edit-status')}}','#client-datatable'); --}}
        {{-- }); --}}

        {{-- $('.deactive_status_all').on('click', function(e) { --}}
        {{--    var allVals = []; --}}
        {{--    $(".single_checkbox:checked").each(function() { --}}
        {{--        allVals.push($(this).attr('data-id')); --}}
        {{--    }); --}}
        {{--    var join_selected_values = allVals.join(","); --}}
        {{--    change_status(join_selected_values,1,'{{route('client.edit-status')}}','#client-datatable'); --}}
        {{-- }); --}}
    </script>
@endpush
