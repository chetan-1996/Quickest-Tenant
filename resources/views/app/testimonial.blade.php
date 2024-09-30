@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title','Testimonial')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
    <style>
        .image_area {
            position: relative;
        }

       /* #image-one-modal .modal-lg{
            max-width: 1000px !important;
        }*/

        #image-one-modal img {
            display: block;
            max-width: 100%
            min-height: 400px;
            max-height:400px;;
        }

        #image-two-modal img {
            display: block;
            max-width: 100%;
            min-height: 400px;
            max-height:400px;
        }

        #image-three-modal img {
            display: block;
            max-width: 100%;
            min-height: 400px;
            max-height:400px;
        }
        .image_one_preview {
            overflow: hidden;
            width: 160px;
            height: 160px;
            margin: 10px;
            border: 1px solid red;
        }

        .image_two_preview {
            overflow: hidden;
            width: 160px;
            height: 160px;
            margin: 10px;
            border: 1px solid red;
        }

        .image_three_preview {
            overflow: hidden;
            width: 160px;
            height: 160px;
            margin: 10px;
            border: 1px solid red;
        }

        .modal-lg{
            max-width: 1000px !important;
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
                            {{-- @if(in_array('add-testimonial', $user_perm) || auth()->user()->company_id==null)--}}
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2"
                            onclick="openModal('#testimonial-modal','Create Testimonial','#testimonial-form','.modal-title',id=0,flag=2)"><i
                                    class="mdi mdi-plus-circle"></i> New</a>
                            {{--  @endif--}}
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
                                    <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                            class="mdi mdi-delete-circle"></i> Delete All</a>
                                </div>
                            </div>
                        </div>
                        <div class="page-title-left pt-2">
                            {{--<h4 class="page-title">Testimonial</h4>--}}
                            <select class="form-select" id="fil_status" name="fil_status"
                                    style="width: 230px;background-color: #fff0 !important;border: 0px solid #fff !important;font-size: 18px;margin: 0;white-space: nowrap;font-weight: 700;padding: 0.0rem 0.0rem 0rem 0.5rem;">
                                <option value="">All Testimonials</option>
                                <option value="0">Active Testimonials</option>
                                <option value="1">Deactive Testimonials</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
       

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="header-title mb-0">New Upload Photo</h4>
                            <a href="javascript:void(0);" onclick="clearForm()" class="btn btn-sm btn-primary">
                                <i class="mdi mdi-plus"></i>New Testimonial
                            </a>
                        </div>
                        <div class="card-body">

                            <form class="ps-3 pe-3 testimonial-form" id="testimonial-form" action="#"
                                enctype="multipart/form-data">

                                <div class="mb-1">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="name" name="name" required=""
                                        placeholder="Enter name" autofocus>
                                    <input class="form-control" type="hidden" id="id" name="id" value="0">
                                    <input class="form-control" type="hidden" id="h_image_one" name="h_image_one" value="">
                                    <input class="form-control" type="hidden" id="h_image_two" name="h_image_two" value="">
                                    <input class="form-control" type="hidden" id="h_image_three" name="h_image_three" value="">
                                </div>

                                <div class="form-check mb-1">
                                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
                                    <label class="form-check-label" for="is_default">Set as Default</label>
                                </div>

                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                    <th>Client Name<span class="text-danger">*</span></th>
                                    <th>Description<span class="text-danger">*</span></th>
                                    <th>Rating<span class="text-danger">*</span></th>
                                    <th>Picture<span class="text-danger">*</span></th>
                                    </thead>
                                    <tbody class="testimonialRow">
                                    <tr id="1">
                                        <td>
                                            <input class="form-control" type="text" id="client_name_one" name="client_name_one"
                                                required="" placeholder="Enter name" autofocus>
                                        </td>
                                        <td>
                                            <textarea class="form-control" id="description_one" name="description_one"
                                                    placeholder="Enter description" maxlength="300" data-toggle="maxlength"
                                                    required=""></textarea>
                                        </td>
                                        <td>
                                            <select class="form-select" id="rating_one" name="rating_one" required="">
                                                <option value="">Choose</option>
                                                <option value="1">1.0</option>
                                                {{--                                    <option value="1.5">1.5</option>--}}
                                                <option value="2">2.0</option>
                                                {{--                                    <option value="2.5">2.5</option>--}}
                                                <option value="3">3.0</option>
                                                {{--                                    <option value="3.5">3.5</option>--}}
                                                <option value="4">4.0</option>
                                                {{--                                    <option value="4.5">4.5</option>--}}
                                                <option value="5">5.0</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="file" class="form-control" data-parsley-trigger="change"
                                                name="image_one"
                                                id="image_one" data-parsley-required="false"
                                                accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                data-parsley-fileextension="jpg,png,jpeg"
                                                data-parsley-max-file-size="1024"
                                                required>
                                            <span class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                        </td>
                                    </tr>
                                    <tr id="2">
                                        <td>
                                            <input class="form-control" type="text" id="client_name_two" name="client_name_two"
                                                required="" placeholder="Enter name" autofocus>

                                        </td>
                                        <td>
                                                <textarea class="form-control" id="description_two" name="description_two"
                                                        placeholder="Enter description" maxlength="300"
                                                        data-toggle="maxlength" required=""></textarea>

                                        </td>
                                        <td>
                                            <select class="form-select" id="rating_two" name="rating_two" required="">
                                                <option value="">Choose</option>
                                                <option value="1">1.0</option>
                                                {{--                                    <option value="1.5">1.5</option>--}}
                                                <option value="2">2.0</option>
                                                {{--                                    <option value="2.5">2.5</option>--}}
                                                <option value="3">3.0</option>
                                                {{--                                    <option value="3.5">3.5</option>--}}
                                                <option value="4">4.0</option>
                                                {{--                                    <option value="4.5">4.5</option>--}}
                                                <option value="5">5.0</option>
                                            </select>

                                        </td>
                                        <td>
                                            <input type="file" class="form-control" data-parsley-trigger="change"
                                                name="image_two"
                                                id="image_two" data-parsley-required="false"
                                                accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                data-parsley-fileextension="jpg,png,jpeg"
                                                data-parsley-max-file-size="1024"
                                                required>
                                            <span class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                        </td>
                                    </tr>
                                    <tr id="3">
                                        <td>
                                            <input class="form-control" type="text" id="client_name_three"
                                                name="client_name_three"
                                                required=""
                                                placeholder="Enter name" autofocus>

                                        </td>
                                        <td>
                                                <textarea class="form-control" id="description_three" name="description_three"
                                                        placeholder="Enter description" maxlength="300"
                                                        data-toggle="maxlength" required=""></textarea>

                                        </td>
                                        <td>
                                            <select class="form-select" id="rating_three" name="rating_three" required="">
                                                <option value="">Choose</option>
                                                <option value="1">1.0</option>
                                                {{--                                    <option value="1.5">1.5</option>--}}
                                                <option value="2">2.0</option>
                                                {{--                                    <option value="2.5">2.5</option>--}}
                                                <option value="3">3.0</option>
                                                {{--                                    <option value="3.5">3.5</option>--}}
                                                <option value="4">4.0</option>
                                                {{--                                    <option value="4.5">4.5</option>--}}
                                                <option value="5">5.0</option>
                                            </select>

                                        </td>
                                        <td>
                                            <input type="file" class="form-control" data-parsley-trigger="change"
                                                name="image_three"
                                                id="image_three" data-parsley-required="false"
                                                accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                data-parsley-fileextension="jpg,png,jpeg"
                                                data-parsley-max-file-size="1024"
                                                required>
                                            <span class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <div class="mt-3 text-end">
                                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>--}}
                                    <button class="btn btn-primary" id="unit_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save & Upload
                                    </button>
                                </div>

                            </form>
                            {{--<form class="product-form" id="product-form" action="#" enctype="multipart/form-data">

                                <div class="mb-1">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="name" name="name" required=""
                                        placeholder="Enter name" autofocus>
                                    <input class="form-control" type="hidden" id="id" name="id" value="0">
                                    <input class="form-control" type="hidden" id="h_image_one" name="h_image_one" value="">
                                    <input class="form-control" type="hidden" id="h_image_two" name="h_image_two" value="">
                                    <input class="form-control" type="hidden" id="h_image_three" name="h_image_three" value="">
                                </div>

                                <div class="mb-1">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description"
                                            placeholder="Enter description"></textarea>
                                </div>

                                --}}{{--<div class="row">
                                    <div class="col-4">
                                        <div class="image_area">
                                            <form method="post">
                                                <label for="image_one">
                                                    <img src="{{Storage::url('public/template/user.png')}}" id="uploaded_image" class="img-responsive img-circle" />
                                                    <div class="overlay">
                                                        <div class="text">Click to Change Image 1</div>
                                                    </div>
                                                    <input type="file" class="image d-none" data-parsley-trigger="change" name="image_one"
                                                        id="image_one" data-parsley-required="false"
                                                        accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                        data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024"
                                                        required>
                                                </label>
                                            </form>
                                        </div>

                                        <span class="form-text text-muted"><small>Rendered size: 2160 x 1440 ( maximum 1 MB )</small></span>
                                    </div>
                                    <div class="col-4">
                                        <div class="image_area">
                                            <form method="post">
                                                <label for="image_two">
                                                    <img src="{{Storage::url('public/template/user.png')}}" id="uploaded_image" class="img-responsive img-circle" />
                                                    <div class="overlay">
                                                        <div class="text">Click to Change Image 2</div>
                                                    </div>
                                                    <input type="file" class="image d-none form-control" data-parsley-trigger="change" name="image_two"
                                                        id="image_two" data-parsley-required="false"
                                                        accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                        data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024"
                                                        required>
                                                </label>
                                            </form>
                                        </div>

                                        <span class="form-text text-muted"><small>Rendered size: 2160 x 1440 ( maximum 1 MB )</small></span>
                                    </div>
                                    <div class="col-4">
                                        <div class="image_area">
                                            <form method="post">
                                                <label for="image_three">
                                                    <img src="{{Storage::url('public/template/user.png')}}" id="uploaded_image" class="img-responsive img-circle" />
                                                    <div class="overlay">
                                                        <div class="text">Click to Change Image 3</div>
                                                    </div>
                                                    <input type="file" class="image d-none form-control" data-parsley-trigger="change" name="image_three"
                                                        id="image_three" data-parsley-required="false"
                                                        accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                        data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024"
                                                        required>
                                                </label>
                                            </form>
                                        </div>

                                        <span class="form-text text-muted"><small>Rendered size: 640 x 480 ( maximum 1 MB )</small></span>
                                    </div>
                                </div>--}}{{--






                                <div class="mb-1">
                                    <label for="image_one" class="form-label">Photo one <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control image_one" data-parsley-trigger="change" name="image_one"
                                        id="image_one" data-parsley-required="false"
                                        accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                        data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024" required>

                                    <span class="form-text text-muted"><small>Rendered size: 2160 x 1440 ( maximum 1 MB ) <a href="javascript:void(0);" onclick="clearImage(1)">Click to clear image
                            </a></small></span>
                                </div>

                                <div class="mb-1">
                                    <label for="image_two" class="form-label">Photo two <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control image_two" data-parsley-trigger="change" name="image_two"
                                        id="image_two" data-parsley-required="false"
                                        accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                        data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024" required>
                                    <span class="form-text text-muted"><small>Rendered size: 640 x 480 ( maximum 1 MB ) <a href="javascript:void(0);" onclick="clearImage(2)">Click to clear image
                            </a></small></span>
                                </div>

                                <div class="mb-3">
                                    <label for="image_three" class="form-label">Photo three<span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control image_three" data-parsley-trigger="change" name="image_three"
                                        id="image_three" data-parsley-required="false"
                                        accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                        data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024" required>
                                    <span class="form-text text-muted"><small>Rendered size: 640 x 480 ( maximum 1 MB ) <a href="javascript:void(0);" onclick="clearImage(3)">Click to clear image
                            </a></small></span>
                                </div>

                                <div class="text-end">
                                    <a href="javascript:void(0)" class="btn btn-secondary" onclick="clearForm()">Reset</a>
                                    <button class="btn btn-primary" id="product_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save & Upload
                                    </button>
                                </div>

                            </form>--}}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <table id="testimonial-datatable"
                                class="table table-centered table-sm w-100 nowrap">
                                <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    {{--                                <th>Rating</th>--}}
                                    {{--                                <th>Description</th>--}}
                                    <th>Status</th>
                                    <th>Is Default</th>
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
        </div>
    </div>
