@extends('layouts.app')
@section('title','Dashboard')
@push('styles')
    <style>

        .fc-scrollgrid-sync-table {
            width: 100% !important;
        }

        .fc-daygrid-body {
            width: 100% !important;
        }

        .card.card-fullscreen {
            display: block;
            z-index: 9999;
            position: fixed;
            width: 100% !important;
            height: 100% !important;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            overflow: auto;

        .fc-daygrid-event-dot {

            border: calc(var(--fc-daygrid-event-dot-width, 8px) / 2) solid #fff;

        }

        #calendar {
            /*width: 200px;*/
            margin: 0 auto;
            font-size: 10px;
        }

        .fc-toolbar {
            font-size: .9em;
        }

        .fc-toolbar h2 {
            font-size: 12px;
            white-space: normal !important;
        }

        /* click +2 more for popup */
        .fc-more-cell a {
            display: block;
            width: 85%;
            margin: 1px auto 0 auto;
            border-radius: 3px;
            background: grey;
            color: transparent;
            overflow: hidden;
            height: 4px;
        }

        .fc-more-popover {
            width: 100px;
        }

        .fc-view-month .fc-event, .fc-view-agendaWeek .fc-event, .fc-content {
            font-size: 0;
            overflow: hidden;
            height: 2px;
        }

        .fc-view-agendaWeek .fc-event-vert {
            font-size: 0;
            overflow: hidden;
            width: 2px !important;
        }

        .fc-agenda-axis {
            width: 20px !important;
            font-size: .7em;
        }

        .fc-button-content {
            padding: 0;
        }
        #sales_performance_date_range{
            max-width: 100% !important;
        }
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
                            <div>
                                {{-- <select class="form-select form-select-sm" aria-label=".form-select-sm example"
                                        onchange="barChart(this.value)">
                                    <option selected value="{{$bar_chart_filter['current_fiscal_year']}}">This Fiscal Year
                                    </option>
                                    <option value="{{$bar_chart_filter['previous_fiscal_year']}}">Previous Fiscal Year
                                    </option>
                                    <option value="{{$bar_chart_filter['last_twelve_month']}}">Last 12 Months</option>
                                </select>--}}
                                <div id="sales_performance_date_range" class="form-control mb-2" data-toggle="date-picker-range"
                                    data-target-display="#selectedValues" data-cancel-class="btn-light" style="max-width:100%;">
                                    <i class="mdi mdi-calendar"></i>&nbsp;
                                    <span id="selectedValues"></span> <i class="mdi mdi-menu-down"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="page-title">Dashboard</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                {{--<div class="col-xl-6 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 class="header-title mb-0">Last 10 New Clients</h4>
                            </div>



                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0 table-sm">
                                    <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Mobile</th>
                                        <th scope="col">Email</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($new_clients))
                                    @foreach ($new_clients as $kay => $record)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="rounded-circle" src="{{($record->profile_icon) ? Storage::url($record->profile_icon) : url('../assets/images/users/avatar-1.jpg')}}" alt="{{$record->name}}" width="33">
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    {{$record->name}}
                                                </div>
                                            </div>
                                        </td>
                                        <td><i class="uil uil-calender me-1"></i>{{date('d-m-Y', strtotime($record->created_at))}}</td>
                                        <td>
                                        {{$record->mobile_no}}
                                        </td>
                                        <td>
                                            {{$record->email}}
                                            <span class="text-success fw-semibold"></span>
                                        </td>
                                    </tr> <!-- end tr -->
                                    @endforeach
                                    @else
                                        <tr>
                                            <th colspan="4" class="text-dark text-center">No data found!</th>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-xl-6 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 class="header-title mb-0">Last 10 Inprogress Clients</h4>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0 table-sm">
                                    <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Mobile</th>
                                        <th scope="col">Email</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($in_progress_clients))
                                    @foreach ($in_progress_clients as $record)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <img class="rounded-circle" src="{{($record->profile_icon) ? Storage::url($record->profile_icon) : url('assets/images/users/avatar-1.jpg')}}" alt="{{$record->name}}" width="33">
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        {{$record->name}}
                                                    </div>
                                                </div>
                                            </td>
                                            <td><i class="uil uil-calender me-1"></i>{{date('d-m-Y', strtotime($record->updated_at))}}</td>
                                            <td>
                                                <span class="badge bg-success-lighten text-success">{{$record->mobile_no}}</span>
                                            </td>
                                            <td>
                                                {{$record->email}}
                                                <span class="text-success fw-semibold"></span>
                                            </td>
                                        </tr> <!-- end tr -->
                                    @endforeach
                                    @else
                                        <tr>
                                            <th colspan="4" class="text-dark text-center">No data found!</th>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->--}}

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title">Estimate</h4>
                            </div>

                            <div dir="ltr">
                                <div id="chart-dash" class="apex-charts" data-colors="#ced1ff,#727cf5"></div>
                            </div>
                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title">Lead</h4>

                            </div>

                            <div dir="ltr">
                                <div id="lead-chart" class="apex-charts" data-colors="#ced1ff,#727cf5"></div>
                            </div>
                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->

            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6">
                    <div class="row widget-list">
                        {{-- @foreach($widgets as $widget)
                            <div class="col-sm-6">
                                <a class="text-muted" href="{{url('quotes?status='.$widget['status'])}}">
                                    <div class="card widget-flat">
                                        <div class="card-body p-2">
                                            <div class="float-end">
                                                @if($widget['status']=='Accept')
                                                    <i class="mdi mdi-checkbox-marked-circle-outline widget-icon rounded-circle"></i>
                                                @endif
                                                @if($widget['status']=='Decline')
                                                    <i class="mdi mdi-close-box-multiple-outline widget-icon rounded-circle"></i>
                                                @endif
                                                @if($widget['status']=='Inprogress')
                                                    <i class="mdi mdi-progress-pencil widget-icon rounded-circle"></i>
                                                @endif
                                                @if($widget['status']=='Sent')
                                                    <i class="mdi mdi-email-check-outline widget-icon rounded-circle"></i>
                                                @endif
                                                @if($widget['status']=='Draft')
                                                    <i class="mdi mdi-lead-pencil widget-icon rounded-circle"></i>
                                                @endif
                                                @if($widget['status']=='Total')
                                                    <i class="mdi mdi-equal widget-icon rounded-circle"></i>
                                                @endif
                                                --}}{{--                                    <span class="widget-icon rounded-circle">{!! ($total)?number_format(($widget['widget_total'] *100)/ $total,2):number_format(0.00,2) !!}%</span>--}}{{--
                                            </div>
                                            <h5 class="text-muted fw-normal mt-0"
                                                title="Number of Customers">{{$widget['status']}}</h5>
                                            <h3 class="mt-2 mb-2 text-dark">{{$widget['widget_total']}}</h3>
                                            <p class="mb-0 text-muted text-start">
                                                <span class="text-primary me-2">{!! ($total)?number_format(($widget['widget_total'] *100)/ $total,2):number_format(0.00,2) !!}%</span>
                                                --}}{{--                                                                    <span class="text-nowrap">Since last month</span>--}}{{--
                                            </p>
                                        </div> <!-- end card-body-->
                                    </div> <!-- end card-->
                                </a>
                            </div> <!-- end col-->
                        @endforeach--}}
                    </div> <!-- end row -->
                </div> <!-- end col -->

                <div class="col-xl-6 col-lg-6">
                    <div class="card">
                        <div class="card-body pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">Generated Top 10 Estimate Client </h4>
                            </div>
                        </div>

                        <div class="card-body py-0" data-simplebar style="max-height: 300px;min-height: 300px;">
                            @foreach ($top_clients as $kay => $record)
                            <div class="d-flex align-items-start @if($kay > 0)mt-3 @endif">
                                <img class="me-3 rounded-circle" src="{{($record->profile_icon) ? Storage::url($record->profile_icon) : url('assets/images/users/avatar-1.jpg')}}" width="40" alt="Generic placeholder image">
                                <div class="w-100 overflow-hidden">
                                    <span class="widget-icon flex-shrink-0 float-end rounded-circle">{{$record->estimate_count}}</span>
                                    <h5 class="mt-0 mb-1">{{$record->name}}</h5>
                                    <span class="font-13">{{$record->email}}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div> <!-- end card-->

                </div> <!-- end col -->
            </div>
        </div>
    </div>
