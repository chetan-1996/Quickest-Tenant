@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
$t_company_id = (auth()->user()->company_id==null)? auth()->user()->id:auth()->user()->company_id;
$expData = App\Helpers\PermissionCheck::plan_details_check();

@endphp
@extends('app.layouts.app')
@section('title','Lead')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.css"
          type="text/css">
    <link href="{{ asset('vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <style>

        #customer-modal .select2 .select2-container .select2-container--default .select2-container--above .select2-container--focus {
            height: 59px !important;
        }

        #customer-modal .select2-container .select2-selection--single .select2-selection__rendered {
            height: 56px !important;
            line-height: 76px !important;
            padding-left: 12px;
            /*color: var(--ct-input-color);*/
            background-color: #eef2f7;
        }

        #customer-modal .select2-container .select2-selection--single .select2-selection__arrow {
            height: 58px !important;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #customer-modal .select2-container .select2-selection--single {
            height: 58px !important;
            border: 1px solid var(--ct-input-border-color);
            height: calc(1.5em + 0.9rem + 2px);
            background-color: var(--ct-input-bg);
            outline: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #customer-modal .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: 4px;
        }

        #customer-modal .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #727cf5 !important;
            color: #fff !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #555 !important;
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
                    <div class="page-title-left pt-2">
                        <h4 class="page-title fs-4 d-nones">Export Leads</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        {{--    <div class="row">--}}
        {{--        <div class="col-12">--}}
                        <div class="card filter-container">
                            <div class="card-body">
                                <div class="row">
                                    <form action="{{ url($segment.'/leads-export') }}" method="GET" id="export-lead-excel">
                                        <div class="col-xl-12">
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_lead_stage" class="text-dark fw-bold me-2">Stage</label>
                                                        <select class="form-select" id="fil_lead_stage_id" name="fil_lead_stage_id">
                                                            <option value="">All</option>
                                                            @foreach($leadStages as $leadStage)
                                                                <option value="{{$leadStage->id}}">{{$leadStage->name}}</option>
                                                            @endforeach
                                                            <option value="blank">Blank Stage</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_lead_label" class="text-dark fw-bold me-2">Label</label>
                                                        <select class="form-select" id="fil_status" name="fil_status[]" multiple>
                                                            {{--                                            <option value="">All</option>--}}
                                                            @foreach($leadGroups as $leadGroup)
                                                                <option
                                                                    value="{{$leadGroup->id}}">{{$leadGroup->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_lead_category" class="text-dark fw-bold me-2">Category</label>
                                                        <select class="form-select text-dark" id="fil_customer_category_id"
                                                                name="fil_customer_category_id">
                                                            <option value="">Choose</option>
                                                            @foreach($customerCategories as $customerCategory)
                                                                <option
                                                                    value="{{$customerCategory->id}}">{{$customerCategory->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_lead_orgin" class="text-dark fw-bold me-2">Source</label>
                                                        <select class="form-select text-dark" id="fil_customer_lead_id"
                                                                name="fil_customer_lead_id">
                                                            <option value="">Choose</option>
                                                            @foreach($customerLeads as $customerLead)
                                                                <option
                                                                    value="{{$customerLead->id}}">{{$customerLead->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_created_user_id" class="text-dark fw-bold me-2">Created By</label>
                                                        <select class="form-select text-dark" id="fil_created_user_id"
                                                                name="fil_created_user_id">
                                                            <option value="">Choose</option>
                                                            @foreach($teamUsers as $teamUser)
                                                                <option
                                                                    value="{{$teamUser->id}}">{{$teamUser->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_estimate_status_id" class="text-dark fw-bold me-2">Estimate
                                                            Status</label>
                                                        <select class="form-select text-dark" id="fil_estimate_status_id"
                                                                name="fil_estimate_status_id">
                                                            <option value="">Choose</option>
                                                            <option value="Draft">Draft</option>
                                                            <option value="Inprogress">Inprogress</option>
                                                            <option value="Accept">Accept</option>
                                                            <option value="Decline">Decline</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_created_user_id" class="text-dark fw-bold me-2">Lead Created Date</label>
                                                        <div
                                                            class="d-flex justify-content-between xxx align-items-center text-primary">
                                                            <div id="lead_date_range" class="form-control text-primary"
                                                                data-toggle="date-picker-range"
                                                                data-target-display="#selectedValue" data-cancel-class="btn-light"
                                                                style="max-width:100%;">
                                                                <i class="mdi mdi-calendar"></i>&nbsp;
                                                                <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_city_name" class="text-dark fw-bold me-2">City Name</label>
                                                        <input class="form-control" type="text" id="fil_city_name" name="fil_city_name"
                                                            placeholder="City name">
                                                        <input class="form-control" type="hidden" id="fil_lead_date_start" name="fil_lead_date_start"
                                                            placeholder="Start date">
                                                        <input class="form-control" type="hidden" id="fil_lead_date_end" name="fil_lead_date_end"
                                                            placeholder="End date">
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center" id="sel_st">
                                                        <label for="fil_state_id" class="text-dark fw-bold me-2">State</label>
                                                        <select class="form-select" id="fil_state_id" name="fil_state_id">
                                                            <option value="">All</option>
                                                            @foreach($fil_states as $val_state)
                                                                <option value="{{$val_state->id}}">{{$val_state->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center" id="sel_co">
                                                        <label for="fil_country_id" class="text-dark fw-bold me-2">Country</label>
                                                        <select class="form-select" id="fil_country_id" name="fil_country_id">
                                                            <option value="">All</option>
                                                            @foreach($countries as $val_countries)
                                                                <option value="{{$val_countries->id}}">{{$val_countries->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <div class="align-items-center">
                                                        <label for="fil_team_member1" class="text-dark fw-bold me-2">Team Member</label>
                                                        <select id="fil_team_member1" name="fil_team_member1" class="form-select">
                                                            <option value="0">All</option>
                                                            @foreach($teamUsers as $teamUser)
                                                                <option value="{{$teamUser->id}}">{{ $teamUser->id == auth()->user()->id ? 'Myself' : $teamUser->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{--<div class="col-3">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="align-items-center">
                                                                <label for="fil_lead_dtage" class="text-dark fw-bold me-2 d-none">Advance
                                                                    Filter</label><br>
                                                                <button class="btn btn-primary" type="submit"><i
                                                                        class="mdi mdi-cloud-download-outline"></i> Export Leads
                                                                </button>
                                                                <button type="button" class="btn btn-light fullscreen ms-2"
                                                                        id="filter_reset_button">
                                                                    Clear
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>--}}
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit" form="export-lead-excel" id="export-button">
                                    <span id="loading-spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span> Export Leads
                                    {{-- <i class="mdi mdi-cloud-download-outline"></i>--}}
                                </button>
                                <button type="button" class="btn btn-light fullscreen ms-2" id="filter_reset_button"> Clear</button>
                            </div>
                        </div>
            {{--    </div>--}}
        {{--    </div>--}}
        </div>
    </div>
</div>

@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script> -->

    <!-- third party js -->
    @include('layouts.partials.datatable-script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('js/sweetalert2.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/jquery-clockpicker.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>--}}

    <script>

        $(function () {
            $('#fil_state_id').select2({
                dropdownParent: $('#sel_st')
            });
            $('#fil_country_id').select2({
                dropdownParent: $('#sel_co')
            });

            $("#fil_status").select2().on("select2:select select2:unselect", function (e) {

                //this returns all the selected item
                var items = $(this).val();
                //Gets the last selected item
                var lastSelectedItem = e.params.data.id;
            })

            $('#country_code').select2({
                dropdownParent: $('#sel_cc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
                var selectedOption = $(this).find(':selected');
                var dataId = selectedOption.data('id');

                $("#country_id").val($('option:selected', this).data('id'));

                getStatesList($('option:selected', this).data('id'));


                $("#whatsapp_country_code option[data-id='" + dataId + "']").prop("selected", true);
                $('#whatsapp_country_code').select2({dropdownParent: $('#sel_wcc')}).trigger('change');
                console.log($('#country_id').find(':selected').data('id'));

                /* var option = $('#currency_name').find('option[data-id="' + dataId + '"]');
                 $('#currency_name').val(option.val()).trigger('change');*/

            });

            /* $('#currency_name').select2({
                 dropdownParent: $('#sel_cn')
             }).on('select2:select', function (e) {
                 var data = e.params.data;
             });*/

            $('#whatsapp_country_code').select2({
                dropdownParent: $('sel_wcc')
            }).on('select2:select', function (e) {
                var data = e.params.data;
            });
        })
        $(document).ready(function () {
            $('#export-button').on('click', function() {
                // Disable the button to prevent multiple clicks
                $(this).prop('disabled', true);

                // Show the loading spinner
                $('#loading-spinner').show();

                // Change the button text (optional)
                /*$(this).contents().filter(function() {
                    return this.nodeType == 3;
                }).each(function(){
                    this.nodeValue = ' Exporting...';
                });*/

                // Submit the form
                $('#export-lead-excel').submit();
                $(this).prop('disabled', false);
                setTimeout(function(){
                    $('#loading-spinner').hide();
                },1000);
                // Show the loading spinner
                // $('#loading-spinner').hide();
                // $("#loading-spinner").hide();
            });
            var fil_lead_date_start = moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days');
            var fil_lead_date_end = moment();
            function cbt(fil_lead_date_start, fil_lead_date_end) {
                $('#lead_date_range span').html(fil_lead_date_start.format('MMMM D, YYYY') + ' - ' + fil_lead_date_end.format('MMMM D, YYYY'));
                let date_range = fil_lead_date_start.format('YYYY-MM-DD') + '_' + fil_lead_date_end.format('YYYY-MM-DD');
                $('#fil_lead_date_start').val(moment(fil_lead_date_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                $('#fil_lead_date_end').val(moment(fil_lead_date_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                // localStorage.setItem('fil_lead_date_start', moment(fil_lead_date_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                // localStorage.setItem('fil_lead_date_end', moment(fil_lead_date_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
            }

            $('#lead_date_range').daterangepicker({
                /*startDate: fil_lead_date_start,
                endDate: fil_lead_date_end,*/
// "drops": "up",
//                 parentEl: "#theme-settings-offcanvas .xxx",
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days'), moment()],
                }
            }, cbt);

            cbt(fil_lead_date_start, fil_lead_date_end);

            $(document).on('click', '#filter_reset_button', function () {
                $('#fil_lead_stage_id').val('');
                $('#fil_status').val('');
                $('#fil_customer_category_id').val('');
                $('#fil_customer_lead_id').val('');
                $('#fil_created_user_id').val('');
                $('#fil_team_member1').val(0);
                $('#fil_estimate_status_id').val('');
                $('#lead_date_range span').html(moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
                /*localStorage.setItem('fil_lead_date_start', moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('YYYY-MM-DD'));
                localStorage.setItem('fil_lead_date_end', moment().format('YYYY-MM-DD'));
                localStorage.setItem('fil_lead_label_id', '');*/
                $('#fil_lead_date_start').val(moment(fil_lead_date_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                $('#fil_lead_date_end').val(moment().subtract({{ (\Carbon\Carbon::parse($expData->created_at)->diffInDays())+1}}, 'days').format('YYYY-MM-DD'));
                $("#fil_status").val('').trigger('change');
                $('#fil_country_id').val('');
                $("#fil_country_id option[value='']").prop("selected", true);
                $('#fil_country_id').select2({dropdownParent: $('#sel_co')}).trigger('change');
                $('#fil_state_id').val('');
                $("#fil_state_id option[value='']").prop("selected", true);
                $('#fil_state_id').select2({dropdownParent: $('#sel_st')}).trigger('change');
                $('#fil_city_name').val('');
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    <style>
        /*.th-a,.th-b,.th-c,.th-d,.th-e,.th-f,.th-g,.th-h,.th-i,.th-j,.th-k,.th-l,.th-m,.th-n,.th-o,.th-p,.th-q,.th-r,.th-s,.th-t{
            width:250px !important;
        }*/
        .select2-search__field {
            height: 26px !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 37px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 37px !important;
    </style>
@endpush
