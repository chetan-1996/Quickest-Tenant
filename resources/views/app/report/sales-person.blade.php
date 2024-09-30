@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title', 'Report')
@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}" type="text/css" />

    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
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


                            {{-- <div class="dropdown btn-group mb-2">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                id="select_count">0</span>Bulk Action
                                </button>
                                <div class="dropdown-menu dropdown-menu-animated">
                                    <a href="javascript:void(0);" class="dropdown-item active_status_all"><i
                                            class="mdi mdi-update"></i> Active All</a>
                                    <a href="javascript:void(0);" class="dropdown-item deactive_status_all"><i
                                            class="mdi mdi-update"></i> Deactive All</a>
                                    <a href="javascript:void(0);" class="dropdown-item delete_all"><i
                                            class="mdi mdi-delete-circle"></i> Delete All</a>
                                </div>
                            </div> --}}
                        </div>

                        <div class="page-title-left pt-2">
                            <h4 class="page-title">Reports</h4>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        {{--<div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="header-title">Project Status</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Weekly Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Monthly Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                </div>
                            </div>
                        </div>--}}

                        <div class="card-body pt-0">


                            <div class="row mb-2">

                                <div class="col-md-4    ">
                                    <div class="mb-3">
                                        {{--<label class="form-label">Date</label>--}}
                                        <input type="hidden" class="form-control fil_date" id="fil_date" name="fil_date">
                                    </div>
                                    <div id="fil_date_range" class="form-control" data-toggle="date-picker-range"
                                        data-target-display="#selectedValue" data-cancel-class="btn-light">
                                        <i class="mdi mdi-calendar"></i>
                                        <span id="selectedValue"></span> <i class="mdi mdi-menu-down"></i>
                                    </div>
                                </div>

                                <div class="col-lg-4 {{(in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm))?'d-none' :''}}">
                                    <div class="mt-3">
                                        {{-- <label for="user_id" class="form-label">Select Team Member</label>--}}
                                        <select class="form-select" id="fil_assignee" name="fil_assignee">
                                            <option value="0">All</option>
                                            @foreach ($user_list as $user_list)
                                                <option value="{{ $user_list->id }}"
                                                        @if (auth()->user()->id == $user_list->id) selected @endif>{{ $user_list->id == auth()->user()->id ? 'Myself' : $user_list->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-2 d-none">
                                    <label class="form-label">Report Type</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="fil_report_type" value="0" class="form-check-input"
                                                id="report_type_summary" checked>
                                            <label class="form-check-label" for="report_type_summary">Summary</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="fil_report_type" value="1" class="form-check-input"
                                                id="report_type_in_detail">
                                            <label class="form-check-label" for="report_type_in_detail">In Detail</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 mt-3">
                                    <button class="btn btn-secondary" id="generate_button" type="submit">Generate</button>
                                    <button class="btn btn-secondary" id="export_pdf_button" type="button">Export Pdf</button>
                                </div>
                            </div>
                            <!-- end row-->

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->

                <div class="col-lg-4">
                    <div class="card">
                        {{--<div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="header-title">Do you Email notification ?</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                            </div>
                        </div>--}}

                        <div class="card-body pt-0">
                            <h6 class="font-15 mt-3">Do you Email notification ?</h6>
                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input cron_flg" id="weekly_cron_flg" name="weekly_cron_flg" {{(auth()->user()->weekly_cron_flg==1)?'checked':''}}>
                                    <label class="form-check-label" for="weekly_cron_flg">Weekly</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input cron_flg" id="monthly_cron_flg" name="monthly_cron_flg" {{(auth()->user()->monthly_cron_flg==1)?'checked':''}}>
                                    <label class="form-check-label" for="monthly_cron_flg">Monthly</label>
                                </div>
                            </div>

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Summary</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12 col-lg-12">

                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Accept</th>
                                                    <th>Decline</th>
                                                    <th>Draft</th>
                                                    <th>Inprogress</th>
                                                    {{-- <th>Sent</th>--}}
                                                    <th>Total</th>
                                                    <th>Conv. Ratio(%)</th>
                                                    <th>Total Task</th>
                                                    <th>Completed Task</th>
                                                    {{-- <th>Performance(%)</th>--}}
                                                    <th>Completion Ratio(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tblSummaryData">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


                        </div> <!-- end col -->
                    </div>

                {{-- <div class="col-12 sales-performance-report-chart-div">
                        <div class="card">
                            <div class="card-body">
                                <div id="sales-performance-report-chart" class="apex-charts" data-colors="#ced1ff,#727cf5"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-centered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Est No</th>
                                                <th>Customer</th>
                                                <th>Total</th>
                                                <th>Created By</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblEstimateData">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                </div><!-- end col-->
            </div>
            <!-- end row-->
        </div>
    </div>
</div>



@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script> -->

    <!-- third party js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('vendor/chart.js/Chart.bundle.min.js')}}"></script>
    <script src="{{ asset('vendor/demo/demo.chartjs.js')}}"></script>

    <!-- Daterangepicker js -->
    <script src="{{ asset('vendor/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/daterangepicker/daterangepicker.js') }}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js')}}"></script>
    <!-- end demo js-->
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var start = moment();
            var end = moment();

            function cb(start, end) {
                $('#fil_date_range span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                let date_range = start.format('YYYY-MM-DD') + '_' + end.format('YYYY-MM-DD');
                $(".fil_date").val(date_range);
                localStorage.setItem('start', start.format('YYYY-MM-DD'));
                localStorage.setItem('end', end.format('YYYY-MM-DD'));
            }

            $('#fil_date_range').daterangepicker({
                startDate: start,
                endDate: end,
                // "drops": "up",
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse(auth()->user()->created_at)->diffInDays())}}, 'days'), moment()]
                }
            }, cb);

            cb(start, end);

            $("#generate_button").click(function(e) {
                $(".sales-performance-report-chart-div").hide();
                if ($("#fil_assignee").val() > 0) {
                    $(".sales-performance-report-chart-div").show();
                }
                e.preventDefault();
                $.ajax({
                    type: 'GET',
                    url: '{{ route('tenant.report.index', ['tenant' => $segment]) }}',
                    data: {
                        fil_date: $(".fil_date").val(),
                        fil_assignee: $("#fil_assignee").val(),
                        fil_report_type: $('input[name="fil_report_type"]:checked').val()
                    },

                    dataType: "json",
                    beforeSend: function() {
                        $("#generate_button").prop('disabled', true);
                        $("#generate_button").html(
                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    success: function(reponse) {
                        let summaryHtml = '';
                        let total_accept = 0;
                        let total_decline = 0;
                        let total_draft = 0;
                        let total_inprogress = 0;
                        let total_sent = 0;
                        let total_total = 0;
                        let total_total_task = 0;
                        let total_completed_task = 0;
                        let total_performance = 0;
                        let total_completion_ratio = 0;
                        let count_performance = 0;
                        let count_completion_ratio = 0;
                        let total_conver_ratio = 0;
                        $.each(reponse.data.summaryData, function(key, summary_data) {

                            if (summary_data.performance > 0)
                                count_performance = Number(count_performance) + Number(
                                    1);

                            if (summary_data.completion_ratio > 0)
                                count_completion_ratio = Number(
                                count_completion_ratio) + Number(1);
                            total_accept = Number(total_accept) + Number(summary_data
                                .widget[0].widget_total);
                            total_decline = Number(total_decline) + Number(summary_data
                                .widget[1].widget_total);
                            total_draft = Number(total_draft) + Number(summary_data
                                .widget[2].widget_total);
                            total_inprogress = Number(total_inprogress) + Number(
                                summary_data.widget[3].widget_total);
                            total_sent = Number(total_sent) + Number(summary_data
                                .widget[4].widget_total);
                            total_total = Number(total_total) + Number(summary_data
                                .widget[5].widget_total);
                            total_total_task = Number(total_total_task) + Number(
                                summary_data.total_task);
                            total_completed_task = Number(total_completed_task) +
                                Number(summary_data.completed_task);
                            total_performance = Number(total_performance) + Number(
                                summary_data.performance);
                            total_completion_ratio = Number(total_completion_ratio) +
                                Number(summary_data.completion_ratio);

                            let conver_ratio = (summary_data.widget[0].widget_total > 0) ? ((summary_data.widget[0].widget_total /
                                summary_data.widget[5].widget_total)*100).toFixed(2) : 0;

                            total_conver_ratio = (total_accept > 0) ? ((total_accept / total_total)*100).toFixed(2) : 0;
                            summaryHtml += '<tr><td>' + summary_data.name +
                                '</td><td>' + summary_data.widget[0].widget_total +
                                '</td><td>' + summary_data.widget[1].widget_total +
                                '</td><td>' + summary_data.widget[2].widget_total +
                                '</td><td>' + summary_data.widget[3].widget_total +
                                // '</td><td>' + summary_data.widget[4].widget_total +
                                '</td><td>' + summary_data.widget[5].widget_total +
                                '</td><td>' + conver_ratio +
                                '</td><td>' + summary_data.total_task + '</td><td>' +
                                summary_data.completed_task + '</td><td>' + summary_data
                                .completion_ratio + '</td></tr>'; //<td>' + summary_data.performance + '</td>
                        });
                        let final_performance = (count_performance > 0) ? (total_performance /
                            count_performance).toFixed(2) : total_performance;
                        let final_completion_ratio = (total_completed_task > 0) ? ((
                            total_completed_task / total_total_task)*100).toFixed(2) :
                            0;
                        summaryHtml += '<tr class="table-light"><th>Total :</th><th>' +
                            total_accept + '</th><th>' + total_decline + '</th><th>' +
                            total_draft + '</th><th>' + total_inprogress + '</th><th>' + total_total + '</th><th>' + total_conver_ratio + '</th><th>' +
                            total_total_task + '</th><th>' + total_completed_task +
                            '</th><th>' +
                            final_completion_ratio + '</th></tr>';//<th>' + final_performance + '</th>  <th>' + total_sent + '</th>
                        $("#tblSummaryData").html(summaryHtml);


                       /* let estimateHtml = '';
                        $.each(reponse.data.estimateList, function(key, estimate_data) {
                            estimateHtml += '<tr><th>' + estimate_data.estimate_dates +
                                '</th><th>' + estimate_data.estimate_no + '</th><th>' +
                                estimate_data.customer_name + '</th><th>' +
                                estimate_data.net_amount + '</th><th>' + estimate_data
                                .created_by + '</th><th>' + estimate_data.status +
                                '</th></tr>';

                            $.each(reponse.data.estimateList[key].followupDetails,
                                function(key, followup_details_data) {
                                    let nextFollowUp = followup_details_data
                                        .start_date;
                                    if (followup_details_data.next_follow_up == 1)
                                        nextFollowUp = "-";
                                    estimateHtml += '<tr>';
                                    estimateHtml += '<td colspan="6">';

                                    estimateHtml +=
                                        '<table class="table table-borderless table-sm mb-0">';
                                    estimateHtml +=
                                        '<tbody style="border-top: 0px solid;">';

                                    estimateHtml += '<tr>';
                                    estimateHtml += '<td width="58%">';
                                    estimateHtml +=
                                        '<span class="text-muted font-13">Notes</span>';
                                    estimateHtml +=
                                        '<h5 class="font-14 fw-normal">' +
                                        followup_details_data.notes + '</h5>';
                                    estimateHtml += '</td>';
                                    estimateHtml += '<td width="16%">';
                                    estimateHtml +=
                                        '<span class="text-muted font-13">Created Date</span>';
                                    estimateHtml +=
                                        '<h5 class="font-14 fw-normal">' +
                                        followup_details_data.created_at + '</h5>';
                                    estimateHtml += '</td>';
                                    estimateHtml += '<td width="10%">';
                                    estimateHtml +=
                                        '<span class="text-muted font-13">Created by</span>';
                                    estimateHtml +=
                                        '<h5 class="font-14 fw-normal">' +
                                        followup_details_data.user_name + '</h5>';
                                    estimateHtml += '</td>';
                                    estimateHtml += '<td width="16%">';
                                    estimateHtml +=
                                        '<span class="text-muted font-13">Next follow up</span>';
                                    estimateHtml +=
                                        '<h5 class="font-14 fw-normal">' +
                                        nextFollowUp + '</h5>';
                                    estimateHtml += '</td>';
                                    estimateHtml += '</tr>';
                                    estimateHtml += '</tbody>';
                                    estimateHtml += '</table>';
                                    estimateHtml += '</td>';
                                    estimateHtml += '</tr>';
                                });

                        });
                        $("#tblEstimateData").html(estimateHtml);

                        // example of series in another format

                        var options = {
                            series: [{
                                name: "Performance",
                                data: reponse.data.chart.daily_performance_chart
                            }],
                            chart: {
                                height: 350,
                                type: 'line',
                                zoom: {
                                    enabled: false
                                },
                                /!*type: 'area',
                                stacked: false,
                                height: 350,
                                zoom: {
                                    type: 'x',
                                    enabled: true,
                                    autoScaleYaxis: true
                                },*!/
                                toolbar: {
                                    autoSelected: 'zoom'
                                }
                            },
                            dataLabels: {
                                enabled: true
                            },
                            stroke: {
                                curve: 'straight'
                            },
                            title: {
                                text: 'Performance Chart',
                                align: 'left'
                            },
                            grid: {
                                row: {
                                    colors: ['#f3f3f3',
                                    'transparent'], // takes an array which will be repeated on columns
                                    opacity: 0.5
                                },
                            },
                            xaxis: {
                                categories: reponse.data.chart.performance_date_chart,
                            }
                        };


                        var chart_datas = new ApexCharts(document.querySelector(
                            "#sales-performance-report-chart"), options);
                        chart_datas.render();

                        chart_datas.updateSeries([{
                            name: 'Performance',
                            data: reponse.data.chart.daily_performance_chart
                        }])
                        chart_datas.updateOptions({
                            xaxis: {
                                categories: reponse.data.chart.performance_date_chart,
                            }
                        })*/

                        /*ApexCharts.exec('mychart', 'updateOptions', {
                            series: [{
                                name: "Performance",
                                data: reponse.data.chart.daily_performance_chart
                            }],
                            xaxis: {
                                categories: reponse.data.chart.performance_date_chart,
                            }


                        }, false, true);


                        chart.updateSeries([{
                            name: "Performance",
                            data: reponse.data.chart.daily_performance_chart
                        }])*/


                        // toastrSuccess('Successfully saved...', 'Success');
                        $("#generate_button").prop('disabled', false);
                        $("#generate_button").html('Generate');
                    },
                    error: function(xhr, status, error) {
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
                        $("#generate_button").prop('disabled', false);
                        $("#generate_button").html('Generate');
                    },
                    complete: function(data) {
                        $("#generate_button").html('Generate');
                        $("#generate_button").prop(
                            '<i class="uil-arrow-circle-right"></i> disabled', false);
                    }
                });
            });

            $("#export_pdf_button").click(function(e) {
                /*$(".sales-performance-report-chart-div").hide();
                if ($("#fil_assignee").val() > 0) {
                    $(".sales-performance-report-chart-div").show();
                }*/
                e.preventDefault();
                $.ajax({
                    type: 'GET',
                    url: '{{ route('tenant.report.sales-person-pdf-report', ['tenant' => $segment]) }}',
                    data: {
                        fil_date: $(".fil_date").val(),
                        fil_assignee: $("#fil_assignee").val(),
                        fil_report_type: $('input[name="fil_report_type"]:checked').val()
                    },
                    // dataType: "json",
                    xhrFields: {
                        responseType: 'blob'
                    },
                    beforeSend: function() {
                        $("#export_pdf_button").prop('disabled', true);
                        $("#export_pdf_button").html(
                            '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                    },
                    success: function(response) {
                        $("#export_pdf_button").prop('disabled', false);
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "REPORT-" + $(".fil_date").val() +
                            "-{{ auth()->user()->name }}.pdf";
                        link.click();

                    },
                    error: function(blob) {
                        console.log(blob);
                    },
                    complete: function(data) {
                        $("#export_pdf_button").html('Export Pdf');
                        $("#export_pdf_button").prop('disabled', false);
                    }
                });
            });
        });

        $('#weekly_cron_flg').click(function() {
            $.ajax({
                type: 'post',
                url: '{{ route('tenant.report.weekly-mail-notification-flag', ['tenant' => $segment]) }}',
                data: {
                    weekly_cron_flg: this.checked
                },
                dataType: "json",

                beforeSend: function() {

                },
                success: function(response) {
                    toastrSuccess('Successfully saved');
                },
                error: function(xhr, status, error) {
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
                },
                complete: function(data) {

                }
            });
        });

        $('#monthly_cron_flg').click(function() {
            $.ajax({
                type: 'post',
                url: '{{ route('tenant.report.monthly-mail-notification-flag', ['tenant' => $segment]) }}',
                data: {
                    monthly_cron_flg: this.checked
                },
                dataType: "json",

                beforeSend: function() {

                },
                success: function(response) {
                    toastrSuccess('Successfully saved');
                },
                error: function(xhr, status, error) {
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
                },
                complete: function(data) {

                }
            });
        });
    </script>
@endpush
