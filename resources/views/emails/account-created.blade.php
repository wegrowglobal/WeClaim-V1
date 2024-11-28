<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created - Set Password</title>
</head>
<body>
    <p>Hello {{ $user->first_name }},</p>

    <p>Your account registration has been approved. Please set up your password to access your account.</p>

    <div style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Name:</span>
            <span style="color: #333; font-size: 14px;">{{ $user->first_name }} {{ $user->last_name }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Email:</span>
            <span style="color: #333; font-size: 14px;">{{ $user->email }}</span>
        </div>
    </div>

    <p>Click the button below to set your password:</p>

    <table cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
        <tr>
            <td style="background-color: #4CAF50; border-radius: 4px; padding: 10px 20px;">
                <a href="{{ route('password.setup.form', $token) }}" 
                   style="color: white; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px;">
                    Set Password
                </a>
            </td>
        </tr>
    </table>

    <p>Best regards,<br>
    <p><strong>The WeClaim System</strong></p>
</body>
</html> 