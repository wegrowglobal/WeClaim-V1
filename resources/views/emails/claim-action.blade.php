<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Approval Request</title>
</head>
<body>
    <p>Hello Datuk Dr Yong,</p>

    <p>A claim has been submitted for your approval. Please review the details below and take the necessary action.</p>

    <h3>Claim Details</h3>
    <div style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Claim ID:</span>
            <span style="color: #333; font-size: 14px;">{{ $claim->id }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Title:</span>
            <span style="color: #333; font-size: 14px;">{{ $claim->title }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Submitted By:</span>
            <span style="color: #333; font-size: 14px;">{{ $claim->user->first_name }} {{ $claim->user->second_name }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Status:</span>
            <span style="color: #333; font-size: 14px;">{{ str_replace('_', ' ', $claim->status) }}</span>
        </div>
    </div>

    <h3>Locations</h3>
    <div style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
      <div style="margin-bottom: 12px;">
        <p style="font-weight: bold;">Remarks</p>
        <p style="color: #333; font-size: 14px;">{{ $claim->description }}</p>
      </div>
        @foreach($locations as $index => $location)
            <div style="margin-bottom: 8px; padding: 8px;">
                <span style="font-weight: bold; margin-right: 10px; font-size: 14px;">{{ $index + 1 }}.</span>
                <span style="color: #333; font-size: 14px;">{{ $location->location }}</span>
            </div>
        @endforeach
    </div>

    <p>Please take the necessary action below:</p>

    <table cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
        <tr>
            <td style="padding-right: 10px;">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="background-color: #4CAF50; border-radius: 4px; padding: 10px 20px;">
                            <a href="{{ route('claims.email.action', ['id' => $claim->id, 'action' => 'approve']) }}" 
                               style="color: white; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px;">
                                ✓ Approve Claim
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="background-color: #f44336; border-radius: 4px; padding: 10px 20px;">
                            <a href="{{ route('claims.email.action', ['id' => $claim->id, 'action' => 'reject']) }}" 
                               style="color: white; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px;">
                                ✗ Reject Claim
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p>Thank you for your attention to this matter.</p>
    <p>Best regards,<br>
    <strong>The WeClaim System</strong></p>
</body>
</html>