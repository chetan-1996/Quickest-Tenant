@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title', 'User')
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
@endpush
@section('content')
    <style>
        .user-status{
            cursor: pointer;
        }
        #continue_btn {
            text-transform: uppercase;
            font-size: 0.9em;
            color: #fff;
            /* background-color: #17a2b8 !important; */
            border-radius: 5px;
            padding: 0.75em 3em !important;
            font-weight: 500;
            font-weight: 800;
        }

        #userLimitModal {
            backdrop-filter: blur(4px);
        }
    </style>
    {{--    <x-breadcrumbs pagename="USERS" pagetitle="VIEW_USER_LIST"/> --}}
    <div class="content-page">
        <div class="content">

            <!-- Start Content-->
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                @if (isset($plan->user_limit) && $plan->user_limit <= $userCount + 1)
                                   <a class="btn  btn-primary btn-sm mb-2" data-toggle="modal" id="mediumButton"
                                    data-target="#userLimitModal">
                                        <i class="mdi mdi-plus-circle"></i>Invite New Member
                                    </a>
                                @elseif (in_array('give-access-to-delete-other-users', $user_perm) || auth()->user()->company_id == null)
                                    <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2"
                                    onclick="openModal('#user-modal','New Team Member','#user-form','.modal-title',id=0,flag=6)"><i
                                            class="mdi mdi-plus-circle"></i> Invite New Member</a>
                                @else
                                    <a href="javascript:void(0);" class="btn btn-primary btn-sm mb-2"
                                    onclick="accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')"><i
                                            class="mdi mdi-plus-circle"></i> Invite New Member</a>
                                @endif
                                {{-- @if (in_array('add-user', $user_perm) || auth()->user()->company_id == null)
                                    <a href="{{route('user-create')}}" class="btn btn-info btn-sm mb-2"><i
                                            class="mdi mdi-plus-circle"></i> New</a>
                                @endif --}}

                                <div class="dropdown btn-group mb-2 d-none">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            id="select_count">0</span>Bulk Action
                                        <!-- <span class="badge badge-success-lighten" id="select_count">0</span> -->
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
                            <div class="page-title-left pt-2 fw-bold text-dark fs-2">
                                Team
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                {{-- <div class="row mb-2">
                                    <div class="col-xl-8">
                                        <form
                                            class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                                            <div class="col-auto">
                                                <div class="d-flex align-items-center">
                                                    <label for="fil_name" class="visually-hidden">Name</label>
                                                    <input type="text" class="form-control" id="fil_name" name="fil_name"
                                                        placeholder="name...">
                                                </div>
                                            </div>
                                            --}}{{-- <div class="col-auto">
                                                <div class="d-flex align-items-center">
                                                    <label for="fil_status" class="me-2">Status</label>
                                                    <select class="form-select" id="fil_status" name="fil_status">
                                                        <option value="">Choose...</option>
                                                        <option value="0">Active</option>
                                                        <option value="1">Deactive</option>
                                                    </select>
                                                </div>
                                            </div> --}}{{--
                                        </form>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="text-xl-end mt-xl-0 mt-2">
                                            <button type="button" class="btn btn-secondary waves-effect waves-light mr-1 mb-2"
                                                    id="resetFilter">
                                                <i class="mdi mdi-filter"></i> Reset Filters
                                            </button>
                                            <a href="{{route('user-create')}}" class="btn btn-info mb-2"><i
                                                    class="mdi mdi-plus-circle"></i> Add User</a>
                                            <div class="dropdown btn-group mb-2">
                                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span
                                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                        id="select_count">0</span>Bulk Action
                                                    <!-- <span class="badge badge-success-lighten" id="select_count">0</span> -->
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
                                    </div><!-- end col-->
                                </div> <!-- end row --> --}}
                                <table id="user-datatable" class="table dt-responsive table-sm nowrap w-100">
                                    <thead class="table-light">
                                    <tr>
                                        {{--                            <th><input type="checkbox" id="select_all"></th> --}}
                                        {{--                            <th>Role</th> --}}
                                        {{--                            <th>Senior User</th> --}}
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
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
                <div class="modal fade" id="userLimitModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
                    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="model-body p-0">
                                    <div id="screen_1" class="tabcontent" style="display: block;">
                                        <div class="image">
                                            {{-- <div> --}}
                                            {{-- <button> --}}
                                            <div style="float: right;">
                                                <i class="mdi mdi-close-circle"
                                                style="position: fixed;margin: 4px -33px;font-size: 25px;color: white;"
                                                data-bs-dismiss="modal"></i>
                                            </div>
                                            {{-- </button> --}}
                                            {{-- </div> --}}
                                            <img class="welcome_image" style="width: 100%;" alt=""
                                                src="{{ asset('images/model_image1.png') }}">
                                        </div>
                                        <div class="welcome_main d-flex justify-content-center align-items-center flex-column">
                                            <div class="welcome_part ">
                                                <h3 class="pt-3 pr-2 pl-2 fw-bold text-dark">OOPS !! Your New User invite limit is
                                                    over
                                                </h3>
                                            </div>
                                            <p class="px-5 pt-2 text-dark">Your User Limit is over please contact admin increase
                                                your user invite limit.</p>
                                            <div class="w-75 my-4 d-flex justify-content-center col-12">
                                                {{-- <div class="col-5">
                                                    <button type="button" class="btn btn-block btn-lg btn-primary continue_btn w-100"
                                                        data-bs-dismiss="modal">
                                                        Close
                                                    </button>
                                                </div> --}}
                                                {{-- <div class="col-2"></div> --}}
                                                {{-- <div class="col-5"> --}}
                                                {{-- <form action="{{ url('payment-checkout') }}" method="post">
                                                        @csrf
                                                        @php
                                                            $user = Auth::user();
                                                            $companyId = isset($user->company_id) ? $user->company_id : $user->id;
                                                            $companyData = App\Models\User::where('id', $companyId)->first();
                                                            $activePlan = App\Models\admin\Plans::where('id', isset($companyData) ? $companyData->plan_id : $user->plan_id)->first();
                                                        @endphp
                                                        <input type="hidden" name="plan" value="{{ $activePlan }}">
                                                        <input type="hidden" name="plan_id" value="{{ $activePlan->id }}">
                                                        <input type="hidden" name="add_user" value="0"> --}}
                                                <a href='/{{$segment}}/plan'>
                                                    <button type="submit"
                                                            class="btn btn-block btn-lg btn-primary continue_btn w-100"
                                                            id="continue_btn" data-bs-dismiss="modal">
                                                        Add More Users
                                                    </button>
                                                </a>
                                                {{-- </form> --}}
                                                {{-- </div> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="user-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-1 bg-light">
                                <h3 class="modal-title text-dark">Create New Team Member</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3 p-0">
                                <form class="user-form" id="user-form" action="#">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-1">
                                                        <input type="text" class="form-control bg-light text-dark" id="name"
                                                            name="name" required="" placeholder="Name">
                                                        <label for="name" class="form-label">Name <span
                                                                class="text-danger">*</span></label>
                                                        <input class="form-control" type="hidden" id="id" name="id"
                                                            value="0">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating mb-1">
                                                        <input type="text" class="form-control bg-light text-dark" id="mobile_no"
                                                            name="mobile_no" required="" placeholder="Mobile no"

                                                            {{--data-parsley-type="digits" data-parsley-minlength="10"
                                                            data-parsley-maxlength="15"--}}
                                                        >
                                                        <label for="mobile_no" class="form-label">Mobile no <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-floating mb-1">
                                                        <input class="form-control bg-light text-dark" type="email" id="email"
                                                            name="email" required="" placeholder="Enter email">
                                                        <label for="email" class="form-label">Email <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-floating mb-1">
                                                        <input class="form-control bg-light text-dark" type="text" id="role_name"
                                                            name="role_name" placeholder="Enter designation">
                                                        <label for="designation" class="form-label">Designation</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 bg-light px-2">
                                                    <h6 class="text-uppercase text-dark">PERMISSIONS</h6>


                                                    <h5 class="mt-3 my-1 text-dark">Lead access</h5>
                                                    <div class="mt-3">
                                                        <div class="form-check">
                                                            <input type="radio" id="customRadio1" name="data[0][permission_id]"
                                                                class="form-check-input" value="70">
                                                            <label class="form-check-label text-dark" for="customRadio1">Access
                                                                all
                                                                lead & Assign to anyone in team</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="radio" id="customRadio2" name="data[0][permission_id]"
                                                                class="form-check-input" value="71">
                                                            <label class="form-check-label text-dark" for="customRadio2">Access
                                                                self
                                                                leads only & Assign my leads to anyone in team</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input type="radio" id="customRadio3" name="data[0][permission_id]"
                                                                class="form-check-input" value="72" checked>
                                                            <label class="form-check-label text-dark" for="customRadio3">Access
                                                                self
                                                                leads only & Can’t Assign my leads to anyone in team</label>
                                                        </div>
                                                    </div>
                                                    <hr class="bg-dark my-3">

                                                    <h5 class="mt-3 my-1 text-dark">Give Access to attend unassigned leads</h5>
                                                    <div class="mt-2">
                                                        <div class="form-check form-check-inline">
                                                            <input type="hidden" class="form-control bg-light text-dark"
                                                                id="input_permission_radio1" name="data[1][permission_id]"
                                                                required="" value="73">
                                                            <input type="radio" id="permission_radio1"
                                                                name="data[1][permission_id]" class="form-check-input" checked=""
                                                                value="73">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio1">Yes</label>
                                                        </div>

                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio2"
                                                                name="data[1][permission_id]" class="form-check-input"
                                                                value="0">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio2">No</label>
                                                        </div>
                                                    </div>
                                                    <hr class="bg-dark my-3">

                                                    <h5 class="mt-3 my-1 text-dark">Give Access to active or deactive other users.</h5>
                                                    <div class="mt-2">
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio3"
                                                                name="data[2][permission_id]" class="form-check-input"
                                                                value="74">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio3">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio4"
                                                                name="data[2][permission_id]" class="form-check-input" checked
                                                                value="0">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio4">No</label>
                                                        </div>
                                                    </div>
                                                    <hr class="bg-dark my-3">

                                                    <h5 class="mt-3 my-1 text-dark">Template setting, Report visible & Integration</h5>
                                                    <div class="mt-2">
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio5"
                                                                name="data[3][permission_id]" class="form-check-input"
                                                                value="75">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio5">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio6"
                                                                name="data[3][permission_id]" class="form-check-input" checked=""
                                                                value="0">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio6">No</label>
                                                        </div>
                                                    </div>

                                                    <hr class="bg-dark my-3">

                                                    <h5 class="mt-3 my-1 text-dark">Access to Add and edit Files, message
                                                        template & Label</h5>
                                                    <div class="mt-2 mb-2">
                                                        <div class="form-check form-check-inline">
                                                            <input type="hidden" class="form-control bg-light text-dark"
                                                                id="input_permission_radio7" name="data[4][permission_id]"
                                                                required="" value="76">
                                                            <input type="radio" id="permission_radio7"
                                                                name="data[4][permission_id]" class="form-check-input" checked=""
                                                                value="76">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio7">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio8"
                                                                name="data[4][permission_id]" class="form-check-input"
                                                                value="0">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio8">No</label>
                                                        </div>
                                                    </div>

                                                    <hr class="bg-dark my-3">

                                                    <h5 class="mt-3 my-1 text-dark">Access to Add and edit item & product photos</h5>
                                                    <div class="mt-2 mb-2">
                                                        <div class="form-check form-check-inline">
                                                            <input type="hidden" class="form-control bg-light text-dark"
                                                                id="input_permission_radio9" name="data[5][permission_id]"
                                                                required="" value="77">
                                                            <input type="radio" id="permission_radio9"
                                                                name="data[5][permission_id]" class="form-check-input" checked=""
                                                                value="77">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio9">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio10"
                                                                name="data[5][permission_id]" class="form-check-input"
                                                                value="0">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio10">No</label>
                                                        </div>
                                                    </div>

                                                    <hr class="bg-dark my-3">

                                                    <h5 class="mt-3 my-1 text-dark">Give Access to delete leads</h5>
                                                    <div class="mt-2 mb-2">
                                                        <div class="form-check form-check-inline">
                                                            <input type="hidden" class="form-control bg-light text-dark"
                                                                id="input_permission_radio11" name="data[6][permission_id]"
                                                                required="" value="78">
                                                            <input type="radio" id="permission_radio11"
                                                                name="data[6][permission_id]" class="form-check-input" checked=""
                                                                value="78">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio11">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="permission_radio12"
                                                                name="data[6][permission_id]" class="form-check-input"
                                                                value="0">
                                                            <label class="form-check-label text-dark"
                                                                for="permission_radio12">No</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary fullscreen" form="user-form" id="user_button" type="submit">
                                        <i class="uil-arrow-circle-right"></i> Save
                                    </button>
                                </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>                                                             
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('js/pages/demo.datatable-init.js')}}"></script> --}}
    <!-- end demo js-->
    <script>
        $(document).on('click', '#mediumButton', function (event) {
            event.preventDefault();
            let href = $(this).attr('data-attr');
            $('#userLimitModal').modal("show");
        });
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            "use strict";
            var table = $("#user-datatable").DataTable({
                // dom: 'Bfrtp',
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
                        title: 'User List',
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
                        title: 'User List',
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
                        title: 'User List',
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
                    url: "{{ route('tenant.user.userdata', ['tenant' => $segment]) }}",
                    data: function (d) {
                        d.status = $('#fil_status').val(),
                            d.name = $('#fil_name').val(),
                            d.search = $('input[type="search"]').val()
                    }
                },
                columns: [
                    /*{
                        data: 'id', name: 'u1.id', orderable: false,
                        render: function (data, type, row) {
                            return '<input type="checkbox" class="single_checkbox" data-id="' + row.action + '">';
                        }
                    },*/
                    // {data: 'user_role', name: 'u1.user_role', orderable: true},
                    // {data: 'assign_user', name: 'u1.assign_user'},
                    {
                        data: 'name',
                        name: 'u1.name',
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

                    {
                        data: 'role_name',
                        name: 'u1.role_name'
                    },
                    {
                        data: 'mobile_no',
                        name: 'u1.mobile_no',
                        render: function (data, type, row) {
                            if(row.mobile_no != '' && row.mobile_no != null) {
                                return '<td>' +
                                    '<h5 class="font-15 mb-1 fw-normal text-dark">' + row.mobile_no +
                                    '</h5>' +
                                    // '<span class="text-dark font-14">' + row.email + '</span>'+
                                    '</td>';
                            } else {
                                return 'N.A.';
                            }
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                           /* if (data == 0)
                                return '<span class="badge badge-danger-lighten">Invited</span>';
                            else {
                                return '<span class="badge badge-success-lighten">Activate</span>';
                            }*/

                            {{--var fun_status = "change_status('" + row.action + "', 1,'{{route('testimonial.edit-status')}}','#testimonial-datatable')";--}}
                            if (data == 0)
                                return '<span class="badge badge-danger-lighten">Invited</span>';
                            if (data == 1) {
                                @if (in_array('give-access-to-delete-other-users', $user_perm))
                                    fun_status = "change_status('" + row.action + "', 2,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                @else
                                    fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                                @endif
                                let rx ='';
                                if (row.temp_user_id != {{auth()->user()->id}}) {
                                    rx = '<span class="badge badge-success-lighten user-status" onclick="' + fun_status + '">Activate</span>';
                                }
                                return rx;
                                }

                            if (data == 2) {
                                @if (in_array('give-access-to-delete-other-users', $user_perm))
                                    fun_status = "change_status_user('" + row.action + "', 1,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                @else
                                    fun_status = "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                                @endif
                                    return '<span class="badge badge-danger-lighten user-status" onclick="' + fun_status + '">Deactive</span>';
                            }
                            /*if (data == 1) {
                                fun_status = "change_status('" + row.action + "', 2,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                return '<span class="badge badge-success-lighten" onclick="' + fun_status + '">Activate</span>';
                            }

                            if (data == 2) {
                                fun_status = "change_status('" + row.action + "', 1,'{{route('tenant.user.edit-status', ['tenant' => $segment])}}','#user-datatable')";
                                return '<span class="badge badge-danger-lighten" onclick="' + fun_status + '">Deactive</span>';
                            }*/

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
                            var delete_fun =
                                "accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the teams.')";
                            @endif
                            var resend_fun = "resend_id('" + row.action + "')";
                            {{-- var delete_fun = "remove_id('" + row.action + "','{{route('tenant.user.delete', ['tenant => $segment])}}','#user-datatable')"; --}}
                            var editlink = "{{ url('user-edit') }}/" + row.action;

                            let action_str =
                               /* '<a href="javascript:void(0)" class="action-icon" id="remove_' + row
                                    .action + '"  onclick="' + delete_fun + '">' +
                                '<i class="mdi mdi-delete"></i>' +
                                '</a>' +*/
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
                    },
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });
            table.buttons().container().appendTo("#unit-datatable_wrapper .col-md-6:eq(0)"), $(
                "#alternative-page-datatable").DataTable({
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
        });

        //Remove multiple record
        $('.delete_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            remove_id(join_selected_values, '{{ route('tenant.unit.delete', ['tenant' => $segment]) }}', '#user-datatable');
        });

        $('.active_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 0, '{{ route('tenant.unit.edit-status', ['tenant' => $segment]) }}', '#user-datatable');
        });

        $('.deactive_status_all').on('click', function (e) {
            var allVals = [];
            $(".single_checkbox:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });
            var join_selected_values = allVals.join(",");
            change_status(join_selected_values, 1, '{{ route('tenant.unit.edit-status', ['tenant' => $segment]) }}', '#user-datatable');
        });

        $('.user-form').on('submit', function (e) {
            e.preventDefault();
            if ($(this).parsley().isValid()) {
                $.ajax({
                    // async: false,
                    type: 'POST',
                    url: '{{ route('tenant.user.user-create-new', ['tenant' => $segment]) }}',
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: new FormData(this),
                    dataType: "json",
                    beforeSend: function () {
                        $("#user_button").prop('disabled', true);
                        $("#user_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    success: function (data, textStatus, xhr) {

                        toastrSuccess('Successfully saved...', 'Success');
                        $("#user_button").prop('disabled', false);
                        $("#user_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        if ($('#id').val() == 0) {
                            $("#user_button").html('<i class="uil-arrow-circle-right"></i> Invite');
                        }
                        if (xhr.status == 201)
                            location.reload();
                        {{-- $(location).attr("href", "{{ route('user.index') }}"); --}}
                        // goBack();
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
                                toastrInfo('Email already exist.', 'Warning');
                                break;
                            default:
                                toastrError('Error - ' + errorMessage, 'Error');
                        }
                        $("#user_button").prop('disabled', false);
                        $("#user_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        if ($('#id').val() == 0) {
                            $("#user_button").html('<i class="uil-arrow-circle-right"></i> Invite');
                        }
                    },
                    complete: function (data) {
                        // $("#user_button").html('Save');
                        $("#user_button").prop('disabled', false);
                        $("#user_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        if ($('#id').val() == 0) {
                            $("#user_button").html('<i class="uil-arrow-circle-right"></i> Invite');
                        }
                    }
                });
            }
        });

        function edit_id(id) {//alert('here')
            $.ajax({
                type: "GET",
                async: false,
                url: "{{ route('tenant.user.user-edit-new', ['tenant' => $segment]) }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    resetFormValidation("#user-form");
                    resetForm("#user-form");
                    $('#id').val(res.data.user.id);
                    $('#name').val(res.data.user.name);
                    $('#mobile_no').val(res.data.user.mobile_no);
                    $('#email').val(res.data.user.email);
                    $('#role_name').val(res.data.user.role_name);

                    if (res.data.user_permissions[0] == 70) {
                        $("#customRadio1").prop('checked', true);
                    }
                    if (res.data.user_permissions[0] == 71) {
                        $("#customRadio2").prop('checked', true);
                    }
                    if (res.data.user_permissions[0] == 72) {
                        $("#customRadio3").prop('checked', true);
                    }

                    /* if(res.data.user_permissions[1]==73){
                         $("#permission_radio1").prop('checked', true);
                         $("#input_permission_radio1").val(res.data.user_permissions[1]);

                     }else{
                         $("#permission_radio2").prop('checked', true);
                     }*/
                    /*
                                        if(res.data.user_permissions[2]==74){
                                            $("#permission_radio3").prop('checked', true);

                                        }else{
                                            $("#permission_radio4").prop('checked', true);
                                        }*/

                    /* if(res.data.user_permissions[3]==75){
                         $("#permission_radio5").prop('checked', true);

                     }else{
                         $("#permission_radio6").prop('checked', true);
                     }*/

                    if (jQuery.inArray(73, res.data.user_permissions) != -1) {
                        $("#permission_radio1").prop('checked', true);
                        $("#input_permission_radio1").val(73);

                    } else {
                        $("#permission_radio2").prop('checked', true);
                    }

                    if (jQuery.inArray(74, res.data.user_permissions) != -1) {
                        $("#permission_radio3").prop('checked', true);

                    } else {
                        $("#permission_radio4").prop('checked', true);
                    }

                    if (jQuery.inArray(75, res.data.user_permissions) != -1) {
                        $("#permission_radio5").prop('checked', true);

                    } else {
                        $("#permission_radio6").prop('checked', true);
                    }

                    if (jQuery.inArray(76, res.data.user_permissions) != -1) {
                        $("#permission_radio7").prop('checked', true);
                        $("#input_permission_radio7").val(76);

                    } else {
                        $("#permission_radio8").prop('checked', true);
                    }

                    if (jQuery.inArray(77, res.data.user_permissions) != -1) {
                        $("#permission_radio9").prop('checked', true);
                        $("#input_permission_radio9").val(77);

                    } else {
                        $("#permission_radio10").prop('checked', true);
                    }

                    if (jQuery.inArray(78, res.data.user_permissions) != -1) {
                        $("#permission_radio11").prop('checked', true);
                        $("#input_permission_radio12").val(78);

                    } else {
                        $("#permission_radio12").prop('checked', true);
                    }


                    $('.modal-title').text('Edit Member');
                    $('#user-modal').modal('toggle');
                    $("#user_button").html('<i class="uil-arrow-circle-right"></i> Save');
                    if ($('#id').val() == 0) {
                        $("#user_button").html('<i class="uil-arrow-circle-right"></i> Invite');
                    }
                }
            });
        }

        function resend_id(id) {
            $.ajax({
                type: "POST",
                async: false,
                url: "{{ route('tenant.user.resend-mail', ['tenant' => $segment]) }}",
                data: {
                    id: id
                },
                dataType: "json",
                success: function (res) {
                    toastrSuccess('Mail Resend Successfully!', 'Success');
                }
            });
        }
    </script>
@endpush
