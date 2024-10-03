@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp

{{--@dd($user_perm);--}}
@extends('app.layouts.app')
@section('title','Item')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <style>
        .cke_dialog_container
        {
            z-index: 20000
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
                                @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                            {{-- <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2"
                                onclick="openModal('#item-modal','Create Item','#item-form','.modal-title',id=0,flag=4)"><i
                                        class="mdi mdi-plus-circle"></i> New</a>--}}
                                    <a href="{{route('tenant.item.create', ['tenant' => $segment])}}" class="btn btn-primary btn-sm mb-2"><i
                                        class="mdi mdi-plus-circle"></i> New</a>
                                @endif
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
                                        @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                                        <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                                class="mdi mdi-delete-circle"></i> Delete All</a>
                                            @endif
                                    </div>
                                </div>
                            </div>
                            <div class="page-title-left pt-2">
                                {{--<h4 class="page-title">Item</h4>--}}
                                <select class="form-select" id="fil_status" name="fil_status"
                                        style="width: 170px;background-color: #fff0 !important;border: 0px solid #fff !important;font-size: 18px;margin: 0;white-space: nowrap;font-weight: 700;padding: 0.0rem 0.0rem 0rem 0.5rem;">
                                    <option value="">All Items</option>
                                    <option value="0">Active Items</option>
                                    <option value="1">Deactive Items</option>
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
                                <table id="item-datatable" class="table table-centered table-sm w-100 nowrap">
                                    <thead class="table-light">
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                        <th>Name</th>
                                        <th>Unit Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                                        <th>Action</th>
                                        @endif
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
            </div>
        </div>
    </div>

    <div id="item-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-center">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Create Item</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="ps-3 pe-3 item-form" id="item-form" action="#">
                        <div class="mb-1">
                            <h6 class="form-label font-14 mt-3">Type <span class="text-danger">*</span></h6>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="item_type_goods" name="item_type" class="form-check-input"
                                       value="Goods" checked>
                                <label class="form-check-label" for="item_type_goods">Goods</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="item_type_service" name="item_type" class="form-check-input"
                                       value="Service">
                                <label class="form-check-label" for="item_type_service">Service</label>
                            </div>
                        </div>

                        <div class="mb-1">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="name" name="name" required=""
                                   placeholder="Enter name" autofocus>
                            <input class="form-control" type="hidden" id="id" name="id" value="0">
                        </div>

                        <div class="mb-1">
                            <label for="name" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="unit_id" name="unit_id" required="">
                                <option value="">Choose</option>
                                @foreach($units as $unit)
                                    <option value="{{$unit->id}}">{{$unit->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-1">
                            <label for="image_icon" class="form-label">Image</label>
                            <input type="file" class="form-control" data-parsley-trigger="change" name="image_icon"
                                   id="image_icon" data-parsley-required="false"
                                   accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                   data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024">
                        </div>

                        <div class="mb-1">
                            <label for="name" class="form-label">HSN Code</label>
                            <input class="form-control" type="text" id="hsn_code" name="hsn_code"
                                   placeholder="Enter hsn code" autofocus>
                        </div>

                        <div class="mb-1">
                            <h6 class="form-label font-14 mt-3">Tax Preference <span class="text-danger">*</span>
                            </h6>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="tax_preference_non_taxable" name="tax_preference"
                                       value="Non-Taxable"
                                       class="form-check-input" checked>
                                <label class="form-check-label" for="tax_preference_non_taxable">Non-Taxable</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="tax_preference_taxable" name="tax_preference"
                                       value="Taxable"
                                       class="form-check-input">
                                <label class="form-check-label" for="tax_preference_taxable">Taxable</label>
                            </div>
                        </div>

                        <div class="mb-1 tax-div" style="display:none">
                            <div class="row">
                                <div class="col-md-6 d-none">
                                    <label for="fil_status" class="me-2">Inter State</label>
                                    <select class="form-select" id="inter_state" name="inter_state">
                                        <option value="0">GST0 [0%]</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}">GST{{$tax->name}} [{{$tax->name}}%]</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="fil_status" class="me-2"> Tax (GSTIN)</label>
                                    <select class="form-select" id="intra_state" name="intra_state">
                                        <option value="0">GST0 [0%]</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}">GST{{$tax->name}} [{{$tax->name}}%]</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1">
                            <div class="row">
                                <div class="col-md-6 mb-1">
                                   {{-- <div class="form-check mb-1">
                                        <input type="checkbox" class="form-check-input" id="sales_flag"
                                               name="sales_flag" value="1">
                                        <label class="form-check-label" for="sales_flag">Sales Info</label>
                                    </div>--}}

                                    <label for="name" class="form-label">Selling Price {!! '(<span class="text-primary">'.$country_data->currency_code.'</span>)' !!}</label>
                                    <div class="input-group flex-nowrap">
                                        <span class="input-group-text" id="basic-addon1">{!! $country_data->currency_symbol !!}</span>
                                        <input class="form-control" type="text" id="sale_price" name="sale_price"
                                               placeholder="Enter sale price" value="0">
                                    </div>

                                    {{--<label for="name" class="form-label">Selling Price</label>
                                    <input class="form-control" type="text" id="sale_price" name="sale_price"
                                           placeholder="Enter sale price" value="0">--}}
                                </div>
                                <div class="col-md-6 mb-1">
                                    <label for="name" class="form-label">{!! $proposal_template->item_table_discount !!}</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="item_discount" name="item_discount"
                                               placeholder="Enter discount" value="0">
                                        <select class="btn-light item_discount_flag"
                                                id="item_discount_flag"
                                                name="item_discount_flag">
                                            <option value="2">{!! $country_data->currency_symbol !!}</option>
                                            <option value="1">%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 d-none">
                                    {{--<div class="form-check mb-1">
                                        <input type="checkbox" class="form-check-input" id="purchase_flag"
                                               name="purchase_flag" value="1">
                                        <label class="form-check-label" for="purchase_flag">Purchase Info</label>
                                    </div>--}}
                                    <label for="name" class="form-label">Cost Price</label>
                                    <div class="input-group flex-nowrap">
                                        <span class="input-group-text" id="basic-addon1">{!! $country_data->currency_symbol !!}</span>
                                        <input class="form-control" type="text" id="cost_price" name="cost_price"
                                               placeholder="Enter purchase price" value="0">
                                    </div>

                                    {{--<label for="name" class="form-label">Cost Price</label>
                                    <input class="form-control" type="text" id="cost_price" name="cost_price"
                                           placeholder="Enter purchase price" value="0">--}}
                                </div>

                            </div>


                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                      placeholder="Enter description"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="technical_specification" class="form-label">Technical Specifications</label>
                            <textarea class="form-control" id="technical_specification" name="technical_specification"
                                      placeholder="Enter description"></textarea>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                            </button>
                            <button class="btn btn-primary" id="item_button" type="submit"><i
                                    class="uil-arrow-circle-right"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection
@push('scripts')
    <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script>

    <!-- third party js -->
    @include('app.layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>

    <script src="{{ asset('ckeditor/ckeditor.js')}}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('js/pages/demo.datatable-init.js')}}"></script>--}}
    <!-- end demo js-->
    <!-- App js -->
    <script src="{{ asset('js/app.min.js')}}"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script>
        $(document).ready(function () {

            CKEDITOR.replace('technical_specification', {
                extraPlugins: 'editorplaceholder',
            });
           /* $("input[name='sales_flag']").click(function () {

                if ($("#sales_flag").is(":checked")) {
                    $("#sale_price").attr("required", true);
                    {
                        if ($('#sale_price').val() <= 0 || $('#sale_price').val() == '')
                            $("#sale_price").val('');
                        $("#sale_price").prop('readonly', false);
                    }
                } else {
                    $("#sale_price").attr("required", false);
                    if ($('#sale_price').val() >= 0 || $('#sale_price').val() == '') {
                        $("#sale_price").val(0);
                        $("#sale_price").prop('readonly', true);
                    }
                }
            });*/

            /*$("input[name='purchase_flag']").click(function () {

                if ($("#purchase_flag").is(":checked")) {
                    $("#cost_price").attr("required", true);
                    if ($('#cost_price').val() <= 0 || $('#cost_price').val() == '') {
                        $("#cost_price").val('');
                        $("#cost_price").prop('readonly', false);
                    }

                } else {
                    $("#cost_price").attr("required", false);
                    if ($('#cost_price').val() >= 0 || $('#cost_price').val() == '') {
                        $("#cost_price").val(0);
                        $("#cost_price").prop('readonly', true);
                    }

                }
            });*/

            $("input[name='tax_preference']").click(function () {

                if ($("#tax_preference_taxable").is(":checked")) {
                    $(".tax-div").show();
                    $("#inter_state,#intra_state").attr("required", true);
                } else {
                    $(".tax-div").hide();
                    $("#inter_state,#intra_state").attr("required", false);
                }
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            "use strict";
            var table = $("#item-datatable").DataTable({
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
                        title: 'Item List',
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
                        title: 'Item List',
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
                        title: 'Item List',
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
                    url: "{{ route('tenant.item.index', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
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
                    {
                        data: 'name', name: 'name', orderable: true,
                        render: function (data, type, row) {
                            // var imgIcon='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            var imgIcon='';
                            if(row.image_icon){
                                imgIcon ='<a href="' + row.image_icon + '" download><img src="' + row.image_icon + '" class="me-2 rounded-circle avatar-xs zoom" alt="friend"></a>';
                            }
                            return '<div id="tooltip-container" class="d-flex align-items-center">'+imgIcon+'<div><h5 class="mt-0 mb-1">' + row.name + '</h5></div></div>';
                        }
                    },
                    {data: 'unit_name', name: 'unit_name'},
                    {
                        data: 'description', name: 'description',
                        render: function (data, type, row) {
                            var str = row.description;
                            return funcStrLimit(str);
                        }
                    },
                    {
                        data: 'status', name: 'status',
                        render: function (data, type, row) {
                            var fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.item.edit-status', ['tenant' => $segment])}}','#item-datatable')";
                            if (data == 0)
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Active</span>';
                            else {
                                fun_status = "change_status('" + row.action + "', 0,'{{route('tenant.item.edit-status', ['tenant' => $segment])}}','#item-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }

                        }
                    }
                    @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm)),
                    {
                        data: 'action', name: 'action', orderable: false,
                        render: function (data, type, row) {

                            var edit_fun = SITEURL + "/item/edit/" + row.action;
                            // var edit_fun = "edit_id('" + row.action + "')";
                            var delete_fun = "remove_id('" + row.action + "','{{route('tenant.item.delete', ['tenant' => $segment])}}','#item-datatable')";

                            return '<div class="invoice-action">' +
{{--                                @if(in_array('edit-item', $user_perm) || auth()->user()->company_id==null)--}}
                                    '<a href="'+edit_fun+'" class="action-icon mr-1" id="edit_' + row.action + '" >' + //onclick="' + edit_fun + '"
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
{{--                                @endif--}}
{{--                                    @if(in_array('remove-item', $user_perm) || auth()->user()->company_id==null)--}}
                                    '<a href="javascript:void(0)" class="action-icon" id="remove_' + row.action + '"  onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete"></i>' +
                                '</a>' +
{{--                                @endif--}}
                                    '</div>';

                        }
                    } @endif
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#item-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
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
                .addMessage('en', 'fileextension', 'The extension should be jpeg, jpg, png allowed');

            formValition('#item-form');
            $('.item-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = new FormData(document.getElementById('item-form'));
                    formData.append('technical_specification', CKEDITOR.instances.technical_specification.getData());
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('tenant.item.store', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        // data: new FormData(this),
                        data:formData,
                        dataType: "json",
                        beforeSend: function () {
                            $("#item_button").prop('disabled', true);
                            $("#item_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#item-modal').modal('toggle');
                            table.ajax.reload();
                            $("#item_button").prop('disabled', false);
                            $("#item_button").html('<i class="uil-arrow-circle-right"></i> Save');
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
                            $("#item_button").prop('disabled', false);
                            $("#item_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#item_button").html('Save');
                            $("#item_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });
        });

        function edit_id(id) {
            $.ajax({
                type: "GET",
                async: false,
                url: "{{route('tenant.item.show', ['tenant' => $segment])}}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#item-form");
                    resetForm("#item-form");
                    $('#id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $('#unit_id').val(res.data.unit_id);
                    $('#hsn_code').val(res.data.hsn_code);
                    if (res.data.tax_preference == 'Taxable') {
                        $("#inter_state,#intra_state").attr("required", true);
                        $(".tax-div").show();
                    } else {
                        $("#inter_state,#intra_state").attr("required", false);
                        $(".tax-div").hide();
                    }
                    $("input[name=item_type][value=" + res.data.item_type + "]").prop('checked', true);
                    $("input[name=tax_preference][value=" + res.data.tax_preference + "]").prop('checked', true);
                   /* $("input[name=sales_flag][value=" + res.data.sales_flag + "]").prop('checked', true);
                    $("input[name=purchase_flag][value=" + res.data.purchase_flag + "]").prop('checked', true);*/
                    $('#description').val(res.data.description);
                    $('#inter_state').val(res.data.inter_state);
                    $('#intra_state').val(res.data.intra_state);
                    $('#sale_price').val(res.data.sale_price);
                    $('#item_discount').val(res.data.item_discount);
                    $('#item_discount_flag').val(res.data.item_discount_flag);
                    $('#cost_price').val(res.data.cost_price);
                    CKEDITOR.instances.technical_specification.setData(res.data.technical_specification);
                    /*if (res.data.sales_flag == 1) {
                        $("#sale_price").attr("required", true);
                        $("#sale_price").prop('readonly', false);
                    } else {
                        $("#sale_price").attr("required", false);
                        $("#sale_price").prop('readonly', true);
                    }
                    if (res.data.purchase_flag == 1) {
                        $("#cost_price").attr("required", true);
                        $("#cost_price").prop('readonly', false);
                    } else {
                        $("#cost_price").attr("required", false);
                        $("#cost_price").prop('readonly', true);
                    }*/
                    $('.modal-title').text('Edit Item');
                    $('#item-modal').modal('toggle');
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
            remove_id(join_selected_values, '{{route('tenant.item.delete', ['tenant' => $segment])}}', '#item-datatable');
        });

        $('.active_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{route('tenant.item.edit-status', ['tenant' => $segment])}}', '#item-datatable');
        });

        $('.deactive_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{route('tenant.item.edit-status', ['tenant' => $segment])}}', '#item-datatable');
        });
    </script>
@endpush
