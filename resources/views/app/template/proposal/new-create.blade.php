@php
    $user_perm = PermissionCheck::check_permission('role-list');
@endphp
@extends('layouts.app')
@section('title','Proposal')
@push('styles')
    <link rel="stylesheet" type="text/css"
          href="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.css">
    <link href="{{ asset('assets/css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
    <style>
        /* html,body{
             min-height: 100vh;
             min-width: 100vw;
         }
         .parent{
             height: 100vh;
         }
         .parent>.row{
             display: flex;
             align-items: center;
             height: 100%;
         }*/
        .col img {
            /*height:100px;*/
            /*width: 100%;*/
            cursor: pointer;
            transition: transform 1s;
            /*object-fit: cover;*/
        }

        .col label {
            overflow: hidden;
            position: relative;
        }

        .imgbgchk:checked + label > .tick_container {
            opacity: 1;
        }

        /*         aNIMATION */
        .imgbgchk:checked + label > img {
            transform: scale(1.25);
            opacity: 0.3;
        }

        .tick_container {
            transition: .5s ease;
            opacity: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            cursor: pointer;
            text-align: center;
        }

        .tick {
            background-color: #000000;
            color: white;
            font-size: 16px;
            padding: 6px 12px;
            height: 40px;
            width: 40px;
            border-radius: 100%;
        }

        .image-radio {
            cursor: pointer;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            border: 2px solid transparent;
            margin-bottom: 0;
            outline: 0;
        }

        .image-radio input[type="radio"] {
            display: none;
        }

        .image-radio-checked {
            border-color: #727cf5;
        }

        .image-radio .mdi {
            position: absolute;
            color: #4A79A3;
            background-color: #fff;
            padding: 10px;
            top: 0;
            right: 0;
        }

        .image-radio-checked .mdi {
            display: block !important;
        }

        img {
            display: block;
            max-width: 100%;
        }

        #image-one-modal img, #aboutus-image-modal img, #image-two-modal img, #image-three-modal img {
            display: block;
            max-width: 100%;
            min-height: 400px;
            max-height:400px;
        }

        .preview {
            overflow: hidden;
            width: 160px;
            height: 160px;
            margin: 10px;
            border: 1px solid red;
        }

        .aboutus_preview {
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


        .modal-lg {
            max-width: 1000px !important;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    {{-- <div class="page-title-right">
                         <ol class="breadcrumb m-0">
                             <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                             <li class="breadcrumb-item"><a href="{{route('quotes.index')}}">Proposal</a></li>
                             <li class="breadcrumb-item active">New</li>
                         </ol>
                     </div>--}}
                    <h4 class="page-title">Edit Proposal</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <form class="proposal-template-form" id="proposal-template-form" method="post">
            <div class="row">
                <div class="col-md-12">
                    <div class="cards">
                        <div class="card-body p-0 m-0">

                            <ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
                                {{--<li class="nav-item">
                                    <a href="#main_tab1" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                        <span class="d-none d-md-block">General Settings</span>
                                    </a>
                                </li>--}}
                                <li class="nav-item">
                                    <a href="#main_tab2" data-bs-toggle="tab" aria-expanded="true"
                                       class="nav-link rounded-0 active">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#1. Cover Page</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#main_tab3" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0">
                                        <i class="mdi mdi-settings-outline d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#2. About Us Page</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="#main_tab4" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#3. Photos Page</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#main_tab5" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#4. Estimate Page</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#main_tab6" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#5. T&C Page</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#main_tab7" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#6. Testimonial Page</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#main_tab8" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">#7. Thank you Page</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                {{--<div class="tab-pane" id="main_tab1">
                                    <p>first</p>
                                </div>--}}
                                <div class="tab-pane show active" id="main_tab2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="name" class="form-label"> Template Name <span
                                                    class="text-danger">*</span></label>
                                            <div class="mb-1">
                                                <input type="text" name="template_name" class="form-control"
                                                       id="template_name"
                                                       placeholder="Enter template name" required
                                                       value="{{$proposal_template->template_name}}">

                                                <input type="hidden" name="id" class="form-control" id="id"
                                                       placeholder="Enter id" required
                                                       value="{{$proposal_template->id}}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="customer_name" class="form-label">Theme color</label>
                                            <div class="mb-1">
                                                <input
                                                    class="form-control form-control-color color_picker form-check-inline"
                                                    id="color_picker_one" type="color" name="theme_color_one"
                                                    type="color"
                                                    value="{{$proposal_template->theme_color_one}}"
                                                    pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$"
                                                    title="Primary color"/>
                                                <input
                                                    class="form-control form-control-color color_picker form-check-inline"
                                                    id="color_picker_two" type="color" name="theme_color_two"
                                                    type="color"
                                                    value="{{$proposal_template->theme_color_two}}"
                                                    pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$"
                                                    title="Secondary color"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="customer_name" class="form-label">Header & Footer color</label>
                                            <div class="mb-1">
                                                <input
                                                    class="form-control form-control-color color_picker form-check-inline"
                                                    id="theme_header_color" type="color" name="theme_header_color"
                                                    type="color"
                                                    value="{{$proposal_template->theme_header_color}}"
                                                    pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" title="Header color"/>
                                                <input
                                                    class="form-control form-control-color color_picker form-check-inline"
                                                    id="theme_footer_color" type="color" name="theme_footer_color"
                                                    type="color"
                                                    value="{{$proposal_template->theme_footer_color}}"
                                                    pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" title="Footer color"/>
                                            </div>
                                        </div>
                                        <div class="row pt-2">
                                            <div class="col-12">
                                                <div class="card border">
                                                    <div class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                        <h4 class="header-title">Image Setting</h4>
                                                        <div class="form-check form-check-inline">
                                                            <input type="checkbox" id="cover_page_flg"
                                                                   name="cover_page_flg"
                                                                   class="form-check-input" {{$proposal_template->cover_page_flg?'checked':''}}>
                                                            <label class="form-check-label" for="cover_page_flg">Display in PDF</label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        {{--<h5 class="card-title">
                                                            <input type="checkbox" class="form-check-input"
                                                                   id="cover_page_flg"
                                                                   name="cover_page_flg" {{$proposal_template->cover_page_flg?'checked':''}}>
                                                            Image Setting</h5>--}}
                                                        <ul class="nav nav-tabs nav-bordered mb-1">
                                                            <li class="nav-item">
                                                                <a href="#cover-page-logo-b1" data-bs-toggle="tab"
                                                                   aria-expanded="false" class="nav-link active">
                                                                    <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                    <span class="d-none d-md-block">Logo</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#cover-page-img-b1" data-bs-toggle="tab"
                                                                   aria-expanded="true" class="nav-link">
                                                                    <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                    <span class="d-none d-md-block">Cover</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#page-setting-b1" data-bs-toggle="tab"
                                                                   aria-expanded="true" class="nav-link">
                                                                    <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                    <span
                                                                        class="d-none d-md-block">Page Setting</span>
                                                                </a>
                                                            </li>
                                                        </ul>

                                                        <div class="tab-content">
                                                            <div class="tab-pane show active"
                                                                 id="cover-page-logo-b1">
                                                                {{--<div class="row">
                                                                    <div class="col-md-12">
                                                                        <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                                class="mdi mdi-office-building me-1"></i>
                                                                            Logo Settings</h5>
                                                                    </div>
                                                                </div>--}}

                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <label class="form-label">Left <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <input class="form-control" type="text"
                                                                                   id="header_logo_left"
                                                                                   name="header_logo_left"
                                                                                   value="{{($proposal_template->header_logo_left)?$proposal_template->header_logo_left:164}}"
                                                                            >
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-2">
                                                                        <label class="form-label">Top <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <input class="form-control" type="text"
                                                                                   id="header_logo_top"
                                                                                   name="header_logo_top"
                                                                                   value="{{($proposal_template->header_logo_top)?$proposal_template->header_logo_top:2}}"
                                                                            >
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-2">
                                                                        <label class="form-label">Size <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <input class="form-control" type="text"
                                                                                   id="header_logo_size"
                                                                                   name="header_logo_size"
                                                                                   value="{{($proposal_template->header_logo_size)?$proposal_template->header_logo_size:40}}"
                                                                            >
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Logo <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <input class="form-control" type="file"
                                                                                   id="header_logo"
                                                                                   name="header_logo"
                                                                                   onchange="image_preview(event)">
                                                                            <span class="form-text text-muted"><small>Rendered size: 213 x 62 ( maximum 1 MB )</small></span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-2">
                                                                        <div class="mb-1">
                                                                            {{--<img
                                                                                src="{{Storage::url($proposal_template->header_logo)}}"
                                                                                alt="image"
                                                                                class="img-fluid avatar rounded"
                                                                                width="100"/>--}}

                                                                            <img
                                                                                src="{{Storage::disk('s3')->temporaryUrl($proposal_template->header_logo,Carbon\Carbon::now()->addMinutes(20));}}"
                                                                                alt="image"
                                                                                class="img-fluid avatar rounded"
                                                                                width="100"/>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                            </div>

                                                            <div class="tab-pane" id="cover-page-img-b1">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label class="form-label">Image <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <input class="form-control image"
                                                                                   type="file"
                                                                                   id="cover_img" name="cover_img">
                                                                            <!-- onchange="cover_preview(event)"-->
                                                                            <span class="form-text text-muted"><small>Rendered size: 1240 x 3508 ( maximum 1 MB )</small></span>
                                                                        </div>
                                                                    </div>


                                                                    <div class="col-md-12">
                                                                        <div class="row">
                                                                            @foreach($proposal_template_cover_photos as $proposal_template_cover_photo)

                                                                                <div
                                                                                    class="col col-2 col-sm-1 col-md-1 nopad text-center"
                                                                                    id="tmp_img_cover_{{$proposal_template_cover_photo->id}}">
                                                                                    <label class="image-radio">
                                                                                        {{--<img
                                                                                            class="img-responsive img-fluid avatar-xl"
                                                                                            src="{{Storage::url($proposal_template_cover_photo->image_icon)}}"/>--}}
                                                                                        <img
                                                                                            class="img-responsive img-fluid avatar-xl"
                                                                                            src="{{Storage::disk('s3')->temporaryUrl($proposal_template_cover_photo->image_icon,Carbon\Carbon::now()->addMinutes(20));}}"/>
                                                                                        <input type="radio"
                                                                                               name="imgbackground"
                                                                                               value="{{$proposal_template_cover_photo->image_icon}}"
                                                                                               @if($proposal_template_cover_photo->image_icon==$proposal_template->cover_img) checked @endif/>
                                                                                        <i class="mdi mdi-check d-none"></i>
                                                                                    </label>
                                                                                    @if($proposal_template_cover_photo->cover_flg==0)
                                                                                        <a href="javascript: void(0);"
                                                                                           onclick="image_cover_remove({{$proposal_template_cover_photo->id}},'{{route("proposal.delete-cover-image")}}','{{$proposal_template_cover_photo->image_icon}}')"
                                                                                           class="float-center btn btn-dark btn-sm"
                                                                                           title="Remove">
                                                                                            <i class="mdi mdi-close-circle"></i>
                                                                                            Remove
                                                                                        </a>
                                                                                    @endif
                                                                                </div>


                                                                            @endforeach
                                                                            {{--                                                                                @foreach($proposal_template_cover_photos as $proposal_template_cover_photo)--}}
                                                                            {{--                                                                                    <div class='col col-2 mb-2'>--}}
                                                                            {{--                                                                                        <input type="radio" name="imgbackground" id="img{{$proposal_template_cover_photo->id}}" class="d-none imgbgchk" value="{{$proposal_template_cover_photo->image_icon}}">--}}
                                                                            {{--                                                                                        <label for="img{{$proposal_template_cover_photo->id}}">--}}
                                                                            {{--                                                                                            <img src="{{Storage::url($proposal_template_cover_photo->image_icon)}}" class="img-fluid avatar-xl img-thumbnail" alt="Proposal Template {{$proposal_template_cover_photo->id}}" width="50">--}}
                                                                            {{--                                                                                            <div class="tick_container">--}}
                                                                            {{--                                                                                                <div class="tick"><i class="mdi mdi-check"></i></div>--}}
                                                                            {{--                                                                                            </div>--}}
                                                                            {{--                                                                                        </label>--}}
                                                                            {{--                                                                                    </div>--}}
                                                                            {{--                                                                                @endforeach--}}
                                                                        </div>

                                                                        {{--                                                                                <img--}}
                                                                        {{--                                                                                    src="{{Storage::url($proposal_template->cover_img)}}"--}}
                                                                        {{--                                                                                    alt="image"--}}
                                                                        {{--                                                                                    class="img-fluid avatar-xl img-thumbnail"/>--}}

                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="tab-pane" id="page-setting-b1">
                                                                <div class="row">
                                                                    <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                            class="mdi mdi-office-building me-1"></i>
                                                                        Page Margin</h5>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Top <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <input class="form-control" type="text"
                                                                                   id="page_top_margin"
                                                                                   name="page_top_margin"
                                                                                   value="{{($proposal_template->page_top_margin)?$proposal_template->page_top_margin:164}}"
                                                                            >
                                                                        </div>
                                                                    </div>


                                                                    {{--<div class="col-md-6">
                                                                        <label class="form-label">Dimension <span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <select class="form-select"
                                                                                    id="logo-div-dimension"
                                                                                    name="logo_dimension_one"
                                                                                    onchange="getDivVal(this.value);">
                                                                                <option>25</option>
                                                                                <option>50</option>
                                                                                <option>75</option>
                                                                                <option>100</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>--}}

                                                                    {{--<div class="col-md-6">
                                                                        <label class="form-label">Image
                                                                            Dimension<span
                                                                                class="text-danger">*</span></label>
                                                                        <div class="mb-1">
                                                                            <select class="form-select"
                                                                                    id="logo-img-dimension"
                                                                                    name="logo_dimension_img"
                                                                                    onchange="getImgVal(this.value);">
                                                                                <option>25</option>
                                                                                <option>50</option>
                                                                                <option>75</option>
                                                                                <option>100</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>--}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!-- end card-body-->
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Content Setting</h5>
                                                        <ul class="nav nav-tabs nav-bordered mb-1">
                                                            <li class="nav-item">
                                                                <a href="#cover-title-b1" data-bs-toggle="tab"
                                                                   aria-expanded="false" class="nav-link active">
                                                                    <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                    <span class="d-none d-md-block">Title</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#cover-content-b1" data-bs-toggle="tab"
                                                                   aria-expanded="true" class="nav-link">
                                                                    <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                    <span class="d-none d-md-block">Content</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#footer-one-b1" data-bs-toggle="tab"
                                                                   aria-expanded="false" class="nav-link">
                                                                    <i class="mdi mdi-settings-outline d-md-none d-block"></i>
                                                                    <span class="d-none d-md-block">Footer 1</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#footer-two-b1" data-bs-toggle="tab"
                                                                   aria-expanded="false" class="nav-link">
                                                                    <i class="mdi mdi-settings-outline d-md-none d-block"></i>
                                                                    <span class="d-none d-md-block">Footer 2</span>
                                                                </a>
                                                            </li>
                                                        </ul>

                                                        <div class="tab-content">
                                                            <div class="tab-pane show active" id="cover-title-b1">
                                                                <label for="name" class="form-label"> Cover page
                                                                    title <span class="text-danger">*</span></label>
                                                                <textarea id="editor" name="cover_title"
                                                                          data-toggle="maxlength"
                                                                          class="form-control" maxlength="225"
                                                                          rows="3"
                                                                          placeholder="This textarea has a limit of 225 chars.">{{($proposal_template->cover_title)? html_entity_decode($proposal_template->cover_title, ENT_QUOTES, 'UTF-8') : ''}}</textarea>
                                                            </div>
                                                            <div class="tab-pane" id="cover-content-b1">
                                                                <label for="name" class="form-label"> Cover main
                                                                    text <span class="text-danger">*</span></label>
                                                                <div class="mb-1">
                                                                        <textarea id="cover_main_text"
                                                                                  name="cover_content"
                                                                                  data-toggle="maxlength"
                                                                                  class="form-control" maxlength="225"
                                                                                  rows="3"
                                                                                  placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->cover_content)? html_entity_decode($proposal_template->cover_content, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="footer-one-b1">
                                                                <label for="name" class="form-label"> Footer 1 <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="mb-1">
                                                                        <textarea id="cover_footer_one"
                                                                                  name="cover_footer_one"
                                                                                  data-toggle="maxlength"
                                                                                  class="form-control" maxlength="225"
                                                                                  rows="3"
                                                                                  placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->cover_footer_one)? html_entity_decode($proposal_template->cover_footer_one, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="footer-two-b1">
                                                                <label for="name" class="form-label"> Footer 2 <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="mb-1">
                                                                        <textarea id="cover_footer_two"
                                                                                  name="cover_footer_two"
                                                                                  data-toggle="maxlength"
                                                                                  class="form-control" maxlength="225"
                                                                                  rows="3"
                                                                                  placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->cover_footer_two)? html_entity_decode($proposal_template->cover_footer_two, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end card-body-->
                                                </div>
                                            </div>
                                        </div>

                                    </div> <!-- end row -->
                                </div>

                                <div class="tab-pane" id="main_tab3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card border">
                                                <div class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                    <h4 class="header-title">Image Setting</h4>
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" id="about_us_flg"
                                                               name="about_us_flg"
                                                               class="form-check-input" {{$proposal_template->about_us_flg?'checked':''}}>
                                                        <label class="form-check-label" for="about_us_flg">Display in PDF</label>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    {{--<h5 class="card-title">
                                                        Image Setting
                                                    </h5>--}}
                                                    <div class="tab-content">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <label class="form-label">Image <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="mb-1">
                                                                    <input class="form-control aboutus_image"
                                                                           type="file"
                                                                           id="aboutas_img"
                                                                           name="aboutas_img"
                                                                    > <!--onchange="cover_preview_aboutus(event)"-->
                                                                    <span class="form-text text-muted"><small>Rendered size: 2480 x 1754 ( maximum 1 MB )</small></span>
                                                                </div>

                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    @foreach($proposal_template_aboutus_photos as $proposal_template_aboutus_photo)

                                                                        <div
                                                                            class="col col-xs-2 col-sm-1 col-md-1 nopad text-center"
                                                                            id="tmp_img_aboutus_{{$proposal_template_aboutus_photo->id}}">
                                                                            <label class="image-radio">
                                                                                {{--<img
                                                                                    class="img-responsive img-fluid avatar-xl"
                                                                                    src="{{Storage::url($proposal_template_aboutus_photo->image_icon)}}"/>--}}
                                                                                <img
                                                                                    class="img-responsive img-fluid avatar-xl"
                                                                                    src="{{Storage::disk('s3')->temporaryUrl($proposal_template_aboutus_photo->image_icon,Carbon\Carbon::now()->addMinutes(20));}}"/>
                                                                                <input type="radio" name="image_aboutus"
                                                                                       value="{{$proposal_template_aboutus_photo->image_icon}}"
                                                                                       @if($proposal_template_aboutus_photo->image_icon==$proposal_template->aboutas_img) checked @endif/>
                                                                                <i class="mdi mdi-check d-none"></i>
                                                                            </label>
                                                                            @if($proposal_template_aboutus_photo->about_flg==0)
                                                                                <a href="javascript: void(0);"
                                                                                   onclick="image_aboutus_remove({{$proposal_template_aboutus_photo->id}},'{{route("proposal.delete-aboutus-image")}}','{{$proposal_template_aboutus_photo->image_icon}}')"
                                                                                   class="float-center btn btn-dark btn-sm"
                                                                                   title="Remove">
                                                                                    <i class="mdi mdi-close-circle"></i>
                                                                                    Remove
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>

                                                                {{--                                                                                <img--}}
                                                                {{--                                                                                    src="{{Storage::url($proposal_template->cover_img)}}"--}}
                                                                {{--                                                                                    alt="image"--}}
                                                                {{--                                                                                    class="img-fluid avatar-xl img-thumbnail"/>--}}

                                                            </div>
                                                            {{--<div class="col-md-4">
                                                                <label class="form-label">Logo Dimension
                                                                    <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="mb-1">
                                                                    <select class="form-select"
                                                                            id="logo-div-dimension-aboutus"
                                                                            name="aboutas_logo_dimension"
                                                                            onchange="getDivValAboutUs(this.value);">
                                                                        <option value="">Choose..</option>
                                                                        <option>5%</option>
                                                                        <option>10%</option>
                                                                        <option>15%</option>
                                                                        <option>20%</option>
                                                                        <option>25%</option>
                                                                        <option>30%</option>
                                                                        <option>35%</option>
                                                                        <option>40%</option>
                                                                        <option>45%</option>
                                                                        <option>50%</option>
                                                                        <option>55%</option>
                                                                        <option>60%</option>
                                                                        <option>65%</option>
                                                                        <option>70%</option>
                                                                        <option>75%</option>
                                                                        <option>80%</option>
                                                                        <option>85%</option>
                                                                        <option>90%</option>
                                                                        <option>95%</option>
                                                                        <option>100%</option>
                                                                    </select>
                                                                </div>
                                                            </div>--}}
                                                        </div>
                                                    </div>
                                                </div><!-- end card-body-->
                                            </div>

                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Content Setting</h5>
                                                    <ul class="nav nav-tabs nav-bordered mb-1">
                                                        <li class="nav-item">
                                                            <a href="#aboutus-title-b1" data-bs-toggle="tab"
                                                               aria-expanded="false"
                                                               class="nav-link active">
                                                                <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Title</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#aboutus-content-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Content</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane show active"
                                                             id="aboutus-title-b1">
                                                            <label for="name" class="form-label"> Cover page
                                                                title <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="editor_aboutus"
                                                                      name="aboutas_title"
                                                                      data-toggle="maxlength"
                                                                      class="form-control" maxlength="225"
                                                                      rows="3"
                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->aboutas_title)? html_entity_decode($proposal_template->aboutas_title, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                        </div>
                                                        <div class="tab-pane" id="aboutus-content-b1">
                                                            <label for="name" class="form-label"> Cover main
                                                                text <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                            <textarea id="aboutus_main_text"
                                                                                      name="aboutas_content"
                                                                                      data-toggle="maxlength"
                                                                                      class="form-control"
                                                                                      maxlength="225" rows="3"
                                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->aboutas_content)? html_entity_decode($proposal_template->aboutas_content, ENT_QUOTES, 'UTF-8') : ''!!}
                                                                            </textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="main_tab4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card border">

                                                    <div
                                                        class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                        <h4 class="header-title">Content Setting</h4>
                                                        <div class="form-check form-check-inline">
                                                        <input type="checkbox" id="photo_position_flg"
                                                               name="photo_position_flg"
                                                               class="form-check-input" {{$proposal_template->photo_position_flg?'checked':''}}>
                                                        <label class="form-check-label" for="photo_position_flg">Display Position in PDF after the estimate page</label>
                                                    </div>
                                                        @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                                                        <a class="btn btn-dark btn-sm" target="_blank"
                                                           href="{{route('product.index')}}">Add Photos</a>
                                                        @endif
                                                    </div>

                                                <div class="card-body pt-1">
                                                    <ul class="nav nav-tabs nav-bordered mb-1">
                                                        <li class="nav-item">
                                                            <a href="#product-title-b1" data-bs-toggle="tab"
                                                               aria-expanded="false"
                                                               class="nav-link active">
                                                                <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Title</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#product-content-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Content</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane show active"
                                                             id="product-title-b1">
                                                            <label for="name" class="form-label"> Cover page
                                                                title <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="editor_product"
                                                                      name="product_title"
                                                                      data-toggle="maxlength"
                                                                      class="form-control" maxlength="225"
                                                                      rows="3"
                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->product_title)? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                        </div>
                                                        <div class="tab-pane" id="product-content-b1">
                                                            <label for="name" class="form-label"> Cover main
                                                                text <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                            <textarea id="product_main_text"
                                                                                      name="product_content"
                                                                                      data-toggle="maxlength"
                                                                                      class="form-control"
                                                                                      maxlength="225" rows="3"
                                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->product_content)? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : ''!!}

                                                                            </textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="main_tab5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card border">
                                                @if (in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                                                    <div
                                                        class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                        <h4 class="header-title">General Setting</h4>
                                                        <a class="btn btn-dark btn-sm" target="_blank"
                                                           href="{{route('item.create')}}">Add Items</a>
                                                    </div>
                                                @endif
                                                <div class="card-body pt-1">
                                                    {{--                                                    <h5 class="card-title">Config</h5>--}}
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Title <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <input class="form-control" type="text"
                                                                       id="estimate_title" name="est_title"
                                                                       value="{{ ($proposal_template->est_title)?$proposal_template->est_title : 'Estimate' }}">
                                                            </div>
                                                        </div>

                                                        {{--<div class="col-md-4">
                                                            <label class="form-label">Logo Dimension <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <select class="form-select"
                                                                        id="logo-div-dimension-estimate"
                                                                        name="est_logo_dimension"
                                                                        onchange="getDivValEstimate(this.value);">
                                                                    <option value="">Choose..</option>
                                                                    <option>5</option>
                                                                    <option>10</option>
                                                                    <option>15</option>
                                                                    <option>20</option>
                                                                    <option>25</option>
                                                                    <option>30</option>
                                                                    <option>35</option>
                                                                    <option>40</option>
                                                                    <option>45</option>
                                                                    <option>50</option>
                                                                    <option>55</option>
                                                                    <option>60</option>
                                                                    <option>65</option>
                                                                    <option>70</option>
                                                                    <option>75</option>
                                                                    <option>80</option>
                                                                    <option>85</option>
                                                                    <option>90</option>
                                                                    <option>95</option>
                                                                    <option>100</option>
                                                                </select>
                                                            </div>
                                                        </div>--}}

                                                        {{--<div class="col-md-4">
                                                            <label class="form-label">Theme Color <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <select class="form-select" id="theme-color"
                                                                        onchange="getDivValThemeEstimate(this.value);">
                                                                    <option value="#000000">Black</option>
                                                                    <optgroup label="Vibrant">
                                                                        <option value="#2F81B7">Blue
                                                                        </option>
                                                                        <option value="#30AD63">Green
                                                                        </option>
                                                                        <option value="#FA6F57">Orange
                                                                        </option>
                                                                        <option value="#BE3A31">Red</option>
                                                                        <option value="#0B685C">Teal
                                                                        </option>
                                                                        <option value="#7A5A9E">Purple
                                                                        </option>
                                                                        <option value="#3B90EC">Light Blue
                                                                        </option>
                                                                        <option value="#134A9E">Indigo
                                                                        </option>
                                                                        <option value="#CE3B5A">Pink
                                                                        </option>
                                                                    </optgroup>
                                                                    <optgroup label="Formal">
                                                                        <option value="#8A5A49">Brown
                                                                        </option>
                                                                        <option value="#3F6167">Turquoise
                                                                            Green
                                                                        </option>
                                                                        <option value="#4D5973">Blue Gray
                                                                        </option>
                                                                        <option value="#239F85">Grean Sea
                                                                        </option>
                                                                    </optgroup>
                                                                </select>
                                                            </div>
                                                        </div>--}}
                                                    </div>
                                                </div><!-- end card-body-->
                                            </div>

                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Content Setting</h5>
                                                    <ul class="nav nav-tabs nav-bordered mb-1">
                                                        <li class="nav-item">
                                                            <a href="#estimate-item-table-b1"
                                                               data-bs-toggle="tab" aria-expanded="false"
                                                               class="nav-link active">
                                                                <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Item Table</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#estimate-bank-info-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Bank Info</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#estimate-term-condition-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Customer Notes</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#estimate-signature-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Signature</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane show active"
                                                             id="estimate-item-table-b1">
                                                            <table
                                                                class="table table-centered table-borderless mb-0 table-sm">
                                                                <thead>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Label</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <th>
                                                                        <div class="form-check mb-1">
                                                                            <input type="checkbox"
                                                                                   class="form-check-input"
                                                                                   id="item_number_flag"
                                                                                   name="item_number_flag"
                                                                                   value="1"
                                                                                   {{ ($proposal_template->item_number_flag)? "checked": "" }}
                                                                                   data-parsley-multiple="item_number_flag">
                                                                            <label class="form-check-label"
                                                                                   for="item_number_flag">Item
                                                                                Number</label>
                                                                        </div>

                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_no"
                                                                               name="item_table_no"
                                                                               value="{!!($proposal_template->item_table_no)?$proposal_template->item_table_no : '#'!!}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th> Item</th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_item"
                                                                               name="item_table_item"
                                                                               value="{{($proposal_template->item_table_item)?$proposal_template->item_table_item : 'Item & Description' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>
                                                                        <input type="checkbox"
                                                                               class="form-check-input"
                                                                               id="item_icon_flag"
                                                                               name="item_icon_flag"
                                                                               value="1"
                                                                               {{ ($proposal_template->item_icon_flag)? "checked": "" }}
                                                                               data-parsley-multiple="item_icon_flag">
                                                                        <label class="form-check-label"
                                                                               for="item_icon_flag">Image</label>
                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_icon"
                                                                               name="item_table_icon"
                                                                               value="{{($proposal_template->item_table_icon)?$proposal_template->item_table_icon : 'Image' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>
                                                                        <input type="checkbox"
                                                                               class="form-check-input"
                                                                               id="item_hsn_flag"
                                                                               name="item_hsn_flag"
                                                                               value="1"
                                                                               {{ ($proposal_template->item_hsn_flag)? "checked": "" }}
                                                                               data-parsley-multiple="item_hsn_flag">
                                                                        <label class="form-check-label"
                                                                               for="item_hsn_flag">HSN/SAC</label>
                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_hsn"
                                                                               name="item_table_hsn"
                                                                               value="{{($proposal_template->item_table_hsn)?$proposal_template->item_table_hsn : 'HSN/SAC' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Qty</th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_qty"
                                                                               name="item_table_qty"
                                                                               value="{{($proposal_template->item_table_qty)?$proposal_template->item_table_qty : 'Qty' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Rate</th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_rate"
                                                                               name="item_table_rate"
                                                                               value="{{($proposal_template->item_table_rate)?$proposal_template->item_table_rate : 'Rate' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>
                                                                        <input type="checkbox"
                                                                               class="form-check-input"
                                                                               id="item_discount_flag"
                                                                               name="item_discount_flag"
                                                                               value="1"
                                                                               {{ ($proposal_template->item_discount_flag)? "checked": "" }}
                                                                               data-parsley-multiple="item_discount_flag">
                                                                        <label class="form-check-label"
                                                                               for="item_discount_flag">Discount</label>
                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_discount"
                                                                               name="item_table_discount"
                                                                               value="{{($proposal_template->item_table_discount)?$proposal_template->item_table_discount : 'Discount' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>
                                                                        <input type="checkbox"
                                                                               class="form-check-input"
                                                                               id="item_cgst_flag"
                                                                               name="item_cgst_flag"
                                                                               value="1"
                                                                               {{ ($proposal_template->item_cgst_flag)? "checked": "" }}
                                                                               data-parsley-multiple="item_cgst_flag">
                                                                        <label class="form-check-label"
                                                                               for="item_cgst_flag">CGST</label>
                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_cgst"
                                                                               name="item_table_cgst"
                                                                               value="{{($proposal_template->item_table_cgst)?$proposal_template->item_table_cgst : 'CGST' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>
                                                                        <input type="checkbox"
                                                                               class="form-check-input"
                                                                               id="item_sgst_flag"
                                                                               name="item_sgst_flag"
                                                                               value="1"
                                                                               {{ ($proposal_template->item_sgst_flag)? "checked": "" }}
                                                                               data-parsley-multiple="item_sgst_flag">
                                                                        <label class="form-check-label"
                                                                               for="item_sgst_flag">SGST</label>
                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_sgst"
                                                                               name="item_table_sgst"
                                                                               value="{{($proposal_template->item_table_sgst)?$proposal_template->item_table_sgst : 'SGST' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>
                                                                        <input type="checkbox"
                                                                               class="form-check-input"
                                                                               id="item_igst_flag"
                                                                               name="item_igst_flag"
                                                                               value="1"
                                                                               {{ ($proposal_template->item_igst_flag)? "checked": "" }}
                                                                               data-parsley-multiple="item_igst_flag">
                                                                        <label class="form-check-label"
                                                                               for="item_igst_flag">IGST</label>
                                                                    </th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_igst"
                                                                               name="item_table_igst"
                                                                               value="{{($proposal_template->item_table_igst)?$proposal_template->item_table_igst : 'IGST' }}">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Total</th>
                                                                    <td><input class="form-control"
                                                                               type="text"
                                                                               id="item_table_total"
                                                                               name="item_table_total"
                                                                               value="{{($proposal_template->item_table_total)?$proposal_template->item_table_total : 'Total' }}">
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="tab-pane" id="estimate-bank-info-b1">
                                                            <label for="name" class="form-label"> Label
                                                                <span class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <input class="form-control" type="text"
                                                                       id="estimate_bank_label"
                                                                       name="est_bank_label"
                                                                       value="{!! ($proposal_template->est_bank_label)? $proposal_template->est_bank_label : 'Bank Detail :'!!}">
                                                            </div>
                                                            <label for="name" class="form-label"> Description <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                            <textarea id="est_bank_details"
                                                                                      name="est_bank_details"
                                                                                      data-toggle="maxlength"
                                                                                      class="form-control"
                                                                                      maxlength="512" rows="3"
                                                                                      placeholder="This textarea has a limit of 512 chars.">{!! ($proposal_template->est_bank_details)? html_entity_decode($proposal_template->est_bank_details, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane"
                                                             id="estimate-term-condition-b1">
                                                            <h5 class="mb-1 text-uppercase bg-light p-2 d-none"><i
                                                                    class="mdi mdi-office-building me-1"></i> Terms &
                                                                Conditions</h5>
                                                            <label for="name" class="form-label d-none"> Label<span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1 d-none">
                                                                <input class="form-control" type="text"
                                                                       id="estimate_term_condition_label"
                                                                       name="est_term_condition_lable"
                                                                       value="{!! ($proposal_template->est_term_condition_lable)? html_entity_decode($proposal_template->est_term_condition_lable, ENT_QUOTES, 'UTF-8') : ''!!}">
                                                            </div>

                                                            <label for="name" class="form-label d-none"> Description
                                                                <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1 d-none">
                                                                            <textarea
                                                                                id="estimate_term_condition_main_text"
                                                                                name="est_term_condition_details"
                                                                                data-toggle="maxlength"
                                                                                class="form-control" maxlength="512"
                                                                                rows="3"
                                                                                placeholder="This textarea has a limit of 512 chars.">{!! ($proposal_template->est_term_condition_details)? html_entity_decode($proposal_template->est_term_condition_details, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                            </div>
                                                            <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                    class="mdi mdi-office-building me-1"></i> Customer
                                                                Notes</h5>

                                                            {{-- <label for="name" class="form-label"> Label<span
                                                                    class="text-danger">*</span></label>
                                                           <div class="mb-1">
                                                                <input class="form-control" type="text"
                                                                       id="estimate_term_condition_label"
                                                                       name="est_term_condition_lable"
                                                                       value="{!! ($proposal_template->est_term_condition_lable)? html_entity_decode($proposal_template->est_term_condition_lable, ENT_QUOTES, 'UTF-8') : ''!!}">
                                                            </div>--}}

                                                            <label for="name" class="form-label"> Description <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                            <textarea
                                                                                id="est_customer_notes_details"
                                                                                name="est_customer_notes_details"
                                                                                data-toggle="maxlength"
                                                                                class="form-control" maxlength="512"
                                                                                rows="3"
                                                                                placeholder="This textarea has a limit of 512 chars.">{!! ($proposal_template->est_customer_notes_details)? html_entity_decode($proposal_template->est_customer_notes_details, ENT_QUOTES, 'UTF-8') : '' !!}</textarea>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane" id="estimate-signature-b1">
                                                            <label for="name" class="form-label"> Label<span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <input class="form-control" type="text"
                                                                       id="estimate_signature_label"
                                                                       name="est_signature_lable"
                                                                       value="{!! ($proposal_template->est_signature_lable)? html_entity_decode($proposal_template->est_signature_lable, ENT_QUOTES, 'UTF-8') : ''!!}">
                                                            </div>
                                                            <label class="form-label">Signature image <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <input class="form-control" type="file"
                                                                       name="est_signature_img" id="est_signature_img"
                                                                       onchange="signature_image_preview(event)">
                                                            </div>
                                                            @if($proposal_template->est_signature_img)
                                                                <div class="col-2" id="image_preview_div">
                                                                    <div class="help-box border mt-3 ms-0"
                                                                         style="background-color: #ffff;display:-webkit-inline-box;">
                                                                        <a href="javascript: void(0);"
                                                                           onclick="image_remove({{Request::segment(4)}},'{{route("proposal.delete-signature-image")}}','{{($proposal_template->est_signature_img && $proposal_template->est_signature_img!='template/64x64.png')?$proposal_template->est_signature_img:''}}')"
                                                                           data-id="{{ Request::segment(4) }}"
                                                                           data-type="{{($proposal_template->est_signature_img && $proposal_template->est_signature_img!='template/64x64.png')?Storage::disk('s3')->url($proposal_template->est_signature_img):''}}"
                                                                           class="float-end close-btn text-black"
                                                                           style="right: 4px;top: 0px;" title="Remove">
                                                                            <i class="mdi mdi-close-circle fs-4"></i>
                                                                        </a>
                                                                        {{--<img
                                                                            src="{{Storage::url($proposal_template->est_signature_img)}}"
                                                                            height="90" alt="Helper Icon Image">--}}
                                                                        <img
                                                                            src="{{($proposal_template->est_signature_img)?Storage::disk('s3')->temporaryUrl($proposal_template->est_signature_img,Carbon\Carbon::now()->addMinutes(20)):'';}}"
                                                                            height="90" alt="Helper Icon Image">
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            <div class="mb-1 d-none">
                                                                {{--<img
                                                                    src="{{Storage::url($proposal_template->est_signature_img)}}"
                                                                    alt="image"
                                                                    class="img-fluid avatar-xl"/>--}}
                                                                <img
                                                                    src="{{($proposal_template->est_signature_img)?Storage::disk('s3')->temporaryUrl($proposal_template->est_signature_img,Carbon\Carbon::now()->addMinutes(20)):'';}}"
                                                                    alt="image"
                                                                    class="img-fluid avatar-xl"/>
                                                                {{--<p class="mb-0">
                                                                    <code>Remove</code>
                                                                </p>--}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="main_tab6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card border">
                                                <div
                                                    class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                    <h4 class="header-title">Content Setting</h4>
                                                    <a class="btn btn-dark btn-sm" target="_blank"
                                                       href="{{url('term-condition')}}">Add Term Condition</a>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="nav nav-tabs nav-bordered mb-1">
                                                        <li class="nav-item">
                                                            <a href="#terms-title-b1" data-bs-toggle="tab"
                                                               aria-expanded="false"
                                                               class="nav-link">
                                                                <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Title</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#terms-content-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link active">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Content</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane"
                                                             id="terms-title-b1">
                                                            <label for="name" class="form-label"> Cover page
                                                                title <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="editor_terms"
                                                                      name="terms_title"
                                                                      data-toggle="maxlength"
                                                                      class="form-control" maxlength="225"
                                                                      rows="3"
                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->terms_title)? html_entity_decode($proposal_template->terms_title, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                        </div>
                                                        <div class="tab-pane show active" id="terms-content-b1">
                                                            <label for="name" class="form-label"> Cover main
                                                                text <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <select class="form-select" id="term_condition_id"
                                                                        name="term_condition_id" required="">
                                                                    <option value="">Choose</option>
                                                                    @foreach($termConditionDatas as $termConditionData)
                                                                        <option
                                                                            value="{{$termConditionData->id}}" {{($termConditionData->id==$proposal_template->term_condition_id)?"selected":""}}>{{$termConditionData->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-1 d-none">

                                                                            <textarea
                                                                                style="display:none"
                                                                                id="terms_main_text"
                                                                                name="terms_content"
                                                                                data-toggle="maxlength"
                                                                                class="form-control"
                                                                                maxlength="225" rows="3"
                                                                                placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->terms_content)? html_entity_decode($proposal_template->terms_content, ENT_QUOTES, 'UTF-8') : ''!!}

                                                                            </textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="main_tab7">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card border">
                                                <div
                                                    class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                    <h4 class="header-title">Content Setting</h4>
                                                    <a class="btn btn-dark btn-sm" target="_blank"
                                                       href="{{route('testimonial.index')}}">Add Testimonial</a>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="nav nav-tabs nav-bordered mb-1">
                                                        <li class="nav-item">
                                                            <a href="#testimonials-title-b1"
                                                               data-bs-toggle="tab"
                                                               aria-expanded="false"
                                                               class="nav-link active">
                                                                <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Title</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#testimonials-content-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Content</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane show active"
                                                             id="testimonials-title-b1">
                                                            <label for="name" class="form-label"> Cover page
                                                                title <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="editor_testimonials"
                                                                      name="testimonials_title"
                                                                      data-toggle="maxlength"
                                                                      class="form-control" maxlength="225"
                                                                      rows="3"
                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->testimonials_title)? html_entity_decode($proposal_template->testimonials_title, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                        </div>
                                                        <div class="tab-pane" id="testimonials-content-b1">
                                                            <label for="name" class="form-label"> Cover main
                                                                text <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                            <textarea id="testimonials_main_text"
                                                                                      name="testimonials_content"
                                                                                      data-toggle="maxlength"
                                                                                      class="form-control"
                                                                                      maxlength="225" rows="3"
                                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->testimonials_content)? html_entity_decode($proposal_template->testimonials_content, ENT_QUOTES, 'UTF-8') : ''!!}

                                                                            </textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="main_tab8">
                                    <div class="row">
                                        <div class="col-md-12">
                                            {{--<div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Content Setting</h5>
                                                    <ul class="nav nav-tabs nav-bordered mb-1">
                                                        <li class="nav-item">
                                                            <a href="#terms-title-b1" data-bs-toggle="tab"
                                                               aria-expanded="false"
                                                               class="nav-link active">
                                                                <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Title</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#terms-content-b1"
                                                               data-bs-toggle="tab" aria-expanded="true"
                                                               class="nav-link">
                                                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                                                <span
                                                                    class="d-none d-md-block">Content</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane show active"
                                                             id="terms-title-b1">
                                                            <label for="name" class="form-label"> Cover page
                                                                title <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="editor_terms"
                                                                      name="terms_title"
                                                                      data-toggle="maxlength"
                                                                      class="form-control" maxlength="225"
                                                                      rows="3"
                                                                      placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->terms_title)? html_entity_decode($proposal_template->terms_title, ENT_QUOTES, 'UTF-8') : ''!!}</textarea>
                                                        </div>
                                                        <div class="tab-pane" id="terms-content-b1">
                                                            <label for="name" class="form-label"> Cover main
                                                                text <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="mb-1">
                                                                <textarea id="terms_main_text"
                                                                          name="terms_content"
                                                                          data-toggle="maxlength"
                                                                          class="form-control"
                                                                          maxlength="225" rows="3"
                                                                          placeholder="This textarea has a limit of 225 chars.">{!! ($proposal_template->terms_content)? html_entity_decode($proposal_template->terms_content, ENT_QUOTES, 'UTF-8') : ''!!}

                                                                </textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> <!-- end card-body-->
                                            </div>--}}

                                            <div class="card border border">
                                                <div class="d-flex card-headers p-2 table-borderless justify-content-between align-items-center">
                                                    <h4 class="header-title">Image Setting</h4>
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" id="thank_you_flg"
                                                               name="thank_you_flg"
                                                               class="form-check-input" {{$proposal_template->thank_you_flg?'checked':''}}>
                                                        <label class="form-check-label" for="thank_you_flg">Display in PDF</label>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="tab-content">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Image <span
                                                                        class="text-danger">*</span></label>
                                                                <div class="mb-1">
                                                                    <input class="form-control" type="file"
                                                                           id="thank_you_img"
                                                                           name="thank_you_img">
                                                                    {{--                                                                            onchange="cover_preview_thankyou(event)"--}}
                                                                    <span class="form-text text-muted"><small>Rendered size: 2480 x 1754 ( maximum 1 MB )</small></span>
                                                                </div>
                                                            </div>

                                                            <div class="col-2">
                                                                {{--<img
                                                                    src="{{Storage::url($proposal_template->thank_you_img)}}"
                                                                    id="preview_image_container_thankyou"
                                                                    class="preview_image_container_thankyou"
                                                                    alt="Put logo Here"
                                                                    style="width: 100%;"/>--}}
                                                                <img
                                                                    src="{{Storage::disk('s3')->temporaryUrl($proposal_template->thank_you_img,Carbon\Carbon::now()->addMinutes(20));}}"
                                                                    id="preview_image_container_thankyou"
                                                                    class="preview_image_container_thankyou"
                                                                    alt="Put logo Here"
                                                                    style="width: 100%;"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!-- end card-body-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{--                        <div class="accordion custom-accordion" id="custom-accordion-one">--}}
                        {{--                        <div class="card-footer">--}}
                        <div class="text-end">
                            <a href="{{route('proposal.pdf-preview')}}" class="btn btn-secondary me-2"
                               target="_blank"><i class="uil uil-web-grid-alt"></i> Preview</a>
                            <input type="submit" form="proposal-template-form"
                                   class="btn btn-primary proposal_template_button float-end"
                                   id="proposal_template_button" value="Save"/>
                        </div>
                        {{--                        </div>--}}
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <input class="form-control" type="hidden" id="h_image_two" name="h_image_two" value="">
            <input class="form-control" type="hidden" id="h_image_three" name="h_image_three" value="">
        </form>
    </div>


    <div class="modal" id="image-one-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Cover Image</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <div class="row">
                            <div class="col-md-8">
                                <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">
                            </div>
                            <div class="col-md-4">
                                <h5 class="mt-0 mb-0 text-dark ms-2">Preview</h5>
                                <div class="preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--}}
                    <button type="button" class="btn btn-primary" id="crop">Crop & Upload</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="aboutus-image-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Aboutus Image</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <div class="row">
                            <div class="col-md-8">
                                <img id="aboutus_image" src="https://avatars0.githubusercontent.com/u/3456749">
                            </div>
                            <div class="col-md-4">
                                <h5 class="mt-0 mb-0 text-dark ms-2">Preview</h5>
                                <div class="aboutus_preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--}}
                    <button type="button" class="btn btn-primary" id="aboutus_crop">Crop & Upload</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="image-two-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
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

    <div class="modal" id="image-three-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
         aria-hidden="true">
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
    <script src="{{ asset('assets/js/custom.js')}}"></script>
    <script src="{{ asset('ckeditor/ckeditor.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js')}}"></script>
    <!-- third party js ends -->

    <script>
        $(document).ready(function () {
            // add/remove checked class
            $(".image-radio").each(function () {
                if ($(this).find('input[type="radio"]').first().attr("checked")) {
                    $(this).addClass('image-radio-checked');
                } else {
                    $(this).removeClass('image-radio-checked');
                }
            });

            // sync the input state
            $(".image-radio").on("click", function (e) {
                $(".image-radio").removeClass('image-radio-checked');
                $(this).addClass('image-radio-checked');
                var $radio = $(this).find('input[type="radio"]');
                $radio.prop("checked", !$radio.prop("checked"));

                e.preventDefault();
            });
        });
        CKEDITOR.replace('editor', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('cover_main_text', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['customers.company_name', 'customers.name', 'customers.address', 'customers.pincode', 'customers.country_name', 'customers.state_name', 'customers.city_name'],
                format: '${%placeholder%}'
            }
        });

        CKEDITOR.replace('editor_aboutus', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('aboutus_main_text', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['companies.company_name'],
                format: '${%placeholder%}'
            }
        });

        CKEDITOR.replace('editor_product', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('product_main_text', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('editor_testimonials', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('testimonials_main_text', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('editor_terms', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('terms_main_text', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('estimate_cover_main_text', {
            extraPlugins: 'editorplaceholder',
        });
        // CKEDITOR.replace('estimate_term_condition_main_text', {
        //     extraPlugins: 'editorplaceholder',
        // });

        CKEDITOR.replace('cover_footer_one', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['companies.name', 'companies.company_name', 'companies.address', 'companies.pincode', 'companies.country_name', 'companies.state_name', 'companies.city_name'],
                format: '${%placeholder%}'
            }
        });

        CKEDITOR.replace('cover_footer_two', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['estimates.estimate_no', 'estimates.estimate_date', 'estimates.sales_person_id', 'customers.lead_category'],
                format: '${%placeholder%}'
            }
        });

        CKEDITOR.on('editor', function (event) {
            if ('placeholder' == event.data.name) {
                var input = event.data.definition.getContents('info').get('name');
                input.type = 'select';
                input.items = [['FirstName'], ['LastName']];
                input.setup = function () {
                    this.setValue('FirstName');
                };
            }
        });

        $(document).ready(function () {
            $('.color_picker').on('change', function () {
                if ($(this).attr('id') == 'color_picker_one') {
                    $('.main_div_bg').css('background-color', $(this).val());
                    $('#color_picker_one').val($(this).val());
                    getDivValThemeEstimate($(this).val())
                }

                if ($(this).attr('id') == 'color_picker_two') {
                    $('#partial_div_bg').css('background-color', $(this).val());
                    $('#color_picker_two').val($(this).val());
                }
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // formValition('#proposal-template-form');
            $('.proposal-template-form').on('submit', function (e) {
                e.preventDefault();
                // if ( $(this).parsley().isValid() ) {

                let aboutUsTtile = CKEDITOR.instances.editor_aboutus.getData();
                let aboutUsTtiles = $.trim(aboutUsTtile.replace(/(<([^>]+)>)/ig, ""));

                if (aboutUsTtiles.length > 100) {
                    toastrError('About us title value is too long. It should have 50 characters or fewer.', 'Error');
                    return false;
                }
                var formData = new FormData(this);
                formData.append("cover_title", CKEDITOR.instances.editor.getData());
                formData.append("cover_content", CKEDITOR.instances.cover_main_text.getData());
                formData.append("cover_footer_one", CKEDITOR.instances.cover_footer_one.getData());
                formData.append("cover_footer_two", CKEDITOR.instances.cover_footer_two.getData());
                formData.append("aboutas_title", CKEDITOR.instances.editor_aboutus.getData());
                formData.append("aboutas_content", CKEDITOR.instances.aboutus_main_text.getData());
                formData.append("product_title", CKEDITOR.instances.editor_product.getData());
                formData.append("product_content", CKEDITOR.instances.product_main_text.getData());
                formData.append("testimonials_title", CKEDITOR.instances.editor_testimonials.getData());
                formData.append("testimonials_content", CKEDITOR.instances.testimonials_main_text.getData());
                formData.append("terms_title", CKEDITOR.instances.editor_terms.getData());
                formData.append("terms_content", CKEDITOR.instances.terms_main_text.getData());
                // formData.append("est_bank_details", CKEDITOR.instances.estimate_cover_main_text.getData());
                // formData.append("est_term_condition_details", CKEDITOR.instances.estimate_term_condition_main_text.getData());
                formData.append("theme_color_one", $('#color_picker_one').val());
                formData.append("theme_color_two", $('#color_picker_two').val());
                formData.append("theme_header_color", $('#theme_header_color').val());
                formData.append("theme_footer_color", $('#theme_footer_color').val());

                var item_number_flag = 0;
                if ($("#item_number_flag").is(":checked")) {
                    item_number_flag = 1;
                }

                var item_hsn_flag = 0;
                if ($("#item_hsn_flag").is(":checked")) {
                    item_hsn_flag = 1;
                }
                var item_discount_flag = 0;
                if ($("#item_discount_flag").is(":checked")) {
                    item_discount_flag = 1;
                }
                var item_cgst_flag = 0;
                if ($("#item_cgst_flag").is(":checked")) {
                    item_cgst_flag = 1;
                }
                var item_sgst_flag = 0;
                if ($("#item_sgst_flag").is(":checked")) {
                    item_sgst_flag = 1;
                }
                var item_igst_flag = 0;
                if ($("#item_igst_flag").is(":checked")) {
                    item_igst_flag = 1;
                }

                var item_icon_flag = 0;
                if ($("#item_icon_flag").is(":checked")) {
                    item_icon_flag = 1;
                }
                formData.append("item_number_flag", item_number_flag);
                formData.append("item_hsn_flag", item_hsn_flag);
                formData.append("item_discount_flag", item_discount_flag);
                formData.append("item_cgst_flag", item_cgst_flag);
                formData.append("item_sgst_flag", item_sgst_flag);
                formData.append("item_igst_flag", item_igst_flag);
                formData.append("item_icon_flag", item_icon_flag);

                $.ajax({
                    type: 'POST',
                    url: '{{route('proposal.store')}}',
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: formData,
                    // data: $(this).serialize(),
                    dataType: "json",
                    beforeSend: function () {
                        $("#proposal_template_button").prop('disabled', true);
                        $("#proposal_template_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    success: function (data) {
                        toastrSuccess('Successfully saved...', 'Success');
                        $("#proposal_template_button").prop('disabled', false);
                        $("#proposal_template_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        {{--window.location.href = '{{route('proposal.index')}}';--}}
                        location.reload();
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
                                toastrInfo('Estimate no already exist.', 'Warning');
                                break;
                            default:
                                toastrError('Error - ' + errorMessage, 'Error');
                        }
                        $("#proposal_template_button").prop('disabled', false);
                        $("#proposal_template_button").html('<i class="uil-arrow-circle-right"></i> Save');
                    },
                    complete: function (data) {
                        $("#proposal_template_button").html('Save');
                        $("#proposal_template_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                    }
                });
                // }


            });
        });

        var $modal_cover = $('#image-one-modal');
        var image_cover = document.getElementById('image');
        var cropper;

        $("body").on("change", "#cover_img", function (e) {
            var files = e.target.files;
            var done = function (url) {
                image_cover.src = url;
                $modal_cover.modal('show');
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
        $modal_cover.on('shown.bs.modal', function () {
            cropper = new Cropper(image_cover, {
                // autoCrop: true,
                // autoCropArea: 1,
                aspectRatio: 1240 / 3508,
                minCropBoxWidth: 1240,
                minCropBoxHeight: 3508,
                maxContainerHeight: 3508,
                maxContainerWidth: 1240,
                viewMode: 0,
                scalable: false,
                cropBoxResizable: false,
                zoomable: true,
                cropBoxMovable: true,
                dragMode: 'move',
                // dragMode: 'move',
                // aspectRatio: 1,
                // viewMode: 3,
                preview: '.preview'
            });
        }).on('hidden.bs.modal', function () {
            /* cropper.scale(1, -1);
             cropper.scaleX(1);
             cropper.scaleY(1);
             cropper.destroy();
             cropper = null;*/

        });
        $("#crop").click(function () {
            canvas = cropper.getCroppedCanvas({
                // width: 2160,
                // height: 1440,
                width: 1240,
                height: 3508,
                fillColor: '#fff',
                imageSmoothingEnabled: false,
                imageSmoothingQuality: 'high',
            });


            canvas.toBlob(function (blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    var base64data = reader.result;
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{route('croImg.crop-cover-image-upload')}}",
                        data: {
                            '_token': $('meta[name="csrf-token"]').attr('content'),
                            'image': base64data,
                            id: {{$proposal_template->id}}
                        },
                        beforeSend: function () {
                            $("#crop").prop('disabled', true);
                            $("#crop").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            $("#cover_img").val(null);
                            // console.log(data);
                            $modal.modal('hide');
                            toastrSuccess('Cover image successfully uploaded...', 'Success');
                            location.reload();
                        },
                        complete: function (data) {
                            $("#crop").html('Crop & Upload');
                            $("#crop").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            }, 'image/jpeg');
        })


        var $modal = $('#aboutus-image-modal');
        var image = document.getElementById('aboutus_image');
        var cropper;

        $("body").on("change", ".aboutus_image", function (e) {
            var files = e.target.files;
            var done = function (url) {
                image.src = url;
                $modal.modal('show');
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
        $modal.on('shown.bs.modal', function () {
            cropper = new Cropper(image, {
                // autoCrop: true,
                // autoCropArea: 1,
                aspectRatio: 2480 / 1754,
                minCropBoxWidth: 2480,
                minCropBoxHeight: 1754,
                maxContainerHeight: 1754,
                maxContainerWidth: 2480,
                viewMode: 0,
                scalable: false,
                cropBoxResizable: false,
                zoomable: true,
                cropBoxMovable: true,
                dragMode: 'move',
                // dragMode: 'move',
                // aspectRatio: 1,
                // viewMode: 3,
                preview: '.aboutus_preview'
            });
        }).on('hidden.bs.modal', function () {
            cropper.scale(1, -1);
            cropper.scaleX(1);
            cropper.scaleY(1);
            cropper.destroy();
            cropper = null;

        });
        $("#aboutus_crop").click(function () {
            canvas = cropper.getCroppedCanvas({
                // width: 2160,
                // height: 1440,
                width: 2480,
                height: 1754,
                fillColor: '#fff',
                imageSmoothingEnabled: false,
                imageSmoothingQuality: 'high',
            });


            canvas.toBlob(function (blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    var base64data = reader.result;
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{route('croImg.crop-aboutus-image-upload')}}",
                        data: {
                            '_token': $('meta[name="csrf-token"]').attr('content'),
                            'image': base64data,
                            id: {{$proposal_template->id}}
                        },
                        beforeSend: function () {
                            $("#aboutus_crop").prop('disabled', true);
                            $("#aboutus_crop").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            $("#aboutas_img").val(null);
                            // console.log(data);
                            $modal.modal('hide');
                            toastrSuccess('About us image successfully uploaded...', 'Success');
                            location.reload();
                        },
                        complete: function (data) {
                            $("#aboutus_crop").html('Crop & Upload');
                            $("#aboutus_crop").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            }, 'image/jpeg');
        })

        var $modal_two = $('#image-two-modal');
        var image_two = document.getElementById('image_twos');
        var cropper;

        $("body").on("change", "#thank_you_img", function (e) {
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
        $modal_two.on('shown.bs.modal', function () {
            cropper = new Cropper(image_two, {

                restore: false,
                guides: false,
                center: false,
                highlight: false,
                toggleDragModeOnDblclick: false,
                // autoCrop: true,
                // autoCropArea: 1,
                aspectRatio: 640 / 480,
                minCropBoxWidth: 640,
                minCropBoxHeight: 480,
                maxContainerHeight: 480,
                maxContainerWidth: 640,
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
        }).on('hidden.bs.modal', function () {
            cropper.scale(1, -1);
            cropper.scaleX(1);
            cropper.scaleY(1);
            cropper.destroy();
            cropper = null;

        });
        $("#crop_two").click(function () {
            canvas = cropper.getCroppedCanvas({
                width: 640,
                height: 480,
                fillColor: '#fff',
                imageSmoothingEnabled: false,
                imageSmoothingQuality: 'high',
            });


            canvas.toBlob(function (blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
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
            }, 'image/png');
        })

        var $modal_three = $('#image-three-modal');
        var image_three = document.getElementById('image_threes');
        var cropper;

        $("body").on("change", "#est_signature_img", function (e) {
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
        $modal_three.on('shown.bs.modal', function () {
            cropper = new Cropper(image_three, {
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                toggleDragModeOnDblclick: false,
                // autoCrop: true,
                // autoCropArea: 1,
                 aspectRatio: 640/480,
                 minCropBoxWidth: 640,
                 minCropBoxHeight: 480,
                maxContainerHeight: 480,
                maxContainerWidth: 640,
                viewMode: 0,
                scalable: true,
                cropBoxResizable: true,
                zoomable: true,
                cropBoxMovable: true,
                dragMode: 'move',
                // dragMode: 'move',
                // aspectRatio: 1,
                // viewMode: 3,
                preview: '.image_three_preview'
            });
        }).on('hidden.bs.modal', function () {
            cropper.scale(1, -1);
            cropper.scaleX(1);
            cropper.scaleY(1);
            cropper.destroy();
            cropper = null;

        });
        $("#crop_three").click(function () {
            canvas = cropper.getCroppedCanvas({
                width: 640,
                height: 480,
                fillColor: '#fff',
                imageSmoothingEnabled: false,
                imageSmoothingQuality: 'high',
            });


            canvas.toBlob(function (blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
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
    </script>
@endpush
