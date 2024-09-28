@php
    $user_perm = PermissionCheck::check_permission('role-list');
@endphp
@extends('layouts.app')
@section('title','Estimate')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
    <style>
        .overlay {
            position: absolute;
            bottom: 6px;
            left: 0;
            right: 0;
            background-color: rgba(255, 255, 255, 0.5);
            overflow: hidden;
            height: 0;
            transition: .5s ease;
            width: 44%;
        }

        .image_area:hover .overlay {
            height: 50%;
            cursor: pointer;
        }

        .text {
            color: #333;
            font-size: 12px;
            position: absolute;
            top: 50%;
            left: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .image_area {
            position: relative;
        }

        #image-one-modal .modal-lg{
            max-width: 1000px !important;
        }

        #image-one-modal img {
            display: block;
            max-width: 100%;
            min-height: 400px;
            max-height:400px;
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

        .photo-upload {
            position: relative;
            overflow: hidden;
            margin: 10px;
        }


        .photo-upload input.file-input {
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            padding: 0;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            filter: alpha(opacity=0);
            height:100%;
        }

        .photo-upload input.file-input-small {
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            padding: 0;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            filter: alpha(opacity=0);
            height:100%;
        }
        /*----------------------------*/
        .aboutus_title p {
            margin-bottom: 0px !important;
        }

        .ui-menu .ui-menu-item {
            border-bottom: 1px solid #EBEBEB;
        }

        .ui-autocomplete .header-title {
            margin: 0px;
            padding-bottom: 0px !important;
        }

        .ui-autocomplete .desc {
            margin: 0px;
            font-size: 11px;
            font-weight: 500;
            padding-top: 0px !important;
        }

        .header-title {
            font-size: 14px;
            font-weight: 500;
            font-size: 14px;
            line-height: 19px;
            letter-spacing: 0.5px;
            color: #353A40;
            /* margin: 0 0 7px 0; */
        }

        .ui-menu .ui-menu-item-wrapper {
            position: relative;
            padding: 10px 15px;
        }

        .ui-menu > h4 {
            margin: 10px 0;
            color: #343a40;
        }

        .ui-state-active,
        .ui-widget-content .ui-state-active {
            margin: 0 !important;
            border: none !important;
        }

        .header-title .ui-state-active,
        .header-title .ui-state-active:hover {
            font-size: 14px;
            line-height: 19px;

            color: #353A40;
        }

        .desc .ui-state-active,
        .desc .ui-state-active:hover {
            margin: 0px !important;
            font-size: 11px;
            padding-top: 0px !important;
        }

        .ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active, a.ui-button:active, .ui-button:active, .ui-button.ui-state-active:hover {
            border: 1px solid #ECF6FF;
            background: #ECF6FF;
            font-weight: normal;
            color: #353A40;
        }

        /* #ui-id-1 {
             -moz-transform: rotate(180deg);
             -webkit-transform: rotate(180deg);
             transform: rotate(180deg);
         }

         #ui-id-1 > li {
             -moz-transform: rotate(-180deg);
             -webkit-transform: rotate(-180deg);
             transform: rotate(-180deg);
         }*/

        table td .form-control, table td select {
            border: 1px solid rgba(0, 0, 0, 0) !important;
            padding: 0.1rem 0.2rem !important;
            /*padding: 0.3rem 0.5rem !important;*/
        }

        .ui-menu .ui-menu-item {
            border-top: 1px solid #EBEBEB;
        }

        /*.ui-autocomplete {*/
        /*    max-height: 100px;*/
        /*    overflow-y: auto;*/
        /*    !* prevent horizontal scrollbar *!*/
        /*    overflow-x: hidden;*/
        /*}*/
        /*!* IE 6 doesn't support max-height*/
        /* * we use height instead, but this forces the menu to always be this tall*/
        /* *!*/
        /*.ui-autocomplete {*/
        /*    height: 100px;*/
        /*}*/

        .ui-autocomplete {
            width: 25%;
        }

        .item_description {
            overflow: hidden !important;
        }

        .ui-menu-item-wrapper a {
            display: block !important;
        }
    </style>

