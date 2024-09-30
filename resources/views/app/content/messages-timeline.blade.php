@php
    $user_perm = PermissionCheck::check_permission('role-list');
@endphp
@extends('layouts.app')
@section('title','Customer')
@push('styles')
    <link href="{{ asset('assets/css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/virtual-select.min.css')}}" rel="stylesheet" type="text/css"/>
{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">--}}
    <style>
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


        form {
            /*max-width: 250px;*/
            position: relative;
            /*margin: 50px auto 0;*/
            font-size: 15px;
        }

        .radiobtn {
            position: relative;
            display: block;
        }

        .radiobtn label {
            display: block;
            background: #eef2f7;
            color: #444;
            border-radius: 2px;
            padding: 10px 20px;
            border: 1px solid #d5d5d5;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .radiobtn label:after, .radiobtn label:before {
            content: "";
            position: absolute;
            right: 11px;
            top: 14px;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            /*background: #d5d5d5;*/
            background: #eef2f7;
        }

        .radiobtn label:before {
            background: transparent;
            transition: 0.1s width cubic-bezier(0.075, 0.82, 0.165, 1) 0s, 0.3s height cubic-bezier(0.075, 0.82, 0.165, 2) 0.1s;
            z-index: 2;
            overflow: hidden;
            background-repeat: no-repeat;
            background-size: 13px;
            background-position: center;
            width: 0;
            height: 0;
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNS4zIDEzLjIiPiAgPHBhdGggZmlsbD0iI2ZmZiIgZD0iTTE0LjcuOGwtLjQtLjRhMS43IDEuNyAwIDAgMC0yLjMuMUw1LjIgOC4yIDMgNi40YTEuNyAxLjcgMCAwIDAtMi4zLjFMLjQgN2ExLjcgMS43IDAgMCAwIC4xIDIuM2wzLjggMy41YTEuNyAxLjcgMCAwIDAgMi40LS4xTDE1IDMuMWExLjcgMS43IDAgMCAwLS4yLTIuM3oiIGRhdGEtbmFtZT0iUGZhZCA0Ii8+PC9zdmc+);
        }

        .radiobtn input[type=radio] {
            display: none;
            position: absolute;
            width: 100%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .radiobtn input[type=radio]:checked + label {
            background: #eaf5ff;
            -webkit-animation-name: blink;
            animation-name: blink;
            -webkit-animation-duration: 1s;
            animation-duration: 1s;
            border-color: #727cf5;
        }

        .radiobtn input[type=radio]:checked + label:after {
            /*background: #d5d5d5;*/
            background: #727cf5;
        }

        .radiobtn input[type=radio]:checked + label:before {
            width: 20px;
            height: 20px;
        }

        #sample-select {
            max-width: 100% !important;
        }

        @-webkit-keyframes blink {
            0% {
                background-color: #eaf5ff;
            }
            10% {
                background-color: #eaf5ff;
            }
            11% {
                background-color: #eaf5ff;
            }
            29% {
                background-color: #eaf5ff;
            }
            30% {
                background-color: #eaf5ff;
            }
            50% {
                background-color: #eaf5ff;
            }
            45% {
                background-color: #eaf5ff;
            }
            50% {
                background-color: #eaf5ff;
            }
            100% {
                background-color: #eaf5ff;
            }
        }

        @keyframes blink {
            0% {
                background-color: #eaf5ff;
            }
            10% {
                background-color: #eaf5ff;
            }
            11% {
                background-color: #eaf5ff;
            }
            29% {
                background-color: #eaf5ff;
            }
            30% {
                background-color: #eaf5ff;
            }
            50% {
                background-color: #eaf5ff;
            }
            45% {
                background-color: #eaf5ff;
            }
            50% {
                background-color: #eaf5ff;
            }
            100% {
                background-color: #eaf5ff;
            }
        }


        .timeline-alt .timeline-item:before {
            background-color: #f1f3fa;
            bottom: 0;
            content: "";
            left: 14px;
            position: absolute;
            top: 31px;
            width: 2px;
            z-index: 0;
        }

        .timeline-alt .timeline-item .timeline-icon {
            float: left;
            height: 2rem;
            width: 2rem;
            border-radius: 50%;
            border: 2px solid transparent;
            font-size: 20px;
            text-align: center;
            line-height: 28px;
            background-color: #fff;
        }

        .timeline-alt .timeline-item .timeline-item-info {
            margin-left: 45px;
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

        /*.description_td, .label_td {
            cursor: pointer;
        }*/
        .description_td {
           white-space: inherit !important;
        }


        #lead-table > :not(caption) > * > * {
            padding: 0rem 0rem;
        }
    </style>
