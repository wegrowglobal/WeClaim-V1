<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WeClaim</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 0;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <svg width="48" height="48" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 24px;">
                                            <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="#242424"/>
                                            <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="#242424"/>
                                            <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="#242424"/>
                                        </svg>
                                        <h1 style="margin: 0 0 8px; color: #111827; font-size: 24px; font-weight: 600;">Reset Your Password</h1>
                                        <p style="margin: 0; color: #6B7280; font-size: 16px;">Follow the instructions to reset your password</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 24px 40px;">
                            <p style="margin: 0 0 24px; color: #374151; font-size: 16px; line-height: 24px;">
                                Hello,
                            </p>
                            <p style="margin: 0 0 24px; color: #374151; font-size: 16px; line-height: 24px;">
                                You are receiving this email because we received a password reset request for your account.
                            </p>

                            <!-- Account Details Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px; background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td colspan="2" style="padding: 8px 16px;">
                                                    <h3 style="margin: 0 0 16px; color: #111827; font-size: 16px; font-weight: 600;">Account Details</h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="120" style="padding: 8px 16px; color: #374151; font-size: 14px; font-weight: 500;">
                                                    Email
                                                </td>
                                                <td style="padding: 8px 16px; color: #6B7280; font-size: 14px;">
                                                    {{ $email }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="120" style="padding: 8px 16px; color: #374151; font-size: 14px; font-weight: 500;">
                                                    Expires In
                                                </td>
                                                <td style="padding: 8px 16px; color: #6B7280; font-size: 14px;">
                                                    {{ $count }} minutes
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Action Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 32px 0;">
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" border="0" style="min-width: 160px;">
                                            <tr>
                                                <td style="background-color: #4F46E5; border-radius: 6px; padding: 4px;">
                                                    <a href="{{ $url }}" 
                                                        style="display: block; padding: 12px 24px; color: #ffffff; font-size: 16px; font-weight: 500; text-decoration: none; text-align: center;">
                                                        Reset Password
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- URL Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px; background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0 0 8px; color: #374151; font-size: 14px;">
                                            If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
                                        </p>
                                        <p style="margin: 0; color: #4F46E5; font-size: 14px; word-break: break-all;">
                                            {{ $url }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Warning Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px; background-color: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0; color: #991B1B; font-size: 14px; line-height: 20px;">
                                            If you did not request a password reset, no further action is required.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer -->
                            <p style="margin: 0; color: #374151; font-size: 16px; line-height: 24px;">
                                Best regards,<br>
                                <strong>The WeClaim Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer Note -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #F9FAFB; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            <p style="margin: 0; color: #6B7280; font-size: 14px; line-height: 20px;">
                                This is an automated message. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>