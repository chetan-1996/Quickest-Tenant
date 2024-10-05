@extends('layouts.app')
@section('title', 'Plan History')
@push('styles')
    <link href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
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
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Plan History List</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Plan History</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <table id="plan-history-datatable" class="table table-centered table-striped table-sm nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        {{-- <th><input type="checkbox" class="form-check-input" id="select_all"></th> --}}
                                        <th>User Name</th>
                                        <th>User Email</th>
                                        <th>Company Name</th>
                                        <th>Plan Name</th>
                                        <th>User Limite</th>
                                        <th>Estimate Limite</th>
                                        <th>Plan Start Date</th>
                                        <th>Plan End Date</th>
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
            <div id="plan-history-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-right" style="width: 100%;">
                    <div class="modal-content" style="height: 100%;">
                        <div class="modal-header border-1">
                            <h4 class="modal-title">Create Plan History</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 plan-history-form" id="plan-history-form" action="#">
                                @csrf
                                <div class="row">
                                    <div class="form-floating mb-3">
                                        <input class="form-control bg-light text-dark" type="text" id="user_name"
                                            name="name" required="" placeholder="Enter name" disabled autofocus>
                                        <label for="name" class="form-label">User Name <span
                                                class="text-danger">*</span></label>
                                        <input class="form-control" type="hidden" id="id" name="id" value="0">
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input class="form-control bg-light text-dark" type="text" id="plan_name"
                                            name="name" required="" placeholder="Enter name" disabled autofocus>
                                        <label for="name" class="form-label">Plan Name <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating mb-3 col-6">
                                        <input class="form-control bg-light text-dark" type="number" id="user_limit"
                                            name="user_limit" placeholder="Enter user limit" autofocus>
                                        <label for="user_limit" class="form-label">User Limit</label>
                                    </div>
                                    <div class="form-floating mb-3 col-6">
                                        <input class="form-control bg-light text-dark" type="number" id="estimate_limit"
                                            name="estimate_limit" placeholder="Enter estimate limit" autofocus>
                                        <label for="estimate_limit" class="form-label">Estimate Limit</label>
                                    </div>
                                </div>
                                <div class="mb-3 text-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" id="plans_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save</button>
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
    <!-- third party js -->
    @include('layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <!-- App js -->
    <script src="{{asset('assets/js/app.min.js')}}"></script>
    <script src="{{asset('assets/js/custom.js')}}"></script>
    <script src="{{ asset('assets/js/admin-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('assets/js/pages/demo.datatable-init.js')}}"></script> --}}
    <!-- end demo js-->
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            "use strict";
            var table = $("#plan-history-datatable").DataTable({
                // dom: 'Bfrtip',
                dom: "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                scrollX: !0,
                processing: true,
                serverSide: true,
                stateSave: true,
                lengthChange: !1,
                buttons: [{
                        extend: 'print',
                        title: 'Plan History List',
                        customize: function(win) {
                            $(win.document.body)
                                .css('font-size', '10pt')
                                .prepend(
                                    '<img src="http://192.168.5.103:8080/assets/images/logo-dark.png" style="position:absolute; top:0; left:0;" />'
                                );

                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    },
                    {
                        extend: 'pdf',
                        title: 'Plan History List',
                    },
                    {
                        extend: 'excel',
                        title: 'Plan History List',
                    },
                    {
                        extend: 'csv',
                        title: 'Plan History List',
                    }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                stateSaveParams: function(settings, data) {
                    data.fil_status = $('#fil_status').val();
                    data.fil_name = $('#fil_name').val();
                },
                stateLoadParams: function(settings, data) {
                    $('#fil_status').val(data.fil_status);
                    $('#fil_name').val(data.fil_name);
                },
                stateSaveCallback: function(settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('admin.plan_history.index') }}",
                    data: function(d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                columns: [{
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'user_email',
                        name: 'user_email'
                    },
                    {
                        data: 'user_company_name',
                        name: 'user_company_name'
                    },
                    {
                        data: 'plan_id',
                        name: 'plan_id'
                    },
                    {
                        data: 'user_limit',
                        name: 'user_limit'
                    },
                    {
                        data: 'estimate_limit',
                        name: 'estimate_limit'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        render: function(data, type, row) {

                            var edit_fun = "edit_id('" + row.action + "')";
                            // var delete_fun = "remove_id('" + row.action +
                            //     "','{{ route('admin.plan_history.delete') }}','#plan_history-datatable')";
                            return '<div class="invoice-action">' +
                                '<a href="javascript:void(0)" class="action-icon mr-1" id="edit_' +
                                row.action + '" onclick="' + edit_fun + '">' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
                                // '<a href="javascript:void(0)" class="action-icon" id="remove_' + row
                                // .action + '"  onclick="' + delete_fun + '">' +
                                // '<i class="mdi mdi-delete"></i>' +
                                // '</a>' +
                                '</div>';
                        }
                    },
                ],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#plan-history-datatable_wrapper .col-md-6:eq(0)"), $(
                "#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })

            $('#fil_status,#fil_name').change(function() {
                table.draw();
            });

            $('#resetFilter').click(function() {
                $('input[type=text]').val('');
                $('#fil_status').val('');
                table
                    .search('')
                    .columns().search('')
                    .draw();
            });

            formValition('#plan-history-form');
            $('.plan-history-form').on('submit', function(e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('admin.plan_history.store') }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        // data: $('.plan-form').serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $("#plans_button").prop('disabled', true);
                            $("#plans_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function(data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#plan-history-modal').modal('toggle');
                            table.ajax.reload();
                            $("#plans_button").prop('disabled', false);
                            $("#plans_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                        },
                        error: function(xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The plan is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#plans_button").prop('disabled', false);
                            $("#plans_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function(data) {
                            $("#plans_button").html('Save');
                            $("#plans_button").prop(
                                '<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });
        });

        function edit_id(id) {
            $.ajax({
                async: false,
                type: "GET",
                url: "{{ route('admin.plan_history.show') }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(res) {
                    resetFormValidation("#plan-history-form");
                    $('#id').val(res.data.id);
                    $('#user_name').val(res.data.tenants.name);
                    $('#plan_name').val(res.data.plans.name);
                    $('#user_limit').val(res.data.user_limit);
                    $('#estimate_limit').val(res.data.estimate_limit);
                    if (res.data.isDefault == 1) {
                        $('#isDefault').attr('checked', 'checked');
                    } else {
                        $('#isDefault').removeAttr('checked');
                    }
                    $('.modal-title').text('Edit Plan History');
                    $('#plan-history-modal').modal('toggle');
                }
            });
        }

        //Remove multiple record
        $('.delete_all').on('click', function(e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            remove_id(join_selected_values, '{{ route('admin.plan_history.delete') }}',
                '#plan-history-datatable');
        });

        $('.active_status_all').on('click', function(e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{ route('admin.plan_history.edit-status') }}',
                '#plan-history-datatable');
        });

        $('.deactive_status_all').on('click', function(e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{ route('admin.plan_history.edit-status') }}',
                '#plan-history-datatable');
        });
    </script>
@endpush
