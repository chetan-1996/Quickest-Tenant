<!DOCTYPE html>
<html>

<head>
    <title>User Registration</title>
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
                                                        Dear {{ $mail_details['name'] }} JI,
                                                    </td>
                                                </tr>
                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; color: #878a99; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                                        valign="top">
                                                        <p style="color:#3b3f5c;">We're thrilled to welcome you to QuickEst! We're excited to have you on board as a new user, and we can't wait for you to experience all that our app has to offer.</p>
                                                        <p style="color:#3b3f5c;font-weight: bold;text-decoration-line: underline;">
                                                            With QuickEst, you'll be able to Grow your sales rapidly.
                                                        </p>
                                                        <p style="color:#3b3f5c; margin-bottom: 2px; font-weight: 400;">
                                                            To get started, access our private guide : </p>
                                                        <a
                                                            href=" https://sites.google.com/view/quickest-guide"
                                                            target="_blank">  https://sites.google.com/view/quickest-guide
                                                        </a>

                                                        {{--<p class="text-black">
                                                            <i>
                                                            <span style="font-weight: bold;">Step 1:</span> Introduction
                                                            To Quickest <a href="https://youtu.be/sdGzsTU5HDY"
                                                                target="_blank">https://youtu.be/sdGzsTU5HDY</a>
                                                            </i>
                                                        </p>--}}
                                                        {{--<p class="text-black">
                                                            <i>
                                                            <span style="font-weight: bold;">Step 2:</span> How to set
                                                            up your company profile in
                                                            Quickest? <a href="https://youtu.be/jSJ3uc50WBE"
                                                                target="_blank">https://youtu.be/jSJ3uc50WBE</a>
                                                            </i>
                                                        </p>--}}
                                                       {{-- <p class="text-black">
                                                            <i>
                                                            <span style="font-weight: bold;">Step 3:</span> How to use Quickest Web Portal? <a
                                                                href="https://youtu.be/0e5kJjDmY3w"
                                                                target="_blank">https://youtu.be/0e5kJjDmY3w
                                                            </a>
                                                            </i>
                                                        </p>--}}
                                                        {{--<p class="text-black">
                                                            <i>
                                                            <span style="font-weight: bold;">Step 4:</span> How to use mobile app? <a
                                                                href=https://youtu.be/fPmx589UiwE"
                                                                target="_blank"> https://youtu.be/fPmx589UiwE
                                                            </a>
                                                            </i>
                                                        </p>--}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th style="padding: 5px;text-align:left;">
                                                        <p style="color:#3b3f5c; margin-bottom: 2px; font-weight: 400;">
                                                            <b>For more support & to Book a meeting with us</b></p>
                                                        <a
                                                            href="https://wa.me/message/BC4IGQAQTYALB1"
                                                            target="_blank"> https://wa.me/message/BC4IGQAQTYALB1
                                                        </a>
                                                    </th>
                                                </tr>

                                                <tr>
                                                    <th style="padding: 5px;text-align:left;">

                                                        <p style="color:#3b3f5c;margin-bottom: 2px; font-weight: 400;">
                                                            Thank you for choosing Quickest, and welcome to the
                                                            community!</p>
                                                    </th>
                                                </tr>
                                                {{-- <tr>
                                    <th style="padding: 5px;text-align:left;">
                                        <p style="color: #878a99; font-size: 13px; margin-bottom: 2px; font-weight: 400;">Password</p>
                                        <span>{{$mail_details['password']}}</span>
                                    </th>
                                </tr> --}}

                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 24px;text-align: center;padding-top: 15px;text-align:left;"
                                                        valign="top">
                                                        <p style="color:#3b3f5c;">Download Mobile App.</p>
                                                        <a href="https://apps.apple.com/us/app/quickest-sales-crm/id1635101087"
                                                            target="_blank"
                                                            style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: .8125rem;font-weight: 400; color: #FFF; text-decoration: none; display: inline-block; border-radius: .25rem; text-transform: capitalize; background-color: #4b38b3; margin: 0; border-color: #4b38b3; border-style: solid; border-width: 1px; padding: .5rem .9rem;box-shadow: 0 3px 3px rgba(56,65,74,0.1);"
                                                            onmouseover="this.style.background='#4b38b3'"
                                                           onmouseout="this.style.background='#4b38b3'">App Store</a>
                                                        <a href="https://play.google.com/store/apps/details?id=co.quickestimate.app&pli=1"
                                                            target="_blank"
                                                            style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: .8125rem;font-weight: 400; color: #FFF; text-decoration: none; display: inline-block; border-radius: .25rem; text-transform: capitalize; background-color: #4b38b3; margin: 0; border-color: #4b38b3; border-style: solid; border-width: 1px; padding: .5rem .9rem;box-shadow: 0 3px 3px rgba(56,65,74,0.1);"
                                                            onmouseover="this.style.background='#4b38b3'"
                                                           onmouseout="this.style.background='#4b38b3'">Play Store</a>
                                                        <a href="{!! url('/') !!}"
                                                           target="_blank"
                                                           style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: .8125rem;font-weight: 400; color: #FFF; text-decoration: none; display: inline-block; border-radius: .25rem; text-transform: capitalize; background-color: #4b38b3; margin: 0; border-color: #4b38b3; border-style: solid; border-width: 1px; padding: .5rem .9rem;box-shadow: 0 3px 3px rgba(56,65,74,0.1);"
                                                           onmouseover="this.style.background='#4b38b3'"
                                                           onmouseout="this.style.background='#4b38b3'"><i>Web Portal</i></a>
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
                                                                <span style="color:#3b3f5c;font-weight: 600;"><b>Best,</b></span>
                                                                <br>
                                                                <p
                                                                    style="color:#3b3f5c;font-size: 13px; margin-bottom: 0px; margin-top: 3px;">
                                                                    Team Quickest</p>
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
