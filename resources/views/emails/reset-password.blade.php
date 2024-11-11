<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Request</title>
</head>
<body>
    <p>Hello,</p>

    <p>You are receiving this email because we received a password reset request for your account.</p>

    <h3>Account Details</h3>
    <div style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Email:</span>
            <span style="color: #333; font-size: 14px;">{{ $email }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Expires In:</span>
            <span style="color: #333; font-size: 14px;">{{ $count }} minutes</span>
        </div>
    </div>

    <p>Please click the button below to reset your password:</p>

    <table cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="background-color: #4CAF50; border-radius: 4px; padding: 10px 20px;">
                            <a href="{{ $url }}" 
                               style="color: white; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px;">
                                Reset Password
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin-top: 20px;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
    
    <p style="color: #333; font-size: 14px;">{{ $url }}</p>

    <p>If you did not request a password reset, no further action is required.</p>

    <p>Best regards,<br>
    <p><strong>The WeClaim System</strong></p>
</body>
</html>