@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title','Content')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <style>
        #content-message-datatable tbody tr, #content-file-datatable tbody tr {
            cursor: pointer;
        }
        #content-message-datatable tbody tr td:last-child,
        #content-file-datatable tbody tr td:last-child {
            cursor: auto;
        }

        .form-check-inline {
            margin-right: 0.5rem !important;
        }

        #content-file-datatable {
            min-width: 100% !important;
            max-width: 100% !important;
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
                            {{--                    @if(in_array('add-content-message', $user_perm) || auth()->user()->company_id==null)--}}
                            {{--  <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2"
                                onclick="openModal('#content-message-modal','New Message Template','#content-message-form','.modal-title',id=0)"><i
                                    class="mdi mdi-plus-circle"></i> New Message</a>--}}
                            {{--                    @endif--}}
                            <div class="dropdown btn-group mb-2 d-none">
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
                                    <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                            class="mdi mdi-delete-circle"></i> Delete All</a>
                                </div>
                            </div>
                        </div>
                        <div class="page-title-left pt-0">
                            <h4 class="page-title fs-4">Content</h4>
                            {{--<select class="form-select" id="fil_status" name="fil_status" ({{$totalLeadCount}})
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

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills bg-nav-pills nav-justified mb-3 text-dark" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a href="#messages-tab" data-bs-toggle="tab" aria-expanded="false"
                                    class="nav-link rounded-0{{ (Request::segment(3)=='messages')?' active':'' }}"
                                    aria-selected="true" role="tab">
                                        <i class="uil uil-file-alt font-18"></i>
                                        <span>Messages</span>
                                        {{--  <span class="d-none d-lg-block">Messages</span>--}}
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a href="#files-tab" data-bs-toggle="tab" aria-expanded="true"
                                    class="nav-link rounded-0{{ (Request::segment(3)=='files')?' active':'' }}"
                                    aria-selected="false" role="tab" tabindex="-1">
                                        <i class="mdi mdi-file-pdf-box font-18"></i>
                                        <span>Files </span>
                                        {{--   <span class="d-none d-lg-block">Files </span>--}}
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Billing Content-->
                                <div class="tab-pane{{ (Request::segment(3)=='messages')?' active show':'' }}" id="messages-tab"
                                    role="tabpanel">
                                    <div class="row">
                                    {{-- <div class="col-lg-12">
                                            @if(in_array('access-to-add-and-edit-files-and-message-template', $user_perm)) --}}{{-- || auth()->user()->company_id==null--}}{{--
                                                <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2 float-end"
                                                onclick="openModal('#content-message-modal','New Message Template','#content-message-form','.modal-title',id=0)"><i
                                                        class="mdi mdi-plus-circle"></i> New Message</a>
                                            @endif
                                        </div>--}}
                                        <div class="col-lg-12">
                                            {{--                                    <div class="table-responsive">--}}
                                            <table id="content-message-datatable"
                                                class="table table-centered table-hover table-sm w-100 nowrap">
                                                <thead class="table-light">
                                                <tr>
                                                    <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>

                                                <tbody>

                                                </tbody>
                                            </table>
                                        {{--                                    </div>--}}
                                        <!-- end table-responsive -->
                                        </div> <!-- end col -->
                                    </div> <!-- end row-->
                                </div>
                                <!-- End Billing Information Content-->

                                <!-- Shipping Content-->
                                <div class="tab-pane{{ (Request::segment(3)=='files')?' active show':'' }}" id="files-tab"
                                    role="tabpanel">
                                    <div class="row">
                                        {{--<div class="col-lg-12">
                                            @if(in_array('access-to-add-and-edit-files-and-message-template', $user_perm)) --}}{{-- || auth()->user()->company_id==null--}}{{--
                                                <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2 float-end"
                                                onclick="openModal('#content-file-modal','Upload New File','#content-file-form','.modal-title',id=0)"><i
                                                        class="mdi mdi-plus-circle"></i> Upload File</a>
                                            @endif
                                        </div>--}}

                                        <div class="col-lg-12">
                                            {{--                                    <div class="table-responsive">--}}
                                            <table id="content-file-datatable"
                                                class="table table-centered table-hover table-sm nowrap w-100">
                                                <thead class="table-light">
                                                <tr>
                                                    <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                    {{--                                                <th>Action</th>--}}
                                                </tr>
                                                </thead>

                                                <tbody>

                                                </tbody>
                                            </table>
                                            {{--                                    </div>--}}
                                        </div> <!-- end col -->
                                    </div> <!-- end row-->
                                </div>
                                <!-- End Shipping Information Content-->
                            </div> <!-- end tab content-->

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->
        </div>
    </div>
</div>

    <!-- Modal -->
    <div id="content-message-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">New Message Template</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="content-message-form" id="content-message-form" action="#">
                        <div class="row">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <input class="form-control" type="hidden" id="id" name="id" value="0">
                                        <div class="form-floating mb-1">
                                            <input type="text" class="form-control bg-light text-dark" id="name"
                                                   name="name"
                                                   required=""
                                                   placeholder="Name">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <textarea class="form-control bg-light text-dark" id="description"
                                                      name="description" placeholder="Enter description"
                                                      style="height: 80px;"></textarea>
                                            <label for="description" class="form-label">Description</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <p class="text-dark mb-2"><span class="fw-bold">@leadName </span>
                                            will be replaced with your lead's display name when sending
                                            <a href="#" data-type="@leadName"
                                               class="text-primary lead_name_cl fw-bold underline cursor-pointer inline-block">(
                                                insert @leadName )</a></p>
                                        <p class="text-dark mb-2"><span class="fw-bold">@senderName </span>
                                            will be replaced with the name of the user account sending this content
                                            <a href="#" data-type="@senderName"
                                               class="text-primary sender_name_cl fw-bold underline cursor-pointer inline-block">(
                                                insert @senderName )</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary" form="content-message-form" id="content-message_button"
                                type="submit">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="content-file-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">New Message Template</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="content-file-form" id="content-file-form" action="#" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label for="image_icon" class="form-label">File</label>
                                            <input type="file" class="form-control" data-parsley-trigger="change"
                                                   name="image_icon"
                                                   id="image_icon" data-parsley-required="false"
                                                   accept="application/pdf,image/png,image/jpeg,image/jpg,application/xlsx,,application/csv,,application/doc"
                                                   data-parsley-fileextension="pdf,Pdf,PDF,png,Png,PNG,jpeg,Jpeg,JPEG,jpg,Jpg,JPG,csv,xlsx,xls,doc,docx,Csv,Xlsx,Xls,Doc,Docx,CSCV,XLXS,XLS,DOC,DOCX" data-parsley-max-file-size="20480">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <input class="form-control" type="hidden" id="id" name="id" value="0">
                                        <div class="form-floating mb-1">
                                            <input type="text" class="form-control bg-light text-dark" id="name"
                                                   name="name"
                                                   required=""
                                                   placeholder="Name">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary" form="content-file-form" id="content-file_button" type="submit">
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

    <!-- demo app -->
    {{--    <script src="{{ asset('js/pages/demo.datatable-init.js')}}"></script>--}}
    <!-- end demo js-->
    <!-- App js -->
    <script src="{{ asset('js/app.min.js')}}"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('input[type="file"]').change(function (e) {
                let fileName = e.target.files[0].name;
                $('#content-file-form #name').val(fileName.split('.')[0]);
                // alert('The file "' + fileName +  '" has been selected.');
            });
            /*$('#image_icon').change(function() {
                alert();
                let filename = $('#image_icon');
                // let fp = $("#fUpload");
                // let lg = filename[0].files.length; // get length
                let items = filename[0].files;
                $('#content-file-form #name').html(items[0].name);
            });*/
            $('.lead_name_cl').on('click', function () {
                let str_val = $(this).attr('data-type');
                var cursorPos = $('#description').prop('selectionStart');
                var v = $('#description').val();
                var textBefore = v.substring(0, cursorPos);
                var textAfter = v.substring(cursorPos, v.length);
                $('#description').val(textBefore + str_val + textAfter);
                $('#description').focus();
            });

            $('.sender_name_cl').on('click', function () {
                let str_val = $(this).attr('data-type');
                var cursorPos = $('#description').prop('selectionStart');
                var v = $('#description').val();
                var textBefore = v.substring(0, cursorPos);
                var textAfter = v.substring(cursorPos, v.length);
                $('#description').val(textBefore + str_val + textAfter);
                $('#description').focus();
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            "use strict";
            var table = $("#content-message-datatable").DataTable({
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
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length btn-sm',
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
                            class: 'btn btn-light buttons-html5 buttons-pdf btn-sm',
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
                            class: 'btn btn-light buttons-html5 buttons-excel btn-sm',
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
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis btn-sm',
                        },
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                    @if(in_array('access-to-add-and-edit-files-and-message-template', $user_perm))
                    ,{
                        text: '<i class="mdi mdi-plus-circle fs-4"></i> New Message',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-primary buttons-collection btn-sm',
                        },
                        action: function ( e, dt, node, config ) {
                            openModal('#content-message-modal','New Message Template','#content-message-form','.modal-title',id=0)
                        }
                    }
                    @endif
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
                },
                stateLoadParams: function (settings, data) {
                    $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.content.messages.index', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                "order": [[0, "desc"]],
                columns: [
                    {
                        data: 'id', name: 'id', orderable: true, visible: false,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            return '<td>' +
                                '<h5 class="font-15 mb-1 fw-normal">' + row.name + '</h5>' +
                                '</td>';
                        }
                    },


                    // {data: 'created_at', name: 'created_at'},
                    {
                        data: 'description', name: 'description',
                        render: function (data, type, row) {
                            var str = row.description;
                            return funcStrLimit(str);
                        }
                    },

                    // {data: 'description', name: 'description'},
                    {
                        data: 'status', name: 'status',
                        render: function (data, type, row) {
                            var fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.customer.edit-status', ['tenant' => $segment])}}','#content-message-datatable')";
                            if (data == 0)
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Active</span>';
                            else {
                                fun_status = "change_status('" + row.action + "', 0,'{{route('tenant.customer.edit-status', ['tenant' => $segment])}}','#content-message-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }

                        }
                    },
                    {
                        data: 'action', name: 'action', orderable: false, visible: false,
                        render: function (data, type, row) {

                            var edit_fun = "edit_id('" + row.action + "')";
                            var edit_fun = "{{url('lead/timeline')}}/" + row.action;
                            var delete_fun = "remove_id('" + row.action + "','{{route('tenant.customer.delete', ['tenant' => $segment])}}','#content-message-datatable')";
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
                    },
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);

                }
            });
            table.on('click', 'tbody tr td:not(:last-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/content/messages/timeline/' + id;
            });
            table.buttons().container().appendTo("#content-message-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })

            var tableFile = $("#content-file-datatable").DataTable({
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
                lengthChange: !1,
                buttons: [
                    {
                        extend: 'pageLength',
                        attr: {
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length btn-sm',
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
                            class: 'btn btn-light buttons-html5 buttons-pdf btn-sm',
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
                            class: 'btn btn-light buttons-html5 buttons-excel btn-sm',
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
                            class: 'btn btn-light buttons-collection dropdown-toggle buttons-colvis btn-sm',
                        },
                        title: 'Lead List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                    @if(in_array('access-to-add-and-edit-files-and-message-template', $user_perm))
                    ,{
                        text: '<i class="mdi mdi-plus-circle fs-4"></i> Upload File',
                        attr: {
                            title: 'Column visibility',
                            class: 'btn btn-primary buttons-collection btn-sm',
                        },
                        action: function ( e, dt, node, config ) {
                            openModal('#content-file-modal','Upload New File','#content-file-form','.modal-title',id=0)
                        }
                    }
                    @endif
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
                },
                stateLoadParams: function (settings, data) {
                    $('#fil_status').val(data.fil_status);
                    $('#fil_type').val(data.fil_type);
                    $('#fil_name').val(data.fil_name);
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem(settings.sInstance, JSON.stringify(data))
                },
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem(settings.sInstance))
                },
                ajax: {
                    url: "{{ route('tenant.content.files.index', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                "order": [[0, "desc"]],
                columns: [
                    {
                        data: 'id', name: 'id', orderable: true, visible: false,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' + row.action + '">';
                        }
                    },
                    {
                        data: 'name', name: 'name',
                        render: function (data, type, row) {
                            let icon = '<i class="mdi mdi-file-image text-dark fs-3"></i>';
                            if(row.pdfname=='pdf' || row.pdfname=='Pdf' || row.pdfname=='PDF')
                                icon = '<i class="mdi mdi-file-pdf-box text-dark fs-3"></i>';

                            if(row.pdfname=='xlsx' || row.pdfname=='xls')
                                icon = '<i class="mdi mdi-file-excel text-dark fs-3"></i>';

                            if(row.pdfname=='doc')
                                icon = '<i class="mdi mdi-file-document-outline text-dark fs-3"></i>';
                            return '<td>' +
                                '<div class="d-flex align-items-start">' +
                                icon +
                                '<div>' +
                                '<h5 class="mt-2 mb-1 fw-normal text-dark"> ' + row.name + '</h5>' +
                                '</div>' +
                                '</div>' +
                                '</td>';


                        }
                    },
                    {
                        data: 'status', name: 'status',
                        render: function (data, type, row) {
                            var fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.customer.edit-status', ['tenant' => $segment])}}','#content-message-datatable')";
                            if (data == 0)
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Active</span>';
                            else {
                                fun_status = "change_status('" + row.action + "', 0,'{{route('tenant.customer.edit-status', ['tenant' => $segment])}}','#content-message-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }

                        }
                    },
                    {
                        data: 'action', name: 'action', orderable: false, visible: false,
                        render: function (data, type, row) {

                            var edit_fun = "edit_id('" + row.action + "')";
                            var edit_fun = "{{url('lead/timeline')}}/" + row.action;
                            var delete_fun = "remove_id('" + row.action + "','{{route('tenant.customer.delete', ['tenant' => $segment])}}','#content-message-datatable')";
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
                    },
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                createdRow: function (row, data, dataIndex) {
                    // Set the data-status attribute, and add a class
                    $(row).attr('data-id', data.action);

                }
            });
            tableFile.on('click', 'tbody tr td:not(:last-child)', function () {
                var id = $(this).parent().attr('data-id');
                location.href = SITEURL + '/content/files/timeline/' + id;
            });
            tableFile.buttons().container().appendTo("#content-file-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
                pagingType: "full_numbers",
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            })

            $('#fil_status,#fil_name,#fil_type').change(function () {
                table.draw();
            });

            $('#resetFilter').click(function () {
                $('input[type=text]').val('');
                $('#fil_status').val('');
                $('#fil_type').val('');
                table
                    .search('')
                    .columns().search('')
                    .draw();
            });

            formValition('#content-message-form');
            window.Parsley.addValidator('maxFileSize', {
                validateString: function (_value, maxSize, parsleyInstance) {
                    if (!window.FormData) {
                        alert('You are making all developpers in the world cringe. Upgrade your browser!');
                        return true;
                    }
                    var files = parsleyInstance.$element[0].files;
                    return files.length != 1 || files[0].size <= maxSize * 1024;
                },
                requirementType: 'integer',
                messages: {
                    en: 'This file should not be larger than %s Kb',
                    fr: 'Ce fichier est plus grand que %s Kb.'
                }
            });
            window.ParsleyValidator.addValidator('fileextension', function (value, requirement) {
                var tagslistarr = requirement.split(',');
                var fileExtension = value.split('.').pop();
                var arr = [];
                $.each(tagslistarr, function (i, val) {
                    arr.push(val);
                });
                if (jQuery.inArray(fileExtension, arr) != '-1') {
                    //console.log("is in array");
                    return true;
                } else {
                    //console.log("is NOT in array");
                    return false;
                }
            }, 32)
                .addMessage('en', 'fileextension', 'The extension should be jpeg, jpg, png, csv, xlsx, doc,docx allowed');
            formValition('#content-file-form');
            $('.content-message-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('tenant.content.messages.store', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#content-message_button").prop('disabled', true);
                            $("#content-message_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#content-message-modal').modal('toggle');
                            table.ajax.reload();
                            $("#content-message_button").prop('disabled', false);
                            $("#content-message_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
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
                                    toastrInfo('name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#content-message_button").prop('disabled', false);
                            $("#content-message_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#content-message_button").html('Save');
                            $("#content-message_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
                }
            });

            $('.content-file-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        // async: false,
                        type: 'POST',
                        url: '{{route('tenant.content.files.store', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#content-file_button").prop('disabled', true);
                            $("#content-file_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#content-file-modal').modal('toggle');
                            tableFile.ajax.reload();
                            $("#content-file_button").prop('disabled', false);
                            $("#content-file_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
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
                                    toastrInfo('name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#content-file_button").prop('disabled', false);
                            $("#content-file_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            $("#content-file_button").html('Save');
                            $("#content-file_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
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
                    resetFormValidation("#content-message-form");
                    resetForm("#content-message-form");
                    $('#content-message-form #id').val(res.data.id);
                    $('#content-message-form #name').val(res.data.name);
                    $('#content-message-form #description').val(res.data.description);
                    $('#content-message-form .modal-title').text('Edit Lead');
                    $('#content-message-modal').modal('toggle');
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
            remove_id(join_selected_values, '{{route('tenant.customer.delete', ['tenant' => $segment])}}', '#content-message-datatable');
        });

        $('.active_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{route('tenant.customer.edit-status', ['tenant' => $segment])}}', '#content-message-datatable');
        });

        $('.deactive_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{route('tenant.customer.edit-status', ['tenant' => $segment])}}', '#content-message-datatable');
        });
    </script>
    <style>
        .dataTables_scrollHeadInner {
            width: 100% !important;
        }
    </style>
@endpush