@endpush
@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{route('quotes.index')}}">Estimate</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Edit Estimate</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <form class="estimate-form" id="estimate-form" method="post" data-parsley-validate="">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body p-0 m-0">
                            <ul class="nav nav-pills bg-nav-pills nav-justified mb-1">
                                @if($estimate->status !='')
                                <li class="nav-item">
                                    <a href="#home1" data-bs-toggle="tab" aria-expanded="false"
                                       class="nav-link rounded-0 @if(Request::segment(4) && $estimate->status !='') active @endif">
                                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                        <span class="d-none d-md-block">Edit</span>
                                    </a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <a href="#preview" data-bs-toggle="tab" aria-expanded="true"
                                       class="nav-link rounded-0  @if(!Request::segment(4) || $estimate->status =='') active @endif">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">Preview</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane @if(Request::segment(4) && $estimate->status !='') show active @endif" id="home1">
                                    <div class="accordion custom-accordion" id="custom-accordion-one">
                                        <div class="card mb-0">
                                            <div class="card-header" id="headingFour">
                                                <h5 class="m-0">
                                                    <div class="form-check form-checkbox-dark">
                                                        <input type="checkbox" class="form-check-input"
                                                               id="pdf_cover_page_flg"
                                                               name="pdf_cover_page_flg" @if($estimate->pdf_cover_page_flg){{'checked'}} @endif>
                                                        <a class="custom-accordion-title d-block py-1"
                                                           data-bs-toggle="collapse" href="#collapseFour"
                                                           aria-expanded="true" aria-controls="collapseFour">
                                                            Cover page <i
                                                                class="mdi mdi-chevron-down accordion-arrow"></i>
                                                        </a>
                                                    </div>

                                                </h5>
                                            </div>

                                            <div id="collapseFour" class="collapse tab-validation"
                                                 aria-labelledby="headingFour"
                                                 data-bs-parent="#custom-accordion-one">
                                                <div class="card-body">
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
                                                               aria-expanded="false" class="nav-link"
                                                               title="Footer one">
                                                                <i class="mdi mdi-page-layout-footer d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Footer 1</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#footer-two-b1" data-bs-toggle="tab"
                                                               aria-expanded="false" class="nav-link"
                                                               title="Footer two">
                                                                <i class="mdi mdi-page-layout-footer d-md-none d-block"></i>
                                                                <span class="d-none d-md-block">Footer 2</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <div class="tab-pane show active" id="cover-title-b1">
                                                <textarea id="est_cover_page_title" name="est_cover_page_title"
                                                          data-toggle="maxlength"
                                                          class="form-control" maxlength="2048"
                                                          rows="3"
                                                          placeholder="This textarea has a limit of 2048 chars.">{{html_entity_decode($estimate->est_cover_page_title, ENT_QUOTES, 'UTF-8')}}</textarea>
                                                        </div>
                                                        <div class="tab-pane" id="cover-content-b1">
                                                            <div class="mb-1">
                                                    <textarea id="est_cover_page_content"
                                                              name="est_cover_page_content"
                                                              data-toggle="maxlength"
                                                              class="form-control" maxlength="2048"
                                                              rows="3"
                                                              placeholder="This textarea has a limit of 2048 chars.">{!! html_entity_decode($estimate->est_cover_page_content, ENT_QUOTES, 'UTF-8')      !!}
                                                    </textarea>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="footer-one-b1">
                                                            <div class="mb-1">
                                                    <textarea id="est_cover_page_footer_one"
                                                              name="est_cover_page_footer_one"
                                                              data-toggle="maxlength"
                                                              class="form-control" maxlength="2048"
                                                              rows="3"
                                                              placeholder="This textarea has a limit of 2048 chars.">{!!html_entity_decode($estimate->est_cover_page_footer_one, ENT_QUOTES, 'UTF-8')!!}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="footer-two-b1">
                                                            <div class="mb-1">
                                                    <textarea id="est_cover_page_footer_two"
                                                              name="est_cover_page_footer_two"
                                                              data-toggle="maxlength"
                                                              class="form-control" maxlength="2048"
                                                              rows="3"
                                                              placeholder="This textarea has a limit of 2048 chars.">{!! html_entity_decode($estimate->est_cover_page_footer_two, ENT_QUOTES, 'UTF-8')!!}</textarea>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3 text-end">
                                                            <button class="btn btn-primary estimate_button"
                                                                    type="submit"><i
                                                                    class="uil-arrow-circle-right"></i> Revise Estimate
                                                            </button>
                                                            <a href="{{route('quotes.index')}}" type="button"
                                                               class="btn btn-light">Close</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-0">
                                            <div class="card-header" id="headingFive">
                                                <h5 class="m-0">
                                                    <div class="form-check form-checkbox-dark">
                                                        <input type="checkbox" class="form-check-input"
                                                               id="pdf_about_us_flg"
                                                               name="pdf_about_us_flg" @if($estimate->pdf_about_us_flg){{'checked'}} @endif>
                                                        <a class="custom-accordion-title collapsed d-block py-1"
                                                           data-bs-toggle="collapse" href="#collapseFive"
                                                           aria-expanded="false" aria-controls="collapseFive">
                                                            About Us <i
                                                                class="mdi mdi-chevron-down accordion-arrow"></i>
                                                        </a>
                                                    </div>
                                                </h5>
                                            </div>
                                            <div id="collapseFive" class="collapse tab-validation"
                                                 aria-labelledby="headingFive"
                                                 data-bs-parent="#custom-accordion-one">
                                                <div class="card-body">
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
                                                            <textarea id="est_aboutus_title"
                                                                      name="est_aboutus_title"
                                                                      data-toggle="maxlength"
                                                                      class="form-control" maxlength="2048"
                                                                      rows="3"
                                                                      placeholder="This textarea has a limit of 2048 chars.">{!! html_entity_decode($estimate->est_aboutus_title, ENT_QUOTES, 'UTF-8')!!}
                                                            </textarea>
                                                        </div>
                                                        <div class="tab-pane" id="aboutus-content-b1">
                                                            <div class="mb-1">
                                                                <textarea id="est_aboutus_content"
                                                                          name="est_aboutus_content"
                                                                          data-toggle="maxlength"
                                                                          class="form-control"
                                                                          rows="3"
                                                                          placeholder="This textarea has a limit of 2048 chars.">{!! html_entity_decode($estimate->est_aboutus_content, ENT_QUOTES, 'UTF-8')!!}
                                                                </textarea>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 text-end">
                                                            <button class="btn btn-primary estimate_button"
                                                                    type="submit"><i
                                                                    class="uil-arrow-circle-right"></i> Revise Estimate
                                                            </button>
                                                            <a href="{{route('quotes.index')}}" type="button"
                                                               class="btn btn-light">Close</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-0">
                                            <div class="card-header" id="headingSeven">
                                                <h5 class="m-0">
                                                    <div class="form-check form-checkbox-dark">
                                                        <input type="checkbox" class="form-check-input" id="pdf_est_flg"
                                                               name="pdf_est_flg" @if($estimate->pdf_est_flg){{'checked'}} @endif disabled>
                                                        <a class="custom-accordion-title collapsed d-block py-1"
                                                           data-bs-toggle="collapse" href="#collapseSeven"
                                                           aria-expanded="false" aria-controls="collapseSeven">
                                                            Estimate <i
                                                                class="mdi mdi-chevron-down accordion-arrow"></i>
                                                        </a>
                                                    </div>
                                                </h5>
                                            </div>
                                            <div id="collapseSeven" class="collapse tab-validation show"
                                                 aria-labelledby="headingSeven"
                                                 data-bs-parent="#custom-accordion-one">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                    class="mdi mdi-office-building me-1"></i>
                                                                Customer Info</h5>
                                                            @php
                                                                $adreessStr = '';
                                                                if (count($customers) > 0){
                                                                    if ($customers[0]['address'] && $customers[0]['address'] != null) {
                                                                        $adreessStr .= $customers[0]['address'].',';
                                                                    }
                                                                    if ($customers[0]['city_name'] && $customers[0]['city_name'] != null) {
                                                                        $adreessStr .= $customers[0]['city_name'].',';
                                                                    }
                                                                    if ($customers[0]['state_name'] && $customers[0]['state_name'] != null) {
                                                                        $adreessStr .= $customers[0]['state_name'].',';
                                                                    }
                                                                    if ($customers[0]['pincode'] && $customers[0]['pincode'] != null) {
                                                                        $adreessStr .= $customers[0]['pincode'].',';
                                                                    }
                                                                    if ($customers[0]['country_name'] && $customers[0]['country_name'] != null) {
                                                                        $adreessStr .= $customers[0]['country_name'].',';
                                                                    }
                                                                    $adreessStr .= "\r\nMobile :" .$customers[0]['phone_no'];
                                                                }
                                                            @endphp
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label for="customer_name" class="form-label"> <a
                                                                            href="#"
                                                                            id="customer_info"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-html="true"
                                                                            title="{!! $adreessStr !!}"><i
                                                                                class="mdi mdi-information"></i></a>
                                                                        Customer
                                                                        Name <span class="text-primary customer_currency" style="display:none;">  @php
                                                                                $currency_name = '';
                                                                                if (count($customers) > 0){
                                                                                    if ($customers[0]['currency_name']) {
                                                                                        $currency_name = $customers[0]['currency_name'];
                                                                                    }

                                                                                }
                                                                            @endphp {{--({!! $country_data->currency_code !!})--}}</span> <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="mb-1">
                                                                        <input type="text" name="customer_name"
                                                                               class="form-control search_box customer_autocomplete"
                                                                               data-type="customers" id="customer_name"
                                                                               placeholder="Search and Add customers"
                                                                               onkeyup="javascript:customer();"
                                                                               value="{{($customers && $customers[0]['name'])?$customers[0]['name']:''}}"
                                                                               required readonly>
                                                                        <input type="hidden" name="customer_id"
                                                                               class="form-control"
                                                                               id="customer_id"
                                                                               value="{{$estimate->customer_id}}">
                                                                        <input type="hidden" name="customer_state_id"
                                                                               class="form-control customer_state_id"
                                                                               id="customer_state_id"
                                                                               value="{{($customers && $customers[0]['state_id'])?$customers[0]['state_id']:0}}"
                                                                               data-id="{{Auth::user()->state_id}}">
                                                                        <input type="hidden" name="id"
                                                                               class="form-control"
                                                                               id="ids" value="{{$estimate->id}}">
                                                                        <input type="hidden" name="customer_address"
                                                                               class="form-control"
                                                                               id="customer_address"
                                                                               value="{!! $adreessStr !!}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="customer_name" class="form-label">Item
                                                                        Rates Are (GST)</label>
                                                                    <div class="mb-1">
                                                                        <select class="form-select item_rate_are"
                                                                                id="item_rate_are"
                                                                                name="item_rate_are">
                                                                            <option
                                                                                value="1" {{($estimate->item_rate_are==1)?'selected': ''}}>
                                                                                Tax
                                                                                Exclusive
                                                                            </option>
                                                                            <option
                                                                                value="2" {{($estimate->item_rate_are==2)?'selected': ''}}>
                                                                                Tax
                                                                                Inclusive
                                                                            </option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="customer_name" class="form-label">Currency</label>
                                                                    <div class="mb-1" id="sel_cn">
                                                                        <select class="text-left form-select select2"
                                                                                id="est_currency_id"
                                                                                name="est_currency_id"
                                                                                data-toggle="select2">
                                                                            @php
                                                                                $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                                            @endphp

                                                                            @foreach($countries as $country)
                                                                                <option class="text-left" value="{{$country->currency_code}}"
                                                                                        data-id="{{$country->id}}" data-type="{{$country->currency_symbol}}"
                                                                                        @if($country->id==$estimate->est_currency_id) selected @endif>
                                                                                    {{$country->currency_code}}
                                                                                    - {{$country->currency_name}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 d-none">
                                                                    <label for="customer_name" class="form-label">Assign
                                                                        to</label>
                                                                    <div class="mb-1">
                                                                        @if($user_list)
                                                                            <select class="form-select" name="user_id"
                                                                                    id="user_id"
                                                                                    required>
                                                                                <option value="0">Choose a Assign to
                                                                                </option>
                                                                                @foreach($user_list as $user_list)
                                                                                    <option
                                                                                        value="{{$user_list->id}}"
                                                                                        @if($user_list->id==$estimate->user_id)selected @endif>{{$user_list->name}}
                                                                                        - ({{$user_list->email}})
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        @else
                                                                            <input type="text" name="user_name"
                                                                                   class="form-control"
                                                                                   id="user_name"
                                                                                   value="{{Auth::user()->name.' - '.Auth::user()->email}}"
                                                                                   readonly>
                                                                            <input type="hidden" name="user_id"
                                                                                   class="form-control"
                                                                                   id="user_id"
                                                                                   value="{{$estimate->user_id}}">
                                                                        @endif

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                    class="mdi mdi-office-building me-1"></i>
                                                                Estimate Info</h5>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="estimate_no" class="form-label"><a
                                                                            href="JavaScript:void(0);"
                                                                            id="autogenetare-estimate"
                                                                            class="autogenetare-estimate"
                                                                            onclick="get_estimate_number();"
                                                                            data-bs-toggle="tooltip"
                                                                            aria-label="Click here to enable or disable auto-generation of Estimate numbers."
                                                                            data-bs-html="true"
                                                                            data-bs-original-title="Click here to enable or disable auto-generation of Estimate numbers."><i
                                                                                class="dripicons-gear noti-icon mdi-18px"></i></a>
                                                                        Estimate# <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="mb-1">
                                                                        <input type="text" name="estimate_no"
                                                                               class="form-control" id="estimate_no"
                                                                               placeholder="Estimate no"
                                                                               value="{{$estimate->estimate_no}}"
                                                                               required data-parsley-pattern="^[a-zA-Z0-9\-]+$" data-parsley-pattern-message="Only letters, numbers, and hyphens (dashes) are accepted." data-parsley-trigger="input" readonly>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label for="reference"
                                                                           class="form-label">Reference# </label>
                                                                    <div class="mb-1">
                                                                        <input type="text" name="reference"
                                                                               class="form-control" id="reference"
                                                                               placeholder="Reference"
                                                                               value="{{$estimate->reference}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label for="estimate_date" class="form-label">Estimate
                                                                        Date <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="mb-1">
                                                                        <div class="input-group">
                                                                            <input type="text"
                                                                                   class="form-control form-control-light"
                                                                                   id="estimate_date"
                                                                                   name="estimate_date"
                                                                                   data-provide="datepicker"
                                                                                   data-single-date-picker="true"
                                                                                   data-date-autoclose="true"
                                                                                   data-date-format="d/m/yyyy"
                                                                                   value="{{\Carbon\Carbon::parse($estimate->estimate_date)->format('d/m/Y')}}">
                                                                            {{--<span
                                                                                class="input-group-text bg-primary border-primary text-white">
                                                    <i class="mdi mdi-calendar-range font-13"></i>
                                                </span>--}}
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label for="expiry_date" class="form-label">Expiry
                                                                        Date <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="mb-1">
                                                                        <div class="input-group">
                                                                            <input type="text" name="expiry_date"
                                                                                   class="form-control form-control-light"
                                                                                   id="expiry_date"
                                                                                   data-provide="datepicker"
                                                                                   data-single-date-picker="true"
                                                                                   data-date-autoclose="true"
                                                                                   data-date-format="d/m/yyyy"
                                                                                   value="{{date('d/m/Y', strtotime("+5 days"))}}">
                                                                            {{--<span
                                                                                class="input-group-text bg-primary border-primary text-white">
                                            <i class="mdi mdi-calendar-range font-13"></i>
                                        </span>--}}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div>

                                                        <div class="col-md-4 d-none">
                                                            <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                    class="mdi mdi-office-building me-1"></i>
                                                                Project Info</h5>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="estimate_no"
                                                                           class="form-label">Tilt</label>
                                                                    <div class="mb-1">
                                                                        <input type="text" name="tilt"
                                                                               class="form-control" id="tilt"
                                                                               placeholder="Tilt"
                                                                               value="{{$estimate->tilt}}"
                                                                               data-parsley-type="number"
                                                                               data-parsley-trigger="input">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label for="reference"
                                                                           class="form-label">Azumuth</label>
                                                                    <div class="mb-1">
                                                                        <select class="form-select" id="azumuth"
                                                                                name="azumuth">
                                                                            <option value="">Choose a Azumuth</option>
                                                                            <option
                                                                                value="N" {{($estimate->azumuth=='N')?'selected': ''}}>
                                                                                Nourth
                                                                            </option>
                                                                            <option
                                                                                value="S" {{($estimate->azumuth=='S')?'selected': ''}}>
                                                                                South
                                                                            </option>
                                                                            <option
                                                                                value="E" {{($estimate->azumuth=='E')?'selected': ''}}>
                                                                                East
                                                                            </option>
                                                                            <option
                                                                                value="W" {{($estimate->azumuth=='W')?'selected': ''}}>
                                                                                West
                                                                            </option>
                                                                            <option
                                                                                value="NE" {{($estimate->azumuth=='NE')?'selected': ''}}>
                                                                                Nourth-East
                                                                            </option>
                                                                            <option
                                                                                value="SE" {{($estimate->azumuth=='SE')?'selected': ''}}>
                                                                                South-East
                                                                            </option>
                                                                            <option
                                                                                value="SW" {{($estimate->azumuth=='SW')?'selected': ''}}>
                                                                                South-West
                                                                            </option>
                                                                            <option
                                                                                value="NW" {{($estimate->azumuth=='NW')?'selected': ''}}>
                                                                                Nourth-West
                                                                            </option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label for="estimate_date" class="form-label">No. of
                                                                        Panel</label>
                                                                    <div class="mb-1">
                                                                        <input type="text" class="form-control"
                                                                               id="no_of_panel" name="no_of_panel"
                                                                               placeholder="No. of panel"
                                                                               value="{{$estimate->no_of_panel}}"
                                                                               data-parsley-type="number"
                                                                               data-parsley-trigger="input">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label for="estimate_date" class="form-label">Panel
                                                                        Wattage</label>
                                                                    <div class="mb-1">
                                                                        <input type="text" class="form-control"
                                                                               id="panel_wattage" name="panel_wattage"
                                                                               placeholder="Panel wattage"
                                                                               value="{{$estimate->panel_wattage}}"
                                                                               data-parsley-type="number"
                                                                               data-parsley-trigger="input">
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end row -->
                                                            <!-- end card -->
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <h5 class="mb-1 text-uppercase bg-light p-2"><i
                                                                    class="mdi mdi-office-building me-1"></i>Item
                                                                Info </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-nowrap mb-0 table-sm">
                                                                    <thead class="table-light">
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th style="min-width:300px">Name</th>
                                                                        <th style="max-width:30px">Qty</th>
                                                                        <th style="min-width:60px">Rate</th>
                                                                        <th style="min-width:100px">{!! $proposal_template->item_table_discount !!}</th>
                                                                        <th style="min-width:150px">Tax(GST)</th>
                                                                        <th>Amount</th>
                                                                        <th>#</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody class="itemRow">

                                                                    @foreach($estimate_items as $key =>$estimate_item)

                                                                        <tr id="{{ ++$key }}">
                                                                            <td>{{ $key }}</td>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control item_autocomplete"
                                                                                       onkeyup="javascript:item({{ $key }})"
                                                                                       name="data[{{ $key }}][item_name]"
                                                                                       id="item_name_{{ $key }}"
                                                                                       data-type="itemName"
                                                                                       placeholder="Click to select item"
                                                                                       required=""
                                                                                       value="{{$estimate_item->item_name}}">
                                                                                <input type="hidden"
                                                                                       class="form-control"
                                                                                       id="item_id_{{ $key }}"
                                                                                       name="data[{{ $key }}][item_id]"
                                                                                       value="{{$estimate_item->item_id}}">
                                                                                <input type="hidden"
                                                                                       class="form-control"
                                                                                       id="hsn_code_{{ $key }}"
                                                                                       name="data[{{ $key }}][hsn_code]"
                                                                                       value="{{$estimate_item->hsn_code}}">
                                                                                <textarea
                                                                                    class="form-control item_description bg-light"
                                                                                    id="item_description_{{ $key }}"
                                                                                    name="data[{{ $key }}][item_description]"
                                                                                    placeholder="Add a description to your item"
                                                                                    style="{{($estimate_item->item_description)? '': 'display: none;'}}">{{$estimate_item->item_description}}</textarea>
                                                                                <textarea
                                                                                    class="form-control item_technical_specification bg-light d-none"
                                                                                    id="item_technical_specification_{{ $key }}"
                                                                                    name="data[{{ $key }}][item_technical_specification]"
                                                                                    placeholder="Add a technical specification to your item"
                                                                                    style="display:none;">{{$estimate_item->technical_specification}}</textarea>
                                                                                <span
                                                                                    class="form-control item_description_span text-truncate bg-light"
                                                                                    id="item_description_span_1"
                                                                                    style="display: none;">{!! nl2br($estimate_item->item_description  )!!}</span>
                                                                                <a href="javascript:void(0);" id="update_technical_specification_{{ $key }}" class="fw-bolder update_technical_specification" title="Item Specification" style="vertical-align: middle!important;" data-id="{{ $key }}">
                                                                                    <i class="mdi mdi-lead-pencil mdi-16px text-primary"></i></a>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control quantity"
                                                                                       name="data[{{ $key }}][quantity]"
                                                                                       id="quantity_{{ $key }}"
                                                                                       placeholder="Quantity"
                                                                                       required=""
                                                                                       value="{{$estimate_item->quantity}}">
                                                                                <span
                                                                                    class="font-13 text-muted units_span_{{ $key }}">{{$estimate_item->unit_name}}</span>
                                                                                <input type="hidden"
                                                                                       class="form-control unit_name"
                                                                                       name="data[{{ $key }}][unit_name]"
                                                                                       id="unit_name_{{ $key }}"
                                                                                       placeholder="Unit" required=""
                                                                                       value="{{$estimate_item->unit_name}}">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control price"
                                                                                       name="data[{{ $key }}][price]"
                                                                                       id="price_{{ $key }}"
                                                                                       placeholder="Rate" required=""
                                                                                       value="{{$estimate_item->price}}">
                                                                            </td>
                                                                            <td class="input-group">
                                                                                {{--                                                                        <div class="input-group">--}}
                                                                                <input type="text"
                                                                                       class="form-control discount"
                                                                                       name="data[{{ $key }}][discount]"
                                                                                       id="discount_{{ $key }}"
                                                                                       placeholder="Discount"
                                                                                       required=""
                                                                                       value="{{$estimate_item->discount}}">
                                                                                <select class="btn-light discount_flag"
                                                                                        id="discount_flag_{{ $key }}"
                                                                                        name="data[{{ $key }}][discount_flag]">
                                                                                    <option
                                                                                        value="2" {{($estimate_item->discount_flag==2)?'selected': ''}}>
                                                                                        {{$country_data->currency_symbol}}
                                                                                    </option>
                                                                                    <option
                                                                                        value="1" {{($estimate_item->discount_flag==1)?'selected': ''}}>
                                                                                        %
                                                                                    </option>
                                                                                </select>
                                                                                {{--                                                                        </div>--}}
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-select gst_per"
                                                                                        id="gst_per_{{ $key }}"
                                                                                        name="data[{{ $key }}][gst_per]">
                                                                                    <option value="0">GST0 [0%]</option>
                                                                                    @foreach($taxes as $tax)
                                                                                        <option
                                                                                            value="{{$tax->name}}" {{($estimate_item->gst_per==$tax->name)?'selected': ''}}>
                                                                                            GST{{$tax->name}}
                                                                                            [{{$tax->name}}%]
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <input type="hidden"
                                                                                       class="form-control cgst_amount"
                                                                                       name="data[{{ $key }}][cgst_amount]"
                                                                                       id="cgst_amount_{{ $key }}"
                                                                                       value="{{$estimate_item->sgst_amount}}">
                                                                                <input type="hidden"
                                                                                       class="form-control sgst_amount"
                                                                                       name="data[{{ $key }}][sgst_amount]"
                                                                                       id="sgst_amount_{{ $key }}"
                                                                                       value="{{$estimate_item->sgst_amount}}">
                                                                                <input type="hidden"
                                                                                       class="form-control igst_amount"
                                                                                       name="data[{{ $key }}][igst_amount]"
                                                                                       id="igst_amount_{{ $key }}"
                                                                                       value="{{$estimate_item->igst_amount}}">
                                                                            </td>
                                                                            <td><input type="text"
                                                                                       class="form-control total"
                                                                                       name="data[{{ $key }}][total]"
                                                                                       id="total_{{ $key }}"
                                                                                       placeholder="Total" required=""
                                                                                       readonly
                                                                                       value="{{$estimate_item->total}}">
                                                                            </td>
                                                                            <td>
                                                                                <a href="JavaScript:void(0);"
                                                                                   id="quotation_{{ $key }}"
                                                                                   class="text-danger remove"><i
                                                                                        class="mdi mdi-trash-can-outline mdi-18px"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="row pt-2">
                                                                <div class="col-md-6">
                                                                    <a href="javascript:void(0);" id="add"
                                                                       class="btn btn-light btn-sm fw-bolder"
                                                                       style="vertical-align: middle!important;">
                                                                        <i class="mdi mdi-plus-circle mdi-16px text-primary"></i>Add
                                                                        another line</a>
                                                                    <div class="pb-2 pt-2">
                                                                        <label for="description" class="form-label">Customer
                                                                            Notes</label>
                                                                        <textarea class="form-control"
                                                                                  id="customer_notes"
                                                                                  name="customer_notes"
                                                                                  data-toggle="maxlength"
                                                                                  maxlength="2048"
                                                                                  placeholder="Will be displayed on the estimate">{{$estimate->customer_notes}}</textarea>
                                                                    </div>

                                                                </div>
                                                                <div class="col-md-6">
                                                                    <table
                                                                        class="table table-nowrap table-borderless table-light mb-0 table-sm">

                                                                        <tr>
                                                                            <th>Sub Total</th>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control subtotal d-none"
                                                                                       name="subtotal"
                                                                                       id="subtotal" required=""
                                                                                       readonly
                                                                                       value="{{$estimate->subtotal}}">
                                                                                <span
                                                                                    class="subtotal_span">{{$estimate->subtotal}}</span>
                                                                            </td>
                                                                        </tr>

                                                                        <tr style="{{($estimate->customer_state_id==Auth::user()->state_id)?'': 'display: none'}}">
                                                                            <th>CGST</th>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control total_cgst_amount d-none"
                                                                                       name="total_cgst_amount"
                                                                                       id="total_cgst_amount"
                                                                                       required=""
                                                                                       readonly
                                                                                       value="{{$estimate->total_cgst_amount}}">
                                                                                <span
                                                                                    class="total_cgst_amount_span">{{$estimate->total_cgst_amount}}</span>
                                                                            </td>
                                                                        </tr>

                                                                        <tr style="{{($estimate->customer_state_id==Auth::user()->state_id)?'': 'display: none'}}">
                                                                            <th>SGST</th>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control total_sgst_amount d-none"
                                                                                       name="total_sgst_amount"
                                                                                       id="total_sgst_amount"
                                                                                       required=""
                                                                                       readonly
                                                                                       value="{{$estimate->total_sgst_amount}}">
                                                                                <span
                                                                                    class="total_sgst_amount_span">{{$estimate->total_sgst_amount}}</span>
                                                                            </td>
                                                                        </tr>

                                                                        <tr style="{{($estimate->customer_state_id==Auth::user()->state_id)?'display: none': ''}}">
                                                                            <th>IGST</th>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control total_igst_amount d-none"
                                                                                       name="total_igst_amount"
                                                                                       id="total_igst_amount"
                                                                                       required=""
                                                                                       readonly
                                                                                       value="{{$estimate->total_igst_amount}}">
                                                                                <span
                                                                                    class="total_igst_amount_span">{{$estimate->total_igst_amount}}</span>
                                                                            </td>
                                                                        </tr>

                                                                        <tr>
                                                                            <th class="input-group">
                                                                                <input type="text"
                                                                                       class="form-control addless_title"
                                                                                       name="addless_title"
                                                                                       id="addless_title"
                                                                                       value="{{$estimate->addless_title}}"
                                                                                       style="padding-right: 15px; ">
                                                                                <input type="text"
                                                                                       class="form-control addless_amount"
                                                                                       name="addless_amount"
                                                                                       id="addless_amount"
                                                                                       value="{{$estimate->addless_amount}}">
                                                                            </th>
                                                                            <td><span
                                                                                    class="addless_amount_span">{{$estimate->addless_amount}}</span>
                                                                            </td>
                                                                        </tr>

                                                                        <tr>
                                                                            <th>Total <span class="text-primary customer_currency"> ({!! $country_data->currency_code !!})</span>
                                                                            </th>
                                                                            <td>
                                                                                <input type="text"
                                                                                       class="form-control net_amount d-none"
                                                                                       name="net_amount"
                                                                                       id="net_amount" required=""
                                                                                       readonly
                                                                                       value="{{$estimate->net_amount}}">
                                                                                <span
                                                                                    class="net_amount_span">{{$estimate->net_amount}}</span>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>

                                                                <div class="col-md-6">

                                                                    <div class="pb-3 d-none">
                                                                        <label for="description" class="form-label">Terms
                                                                            & Conditions</label>
                                                                        <textarea class="form-control term_condition"
                                                                                  id="term_condition"
                                                                                  name="term_condition"
                                                                                  data-toggle="maxlength"
                                                                                  maxlength="2048"
                                                                                  placeholder="Enter the terms and conditions of your business to be displayed in your transaction">{{$estimate->term_condition}}</textarea>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                            {{-- <div class="mb-1 text-end">
                                                                 <button type="button" class="btn btn-light"
                                                                         data-bs-dismiss="modal">Close
                                                                 </button>
                                                                 <button class="btn btn-primary" id="item_button"
                                                                         form="estimate-form" type="submit"><i
                                                                         class="uil-arrow-circle-right"></i> Save
                                                                 </button>
                                                             </div>--}}

                                                            <div class="mt-3 text-end">
                                                                <button class="btn btn-primary estimate_button"
                                                                        type="submit"><i
                                                                        class="uil-arrow-circle-right"></i> Revise
                                                                    Estimate
                                                                </button>
                                                                <a href="{{route('quotes.index')}}" type="button"
                                                                   class="btn btn-light">Close</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-0">
                                            <div class="card-header" id="headingSix">
                                                <h5 class="m-0">
                                                    <div class="form-check form-checkbox-dark">
                                                        <input type="checkbox" class="form-check-input"
                                                               id="pdf_product_flg"
                                                               name="pdf_product_flg" @if($estimate->pdf_product_flg){{'checked'}} @endif>
                                                        <a class="custom-accordion-title collapsed d-block py-1"
                                                           data-bs-toggle="collapse" href="#collapseSix"
                                                           aria-expanded="false" aria-controls="collapseSix">
                                                            Select Photos<i
                                                                class="mdi mdi-chevron-down accordion-arrow"></i>
                                                        </a>
                                                    </div>
                                                </h5>
                                            </div>

                                            <div id="collapseSix" class="collapse tab-validation"
                                                 aria-labelledby="headingSix"
                                                 data-bs-parent="#custom-accordion-one">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-nowrap mb-0 table-sm">
                                                            <tbody class="productRow">
                                                            @if(count($products) > 0)
                                                                @foreach($products as $pkey => $product)
                                                                    <tr id="{{ ++$pkey }}">
                                                                        <td>
                                                                            <input type="text"
                                                                                   class="form-control product_autocomplete"
                                                                                   onkeyup="javascript:product({{$pkey}})"
                                                                                   name="product_name[]"
                                                                                   id="product_name_{{$pkey}}"
                                                                                   data-type="productName"
                                                                                   placeholder="Click and add photos"
                                                                                   value="{{$product->name}}" @if($estimate->pdf_product_flg){{'required'}} @endif>
                                                                            <input type="hidden" class="form-control"
                                                                                   id="product_id_{{$pkey}}"
                                                                                   name="product_id[]"
                                                                                   value="{{$product->id}}">
                                                                        </td>
                                                                        {{--<td><img class="img-fluid img-thumbnail rounded"
                                                                                 src="{{Storage::url(trim($product->image_one))}}"
                                                                                 width="100"></td>
                                                                        <td><img class="img-fluid img-thumbnail rounded"
                                                                                 src="{{($product->image_two)?Storage::url(trim($product->image_two)):Storage::url('64x64.png')}}"
                                                                                 width="100"></td>
                                                                        <td><img class="img-fluid img-thumbnail rounded"
                                                                                 src="{{($product->image_three)?Storage::url(trim($product->image_three)):Storage::url('64x64.png')}}"
                                                                                 width="100"></td>--}}
                                                                        <td>
                                                                            <input type="checkbox" class="form-check-input me-1" id="product_flg_{{$pkey}}" name="product_flag"><span style="position: relative;right: 1.4rem;"><label class="form-check-label text-dark me-1" title="Click to Add photo in library"><i class="mdi mdi-information"></i></label></span><input type="hidden" class="form-control me-1" id="tmp_product_id_{{$pkey}}1" name="image_data[{{$pkey}}][tmp_product_id]" value="{{$product->id}}"><input type="hidden" class="form-control me-1" id="estimate_product_id_{{$pkey}}1" name="image_data[{{$pkey}}][estimate_product_id]" value="{{$product->estimate_product_id}}">
                                                                            <img id="img_thumbnail_{{$pkey}}1" class="img-fluid img-thumbnail rounded"
                                                                                 src="{{($product->image_one)?Storage::disk('s3')->temporaryUrl(trim($product->image_one),Carbon\Carbon::now()->addMinutes(20)):Storage::url('64x64.png');}}"
                                                                                 width="100">
                                                                            <a class="photo-upload text-black" style="position: relative;top: -1.8rem;left: -1.2rem;" data-id="{{$pkey}}1"><input type="file" class="file-input w-100" id="one_photo_{{$pkey}}1" data-type="1"><i class="mdi mdi-pencil-circle fs-4"></i></a><input class="form-control" type="hidden" id="h_image_{{$pkey}}1" name="image_data[{{$pkey}}][h_image_one]" value="">
                                                                        </td>
                                                                        <td><img id="img_thumbnail_{{$pkey}}2" class="img-fluid img-thumbnail rounded"
                                                                                 src="{{($product->image_two)?Storage::disk('s3')->temporaryUrl(trim($product->image_two),Carbon\Carbon::now()->addMinutes(20)):Storage::url('64x64.png');}}"
                                                                                 width="100">
                                                                            <a class="photo-upload text-black" style="position: relative;top: -1.8rem;left: -1.2rem;" data-id="{{$pkey}}2"><input type="file" class="file-input-small w-100" id="one_photo_{{$pkey}}2" data-type="2"><i class="mdi mdi-pencil-circle fs-4"></i></a><input class="form-control" type="hidden" id="h_image_{{$pkey}}2" name="image_data[{{$pkey}}][h_image_two]" value=""></td>
                                                                        <td><img id="img_thumbnail_{{$pkey}}3" class="img-fluid img-thumbnail rounded"
                                                                                 src="{{($product->image_three)?Storage::disk('s3')->temporaryUrl(trim($product->image_three),Carbon\Carbon::now()->addMinutes(20)):Storage::url('64x64.png');}}"
                                                                                 width="100">
                                                                            <a class="photo-upload text-black" style="position: relative;top: -1.8rem;left: -1.2rem;" data-id="{{$pkey}}3"><input type="file" class="file-input-small w-100" id="one_photo_{{$pkey}}3" data-type="3"><i class="mdi mdi-pencil-circle fs-4"></i></a><input class="form-control" type="hidden" id="h_image_{{$pkey}}3" name="image_data[{{$pkey}}][h_image_three]" value=""></td>

                                                                        <td>
                                                                            <a href="JavaScript:void(0);"
                                                                               id="product_{{$pkey}}"
                                                                               class="text-danger product_remove {{($pkey==1)?'d-nones':'';}}"><i
                                                                                    class="mdi mdi-trash-can-outline mdi-18px"></i></a>
                                                                        </td>
                                                                    </tr>

                                                                @endforeach
                                                            @else
                                                                <tr id="1">
                                                                    <td>
                                                                        <input type="text"
                                                                               class="form-control product_autocomplete"
                                                                               onkeyup="javascript:product(1)"
                                                                               name="product_name[]"
                                                                               id="product_name_1"
                                                                               data-type="productName"
                                                                               placeholder="Click and add photos"
                                                                               value="">
                                                                        <input type="hidden" class="form-control"
                                                                               id="product_id_1"
                                                                               name="product_id[]"
                                                                               value="">
                                                                    </td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>

                                                                    <td>
                                                                        <a href="JavaScript:void(0);"
                                                                           id="product_1"
                                                                           class="text-danger product_remove d-nones"><i
                                                                                class="mdi mdi-trash-can-outline mdi-18px"></i></a>
                                                                    </td>
                                                                </tr>
                                                            @endif

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="row pt-2">
                                                        <div class="col-md-6">
                                                            <a href="javascript:void(0);" id="product_add"
                                                               class="text-secondary"
                                                               style="vertical-align: middle!important;">
                                                                <i class="mdi mdi-plus-circle-outline mdi-18px"></i> Add
                                                                another
                                                                line
                                                            </a>

                                                        </div>
                                                    </div>

                                                    <div class="mt-3 text-end">
                                                        <button class="btn btn-primary estimate_button"
                                                                type="submit"><i
                                                                class="uil-arrow-circle-right"></i> Revise Estimate
                                                        </button>
                                                        <a href="{{route('quotes.index')}}" type="button"
                                                           class="btn btn-light">Close</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-0">
                                            <div class="card-header" id="headingEight">
                                                <h5 class="m-0">
                                                    <div class="form-check form-checkbox-dark">
                                                        <input type="checkbox" class="form-check-input"
                                                               id="pdf_terms_flg"
                                                               name="pdf_terms_flg" @if($estimate->pdf_terms_flg){{'checked'}} @endif>
                                                        <a class="custom-accordion-title collapsed d-block py-1"
                                                           data-bs-toggle="collapse" href="#collapseEight"
                                                           aria-expanded="false" aria-controls="collapseEight">
                                                            Select Terms & Conditions <i
                                                                class="mdi mdi-chevron-down accordion-arrow"></i>
                                                        </a>
                                                    </div>
                                                </h5>
                                            </div>
                                            <div id="collapseEight" class="collapse tab-validation"
                                                 aria-labelledby="headingEight"
                                                 data-bs-parent="#custom-accordion-one">
                                                <div class="card-body">

                                                    <ul class="nav nav-tabs nav-bordered mb-1 d-none">
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
                                                        <div class="tab-pane" id="terms-title-b1">
                                                    <textarea id="est_term_condition_title"
                                                              name="est_term_condition_title"
                                                              data-toggle="maxlength"
                                                              class="form-control" maxlength="2048"
                                                              rows="3"
                                                              placeholder="This textarea has a limit of 2048 chars.">{!! html_entity_decode($proposal_template->terms_title, ENT_QUOTES, 'UTF-8')!!}
                                                        {{--                                                        {!! html_entity_decode($estimate->est_term_condition_title, ENT_QUOTES, 'UTF-8')!!}--}}
                                                    </textarea>
                                                        </div>
                                                        <div class="tab-pane show active" id="terms-content-b1">
                                                            <div class="mb-1">
                                                                <div class="col-md-4">
                                                                    <select class="form-select" id="term_condition_id"
                                                                            name="term_condition_id" @if($estimate->pdf_terms_flg){{'required'}} @endif>
                                                                        <option value="">Choose</option>
                                                                        @foreach($termConditionDatas as $termConditionData)
                                                                            <option
                                                                                value="{{$termConditionData->id}}" {{($termConditionData->id==$estimate->term_condition_id)?"selected":""}}>{{$termConditionData->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-1">
                                                        <textarea id="est_term_condition_content"
                                                                  name="est_term_condition_content"
                                                                  data-toggle="maxlength"
                                                                  class="form-control"
                                                                  rows="3"
                                                                  placeholder="This textarea has a limit of 9216 chars.">{!! html_entity_decode($estimate->est_term_condition_content, ENT_QUOTES, 'UTF-8')!!}
                                                        </textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mt-3 text-end">
                                                            <button class="btn btn-primary estimate_button"
                                                                    type="submit"><i
                                                                    class="uil-arrow-circle-right"></i> Revise Estimate
                                                            </button>
                                                            <a href="{{route('quotes.index')}}" type="button"
                                                               class="btn btn-light">Close</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-0">
                                            <div class="card-header" id="headingNine">
                                                <h5 class="m-0">
                                                    <div class="form-check form-checkbox-dark">
                                                        <input type="checkbox" class="form-check-input"
                                                               id="pdf_testimonial_flg"
                                                               name="pdf_testimonial_flg" @if($estimate->pdf_testimonial_flg){{'checked'}} @endif>
                                                        <a class="custom-accordion-title collapsed d-block py-1"
                                                           data-bs-toggle="collapse" href="#collapseNine"
                                                           aria-expanded="false" aria-controls="collapseNine">
                                                            Select Testimonials<i
                                                                class="mdi mdi-chevron-down accordion-arrow"></i>
                                                        </a>
                                                    </div>
                                                </h5>
                                            </div>
                                            <div id="collapseNine" class="collapse tab-validation"
                                                 aria-labelledby="headingNine"
                                                 data-bs-parent="#custom-accordion-one">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-nowrap mb-0 table-sm">
                                                            <tbody class="testimonialRow">
                                                            <tr id="1">
                                                                <td>
                                                                    <input type="text"
                                                                           class="form-control testimonial_autocomplete"
                                                                           onkeyup="javascript:testimonial(1)"
                                                                           name="testimonial_name"
                                                                           id="testimonial_name_1"
                                                                           data-type="testimonialName"
                                                                           placeholder="Search and Add testimonial"
                                                                           @if($estimate->pdf_testimonial_flg){{'required'}} @endif
                                                                           value="{{($testimonials)?$testimonials->name:''}}">
                                                                    <input type="hidden" class="form-control"
                                                                           id="testimonial_id_1"
                                                                           name="testimonial_id"
                                                                           value="{{($testimonials)?$testimonials->id:0}}">
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <div class="flex-shrink-0">
                                                                            <img class="rounded-circle avatar-sm"
                                                                                 src="{{($testimonials && $testimonials->image_one)?Storage::disk('s3')->temporaryUrl(trim($testimonials->image_one),Carbon\Carbon::now()->addMinutes(20)):null;}}"
                                                                                 alt="Avtar image">
                                                                            {{--<img class="rounded-circle avatar-sm"
                                                                                 src="{{Storage::url(trim($testimonials->image_one))}}"
                                                                                 alt="Avtar image">--}}
                                                                        </div>
                                                                        <div class="flex-grow-1 ms-2">
                                                                            <a class="text-secondary"><h5 class="my-1">
                                                                                    {{($testimonials)?$testimonials->client_name_one:''}}</h5>
                                                                            </a>
                                                                            <p class="text-muted mb-0">{{($testimonials)?$testimonials->rating_one:''}}
                                                                                <span
                                                                                    class="text-warning mdi mdi-star"></span>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <div class="flex-shrink-0">
                                                                            <img class="rounded-circle avatar-sm"
                                                                                 src="{{($testimonials && $testimonials->image_two)?Storage::disk('s3')->temporaryUrl(trim($testimonials->image_two),Carbon\Carbon::now()->addMinutes(20)):null;}}"
                                                                                 alt="Avtar image">
                                                                            {{--<img class="rounded-circle avatar-sm"
                                                                                 src="{{Storage::url(trim($testimonials->image_two))}}"
                                                                                 alt="Avtar image">--}}
                                                                        </div>
                                                                        <div class="flex-grow-1 ms-2">
                                                                            <a class="text-secondary"><h5 class="my-1">
                                                                                    {{($testimonials)?$testimonials->client_name_two:''}}</h5>
                                                                            </a>
                                                                            <p class="text-muted mb-0">{{($testimonials)?$testimonials->rating_two:''}}
                                                                                <span
                                                                                    class="text-warning mdi mdi-star"></span>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <div class="flex-shrink-0">
                                                                            <img class="rounded-circle avatar-sm"
                                                                                 src="{{($testimonials && $testimonials->image_three)?Storage::disk('s3')->temporaryUrl(trim($testimonials->image_three),Carbon\Carbon::now()->addMinutes(20)):null;}}"
                                                                                 alt="Avtar image">
                                                                           {{-- <img class="rounded-circle avatar-sm"
                                                                                src="{{Storage::url($testimonials->image_three)}}"
                                                                                style="width:100%">--}}
                                                                        </div>
                                                                        <div class="flex-grow-1 ms-2">
                                                                            <a class="text-secondary"><h5 class="my-1">
                                                                                    {{($testimonials)?$testimonials->client_name_three:""}}</h5>
                                                                            </a>
                                                                            <p class="text-muted mb-0">{{($testimonials)?$testimonials->rating_three:''}}
                                                                                <span
                                                                                    class="text-warning mdi mdi-star"></span>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                            </tr>

                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-12 mt-3 text-end">
                                                        <button class="btn btn-primary estimate_button"
                                                                type="submit"><i
                                                                class="uil-arrow-circle-right"></i> Revise Estimate
                                                        </button>
                                                        <a href="{{route('quotes.index')}}" type="button"
                                                           class="btn btn-light">Close</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane @if(!Request::segment(4) || $estimate->status =='') show active @endif" id="preview">
                                    {{--                                    <div class="card mb-0">--}}

                                    @php
                                        $url_components = parse_url($_SERVER['REQUEST_URI']);
                                        $m=0;
                                        if(isset($url_components['query'])){
                                            parse_str($url_components['query'], $params);
                                            if(isset($params['m']))
                                                $m = $params['m'];
                                        }
                                    @endphp

                                    {{-- {{($estimate->estimate_version > 0)? "Revised" :"Created"}}--}}
                                    <div class="card-body p-0 ribbon-box">
                                        <div class="ribbon-two ribbon-two-primary"><span>
                                                @if($estimate->estimate_version > 0)
                                                    @if($m==1)
                                                        Revised
                                                    @endif
                                                @else
                                                    Created
                                                @endif
{{--                                                {{($estimate->estimate_version > 0 && $m==1)? "Revised" :"Created"}}--}}
                                            </span>
                                        </div>
                                        <div class="card-body text-center">
                                            <div
                                                class="d-flex justify-content-center align-items-center w-100 pb-2">
                                                @php
                                                    $new_pdf_name = ($estimate->estimate_version > 0)?$estimate->estimate_no.'-V'.$estimate->estimate_version :$estimate->estimate_no;
                $pdf_company_id = (auth()->user()->company_id)?auth()->user()->company_id:auth()->user()->user_id;

                                                @endphp

                                                @php
                                                    // Verify file existence and permissions
                                                    $fileKey = 'public/' . auth()->user()->id . '/documents/' . $new_pdf_name . '.pdf';

                                                    // Check if the file exists in S3
                                                    if (Storage::disk('s3')->exists($fileKey)) {
                                                        // Set proper permissions for public access (if needed)
                                                        Storage::disk('s3')->setVisibility($fileKey, 'public');

                                                        // Generate the temporary URL for download
                                                        $temporaryUrl = Storage::disk('s3')->url($fileKey);
                                                    } else {
                                                        $temporaryUrl = null;
                                                    }
                                                @endphp
                                                @if(auth()->user()->company_id)

                                                    {{--<a href="{{Storage::url('public/document/'.auth()->user()->company_id.'/' . $new_pdf_name . '.pdf')}}"
                                                       class="btn btn-primary" download>
                                                        Download as PDF
                                                    </a>--}}
<a href="{{Storage::disk('s3')->temporaryUrl(trim('public/'.auth()->user()->company_id.'/documents/' . $new_pdf_name . '.pdf'),Carbon\Carbon::now()->addMinutes(20))}}"
                                                       class="btn btn-primary" download target="_blank">
                                                        Download as PDF
                                                    </a>

                                                @else
                                                   {{-- <a href="{{Storage::url('public/document/'.auth()->user()->id.'/' . $new_pdf_name . '.pdf')}}"
                                                       class="btn btn-primary" download>
                                                        Download as PDF
                                                    </a>--}}

                                                    <a href="{{$temporaryUrl}}"
                                                       class="btn btn-primary" download target="_blank">
                                                        Download as PDF
                                                    </a>
                                                @endif
                                                <a href="{{url('lead/timeline/'.Crypt::encrypt($estimate->customer_id))}}"
                                                   class="ms-2 btn btn-secondary">Go to lead</a>
                                            </div>
                                            @if(auth()->user()->company_id)
                                               {{-- <object
                                                    data='{{url('/'). Storage::url('document/'.auth()->user()->company_id.'/' . $new_pdf_name . '.pdf#toolbar=0')}}'
                                                    type="application/pdf"
                                                    style="width: 54%"
                                                    height="678"
                                                >--}}

                                                {{--<object
                                                    data='{{url('/'). Storage::url('document/'.auth()->user()->company_id.'/' . $new_pdf_name . '.pdf#toolbar=0')}}'
                                                    type="application/pdf"
                                                    style="width: 54%"
                                                    height="678"
                                                >

                                                    <iframe
                                                        src='{{url('/').Storage::url('document/'.auth()->user()->company_id.'/' . $new_pdf_name . '.pdf#toolbar=0')}}'>
                                                        <p>This browser does not support PDF!</p>
                                                    </iframe>
                                                </object>--}}

                                                    <object
                                                        data='{{Storage::disk('s3')->url('public/'.auth()->user()->company_id.'/documents/' . $new_pdf_name . '.pdf').'#toolbar=0'}}'
                                                        type="application/pdf"
                                                        style="width: 54%"
                                                        height="678"
                                                    >

                                                    <iframe
                                                        src='{{Storage::disk('s3')->url('public/'.auth()->user()->company_id.'/documents/' . $new_pdf_name . '.pdf').'#toolbar=0'}}'>
                                                        <p>This browser does not support PDF!</p>
                                                    </iframe>
                                                </object>

                                            @else

 {{--<object
                                                    data='{{url('/'). Storage::url('document/'.auth()->user()->id.'/' . $new_pdf_name . '.pdf#toolbar=0')}}'
                                                    type="application/pdf"
                                                    style="width: 54%"
                                                    height="678"
                                                >

                                                    <iframe
                                                        src='{{url('/').Storage::url('document/'.auth()->user()->id.'/' . $new_pdf_name . '.pdf#toolbar=0')}}'>
                                                        <p>This browser does not support PDF!</p>
                                                    </iframe>
                                                </object>--}}

                                                <object
                                                    data='{{Storage::disk('s3')->url('public/'.auth()->user()->id.'/documents/' . $new_pdf_name . '.pdf').'#toolbar=0'}}'
                                                    type="application/pdf"
                                                    style="width: 54%"
                                                    height="678"
                                                >

                                                    <iframe
                                                        src='{{Storage::disk('s3')->url('public/'.auth()->user()->id.'/documents/' . $new_pdf_name . '.pdf').'#toolbar=0'}}'>
                                                        <p>This browser does not support PDF!</p>
                                                    </iframe>
                                                </object>

                                            @endif

                                            {{--<embed src="{{Storage::url('document/' . $data['estimate_no'] . '.pdf')}}" width="100%"
                                                   height="1000" style="outline: none;"/>--}}
                                        </div> <!-- end card-body -->

                                        <div class="row d-none" id="invoices">
                                            @if($estimate->pdf_cover_page_flg)
                                                <page size="A4">
                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                           style="width: 100%;height: 100%;">
                                                        <tr>
                                                            <td rowspan="3"
                                                                style="background-color:{!! $proposal_template->theme_color_one !!};text-align:center;width: 50%;">
                                                                <img
                                                                    src="{!! Storage::url($proposal_template->cover_img) !!}"
                                                                    height="100%" width="100%"
                                                                    id="preview_image_container">
                                                            </td>
                                                            <td style="width: 50%;background-color:{!! $proposal_template->theme_color_one !!}">
                                                                <table border="0" cellspacing="6" cellpadding="4"
                                                                       style="text-align:right;vertical-align:middle;padding-bottom:40px;"
                                                                       width="100%">
                                                                    <tr>
                                                                        <td>
                                                                            <div
                                                                                class="proposal_title">{!! html_entity_decode($proposal_template->cover_title, ENT_COMPAT|ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401|ENT_NOQUOTES, 'UTF-8')!!}</div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="background-color:{!! $proposal_template->theme_color_one !!}">

                                                                <table border="0" cellspacing="0" cellpadding="4"
                                                                       style="padding-bottom:40px;width: 100%;">
                                                                    <tr>
                                                                        <td style="background-color:{!! $proposal_template->theme_color_two !!}">
                                                                            <div
                                                                                id="partial_div_bg">{!!html_entity_decode($proposal_template->cover_content, ENT_QUOTES, 'UTF-8')!!}</div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td valign="bottom"
                                                                style="background-color:{!! $proposal_template->theme_color_one !!}">

                                                                <table border="0" cellspacing="2" cellpadding="5"
                                                                       style="text-align:right;width:100%">
                                                                    <tr>
                                                                        <td colspan="3">
                                                                            <div id="footer_one_div">
                                                                                {!! html_entity_decode($proposal_template->cover_footer_one, ENT_QUOTES, 'UTF-8')!!}
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td>
                                                                            <hr style="height:6px;color:#FFF">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3">
                                                                            <div id="footer_two_div">
                                                                                {!! html_entity_decode($proposal_template->cover_footer_two, ENT_QUOTES, 'UTF-8')!!}
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <table cellpadding="6" style="width: 100%;">
                                                                    <tr style="background-color:{!! $proposal_template->theme_color_two !!}">
                                                                        <td><a href="https://heavendesigns.in"
                                                                               target="_blank"
                                                                               style="text-decoration: none;color:{!! $proposal_template->theme_color_one !!};">Heaven
                                                                                Designs</a></td>
                                                                        <td style="text-align: right;color:{!! $proposal_template->theme_color_one !!};">
                                                                            Social Media Link
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </page>
                                                {{--<div class="row col-12">
                                                    <div class="main-page">
                                                        <div class="sub-page">
                                                            <div
                                                                class="d-flex justify-content-center align-items-center w-100">
                                                                <div class="row"
                                                                     style="size: 21cm 29.7cm; height:29.68cm;">
                                                                    <div class="p-0"
                                                                         style=" display: grid;  grid-template-columns: repeat(2, 1fr);">
                                                                        <div id="background_image"
                                                                             class="w-100 h-100"
                                                                             style="position: relative; background-image:url('{{Storage::url(trim($proposal_template->cover_img))}}'); background-repeat: no-repeat; background-size: 100% 100%;">
                                                                            <div class="h-50 col-12" id="logo-img-div"
                                                                                 style="{{$proposal_template->logo_dimension_one}}">
                                                                                <label for="inputGroupFile04">
                                                                                    <img
                                                                                        src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                        id="preview_image_container"
                                                                                        alt="Put logo Here"
                                                                                        class="ms-3 pt-2"
                                                                                        width="{{$proposal_template->logo_dimension_img}}"/>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row h-10 main_div_bg"
                                                                             style="background-color:{{$proposal_template->theme_color_one}}">
                                                                            <div
                                                                                class="w-100 d-flex justify-content-center align-items-center text-black  h-25">
                                                                                <div
                                                                                    class="col-10 text-break text-end proposal_title">
                                                                                    {!! html_entity_decode($estimate->est_cover_page_title_div, ENT_COMPAT|ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401|ENT_NOQUOTES, 'UTF-8')!!}
                                                                                </div>
                                                                            </div>
                                                                            <div
                                                                                class="w-100 d-flex justify-content-end align-items-center text-black h-25 p-0">
                                                                                <div
                                                                                    class="text-break text-left text-white mr-0 p-2"
                                                                                    id="partial_div_bg"
                                                                                    style="position:absolute;background-color:{{$proposal_template->theme_color_two}};width: 440px;">
                                                                                    {!!html_entity_decode($estimate->est_cover_page_content_div, ENT_QUOTES, 'UTF-8')!!}
                                                                                </div>
                                                                            </div>

                                                                            <div
                                                                                class="w-100 d-flex align-items-end text-black h-50">
                                                                                <div
                                                                                    class="w-100 d-flex align-items-end text-black h-50 text-capitalize">
                                                                                    <div
                                                                                        class="w-100 text-break text-end p-3">
                                                                                        <div id="footer_one_div">
                                                                                            {!! html_entity_decode($estimate->est_cover_page_footer_one_div, ENT_QUOTES, 'UTF-8')!!}
                                                                                        </div>

                                                                                        <div id="footer_two_div">
                                                                                            {!! html_entity_decode($estimate->est_cover_page_footer_two_div, ENT_QUOTES, 'UTF-8')!!}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>--}}
                                            @endif
                                            @if($estimate->pdf_about_us_flg)
                                                <page size="A4">
                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                           style="width: 100%">
                                                        <tr>
                                                            <td colspan="2" style="text-align: center;">
                                                                <img
                                                                    src="{!! Storage::url($proposal_template->aboutas_img) !!}"
                                                                    class="preview_image_container_aboutus_cover"
                                                                    id="preview_image_container_aboutus_cover"
                                                                    height="400"/>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table border="0" cellspacing="" cellpadding="4"
                                                           style="width: 100%;">
                                                        <tr>
                                                            <td style="width:50%;"></td>
                                                            <td rowspan="2"
                                                                style="background-color:{!! $proposal_template->theme_color_one !!};">
                                                                <div
                                                                    class="aboutus_title">{!!html_entity_decode($estimate->est_aboutus_title, ENT_QUOTES, 'UTF-8')!!}</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                        </tr>
                                                    </table>
                                                    <table border="0" cellspacing="0" cellpadding="6">
                                                        <tr>
                                                            <td>
                                                                <div
                                                                    class="aboutus_partial_div_bg">{!! html_entity_decode($estimate->est_aboutus_content, ENT_QUOTES, 'UTF-8')!!}</div>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </page>
                                                {{--<div class="col-md-12" style="page-break-before:always;">
                                                    <div class="main-page">
                                                        <div class="sub-page">
                                                            <div
                                                                class="d-flex justify-content-center align-items-center w-100"
                                                                id="invoices">
                                                                <div class="row"
                                                                     style="size: 21cm 29.7cm; height:29.68cm;margin-top:0px;">
                                                                    <div class="mt-2">
                                                                        <label for="inputGroupFile04"
                                                                               style="float:right;width: 100%;">
                                                                            <img
                                                                                src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                id="preview_image_container_aboutus"
                                                                                alt="Put logo Here"
                                                                                style="float:right;padding-right: 2rem;width:{{$proposal_template->aboutas_logo_dimension}};"/>
                                                                        </label>
                                                                    </div>
                                                                    <div
                                                                        class="w-100 d-flex justify-content-center pb-3">
                                                                        <img
                                                                            src="{{Storage::url(trim($proposal_template->aboutas_img))}}"
                                                                            id="preview_image_container_aboutus_cover"
                                                                            class="preview_image_container_aboutus_cover"
                                                                            alt="Put logo Here"
                                                                            style="width: 90%;"/>
                                                                    </div>
                                                                    <div class="d-flex flex-row-reverse"
                                                                         style="margin-top: -10%;">
                                                                        <div
                                                                            class="d-flex justify-content-center align-items-center p-0 main_div_bg"
                                                                            style="width: 50%;background-color:{{trim($proposal_template->theme_color_one)}};">
                                                                            <div
                                                                                class="fs-4 mb-0 text-black text-break text-center aboutus_title"> {!!html_entity_decode($estimate->est_aboutus_title_div, ENT_QUOTES, 'UTF-8')!!}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div
                                                                        class="w-100 d-flex justify-content-center">
                                                                        <div class="d-block"
                                                                             style="width: 90%;">
                                                                            <div
                                                                                class="w-100 justify-content-center align-items-center text-black">
                                                                                <div
                                                                                    class="aboutus_partial_div_bg"
                                                                                    style="text-align: justify;">
                                                                                    {!!html_entity_decode($estimate->est_aboutus_content_div, ENT_QUOTES, 'UTF-8')!!}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>--}}
                                            @endif
                                            @if($estimate->pdf_product_flg)
                                                @foreach($products as $pk => $product)
                                                    <page size="A4">
                                                        <table border="0" cellspacing="5" cellpadding="1"
                                                               style="width:100%">
                                                            <tr>
                                                                <td>
                                                                    {!! ($proposal_template->product_title)? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : ''!!}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>{!! ($proposal_template->product_content)? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : ''!!}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align: center;">
                                                                    <h4><strong>{{++$pk.' '.$product->name}}</strong>
                                                                    </h4></td>
                                                            </tr>
                                                        </table>
                                                        <table border="0" cellspacing="0" cellpadding="4"
                                                               style="width:100%">
                                                            <tr>
                                                                <td colspan="2"
                                                                    style="max-height:500vh;text-align:center">
{{--                                                                    <img--}}
{{--                                                                        src="{!! ($product->image_one)?Storage::disk('s3')->temporaryUrl(trim($product->image_one),Carbon\Carbon::now()->addMinutes(20)):null; !!}"--}}
{{--                                                                        height="400">--}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align:center">
                                                                    {{--<img
                                                                        src={!! ($product->image_two)?Storage::disk('s3')->temporaryUrl(trim($product->image_two),Carbon\Carbon::now()->addMinutes(20)):null; !!}"
                                                                        height="250">--}}
                                                                </td>
                                                                <td style="text-align:center">
                                                                    {{--<img
                                                                        src="{!! ($product->image_three)?Storage::disk('s3')->temporaryUrl(trim($product->image_three),Carbon\Carbon::now()->addMinutes(20)):null; !!}"
                                                                        height="250">--}}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </page>
                                                @endforeach
                                                {{--<div class="col-md-12">
                                                    <div class="main-page">
                                                        <div class="sub-page">
                                                            @foreach($products as $pk => $product)
                                                                <div class="p-0 row"
                                                                     style="margin-top:0px;text-align: center;@if($pk > 0)page-break-before:always;@endif">
                                                                    <div class="mt-2">
                                                                        <label for="inputGroupFile04"
                                                                               style="float:right;width: 100%;">
                                                                            <img
                                                                                src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                id="preview_image_container_product"
                                                                                alt="Put logo Here"
                                                                                style="float:right;padding-right: 2rem;width:{{$proposal_template->aboutas_logo_dimension}};"/>
                                                                        </label>
                                                                    </div>
                                                                    --}}{{--                                                                    <div--}}{{--
                                                                    --}}{{--                                                                        class="clearfix d-flex justify-content-center">--}}{{--
                                                                    <div class="float-start mb-1"
                                                                         style="text-align: center;">
                                                                        <div
                                                                            class="product_title">{!! ($proposal_template->product_title)? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : html_entity_decode('<h1>Your Solar Plant Design</h1>', ENT_QUOTES, 'UTF-8')!!}</div>
                                                                        <div
                                                                            class="product_content">{!! ($proposal_template->product_content)? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : html_entity_decode('<p><strong>This is pre-design or reference photo of your solar power plant.</strong></p>', ENT_QUOTES, 'UTF-8')!!}</div>
                                                                    </div>
                                                                    --}}{{--                                                                    </div>--}}{{--


                                                                    <h3 style="text-align: center;">
                                                                        <u>{{$product->name}}</u></h3>
                                                                    <div class="row">
                                                                        <div class="col-12 pb-3">
                                                                            <img
                                                                                src="{{Storage::url(trim($product->image_one))}}"
                                                                                id="preview_image_container_product_cover"
                                                                                class="preview_image_container_product_cover"
                                                                                alt="Put logo Here"/>
                                                                        </div>
                                                                    </div>
                                                                    --}}{{--                                                                    <div--}}{{--
                                                                    --}}{{--                                                                        class="w-100 d-flex justify-content-center pb-3">--}}{{--
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <img
                                                                                src="{{Storage::url(trim($product->image_two))}}"
                                                                                id="preview_image_container_aboutus_cover"
                                                                                class="preview_image_container_aboutus_cover"
                                                                                alt="Put logo Here" style="height:65%"/>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <img
                                                                                src="{{Storage::url(trim($product->image_three))}}"
                                                                                id="preview_image_container_aboutus_cover"
                                                                                class="preview_image_container_aboutus_cover"
                                                                                alt="Put logo Here" style="height:65%"/>
                                                                        </div>
                                                                    </div>
                                                                    --}}{{--                                                                    </div>--}}{{--


                                                                </div>
                                                            @endforeach
                                                        </div>

                                                    </div>

                                                </div>--}}
                                            @endif
                                            @if($estimate->pdf_est_flg)
                                                <page size="A4">
                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                           style="width: 100%;">
                                                        <tr>
                                                            <td colspan="3">
                                                                <table border="0" cellspacing="2" cellpadding="4"
                                                                       style="width: 100%;">
                                                                    <tr>
                                                                        <td>
                                                                            <h1 style="color:{!! $proposal_template->theme_color_one !!};text-align:center">
                                                                                {{($proposal_template->est_title)?$proposal_template->est_title : 'Estimate' }}</h1>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="border-right:.5px solid #dee2e6;border-radius:1rem!important;width:33.33%;">
                                                                <table border="0" cellspacing="2" cellpadding="4">
                                                                    <tr>
                                                                        <td>
                                                                            <h4 style="color:{!! $proposal_template->theme_color_one !!}">
                                                                                {{$company_data->company_name}}</h4>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align:justify">
                                                                            <address>
                                                                                {{$company_data->name}}<br>
                                                                                {{$company_data->address.', '.$company_data->city_name}}
                                                                                <br>
                                                                                {{$company_data->state_name.', '.$company_data->pincode}}
                                                                                <br>
                                                                                {{$company_data->country_name}}
                                                                                <br>
                                                                                {{'Mobile :'.$company_data->mobile_no}}
                                                                            </address>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td style="border-right:.5px solid #dee2e6;border-radius:1rem!important;width:33.33%;">
                                                                <table border="0" cellspacing="2" cellpadding="4">
                                                                    <tr>
                                                                        <td>
                                                                            <h4 style="color:{!! $proposal_template->theme_color_one !!}">
                                                                                Bill To</h4></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><h4>{{$estimate->customer_name}}</h4>
                                                                            <address>
                                                                                {!! nl2br(e($estimate->customer_address)) !!}
                                                                            </address>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td>
                                                                <table border="0" cellspacing="2" cellpadding="4">
                                                                    <tr>
                                                                        <td><strong
                                                                                style="color:{!! $proposal_template->theme_color_one !!}">Date:</strong>{{\Carbon\Carbon::parse($estimate->estimate_date)->format('d M, Y')}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong
                                                                                style="color:{!! $proposal_template->theme_color_one !!}">Expiry
                                                                                Date:</strong>{{\Carbon\Carbon::parse($estimate->expiry_date)->format('d M, Y')}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong
                                                                                style="color:{!! $proposal_template->theme_color_one !!}">Estimate#:</strong>{{$estimate->estimate_no}}
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3"><br><br>
                                                                <table width="100%" border="0" cellspacing="0"
                                                                       cellpadding="4"
                                                                       style="border:.5px solid #dee2e6;border-radius:1rem!important">
                                                                    <thead>
                                                                    <tr>
                                                                        <th width="5%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_no)?$proposal_template->item_table_no : '#'!!}
                                                                        </th>
                                                                        <th width="25%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_item)?$proposal_template->item_table_item : 'Item & Description'!!}
                                                                        </th>
                                                                        <th width="11%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_hsn)?$proposal_template->item_table_hsn : 'HSN/SAC'!!}
                                                                        </th>
                                                                        <th width="5%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_qty)?$proposal_template->item_table_qty : 'Qty'!!}
                                                                        </th>
                                                                        <th width="6%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_rate)?$proposal_template->item_table_rate : 'Rate'!!}
                                                                        </th>
                                                                        <th width="8%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_discount)?$proposal_template->item_table_discount : 'Dis'!!}
                                                                        </th>
                                                                        <th width="10%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff;{{($company_data->state_id == $estimate->customer_state_id)?'':'display:none'}};">
                                                                            {!!($proposal_template->item_table_cgst)?$proposal_template->item_table_cgst : 'CGST'!!}
                                                                        </th>
                                                                        <th width="10%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff;{{($company_data->state_id == $estimate->customer_state_id)?'':'display:none'}};">
                                                                            {!!($proposal_template->item_table_sgst)?$proposal_template->item_table_sgst : 'SGST'!!}
                                                                        </th>
                                                                        <th width="10%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff;{{($company_data->state_id == $estimate->customer_state_id)?'display:none':''}};">
                                                                            {!!($proposal_template->item_table_igst)?$proposal_template->item_table_igst : 'IGST'!!}
                                                                        </th>
                                                                        <th width="10%"
                                                                            style="background-color:{!! $proposal_template->theme_color_one !!};color:#fff">
                                                                            {!!($proposal_template->item_table_total)?$proposal_template->item_table_total : 'Total'!!}
                                                                        </th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach($estimate_items as $key =>$estimate_item)
                                                                        <tr id="{{ ++$key }}">
                                                                            <td style="border-bottom:.5px solid #dee2e6">
                                                                                {{ $key }}
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6">
                                                                                <b>{{$estimate_item->item_name}}</b>{!!($estimate_item->item_name)?'<br>'.nl2br(e($estimate_item->item_description)):''!!}
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6">
                                                                                {{$estimate_item->hsn_code}}
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6"> {{$estimate_item->quantity}}
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6">
                                                                                {{'₹ '.$estimate_item->price}}
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6">{{$estimate_item->discount}}{{($estimate_item->discount_flag==1)?' %': ' '.$country_data->currency_symbol}}
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6;{{($company_data->state_id == $estimate->customer_state_id)?'':'display:none'}}">
                                                                                {{'₹ '.$estimate_item->cgst_amount}}
                                                                                <br><small>{{($estimate_item->gst_per/2).' %'}}</small>
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6;{{($company_data->state_id == $estimate->customer_state_id)?'':'display:none'}}">
                                                                                {{'₹ '.$estimate_item->sgst_amount}}
                                                                                <br><small>{{($estimate_item->gst_per/2).' %'}}</small>
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6;{{($company_data->state_id == $estimate->customer_state_id)?'display:none':''}}">
                                                                                {{'₹ '.$estimate_item->igst_amount}}
                                                                                <br><small>{{$estimate_item->gst_per.' %'}}</small>
                                                                            </td>
                                                                            <td style="border-bottom:.5px solid #dee2e6;text-align:right">
                                                                                {{'₹ '.$estimate_item->total}}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    <tr>
                                                                        <td rowspan="{{($estimate->addless_amount)?4:3}}"
                                                                            colspan="{{($company_data->state_id == $estimate->customer_state_id)?6:5}}"
                                                                            style="border:.5px solid #dee2e6;border-right:.5px solid #dee2e6;text-align:center;line-height:4">
                                                                            {{ \App\Helpers\LogActivity::convertToIndianCurrency($estimate->net_amount) }}
                                                                        </td>
                                                                        <td colspan="2"
                                                                            style="border:.5px solid #dee2e6">Sub-total:
                                                                        </td>
                                                                        <td style="border:.5px solid #dee2e6;text-align:right">
                                                                            {{'₹ '.$estimate->subtotal}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"
                                                                            style="border:.5px solid #dee2e6">Tax:
                                                                        </td>
                                                                        <td style="border:.5px solid #dee2e6;text-align:right">
                                                                            {{'₹ '.$estimate->total_igst_amount}}
                                                                        </td>
                                                                    </tr>
                                                                    @if($estimate->addless_amount)
                                                                        <tr>
                                                                            <td colspan="2"
                                                                                style="border:.5px solid #dee2e6">{{$estimate->addless_title}}
                                                                            </td>
                                                                            <td style="border:.5px solid #dee2e6;text-align:right">
                                                                                {{'₹ '.$estimate->addless_amount}}
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                    <tr>
                                                                        <td colspan="3"
                                                                            style="border:.5px solid #dee2e6;text-align:right">
                                                                            {{'₹ '.($estimate->net_amount+$estimate->addless_amount)}}
                                                                            INR
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3"><br><br>
                                                                <table border="0" cellspacing="0" cellpadding="4"
                                                                       style="border:1px solid #dee2e6;width: 100%">
                                                                    <tr>
                                                                        <td style="border-right:1px solid #dee2e6;border-bottom:1px solid #dee2e6;width: 33.33%;">
                                                                            <h4 style="color:{!! $proposal_template->theme_color_one !!}">
                                                                                Notes:</h4></td>
                                                                        <td style="border-right:1px solid #dee2e6;border-bottom:1px solid #dee2e6;width: 33.33%;">
                                                                            <h4 style="color:{!! $proposal_template->theme_color_one !!}">
                                                                                Bank Detail :-</h4>
                                                                        </td>
                                                                        <td style="border-bottom:1px solid #dee2e6;width: 33.33%;">
                                                                            <h4
                                                                                style="color:{!! $proposal_template->theme_color_one !!}">
                                                                                Terms & Conditions:-</h4></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="border-right:1px solid #dee2e6">

                                                                            {!! nl2br(e($estimate->customer_notes)) !!}

                                                                        </td>
                                                                        <td style="border-right:1px solid #dee2e6">

                                                                            {!! ($proposal_template->est_bank_details)? html_entity_decode($proposal_template->est_bank_details, ENT_QUOTES, 'UTF-8') : ''!!}

                                                                        </td>
                                                                        <td>

                                                                            {!! ($estimate->term_condition)? html_entity_decode($estimate->term_condition, ENT_QUOTES, 'UTF-8') : ''!!}

                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3"><br><br>
                                                                <table border="0" cellspacing="2" cellpadding="4"
                                                                       style="text-align:center;width: 100%">
                                                                    <tr>
                                                                        <td style="width: 25%"></td>
                                                                        <td style="width: 25%"></td>
                                                                        <td style="width: 25%"></td>
                                                                        <td style="width: 25%"><img
                                                                                src="{{Storage::url(trim($proposal_template->est_signature_img))}}"
                                                                                width="100%"/>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td style="border-top:.5px solid #dee2e6">
                                                                            {!! ($proposal_template->est_signature_lable)? html_entity_decode($proposal_template->est_signature_lable, ENT_QUOTES, 'UTF-8') : ''!!}
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </page>
                                                {{--<div class="col-md-12" style="page-break-before:always;">
                                                    <div class="main-page">
                                                        <div class="sub-page"
                                                             style="margin-top:0px;">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="clearfix">
                                                                        <div class="float-start mb-1">
                                                                            <img
                                                                                src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                id="preview_image_container_estimate"
                                                                                alt="Put logo Here"
                                                                                height="{{$proposal_template->est_logo_dimension}}"/>
                                                                        </div>
                                                                        <div class="float-end">
                                                                            <h2 class="mt-2 estimate_title_heading"
                                                                                id="estimate_title_heading">
                                                                                {{($proposal_template->est_title)?$proposal_template->est_title : 'Estimate' }}</h2>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <h4 class="estimate_title_heading">
                                                                                {{$company_data->company_name}}</h4>
                                                                            <address>
                                                                                {{$company_data->name}}<br>
                                                                                {{$company_data->address.', '.$company_data->city_name}}
                                                                                <br>
                                                                                {{$company_data->state_name.', '.$company_data->pincode}}
                                                                                <br>
                                                                                {{$company_data->country_name}}
                                                                                <br>
                                                                                {{'Mobile :'.$company_data->mobile_no}}
                                                                            </address>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <h4 class="estimate_title_heading">
                                                                                Bill To</h4>
                                                                            <h4>{{$estimate->customer_name}}</h4>
                                                                            <address>
                                                                                {!! nl2br(e($estimate->customer_address)) !!}
                                                                            </address>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <div class="mt-3 ">
                                                                                <p class="font-13">
                                                                                    <strong
                                                                                        class="estimate_title_heading">Date: </strong>
                                                                                    {{\Carbon\Carbon::parse($estimate->estimate_date)->format('d M, Y')}}
                                                                                </p>
                                                                                <p class="font-13"><strong
                                                                                        class="estimate_title_heading">Expiry
                                                                                        Date: </strong> {{\Carbon\Carbon::parse($estimate->expiry_date)->format('d M, Y')}}
                                                                                </p>
                                                                                <p class="font-13"><strong
                                                                                        class="estimate_title_heading">Estimate#: </strong>
                                                                                    {{$estimate->estimate_no}}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <div class="table-responsive">
                                                                                <table
                                                                                    class="table table-sm mt-4">
                                                                                    <thead
                                                                                        class="table-thead">
                                                                                    <tr>
                                                                                        <th class="item_table_no">
                                                                                            {!!($proposal_template->item_table_no)?$proposal_template->item_table_no : '#'!!}
                                                                                        </th>
                                                                                        <th class="item_table_item">
                                                                                            {!!($proposal_template->item_table_item)?$proposal_template->item_table_item : 'Item & Description'!!}
                                                                                        </th>
                                                                                        <th class="item_table_hsn">
                                                                                            {!!($proposal_template->item_table_hsn)?$proposal_template->item_table_hsn : 'HSN/SAC'!!}
                                                                                        </th>
                                                                                        <th class="item_table_qty">
                                                                                            {!!($proposal_template->item_table_qty)?$proposal_template->item_table_qty : 'Qty'!!}
                                                                                        </th>
                                                                                        <th class="item_table_rate">
                                                                                            {!!($proposal_template->item_table_rate)?$proposal_template->item_table_rate : 'Rate'!!}
                                                                                        </th>
                                                                                        <th class="item_table_discount">
                                                                                            {!!($proposal_template->item_table_discount)?$proposal_template->item_table_discount : 'Discount'!!}
                                                                                        </th>
                                                                                        <th class="item_table_cgst {{($company_data->state_id == $estimate->customer_state_id)?'':'d-none'}}">
                                                                                            {!!($proposal_template->item_table_cgst)?$proposal_template->item_table_cgst : 'CGST'!!}
                                                                                        </th>
                                                                                        <th class="item_table_sgst {{($company_data->state_id == $estimate->customer_state_id)?'':'d-none'}}">
                                                                                            {!!($proposal_template->item_table_sgst)?$proposal_template->item_table_sgst : 'SGST'!!}
                                                                                        </th>
                                                                                        <th class="item_table_igst {{($company_data->state_id == $estimate->customer_state_id)?'d-none':''}}">
                                                                                            {!!($proposal_template->item_table_igst)?$proposal_template->item_table_igst : 'IGST'!!}
                                                                                        </th>
                                                                                        <th class="text-end item_table_total">
                                                                                            {!!($proposal_template->item_table_total)?$proposal_template->item_table_total : 'Total'!!}
                                                                                        </th>
                                                                                    </tr>
                                                                                    </thead>
                                                                                    <tbody>

                                                                                    @foreach($estimate_items as $key =>$estimate_item)

                                                                                        <tr id="{{ ++$key }}">
                                                                                            <td>{{ $key }}</td>
                                                                                            <td>
                                                                                                <b>{{$estimate_item->item_name}}</b>
                                                                                                {!!($estimate_item->item_name)?'<br>'.nl2br(e($estimate_item->item_description)):''!!}
                                                                                            </td>
                                                                                            <td>
                                                                                                {{$estimate_item->hsn_code}}
                                                                                            </td>
                                                                                            <td>
                                                                                                {{$estimate_item->quantity}}
                                                                                            </td>
                                                                                            <td>
                                                                                                {{'₹ '.$estimate_item->price}}
                                                                                            </td>
                                                                                            <td>
                                                                                                {{$estimate_item->discount}}{{($estimate_item->discount_flag==1)?' %': ' ₹'}}

                                                                                            </td>
                                                                                            <td class="{{($company_data->state_id == $estimate->customer_state_id)?'':'d-none'}}">{{'₹ '.$estimate_item->sgst_amount}}
                                                                                                <br><small>{{($estimate_item->gst_per/2).' %'}}</small>
                                                                                            </td>
                                                                                            <td class="{{($company_data->state_id == $estimate->customer_state_id)?'':'d-none'}}">{{'₹ '.$estimate_item->cgst_amount}}
                                                                                                <br><small>{{($estimate_item->gst_per/2).' %'}}</small>
                                                                                            </td>
                                                                                            <td class="{{($company_data->state_id == $estimate->customer_state_id)?'d-none':''}}">{{'₹ '.$estimate_item->igst_amount}}
                                                                                                <br><small>{{$estimate_item->gst_per.' %'}}</small>
                                                                                            </td>
                                                                                            <td class="text-end">{{'₹ '.$estimate_item->total}}</td>

                                                                                        </tr>
                                                                                    @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div
                                                                            class="col-6 fs-5 text-center align-self-center text-muted estimate_title_heading">
                                                                            <b>{{ \App\Helpers\LogActivity::convertToIndianCurrency($estimate->net_amount) }}</b>
                                                                        </div>

                                                                        <div class="col-6">
                                                                            <div
                                                                                class="float-end mt-2 mt-0">
                                                                                <p>
                                                                                    <b class="estimate_title_heading">Sub-total:</b>
                                                                                    <span
                                                                                        class="float-end">{{'₹ '.$estimate->subtotal}}</span>
                                                                                </p>
                                                                                <p><b class="estimate_title_heading">TAX:</b>
                                                                                    <span
                                                                                        class="float-end">{{'₹ '.$estimate->total_igst_amount}}</span>
                                                                                </p>
                                                                                @if($estimate->addless_amount)
                                                                                    <p>
                                                                                        <b class="estimate_title_heading">{{$estimate->addless_title}}
                                                                                            :</b> <span
                                                                                            class="float-end">{{'₹ '.$estimate->addless_amount}}</span>
                                                                                    </p>
                                                                                @endif
                                                                                <h3 class="estimate_title_heading">{{'₹ '.($estimate->net_amount+$estimate->addless_amount)}}
                                                                                    INR</h3>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row pt-2">
                                                                        <div class="col-6">
                                                                            <div class="pt-2">
                                                                                <h6 class="estimate_title_heading">
                                                                                    Notes:</h6>
                                                                                <small>
                                                                                    {!! nl2br(e($estimate->customer_notes)) !!}
                                                                                </small>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-6">
                                                                            <div class="pt-2">
                                                                                <h4 class="estimate_title_heading estimate_bank_label">
                                                                                    {!! ($proposal_template->est_bank_label)? html_entity_decode($proposal_template->est_bank_label, ENT_QUOTES, 'UTF-8') : html_entity_decode('Bank Detail :', ENT_QUOTES, 'UTF-8')!!}</h4>
                                                                                <small
                                                                                    class="bank_info_details">
                                                                                    {!! ($proposal_template->est_bank_details)? html_entity_decode($proposal_template->est_bank_details, ENT_QUOTES, 'UTF-8') : html_entity_decode('<p>Bank Name:- ICICI BANK LTD</p><p>Account Name.:- HEAVEN DESIGNS PRIVATE LIMITED</p><p>Account No.:- 183605002858</p><p>ISFC :- ICIC0001836</p><p>Banch:- KATARGAM - SURAT</p>', ENT_QUOTES, 'UTF-8')!!}

                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row pt-2">
                                                                        <div class="col-6">
                                                                            <div class="pt-2">
                                                                                <h4 class="estimate_title_heading estimate_term_condition_label">
                                                                                    {!! ($proposal_template->est_term_condition_lable)? html_entity_decode($proposal_template->est_term_condition_lable, ENT_QUOTES, 'UTF-8') : html_entity_decode('Terms &amp; Conditions:', ENT_QUOTES, 'UTF-8')!!}</h4>
                                                                                <small
                                                                                    class="estimate_term_condition_main_text">
                                                                                    {!! ($proposal_template->est_term_condition_details)? html_entity_decode($proposal_template->est_term_condition_details, ENT_QUOTES, 'UTF-8') : html_entity_decode('test', ENT_QUOTES, 'UTF-8')!!}
                                                                                </small>
                                                                            </div>
                                                                        </div>

                                                                        <div
                                                                            class="col-lg-6 align-self-end">
                                                                            <div class="w-25 float-end">
                                                                                <img
                                                                                    src="{{Storage::url(trim($proposal_template->est_signature_img))}}"
                                                                                    alt=""
                                                                                    class="img-fluid"
                                                                                    id="estimate_signature_image">
                                                                                <p class="border-top estimate_title_heading estimate_signature_label">
                                                                                    {!! ($proposal_template->est_signature_lable)? html_entity_decode($proposal_template->est_signature_lable, ENT_QUOTES, 'UTF-8') : html_entity_decode('Authorized Signature', ENT_QUOTES, 'UTF-8')!!}</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>--}}
                                            @endif

                                            @if($estimate->pdf_terms_flg)
                                                <page size="A4">
                                                    <table border="0" cellspacing="5" cellpadding="1"
                                                           style="text-align:center;width:100%;">
                                                        <tr>
                                                            <td>
                                                                <div class="terms_title">
                                                                    {!! html_entity_decode($estimate->est_term_condition_title, ENT_QUOTES, 'UTF-8') !!}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table border="0" cellspacing="5" cellpadding="1"
                                                           style="width:100%;">
                                                        <tr>
                                                            <td>
                                                                <div class="terms_content">
                                                                    {!! html_entity_decode($estimate->est_term_condition_content, ENT_QUOTES, 'UTF-8') !!}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </page>
                                                {{--<div class="col-md-12" style="page-break-before:always">
                                                    <div class="main-page">
                                                        <div class="sub-page">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="p-0 row"
                                                                         style="padding-right: 2px !important;">
                                                                        --}}{{--<div class="mt-2">
                                                                            <label for="inputGroupFile04"
                                                                                   style="float:right;width: 100%;">
                                                                                <img
                                                                                    src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                    id="preview_image_container_terms"
                                                                                    alt="Put logo Here"
                                                                                    style="float:right;width:{{$proposal_template->aboutas_logo_dimension}};"/>
                                                                            </label>
                                                                        </div>--}}{{--

                                                                        <div class="mt-2">
                                                                            <label for="inputGroupFile04"
                                                                                   style="float:right;width: 100%;">
                                                                                <img
                                                                                    src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                    id="preview_image_container_product"
                                                                                    alt="Put logo Here"
                                                                                    style="float:right;padding-right: 10px;width:{{$proposal_template->aboutas_logo_dimension}};"/>
                                                                            </label>
                                                                        </div>
                                                                        --}}{{--                                                                        <div--}}{{--
                                                                        --}}{{--                                                                            class="clearfix d-flex justify-content-center">--}}{{--
                                                                        <div
                                                                            class="float-start mb-1 terms_title"
                                                                            style="text-align: center;">
                                                                            {!! html_entity_decode($estimate->est_term_condition_title_div, ENT_QUOTES, 'UTF-8')!!}
                                                                        </div>
                                                                        --}}{{--                                                                        </div>--}}{{--


                                                                        <div class="w-90 col-12 terms_content pb-3">
                                                                            {!!html_entity_decode($estimate->est_term_condition_content_div, ENT_QUOTES, 'UTF-8')!!}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>--}}
                                            @endif

                                            @if($estimate->pdf_testimonial_flg)
                                                <page size="A4">
                                                    <table border="0" cellspacing="5" cellpadding="1"
                                                           style="text-align:center;width:100%;">
                                                        <tr>
                                                            <td>{!! ($proposal_template->testimonials_title)? html_entity_decode($proposal_template->testimonials_title, ENT_QUOTES, 'UTF-8') : ''!!}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{!! ($proposal_template->testimonials_content)? html_entity_decode($proposal_template->testimonials_content, ENT_QUOTES, 'UTF-8') : ''!!}</td>
                                                        </tr>
                                                    </table>
                                                    <br><br>
                                                    <table border="0" cellspacing="0" cellpadding="4"
                                                           style="border:.5px solid #dee2e6;border-radius:50rem!important">
                                                        <tr>
                                                            <td rowspan="4">
                                                               {{-- <img
                                                                    src="{{($testimonials->image_one)?Storage::disk('s3')->temporaryUrl(trim($testimonials->image_one),Carbon\Carbon::now()->addMinutes(20)):null;}}"
                                                                    style="width:100%;">--}}
                                                            </td>
                                                            <td colspan="3"><p>{{($testimonials)?$testimonials->description_one:''}}</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">
                                                                <strong>{{($testimonials)?$testimonials->client_name_one:''}}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">{{($testimonials)?$testimonials->rating_one:''}}</td>
                                                        </tr>
                                                    </table>
                                                    <br><br>
                                                    <table border="0" cellspacing="0" cellpadding="4"
                                                           style="border:.5px solid #dee2e6;border-radius:1rem!important">
                                                        <tr>
                                                            <td colspan="3"><p>{{($testimonials)?$testimonials->description_two:''}}</p>
                                                            </td>
                                                            <td rowspan="4">
                                                               {{-- <img
                                                                    src="{{($testimonials->image_two)?Storage::disk('s3')->temporaryUrl(trim($testimonials->image_two),Carbon\Carbon::now()->addMinutes(20)):null;}}"
                                                                    style="width:100%">--}}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">
                                                                <strong>{{($testimonials)?$testimonials->client_name_two:''}}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">{{($testimonials)?$testimonials->rating_two:''}}</td>
                                                        </tr>
                                                    </table>
                                                    <br><br>
                                                    <table border="0" cellspacing="0" cellpadding="4"
                                                           style="border:.5px solid #dee2e6;border-radius:1rem!important">
                                                        <tr>
                                                            <td rowspan="4">
                                                               {{-- <img
                                                                    src="{{($testimonials->image_three)?Storage::disk('s3')->temporaryUrl(trim($testimonials->image_three),Carbon\Carbon::now()->addMinutes(20)):null;}}"
                                                                    style="width:100%">--}}
                                                            </td>
                                                            <td colspan="3">{{($testimonials)?$testimonials->description_three:''}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">
                                                                <strong>{{($testimonials)?$testimonials->client_name_three:''}}</strong>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">{{($testimonials)?$testimonials->rating_three:''}}</td>
                                                        </tr>
                                                    </table>
                                                </page>


                                                {{-- <div class="col-md-12">
                                                     <div class="main-page">
                                                         <div class="sub-page">
                                                             <div class="p-0 row"
                                                                  style="margin-top:0px;text-align: center;">
                                                                 <div class="mt-2">
                                                                     <label for="inputGroupFile04"
                                                                            style="float:right;width: 100%;">
                                                                         <img
                                                                             src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                             id="preview_image_container_product"
                                                                             alt="Put logo Here"
                                                                             style="float:right;padding-right: 2rem;width:{{$proposal_template->aboutas_logo_dimension}};"/>
                                                                     </label>
                                                                 </div>
                                                                 <div class="float-start mb-1"
                                                                      style="text-align: center;">
                                                                     <div
                                                                         class="product_title">{!! ($proposal_template->testimonials_title)? html_entity_decode($proposal_template->testimonials_title, ENT_QUOTES, 'UTF-8') : html_entity_decode('<h1 style="text-align:center">Testimonialss</h1>
 ', ENT_QUOTES, 'UTF-8')!!}</div>
                                                                     <div
                                                                         class="product_content">{!! ($proposal_template->testimonials_content)? html_entity_decode($proposal_template->testimonials_content, ENT_QUOTES, 'UTF-8') : html_entity_decode('<p><strong>This is pre-design or reference photo of your solar power plant.</strong></p>', ENT_QUOTES, 'UTF-8')!!}</div>
                                                                 </div>
                                                             </div>


                                                             <div class="p-0 row"
                                                                  style="padding-right: 30px !important;padding-left: 20px !important;">
                                                                 <div class="col-lg-12">
                                                                     <div class="card border-light border">
                                                                         <div class="row g-0 align-items-center">

                                                                             <div class="col-md-3 p-2">
                                                                                 <img
                                                                                     src="{{Storage::url($testimonials->image_one)}}"
                                                                                     class="card-img rounded-circle  avatar-lg img-thumbnail"
                                                                                     alt="..."
                                                                                     style="height: 11rem;width: 11rem;">
                                                                             </div>

                                                                             <div class="col-md-9">
                                                                                 <div class="card-body"
                                                                                      style="border: 0px solid #f1f3fa !important;">

                                                                                     <p class="card-text">
                                                                                         {{$testimonials->description_one}}</p>
                                                                                     <p class="m-0 d-inline-block font-16">
                                                                                     </p><h5
                                                                                         class="card-title">{{$testimonials->client_name_one}}</h5>
                                                                                     @for($x=0;$x<$testimonials->rating_one;$x++)
                                                                                         <span
                                                                                             class="text-warning mdi mdi-star"></span>
                                                                                     @endfor

                                                                                 </div> <!-- end card-body-->
                                                                             </div> <!-- end col -->

                                                                         </div> <!-- end row-->
                                                                     </div>
                                                                 </div> <!-- end col-->
                                                             </div>

                                                             <div class="p-0 row"
                                                                  style="padding-right: 30px !important;padding-left: 20px !important;">
                                                                 <div class="col-lg-12">
                                                                     <div class="card border-light border">
                                                                         <div class="row g-0 align-items-center">
                                                                             <div class="col-md-9">
                                                                                 <div class="card-body"
                                                                                      style="border: 0px solid #f1f3fa !important;">

                                                                                     <p class="card-text">
                                                                                         {{$testimonials->description_two}}</p>
                                                                                     <p class="m-0 d-inline-block font-16">
                                                                                     </p><h5
                                                                                         class="card-title">{{$testimonials->client_name_two}}</h5>
                                                                                     @for($x=0;$x<$testimonials->rating_two;$x++)
                                                                                         <span
                                                                                             class="text-warning mdi mdi-star"></span>
                                                                                     @endfor

                                                                                 </div> <!-- end card-body-->
                                                                             </div> <!-- end col -->

                                                                             <div class="col-md-3 p-2">
                                                                                 <img
                                                                                     src="{{Storage::url($testimonials->image_two)}}"
                                                                                     class="card-img rounded-circle  avatar-lg img-thumbnail"
                                                                                     alt="..."
                                                                                     style="height: 11rem;width: 11rem;">
                                                                             </div>

                                                                         </div> <!-- end row-->
                                                                     </div>
                                                                 </div> <!-- end col-->
                                                             </div>

                                                             <div class="p-0 row"
                                                                  style="padding-right: 30px !important;padding-left: 20px !important;">
                                                                 <div class="col-lg-12">
                                                                     <div class="card border-light border">
                                                                         <div class="row g-0 align-items-center">

                                                                             <div class="col-md-3 p-2">
                                                                                 <img
                                                                                     src="{{Storage::url($testimonials->image_three)}}"
                                                                                     class="card-img rounded-circle  avatar-lg img-thumbnail"
                                                                                     alt="..."
                                                                                     style="height: 11rem;width: 11rem;">
                                                                             </div>

                                                                             <div class="col-md-9">
                                                                                 <div class="card-body"
                                                                                      style="border: 0px solid #f1f3fa !important;">

                                                                                     <p class="card-text">
                                                                                         {{$testimonials->description_three}}</p>
                                                                                     <p class="m-0 d-inline-block font-16">
                                                                                     </p><h5
                                                                                         class="card-title">{{$testimonials->client_name_three}}</h5>
                                                                                     @for($x=0;$x<$testimonials->rating_three;$x++)
                                                                                         <span
                                                                                             class="text-warning mdi mdi-star"></span>
                                                                                     @endfor

                                                                                 </div> <!-- end card-body-->
                                                                             </div> <!-- end col -->

                                                                         </div> <!-- end row-->
                                                                     </div>
                                                                 </div> <!-- end col-->
                                                             </div>

                                                             --}}{{-- <div class="p-0 row"
                                                               style="padding-right: 30px !important;padding-left: 20px !important;">
                                                              <div class="col-lg-12">
                                                                  <div class="card border-light border">
                                                                      <div class="row g-0 align-items-center">
                                                                          @if($tk%2==0)
                                                                              <div class="col-md-3 p-2">
                                                                                  <img
                                                                                      src="{{Storage::url($testimonial->image_one)}}"
                                                                                      class="card-img rounded-circle  avatar-lg img-thumbnail"
                                                                                      alt="..."
                                                                                      style="height: 11rem;width: 11rem;">
                                                                              </div>
                                                                          @endif
                                                                          <div class="col-md-9">
                                                                              <div class="card-body"
                                                                                   style="border: 0px solid #f1f3fa !important;">

                                                                                  <p class="card-text">
                                                                                      {{$testimonial->description}}</p>
                                                                                  <p class="m-0 d-inline-block font-16">
                                                                                  </p><h5
                                                                                      class="card-title">{{$testimonial->name}}</h5>
                                                                                  @for($x=0;$x<$testimonial->rating;$x++)
                                                                                      <span
                                                                                          class="text-warning mdi mdi-star"></span>
                                                                                  @endfor

                                                                              </div> <!-- end card-body-->
                                                                          </div> <!-- end col -->
                                                                          @if($tk%2!=0)
                                                                              <div class="col-md-3 p-2">
                                                                                  <img
                                                                                      src="{{Storage::url($testimonial->image_one)}}"
                                                                                      class="card-img rounded-circle  avatar-lg img-thumbnail"
                                                                                      alt="..."
                                                                                      style="height: 11rem;width: 11rem;">
                                                                              </div>
                                                                          @endif
                                                                      </div> <!-- end row-->
                                                                  </div>
                                                              </div> <!-- end col-->
                                                          </div>--}}{{--


                                                         </div>

                                                     </div>

                                                 </div>--}}
                                            @endif
                                            {{--                                            @if($estimate->pdf_testimonial_flg)--}}
                                            <page size="A4">
                                                <table border="0" cellspacing="0" cellpadding="4" style="width: 100%;">
                                                    <tr>
                                                        <td colspan="3" style="text-align:center"><img
                                                                src="{{asset('assets/images/maintenance.svg')}}"
                                                                height="400."></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" style="text-align:center"><h1
                                                                style="text-align:left;font-size:25px;text-align:center">
                                                                Thank You!</h1></td>
                                                    </tr>
                                                    <tr><br><br>
                                                        <td style="border-right:.5px solid #dee2e6;text-align:center">
                                                            <h4 style="text-transform:uppercase">{{$company_data->company_name}}</h4>
                                                        </td>
                                                        <td style="border-right:.5px solid #dee2e6;text-align:center">
                                                            <h4 style="text-transform:uppercase">Mobile</h4></td>
                                                        <td style="text-align:center"><h4
                                                                style="text-transform:uppercase">Email</h4></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border-right:.5px solid #dee2e6;text-align:center">
                                                            <p>
                                                                F{{$company_data->address.','.$company_data->city_name.','.$company_data->state_name.','.$company_data->pincode}}</p>
                                                        </td>
                                                        <td style="border-right:.5px solid #dee2e6;text-align:center">
                                                            <p>{{$company_data->mobile_no}}</p></td>
                                                        <td style="text-align:center"><p>{{$company_data->email}}</p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </page>
                                            {{-- <div class="col-md-12">
                                                 <div class="main-page">
                                                     <div class="sub-page">
                                                         <div class="card">
                                                             <div class="card-body">
                                                                 <div class="p-0 row"
                                                                      style="margin-top:0px;text-align: center;">
                                                                     <div class="mt-2">
                                                                         <label for="inputGroupFile04"
                                                                                style="float:right;width: 100%;">
                                                                             <img
                                                                                 src="{{Storage::url(trim($proposal_template->header_logo))}}"
                                                                                 id="preview_image_container_product"
                                                                                 alt="Put logo Here"
                                                                                 style="float:right;padding-right: 2rem;width:{{$proposal_template->aboutas_logo_dimension}};"/>
                                                                         </label>
                                                                     </div>
                                                                 </div>
                                                                 <div class="col-xl-12">

                                                                     <div class="text-center">
                                                                         <img
                                                                             src="{{asset('assets/images/maintenance.svg')}}"
                                                                             height="300" alt="File not found Image">
                                                                         <h1 class="mt-4" style="font-weight: bold;">
                                                                             Thank
                                                                             You!</h1>


                                                                         <div class="row mt-5">
                                                                             <div class="col-md-4">
                                                                                 <div class="text-center mt-3 ps-1 pe-1">
                                                                                     <i class="dripicons-store maintenance-icon text-white mb-2"
                                                                                        style="background-color: {{$proposal_template->theme_color_two}}"></i>
                                                                                     <h5 class="text-uppercase">{{$company_data->company_name}}</h5>
                                                                                     <p class="text-muted">{{$company_data->address.','.$company_data->city_name.','.$company_data->state_name.','.$company_data->pincode}}</p>
                                                                                 </div>
                                                                             </div> <!-- end col-->
                                                                             <div class="col-md-4">
                                                                                 <div class="text-center mt-3 ps-1 pe-1">
                                                                                     <i class="dripicons-phone maintenance-icon text-white mb-2"
                                                                                        style="background-color: {{$proposal_template->theme_color_one}}"></i>
                                                                                     <h5 class="text-uppercase">
                                                                                         Mobile/Email</h5>
                                                                                     <p class="text-muted">{{$company_data->mobile_no}}
                                                                                         <br>
                                                                                         {{$company_data->email}}</p>
                                                                                 </div>
                                                                             </div> <!-- end col-->
                                                                             <div class="col-md-4">
                                                                                 <div class="text-center mt-3 ps-1 pe-1">
                                                                                     <i class="dripicons-link maintenance-icon text-white mb-2"
                                                                                        style="background-color: {{$proposal_template->theme_color_two}}"></i>
                                                                                     <h5 class="text-uppercase">Website
                                                                                         Link</h5>
                                                                                     <p class="text-muted">{{$company_data->website_link}}</p>
                                                                                 </div>
                                                                             </div> <!-- end col-->
                                                                         </div> <!-- end row-->
                                                                     </div> <!-- end /.text-center-->

                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </div>

                                                 </div>

                                             </div>--}}
                                            {{--                                            @endif--}}
                                        </div>
                                    </div>
                                    {{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        {{-- <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between estimate-form"
               id="estimate-form" method="post">
             <div class="row">
                 <div class="col-md-6">
                     <div class="card">
                         <div class="card-body">

                             <h5 class="mb-1 text-uppercase bg-light p-2"><i class="mdi mdi-office-building me-1"></i>
                                 Customer Info</h5>

                             <div class="row">
                                 <div class="col-md-12">
                                     <label for="customer_name" class="form-label"> <a href="#" id="customer_info"
                                                                                       data-bs-toggle="tooltip"
                                                                                       data-bs-html="true"
                                                                                       title="{{$estimate->customer_address}}"><i
                                                 class="mdi mdi-information"></i></a> Customer Name <span
                                             class="text-danger">*</span></label>
                                     <div class="mb-1">
                                         <input type="text" name="customer_name"
                                                class="form-control search_box customer_autocomplete"
                                                data-type="customers" id="customer_name"
                                                placeholder="Search and Add customers" onkeyup="javascript:customer();"
                                                value="{{$estimate->customer_name}}" required>
                                         <input type="hidden" name="customer_id" class="form-control"
                                                id="customer_id" value="{{$estimate->customer_id}}">
                                         <input type="hidden" name="customer_state_id"
                                                class="form-control customer_state_id"
                                                id="customer_state_id" value="{{$estimate->customer_state_id}}"
                                                data-id="{{Auth::user()->state_id}}">
                                         <input type="hidden" name="id" class="form-control"
                                                id="id" value="{{$estimate->id}}">
                                         <input type="hidden" name="customer_address" class="form-control"
                                                id="customer_address" value="{{$estimate->customer_address}}">
                                     </div>
                                 </div>
                                 <div class="col-md-12">
                                     <label for="customer_name" class="form-label">Item Rates Are</label>
                                     <div class="mb-1">
                                         <select class="form-select item_rate_are" id="item_rate_are"
                                                 name="item_rate_are">
                                             <option value="1" {{($estimate->item_rate_are==1)?'selected': ''}}>Tax
                                                 Exclusive
                                             </option>
                                             <option value="2" {{($estimate->item_rate_are==2)?'selected': ''}}>Tax
                                                 Inclusive
                                             </option>
                                         </select>
                                     </div>
                                 </div>
                             </div> <!-- end row -->

                         </div> <!-- end card body-->
                     </div>
                     <!-- end card -->
                 </div>

                 <div class="col-md-6">
                     <div class="card">
                         <div class="card-body">

                             <h5 class="mb-1 text-uppercase bg-light p-2"><i class="mdi mdi-office-building me-1"></i>
                                 Estimate Info</h5>
                             <div class="row">
                                 <div class="col-md-6">
                                     <label for="estimate_no" class="form-label"><a href="JavaScript:void(0);"
                                                                                    id="autogenetare-estimate"
                                                                                    class="autogenetare-estimate"
                                                                                    onclick="get_estimate_number();"
                                                                                    data-bs-toggle="tooltip"
                                                                                    aria-label="Click here to enable or disable auto-generation of Estimate numbers."
                                                                                    data-bs-html="true"
                                                                                    data-bs-original-title="Click here to enable or disable auto-generation of Estimate numbers."><i
                                                 class="dripicons-gear noti-icon mdi-18px"></i></a> Estimate# <span
                                             class="text-danger">*</span></label>
                                     <div class="mb-1">
                                         <input type="text" name="estimate_no"
                                                class="form-control" id="estimate_no"
                                                placeholder="Estimate no" value="{{$estimate->estimate_no}}" required>
                                     </div>
                                 </div>

                                 <div class="col-md-6">
                                     <label for="reference" class="form-label">Reference# </label>
                                     <div class="mb-1">
                                         <input type="text" name="reference"
                                                class="form-control" id="reference"
                                                placeholder="Reference" value="{{$estimate->reference}}">
                                     </div>
                                 </div>

                                 <div class="col-md-6">
                                     <label for="estimate_date" class="form-label">Estimate Date <span
                                             class="text-danger">*</span></label>
                                     <div class="mb-1">
                                         <div class="input-group">
                                             <input type="text" class="form-control form-control-light"
                                                    id="estimate_date" name="estimate_date"
                                                    data-provide="datepicker" data-single-date-picker="true"
                                                    data-date-autoclose="true" data-date-format="d/m/yyyy"
                                                    value="{{\Carbon\Carbon::parse($estimate->estimate_date)->format('d/m/Y')}}">
                                             <span class="input-group-text bg-primary border-primary text-white">
                                                     <i class="mdi mdi-calendar-range font-13"></i>
                                                 </span>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-6">
                                     <label for="expiry_date" class="form-label">Expiry Date <span
                                             class="text-danger">*</span></label>
                                     <div class="mb-1">
                                         <div class="input-group">
                                             <input type="text" name="expiry_date"
                                                    class="form-control form-control-light" id="expiry_date"
                                                    data-provide="datepicker" data-single-date-picker="true"
                                                    data-date-autoclose="true" data-date-format="d/m/yyyy"
                                                    value="{{\Carbon\Carbon::parse($estimate->expiry_date)->format('d/m/Y')}}">
                                             <span class="input-group-text bg-primary border-primary text-white">
                                             <i class="mdi mdi-calendar-range font-13"></i>
                                         </span>
                                         </div>
                                     </div>
                                 </div>
                             </div> <!-- end row -->
                         </div> <!-- end card body-->
                     </div>
                     <!-- end card -->
                 </div>
             </div>

             <div class="row">
                 <div class="col-md-12">
                     <div class="card">
                         <div class="card-body">
                             <h5 class="mb-1 text-uppercase bg-light p-2"><i class="mdi mdi-office-building me-1"></i>Item
                                 Info </h5>
                             <div class="table-responsive">
                                 <table class="table table-nowrap mb-0 table-sm">
                                     <thead class="table-light">
                                     <tr>
                                         <th>#</th>
                                         <th>Name</th>
                                         <th>Quantity</th>
                                         <th>Rate</th>
                                         <th>Discount</th>
                                         <th>Tax</th>
                                         <th>Amount</th>
                                         <th>Action</th>
                                     </tr>
                                     </thead>
                                     <tbody class="itemRow">

                                     @foreach($estimate_items as $key =>$estimate_item)

                                         <tr id="{{ ++$key }}">
                                             <td>{{ $key }}</td>
                                             <td>
                                                 <input type="text" class="form-control item_autocomplete"
                                                        onkeyup="javascript:item({{ $key }})"
                                                        name="data[{{ $key }}][item_name]" id="item_name_{{ $key }}"
                                                        data-type="itemName"
                                                        placeholder="Item Name" required=""
                                                        value="{{$estimate_item->item_name}}">
                                                 <input type="hidden" class="form-control" id="item_id_{{ $key }}"
                                                        name="data[{{ $key }}][item_id]"
                                                        value="{{$estimate_item->item_id}}">
                                                 <input type="hidden" class="form-control" id="hsn_code_{{ $key }}"
                                                        name="data[{{ $key }}][hsn_code]"
                                                        value="{{$estimate_item->hsn_code}}">
                                                 <textarea class="form-control item_description"
                                                           id="item_description_{{ $key }}"
                                                           name="data[{{ $key }}][item_description]"
                                                           placeholder="Add a description to your item">{{$estimate_item->item_description}}</textarea>
                                             </td>
                                             <td>
                                                 <input type="text" class="form-control quantity"
                                                        name="data[{{ $key }}][quantity]"
                                                        id="quantity_{{ $key }}" placeholder="Quantity" required=""
                                                        value="{{$estimate_item->quantity}}">
                                             </td>
                                             <td>
                                                 <input type="text" class="form-control price"
                                                        name="data[{{ $key }}][price]"
                                                        id="price_{{ $key }}"
                                                        placeholder="Rate" required="" value="{{$estimate_item->price}}">
                                             </td>
                                             <td class="input-group">
                                                 <input type="text" class="form-control discount"
                                                        name="data[{{ $key }}][discount]"
                                                        id="discount_{{ $key }}" placeholder="Discount" required=""
                                                        value="{{$estimate_item->discount}}">
                                                 <select class="btn-light discount_flag" id="discount_flag_{{ $key }}"
                                                         name="data[{{ $key }}][discount_flag]">
                                                     <option
                                                         value="1" {{($estimate_item->discount_flag==1)?'selected': ''}}>
                                                         %
                                                     </option>
                                                     <option
                                                         value="2" {{($estimate_item->discount_flag==2)?'selected': ''}}>
                                                         ₹
                                                     </option>
                                                 </select>

                                             </td>
                                             <td>
                                                 <select class="form-select gst_per" id="gst_per_{{ $key }}"
                                                         name="data[{{ $key }}][gst_per]">
                                                     <option value="0" {{($estimate_item->gst_per==0)?'selected': ''}}>
                                                         GST0 [0%]
                                                     </option>
                                                     <option value="5" {{($estimate_item->gst_per==5)?'selected': ''}}>
                                                         GST5 [5%]
                                                     </option>
                                                     <option value="12" {{($estimate_item->gst_per==12)?'selected': ''}}>
                                                         GST12 [12%]
                                                     </option>
                                                     <option value="18" {{($estimate_item->gst_per==18)?'selected': ''}}>
                                                         GST18 [18%]
                                                     </option>
                                                     <option value="28" {{($estimate_item->gst_per==28)?'selected': ''}}>
                                                         GST28 [28%]
                                                     </option>
                                                 </select>
                                                 <input type="hidden" class="form-control cgst_amount"
                                                        name="data[{{ $key }}][cgst_amount]"
                                                        id="cgst_amount_{{ $key }}"
                                                        value="{{$estimate_item->sgst_amount}}">
                                                 <input type="hidden" class="form-control sgst_amount"
                                                        name="data[{{ $key }}][sgst_amount]"
                                                        id="sgst_amount_{{ $key }}"
                                                        value="{{$estimate_item->sgst_amount}}">
                                                 <input type="hidden" class="form-control igst_amount"
                                                        name="data[{{ $key }}][igst_amount]"
                                                        id="igst_amount_{{ $key }}"
                                                        value="{{$estimate_item->igst_amount}}">
                                             </td>
                                             <td><input type="text" class="form-control total"
                                                        name="data[{{ $key }}][total]"
                                                        id="total_{{ $key }}"
                                                        placeholder="Total" required="" readonly
                                                        value="{{$estimate_item->total}}"></td>
                                             <td style="vertical-align: middle;">
                                                 <a href="JavaScript:void(0);" id="quotation_1"
                                                    class="text-danger remove"><i
                                                         class="mdi mdi-trash-can-outline mdi-18px"></i></a>
                                             </td>
                                         </tr>
                                     @endforeach
                                     </tbody>
                                 </table>
                             </div>
                             <div class="row pt-2">
                                 <div class="col-md-6">
                                     <a href="javascript:void(0);" id="add" class="text-secondary"
                                        style="vertical-align: middle!important;">
                                         <i class="mdi mdi-plus-circle-outline mdi-18px"></i> Add another line
                                     </a>
                                     <div class="pb-2 pt-2">
                                         <label for="description" class="form-label">Customer Notes</label>
                                         <textarea class="form-control" id="customer_notes" name="customer_notes"
                                                   placeholder="Will be displayed on the estimate">{{$estimate->customer_notes}}</textarea>
                                     </div>

                                 </div>
                                 <div class="col-md-6">
                                     <table class="table table-nowrap table-borderless table-light mb-0 table-sm">

                                         <tr>
                                             <th>Sub Total</th>
                                             <td>
                                                 <input type="text" class="form-control subtotal d-none" name="subtotal"
                                                        id="subtotal" required="" readonly
                                                        value="{{$estimate->subtotal}}">
                                                 <span class="subtotal_span">{{$estimate->subtotal}}</span>
                                             </td>
                                         </tr>

                                         <tr style="{{($estimate->customer_state_id==Auth::user()->state_id)?'': 'display: none'}}">
                                             <th>CGST</th>
                                             <td>
                                                 <input type="text" class="form-control total_cgst_amount d-none"
                                                        name="total_cgst_amount" id="total_cgst_amount" required=""
                                                        readonly value="{{$estimate->total_cgst_amount}}">
                                                 <span
                                                     class="total_cgst_amount_span">{{$estimate->total_cgst_amount}}</span>
                                             </td>
                                         </tr>

                                         <tr style="{{($estimate->customer_state_id==Auth::user()->state_id)?'': 'display: none'}}">
                                             <th>SGST</th>
                                             <td>
                                                 <input type="text" class="form-control total_sgst_amount d-none"
                                                        name="total_sgst_amount" id="total_sgst_amount" required=""
                                                        readonly value="{{$estimate->total_sgst_amount}}">
                                                 <span
                                                     class="total_sgst_amount_span">{{$estimate->total_sgst_amount}}</span>
                                             </td>
                                         </tr>

                                         <tr style="{{($estimate->customer_state_id==Auth::user()->state_id)?'display: none': ''}}">
                                             <th>IGST</th>
                                             <td>
                                                 <input type="text" class="form-control total_igst_amount d-none"
                                                        name="total_igst_amount" id="total_igst_amount" required=""
                                                        readonly value="{{$estimate->total_igst_amount}}">
                                                 <span
                                                     class="total_igst_amount_span">{{$estimate->total_igst_amount}}</span>
                                             </td>
                                         </tr>

                                         <tr>
                                             <th class="input-group">
                                                 <input type="text" class="form-control addless_title"
                                                        name="addless_title"
                                                        id="addless_title" value="{{$estimate->addless_title}}"
                                                        style="padding-right: 15px; ">
                                                 <input type="text" class="form-control addless_amount"
                                                        name="addless_amount"
                                                        id="addless_amount" value="{{$estimate->addless_amount}}">
                                             </th>
                                             <td><span class="addless_amount_span">{{$estimate->addless_amount}}</span>
                                             </td>
                                         </tr>

                                         <tr>
                                             <th>Total (<i class="mdi mdi-currency-inr"></i>)</th>
                                             <td>
                                                 <input type="text" class="form-control net_amount d-none"
                                                        name="net_amount"
                                                        id="net_amount" required="" readonly
                                                        value="{{$estimate->net_amount}}">
                                                 <span class="net_amount_span">{{$estimate->net_amount}}</span>
                                             </td>
                                         </tr>
                                     </table>
                                 </div>

                                 <div class="col-md-6">

                                     <div class="pb-3">
                                         <label for="description" class="form-label">Terms & Conditions</label>
                                         <textarea class="form-control term_condition" id="term_condition"
                                                   name="term_condition"
                                                   placeholder="Enter the terms and conditions of your business to be displayed in your transaction">{{$estimate->term_condition}}</textarea>
                                     </div>

                                 </div>

                             </div>
                             <div class="mb-1 text-end">
                                 <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close
                                 </button>
                                 <button class="btn btn-primary" id="item_button" form="estimate-form" type="submit"><i
                                         class="uil-arrow-circle-right"></i> Save
                                 </button>
                             </div>
                         </div> <!-- end card body-->

                     </div>
                     <!-- end card -->
                 </div>
             </div>

         </form>--}}


    </div>

    {{-- <div id="customer-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
         <div class="modal-dialog modal-lg modal-center">
             <div class="modal-content">
                 <div class="modal-header border-1 bg-light">
                     <h4 class="modal-title">Create Customer</h4>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body">
                     <form class="ps-3 pe-3 customer-form" id="customer-form" action="#">
                         <div class="row">
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>
                                     <div class="form-check form-check-inline">
                                         <input type="radio" id="customer_type_business" name="customer_type"
                                                class="form-check-input" value="Business">
                                         <label class="form-check-label"
                                                for="customer_type_business">Business</label>
                                     </div>
                                     <div class="form-check form-check-inline">
                                         <input type="radio" id="customer_type_individual" name="customer_type"
                                                class="form-check-input" value="Individual" checked>
                                         <label class="form-check-label"
                                                for="customer_type_individual">Individual</label>
                                     </div>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="name" class="form-label">Name <span
                                             class="text-danger">*</span></label>
                                     <input class="form-control" type="text" id="name" name="name" required=""
                                            placeholder="Enter name" autofocus>
                                     <input class="form-control" type="hidden" id="id" name="id" value="0">
                                 </div>
                             </div>
                             <div class="col-md-12 cust_company_name_div">
                                 <div class="mb-1">
                                     <label for="name" class="form-label">Company Name <span
                                             class="text-danger">*</span></label>
                                     <input class="form-control" type="text" id="company_name" name="company_name"
                                            placeholder="Enter company name">
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="customer_category_id" class="form-label">Lead Category</label>
                                     <select class="form-select" id="customer_category_id" name="customer_category_id">
                                         <option value=0>Choose</option>
                                         @foreach($customerCategories as $customerCategory)
                                             <option value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="customer_lead_id" class="form-label">Lead Source</label>
                                     <select class="form-select" id="customer_lead_id" name="customer_lead_id">
                                         <option value=0>Choose</option>
                                         @foreach($customerLeads as $customerLead)
                                             <option value="{{$customerLead->id}}">{{$customerLead->name}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="email" class="form-label">Email</label>
                                     <input class="form-control" type="email" id="email" name="email"
                                            placeholder="Enter email">
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="phone_no" class="form-label">Phone no <span
                                             class="text-danger">*</span></label>
                                     <input class="form-control" type="text" id="phone_no" name="phone_no"
                                            required="" placeholder="Enter phone no">
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <div class="mb-1">
                                     <label for="address" class="form-label">Address</label>
                                     <textarea class="form-control" id="address" name="address"
                                               placeholder="Enter address"></textarea>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="pincode" class="form-label">Pincode</label>
                                     <input class="form-control" type="text" id="pincode" name="pincode"
                                            placeholder="Enter pincode">
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="country_id" class="form-label">Country <span
                                             class="text-danger">*</span></label>
                                     <select class="form-select" id="country_id" name="country_id" required="">
                                         <option value="">Choose</option>
                                         @php
                                             $expData = App\Helpers\PermissionCheck::plan_details_check();
                                         @endphp
                                         @foreach($countries as $country)
                                             <option value="{{$country->id}}"
                                                     @if($country->id==$expData->country_id) selected @endif>{{$country->name}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1">
                                     <label for="state_id" class="form-label">State <span
                                             class="text-danger">*</span></label>
                                     <select class="form-select" id="state_id" name="state_id" required="">
                                         <option value="">Choose</option>
                                     </select>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-1 d-none">
                                     <label for="city_id" class="form-label">City</label>
                                     <select class="form-select" id="city_id" name="city_id">
                                         <option value="0">Choose</option>
                                     </select>
                                 </div>
                                 <div class="mb-1">
                                     <label for="city_name" class="form-label">City</label>
                                     <input class="form-control" type="text" id="city_name" name="city_name" placeholder="Enter city">
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <div class="mb-1">
                                     <label for="gst_no" class="form-label">GSTIN</label>
                                     <input class="form-control" type="text" id="gst_no" name="gst_no"
                                            placeholder="Enter gstin">
                                 </div>
                             </div>

                         </div>

                         <div class="mb-1">
                             <label for="description" class="form-label">Description</label>
                             <textarea class="form-control" id="description" name="description"
                                       placeholder="Enter description"></textarea>
                         </div>

                         <div class="mb-1 text-end">
                             <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close
                             </button>
                             <button class="btn btn-secondary" id="customer_button" type="submit"><i
                                     class="uil-arrow-circle-right"></i> Save
                             </button>
                         </div>

                     </form>
                 </div>
             </div><!-- /.modal-content -->
         </div><!-- /.modal-dialog -->
     </div>--}}<!-- /.modal -->

    <div id="customer-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Create Lead</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="customer-form" id="customer-form" action="#">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <h6 class="form-label font-14">Type <span class="text-danger">*</span></h6>
                                    <div class="form-check form-check-inline">
                                        <input class="form-control" type="hidden" id="id" name="id" value="0">
                                        <input type="radio" id="customer_type_business" name="customer_type"
                                               class="form-check-input" value="Business">
                                        <label class="form-check-label"
                                               for="customer_type_business">Business</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="customer_type_individual" name="customer_type"
                                               class="form-check-input" value="Individual" checked>
                                        <label class="form-check-label"
                                               for="customer_type_individual">Individual</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 cust_company_name_div">
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="text" id="company_name"
                                           name="company_name"
                                           placeholder="Company name">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-1">
                                            <input type="text" class="form-control bg-light text-dark" id="name"
                                                   name="name"
                                                   required=""
                                                   placeholder="Name">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating mb-1">
                                            <input type="text" class="form-control bg-light text-dark" id="phone_no"
                                                   name="phone_no" required=""
                                                   placeholder="Phone no">
                                            <label for="phone_no" class="form-label">Phone no <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-floating">
                                    <textarea class="form-control bg-light text-dark" id="description"
                                              name="description"
                                              placeholder="Enter description" style="height: 80px;"></textarea>
                                            <label for="description" class="form-label">Description</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select bg-light text-dark" id="customer_category_id"
                                                    name="customer_category_id">
                                                <option value=0>Choose</option>
                                                @foreach($customerCategories as $customerCategory)
                                                    <option
                                                        value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>
                                                @endforeach
                                            </select>
                                            <label for="customer_category_id" class="form-label">Lead Category</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select bg-light text-dark" id="customer_lead_id"
                                                    name="customer_lead_id">
                                                <option value=0>Choose</option>
                                                @foreach($customerLeads as $customerLead)
                                                    <option
                                                        value="{{$customerLead->id}}">{{$customerLead->name}}</option>
                                                @endforeach
                                            </select>
                                            <label for="customer_lead_id" class="form-label">Lead Source</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-floating">
                                    <textarea class="form-control bg-light text-dark" id="description"
                                              name="description"
                                              placeholder="Enter description" style="height: 80px;"></textarea>
                                            <label for="description" class="form-label">Description</label>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="text-uppercase">Advance Options <a href="javascript: void(0);"
                                                                              class="advance-option text-primary"
                                                                              style="text-transform: initial !important;">Click
                                        to show </a></h5>
                                {{--                                <span class="form-text text-dark text-uppercase mb-1 mt-1" style="font-weight: bold !important;font-size: 1rem !important;"></span>--}}
                            </div>
                        </div>

                        <div class="row advance-option-div d-none">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="email" id="email" name="email"
                                           placeholder="Enter email">
                                    <label for="email" class="form-label">Email</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control bg-light text-dark" id="address" name="address"
                                              placeholder="Enter address" style="height: 100px;"></textarea>
                                    <label for="address" class="form-label">Address</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="text" id="pincode"
                                                   name="pincode"
                                                   placeholder="Enter pincode">
                                            <label for="pincode" class="form-label">Pincode</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select bg-light text-dark" id="country_id"
                                                    name="country_id"
                                                    required="">
                                                <option value="">Choose</option>
                                                @php
                                                    $expData = App\Helpers\PermissionCheck::plan_details_check();
                                                @endphp

                                                @foreach($countries as $country)
                                                    <option value="{{$country->id}}" data-id="{{$country->phonecode}}"
                                                            @if($country->id==$expData->country_id) selected @endif>{{$country->name}}</option>
                                                @endforeach
                                            </select>
                                            <label for="country_id" class="form-label">Country <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select bg-light text-dark" id="state_id" name="state_id"
                                                    required="">
                                                <option value="">Choose</option>
                                            </select>
                                            <label for="state_id" class="form-label">State <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating bg-light mb-3 d-none">
                                            <select class="form-select text-dark" id="city_id" name="city_id">
                                                <option value="0">Choose</option>
                                            </select>
                                            <label for="city_id" class="form-label">City</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control bg-light text-dark" type="text" id="city_name"
                                                   name="city_name"
                                                   placeholder="Enter city">
                                            <label for="city_name" class="form-label">City</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input class="form-control bg-light text-dark" type="text" id="gst_no" name="gst_no"
                                           placeholder="Enter gstin">
                                    <label for="gst_no" class="form-label">GSTIN</label>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary" form="customer-form" id="customer_button" type="submit">
                            <i
                                class="uil-arrow-circle-right"></i> Save
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

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
                                <input type="radio" id="tax_preference_taxable" name="tax_preference" value="Taxable"
                                       class="form-check-input">
                                <label class="form-check-label" for="tax_preference_taxable">Taxable</label>
                            </div>
                        </div>

                        <div class="mb-1 tax-div" style="display:none">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="fil_status" class="me-2">Tax (GSTIN)</label>
                                    <select class="form-select" id="inter_state" name="inter_state">
                                        <option value="0">GST0 [0%]</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}">GST{{$tax->name}} [{{$tax->name}}%]</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 d-none">
                                    <label for="fil_status" class="me-2">Intra State</label>
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
                                    <div class="form-check mb-1">
                                        <input type="checkbox" class="form-check-input" id="purchase_flag"
                                               name="purchase_flag" value="1">
                                        <label class="form-check-label" for="purchase_flag">Purechase Info</label>
                                    </div>

                                    <label for="name" class="form-label">Cost Price</label>
                                    <input class="form-control" type="text" id="cost_price" name="cost_price"
                                           placeholder="Enter purchase price" value="0">
                                </div>

                            </div>


                        </div>

                        <div class="mb-1">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                      placeholder="Enter description"></textarea>
                        </div>

                        <div class="mb-1 text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close
                            </button>
                            <button class="btn btn-secondary" id="item_button" type="submit"><i
                                    class="uil-arrow-circle-right"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="estimate-auto-number-modal" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static"
         data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-center">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Estimate Number</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="estimate-auto-number-form" id="estimate-auto-number-form" action="#"
                          autocomplete="off">
                        <div class="mb-1">
                            <h5>ASSOCIATED SERIES</h5>
                            <p>Default Transaction Series</p>
                            <p>Your estimate numbers are set on auto-generate mode to save your time. Are you sure
                                about changing this setting?</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label for="name" class="form-label">Prefix <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="estimate_prefix" class="form-control" id="estimate_prefix"
                                           value="" required data-parsley-pattern-message="Please use just letters, digits, and hyphens(dashes), and make sure the last character is a hyphen(dashes)." data-parsley-trigger="input" data-parsley-pattern="^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*-$">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label for="name" class="form-label">Next Number <span class="text-danger">*</span></label>
                                    <input type="text" name="estimate_next_no" class="form-control"
                                           id="estimate_next_no" value="" required data-parsley-type="number" data-parsley-type="number" data-parsley-pattern-message="Please use just letters, digits, and hyphens(dashes), and make sure the last character is a hyphen(dashes)." data-parsley-trigger="input" data-parsley-pattern="/^[0-9]+$/">
                                </div>
                            </div>
                        </div>

                    </form>

                </div><!-- /.modal-content -->
                <div class="modal-footer">
                    <div class="text-start">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-dark" id="estimate_auto_number_button" type="submit"
                                form="estimate-auto-number-form"><i
                                class="uil-arrow-circle-right"></i> Save
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-dialog -->
        </div>
    </div><!-- /.modal -->

    <div id="product-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-right" style="width: 100%;">
            <div class="modal-content" style="height: 100%;">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Create Product</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="ps-3 pe-3 product-form" id="product-form" action="#" enctype="multipart/form-data">

                        <div class="mb-1">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="name" name="name" required=""
                                   placeholder="Enter name" autofocus>
                            <input class="form-control" type="hidden" id="id" name="id" value="0">
                        </div>

                        <div class="mb-1">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                      placeholder="Enter description"></textarea>
                        </div>

                        <div class="mb-1">
                            <label for="image_one" class="form-label">Product Image 1 <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" data-parsley-trigger="change" name="image_one"
                                   id="image_one" data-parsley-required="false"
                                   accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                   data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024"
                                   required>
                            <small>Rendered size: 2160 x 1440 ( maximum 1 MB )</small>
                        </div>

                        <div class="mb-1">
                            <label for="image_two" class="form-label">Product Image 2 <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" data-parsley-trigger="change" name="image_two"
                                   id="image_two" data-parsley-required="false"
                                   accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                   data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024"
                                   required>
                            <span class="form-text text-muted"><small>Rendered size: 640 x 480 ( maximum 1 MB )</small></span>
                        </div>

                        <div class="mb-1">
                            <label for="image_three" class="form-label">Product Image 3 <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" data-parsley-trigger="change" name="image_three"
                                   id="image_three" data-parsley-required="false"
                                   accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                   data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024"
                                   required>
                            <span class="form-text text-muted"><small>Rendered size: 640 x 480 ( maximum 1 MB )</small></span>
                        </div>

                        <div class="mb-1 text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-secondary" id="product_button" type="submit"><i
                                    class="uil-arrow-circle-right"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="testimonial-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
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
                                    <span
                                        class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
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
                                    <span
                                        class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
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
                                    <span
                                        class="form-text text-muted"><small>Rendered size: 256 x 256 ( maximum 1 MB )</small></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="mb-1 mt-3 text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-secondary" id="unit_button" type="submit"><i
                                    class="uil-arrow-circle-right"></i> Save
                            </button>
                        </div>

                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="update-technical-specification-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Item Specification</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="update-technical-specification-form" id="update-technical-specification-form" action="#">
                        <div class="row">
                            <div class="col-12">
                                <div class="col-md-12">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="hidden" id="temp_id" name="temp_id" value=""/>
                                    <textarea class="form-control bg-light text-dark" id="item_technical_specification_modal"
                                              name="item_technical_specification_modal" placeholder="Enter description" style="height: 80px;"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary" form="update-technical-specification-form" id="technical_button" type="submit">
                            <i class="uil-arrow-circle-right"></i> Update
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal" id="image-one-modal" tabindex="-1" role="dialog"  aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Photo</h4>
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
                    <button type="button" class="btn btn-primary" id="crop_one" data-id="0" data-type="0">Crop</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="image-two-modal" tabindex="-1" role="dialog"  aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h4 class="modal-title">Upload Photo</h4>
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
                    <button type="button" class="btn btn-primary" id="crop_two" data-id="0" data-type="0">Crop</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/vendor.min.js')}}"></script>
    <script src="{{ asset('assets/js/app.min.js')}}"></script>

    <script src="{{ asset('assets/js/custom.js')}}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js')}}"></script>
    <script src="{{ asset('ckeditor/ckeditor.js')}}"></script>
    <!-- third party js ends -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                    $('#imagePreview').hide();
                    $('#imagePreview').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(function () {
            var $modal_one = $('#image-one-modal');
            var cropper; // Declare cropper in a higher scope

            // Function to initialize cropper
            function initializeCropper(image) {
                cropper = new Cropper(image, {
                    restore: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    toggleDragModeOnDblclick: false,
                    aspectRatio: 2160/1440,
                    minCropBoxWidth: 2160,
                    minCropBoxHeight: 1440,
                    maxContainerHeight: 1440,
                    maxContainerWidth: 2160,
                    viewMode: 0,
                    scalable: false,
                    cropBoxResizable: false,
                    zoomable: true,
                    cropBoxMovable: true,
                    dragMode: 'move',
                    preview: '.image_one_preview'
                });
            }

            // Function to initialize cropper
            function initializeCropperSmall(image) {
                cropper = new Cropper(image, {
                    restore: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    toggleDragModeOnDblclick: false,
                    aspectRatio: 640/480,
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
                    preview: '.image_two_preview'
                });
            }

            $("body").on("change", ".file-input", function(e){
                var files = e.target.files;
                var image = document.getElementById('image_ones');
                var fileId = $(this).attr('id');
                var dataId = $(this).parent().data('id');
                var dataType = $(this).data('type');
                // alert(dataId);

                var done = function (url) {
                    image.src = url;
                    $("#crop_one").attr('data-id',dataId);
                    $("#crop_one").attr('data-type',dataType);
                    $modal_one.modal('show');
                    // Initialize cropper after image is shown
                    initializeCropper(image);
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

            $("#crop_one").click(function(){
                let dataId = $(this).attr('data-id');
                let dataType = $(this).attr('data-type');
                if (cropper) {
                    var canvas = cropper.getCroppedCanvas({
                        width: 2160,
                        height: 1440,
                        maxContainerHeight: 1440,
                        maxContainerWidth: 2160,
                        fillColor: '#fff',
                        imageSmoothingEnabled: false,
                        imageSmoothingQuality: 'high'
                    });
                    if (canvas) {
                        canvas.toBlob(function(blob) {
                            var reader = new FileReader();
                            reader.readAsDataURL(blob);
                            reader.onloadend = function() {
                                var base64data_one = reader.result;
                                var xa = $("#one_photo_"+dataId).closest('tr').attr('id');
                                console.log('abc : '+xa);
                                if($('#product_flg_'+xa).is(":checked")){
                                    $.ajax({
                                        type: "POST",
                                        dataType: "json",
                                        url: "{{route('EstimateProductStore')}}",
                                        data: {
                                            '_token': $('meta[name="csrf-token"]').attr('content'),
                                            'image': base64data_one,
                                            'id':$("#tmp_product_id_"+dataId).val(),
                                            'img_type' :dataType
                                        },
                                        beforeSend: function () {
                                            $("#crop").prop('disabled', true);
                                            $("#crop").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                                        },
                                        success: function (data) {
                                            $("#cover_img").val(null);
                                            // console.log(data);
                                            $modal_one.modal('hide');
                                            toastrSuccess('Photo successfully uploaded...', 'Success');
                                        },
                                        complete: function (data) {
                                            $("#crop").html('Crop & Upload');
                                            $("#crop").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                                        }
                                    });
                                }


                                $("#h_image_"+dataId).val(base64data_one);
                                $('#img_thumbnail_'+dataId).attr('src', base64data_one);
                                $modal_one.modal('hide');
                                // Do something with base64data_one
                            };
                        }, 'image/jpeg');
                    }
                }
            });

            $modal_one.on('hidden.bs.modal', function (){
                if (cropper) {
                    cropper.destroy();
                }
            });


            //Second image popup

            var $modal_two = $('#image-two-modal');
            var cropper; // Declare cropper in a higher scope

            $("body").on("change", ".file-input-small", function(e){
                var files = e.target.files;
                var image = document.getElementById('image_twos');
                var fileId = $(this).attr('id');
                var dataId = $(this).parent().data('id');
                var dataType = $(this).data('type');
                // alert(dataId);

                var done = function (url) {
                    image.src = url;
                    $("#crop_two").attr('data-id',dataId);
                    $("#crop_two").attr('data-type',dataType);
                    $modal_two.modal('show');
                    // Initialize cropper after image is shown
                    initializeCropperSmall(image);
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

            $("#crop_two").click(function(){
                let dataId = $(this).attr('data-id');
                let dataType = $(this).attr('data-type');
                if (cropper) {
                    var canvas = cropper.getCroppedCanvas({
                        width: 480,
                        height: 640,
                        maxContainerHeight: 480,
                        maxContainerWidth: 640,
                        fillColor: '#fff',
                        imageSmoothingEnabled: false,
                        imageSmoothingQuality: 'high'
                    });
                    if (canvas) {
                        canvas.toBlob(function(blob) {
                            var reader = new FileReader();
                            reader.readAsDataURL(blob);
                            reader.onloadend = function() {
                                var base64data_one = reader.result;
                                var xa = $("#one_photo_"+dataId).closest('tr').attr('id');
                                console.log('abc : '+xa);
                                if($('#product_flg_'+xa).is(":checked")){
                                    $.ajax({
                                        type: "POST",
                                        dataType: "json",
                                        url: "{{route('EstimateProductStore')}}",
                                        data: {
                                            '_token': $('meta[name="csrf-token"]').attr('content'),
                                            'image': base64data_one,
                                            'id':$("#tmp_product_id_"+xa+"1").val(),
                                            'img_type' :dataType
                                        },
                                        beforeSend: function () {
                                            $("#crop").prop('disabled', true);
                                            $("#crop").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                                        },
                                        success: function (data) {
                                            $("#cover_img").val(null);
                                            // console.log(data);
                                            $modal_two.modal('hide');
                                            toastrSuccess('Photo successfully uploaded...', 'Success');
                                        },
                                        complete: function (data) {
                                            $("#crop").html('Crop & Upload');
                                            $("#crop").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                                        }
                                    });
                                }
                                $("#h_image_"+dataId).val(base64data_one);
                                $('#img_thumbnail_'+dataId).attr('src', base64data_one);
                                $modal_two.modal('hide');
                                // Do something with base64data_one
                            };
                        }, 'image/jpeg');
                    }
                }
            });

            $modal_two.on('hidden.bs.modal', function (){
                if (cropper) {
                    cropper.destroy();
                }
            });

        });
        CKEDITOR.on('instanceReady', function () {
            $('#est_cover_page_title').attr('required', '');
            $('#est_cover_page_content').attr('required', '');
            $('#est_cover_page_footer_one').attr('required', '');
            $('#est_cover_page_footer_two').attr('required', '');
            $('#est_aboutus_content').attr('required', '');
            $('#est_aboutus_title').attr('required', '');
            // $('#est_term_condition_title').attr('required', '');
            $('#est_term_condition_content').attr('required', '');
            // $('#term_condition').attr('required', '');
            // $('#purpose').attr('required', '');
            $.each(CKEDITOR.instances, function (instance) {
                CKEDITOR.instances[instance].on("change", function (e) {
                    for (instance in CKEDITOR.instances) {
                        CKEDITOR.instances[instance].updateElement();
                        $('form').parsley();
                    }
                });
            });
        });

        CKEDITOR.replace('est_aboutus_title', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('est_aboutus_content', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['companies.company_name'],
                format: '${%placeholder%}'
            }
        });

        CKEDITOR.replace('est_term_condition_title', {
            extraPlugins: 'editorplaceholder',
        });

        CKEDITOR.replace('est_term_condition_content', {
            extraPlugins: 'editorplaceholder',
        });

        // CKEDITOR.replace('term_condition', {
        //     extraPlugins: 'editorplaceholder',
        // });

        CKEDITOR.replace('est_cover_page_title', {
            extraPlugins: 'editorplaceholder',

        });

        CKEDITOR.replace('est_cover_page_content', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['customers.company_name', 'customers.name', 'customers.address', 'customers.pincode', 'customers.country_name', 'customers.state_name', 'customers.city_name'],
                format: '${%placeholder%}'
            },


        });

        CKEDITOR.replace('est_cover_page_footer_one', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['companies.name', 'companies.company_name', 'companies.address', 'companies.pincode', 'companies.country_name', 'companies.state_name', 'companies.city_name'],
                format: '${%placeholder%}'
            }
        });

        CKEDITOR.replace('est_cover_page_footer_two', {
            extraPlugins: 'editorplaceholder',
            placeholder_select: {
                placeholders: ['estimates.estimate_no', 'estimates.estimate_date', 'estimates.sales_person_id', 'customers.lead_category'],
                format: '${%placeholder%}'
            }
        });


        $(document).ready(function () {
            $('#est_currency_id').select2({
                dropdownParent: $('#sel_cn')
            }).on('select2:select', function (e) {
                var data = e.params.data;
                var selectedOption = $(this).find(':selected');
                var dataId = selectedOption.data('id');
                var dataType = selectedOption.data('type');

                $('.customer_currency').html('('+data.id+')');
                $('#tmp_customer_currency').val(dataType);
                $('.discount_flag option[value="2"]').text(dataType);
                // $('#whatsapp_country_code').val(data.id).change();
                // $("#whatsapp_country_code").val($('option:selected', this).data('id'));
            });

            CKEDITOR.replace('item_technical_specification_modal', {
                extraPlugins: 'editorplaceholder',
            });
            $(document).on('click', '.update_technical_specification', function () {
                /* $('.update_technical_specification').click(
                     function(){*/
                let id = $(this).data('id');
                $("#temp_id").val(id);
                let ts = $("#item_technical_specification_"+id).val();
                // $(modalForm).parsley().reset();
                // CKEDITOR.instances.item_technical_specification_modal.setData('');
                CKEDITOR.instances.item_technical_specification_modal.setData(ts);
                $("#update-technical-specification-modal").modal('show');
                //alert(id);
            });

            $(".advance-option").click(function () {
                //Do stuff when clicked
                $(".advance-option").addClass('d-none');
                $(".advance-option-div").removeClass('d-none');
            });
            $("#pdf_product_flg").click(function () {
                $('.product_autocomplete').prop('required', false);
                if ($(this).is(':checked')) {
                    $('.product_autocomplete').prop('required', true);
                }
            });

            $("#pdf_testimonial_flg").click(function () {
                $('.testimonial_autocomplete').prop('required', false);
                if ($(this).is(':checked')) {
                    $('.testimonial_autocomplete').prop('required', true);
                }
            });

            $("#pdf_terms_flg").click(function () {
                $('#term_condition_id').prop('required', false);
                if ($(this).is(':checked')) {
                    $('#term_condition_id').prop('required', true);
                }
            });

            $("input[name='customer_type']").click(function () {
                if ($("#customer_type_business").is(":checked")) {
                    $(".cust_company_name_div").show();
                    $("#company_name").prop("required", true);
                } else {
                    $(".cust_company_name_div").hide();
                    $("#company_name").prop("required", false);
                }
            });
            $('#estimate_no').change(function () {
                var tmp_est_cover_page_footer_two = CKEDITOR.instances.est_cover_page_footer_two.getData();
                const matches = tmp_est_cover_page_footer_two.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                tmp_est_cover_page_footer_two = tmp_est_cover_page_footer_two.replace("${estimates.estimate_no}", $(this).val());
                $("#footer_two_div").html(tmp_est_cover_page_footer_two);

                var d = moment($('#estimate_date').val(), 'DD/MM/YYYY');
                var w = d.format('DD MMM, YYYY');
                tmp_est_cover_page_footer_two = tmp_est_cover_page_footer_two.replace("${estimates.estimate_date}", w);
                $("#footer_two_div").html(tmp_est_cover_page_footer_two);
            });

            $('#estimate_date').change(function () {
                var tmp_est_cover_page_footer_two = CKEDITOR.instances.est_cover_page_footer_two.getData();
                const matches = tmp_est_cover_page_footer_two.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                tmp_est_cover_page_footer_two = tmp_est_cover_page_footer_two.replace("${estimates.estimate_no}", $(this).val());
                $("#footer_two_div").html(tmp_est_cover_page_footer_two);

                var d = moment($('#estimate_date').val(), 'DD/MM/YYYY');
                var w = d.format('DD MMM, YYYY');
                tmp_est_cover_page_footer_two = tmp_est_cover_page_footer_two.replace("${estimates.estimate_date}", w);
                $("#footer_two_div").html(tmp_est_cover_page_footer_two);
            });


            var est_cover_page_title = CKEDITOR.instances.est_cover_page_title.getData();
            const matches_page_title = est_cover_page_title.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

            if (matches_page_title && matches_page_title.length > 0) {

                $.ajax({
                    async: false,
                    url: "{{route('quotes.estimatePdfInfo')}}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        matches: matches,
                        customer_id: $('#customer_id').val(),
                        estimate_id: $('#id').val()
                    },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            est_cover_page_title = est_cover_page_title.replace("${" + key + "}", value);
                        });

                    }
                });
            }
            $(".proposal_title").html(est_cover_page_title);


            //Start cover Page content
            var cover_main_text = CKEDITOR.instances.est_cover_page_content.getData();
            const matches = cover_main_text.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

            if (matches && matches.length > 0) {
                $.ajax({
                    async: false,
                    url: "{{route('quotes.estimatePdfInfo')}}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        matches: matches,
                        customer_id: $('#customer_id').val(),
                        estimate_id: $('#id').val()
                    },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            var st = '';
                            if (value != null) {
                                st = value;
                            }
                            cover_main_text = cover_main_text.replace("${" + key + "}", st);
                        });
                    }
                });
            }
            $("#partial_div_bg").html(cover_main_text);


            var est_cover_page_footer_one = CKEDITOR.instances.est_cover_page_footer_one.getData();
            const matches_est_cover_page_footer_one = est_cover_page_footer_one.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

            if (matches_est_cover_page_footer_one && matches_est_cover_page_footer_one.length > 0) {

                $.ajax({
                    async: false,
                    url: "{{route('quotes.estimatePdfInfo')}}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        matches: matches_est_cover_page_footer_one,
                        customer_id: $('#customer_id').val(),
                        estimate_id: $('#id').val()
                    },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            let tmp_name = '';
                            if (value && value != null)
                                tmp_name = value;
                            est_cover_page_footer_one = est_cover_page_footer_one.replace("${" + key + "}", tmp_name);
                        });

                    }
                });
            }
            $("#footer_one_div").html(est_cover_page_footer_one);


            var est_cover_page_footer_two = CKEDITOR.instances.est_cover_page_footer_two.getData();
            const matches_est_cover_page_footer_two = est_cover_page_footer_two.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

            if (matches_est_cover_page_footer_two && matches_est_cover_page_footer_two.length > 0) {

                $.ajax({
                    async: false,
                    url: "{{route('quotes.estimatePdfInfo')}}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        matches: matches_est_cover_page_footer_two,
                        customer_id: $('#customer_id').val(),
                        estimate_id: $('#id').val()
                    },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            est_cover_page_footer_two = est_cover_page_footer_two.replace("${" + key + "}", value);
                        });

                    }
                });
            }
            $("#footer_two_div").html(est_cover_page_footer_two);


            /*CKEDITOR.instances.est_cover_page_content.on('change', function () {
                var cover_main_text = this.getData();
                var cover_main_text = this.getData();
                // const matches = cover_main_text.match(/\{.+?\}/g); // ["[name]", "[age]", "[profession]"]
                const matches = cover_main_text.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                if (matches && matches.length > 0) {

                    $.ajax({
                        async: false,
                        url: "{{route('quotes.estimatePdfInfo')}}",
                        type: "GET",
                        dataType: "json",
                        data: {
                            matches: matches,
                            customer_id: $('#customer_id').val(),
                            estimate_id: $('#id').val()
                        },
                        success: function (data) {
                            $.each(data, function (key, value) {
                                var st ='';
                                if(value!=null){
                                    st = value;
                                }
                                cover_main_text = cover_main_text.replace("${" + key + "}", st);
                            });

                        }
                    });
                }
                $("#partial_div_bg").html(cover_main_text);
            });*/

            /*CKEDITOR.instances.est_cover_page_footer_one.on('change', function () {
                var est_cover_page_footer_one = this.getData();
                const matches = est_cover_page_footer_one.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                if (matches && matches.length > 0) {

                    $.ajax({
                        async: false,
                        url: "{{route('quotes.estimatePdfInfo')}}",
                        type: "GET",
                        dataType: "json",
                        data: {
                            matches: matches,
                            customer_id: $('#customer_id').val(),
                            estimate_id: $('#id').val()
                        },
                        success: function (data) {
                            $.each(data, function (key, value) {
                                est_cover_page_footer_one = est_cover_page_footer_one.replace("${" + key + "}", value);
                            });

                        }
                    });
                }
                $("#footer_one_div").html(est_cover_page_footer_one);
            });

            CKEDITOR.instances.est_cover_page_footer_two.on('change', function () {
                var est_cover_page_footer_two = this.getData();
                const matches = est_cover_page_footer_two.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                if (matches && matches.length > 0) {

                    $.ajax({
                        async: false,
                        url: "{{route('quotes.estimatePdfInfo')}}",
                        type: "GET",
                        dataType: "json",
                        data: {
                            matches: matches,
                            customer_id: $('#customer_id').val(),
                            estimate_id: $('#id').val()
                        },
                        success: function (data) {
                            $.each(data, function (key, value) {
                                est_cover_page_footer_two = est_cover_page_footer_two.replace("${" + key + "}", value);
                            });

                        }
                    });
                }
                $("#footer_two_div").html(est_cover_page_footer_two);
            });
*/
            /* CKEDITOR.instances.est_cover_page_title.on('change', function () {
                 var est_cover_page_title = this.getData();
                 const matches = est_cover_page_title.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                 if (matches && matches.length > 0) {

                     $.ajax({
                         async: false,
                         url: "{{route('quotes.estimatePdfInfo')}}",
                        type: "GET",
                        dataType: "json",
                        data: {
                            matches: matches,
                            customer_id: $('#customer_id').val(),
                            estimate_id: $('#id').val()
                        },
                        success: function (data) {
                            $.each(data, function (key, value) {
                                est_cover_page_title = est_cover_page_title.replace("${" + key + "}", value);
                            });

                        }
                    });
                }
                $(".proposal_title").html(est_cover_page_title);
            });*/

            CKEDITOR.instances.est_aboutus_title.on('change', function () {
                var aboutus_page_title = this.getData();
                $(".aboutus_title").html(aboutus_page_title);
            });

            CKEDITOR.instances.est_aboutus_content.on('change', function () {
                var est_aboutus_content = this.getData();
                $(".aboutus_partial_div_bg").html(est_aboutus_content);
            });

            CKEDITOR.instances.est_term_condition_title.on('change', function () {
                var est_term_condition_title = this.getData();
                $(".terms_title").html(est_term_condition_title);
            });

            CKEDITOR.instances.est_term_condition_content.on('change', function () {
                var est_term_condition_content = this.getData();
                $(".terms_content").html(est_term_condition_content);
            });

            {{--CKEDITOR.instances.est_term_condition_content.setData('{{ $estimate->est_term_condition_content}}');--}}

            $('#term_condition_id').change(function () {
                // Get the selected value from the dropdown
                var selectedValue = $(this).val();
                get_term_condition_id(selectedValue);
            });

            getDivValThemeEstimate('{{$proposal_template->theme_color_one}}')
            $("input[name='sales_flag']").click(function () {

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
            });

            $("input[name='purchase_flag']").click(function () {

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
            });

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


            $(function () { //DOM Ready
                getStatesList({{$expData->country_id}});
                $.widget("custom.autocomplete", $.ui.autocomplete, {
                    _create: function () {
                        this._on(this.element, {
                            focus: function (event) {
                                this.search();
                            }
                        });
                        this._super();
                    }
                    // our fancy new _renderMenu function adds the header and footers!
                    //    _renderMenu: function( ul, items ) {
                    //        console.log(ul);
                    //         var self = this;
                    //         $.each( items, function( index, item ) {
                    //             //console.log(items);
                    //             // if (index == 0)
                    //             //     ul.append( '<li><input type="checkbox"> I\'m at the top! Choose me!</li>' );
                    //             self._renderItem( ul, item );
                    //             if(index == items.length - 1)
                    //                 ul.append( '<li class="ui-menu-item"><div class="ui-menu-item-wrapper"><a href="create-batch-next.php" style="color:#207cf4 !important;"><i class="fa fa-plus"></i> Add New Payee</a></div></li>');
                    //         });
                    //     },
                    // _renderMenu: function (ul, items) {
                    //     var that = this,
                    //         currentCategory = "";
                    //     var i = 0;
                    //     $.each(items, function (index, item) {
                    //         i++;
                    //         if (item.label != currentCategory) {
                    //             //if(index == items.length - 1)
                    //             ul.append('<li class="ui-menu-item" id="ui-menu-item"><div class="ui-menu-item-wrapper" style="padding:10px;12px;"><a href="create-batch-next.php" style="color:#207cf4 !important;font-style: normal;font-weight: 500;font-size: 14px;line-height: 19px;letter-spacing: 0.3px;"><i class="mdi mdi-plus"></i> New Customer</a></div></li>');
                    //             currentCategory = item.label;
                    //             //}
                    //         }
                    //         that._renderItemData(ul, item);
                    //     });
                    // },
                });

                // note the new 'widget', extended from autocomplete above
                $(".customer_autocomplete").autocomplete({
                    minLength: 500,
                    scroll: true,
                    autofocus: true,
                    source: function (request, response) {
                        // Fetch data
                        $.ajax({
                            async: false,
                            url: "{{route('customerAutocomplete')}}",
                            type: 'post',
                            dataType: "json",
                            data: {
                                search: request.term
                            },
                            success: function (data) {
                                response($.map(data, function (obj, key) {
                                    var name = obj.label.toUpperCase();
                                    // if (name.indexOf(request.term.toUpperCase()) != -1) {
                                    return {
                                        label: obj.label, // Label for Display  + " (" + obj.desc + ")"
                                        value: obj.value, // Value
                                        desc: obj.desc,
                                        state_id: obj.state_id,
                                        state_name: obj.state_name,
                                        country_name: obj.country_name,
                                        city_name: obj.city_name,
                                        address: obj.address,
                                        pincode: obj.pincode,
                                        phone_no: obj.phone_no,
                                        company_name: obj.company_name
                                    }
                                    // } else {
                                    //     return null;
                                    // }
                                }));
                            }
                        });
                    },

                    focus: function (event, ui) {
                        event.preventDefault();
                        return false;
                    },
                    // focus: function(event, ui) {
                    //         $('#project').val(ui.item.value);
                    //         return false;
                    //     },
                    select: function (event, ui) {
                        $("#customer-modal #name").val($("#customer_name").val());
                        //console.log(ui);
                        if (ui.item.value > 0) {
                            $("#customer_name").val(ui.item.label);
                            $("#customer_id").val(ui.item.value);

                            $("#customer_name").val(ui.item.label);
                            $("#customer_id").val(ui.item.value);
                            var str = '';
                            if (ui.item.address && ui.item.address != null) {
                                str += ui.item.address + ',';
                            }

                            if (ui.item.city_name && ui.item.city_name != null) {
                                str += ui.item.city_name + ',';
                            }

                            if (ui.item.state_name && ui.item.state_name != null) {
                                str += ui.item.state_name + ',';
                            }

                            if (ui.item.pincode && ui.item.pincode != null) {
                                str += ui.item.pincode + ',';
                            }

                            if (ui.item.country_name && ui.item.country_name != null) {
                                str += ui.item.country_name + ',';
                            }
                            str += '\r\nMobile :' + ui.item.phone_no;
                            // var str = ui.item.address + ',' + ui.item.city_name + '\r\n' + ui.item.state_name + ' - ' + ui.item.pincode + '\r\n' + ui.item.country_name + '\r\nMobile :' + ui.item.phone_no;
                            $('#customer_info').attr('title', str);
                            $('#customer_address').val(str);
                            $('#customer_info').show();

                            $('#customer_state_id').val(ui.item.state_id);


                            {{--var cover_main_text = CKEDITOR.instances.est_cover_page_content.getData();--}}
                            {{--const matches = cover_main_text.match(/(?<=\{).+?(?=\})/g);--}}

                            {{--if (matches && matches.length > 0) {--}}

                            {{--    $.ajax({--}}
                            {{--        async: false,--}}
                            {{--        url: "{{route('quotes.estimatePdfInfo')}}",--}}
                            {{--        type: "GET",--}}
                            {{--        dataType: "json",--}}
                            {{--        data: {--}}
                            {{--            matches: matches,--}}
                            {{--            customer_id: $('#customer_id').val(),--}}
                            {{--            estimate_id: $('#id').val()--}}
                            {{--        },--}}
                            {{--        success: function (data) {--}}
                            {{--            $.each(data, function (key, value) {--}}
                            {{--                var st ='';--}}
                            {{--                if(value!=null){--}}
                            {{--                    st = value;--}}
                            {{--                }--}}
                            {{--                cover_main_text = cover_main_text.replace("${" + key + "}", st);--}}
                            {{--            });--}}

                            {{--        }--}}
                            {{--    });--}}
                            {{--}--}}
                            {{--$("#partial_div_bg").html(cover_main_text);--}}

                            amount();
                        }
                        return false;
                    },
                    response: function (event, ui) {
                        let tmp = "openModal('#customer-modal','Create Customer','#customer-form','.modal-title',id=0)";
                        ui.content.unshift({
                            value: 0,
                            label: '<a href="javascript:void(0);" onclick="' + tmp + '" style="color:#207cf4 !important;font-style: normal;font-weight: 500;font-size: 14px;line-height: 19px;letter-spacing: 0.3px;"><i class="mdi mdi-plus"></i> New Customer</a>',
                            desc: ''
                        });
                    }
                })
                    .autocomplete("instance")._renderItem = function (ul, item) {
                    var company_name = '';
                    if (item.company_name)
                        company_name = ' (' + item.company_name + ')';
                    return $("<li>").append("<h4 class='header-title'>" + item.label + company_name + "</h4><p class='desc'>" + item.desc + "</p>").appendTo(ul);
                }

                // note the new 'widget', extended from autocomplete above


            });
            // "use strict";
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
            window.Parsley.addValidator('fileextension', function (value, requirement) {
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
            $('.product-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('product.store')}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        // data: $('.category-form').serialize(),
                        dataType: "json",
                        beforeSend: function () {
                            $("#product_button").prop('disabled', true);
                            $("#product_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#product-modal').modal('toggle');
                            $("#product_button").prop('disabled', false);
                            $("#product_button").html('<i class="uil-arrow-circle-right"></i> Save');
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
                            $("#product_button").prop('disabled', false);
                            $("#product_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#product_button").html('Save');
                            $("#product_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });

            formValition('#estimate-form');
            formValition('#follow-up-form');
            formValition('#estimate-auto-number-form');
            formValition('#product-form');
            formValition('#item-form');
            formValition('#testimonial-form');
            $('.testimonial-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('testimonial.store')}}',
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
                            table.ajax.reload();
                            $("#unit_button").prop('disabled', false);
                            $("#unit_button").html('<i class="uil-arrow-circle-right"></i> Save');
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
                            $("#unit_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#unit_button").html('Save');
                            $("#unit_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });
            window.Parsley.on('field:error', function () {
                $('body .tab-validation').each(function () {
                    var error = $(this).find('.parsley-errors-list li').length;
                    var id = $(this).attr('aria-labelledby');
                    // console.log(error);
                    // console.log(id);
                    if (error != 0) {
                        // console.log('border success');
                        $("#" + id).css("border-bottom", "2px solid red");
                    } else {
                        $("#" + id).removeAttr("style");
                    }
                });
            });
            $('.estimate-form').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serializeArray();
                formData.push({name: 'est_cover_page_title', value: CKEDITOR.instances.est_cover_page_title.getData()});
                formData.push({
                    name: 'est_cover_page_content',
                    value: CKEDITOR.instances.est_cover_page_content.getData()
                });
                formData.push({
                    name: 'est_cover_page_footer_one',
                    value: CKEDITOR.instances.est_cover_page_footer_one.getData()
                });
                formData.push({
                    name: 'est_cover_page_footer_two',
                    value: CKEDITOR.instances.est_cover_page_footer_two.getData()
                });
                formData.push({name: 'est_aboutus_title', value: CKEDITOR.instances.est_aboutus_title.getData()});
                formData.push({name: 'est_aboutus_content', value: CKEDITOR.instances.est_aboutus_content.getData()});
                formData.push({
                    name: 'est_term_condition_title',
                    value: CKEDITOR.instances.est_term_condition_title.getData()
                });
                formData.push({
                    name: 'est_term_condition_content',
                    value: CKEDITOR.instances.est_term_condition_content.getData()
                });

                // formData.push({
                //     name: 'term_condition',
                //     value: CKEDITOR.instances.term_condition.getData()
                // });

                formData.push({name: 'est_cover_page_title_div', value: $('.proposal_title').html()});
                formData.push({
                    name: 'est_cover_page_content_div',
                    value: $('#partial_div_bg').html()
                });
                formData.push({
                    name: 'est_cover_page_footer_one_div',
                    value: $('#footer_one_div').html()
                });
                formData.push({
                    name: 'est_cover_page_footer_two_div',
                    value: $('#footer_two_div').html()
                });
                formData.push({name: 'est_aboutus_title_div', value: $('.aboutus_title').html()});
                formData.push({name: 'est_aboutus_content_div', value: $('.aboutus_partial_div_bg').html()});
                formData.push({
                    name: 'est_term_condition_title_div',
                    value: $('.terms_title').html()
                });
                formData.push({
                    name: 'est_term_condition_content_div',
                    value: $('.terms_content').html()
                });

                var customer_currency = $(".customer_currency").text();
                formData.push({name: 'est_currency_id', value: $("#est_currency_id").find(':selected').data('id')});
                formData.push({name: 'est_currency_name', value: customer_currency.replace(/\(|\)/g, '')});
                $(".estimate_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');

                $(".estimate_button").prop('disabled', true);
                // if ( $(this).parsley().isValid() ) {
                $.ajax({
                    async: true,
                    type: 'POST',
                    url: '{{route('quotes.update')}}',
                    // contentType: false,
                    // cache: false,
                    // processData: false,
                    // data: new FormData(this),
                    // data: $(this).serialize(),
                    data: formData,
                    dataType: "json",
                    beforeSend: function () {
                        $(".estimate_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        $(".estimate_button").prop('disabled', true);
                    },
                    success: function (data) {
                        toastrSuccess('Successfully saved...', 'Success');
                        // goBack();
                        toastrSuccess('Successfully saved...', 'Success');
                        // goBack();
                        @if(!request()->has('cid'))
                            window.location.href = data.url;
                        @else
                            window.location.href = data.url;
                        {{--window.location.href = SITEURL+'/lead/timeline/{{\Crypt::encrypt(\Request::get('cid'))}}';--}}
                        @endif
                        $(".estimate_button").prop('disabled', false);
                        $(".estimate_button").html('<i class="uil-arrow-circle-right"></i> Revise Estimate');
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
                        $(".estimate_button").prop('disabled', false);
                        $(".estimate_button").html('<i class="uil-arrow-circle-right"></i> Revise Estimate');
                    },
                    complete: function (data) {
                        $(".estimate_button").prop('disabled', false);
                        $(".estimate_button").html('<i class="uil-arrow-circle-right"></i> Revise Estimate');
                    }
                });
                // }
            });

            $('.estimate-auto-number-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('quotes.updateEstimateNumber')}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#estimate_auto_number_button").prop('disabled', true);
                            $("#estimate_auto_number_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {

                            toastrSuccess('Your estimate preferences have been saved.', 'Success');
                            $("#estimate_auto_number_button").prop('disabled', false);
                            $("#estimate_auto_number_button").html('<i class="uil-arrow-circle-right"></i> Save');
                            $("#estimate_no").val(data.data.estimate_prefix + data.data.estimate_next_no);
                            var tmp_est_cover_page_footer_two = CKEDITOR.instances.est_cover_page_footer_two.getData();
                            const matches = tmp_est_cover_page_footer_two.match(/(?<=\{).+?(?=\})/g); //// ["name", "age", "profession"]

                            tmp_est_cover_page_footer_two = tmp_est_cover_page_footer_two.replace("${estimates.estimate_no}", data.data.estimate_prefix + data.data.estimate_next_no);
                            $("#footer_two_div").html(tmp_est_cover_page_footer_two);
                            $('#estimate-auto-number-modal').modal('toggle');

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
                                    toastrInfo(xhr.responseJSON.success, 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#estimate_auto_number_button").prop('disabled', false);
                            $("#estimate_auto_number_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#estimate_auto_number_button").html('Save');
                            $("#estimate_auto_number_button").prop('disabled', false);
                        }
                    });
                }
            });

            $('.item-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('item.store')}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#item_button").prop('disabled', true);
                            $("#item_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#item-modal').modal('toggle');
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

            $('#add').click(function () {
                addnewrow();
            });
            $('#product_add').click(function () {
                productAddRow();
            });

            $('.item_rate_are').click(function () {
                var obj = $('.itemRow tr');
                obj.each(function (index, element) {
                    // $.each( obj, function( key, value ) {

                    var quantity = $(element).find('.quantity').val();

                    var price = $(element).find('.price').val();
                    var discount = $(element).find('.discount').val();
                    var discount_flag = $(element).find('.discount_flag').val();
                    var gst_per = $(element).find('.gst_per').val();
                    each_row_calc($(element), quantity, price, discount, discount_flag, gst_per);
                });


            });

            $('body').delegate('.remove', 'click', function () {
                //$(this).closest('tr').remove();
                var id = $(this).attr('id');
                // swal({
                //         title: "Are you sure?",
                //         type: "warning",
                //         showCancelButton: true,
                //         confirmButtonClass: "btn-danger",
                //         confirmButtonText: "Yes, delete it!",
                //         cancelButtonText: "No, cancel it!",
                //         closeOnConfirm: false,
                //         closeOnCancel: true
                //
                //     },
                //     function(isConfirm) {
                //         if (isConfirm) {

                $('#' + id).closest('tr').remove();
                amount();
                // swal("Deleted!", "Your data has been deleted.", "success");
                //$('.confirm').trigger('click');
                // $('.confirm').trigger('click');
                //     }
                //     else
                //     {
                //         /*new PNotify({
                //             title: 'Warning Notification',
                //             text: 'Contact Support Team',
                //             icon: 'icofont icofont-info-circle',
                //             type: 'Warning'
                //         });*/
                //         swal("Cancelled", "Your data has been safe :)", "error");
                //     }
                // })
            });

            $('body').delegate('.product_remove', 'click', function () {
                var id = $(this).attr('id');
                $('#' + id).closest('tr').remove();
            });

            $('body').delegate('.quantity,.price,.discount,.item_autocomplete', 'keyup', function () {
                var tr = $(this).parent().parent();
                // console.log(tr);
                var quantity = tr.find('.quantity').val();
                var price = tr.find('.price').val();
                var discount = tr.find('.discount').val();
                var discount_flag = tr.find('.discount_flag').val();
                var gst_per = tr.find('.gst_per').val();
                each_row_calc(tr, quantity, price, discount, discount_flag, gst_per);
            });

            $('body').delegate('.discount_flag,.gst_per', 'change', function () {
                var tr = $(this).parent().parent();
                var quantity = tr.find('.quantity').val();
                var price = tr.find('.price').val();
                var discount = tr.find('.discount').val();
                var discount_flag = tr.find('.discount_flag').val();
                var gst_per = tr.find('.gst_per').val();
                each_row_calc(tr, quantity, price, discount, discount_flag, gst_per);
            });


            $('body').delegate('.addless_amount', 'change', function () {
                var net_amount = parseFloat(0) + parseFloat($('.subtotal_span').html());
                var total_cgst_amount = parseFloat(0) + parseFloat($('.total_cgst_amount_span').html());
                var total_sgst_amount = parseFloat(0) + parseFloat($('.total_sgst_amount_span').html());
                var total_igst_amount = parseFloat(0) + parseFloat($('.total_igst_amount_span').html());
                var addless_amount = parseFloat(0) + parseFloat($('.addless_amount').val());
                if (isNaN(addless_amount))
                    addless_amount = 0;
                $('.addless_amount_span').html(addless_amount.toFixed(2));
                addLessAmount(net_amount, addless_amount, total_cgst_amount, total_sgst_amount, total_igst_amount);
            });

            formValition('#customer-form');
            $('.customer-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{route('customer.store')}}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        dataType: "json",
                        beforeSend: function () {
                            $("#customer_button").prop('disabled', true);
                            $("#customer_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $('#customer-modal').modal('toggle');
                            // table.ajax.reload();
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html('<i class="uil-arrow-circle-right"></i> Save');

                            $("#customer_name").val(data.customer_name);
                            $("#customer_id").val(data.customer_id);
                            $("#customer_state_id").val(data.state_id);

                            var str = '';
                            if (data.address && data.address != null) {
                                str += data.address + ',';
                            }

                            if (data.city_name && data.city_name != null) {
                                str += data.city_name + ',';
                            }

                            if (data.state_name && data.state_name != null) {
                                str += data.state_name + ',';
                            }

                            if (data.pincode && data.pincode != null) {
                                str += data.pincode + ',';
                            }

                            if (data.country_name && data.country_name != null) {
                                str += data.country_name + ',';
                            }
                            str += '\r\nMobile :' + data.phone_no;

                            // var str = data.address + ',' + data.city_name + '\r\n' + data.state_name + ' - ' + data.pincode + '\r\n' + data.country_name + '\r\nMobile :' + data.phone_no;
                            $('#customer_info').attr('title', str);
                            $('#customer_address').val(str);
                            $('#customer_info').show();
                            amount();
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.statusText);
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
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#customer_button").html('Save');
                            $("#customer_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });

            $('.update-technical-specification-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = $('.customer-form').serializeArray();

                    let temp_id = $("#temp_id").val();
                    $("#item_technical_specification_"+temp_id).val(CKEDITOR.instances.item_technical_specification_modal.getData());
                    toastrSuccess('Successfully saved...', 'Success');
                    $('#update-technical-specification-modal').modal('toggle');
                }
            });
        });
        $(document).on('focus', '.item_autocomplete', function () {
            $(this).autocomplete({
                minLength: 0,
                source: function (request, response) {
                    // Fetch data
                    $.ajax({
                        async: false,
                        url: "{{route('itemAutocomplete')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (obj, key) {
                                var name = obj.label.toUpperCase();
                                if (name.indexOf(request.term.toUpperCase()) != -1) {
                                    return {
                                        label: obj.label, // Label for Display  + " (" + obj.desc + ")"
                                        value: obj.value, // Value
                                        desc: obj.desc,
                                        desc_span: obj.desc_span,
                                        sale_price: obj.sale_price,
                                        inter_state: obj.inter_state,
                                        intra_state: obj.intra_state,
                                        item_discount: obj.item_discount,
                                        item_discount_flag: obj.item_discount_flag,
                                        hsn_code: obj.hsn_code,
                                        description: obj.desc,
                                        unit_name: obj.unit_name,
                                        technical_specification: obj.technical_specification,
                                    }
                                } else {
                                    return null;
                                }
                            }));
                        }
                    });
                },

                focus: function (event, ui) {
                    event.preventDefault();
                    return false;
                },
                select: function (event, ui) {
                    if (ui.item.value > 0) {
                        let idArr = $(this).attr('id').split("_");
                        let id = idArr[2];
                        $("#item_name_" + id).val(ui.item.label);
                        $("#item_id_" + id).val(ui.item.value);
                        $("#price_" + id).val(ui.item.sale_price);
                        $("#gst_per_" + id).val(ui.item.intra_state);
                        $("#hsn_code_" + id).val(ui.item.hsn_code);
                        $("#discount_" + id).val(ui.item.item_discount);
                        $("#discount_flag_" + id).val(ui.item.item_discount_flag);
                        $('#item_description_' + id).val(ui.item.description);
                        $('#item_description_span_' + id).html(ui.item.desc_span);
                        $('.units_span_' + id).html(ui.item.unit_name);
                        $('#unit_name_' + id).val(ui.item.unit_name);
                        $('#item_technical_specification_' + id).val(ui.item.technical_specification);
                        CKEDITOR.instances.item_technical_specification_modal.setData(ui.item.technical_specification);
                        /*$('#item_technical_specification_' + id).val('<h2>('+ id+') '+ui.item.label+'</h3>&nbsp;'+ui.item.technical_specification);
                        CKEDITOR.instances.item_technical_specification_modal.setData('<h2>'+ui.item.label+'</h3>&nbsp;'+ui.item.technical_specification);*/
                        if (ui.item.description) {
                            $('#item_description_' + id).show();
                            // $('#item_description_span_' + id).show();
                        }
                        $('#quantity_' + id).focus();
                        $('#quantity_' + id).keyup();
                        $("textarea").each(function () {
                            this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
                        }).on("input", function () {
                            this.style.height = 0;
                            this.style.height = (this.scrollHeight) + "px";
                        });
                    }
                    return false;
                },
                response: function (event, ui) {
                    let tmp = "openModal('#item-modal','Create Item','#item-form','.modal-title',id=0)";
                    @if(in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                    ui.content.push({
                        value: 0,
                        label: '<a href="javascript:void(0);" onclick="' + tmp + '"  style="color:#207cf4 !important;font-style: normal;font-weight: 500;font-size: 14px;line-height: 19px;letter-spacing: 0.3px;"><i class="mdi mdi-plus"></i> New Item</a>',
                        desc: ''
                    });
                    @endif
                }
            })
                .autocomplete("instance")._renderItem = function (ul, item) {
                let tc = '';
                if (!(item.sale_price === undefined || item.sale_price === null))
                    tc = "Rate: " + item.sale_price;
                return $("<li></li>").append("<h4 class='header-title'>" + item.label + "</h4><p class='desc'>" + tc + "</p>").appendTo(ul);

            }
        });

        $(document).on('focus', '.product_autocomplete', function () {
            $(this).autocomplete({
                minLength: 0,
                source: function (request, response) {
                    // Fetch data
                    $.ajax({
                        async: false,
                        url: "{{route('productAutocomplete')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (obj, key) {
                                var name = obj.label.toUpperCase();
                                if (name.indexOf(request.term.toUpperCase()) != -1) {
                                    return {
                                        label: obj.label, // Label for Display  + " (" + obj.desc + ")"
                                        value: obj.value, // Value
                                        image_one: obj.image_one,
                                        image_two: obj.image_two,
                                        image_three: obj.image_three,
                                        thumb_image_one: obj.thumb_image_one,
                                        thumb_image_two: obj.thumb_image_two,
                                        thumb_image_three: obj.thumb_image_three
                                    }
                                } else {
                                    return null;
                                }
                            }));
                        }
                    });
                },

                focus: function (event, ui) {
                    event.preventDefault();
                    return false;
                },
                select: function (event, ui) {
                    if (ui.item.value > 0) {
                        let idArr = $(this).attr('id').split("_");
                        let id = idArr[2];
                        $("#product_name_" + id).val(ui.item.label);
                        $("#product_id_" + id).val(ui.item.value);
                        /*$(".productRow #" + id + " td:nth-child(2)").html('<img class="img-fluid img-thumbnail rounded" src="' + ui.item.image_one + '" width="100"/>');
                        $(".productRow #" + id + " td:nth-child(3)").html('<img class="img-fluid img-thumbnail rounded" src="' + ui.item.image_two + '" width="100"/>');
                        $(".productRow #" + id + " td:nth-child(4)").html('<img class="img-fluid img-thumbnail rounded" src="' + ui.item.image_three + '" width="100"/>');*/
                        $(".productRow #" + id + " td:nth-child(2)").html('<input type="checkbox" class="form-check-input me-1" id="product_flg_' + id + '" name="product_flag"><span style="position: relative;right: 1.4rem;"><label class="form-check-label text-dark me-1" title="Click to Add photo in library"><i class="mdi mdi-information"></i></label></span><input type="hidden" class="form-control me-1" id="tmp_product_id_' + id + '1" name="image_data[' + id + '][tmp_product_id]" value="'+ui.item.value+'"><input type="hidden" class="form-control me-1" id="estimate_product_id_' + id + '1" name="image_data[' + id + '][estimate_product_id]" value="0"><img class="img-fluid img-thumbnail rounded" id="img_thumbnail_' + id + '1" src="' + ui.item.image_one + '" width="100"/><a class="b photo-upload text-black" style="position: relative;top: -1.8rem;left: -1.2rem;" data-id="' + id + '1"><input type="file" class="file-input w-100" id="one_photo_' + id + '1" data-type="1"><i class="mdi mdi-pencil-circle fs-4"></i></a><input class="form-control" type="hidden" id="h_image_' + id + '1" name="image_data[' + id + '][h_image_one]" value="">');
                        $(".productRow #" + id + " td:nth-child(3)").html('<img class="img-fluid img-thumbnail rounded" id="img_thumbnail_' + id + '2" src="' + ui.item.image_two + '" width="100"/><a class="photo-upload text-black" style="position: relative;top: -1.8rem;left: -1.2rem;" data-id="' + id + '2"><input type="file" class="file-input-small w-100" id="one_photo_' + id + '2" data-type="2"><i class="mdi mdi-pencil-circle fs-4"></i></a><input class="form-control" type="hidden" id="h_image_' + id + '2" name="image_data[' + id + '][h_image_two]" value="">');
                        $(".productRow #" + id + " td:nth-child(4)").html('<img class="img-fluid img-thumbnail rounded" id="img_thumbnail_' + id + '3" src="' + ui.item.image_three + '" width="100"/><a class="photo-upload text-black" style="position: relative;top: -1.8rem;left: -1.2rem;" data-id="' + id + '3"><input type="file" class="file-input-small w-100" id="one_photo_' + id + '3" data-type="3"><i class="mdi mdi-pencil-circle fs-4"></i></a><input class="form-control" type="hidden" id="h_image_' + id + '3" name="image_data[' + id + '][h_image_three]" value="">');

                        $('#product_name_' + id).focus();
                    }
                    return false;
                },
                response: function (event, ui) {
                    let tmp = "openModal('#product-modal','Create Product','#product-form','.modal-title',id=0,flag=4)";
                    @if(in_array('access-to-add-and-edit-item-and-product-photos', $user_perm))
                    ui.content.push({
                        value: 0,
                        label: '<a href="javascript:void(0);" onclick="' + tmp + '"  style="color:#207cf4 !important;font-style: normal;font-weight: 500;font-size: 14px;line-height: 19px;letter-spacing: 0.3px;"><i class="mdi mdi-plus"></i> New Product</a>',
                        // desc: ''
                    });
                    @endif
                }
            })
                .autocomplete("instance")._renderItem = function (ul, item) {
                // return $("<li>").append("<h4 class='header-title'>" + item.label + "</h4><p class='desc'>" + item.desc + "</p>").appendTo(ul);
                // return $("<li>").append("<h4 class='header-title'>" + item.label + "</h4> <p class='desc'></p>").appendTo(ul);
                var image_one = '';
                var image_two = '';
                var image_three = '';
                if (item.thumb_image_one)
                    image_one = "<img class='rounded-circle avatar-xs' src='" + item.thumb_image_one + "' alt='Avtar image'>";
                if (item.thumb_image_two)
                    image_two = "<img class='rounded-circle avatar-xs' src='" + item.thumb_image_two + "' alt='Avtar image'>";
                if (item.thumb_image_three)
                    image_three = "<img class='rounded-circle avatar-xs' src='" + item.thumb_image_three + "' alt='Avtar image'>";
                // return $("<li>").append("<h4 class='header-title'>" + item.label + "</h4><p class='desc'>" + item.desc + "</p>").appendTo(ul);
                return $("<li>").append("<div class='d-flex align-items-center'> <div class='flex-shrink-0 multi-user'>" + image_one + " " + image_two + " " + image_three + "</div><div class='flex-grow-1 ms-2'><h4 class='header-title'>" + item.label + "</h4><p class='desc'></p></div></div>").appendTo(ul);
            }
        });

        $(document).on('focus', '.testimonial_autocomplete', function () {
            $(this).autocomplete({
                minLength: 0,
                source: function (request, response) {
                    console.log(request);
                    // Fetch data
                    $.ajax({
                        async: false,
                        url: "{{route('testimonialAutocomplete')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (obj, key) {
                                var name = obj.label.toUpperCase();
                                if (name.indexOf(request.term.toUpperCase()) != -1) {
                                    return {
                                        label: obj.label, // Label for Display  + " (" + obj.desc + ")"
                                        value: obj.value, // Value
                                        image_one: obj.image_one,
                                        rating_one: obj.rating_one,
                                        description_one: obj.description_one,
                                        client_name_one: obj.client_name_one,
                                        image_two: obj.image_two,
                                        rating_two: obj.rating_two,
                                        description_two: obj.description_two,
                                        client_name_two: obj.client_name_two,
                                        image_three: obj.image_three,
                                        rating_three: obj.rating_three,
                                        description_three: obj.description_three,
                                        client_name_three: obj.client_name_three,
                                    }
                                } else {
                                    return null;
                                }
                            }));
                        }
                    });
                },

                focus: function (event, ui) {
                    event.preventDefault();
                    return false;
                },
                select: function (event, ui) {
                    if (ui.item.value > 0) {
                        let idArr = $(this).attr('id').split("_");
                        let id = idArr[2];
                        $("#testimonial_name_" + id).val(ui.item.label);
                        $("#testimonial_id_" + id).val(ui.item.value);

                        $(".testimonialRow #" + id + " td:nth-child(2)").html('<div class="d-flex">' +
                            '<div class="flex-shrink-0">' +
                            '<img class="rounded-circle avatar-sm" src="' + ui.item.image_one + '" alt="Avtar image">' +
                            '</div>' +
                            '<div class="flex-grow-1 ms-2">' +
                            '<a href="javascript:void(0);" class="text-secondary"><h5 class="my-1">' + ui.item.client_name_one + '</h5></a>' +
                            '<p class="text-muted mb-0">' + ui.item.rating_one + ' <span class="text-warning mdi mdi-star"></span></p>' +
                            '</div>' +
                            '</div>');
                        $(".testimonialRow #" + id + " td:nth-child(3)").html('<div class="d-flex">' +
                            '<div class="flex-shrink-0">' +
                            '<img class="rounded-circle avatar-sm" src="' + ui.item.image_two + '" alt="Avtar image">' +
                            '</div>' +
                            '<div class="flex-grow-1 ms-2">' +
                            '<a href="javascript:void(0);" class="text-secondary"><h5 class="my-1">' + ui.item.client_name_two + '</h5></a>' +
                            '<p class="text-muted mb-0">' + ui.item.rating_two + ' <span class="text-warning mdi mdi-star"></span></p>' +
                            '</div>' +
                            '</div>');
                        $(".testimonialRow #" + id + " td:nth-child(4)").html('<div class="d-flex">' +
                            '<div class="flex-shrink-0">' +
                            '<img class="rounded-circle avatar-sm" src="' + ui.item.image_three + '" alt="Avtar image">' +
                            '</div>' +
                            '<div class="flex-grow-1 ms-2">' +
                            '<a href="javascript:void(0);" class="text-secondary"><h5 class="my-1">' + ui.item.client_name_three + '</h5></a>' +
                            '<p class="text-muted mb-0">' + ui.item.rating_three + ' <span class="text-warning mdi mdi-star"></span></p>' +
                            '</div>' +
                            '</div>');

                        $('#testimonial_name_' + id).focus();
                    }
                    return false;
                },
                response: function (event, ui) {
                    let tmp = "openModal('#testimonial-modal','Create Testimonial','#testimonial-form','.modal-title',id=0,flag=4)";
                    ui.content.push({
                        value: 0,
                        label: '<a href="javascript:void(0);" onclick="' + tmp + '"  style="color:#207cf4 !important;font-style: normal;font-weight: 500;font-size: 14px;line-height: 19px;letter-spacing: 0.3px;"><i class="mdi mdi-plus"></i> New Testimonial</a>',
                        // desc: ''
                    });
                }
            })
                .autocomplete("instance")._renderItem = function (ul, item) {
                // return $("<li>").append("<h4 class='header-title'>" + item.label + "</h4><p class='desc'>" + item.desc + "</p>").appendTo(ul);
                return $("<li>").append("<h4 class='header-title'>" + item.label + "</h4> <p class='desc'></p>").appendTo(ul);
            }
        });

        function each_row_calc(tr, quantity, price, discount = 0, discount_flag, gst_per) {
            let cgst_amount = 0;
            let sgst_amount = 0;
            let igst_amount = 0;
            let total_amt = quantity * price;
            var total = (discount_flag == 1 && discount > 0) ? (total_amt - (total_amt * discount / 100)) : total_amt - discount;
            tr.find('.total').val(total.toFixed(2));

            if ($(".item_rate_are").val() == 1) {
                let gst_amount = (total * gst_per) / 100;
                cgst_amount = gst_amount / 2;
                sgst_amount = gst_amount / 2;
                igst_amount = gst_amount;
            } else {
                let tmp = (total * gst_per) / (parseFloat(100) + parseFloat(gst_per));
                cgst_amount = tmp / 2;
                sgst_amount = tmp / 2;
                igst_amount = tmp;
            }

            tr.find('.cgst_amount').val(cgst_amount.toFixed(2));
            tr.find('.sgst_amount').val(sgst_amount.toFixed(2));
            tr.find('.igst_amount').val(igst_amount.toFixed(2));
            amount();
        }

        function addLessAmount(net_amount, addless_amount, total_cgst_amount, total_sgst_amount, total_igst_amount) {
            let final_amount = 0;
            if ($(".item_rate_are").val() == 1) {
                final_amount = parseFloat(net_amount) + parseFloat(addless_amount) + parseFloat(total_cgst_amount) + parseFloat(total_sgst_amount);

            } else {
                final_amount = parseFloat(net_amount) + parseFloat(addless_amount);
            }

            $('.net_amount_span').html(final_amount.toFixed(2));
            $('#net_amount').val(final_amount.toFixed(2));
        }

        function amount() {
            var subtotal = 0;
            let net_amnount = 0;
            $('.total').each(function (i, e) {
                var amt = $(this).val() - 0;
                subtotal += amt;
            });
            var total_cgst_amount = 0;
            $('.cgst_amount').each(function (i, e) {
                var cgst_amount = $(this).val() - 0;
                total_cgst_amount += cgst_amount;
            });

            var total_sgst_amount = 0;
            $('.sgst_amount').each(function (i, e) {
                var sgst_amount = $(this).val() - 0;
                total_sgst_amount += sgst_amount;
            });

            var total_igst_amount = 0;
            $('.igst_amount').each(function (i, e) {
                var igst_amount = $(this).val() - 0;
                total_igst_amount += igst_amount;
            });

            let customer_state_id = $('.customer_state_id').val();
            if ($(".item_rate_are").val() == 1) {
                // if (customer_state_id > 0) {

                    if (customer_state_id == $('.customer_state_id').attr('data-id')) {
                        net_amnount = parseFloat(subtotal) + parseFloat(total_cgst_amount) + parseFloat(total_sgst_amount);
                        $('.total_igst_amount_span').parent().parent().hide();
                        $('.total_cgst_amount_span').parent().parent().show();
                        $('.total_sgst_amount_span').parent().parent().show()
                    }
                    if (customer_state_id != $('.customer_state_id').attr('data-id')) {

                        net_amnount = parseFloat(subtotal) + parseFloat(total_igst_amount);
                        $('.total_cgst_amount_span').parent().parent().hide();
                        $('.total_sgst_amount_span').parent().parent().hide();
                        $('.total_igst_amount_span').parent().parent().show();
                    }

               /* } else {
                    net_amnount = parseFloat(subtotal) + parseFloat(total_cgst_amount) + parseFloat(total_sgst_amount);
                }*/

            } else {
                net_amnount = parseFloat(subtotal)
                if (customer_state_id == $('.customer_state_id').attr('data-id')) {

                    // net_amnount = parseFloat(subtotal) + parseFloat(total_cgst_amount) + parseFloat(total_sgst_amount);
                    $('.total_igst_amount_span').parent().parent().hide();
                    $('.total_cgst_amount_span').parent().parent().show();
                    $('.total_sgst_amount_span').parent().parent().show();
                }
                if (customer_state_id != $('.customer_state_id').attr('data-id')) {
                    // net_amnount = parseFloat(subtotal) + parseFloat(total_igst_amount);
                    $('.total_cgst_amount_span').parent().parent().hide();
                    $('.total_sgst_amount_span').parent().parent().hide();
                    $('.total_igst_amount_span').parent().parent().show();
                }
            }


            $('.subtotal_span').html(subtotal.toFixed(2));
            $('.total_sgst_amount_span').html(total_sgst_amount.toFixed(2));
            $('.total_cgst_amount_span').html(total_cgst_amount.toFixed(2));
            $('.total_igst_amount_span').html(total_igst_amount.toFixed(2));
            $('.total_sgst_amount').val(total_sgst_amount.toFixed(2));
            $('.total_cgst_amount').val(total_cgst_amount.toFixed(2));
            $('.total_igst_amount').val(total_igst_amount.toFixed(2));
            var addless_amount = parseFloat(0) + parseFloat($('.addless_amount').val());
            if (isNaN(addless_amount))
                addless_amount = 0;
            $('.addless_amount_span').html(addless_amount.toFixed(2));
            $('.net_amount_span').html((parseFloat(net_amnount.toFixed(2)) + parseFloat(addless_amount)).toFixed(2));
            $('.net_amount').val((parseFloat(net_amnount.toFixed(2)) + parseFloat(addless_amount)).toFixed(2));

            $('#subtotal').val(subtotal.toFixed(2));
        }

        //add Row
        function addnewrow() {
            if (isNaN(parseInt($('.itemRow tr:last').attr('id'))))
                n = parseInt($('.itemRow tr').length) + parseInt(1);
            if (!isNaN(parseInt($('.itemRow tr:last').attr('id'))))
                n = parseInt($('.itemRow tr:last').attr('id')) + parseInt(1);
            var tr = '<tr id="' + n + '">' +
                '<td class="no">' + n + '</td>' +
                '<td><input type="text" class="form-control item_autocomplete" onkeyup="javascript:item(' + n + ')" name="data[' + n + '][item_name]" id="item_name_' + n + '" data-type="productName" placeholder="Click to select item" required="" ><input type="hidden" class="form-control"  id="item_id_' + n + '" name="data[' + n + '][item_id]" ><input type="hidden" class="form-control" id="hsn_code_' + n + '" name="data[' + n + '][hsn_code]"><textarea class="form-control item_description  bg-light" id="item_description_' + n + '" name="data[' + n + '][item_description]" placeholder="Add a description to your item" style="display: none;"></textarea><textarea class="form-control item_technical_specification bg-light d-none" id="item_technical_specification_' + n + '" name="data[' + n + '][item_technical_specification]" placeholder="Add a technical specification to your item" style="display:none;"></textarea><span class="form-control item_description_span bg-light" id="item_description_span_' + n + '" style="display:none;"></span><a href="javascript:void(0);" id="update_technical_specification_' + n + '" class="fw-bolder update_technical_specification" title="Item Specification" style="vertical-align: middle!important;" data-id="' + n + '"> <i class="mdi mdi-lead-pencil mdi-16px text-primary"></i></a>' +
                '<td><input type="text" class="form-control quantity" name="data[' + n + '][quantity]" id="quantity_' + n + '" placeholder="Quantity" required="" value="1"><span class="font-13 text-muted units_span_' + n + '"></span><input type="hidden"class="form-control unit_name" name="data[' + n + '][unit_name]" id="unit_name_' + n + '"placeholder="Unit" required="" value=""></td>' +
                '<td><input type="text" class="form-control price" name="data[' + n + '][price]" id="price_' + n + '" placeholder="Rate" required="" value="0">' +
                    '<td class="input-group"><input type="text" class="form-control discount" name="data[' + n + '][discount]" id="discount_' + n + '" placeholder="Discount" required="" value="0"> <select class="btn-light discount_flag" id="discount_flag_' + n + '" name="data[' + n + '][discount_flag]"><option value="2">{{$country_data->currency_symbol}}</option><option value="1">%</option></select></td>' +
                '<td><select class="form-select gst_per" id="gst_per_' + n + '" name="data[' + n + '][gst_per]"><option value="0">GST0 [0%]</option>@foreach($taxes as $tax)<option value="{{$tax->name}}">GST{{$tax->name}} [{{$tax->name}}%]</option>@endforeach</select><input type="hidden" class="form-control cgst_amount" name="data[' + n + '][cgst_amount]" id="cgst_amount_' + n + '" value="0"> <input type="hidden" class="form-control sgst_amount" name="data[' + n + '][sgst_amount]" id="sgst_amount_' + n + '" value="0"> <input type="hidden" class="form-control igst_amount" name="data[' + n + '][igst_amount]" id="igst_amount_' + n + '" value="0"></td>' +
                '<td><input type="text" class="form-control total" name="data[' + n + '][total]" id="total_' + n + '" placeholder="Total" required="" value="0" readonly></td>' +
                '<td><a href="JavaScript:void(0);" id="quotation_' + n + '" class="text-danger remove"><i class="mdi mdi-trash-can-outline mdi-18px"></i></td>' +
                '</tr>';
            $('.itemRow').append(tr);
            $('#item_name_' + n).focus();
        }

        function productAddRow() {
            var n = 0
            if (isNaN(parseInt($('.productRow tr:last').attr('id'))))
                n = parseInt($('.productRow tr').length) + parseInt(1);
            if (!isNaN(parseInt($('.productRow tr:last').attr('id'))))
                n = parseInt($('.productRow tr:last').attr('id')) + parseInt(1);
            var tr = '<tr id="' + n + '">' +
                '<td><input type="text" class="form-control product_autocomplete" onkeyup="javascript:product(' + n + ')" name="product_name[]" id="product_name_' + n + '" data-type="productName" placeholder="Click and add photos" @if($estimate->pdf_product_flg){{'required'}} @endif><input type="hidden" class="form-control"  id="product_id_' + n + '" name="product_id[]" >' +
                '<td></td>' +
                '<td></td>' +
                '<td></td>' +
                '<td><a href="JavaScript:void(0);" id="product_' + n + '" class="text-danger product_remove"><i class="mdi mdi-trash-can-outline mdi-18px"></i></a></td>' +
                '</tr>';
            $('.productRow').append(tr);
            $('#product_name_' + n).focus();
        }

        $('#country_id').on('change', function (e) {
            e.preventDefault();
            var country_id = jQuery(this).val();
            getStatesList(country_id);

        });

        $('#state_id').on('change', function (e) {
            e.preventDefault();
            var state_id = jQuery(this).val();
            getCityList(state_id);

        });

        // function get All States
        function getStatesList(country_id, seleted_id = 0) {
            if (seleted_id == 0) {
                seleted_id = {{$expData->state_id}};
            }
            $.ajax({
                async: false,
                url: "{{url('get-states-by-country')}}",
                type: "POST",
                data: {
                    country_id: country_id
                },
                dataType: 'json',
                beforeSend: function () {
                    jQuery('select#state_id').find("option:eq(0)").html("Please wait..");
                },
                success: function (result) {
                    var options = '';
                    options += '<option value="">Choose</option>';
                    $.each(result.states, function (key, value) {
                        var selected = "";
                        if (value.id == seleted_id)
                            selected = 'selected';
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>';
                    });
                    $("#state_id").html(options);
                    $('#city_id').html('<option value="0">Choose</option>');
                },
                complete: function () {
                    // code
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }

        // function get All Cities
        function getCityList(state_id, seleted_id = 0) {
            $.ajax({
                async: false,
                url: "{{url('get-cities-by-state')}}",
                type: "POST",
                data: {
                    state_id: state_id
                },
                dataType: 'json',
                beforeSend: function () {
                    jQuery('select#city_id').find("option:eq(0)").html("Please wait..");
                },
                success: function (result) {
                    var options = '';
                    options += '<option value="0">Choose</option>';
                    $.each(result.cities, function (key, value) {
                        var selected = "";
                        if (value.id == seleted_id)
                            selected = 'selected';
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>';

                    });
                    $("#city_id").html(options);
                },
                complete: function () {
                    // code
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }

        function customer() {
            $('#customer_id').val(0); //customer id
            $('#customer_state_id').val(0); //customer address
            $('#customer_address').empty(); //customer address
            $('#customer_info').attr('title', 'Choose customer');
            $('#customer_info').css('display', 'none');
        }

        function product(id) {
            $('#product_id_' + id).val(0);
        }

        function testimonial(id) {
            $('#testimonial_id_' + id).val(0);
        }

        function item(id) {
            $('#item_id_' + id).val(0); //item id
            $('#item_description_' + id).val('');
            // $('#item_description_span_' + id).empty();
            $('#hsn_code_' + id).val('');
            $('#item_description_' + id).hide();
        }

        function get_estimate_number() {
            $.ajax({
                async: false,
                type: "GET",
                url: "{{route('quotes.getEstimateNumber')}}",
                // data: {id: id},
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#estimate-auto-number-form");
                    $('#estimate_prefix').val(res[0].estimate_prefix);
                    $('#estimate_next_no').val(res[0].estimate_next_no);
                    $('.modal-title').text('Estimate Number');
                    $('#estimate-auto-number-modal').modal('toggle');
                }
            });
        }

        function generatePDF() {
            // html2pdf(document.getElementById('invoices'), {
            //     margin: 0,
            //     filename: "my.pdf",
            //     image: {type: 'jpeg', quality: 1},
            //     html2canvas: {dpi: 72, letterRendering: true},
            //     jsPDF: {unit: 'mm', format: 'a4', orientation: 'portrait'},
            //     pdfCallback: pdfCallback
            // })
            // Choose the element that our invoice is rendered in.
            const element = document.getElementById('invoices');
            var opt = {
                margin: [0, 0, 0, 0], //top,right,bottom,left
                filename: `CV-page1.pdf`,
                image: {type: 'jpeg', quality: 1.0},
                html2canvas: {
                    dpi: 96,
                    scale: 2,
                    letterRendering: true,
                    useCORS: false
                },
                // jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                jsPDF: {unit: 'mm', format: 'a4', orientation: 'portrait'},
                pagebreak: {mode: ['avoid-all', 'css', 'legacy']},
                // pagebreak: { mode: 'avoid-all', after: '.avoidThisRow' },
                /*callback: function (element) {

                    const pageCount = element.internal.getNumberOfPages();

// For each page, print the page number and the total pages
                    for (var i = 1; i <= pageCount; i++) {
                        // Go to page i
                        element.setPage(i);
                            //Print Page 1 of 4 for example
                        element.text('Page ' + String(i) + ' of ' + String(pageCount), 210 - 20, 297 - 30, null, null, "right");
                    }
                }*/
            };
            // Choose the element and save the PDF for our user.
            html2pdf().set(opt).from(element).toPdf().save();
            // html2pdf().from(element).toPdf().save('myfile.pdf');
        }

        function pdfCallback(pdfObject) {
            var number_of_pages = pdfObject.internal.getNumberOfPages()
            var pdf_pages = pdfObject.internal.pages
            var myFooter = "Footer info"
            for (var i = 1; i < pdf_pages.length; i++) {
                // We are telling our pdfObject that we are now working on this page
                pdfObject.setPage(i)
                // The 10,200 value is only for A4 landscape. You need to define your own for other page sizes
                pdfObject.text(myFooter, 10, 200)
            }
        }

        function getDivValThemeEstimate(val) {
            $(".estimate_title_heading").css('color', val);
            $(".table-thead").css('background-color', val);
            $(".table-thead th").css('color', '#fff');
            $("#theme-color").val(val);
        }

        function get_term_condition_id(id){
            // Perform an AJAX request to fetch content based on the selected value
            $.ajax({
                type: 'GET',
                url: '{{route('term-condition.termAjax')}}',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    // Update CKEditor content based on the response data
                    CKEDITOR.instances.est_term_condition_content.setData(data.description);
                },
                error: function (error) {
                    console.error('Error fetching content:', error);
                }
            });
        }
    </script>
@endpush