</div>




@endsection
@push('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js')}}"></script>
    <!-- Daterangepicker js -->
    <script src="{{ asset('assets/vendor/daterangepicker/moment.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/daterangepicker/daterangepicker.js')}}"></script>

    <script src="{{ asset('assets/vendor/chart.js/Chart.bundle.min.js')}}"></script>
    <!-- demo app -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
    {{--<script src="{{ asset('assets/vendor/demo/demo.chartjs.js')}}"></script>
        <script src="{{ asset('assets/vendor/demo/demo.dashboard-analytics.js')}}"></script>--}}
    <!-- end demo js-->

    <!-- App js -->
    <script src="{{asset('assets/js/app.min.js')}}"></script>
    <script src="{{asset('assets/js/custom.js')}}"></script>

{{--    <script src="{{ asset('assets/js/admin-custom.js')}}"></script>--}}
    <script>
        function getWidget() {

            $.ajax({
                // async: false,
                type: "GET",
                url: SITEURL + '/admin/get-widget',
                dataType: "json",
                beforeSend: function () {
                    $(".widget-list").html('<div class="text-center">' +
                        '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
                        '</div>');
                },
                success: function (data, textStatus, jqXHR) {
                    let htmlStr = '';
                    $.each(data.widgets, function (i, val) {

                        let status = '';
                        if (val.status == 'Approved')
                            status = '<i class="mdi mdi-checkbox-marked-circle-outline widget-icon rounded-circle"></i>';
                        if (val.status == 'Rejected')
                            status = '<i class="mdi mdi-close-box-multiple-outline widget-icon rounded-circle"></i>';
                        if (val.status == 'Pending')
                            status = '<i class="mdi mdi-progress-pencil widget-icon rounded-circle"></i>';

                        if (val.status == 'New')
                            status = '<i class="mdi mdi-lead-pencil widget-icon rounded-circle"></i>';

                        if (val.status == 'Total')
                            status = '<i class="mdi mdi-equal widget-icon rounded-circle"></i>';

                        let widgetTotal = (data.total) ? ((val.widget_total * 100) / data.total).toFixed(2) : 0.00;
                        htmlStr += '<div class="col-sm-6">\n' +
                            //'<a class="text-muted" href="{{url('/client')}}?status=' + val.status + '">\n' +
                            '<div class="card widget-flat">\n' +
                            '<div class="card-body p-2">\n' +
                            '<div class="float-end">\n' +
                            status +
                            '</div>\n' +
                            '<h5 class="text-muted fw-normal mt-0" title="Number of Customers">' + val.status + '</h5>\n' +
                            '<h3 class="mt-2 mb-2 text-dark">' + val.widget_total + '</h3>\n' +
                            '<p class="mb-0 text-muted text-start">\n' +
                            '<span class="text-primary me-2">' + widgetTotal + '%</span>\n' +
                            '</p>\n' +
                            '</div>\n' +
                            '</div>\n' +
                           // '</a>\n' +
                            '</div>';

                    });
                    $(".widget-list").html(htmlStr);

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

        $(document).ready(function () {

            var fil_sp_chart_start = moment();
            var fil_sp_chart_end = moment();

            if (localStorage.hasOwnProperty("fil_sp_chart_start")) {
                fil_sp_chart_start = moment(localStorage.getItem('fil_sp_chart_start'));
            } else {
                localStorage.setItem('fil_sp_chart_start', fil_sp_chart_start);
            }

            if (localStorage.hasOwnProperty("fil_sp_chart_end")) {
                fil_sp_chart_end = moment(localStorage.getItem('fil_sp_chart_end'));
            } else {
                localStorage.setItem('fil_sp_chart_end', fil_sp_chart_end);
            }

            $('#sales_performance_date_range').daterangepicker({
                startDate: fil_sp_chart_start,
                endDate: fil_sp_chart_end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Up to Today': [moment().subtract({{ (\Carbon\Carbon::parse(auth()->user()->created_at)->diffInDays())}}, 'days'), moment()],
                }
            }, cb);

            cb(fil_sp_chart_start, fil_sp_chart_end);

            var options = {
                colors: ["#6c757d"],
                series: [{
                    name: 'Estimate',
                    data: []
                }/*, {
                    name: 'Revenue',
                    data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
                }, {
                    name: 'Free Cash Flow',
                    data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
                }*/],
                chart: {
                    id:'bar_chart',
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools:{
                            download:false // <== line to add
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '10%',
                        // endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                },
                yaxis: {
                    title: {
                        text: 'No of estimate'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return + val + " Estimate"
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-dash"), options);
            chart.render();

            /*Lead Chart*/
            var lead_options = {
                colors: ["#727cf5"],
                series: [{
                    name: 'Estimate',
                    data: []
                }/*, {
                    name: 'Revenue',
                    data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
                }, {
                    name: 'Free Cash Flow',
                    data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
                }*/],
                chart: {
                    id:'lead_bar_chart',
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools:{
                            download:false // <== line to add
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '10%',
                        // endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                },
                yaxis: {
                    title: {
                        text: 'No of lead'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return + val + " Lead"
                        }
                    }
                }
            };

            var chart_lead = new ApexCharts(document.querySelector("#lead-chart"), lead_options);
            chart_lead.render();

            getWidget();

            function barChart(date_range) {
                $.ajax({
                    type: "GET",
                    // async: false,
                    url: SITEURL + "/admin/bar-chart",
                    data: { date: date_range},
                    dataType: "json",
                    beforeSend: function () {
                        $('#preloader').show();
                        $('#status').show();
                        $("#chart-dash").html('<div class="text-center">' +
                            '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
                            '</div>');
                    },
                    success: function (res) {
                        ApexCharts.exec('bar_chart', 'updateOptions', {
                            series: [{
                                name: 'Total',
                                data: res.sent
                            }/*, {
                                name: 'Accepted',
                                data: res.close
                            }*/],
                            xaxis: {
                                categories: res.labels,
                            },
                        }, false, true);
                        item = res;
                        $('#preloader').hide();
                        $('#status').hide();
                    }
                });
            }

            function leadBarChart(date_range) {
                $.ajax({
                    type: "GET",
                    // async: false,
                    url: SITEURL + "/admin/lead-bar-chart",
                    data: { date: date_range},
                    dataType: "json",
                    beforeSend: function () {
                        $('#preloader').show();
                        $('#status').show();
                        $("#lead-chart").html('<div class="text-center">' +
                            '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
                            '</div>');
                    },
                    success: function (res) {
                        ApexCharts.exec('lead_bar_chart', 'updateOptions', {
                            series: [{
                                name: 'Total',
                                data: res.sent
                            }/*, {
                                name: 'Accepted',
                                data: res.close
                            }*/],
                            xaxis: {
                                categories: res.labels,
                            },
                        }, false, true);
                        item = res;
                        $('#preloader').hide();
                        $('#status').hide();
                    }
                });
            }

            function cb(fil_sp_chart_start, fil_sp_chart_end, flg = 0) {
                $('#sales_performance_date_range span').html(fil_sp_chart_start.format('MMMM D, YYYY') + ' - ' + fil_sp_chart_end.format('MMMM D, YYYY'));
                let date_range = fil_sp_chart_start.format('YYYY-MM-DD') + '_' + fil_sp_chart_end.format('YYYY-MM-DD');
                localStorage.setItem('fil_sp_chart_start', moment(fil_sp_chart_start, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                localStorage.setItem('fil_sp_chart_end', moment(fil_sp_chart_end, 'YYYY-MM-DD').format("YYYY-MM-DD"));
                barChart(date_range);
                leadBarChart(date_range);
            }
        });

    </script>
@endpush
