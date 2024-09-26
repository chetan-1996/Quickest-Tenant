<!DOCTYPE html>
<html>

<head>
    <title>User & Plan Upgrade </title>
</head>

<body style="background-color: #f2f2f7;">
{{-- <h1>{{ $mail_details['subject'] }}</h1> --}}

<table class="body-wrap"
       style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: transparent; margin: 0;">
    <tbody>
    <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <td style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;"
            valign="top"></td>
        <td class="container" width="600"
            style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;"
            valign="top">
            <div class="content"
                 style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
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
                                            <img src="{{ asset('assets/images/logo.png') }}"
                                                 alt="" height="70">
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 20px; line-height: 1.5; font-weight: 500; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                        valign="top">
                                        Dear {{ $plan_purchase_details['user_name'] }},
                                    </td>
                                </tr>
                                @if($plan_purchase_details['plan_type']==1)
                                <tr
                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; color: #000; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                        valign="top">
                                        <p>We are pleased to inform you that your Quickest plan limit has been upgraded. As a result, your total user limit is {{$plan_purchase_details['user_limit']}}.</p>
                                        {{--<p style="font-weight: bold;text-decoration-line: underline;">
                                            Thank you for your continued trust in our product. If you have any questions or concerns, please don't hesitate to reach out to our support team.

                                        </p>--}}
                                    </td>
                                </tr>

                                <tr>
                                    <th style="padding: 5px;text-align:left;">
                                        <p
                                            style="color: #000; margin-bottom: 2px; font-weight: 400;">
                                            Thank you for your continued trust in our product. If you have any questions or concerns, please don't hesitate to reach out to our support team.</p>
                                    </th>
                                </tr>
                                @endif
                                @if($plan_purchase_details['plan_type']==0)
                                    <tr
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td class="content-block"
                                            style="font-family: 'Roboto', sans-serif; color: #000; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                            valign="top">
                                            <p>We wanted to let you know that your plan has been successfully upgraded to <b>{{$plan_purchase_details['plan_name']}}</b>. You can now enjoy all the premium features of the Quickest.</p>
                                            <p>Your <b>{{$plan_purchase_details['plan_name']}}</b> includes total <b>{{$plan_purchase_details['users_limit']}} users</b> and <b>{{($plan_purchase_details['estimate_limit']==null ||$plan_purchase_details['estimate_limit']==0)? 'unlimited': $plan_purchase_details['estimate_limit']}}</b> estimates per month.</p>
                                            <p>This upgrade means that you can now enjoy even more benefits and features from Quickest. You can now invite more team members or dealers to collaborate on your sales processes and generate more estimates to grow your business.</p>
                                           {{-- <p style="font-weight: bold;text-decoration-line: underline;">
                                                Thank you for your continued trust in our product. If you have any questions or concerns, please don't hesitate to reach out to our support team.
                                            </p>--}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="padding: 5px;text-align:left;">
                                            <p
                                                style="color: #000; margin-bottom: 2px; font-weight: 400;">
                                                Thank you for choosing our service!</p>
                                        </th>
                                    </tr>
                                @endif
                                <tr
                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; border-top: 1px solid #e9ebec;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0; padding-top: 15px"
                                        valign="top">
                                        <div style="display: flex; align-items: center;">
                                            {{--  <img src="assets/images/users/avatar-3.jpg" alt="" height="35" width="35"
                                 style="border-radius: 50px;"> --}}
                                            <div style="margin-left: 8px;">
                                                <span style="font-weight: 600;">Best regards,</span>
                                                <p
                                                    style="font-size: 13px; margin-bottom: 0px; margin-top: 3px; color: #000;">
                                                    Quickest Team</p>
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
