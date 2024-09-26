<!DOCTYPE html>
<html>

<head>
    <title>Extend email</title>
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
                                                        Dear {{ $mail_details['user']->name }},
                                                    </td>
                                                </tr>
                                                <tr
                                                    style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block"
                                                        style="font-family: 'Roboto', sans-serif; color: #878a99; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;"
                                                        valign="top">
                                                        <p>Congratulations! We're excited to inform you that your free
                                                            trial with Quickest has been extended for an additional 7
                                                            days.</p>
                                                        <p>
                                                            At Quickest, we value your feedback and want to give you the
                                                            opportunity to fully experience all the features and
                                                            benefits of our platform. With this extension, you can
                                                            continue to streamline your proposal process and close more
                                                            deals with confidence.
                                                        </p>
                                                        <p>
                                                            If you have any questions or need assistance, please don't
                                                            hesitate to reach out to our support team. We're here to
                                                            help.
                                                        </p>
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
                                                                <span style="font-weight: 600;">Best regards,</span>
                                                                <p
                                                                    style="font-size: 13px; margin-bottom: 0px; margin-top: 3px; color: #878a99;">
                                                                    The Quickest Team</p>
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
