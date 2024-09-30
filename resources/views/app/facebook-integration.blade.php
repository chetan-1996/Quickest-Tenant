@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
    use Carbon\Carbon;
    use App\Models\{Estimate, PlanHistory};
@endphp
@extends('app.layouts.app')
@section('title', 'Lead Timeline')
@push('styles')
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
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

        .radiobtn label:after,
        .radiobtn label:before {
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

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }


        #customer-datatable tbody tr {
            cursor: pointer;
        }

        .form-check-inline {
            margin-right: 0.5rem !important;
        }

        tbody .label_td {
            /*max-width:10px;*/
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            /*white-space: inherit !important;*/
        }

        /*@media (min-width: 1400px)*/
        /*    .container, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {*/
        /*        max-width: 979px;*/
        /*    }*/
    </style>
@endpush
@section('content')
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">
            <!-- start page title -->

            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">

                            @if($user_data->facebook_id)
                                <div class="page-title-right">
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle arrow-none card-drop btn btn-dark btn-sm"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                            Options <i class="mdi mdi-dots-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- item-->
                                            {{--<a href="{{url($segment.'/auth/facebook') }}" target="popup" onclick="window.open('{{url($segment.'/auth/facebook') }}','popup','width=600,height=600'); return false;" class="dropdown-item"><i
                                                    class="mdi mdi-circle-edit-outline me-1"></i>Manage Facebook Pages</a>--}}
                                            <a href="{{url($segment.'/auth/facebook') }}" class="dropdown-item"><i class="mdi mdi-circle-edit-outline me-1"></i>Manage Facebook Pages</a>
                                            <a href="{{url($segment.'/auth/facebook') }}" class="dropdown-item" onclick="#"><i class="mdi mdi-circle-edit-outline me-1"></i>Renew Facebook permissions</a>
                                            {{--<a href="{{url($segment.'/auth/facebook') }}" target="popup" onclick="window.open('{{url($segment.'/auth/facebook') }}','popup','width=600,height=600'); return false;" class="dropdown-item"
                                            onclick="#"><i
                                                    class="mdi mdi-circle-edit-outline me-1"></i>Renew Facebook permissions</a>--}}
                                            <!-- item-->
                                            <a href="javascript:void(0);"
                                            onclick="facebook_disconnected()"
                                            class="dropdown-item text-danger"><i
                                                    class="mdi mdi-delete-outline me-1"></i>Disconnect Facebook account</a>

                                        </div>
                                    </div>

                                </div>
                            @endif

                            <h2 class="page-title fw-bold text-dark text-capitalize"><small>
                                <a class="page-title" href="">Integrations</a>
                                <i class="mdi mdi-greater-than"></i></small>Facebook</h2>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mt-2 mb-2">
                        <h2 class="page-title fw-bold text-dark text-capitalize">Facebook Integration
                        </h2>
                    </div>
                </div>

                <!-- end page title -->
                @if($user_data->facebook_id)
                    <div class="row">
                        <div class="mt-2">
                            <h4 class="m-0 pb-2">
                                <a class="text-dark">Facebook Account</a>
                            </h4>

                            <div class="card mb-2">
                                <div class="card-body">
                                    <!-- task -->
                                    <div class="row justify-content-sm-between">
                                        <div class="col-sm-12 mb-2 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                {{--<img class="me-1 rounded-circle"
                                                    src="{{Storage::disk('s3')->temporaryUrl('template/facebook.png',Carbon::now()->addMinutes(20));}}"
                                                    width="30" alt="Generic placeholder image">--}}
                                                <img class="me-1 rounded-circle"
                                                    src="{{Storage::url('template/facebook.png')}}"
                                                    width="30" alt="Generic placeholder image">
                                                <div>
                                                    <h5 class="d-flex text-dark align-items-center">
                                                        {{($user_data->facebook_name)}}<small class="fw-normal ms-3"></small>
                                                    </h5>
                                                </div>
                                            </div>   <!-- end checkbox -->
                                            <span class="text-primary">Connected</span>
                                        </div> <!-- end col -->
                                    </div>
                                    <!-- end task -->
                                </div> <!-- end card-body-->
                            </div> <!-- end card -->

                        </div>

                        <div class="mt-2">
                            <div class="page-title-box">
                                <div class="page-title-right font"
                                    style="margin-top: 0px !important;font-weight: 500 !important;">
                                    <a href="{{url('auth/facebook') }}"><i class="mdi mdi-cog"></i> Manage Facebook Pages</a>
                                    {{--<a href="{{url('auth/facebook') }}" target="popup" onclick="window.open('{{url('auth/facebook') }}','popup','width=600,height=600'); return false;"><i class="mdi mdi-cog"></i> Manage Facebook Pages</a>--}}
                                </div>
                                <h4 class="m-0 pb-2">
                                    <a class="text-dark" data-bs-toggle="collapse" href="#todayTasks" role="button"
                                    aria-expanded="false" aria-controls="todayTasks">
                                        Connected Pages <span class="text-dark">({{($pages)?count($pages):0}})</span>
                                    </a>
                                </h4>
                            </div>
                        </div>


                        <div class="collapse show">
                            @if($pages)
                                @foreach($pages as $key => $page)


                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <!-- task -->
                                            <div class="row justify-content-sm-between">
                                                <div class="col-sm-6 mb-2 mb-sm-0">
                                                    <div class="d-flex align-items-center">

                                                        <div>
                                                            <h5 class="d-flex text-dark align-items-center">
                                                                {!! $page['name'] !!}<small class="fw-normal ms-3"></small>
                                                            </h5>
                                                            <span>{!! (array_key_exists('leadgen_forms', $page['lead_forms']))?count($page['lead_forms']['leadgen_forms']):0 !!} lead forms connected</span>
                                                        </div>
                                                    </div>   <!-- end checkbox -->
                                                </div> <!-- end col -->

                                                <div class="col-sm-6">
                                                    <div class="d-flex justify-content-between">
                                                        <div id="tooltip-container">

                                                        </div>
                                                        <div>
                                                            <ul class="list-inline text-end mb-0">
                                                                <li class="list-inline-item ms-2 d-flex align-items-center">
                                                                    <span class="p-1"><i
                                                                            class="mdi mdi-check-bold mdi-24px text-dark"></i></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div> <!-- end .d-flex-->
                                                </div> <!-- end col -->

                                                <div class="col-sm-12">
                                                    <ul class="list-group list-group-flushs mt-2">
                                                        @foreach($page['lead_forms_name'] as $key => $page_form_name)
                                                        <li class="list-group-item">{{$page_form_name['name']}}</li>
                                                        @endforeach
                                                    </ul>
                                                </div> <!-- end col -->
                                            </div>
                                            <!-- end task -->
                                        </div> <!-- end card-body-->
                                    </div> <!-- end card -->
                                @endforeach
                            @endif
                        </div> <!-- end .collapse-->
                    </div>
            </div> <!-- end col -->
            </div>
            @else

                <div class="row">
                    <div class="mt-2">
                        <div class="card mb-2">
                            <div class="card-body">
                                <!-- task -->
                                <div class="row text-center">
                                    <div class="col-sm-12 mb-2 mb-sm-0">
                                        <div class="mt-0 pt-2">
                                        {{-- <img class="me-1 rounded-circle"
                                            src="{{Storage::disk('s3')->temporaryUrl('template/quickets_fb.png',Carbon::now()->addMinutes(20));}}"
                                                width="40%" alt="Generic placeholder image">--}}
                                            <img class="me-1 rounded-circle"
                                                src="{{Storage::url('template/quickets_fb.png')}}"
                                                width="40%" alt="Generic placeholder image">
                                                {{--  <h2 class="text-dark pb-3">This lead is currently assigned to someone else</h2>--}}
                                            <p class="text-dark"> Receive new leads from your Facebook Lead Ads into your Quickest account.</p>
                                            <p class="text-dark pb-3"> Tap 'Login with Facebook' and select the Facebook Pages you want to receive leads from.</p>
                                            <!-- Toogle to second dialog -->
                                            {{--<a class="btn btn-primary text-white" href="{{url($segment.'/auth/facebook') }}" target="popup" onclick="window.open('{{url($segment.'/auth/facebook') }}','popup','width=600,height=600'); return false;" style="text-decoration: none !important;color: black;"><i class="mdi mdi-facebook fs-5"></i> Login with Facebook
                                            </a>--}}

                                            <a class="btn btn-primary text-white" href="{{url($segment.'/auth/facebook') }}" style="text-decoration: none !important;color: black;"><i class="mdi mdi-facebook fs-5"></i> Login with Facebook
                                            </a>

                                        {{-- <a class="btn btn-primary text-white" style="text-decoration: none !important;color: black;"><i class="mdi mdi-facebook fs-5"></i> Coming Soon...
                                            </a>--}}

                                        </div>

                                    </div> <!-- end col -->
                                </div>
                                <!-- end task -->
                            </div> <!-- end card-body-->
                        </div> <!-- end card -->
                    </div>
                </div>
                </div> <!-- end col -->
                </div>
            @endif
            <!--end row -->
            </div>
            <div id="facebook-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel">
                <div class="modal-dialog  modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-1 bg-light">
                            <h3 class="modal-title text-dark">Facebook</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3 p-0">
                            <div class="row">
                                <div class="col-md-12">
                                    <form class="facebook-form" id="facebook-form" action="#" method="POST">
                                        <div class="row">
                                            <div class="col-12">
                                                <div id="sidebar-user">
                                                    <div class="justify-content-between align-items-center">
                                                        <input type="hidden" value="{{$user_data->facebook_integration}}"
                                                            id="facebook_checkbox">
                                                        <h6 class="form-label font-16">Facebook Lead assigne</h6>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="fbRadio1" name="fbRadio"
                                                                value="Unassigned"
                                                                class="form-check-input" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Unassigned')? 'checked':''}}>
                                                            <label class="form-check-label"
                                                                for="fbRadio1" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Unassigned')? 'checked':''}}>Unassigned</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="fbRadio2" name="fbRadio"
                                                                value="Round-Robin"
                                                                class="form-check-input" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Round-Robin')? 'checked':''}}>
                                                            <label class="form-check-label"
                                                                for="fbRadio2" {{(isset($user_data->facebook_integration) && $user_data->facebook_integration=='Round-Robin')? 'checked':''}}>Round-Robin</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="col-12 mt-2 facebook-data facebook_setting assign-facebook-user-list {!! (isset($india_mart_data->indiamart_token) && ($user_data->indiamart_integration=='Round-Robin'))? '':'d-none' !!}">
                                                <table id="user-facebook-datatable"
                                                    class="table dt-responsive table-sm nowrap w-100">
                                                    <thead class="table-light">
                                                    <tr>
                                                        <th><input type="checkbox" class="form-check-input" id="select_all_fb">
                                                        </th>
                                                        <th>Name</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                                {{--                                    <a href="{{ url('settings/general') }}" >Go to indiamart settings</a>--}}
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer facebook-data facebook_setting assign-facebook-user-list">
                            <div class="text-end">
                                <a class="btn btn-primary" id="round-robin-facebook-button">
                                    <i class="mdi mdi-floppy fs-5"></i> Save
                                </a>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <!-- Modal -->
        </div>
    </div>