@endpush
@section('content')

    <!-- start page title -->

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">

                    <div class="page-title-right">
                        <div class="dropdown">
                                @if(in_array('access-to-add-and-edit-files-and-message-template', $user_perm)) {{-- || auth()->user()->company_id==null--}}

                                    <a href="#" class="dropdown-toggle arrow-none card-drop btn btn-dark btn-sm"
                                       data-bs-toggle="dropdown" aria-expanded="false">
                                        Options <i class="mdi mdi-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <!-- item-->
                                    {{--                                <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-cached me-1"></i>Refresh</a>--}}
                                    <!-- item-->
                                        <a href="javascript:void(0);" class="dropdown-item"
                                           onclick="edit_id('{{ Request::segment(4) }}')"><i
                                                class="mdi mdi-circle-edit-outline me-1"></i>Edit message template</a>
                                        <!-- item-->
                                        <a href="javascript:void(0);"
                                           onclick="activity_remove_id('{{ Request::segment(4) }}')"
                                           class="dropdown-item text-danger"><i
                                                class="mdi mdi-delete-outline me-1"></i>Delete message template</a>
                                    </div>
                                @endif


                        </div>
                    </div>
                    <h2 class="page-title fw-bold text-dark text-capitalize font-24"><small>
                            <a class="page-title" href="{{route('content.messages.index')}}">Content</a>
                            <i class="mdi mdi-greater-than"></i></small> {{$content_messages->name}}
                    </h2>
                </div>
            </div>
        </div>

        <!-- end page title -->

        <div class="row">
            {{--<div class="col-xxl-3 col-lg-6"></div>--}}

            <div class="col-xxl-8 col-lg-8 order-lg-2 order-xxl-1">
                <!-- start news feeds -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="header-title text-capitalize text-dark font-20">Message Info</h3>
                        </div>

                        <table class="table table-nowrap table-centered mb-0">
                            <tbody>

                            <tr>
                                <td>
                                    <p class="overflow-hidden text-black mb-0">
                                        <span class="fw-bold">Name</span>
                                        <br>
                                        <small
                                            class="text-body font-16 name_small">{{($content_messages->name)? $content_messages->name : '-'}}</small>
                                    </p>
                                </td>


                            </tr>
                            <tr>
                                <td class="description_td" colspan="0">
                                    <p class="overflow-hidden text-black mb-0">
                                        <span class="fw-bold">Description</span>
                                        <br>
                                        <small
                                            class="text-body description_small font-16">{!! ($content_messages->description)? nl2br($content_messages->description) : '-' !!}</small>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div> <!-- end card-body -->
                </div>
                <!-- end card -->
            </div>

            <div class="col-xxl-4 col-lg-4 order-lg-1 order-xxl-2">
                <!-- video -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="header-title text-capitalize text-dark font-20">Timeline</h4>
                        </div>

                        <div class="timeline-alt py-0" id="timeline-info">
                            <div class="timeline-item pb-4">
                                <i class="mdi mdi-plus bg-info-lighten text-info timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-info fw-bold mb-1 pt-1 d-block"
                                       onclick="openModalActivity(id=0,'#activity-modal','#activity-formModalLabel','Add Activity','#activity-form')">Add
                                        Activity</a>
                                    {{--  <small>Send you message
                                          <span class="fw-bold">"Are you there?"</span>
                                      </small>--}}
                                    {{--<p class="mb-0 pb-2">
                                        <small class="text-muted">2 days ago</small>
                                    </p>--}}
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-check-decagram-outline bg-secondary-lighten text-secondary timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">You sold an
                                        item</a>
                                    <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">5 minutes ago</small>
                                    </p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-chat-outline bg-warning-lighten text-warning timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Product on the
                                        Bootstrap Market</a>
                                    <small>Dave Gamache added
                                        <span class="fw-bold">Admin Dashboard</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">30 minutes ago</small>
                                    </p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-phone bg-success-lighten text-success timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Robert
                                        Delaney</a>
                                    <small>Send you message
                                        <span class="fw-bold">"Are you there?"</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">2 hours ago</small>
                                    </p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-arrow-top-right bg-primary-lighten text-primary timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Audrey
                                        Tobey</a>
                                    <small>Uploaded a photo
                                        <span class="fw-bold">"Error.jpg"</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">14 hours ago</small>
                                    </p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-file-document-outline bg-danger-lighten text-danger timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">You sold an
                                        item</a>
                                    <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">16 hours ago</small>
                                    </p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-calendar bg-dark-lighten text-dark timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Product on the
                                        Bootstrap Market</a>
                                    <small>Dave Gamache added
                                        <span class="fw-bold">Admin Dashboard</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">22 hours ago</small>
                                    </p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <i class="mdi mdi-account-plus-outline bg-info-lighten text-info timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="javascript:void(0);" class="text-black fw-bold mb-1 d-block">Robert
                                        Delaney</a>
                                    <small>Send you message
                                        <span class="fw-bold">"Are you there?"</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">2 days ago</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end card-body -->
                </div>
                <!-- end video -->


                <!-- end video -->
            </div> <!-- end col -->
        </div> <!--end row -->
    </div>

    <!-- Modal -->
    <div id="content-messages-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-1 bg-light">
                    <h3 class="modal-title text-dark">Create Customer</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-0">
                    <form class="content-messages-form" id="content-messages-form" action="#">
                        <div class="row">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-1">
                                            <input class="form-control" type="hidden" id="id" name="id" value="0">
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
                                              name="description"
                                              placeholder="Enter description" style="height: 80px;"></textarea>
                                            <label for="description" class="form-label">Description</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <p class="text-dark mb-2"><span class="fw-bold">@leadName </span>
                                            will be replaced with your lead's display name when sending
                                            <a href="#" data-type="@leadName" class="text-primary lead_name_cl fw-bold underline cursor-pointer inline-block">( insert @leadName )</a></p>
                                        <p class="text-dark mb-2"><span class="fw-bold">@senderName </span>
                                            will be replaced with the name of the user account sending this content
                                            <a href="#" data-type="@senderName" class="text-primary sender_name_cl fw-bold underline cursor-pointer inline-block">( insert @senderName )</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button class="btn btn-primary fullscreen" form="content-messages-form" id="customer_button"
                                type="submit">
                            <i class="mdi mdi-floppy fs-5"></i> Save
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@push('scripts')

    <script src="{{ asset('assets/js/vendor.min.js')}}"></script>
    <script src="{{ asset('assets/js/app.min.js')}}"></script>

    <script src="{{ asset('assets/js/virtual-select.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('assets/js/custom.js')}}"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{--    <script src="https://cdn.tiny.cloud/1/qagffr3pkuv17a8on1afax661irst1hbr4e6tbv888sz91jc/tinymce/6/tinymce.min.js"></script>--}}
    {{--    <script src="https://raw.githubusercontent.com/sa-si-dev/tooltip/master/dist/tooltip.min.js"></script>--}}
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('assets/js/pages/demo.datatable-init.js')}}"></script>--}}
    <!-- end demo js-->
    <script>

        $(document).ready(function () {
            /*tinymce.init({
                selector: "#description",
                plugins: "emoticons autoresize",
                toolbar: "emoticons",
                toolbar_location: "bottom",
                menubar: false,
                statusbar: false
            });*/
            $( '.lead_name_cl' ).on('click', function(){
                let str_val = $(this).attr('data-type');
                var cursorPos = $('#description').prop('selectionStart');
                var v = $('#description').val();
                var textBefore = v.substring(0,  cursorPos );
                var textAfter  = v.substring( cursorPos, v.length );
                $('#description').val( textBefore+ str_val +textAfter );
                $('#description').focus();
            });

            $( '.sender_name_cl' ).on('click', function(){
                let str_val = $(this).attr('data-type');
                var cursorPos = $('#description').prop('selectionStart');
                var v = $('#description').val();
                var textBefore = v.substring(0,  cursorPos );
                var textAfter  = v.substring( cursorPos, v.length );
                $('#description').val( textBefore+ str_val +textAfter );
                $('#description').focus();
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            timelineActivity('{{Request::segment(4)}}');
            formValition('#content-messages-form');
            $('.content-messages-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: true,
                        type: 'POST',
                        url: '{{route('content.messages.store')}}',
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
                            location.reload();
                            {{--timelineActivity('{{Request::segment(4)}}');--}}
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
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
                                    toastrInfo('Phone no already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#customer_button").prop('disabled', false);
                            $("#customer_button").html('<i class="mdi mdi-floppy fs-5"></i> Save');
                        },
                        complete: function (data) {
                            view_id('{{ Request::segment(4) }}');
                            $('#content-messages-modal').modal('toggle');
                            $("#customer_button").html('Save');
                            $("#customer_button").prop('<i class="mdi mdi-floppy fs-5"></i> disabled', false);
                        }
                    });
                }
            });
        });



        function view_id(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{route('lead.show-customer-timeline')}}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#content-messages-form");
                    resetForm("#content-messages-form");

                    $('.name_small').html(res.data.name);
                    $('.description_small').html(res.data.description);
                }
            });
        }

        function timelineActivity(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{ route('content.messages.timeline-activity') }}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    var html_temp = '';
                    $.each(res, function (key, value) {
                        var ficon = '';
                        if (value.activity_type == 1)
                            ficon = '<i class="mdi mdi-account-plus-outline bg-info-lighten text-info timeline-icon"></i>';
                        if (value.activity_type == 2)
                            ficon = '<i class="mdi mdi-pencil bg-primary-lighten text-primary timeline-icon"></i>';
                        var activity_name = '';

                        if (value.activity_name)
                            activity_name = '<span class="text-black">' + value.activity_name + '</span>';

                        html_temp += '<div class="timeline-item">' +
                            ficon +
                            '<div class="timeline-item-info">' +
                            '<a class="text-black fw-bold mb-1 d-block">' + value.display_created_at + '</a>' +
                            activity_name+
                            '<p class="mb-0 pb-2">' +
                            '<small class="text-muted"><i class="mdi mdi-account-circle-outline"></i> by ' + value.created_by_name + '</small>' +
                            '</p>' +
                            '</div>' +
                            '</div>';
                    });
                    $("#timeline-info").html(html_temp);
                }
            });
        }

        function edit_id(id) {
            $.ajax({
                async: true,
                type: "GET",
                url: "{{route('content.messages.show')}}",
                data: {id: id},
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#content-messages-form");
                    resetForm("#content-messages-form");
                    $('#content-messages-form #id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $('#description').val(res.data.description);

                    $('.modal-title').text('Edit Message Template');
                    $("#content-messages-modal .advance-option").removeClass('d-none');
                    $("#content-messages-modal .advance-option-div").addClass('d-none');
                    $("#content-messages-modal #email").focus();
                    $('#content-messages-modal').modal('toggle');
                }
            });
        }

        function activity_remove_id(id) {
            if (id.length <= 0) {
                toastrWarning('Please select at least one record', 'Warning');
            } else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonColor: "#3085D6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!",
                    confirmButtonClass: "btn btn-primary",
                    cancelButtonClass: "btn btn-danger ml-1",
                    buttonsStyling: !1,
                    preConfirm: function () {
                        $.ajax({
                            type: "POST",
                            url: "{{route('content.messages.delete')}}",
                            data: {id: id},
                            dataType: "json",
                            success: function (data, textStatus, jqXHR) {
                                timelineActivity('{{Request::segment(4)}}');

                                // toastrSuccess('Successfully removed');
                                location.href = SITEURL+'/content/messages'


                            },
                            error: function (xhr, status, error) {
                                var errorMessage = xhr.status + ': ' + xhr.statusText
                                switch (xhr.status) {
                                    case 401:
                                        toastrError('Error in saving...', 'Error');
                                        break;
                                    case 422:
                                        toastrInfo('Please contact developer.', 'Info');
                                        break;
                                    case 409:
                                        toastrInfo('Name already exist.', 'Warning');
                                        break;
                                    default:
                                        toastrError('Error - ' + errorMessage, 'Error');
                                }
                            },
                            complete: function (data) {
                            }
                        });
                    }
                }).then(function (t) {
                    t.value && Swal.fire({
                        title: "Success",
                        text: "Your record has been updated.",
                        type: "success", showConfirmButton: !1,
                        timer: 1500,
                        confirmButtonClass: "btn btn-success",
                        showConfirmButton: false
                    })
                });
            }
        }
    </script>
@endpush
