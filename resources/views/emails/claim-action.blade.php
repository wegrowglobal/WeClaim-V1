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

    <p><strong>Note:</strong> The approval/rejection buttons can only be used once. Please ensure you select the correct
        action.</p>

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
            @if ($claim->user)
                <span style="color: #333; font-size: 14px;">{{ $claim->user->first_name }}
                    {{ $claim->user->second_name }}</span>
            @else
                <span style="color: #333; font-size: 14px;">Unknown User</span>
            @endif
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Status:</span>
            <span style="color: #333; font-size: 14px;">{{ str_replace('_', ' ', $claim->status) }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">From Date:</span>
            <span style="color: #333; font-size: 14px;">{{ $claim->date_from->format('d/m/Y') }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">To Date:</span>
            <span style="color: #333; font-size: 14px;">{{ $claim->date_to->format('d/m/Y') }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Toll Amount:</span>
            <span style="color: #333; font-size: 14px;">RM {{ number_format($claim->toll_amount, 2) }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <span style="font-weight: bold; display: inline-block; width: 120px; font-size: 14px;">Petrol Amount:</span>
            <span style="color: #333; font-size: 14px;">RM {{ number_format($claim->petrol_amount, 2) }}</span>
        </div>
    </div>

    <h3>Trip Details</h3>
    <div style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
        <div style="margin-bottom: 12px;">
            <p style="font-weight: bold;">Remarks</p>
            <p style="color: #333; font-size: 14px;">{{ $claim->description }}</p>
        </div>

        @foreach ($locations as $location)
            @if ($location->from_location && $location->to_location)
                <div style="margin-bottom: 16px; padding: 16px; border: 1px solid #e6e6e6; border-radius: 6px;">
                    <!-- Location Pair -->
                    <div style="margin-bottom: 12px;">
                        <div style="margin-bottom: 8px;">
                            <span
                                style="display: inline-block; width: 6px; height: 6px; background-color: #4F46E5; border-radius: 50%; margin-right: 8px;"></span>
                            <span style="color: #333; font-size: 14px;">{{ $location->from_location }}</span>
                        </div>
                        <div>
                            <span
                                style="display: inline-block; width: 6px; height: 6px; background-color: #4F46E5; border-radius: 50%; margin-right: 8px;"></span>
                            <span style="color: #333; font-size: 14px;">{{ $location->to_location }}</span>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 12px;">
                        <!-- Distance -->
                        <div>
                            <p style="color: #6B7280; font-size: 12px;">Distance</p>
                            <p style="color: #111827; font-size: 14px; font-weight: 500;">
                                {{ number_format($location->distance, 2) }} km</p>
                        </div>
                        <!-- Cost -->
                        <div>
                            <p style="color: #6B7280; font-size: 12px;">Cost</p>
                            <p style="color: #111827; font-size: 14px; font-weight: 500;">RM
                                {{ number_format($location->distance * 0.6, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <p>Please take the necessary action below:</p>

    <table style="margin-top: 30px;" cellspacing="0" cellpadding="0" border="0">
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
    <p><strong>The WeClaim System</strong></p>
</body>

</html>
