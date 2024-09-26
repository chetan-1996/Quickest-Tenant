<!DOCTYPE html>
<html>

<head>
    <title>Sales Report</title>
</head>

<body style="background-color: #f2f2f7;">
    {{-- <h1>{{ $mail_details['subject'] }}</h1> --}}
    <table class="body-wrap"
        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: transparent; margin: 0;">
{{--        @dd($mail_details['data']);--}}
        <tbody>
            <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                <td style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;"
                    valign="top"></td>
                <td class="container" width="1000"
                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 1000px !important; clear: both !important; margin: 0 auto;"
                    valign="top">
                    <div class="content"
                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; max-width: 1000px; display: block; margin: 0 auto; padding: 20px;">
                        <table class="main" width="100%" cellpadding="0" cellspacing="0"
                            style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; border: none;">
                            <tbody>
                                <tr style="font-family: 'Roboto', sans-serif; font-size: 14px; margin: 0;">
                                    <td class="content-wrap"
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; color: #495057; font-size: 14px; vertical-align: top; margin: 0;padding: 30px; box-shadow: 0 3px 15px rgba(30,32,37,.06); ;border-radius: 7px; background-color: #fff;"
                                        valign="top">
                                        <meta itemprop="name" content="Confirm Email"
                                            style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <table width="100%" cellpadding="0" cellspacing="0"
                                            style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <tbody>
                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;"
                                                        valign="top">
                                                        <div style="text-align: center;margin-bottom: 15px;">
                                                            <img src="{{ ($mail_details['company_logo'])?$mail_details['company_logo']:asset('assets/images/logo.png') }}"
                                                                alt="" height="70">
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 20px; line-height: 1.5; font-weight: 500; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                                        valign="top">
                                                        Dear {{ $mail_details['name'] }},
                                                    </td>
                                                </tr>
{{--                                                <td class="content-block" style="text-align: right;font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 15px; vertical-align: top; margin: 0;" valign="top">--}}
{{--                                                    <span>{{Carbon\Carbon::createFromFormat('Y-m-d',$mail_details['fromdate'])->format('d/m/Y');}} - {{Carbon\Carbon::createFromFormat('Y-m-d',$mail_details['todate'])->format('d/m/Y');}}</span>--}}
{{--                                                </td>--}}
                                                <tr>
                                                    <td style="font-family:'Roboto',sans-serif;color:#878a99;box-sizing:border-box;line-height:1.5;font-size:15px;vertical-align:top;margin:0;padding:0 0 10px;color:#000 !important;" valign="top">
                                                        <p>Here is your team report for <b>{{Carbon\Carbon::createFromFormat('Y-m-d',$mail_details['fromdate'])->format('d/m/Y');}} - {{Carbon\Carbon::createFromFormat('Y-m-d',$mail_details['todate'])->format('d/m/Y');}}</b>. Please make sure your team utilise full potential of the QuickEst App.</p>

                                                        <p>
                                                            Click here to log in to your web portal. https://app.quickestimate.co/report/sales-person
                                                        </p>

                                                    </td>
                                                </tr>
                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; color: #000; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                                        valign="top">
                                                        <table style="width:100%;" cellspacing="0" cellpadding="0">
                                                            <thead style="text-align: left;background-color: #000;color:#fff">
                                                            <tr><th style="padding: 8px;border-bottom: 1px solid #e9ebec;text-align: start;">Team</th>
                                                                <th style="padding: 8px;border-bottom: 1px solid #e9ebec;text-align: end;">No. of Est</th>
                                                                <th style="padding: 8px;border-bottom: 1px solid #e9ebec;text-align: end;">No. of Accepted</th>
                                                                <th style="padding: 8px;border-bottom: 1px solid #e9ebec;text-align: end;">Deal close(Rs)</th>
                                                                <th style="padding: 8px;border-bottom: 1px solid #e9ebec;text-align: end;">Total Task</th>
                                                                <th style="padding: 8px;border-bottom: 1px solid #e9ebec;text-align: end;">Complete Task (%)</th>
                                                            </tr></thead>
                                                            <tbody>
                                                            @php
                                                                $total_total = 0;
                                                                $total_accept = 0;
                                                                $total_close_amount = 0;
                                                                $total_total_task = 0;
                                                                $total_completed_task = 0;
                                                            @endphp
                                                            @foreach ($mail_details['data'] as $summaryKey => $summaryValue)

                                                                @php
                                                                    $total_total += $summaryValue['widget'][5]['widget_total'];
                                                                    $total_accept += $summaryValue['widget'][0]['widget_total'];
                                                                    $total_close_amount += $summaryValue['widget'][0]['widget_net_amount'];
                                                                    $total_total_task += $summaryValue['total_task'];
                                                                    $total_completed_task += $summaryValue['completed_task'];
                                                                @endphp
                                                                <tr>
                                                                    <td style="padding: 8px; font-size: 14px;color:#000;">
                                                                        <p style="margin-bottom: 2px; font-size: 13px; color: #000;">{{$summaryValue['name']}}</p>
                                                                    </td>
                                                                    <td style="padding: 8px; font-size: 14px;text-align: end;color:#000;">{{$summaryValue['widget'][5]['widget_total']}}</td>
                                                                    <td style="padding: 8px; font-size: 14px;text-align: end;color:#000;">{{$summaryValue['widget'][0]['widget_total']}}</td>
                                                                    <td style="padding: 8px; font-size: 14px;text-align: end;color:#000;">{{$summaryValue['widget'][0]['widget_net_amount']}}</td>
                                                                    <td style="padding: 8px; font-size: 14px;text-align: end;color:#000;">{{$summaryValue['total_task']}}</td>
                                                                    <td style="padding: 8px; font-size: 14px;text-align: end;color:#000;">{{$summaryValue['completed_task']}} ({{($summaryValue['total_task'] > 0)?number_format(($summaryValue['completed_task']/$summaryValue['total_task'])*100,2):0;}}%)</td>
                                                                </tr>
                                                            @endforeach
                                                            <tr>
                                                                <th style="padding: 8px; font-size: 15px; text-align: start;border-top: 1px solid #e9ebec;color:#000;">
                                                                    Total
                                                                </th>
                                                                <th style="padding: 8px; font-size: 15px;border-top: 1px solid #e9ebec;text-align: end;color:#000;">{{$total_total}}</th>
                                                                <th style="padding: 8px; font-size: 15px;border-top: 1px solid #e9ebec;text-align: end;color:#000;">{{$total_accept}}</th>
                                                                <th style="padding: 8px; font-size: 15px;border-top: 1px solid #e9ebec;text-align: end;color:#000;">{{$total_close_amount}}</th>
                                                                <th style="padding: 8px; font-size: 15px;border-top: 1px solid #e9ebec;text-align: end;color:#000;">{{$total_total_task}}</th>
                                                                <th style="padding: 8px; font-size: 15px;border-top: 1px solid #e9ebec;text-align: end;color:#000;">{{$total_completed_task}} ({{($total_total_task > 0)?number_format(($total_completed_task/$total_total_task)*100,2):0;}}%)</th>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>

                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; border-top: 1px solid #e9ebec;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0; padding-top: 15px"
                                                        valign="top">
                                                        <div style="display: flex; align-items: center;">
                                                            {{--  <img src="assets/images/users/avatar-3.jpg" alt="" height="35" width="35"
                                                 style="border-radius: 50px;"> --}}
                                                            <div style="margin-left: 8px;">
                                                                <span style="font-weight: 600;">Thank you</span>

                                                                <p
                                                                    style="font-size: 13px; margin-bottom: 0px; margin-top: 3px; color:#000 !important;">
                                                                    Team Quickest</p><p
                                                                    style="font-size: 13px; margin-bottom: 0px; margin-top: 3px; color:#000 !important;">
                                                                    For any support call: +91 972 429 4153</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="text-align: center; margin: 0px auto;">
                            <p
                                style="font-family: 'Roboto', sans-serif; font-size: 14px;color: #98a6ad; margin: 0px;padding-top: 15px;">
                                {{ date('Y') }} &copy; Quickest.</p>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
