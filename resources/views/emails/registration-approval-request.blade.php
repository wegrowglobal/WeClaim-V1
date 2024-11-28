<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Registration Request</title>
</head>
<body>
    <p>Hello Admin,</p>

    <p>A new account registration request has been submitted. Please review the details below and take the necessary action.</p>

    <p><strong>Note:</strong> The approval button can only be used once. Please ensure this is a valid request before approving.</p>

    <h3>Registration Details</h3>
    <div style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">First Name:</span>
            <span style="color: #333; font-size: 14px;">{{ $request->first_name }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Last Name:</span>
            <span style="color: #333; font-size: 14px;">{{ $request->last_name }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Email:</span>
            <span style="color: #333; font-size: 14px;">{{ $request->email }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Department:</span>
            <span style="color: #333; font-size: 14px;">{{ $request->department }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Requested At:</span>
            <span style="color: #333; font-size: 14px;">{{ $request->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <p>Please take the necessary action below:</p>

    <table cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
        <tr>
            <td style="padding-right: 10px;">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="background-color: #4CAF50; border-radius: 4px; padding: 10px 20px;">
                            <a href="{{ route('register.approve', $request->token) }}" 
                               style="color: white; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px;">
                                ✓ Approve Registration
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="background-color: #f44336; border-radius: 4px; padding: 10px 20px;">
                            <a href="{{ route('register.reject', $request->token) }}" 
                               style="color: white; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px;">
                                ✗ Reject Registration
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p>Thank you for your attention to this matter.</p>
    <p>Best regards,<br>
    <p><strong>The WeClaim System</strong></p>
</body>
</html> 