</div>

@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script> -->
    <!-- third party js -->
    @include('layouts.partials.datatable-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>

    <script>

        function facebook_disconnected() {

                Swal.fire({
                    title: "Are you sure you want to disconnect your Facebook account?",
                    text: "This will stop your Quickest account from receiving any new lead alerts or details from your Facebook Lead Ads.",
                    // type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085D6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Disconnect",
                    confirmButtonClass: "btn btn-primary",
                    cancelButtonClass: "btn btn-danger ml-1",
                    buttonsStyling: false,
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        $(".swal2-confirm").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        $(".swal2-confirm").prop('disabled', true);
                    },
                    preConfirm: function () {
                        $.ajax({
                            async:false,
                            type: "POST",
                            url: '{{route('tenant.integration.facebook-diconnected', ['tenant' => $segment])}}',
                            // data: { id: id },
                            dataType: "json",
                            success: function (data, textStatus, jqXHR) {


                                    // toastrSuccess('Successfully Facebook account disconnected');

                                    // location.reload();


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
                                        toastrErrorWithText(xhr.responseJSON, 'Warning');

                                        $(".swal2-confirm").prop('disabled', false);
                                        $(".swal2-confirm").html('Disconnect');
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
                    });
                    window.location.href = '{{route("tenant.integration.facebook-leads-routing", ['tenant' => $segment])}}';
                    // location.href = "https://dev.quickestimate.co/integration";
                    // location.reload(true);
                    // window.location.reload(true);
                }).finally(() => {
                    $(".swal2-confirm").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    $(".swal2-confirm").prop('disabled', true);
                });

        }


        $(document).ready(function () {
            @if(request()->has('flag'))
            openModal('#facebook-modal', 'Facebook', '#facebook-form', '.modal-title', id = 0)
            @endif

            const userFbIntegration = $("#facebook_checkbox").val();
            if (userFbIntegration === "Unassigned") {
                $("#fbRadio1").prop("checked", true);
                $(".assign-facebook-user-list").addClass('d-none');

            } else if (userFbIntegration === "Round-Robin") {
                $("#fbRadio2").prop("checked", true);
                $(".assign-facebook-user-list").removeClass('d-none');
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var tableFb = $("#user-facebook-datatable").DataTable({
                // dom: 'Bfrtp',
                /* dom: "<'row'<'col-sm-12 col-md-6 text-left'B><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>",*/
                responsive: false,
                processing: true,
                serverSide: true,
                stateSave: true,
                lengthChange: !1,
                pageLength: 50,
                // paging: false,
                bFilter: false,
                // ordering: false,
                searching: false,
                bInfo: false,
                // "paging": false,//Dont want paging
                // "bPaginate": false,//Dont want paging  ,
                buttons: [{
                    extend: 'pageLength',
                    attr: {
                        class: 'btn btn-light buttons-collection dropdown-toggle buttons-page-length',
                    },
                    exportOptions: {
                        columns: ':visible'
                    }
                },
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
                    url: "{{ route('tenant.integration.facebook-user-list', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                columns: [
                    {
                        data: 'id', name: 'u1.id', orderable: false,
                        render: function (data, type, row) {
                            var is_checked = "";
                            var is_disable = "";
                            if (row.is_checked == "checked") {
                                is_checked = "checked";
                            }
                            if (row.status == 2) {
                                is_disable = "disabled";
                            }
                            return '<input type="checkbox" class="single_checkbox_fb form-check-input" data-id="' + row.action + '" ' + is_checked + ' ' + is_disable + '>';
                        }
                    },
                    // {data: 'user_role', name: 'u1.user_role', orderable: true},
                    // {data: 'assign_user', name: 'u1.assign_user'},
                    {
                        data: 'name',
                        name: 'u1.name',
                        orderable: true,
                        render: function (data, type, row) {
                            let st = '';
                            if (row.company_id == null) {
                                st = ' (1st Register)';
                            }
                            return '<td>' +
                                '<h5 class="font-15 mb-1 fw-normal text-dark fw-bold">' + row.name +
                                st +
                                '</h5>' +
                                '<span class="text-dark font-14">' + row.email + '</span>' +
                                '</td>';
                        }
                    },

                    /* {
                         data: 'role_name',
                         name: 'u1.role_name'
                     },
                     {
                         data: 'mobile_no',
                         name: 'u1.mobile_no',
                         render: function (data, type, row) {
                             return '<td>' +
                                 '<h5 class="font-15 mb-1 fw-normal text-dark">' + row.mobile_no +
                                 '</h5>' +
                                 // '<span class="text-dark font-14">' + row.email + '</span>'+
                                 '</td>';
                         }
                     },
                     {
                         data: 'status',
                         name: 'status',
                         render: function (data, type, row) {
                             if (data == 0)
                                 return '<span class="badge badge-danger-lighten">Invited</span>';
                             if (data == 1) {
                                @if (in_array('give-access-to-delete-other-users', $user_perm))
                                                    fun_status = "change_status('" + row.action + "', 2,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                                                @else
                                                    fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                                @endif

                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Activate</span>';
                            }

                            if (data == 2) {
                                @if (in_array('give-access-to-delete-other-users', $user_perm))
                                                    fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                                                @else
                                                    fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                                @endif
                                                    return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                                            }
                                        }
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                render: function (data, type, row) {

                        @if (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                                            var edit_fun = "edit_id('" + row.action + "')";
                        @else
                                            var edit_fun =
                                                "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                        @endif

                        @if (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                            var delete_fun = "remove_id('" + row.action +
                                "','{{ route('tenant.user.delete', ['tenant' => $segment]) }}','#user-datatable')";
                                    @else
                            var delete_fun = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                        @endif
                        var resend_fun = "resend_id('" + row.action + "')";
                        {{-- var delete_fun = "remove_id('" + row.action + "','{{route('tenant.user.delete', ['tenant' => $segment])}}','#user-datatable')"; --}}
                        var editlink = "{{ url('user-edit') }}/" + row.action;

                            let action_str =
                                '<a href="javascript:void(0);" class="action-icon mr-1" onclick="' +
                                resend_fun + '" id="resed_' + row.action +
                                '"  title="Resend Invite">' +
                                '<i class="mdi mdi-mail"></i>' +
                                '</a>';
                            if (row.company_id == null) {
                                action_str = '';
                            }
                            return '<div class="invoice-action">' +
                                '<a href="javascript:void(0);" class="action-icon mr-1" onclick="' +
                                edit_fun + '" id="edit_' + row.action + '" >' +
                                '<i class="mdi mdi-square-edit-outline"></i>' +
                                '</a>' +
                                action_str +
                                '</div>';
                        }
                    },*/
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });


            $('input[name="fbRadio"]').change(function () {
                const selectedValue = $(this).val();
                console.log(selectedValue);
                $.ajax({
                    url: SITEURL + "/settings/facebook-flag/update",
                    method: 'POST',
                    data: {
                        facebook_integration: selectedValue
                    },
                    success: function (msg) {
                        if (selectedValue === "Unassigned") {
                            $("#fbRadio1").prop("checked", true);
                            // $(".assign-india-mart-user-list").hide();
                            $(".assign-facebook-user-list").addClass('d-none');
                        }
                        if (selectedValue === "Round-Robin") {
                            $("#fbRadio2").prop("checked", true);


                            // $(".assign-india-mart-user-list").show();
                            $(".assign-facebook-user-list").removeClass('d-none');
                            tableFb
                                .search('')
                                .columns().search('')
                                .draw();
                        }
                        toastrSuccess('Successfully saved...', 'Success');
                    }
                });
            });

            $('#round-robin-facebook-button').on('click', function (e) {
                var allVals = [];
                $(".single_checkbox_fb:checked").each(function () {
                    allVals.push($(this).attr('data-id'));
                });
                var join_selected_values = allVals.join(",");


                if (join_selected_values.length <= 0) {
                    toastrWarning('Please select at least one record', 'Warning');
                    return false;
                }
                Swal.fire({
                    title: "Are you sure ?",
                    // text: "You won't be able to revert this!",
                    // text: "Are you sure ?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonColor: "#3085D6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, change it!",
                    confirmButtonClass: "btn btn-primary",
                    cancelButtonClass: "btn btn-danger ml-1",
                    buttonsStyling: !1,
                    preConfirm: function () {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('tenant.integration.facebook-update-user-list', ['tenant' => $segment]) }}",
                            data: {id: join_selected_values, status: 0},
                            dataType: "json",
                            success: function (data, textStatus, jqXHR) {
                                // toastrSuccess('Successfully updated');
                                $('#user-datatable').DataTable().ajax.reload();
                                $('#select_all').prop('checked', false);
                                $("#select_count").html(0);
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
                                        $('#user-datatable').DataTable().ajax.reload();

                                        toastrErrorWithText(xhr.responseJSON, 'Warning');
                                }
                            },
                            complete: function (data) {
                            }
                        });
                    }
                }).then(function (t) {
                    if (t.isConfirmed)
                        t.value && Swal.fire({
                            title: "Success",
                            text: "Your record has been updated.",
                            type: "success",
                            showConfirmButton: !1,
                            timer: 1500,
                            confirmButtonClass: "btn btn-success",
                            showConfirmButton: false
                        })
                    toastrSuccess('Successfully saved...', 'Success');
                    $('#facebook-modal').modal('toggle');
                    // var oldValueArr = old_value.split('_');
                    // $('#example-select_' + oldValueArr[1]).val(oldValueArr[0]);
                });

            });
        })
    </script>
@endpush