</div>

    {{--<div id="testimonial-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-right" style="width: 100%;">
            <div class="modal-content" style="height: 100%;">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Create Testimonial</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="ps-3 pe-3 testimonial-form" id="testimonial-form" action="#"
                          enctype="multipart/form-data">

                        <div class="mb-1">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="name" name="name" required=""
                                   placeholder="Enter name" autofocus>
                            <input class="form-control" type="hidden" id="id" name="id" value="0">
                        </div>

                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
                            <label class="form-check-label" for="is_default">Set as Default</label>
                        </div>

                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                            <th>Client Name<span class="text-danger">*</span></th>
                            <th>Description<span class="text-danger">*</span></th>
                            <th>Rating<span class="text-danger">*</span></th>
                            <th>Picture<span class="text-danger">*</span></th>
                            </thead>
                            <tbody class="testimonialRow">
                            <tr id="1">
                                <td>
                                    <input class="form-control" type="text" id="client_name_one" name="client_name_one"
                                           required="" placeholder="Enter name" autofocus>
                                </td>
                                <td>
                                    <textarea class="form-control" id="description_one" name="description_one"
                                              placeholder="Enter description" maxlength="225" data-toggle="maxlength"
                                              required=""></textarea>
                                </td>
                                <td>
                                    <select class="form-select" id="rating_one" name="rating_one" required="">
                                        <option value="">Choose</option>
                                        <option value="1">1.0</option>
                                        --}}{{--                                    <option value="1.5">1.5</option>--}}{{--
                                        <option value="2">2.0</option>
                                        --}}{{--                                    <option value="2.5">2.5</option>--}}{{--
                                        <option value="3">3.0</option>
                                        --}}{{--                                    <option value="3.5">3.5</option>--}}{{--
                                        <option value="4">4.0</option>
                                        --}}{{--                                    <option value="4.5">4.5</option>--}}{{--
                                        <option value="5">5.0</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="file" class="form-control" data-parsley-trigger="change"
                                           name="image_one"
                                           id="image_one" data-parsley-required="false"
                                           accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                           data-parsley-fileextension="jpg,png,jpeg"
                                           data-parsley-max-file-size="1024"
                                           required>
                                    <span class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                </td>
                            </tr>
                            <tr id="2">
                                <td>
                                    <input class="form-control" type="text" id="client_name_two" name="client_name_two"
                                           required="" placeholder="Enter name" autofocus>

                                </td>
                                <td>
                                        <textarea class="form-control" id="description_two" name="description_two"
                                                  placeholder="Enter description" maxlength="225"
                                                  data-toggle="maxlength" required=""></textarea>

                                </td>
                                <td>
                                    <select class="form-select" id="rating_two" name="rating_two" required="">
                                        <option value="">Choose</option>
                                        <option value="1">1.0</option>
                                        --}}{{--                                    <option value="1.5">1.5</option>--}}{{--
                                        <option value="2">2.0</option>
                                        --}}{{--                                    <option value="2.5">2.5</option>--}}{{--
                                        <option value="3">3.0</option>
                                        --}}{{--                                    <option value="3.5">3.5</option>--}}{{--
                                        <option value="4">4.0</option>
                                        --}}{{--                                    <option value="4.5">4.5</option>--}}{{--
                                        <option value="5">5.0</option>
                                    </select>

                                </td>
                                <td>
                                    <input type="file" class="form-control" data-parsley-trigger="change"
                                           name="image_two"
                                           id="image_two" data-parsley-required="false"
                                           accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                           data-parsley-fileextension="jpg,png,jpeg"
                                           data-parsley-max-file-size="1024"
                                           required>
                                    <span class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                </td>
                            </tr>
                            <tr id="3">
                                <td>
                                    <input class="form-control" type="text" id="client_name_three"
                                           name="client_name_three"
                                           required=""
                                           placeholder="Enter name" autofocus>

                                </td>
                                <td>
                                        <textarea class="form-control" id="description_three" name="description_three"
                                                  placeholder="Enter description" maxlength="225"
                                                  data-toggle="maxlength" required=""></textarea>

                                </td>
                                <td>
                                    <select class="form-select" id="rating_three" name="rating_three" required="">
                                        <option value="">Choose</option>
                                        <option value="1">1.0</option>
                                        --}}{{--                                    <option value="1.5">1.5</option>--}}{{--
                                        <option value="2">2.0</option>
                                        --}}{{--                                    <option value="2.5">2.5</option>--}}{{--
                                        <option value="3">3.0</option>
                                        --}}{{--                                    <option value="3.5">3.5</option>--}}{{--
                                        <option value="4">4.0</option>
                                        --}}{{--                                    <option value="4.5">4.5</option>--}}{{--
                                        <option value="5">5.0</option>
                                    </select>

                                </td>
                                <td>
                                    <input type="file" class="form-control" data-parsley-trigger="change"
                                           name="image_three"
                                           id="image_three" data-parsley-required="false"
                                           accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                           data-parsley-fileextension="jpg,png,jpeg"
                                           data-parsley-max-file-size="1024"
                                           required>
                                    <span class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-primary" id="unit_button" type="submit"><i
                                    class="uil-arrow-circle-right"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->--}}

    <div class="modal" id="image-one-modal" tabindex="-1" role="dialog"  aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Photo One</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <div class="row">
                            <div class="col-md-8">
                                <img id="image_ones" src="">
                            </div>
                            <div class="col-md-4">
                                <h5 class="mt-0 mb-0 text-dark ms-2">Preview</h5>
                                <div class="image_one_preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--}}
                    <button type="button" class="btn btn-primary" id="crop_one">Crop</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="image-two-modal" tabindex="-1" role="dialog"  aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Photo Two</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <div class="row">
                            <div class="col-md-8">
                                <img id="image_twos" src="">
                            </div>
                            <div class="col-md-4">
                                <h5 class="mt-0 mb-0 text-dark ms-2">Preview</h5>
                                <div class="image_two_preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--}}
                    <button type="button" class="btn btn-primary" id="crop_two">Crop</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="image-three-modal" tabindex="-1" role="dialog"  aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Photo Three</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <div class="row">
                            <div class="col-md-8">
                                <img id="image_threes" src="">
                            </div>
                            <div class="col-md-4">
                                <h5 class="mt-0 mb-0 text-dark ms-2">Preview</h5>
                                <div class="image_three_preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--}}
                    <button type="button" class="btn btn-primary" id="crop_three">Crop</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/vendor.min.js')}}"></script>
    <script src="{{ asset('assets/js/app.min.js')}}"></script>

    <!-- third party js -->
    @include('layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('assets/js/custom.js')}}"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js')}}"></script>
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
            var table = $("#testimonial-datatable").DataTable({
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
                        title: 'Testimonial List',
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
                        title: 'Testimonial List',
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
                        title: 'Testimonial List',
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
                    url: "{{ route('tenant.testimonial.index', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                "order": [[2, "asc"]],
                columns: [
                    {
                        data: 'id', name: 'id', orderable: false,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox form-check-input" data-id="' + row.action + '">';
                        }
                    },


                    {
                        data: 'image_one', name: 'image_one', orderable: false,
                        render: function (data, type, row) {
                            return '<div id="tooltip-container"> <a href="' + row.image_one + '" download><img src="' + row.image_one + '" class="rounded-circle avatar-xs zoom" alt="friend"></a> <a href="' + row.image_two + '" download><img src="' + row.image_two + '" class="rounded-circle avatar-xs zoom" alt="friend"></a> <a href="' + row.image_three + '" download><img src="' + row.image_three + '" class="rounded-circle avatar-xs zoom" alt="friend"></a></div>';
                        }
                    },
                    {data: 'name', name: 'name', orderable: true},
                    // {data: 'rating', name: 'rating'},
                    // {data: 'description', name: 'description'},
                    {
                        data: 'status', name: 'status',
                        render: function (data, type, row) {
                            var fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.testimonial.edit-status', ['tenant' => $segment])}}','#testimonial-datatable')";
                            if (data == 0)
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Active</span>';
                            else {
                                fun_status = "change_status('" + row.action + "', 0,'{{route('tenant.testimonial.edit-status', ['tenant' => $segment])}}','#testimonial-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }
                        }
                    },
                    {
                        data: 'is_default', name: 'is_default', orderable: false,
                        render: function (data, type, row) {
                            return (row.is_default) ? '<span class="badge badge-primary-lighten">Yes</span>' : '-';
                        }
                    },
                    {
                        data: 'action', name: 'action', orderable: false,
                        render: function (data, type, row) {

                            var edit_fun = "edit_id('" + row.action + "')";
                            var delete_fun = "remove_id('" + row.action + "','{{route('tenant.testimonial.delete', ['tenant' => $segment])}}','#testimonial-datatable')";
                            return '<div class="invoice-action">' +
{{--                                @if(in_array('edit-testimonial', $user_perm) || auth()->user()->company_id==null)--}}
                                '<a href="javascript:void(0)" class="action-icon mr-1" id="edit_' + row.action + '" onclick="' + edit_fun + '">' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
{{--                                @endif--}}
{{--                                @if(in_array('remove-testimonial', $user_perm) || auth()->user()->company_id==null)--}}
                                '<a href="javascript:void(0)" class="action-icon" id="remove_' + row.action + '"  onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete"></i>' +
                                '</a>' +
{{--                                @endif--}}
                                '</div>';
                        }
                    },
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#testimonial-datatable_wrapper .col-md-6:eq(0)"), $("#alternative-page-datatable").DataTable({
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


            formValition('#testimonial-form');
            $('.testimonial-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('tenant.testimonial.store', ['tenant' => $segment])}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        // data: $('.category-form').serialize(),
                        dataType: "json",
                        beforeSend: function () {
                            $("#unit_button").prop('disabled', true);
                            $("#unit_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#testimonial-modal').modal('toggle');
                            clearForm();
                            table.ajax.reload();

                            $("#unit_button").prop('disabled', false);
                            $("#unit_button").html('<i class="uil-arrow-circle-right"></i>  Save & Upload');
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
                            $("#unit_button").prop('disabled', false);
                            $("#unit_button").html('<i class="uil-arrow-circle-right"></i> Save & Upload');
                        },
                        complete: function (data) {
                            $("#unit_button").html('Save');
                            $("#unit_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });
        });

        function clearForm(){
            $('.header-title').text('New Testimonial');
            $("#h_image_one").val('');
            $("#h_image_two").val('');
            $("#h_image_three").val('');
            resetForm('#testimonial-form');
            resetFormValidation('#testimonial-form');
            $('#name').focus();
        }

        function edit_id(id) {
            $.ajax({
                async: false,
                type: "GET",
                url: "{{route('tenant.testimonial.show', ['tenant' => $segment])}}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    $("#image_one").val(null);
                    $("#image_two").val(null);
                    $("#image_three").val(null);
                    $("input[name=is_default]").prop('checked', false);
                    resetFormValidation("#testimonial-form");
                    $('#image_one').prop('required', false);
                    $('#image_two').prop('required', false);
                    $('#image_three').prop('required', false);
                    $('#id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $('#client_name_one').val(res.data.client_name_one);
                    $('#rating_one').val(res.data.rating_one);
                    $('#description_one').val(res.data.description_one);
                    $('#client_name_two').val(res.data.client_name_two);
                    $('#rating_two').val(res.data.rating_two);
                    $('#description_two').val(res.data.description_two);
                    $('#client_name_three').val(res.data.client_name_three);
                    $('#rating_three').val(res.data.rating_three);
                    $('#description_three').val(res.data.description_three);
                    $("input[name=is_default][value=" + res.data.is_default + "]").prop('checked', true);
                    $('.modal-title').text('Edit Testimonial');
                    $('#testimonial-modal').modal('toggle');
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
            remove_id(join_selected_values, '{{route('tenant.testimonial.delete', ['tenant' => $segment])}}', '#testimonial-datatable');
        });

        $('.active_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{route('tenant.testimonial.edit-status', ['tenant' => $segment])}}', '#testimonial-datatable');
        });

        $('.deactive_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{route('tenant.testimonial.edit-status', ['tenant' => $segment])}}', '#testimonial-datatable');
        });

        /*Image crop start*/
        var $modal_one = $('#image-one-modal');
        var image_one = document.getElementById('image_ones');
        var cropper;

        $("body").on("change", "#image_one", function(e){
            var files = e.target.files;
            var done = function (url) {
                image_one.src = url;
                $modal_one.modal('show');
            };
            var reader;
            var file;
            var url;
            if (files && files.length > 0) {
                file = files[0];
                if (URL) {
                    done(URL.createObjectURL(file));
                } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload = function (e) {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
        $modal_one.on('shown.bs.modal', function (){
            cropper = new Cropper(image_one, {
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                toggleDragModeOnDblclick: false,
                // autoCrop: true,
                // autoCropArea: 1,
                aspectRatio: 256/256,
                minCropBoxWidth: 256,
                minCropBoxHeight: 256,
                viewMode: 0,
                scalable: false,
                cropBoxResizable: false,
                zoomable: true,
                cropBoxMovable: true,
                dragMode: 'move',
                // dragMode: 'move',
                // aspectRatio: 1,
                // viewMode: 3,
                preview: '.image_one_preview'
            });
        }).on('hidden.bs.modal', function (){
            console.log("chetan moradiya");
            cropper.scale(1, -1);
            cropper.scaleX(1);
            cropper.scaleY(1);
            cropper.destroy();
            cropper = null;

        });
        $("#crop_one").click(function(){
            canvas = cropper.getCroppedCanvas({
                width: 256,
                height: 256,
                fillColor: '#fff',
                imageSmoothingEnabled:false,
                imageSmoothingQuality:'high',
            });


            canvas.toBlob(function(blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    var base64data_one = reader.result;

                    $("#h_image_one").val(base64data_one);
                    $modal_one.modal('hide');
                    // $.ajax({
                    //     type: "POST",
                    //     dataType: "json",
                    //     url: "crop-image-upload",
                    //     data: {'_token': $('meta[name="_token"]').attr('content'), 'image': base64data},
                    //     success: function(data){
                    //         $modal.modal('hide');
                    //         alert("Crop image successfully uploaded");
                    //     }
                    // });
                }
            }, 'image/jpeg');
        })


        var $modal_two = $('#image-two-modal');
        var image_two = document.getElementById('image_twos');
        var cropper;

        $("body").on("change", "#image_two", function(e){
            var files = e.target.files;
            var done = function (url) {
                image_two.src = url;
                $modal_two.modal('show');
            };
            var reader;
            var file;
            var url;
            if (files && files.length > 0) {
                file = files[0];
                if (URL) {
                    done(URL.createObjectURL(file));
                } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload = function (e) {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
        $modal_two.on('shown.bs.modal', function (){
            cropper = new Cropper(image_two, {

                restore: false,
                guides: false,
                center: false,
                highlight: false,
                toggleDragModeOnDblclick: false,
                // autoCrop: true,
                // autoCropArea: 1,
                aspectRatio: 256/256,
                minCropBoxWidth: 256,
                minCropBoxHeight: 256,
                viewMode: 0,
                scalable: false,
                cropBoxResizable: false,
                zoomable: true,
                cropBoxMovable: true,
                dragMode: 'move',
                // dragMode: 'move',
                // aspectRatio: 1,
                // viewMode: 3,
                preview: '.image_two_preview'
            });
        }).on('hidden.bs.modal', function (){
            cropper.scale(1, -1);
            cropper.scaleX(1);
            cropper.scaleY(1);
            cropper.destroy();
            cropper = null;

        });
        $("#crop_two").click(function(){
            canvas = cropper.getCroppedCanvas({
                width: 256,
                height: 256,
                fillColor: '#fff',
                imageSmoothingEnabled:false,
                imageSmoothingQuality:'high',
            });


            canvas.toBlob(function(blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    var base64data_two = reader.result;
                    $("#h_image_two").val(base64data_two);
                    $modal_two.modal('hide');
                    /*$.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "crop-image-upload",
                        data: {'_token': $('meta[name="_token"]').attr('content'), 'image': base64data},
                        success: function(data){
                            $modal.modal('hide');
                            alert("Crop image successfully uploaded");
                        }
                    });*/
                }
            }, 'image/jpeg');
        })

        var $modal_three = $('#image-three-modal');
        var image_three = document.getElementById('image_threes');
        var cropper;

        $("body").on("change", "#image_three", function(e){
            var files = e.target.files;
            var done = function (url) {
                image_three.src = url;
                $modal_three.modal('show');
            };
            var reader;
            var file;
            var url;
            if (files && files.length > 0) {
                file = files[0];
                if (URL) {
                    done(URL.createObjectURL(file));
                } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload = function (e) {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
        $modal_three.on('shown.bs.modal', function (){
            cropper = new Cropper(image_three, {
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                toggleDragModeOnDblclick: false,
                // autoCrop: true,
                // autoCropArea: 1,
                aspectRatio: 256/256,
                minCropBoxWidth: 256,
                minCropBoxHeight: 256,
                viewMode: 0,
                scalable: false,
                cropBoxResizable: false,
                zoomable: true,
                cropBoxMovable: true,
                dragMode: 'move',
                // dragMode: 'move',
                // aspectRatio: 1,
                // viewMode: 3,
                preview: '.image_three_preview'
            });
        }).on('hidden.bs.modal', function (){
            cropper.scale(1, -1);
            cropper.scaleX(1);
            cropper.scaleY(1);
            cropper.destroy();
            cropper = null;

        });
        $("#crop_three").click(function(){
            canvas = cropper.getCroppedCanvas({
                width: 256,
                height: 256,
                fillColor: '#fff',
                imageSmoothingEnabled:false,
                imageSmoothingQuality:'high',
            });


            canvas.toBlob(function(blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    var base64data_three = reader.result;
                    $("#h_image_three").val(base64data_three);
                    $modal_three.modal('hide');
                    /*var base64data = reader.result;
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "crop-image-upload",
                        data: {'_token': $('meta[name="_token"]').attr('content'), 'image': base64data},
                        success: function(data){
                            $modal.modal('hide');
                            alert("Crop image successfully uploaded");
                        }
                    });*/
                }
            }, 'image/jpeg');
        })
        /*Image crop end*/
    </script>
@endpush
