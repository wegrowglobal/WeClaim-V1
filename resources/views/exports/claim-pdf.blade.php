<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            background-color: #000;
            color: #fff;
            padding: 8px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 10px;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .total-row {
            background-color: #ebf5ff;
            color: #1e40af;
            font-weight: bold;
        }
        .signature-section {
            page-break-inside: avoid;
            margin-top: 30px;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .signature-box {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            height: 120px;
        }
        .signature-box img {
            max-height: 70px;
            margin-bottom: 10px;
        }
        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .signature-role {
            font-size: 0.9em;
            color: #666;
        }
        .datuk-signature {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            height: 120px;
            margin-top: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #000;
            color: #fff;
            text-align: right;
            padding: 5px;
            font-style: italic;
            font-size: 0.9em;
        }
        tr:nth-child(even) td {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Travel Claim Report</h1>
        <h3>{{ strtoupper($data['company']) }}</h3>
    </div>

    <div class="section">
        <div class="section-title">Claim Information</div>
        <div class="info-grid">
            <div class="info-label">Claim ID</div>
            <div>#{{ $claim->id }}</div>
            <div class="info-label">Employee Name</div>
            <div>{{ $data['employee_name'] }}</div>
            <div class="info-label">Department</div>
            <div>{{ $data['department'] }}</div>
            <div class="info-label">Claim Period</div>
            <div>{{ $data['date_from'] }} to {{ $data['date_to'] }}</div>
            <div class="info-label">Status</div>
            <div>{{ $data['status'] }}</div>
            <div class="info-label">Submitted Date</div>
            <div>{{ $data['submitted_at'] }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Trip Details</div>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>From</th>
                    <th>To</th>
                    <th>KM</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['locations'] as $index => $location)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $location['from'] }}</td>
                        <td>{{ $location['to'] }}</td>
                        <td>{{ $location['distance'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Financial Summary</div>
        <table>
            <tr>
                <td>Total Distance</td>
                <td>{{ $data['total_distance'] }} KM</td>
            </tr>
            <tr>
                <td>Petrol Amount</td>
                <td>RM {{ $data['petrol_amount'] }}</td>
            </tr>
            <tr>
                <td>Toll Amount</td>
                <td>RM {{ $data['toll_amount'] }}</td>
            </tr>
            <tr>
                <td>Accommodation Cost</td>
                <td>RM {{ $data['accommodation_cost'] ?? '0.00' }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Amount</td>
                <td>RM {{ $data['total_amount'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Approval History</div>
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                    <tr>
                        <td>{{ $review['date'] }}</td>
                        <td>{{ $review['department'] }}</td>
                        <td>{{ $review['status'] }}</td>
                        <td>{{ $review['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <div class="section-title">Signatures</div>
        <div class="signature-grid">
            <!-- Claim Owner & Admin -->
            <div class="signature-box">
                @if($signatures['claim_owner']['path'])
                    <img src="{{ storage_path('app/public/' . $signatures['claim_owner']['path']) }}" alt="Claim Owner Signature">
                @endif
                <div class="signature-name">Approved by {{ $signatures['claim_owner']['name'] }}</div>
                <div class="signature-role">{{ $signatures['claim_owner']['role'] }}</div>
            </div>
            <div class="signature-box">
                @if($signatures['admin']['path'])
                    <img src="{{ storage_path('app/public/' . $signatures['admin']['path']) }}" alt="Admin Signature">
                @endif
                <div class="signature-name">Approved by {{ $signatures['admin']['name'] }}</div>
                <div class="signature-role">{{ $signatures['admin']['role'] }}</div>
            </div>
            <!-- Manager & HR -->
            <div class="signature-box">
                @if($signatures['manager']['path'])
                    <img src="{{ storage_path('app/public/' . $signatures['manager']['path']) }}" alt="Manager Signature">
                @endif
                <div class="signature-name">Approved by {{ $signatures['manager']['name'] }}</div>
                <div class="signature-role">{{ $signatures['manager']['role'] }}</div>
            </div>
            <div class="signature-box">
                @if($signatures['hr']['path'])
                    <img src="{{ storage_path('app/public/' . $signatures['hr']['path']) }}" alt="HR Signature">
                @endif
                <div class="signature-name">Approved by {{ $signatures['hr']['name'] }}</div>
                <div class="signature-role">{{ $signatures['hr']['role'] }}</div>
            </div>
        </div>
        <!-- Datuk Signature -->
        <div class="datuk-signature">
            @if($signatures['datuk']['path'])
                <img src="{{ storage_path('app/public/' . $signatures['datuk']['path']) }}" alt="Datuk Signature">
            @endif
            <div class="signature-name">Approved by {{ $signatures['datuk']['name'] }}</div>
            <div class="signature-role">{{ $signatures['datuk']['role'] }}</div>
        </div>
    </div>

    <div class="footer">
        Generated at: {{ $generated_at }} by WeClaim System
    </div>
</body>
</html> 