@extends('app.layouts.app')
@section('title','Follow up history')
@push('styles')
    <style  >
        .clockpicker-popover {
            z-index: 9999999999 !important;
        }

        .table-responsive-stack tr {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: horizontal;
            -webkit-box-direction: normal;
            -ms-flex-direction: row;
            flex-direction: row;
        }


        .table-responsive-stack td,
        .table-responsive-stack th {
            display: block;
            /*
            flex-grow | flex-shrink | flex-basis   */
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
        }

        .table-responsive-stack .table-responsive-stack-thead {
            font-weight: bold;
        }

        @media screen and (max-width: 768px) {
            .table-responsive-stack tr {
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                -ms-flex-direction: column;
                flex-direction: column;
                border-bottom: 3px solid #ccc;
                display: block;

            }

            .table-responsive-stack .table-action {
                background: #f2f3f7 !important;
                text-align: center !important;
            }

            /*  IE9 FIX   */
            .table-responsive-stack td {
                float: left \9;
                width: 100%;
            }
        }

        .nav-pills > li > a {
            color: #000000;
            font-weight: 600;
        }

        .duetoday-tbody, .upcoming-tbody, .overdue-tbody, .nofollowup-tbody, .someday-tbody {
            cursor: pointer;
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
                            <div class="page-title-right"></div>
                            <h4 class="page-title">General Settings</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-sm-3 mb-2 mb-sm-0">
                        <div class="card">
                            <div class="card-body">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active show" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home"
                                    aria-selected="true">
                                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                        <span class="d-none d-md-block">Follow Up Notes </span>
                                    </a>
                                    <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false">
                                        <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                        <span class="d-none d-md-block">Duplicate Leads</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end col-->

                    <div class="col-sm-9">
                        <div class="card">
                            <div class="card-body">
                                <div class="tab-content" id="v-pills-tabContent">
                            <div class="tab-pane fade active show" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                <p class="mb-0">
                                    <div id="sidebar-user">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="font-16 fw-bold m-0" for="follow_up_note_req_flg">Follow Up Notes Is Mandatory</label>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" name="follow_up_note_req_flg" id="follow_up_note_req_flg" {{($user_data->follow_up_note_req_flg==1)? 'checked':''}}>
                                            </div>
                                        </div>
                                    </div>
                                </p>
                            </div>
                            <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                <p class="mb-0">
                                <h4 class="text-xs font-bold">Duplicate Lead Handling</h4>
                                <p class="mb-2">When a new lead is received via external lead source integrations and their phone number matches an existing lead:</p>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="lead_merge_flag_new" name="lead_merge_flag" class="form-check-input lead_merge_flag" value="0" {{($user_data->lead_merge_flag==0)? 'checked':''}}>
                                        <label class="form-check-label" for="lead_merge_flag_new">Create a new client</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="lead_merge_flag_merge" name="lead_merge_flag" class="form-check-input lead_merge_flag" value="1" {{($user_data->lead_merge_flag==1)? 'checked':''}}>
                                        <label class="form-check-label" for="lead_merge_flag_merge">Merge with the existing lead</label>
                                    </div>
                                </div>
                                </p>
                            </div>
                        </div> <!-- end tab-content-->
                            </div>
                        </div>
                    </div> <!-- end col-->
                </div>
                <!-- end row-->

                <div class="row">
                    {{--<div class="col-md-4 col-xxl-4">
                        <div class="card">
                            <div class="card-body">

                                <div id="sidebar-user">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <label class="font-16 fw-bold m-0" for="follow_up_note_req_flg">Follow Up Notes Is Mandatory</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="follow_up_note_req_flg" id="follow_up_note_req_flg" {{($user_data->follow_up_note_req_flg==1)? 'checked':''}}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                {{-- <div class="col-md-4 col-xxl-4">
                        <div class="card">
                            <div class="card-body">
                                <div id="sidebar-user">
                                    <div class="justify-content-between align-items-center">
                                            <input type="hidden" value="{{$user_data->indiamart_integration}}" id="indiamart_checkbox">
                                            <label class="font-16 fw-bold m-0" for="customRadio1">India Mart Lead assigne</label>
                                            <div class="form-check">
                                                <input type="radio" id="customRadio1" name="customRadio" value="Unassigned" class="form-check-input">
                                                <label class="form-check-label" for="customRadio1" {{($user_data->indiamart_integration=='Unassigned')? 'checked':''}}>Unassigned</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="customRadio2" name="customRadio" value="Round-Robin" class="form-check-input">
                                                <label class="form-check-label" for="customRadio2" {{($user_data->indiamart_integration=='Round-Robin')? 'checked':''}}>Round-Robin</label>
                                            </div>
                                            @if ($user_data->indiamart_integration=='Round-Robin')
                                                <a href="{{ url('indiamart-user-list') }}" >Select Users</a>
                                            @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                </div>
                <!-- end row -->
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- App js -->
    <script src="{{ asset('js/app.min.js')}}"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            const userIntegration = $("#indiamart_checkbox").val();
            if (userIntegration === "Unassigned") {
                $("#customRadio1").prop("checked", true);
            } else if (userIntegration === "Round-Robin") {
                $("#customRadio2").prop("checked", true);
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#follow_up_note_req_flg').on('change', function() {
                let follow_up_note_req_flg = ($(this).prop('checked')==true)? 1 : 0;

                $.ajax({
                    type: "POST",
                    url: SITEURL + "/settings/follow-up-notes-flag/update",
                    data: {follow_up_note_req_flg:follow_up_note_req_flg},        //POST variable name value
                    success: function(msg){
                        toastrSuccess('Successfully saved...', 'Success');
                        // location.reload();
                    }
                });
            });

            $('.lead_merge_flag').on('change', function() {
                let lead_merge_flag = $(this).val();

                $.ajax({
                    type: "POST",
                    url: SITEURL + "/settings/lead-merge-flag/update",
                    data: {lead_merge_flag:lead_merge_flag},        //POST variable name value
                    success: function(msg){
                        toastrSuccess('Successfully saved...', 'Success');
                        // location.reload();
                    }
                });
            });

            $('input[name="customRadio"]').change(function () {
                const selectedValue = $(this).val();
                console.log(selectedValue);
                $.ajax({
                    url: SITEURL + "/settings/indiamart-flag/update",
                    method: 'POST',
                    data: {
                        indiamart_integration: selectedValue
                    },
                    success: function(msg){
                        toastrSuccess('Successfully saved...', 'Success');
                    }
                });
            });

            //$('input[name="customRadio"]:checked').trigger('change');
        });

    </script>
@endpush
