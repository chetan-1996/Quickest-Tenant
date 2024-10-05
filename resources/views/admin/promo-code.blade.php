@extends('layouts.app')
@section('title', 'Promo Code')
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
                                <li class="breadcrumb-item active">Promo Code List</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Promo Code</h4>
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
                                {{-- <div class="col-auto">
                                    <div class="d-flex align-items-center">
                                        <label for="fil_status" class="me-2">Status</label>
                                        <select class="form-select" id="fil_status" name="fil_status">
                                            <option value="">Choose...</option>
                                            <option value="0">Active</option>
                                            <option value="1">Deactive</option>
                                        </select>
                                    </div>
                                </div> --}}
                            </form>
                        </div>
                        <div class="col-xl-4">
                            <div class="text-xl-end mt-xl-0 mt-2">
                                <button type="button" class="btn btn-secondary waves-effect waves-light mr-1 mb-2"
                                    id="resetFilter">
                                    <i class="mdi mdi-filter"></i> Reset Filters
                                </button>
                                <a href="javascript:void(0);" class="btn btn-info mb-2"
                                    onclick="openModal('#promo-code-modal','Create Promo Code','#promo-code-form','.modal-title',id=0)"><i
                                        class="mdi mdi-plus-circle"></i> Add Promo Code</a>

                                {{-- <div class="dropdown btn-group mb-2">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            id="select_count">0</span>Bulk Action
                                        {{--                                    <span class="badge badge-success-lighten" id="select_count">0</span> --}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-animated">
                                        <a href="javascript:void(0);" class="dropdown-item active_status_all"><i
                                                class="mdi mdi-update"></i> Active All</a>
                                        <a href="javascript:void(0);" class="dropdown-item deactive_status_all"><i
                                                class="mdi mdi-update"></i> Deactive All</a>
                                        <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                                class="mdi mdi-delete-circle"></i> Delete All</a>
                                    </div>
                                </div> --}}

                            </div>
                        </div><!-- end col-->
                    </div> <!-- end row -->
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <table id="promo-code-datatable" class="table table-centered table-striped table-sm nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        {{-- <th><input type="checkbox" class="form-check-input" id="select_all"></th> --}}
                                        <th>Promo Code</th>
                                        <th>Discount</th>
                                        <th>Discount Type</th>
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
            <div id="promo-code-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-right" style="width: 100%;">
                    <div class="modal-content" style="height: 100%;">
                        <div class="modal-header border-1">
                            <h4 class="modal-title">Create Promo Code</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 promo-code-form" id="promo-code-form" action="#">
                                @csrf
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="text" id="code" name="code"
                                        required="" placeholder="Enter Promo Code" autofocus>
                                    <label for="code" class="form-label">Promo Code <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="hidden" id="id" name="id" value="0">
                                </div>
                                <div class="row">
                                    <div class="form mb-3 col-6">
                                        <label class="form-label">Discount Type <span class="text-danger">*</span></label><br>
                                        {{-- <input class="form-control bg-light text-dark" type="number" id="type"
                                            required="" name="type" placeholder="Enter Discount Type" autofocus> --}}
                                        <input type="radio" required="" name="type" id="type" value="0"
                                            autofocus> Fixed<br>
                                        <input type="radio" required="" name="type" id="type1" value="1"
                                            autofocus> Percentage
                                    </div>
                                    <div class="form-floating mb-3 col-6">
                                        <input class="form-control bg-light text-dark" type="number" id="discount"
                                            name="discount" placeholder="Enter Discount" autofocus>
                                        <label for="discount" class="form-label"> Enter Discount <span
                                                class="text-danger">*</span></label>
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
            var table = $("#promo-code-datatable").DataTable({
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
                        title: 'Promo Code List',
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
                        title: 'Promo Code List',
                    },
                    {
                        extend: 'excel',
                        title: 'Promo Code List',
                    },
                    {
                        extend: 'csv',
                        title: 'Promo Code List',
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
                    url: "{{ route('admin.promo-code.index') }}",
                    data: function(d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                columns: [{
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'discount',
                        name: 'discount'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        render: function(data, type, row) {

                            var edit_fun = "edit_id('" + row.action + "')";
                            var delete_fun = "remove_id('" + row.action +
                                "','{{ route('admin.promo-code.delete') }}','#promo-code-datatable')";
                            return '<div class="invoice-action">' +
                                '<a href="javascript:void(0)" class="action-icon mr-1" id="edit_' +
                                row.action + '" onclick="' + edit_fun + '">' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
                                '<a href="javascript:void(0)" class="action-icon" id="remove_' + row
                                .action + '"  onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete"></i>' +
                                '</a>' +
                                '</div>';
                        }
                    },
                ],
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#promo-code-datatable_wrapper .col-md-6:eq(0)"), $(
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

            formValition('#promo-code-form');
            $('.promo-code-form').on('submit', function(e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('admin.promo-code.store') }}',
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
                            $('#promo-code-modal').modal('toggle');
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
                url: "{{ route('admin.promo-code.show') }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(res) {
                    resetFormValidation("#promo-code-form");
                    $('#id').val(res.data.id);
                    $('#code').val(res.data.code);
                    $('#discount').val(res.data.discount);
                    if (res.data.type == 0) {
                        $('#type').prop("checked", true);
                    } else {
                        $('#type1').prop("checked", true);
                    }
                    $('.modal-title').text('Edit Promo Code');
                    $('#promo-code-modal').modal('toggle');
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
            remove_id(join_selected_values, '{{ route('admin.promo-code.delete') }}',
                '#promo-code-datatable');
        });

        $('.active_status_all').on('click', function(e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{ route('admin.promo-code.edit-status') }}',
                '#promo-code-datatable');
        });

        $('.deactive_status_all').on('click', function(e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{ route('admin.promo-code.edit-status') }}',
                '#promo-code-datatable');
        });
    </script>
@endpush
