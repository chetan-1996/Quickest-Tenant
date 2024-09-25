{{--@php
$user_perm = PermissionCheck::check_permission('role-list');
@endphp--}}
@extends('app.layouts.app')
@section('title','Lead Labels')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    {{--    <link rel="stylesheet" href="{{ asset('vendor/jquery-toast-plugin/jquery.toast.min.css')}}">--}}
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.css">
    <style>
        .widget-icon {

            height: 20px !important;
            width: 20px !important;
            line-height: 20px !important;
        }

        /*section {*/
        /*    margin: 2rem auto;*/
        /*    padding: 0.5rem 2.5rem;*/
        /*}*/

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

        .parsley-error {
            animation: shake 0.8s;
            border-color: #B94A48;
        }

        @keyframes shake {
            10%, 90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%, 80% {
                transform: translate3d(2px, 0, 0);
            }

            30%, 50%, 70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%, 60% {
                transform: translate3d(4px, 0, 0);
            }
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
                                {{--                    @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))--}}
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2"
                                   onclick="openModal('#lead-groups-modal','Create New Labels','#lead-groups-form','.modal-title',id=0)"><i
                                        class="mdi mdi-plus-circle"></i> New</a>
                                {{--                    @endif--}}
                                <div class="dropdown btn-group mb-2">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span
                                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                    id="select_count">0</span>Bulk Action
                                        {{--                                    <span class="badge badge-success-lighten" id="select_count">0</span> --}}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-animated">
                                        <a href="javascript:void(0);" class="dropdown-item active_status_all"><i
                                                class="mdi mdi-update"></i> Active All</a>
                                        <a href="javascript:void(0);" class="dropdown-item deactive_status_all"><i
                                                class="mdi mdi-update"></i> Deactive All</a>
                                        {{--                            @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))--}}
                                        <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                                class="mdi mdi-delete-circle"></i> Delete All</a>
                                        {{--                            @endif--}}
                                    </div>
                                </div>
                            </div>

                            <div class="page-title-left pt-2">
                                {{--<h4 class="page-title">lead-groups</h4>--}}
                                <select class="form-select" id="fil_status" name="fil_status"
                                        style="width: 190px;background-color: #fff0 !important;border: 0px solid #fff !important;font-size: 14px;margin: 0;white-space: nowrap;font-weight: 700;padding: 0.0rem 0.0rem 0rem 0.5rem;"
                                    <option value="">All Lead Labels</option>
                                    <option value="0">Active Lead Labels</option>
                                    <option value="1">Deactive Lead Labels</option>
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
                                <table id="lead-groups-datatable" class="table table-centered table-sm w-100 nowrap">
                                    <thead class="table-light">
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                        <th>Name</th>
                                        {{--                            <th>Description</th>--}}
                                        <th>Status</th>
                                        {{--                            @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))--}}
                                        <th>Action</th>
                                        {{--                            @endif--}}
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
                <div id="lead-groups-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md" style="width: 100%;">
                        <div class="modal-content" style="height: 100%;">
                            <div class="modal-header border-1 bg-light">
                                <h3 class="modal-title text-dark">Create New Labels</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="lead-groups-form" id="lead-groups-form" action="#">

                                    <div class="mb-1">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="text" id="name"
                                                   name="name" placeholder="Name" required=""
                                                   data-parsley-maxlength="20">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        </div>
                                        <input class="form-control" type="hidden" id="id" name="id" value="0">
                                    </div>

                                    {{--<div class="mb-1">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="name" name="name" required=""
                                               placeholder="Enter name" autofocus>
                                    </div>--}}


                                    <div class="mb-3 d-none">
                                        <div class="form-floating">
                                    <textarea class="form-control bg-light text-dark" id="description"
                                              name="description"
                                              placeholder="Enter description" style="height: 80px;"></textarea>
                                            <label for="description" class="form-label">Description</label>
                                        </div>
                                    </div>

                                    <div class="mb-3 bg-light p-2">
                                        <label for="color" class="form-label text-dark">Colors</label>

                                        <section>
                                            <div class="swatch green">
                                                <input type="radio" name="color_code" id="swatch_2" value="#006398"
                                                       checked/>
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
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                        <button class="btn btn-primary btn-sm" id="lead-groups_button" type="submit"><i
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
    <!-- third party js -->
    @include('app.layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('assets/js/pages/demo.datatable-init.js')}}"></script>--}}
    <!-- end demo js-->
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            "use strict";
            var table = $("#lead-groups-datatable").DataTable({
                // dom: 'Bfrtip',
                dom:
                    "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                responsive: false,
                // scrollX: !0,
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
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf-box fs-4"></i>',
                        attr: {
                            title: 'PDF',
                            class: 'btn btn-light buttons-html5 buttons-pdf',
                        },
                        title: 'Lead Labels List',
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
                        title: 'Lead Labels List',
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
                        title: 'Lead Labels List',
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
                    url: "{{ route('tenant.lead-groups.index', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val()
                        // d.search = $('input[type="search"]').val()
                    }
                },
                "order": [[1, "asc"]],
                columns: [
                    {
                        data: 'id', name: 'id', orderable: false,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    // {data: 'name', name: 'name', orderable: true},
                    {
                        data: 'name', name: 'name', orderable: true,
                        render: function (data, type, row) {
                            return '<div class="d-flex align-items-center">' +
                                '<div class="flex-shrink-0">' +
                                '<i class="widget-icon rounded" style="background-color:' + row.color_code + ' !important;"></i>' +
                                '</div>' +
                                '<div class="flex-grow-1 ms-3">' +
                                '<h5 class="fw-semibold mt-0 mb-1 text-dark">' + row.name + '</h5>' +
                                '</div>' +
                                '</div>';
                        }
                    },
                    // {data: 'description', name: 'description'},
                    {
                        data: 'status', name: 'status',
                        render: function (data, type, row) {
                            var fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.lead-groups.edit-status', ['tenant' => $segment])}}','#lead-groups-datatable')";
                            if (data == 0)
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Active</span>';
                            else {
                                fun_status = "change_status('" + row.action + "', 0,'{{route('tenant.lead-groups.edit-status', ['tenant' => $segment])}}','#lead-groups-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }

                        }
                    },
                        {{--                    @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm)),--}}

                    {
                        data: 'action', name: 'action', orderable: false,
                        render: function (data, type, row) {
                            var edit_fun = "edit_id('" + row.action + "')";
                            var delete_fun = "remove_id('" + row.action + "','{{route('tenant.lead-groups.delete', ['tenant' => $segment])}}','#lead-groups-datatable')";
                            return '<div class="invoice-action">' +
                                {{--                                @if(in_array('edit-lead-groups', $user_perm) || auth()->user()->company_id==null)--}}
                                    '<a href="javascript:void(0)" class="action-icon mr-1" id="edit_' + row.action + '" onclick="' + edit_fun + '">' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
                                {{--                                @endif--}}
                                    {{--                                @if(in_array('remove-lead-groups', $user_perm) || auth()->user()->company_id==null)--}}
                                    '<a href="javascript:void(0)" class="action-icon" id="remove_' + row.action + '"  onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete"></i>' +
                                '</a>' +
                                {{--                                @endif--}}
                                    '</div>';
                        }
                    },
                    {{--                    @endif--}}
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#lead-groups-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
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

            formValition('#lead-groups-form');
            $('.lead-groups-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('tenant.lead-groups.store', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        // data: $('.category-form').serialize(),
                        dataType: "json",
                        beforeSend: function () {
                            $("#lead-groups_button").prop('disabled', true);
                            $("#lead-groups_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#lead-groups-modal').modal('toggle');
                            table.ajax.reload();
                            $("#lead-groups_button").prop('disabled', false);
                            $("#lead-groups_button").html('<i class="uil-arrow-circle-right"></i> Save');
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
                            $("#lead-groups_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#lead-groups_button").html('Save');
                            $("#lead-groups_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });
        });

        function edit_id(id) {
            $.ajax({
                async: false,
                type: "GET",
                url: "{{route('tenant.lead-groups.show', ['tenant' => $segment])}}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#lead-groups-form");
                    $('#id').val(res.data.id);
                    $('#name').val(res.data.name);
                    // $('#description').val(res.data.description);
                    $("input[name=color_code][value='" + res.data.color_code + "']").prop('checked', true);
                    $('.modal-title').text('Edit Labels');
                    $('#lead-groups-modal').modal('toggle');
                }
            });
        }

        //Remove multiple record
        $('.delete_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            remove_id(join_selected_values, '{{route('tenant.lead-groups.delete', ['tenant' => $segment])}}', '#lead-groups-datatable');
        });

        $('.active_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{route('tenant.lead-groups.edit-status', ['tenant' => $segment])}}', '#lead-groups-datatable');
        });

        $('.deactive_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{route('tenant.lead-groups.edit-status', ['tenant' => $segment])}}', '#lead-groups-datatable');
        });
    </script>
@endpush
