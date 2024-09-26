<!DOCTYPE html>
<html>
<head>
    <title>User Account Approved</title>
</head>
<body style="background-color: #f2f2f7;">
{{--<h1>{{ $mail_details['subject'] }}</h1>--}}

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
                                <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;"
                                        valign="top">
                                        <div style="text-align: center;margin-bottom: 15px;">
                                            <img src="{{asset('assets/images/logo.png')}}" alt="" height="70">
                                        </div>
                                    </td>
                                </tr>
                                {{--                                <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">--}}
                                {{--                                    <td class="content-block" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 24px; vertical-align: top; margin: 0; padding: 0 0 10px;  text-align: center;" valign="top">--}}
                                {{--                                        <h4 style="font-family: 'Roboto', sans-serif; font-weight: 500;">Get started</h4>--}}
                                {{--                                    </td>--}}
                                {{--                                </tr>--}}
                                <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 20px; line-height: 1.5; font-weight: 500; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                        valign="top">
                                        Hello {{$mail_details['name']}},
                                    </td>
                                </tr>
                                <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; color: #878a99; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                        valign="top">
                                        <p>Thank You for choosing Quickest as your Sales CRM.</p>
                                        <p>Your Quickest account is now active. You can start generating professional
                                            estimates via mobile app or web portal.</p>
                                        <p>You may require to set up your proposal template. For that please log in to
                                            web portal.</p>
                                        <p>Web portal link: <a href="{{url('/')}}"
                                                               style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: .8125rem;font-weight: 400; color: #FFF; text-decoration: none; display: inline-block; border-radius: .25rem; text-transform: capitalize; background-color: #4b38b3; margin: 0; border-color: #4b38b3; border-style: solid; border-width: 1px; padding: .5rem .9rem;box-shadow: 0 3px 3px rgba(56,65,74,0.1);"
                                                               onmouseover="this.style.background='#4b38b3'"
                                                               onmouseout="this.style.background='#4b38b3'">Login to
                                                your account</a></p>
                                        <p>Please contact us if any query. Whatsapp Link: <a target="_blank"
                                                                                             href="https://wa.me/message/I7RRZKCYKXR4O1"
                                                                                             title="Click here"><img
                                                    src="{{asset('assets/images/whatsapp.gif')}}" height="32"></a>
                                        <p>Thank You</p>
                                    </td>
                                </tr>


                                <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; border-top: 1px solid #e9ebec;">
                                    <td class="content-block"
                                        style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0; padding-top: 15px"
                                        valign="top">
                                        <div style="display: flex; align-items: center;">
                                            {{--  <img src="assets/images/users/avatar-3.jpg" alt="" height="35" width="35"
                                                   style="border-radius: 50px;">--}}
                                            <div style="margin-left: 8px;">
                                                <span style="font-weight: 600;">Regards,</span>
                                                <p style="font-size: 13px; margin-bottom: 0px; margin-top: 3px; color: #878a99;">
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
                    <p style="font-family: 'Roboto', sans-serif; font-size: 14px;color: #98a6ad; margin: 0px;padding-top: 15px;">
                        {{date('Y')}} &copy; Quickest.</p>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
