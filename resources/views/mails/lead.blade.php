<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Email Receipt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        /**
   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
   */
        @media screen {
            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 400;
                src: local('Source Sans Pro Regular'), local('SourceSansPro-Regular'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format('woff');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 700;
                src: local('Source Sans Pro Bold'), local('SourceSansPro-Bold'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format('woff');
            }
        }

        /**
   * Avoid browser level font resizing.
   * 1. Windows Mobile
   * 2. iOS / OSX
   */
        body,
        table,
        td,
        a {
            -ms-text-size-adjust: 100%;
            /* 1 */
            /* 2 */
            -webkit-text-size-adjust: 100%;
        }

        /**
   * Remove extra space added to tables and cells in Outlook.
   */
        table,
        td {
            mso-table-rspace: 0pt;
            mso-table-lspace: 0pt;
        }

        /**
   * Better fluid images in Internet Explorer.
   */
        img {
            -ms-interpolation-mode: bicubic;
        }

        /**
   * Remove blue links for iOS devices.
   */
        a[x-apple-data-detectors] {
            font-family: inherit !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
            color: inherit !important;
            text-decoration: none !important;
        }

        /**
   * Fix centering issues in Android 4.4.
   */
        div[style*="margin: 16px 0;"] {
            margin: 0 !important;
        }

        body {
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /**
   * Collapse table borders to avoid space between cells.
   */
        table {
            border-collapse: collapse !important;
        }

        a {
            color: #1a82e2;
        }

        img {
            height: auto;
            line-height: 100%;
            text-decoration: none;
            border: 0;
            outline: none;
        }

    </style>

</head>

<body style="background-color: #f6f6f6;">

    <!-- start preheader -->
    <div class="preheader"
        style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
        Oba! Aqui está o link de acesso para o seu catálogo!
    </div>
    <!-- end preheader -->

    <!-- start body -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%">

        <!-- start logo -->
        <tr>
            <td align="center" bgcolor="#f6f6f6">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="center" valign="top" style="padding: 36px 24px;">
                            <a href="{{ config('app.url') }}" target="_blank" style="display: inline-block;">
                                <img src="{{ asset('images/logo.png') }}" alt="Logo" border="0" width="250"
                                    style="display: block; width: 250px; max-width: 250px; min-width: 250px;">
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- end logo -->
        <!-- start hero -->
        <tr>
            <td align="center" bgcolor="#f6f6f6">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="left" bgcolor="#ffffff"
                            style="padding: 36px 24px 0; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; border-top: 3px solid #d4dadf;">
                            <h1
                                style="margin: 0; color:black; font-size: 38px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">
                                {{ $lead->catalog->name }}!</h1>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- end hero -->
        <!-- start copy block -->
                <tr>
                    <td align="center" bgcolor="#f6f6f6">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <!-- start copy -->
                            <tr>
                                <td align="left" bgcolor="#ffffff"
                                    style="padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                                    <p style="margin: 0; color:black;">
                                        {!! $lead->catalog->text_email !!}
                                    </p>
                                </td>
                            </tr>
                            <!-- end copy -->
                        </table>
                    </td>
                </tr>
                <!-- end copy block -->

            <tr>
                <td align="center" bgcolor="#f6f6f6" valign="top" width="100%">
                    <table align="center" bgcolor="#fff" border="0" cellpadding="0" cellspacing="0" width="100%"
                        style="max-width: 600px;">
                        <tr>
                            <td align="left" valign="top" style="font-size: 0; border-bottom: 3px solid #d4dadf">
                                <div
                                    style="display: inline-block; width: 100%; max-width: 70%; justify-content-center min-width: 270px; vertical-align: top;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td align="center" valign="top"
                                                style="padding-top: 26px; padding-bottom: 46px; padding-left: 36px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                                                    <a align="center" type="button" style="border-radius: 12px; padding: 10px 24px; font-size: 18px;  background-color:#1a82e2; color:white; border: none;text-align: center; text-decoration: none; display: inline-block; "  href="{{ $lead->catalog->url }}">{{$lead->catalog->name}}</a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        <!-- start footer -->
        <tr>
            <td align="center" bgcolor="#f6f6f6" style="padding: 24px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                </table>
            </td>
        </tr>
        <!-- end footer -->

    </table>
    <!-- end body -->

</body>

</html>
