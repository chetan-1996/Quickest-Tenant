@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp

{{--@dd($user_perm);--}}
@extends('app.layouts.app')
@section('title','Item')
@push('styles')

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
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{route('tenant.dashboard', ['tenant' => $segment])}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{route('tenant.item.index', ['tenant' => $segment])}}">Item</a></li>
                                <li class="breadcrumb-item active">New</li>
                            </ol>
                        </div>
                        <div class="page-title-left pt-2">
                            <h4 class="page-title">Create Item</h4>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                {{--<div class="col-2"></div>--}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
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
                                    <a href="{{route('tenant.item.index', ['tenant' => $segment])}}" class="btn btn-secondary" data-bs-dismiss="modal">Back
                                    </a>
                                    <button class="btn btn-primary" id="item_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save
                                    </button>
                                </div>

                            </form>
                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
                {{-- <div class="col-2"></div>--}}
            </div>
            <!-- end row-->
        </div>
    </div>
</div>

@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script> -->


    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/custom.js')}}"></script>

    <script src="{{ asset('ckeditor/ckeditor.js')}}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('js/pages/demo.datatable-init.js')}}"></script>--}}
    <!-- end demo js-->
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
                        url: '{{route('tenant.item.store', ['tenant', $segment])}}',
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
                            // $('#item-modal').modal('toggle');
                            // table.ajax.reload();
                            window.location.href = SITEURL + '/item';
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
    </script>
@endpush
