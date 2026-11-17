<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            color: #6c757d;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .summary-card {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 5px;
            min-width: 150px;
            flex: 1;
        }
        .summary-card.success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }
        .summary-card.warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        .summary-card.danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .summary-card h3 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .summary-card p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }
        .filters-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .filters-section h3 {
            color: #495057;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 14px;
        }
        .filter-item strong {
            color: #007bff;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-failed {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Payment Report</h1>
            <p>Generated on {{ date('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-section">
            <div class="summary-card">
                <h3>${{ number_format($summary['total_amount'], 2) }}</h3>
                <p>Total Amount</p>
            </div>
            <div class="summary-card success">
                <h3>${{ number_format($summary['paid_amount'], 2) }}</h3>
                <p>Paid Amount</p>
            </div>
            <div class="summary-card warning">
                <h3>${{ number_format($summary['pending_amount'], 2) }}</h3>
                <p>Pending Amount</p>
            </div>
            <div class="summary-card danger">
                <h3>${{ number_format($summary['failed_amount'], 2) }}</h3>
                <p>Failed Amount</p>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <h3>Report Filters</h3>
            <div class="filter-item">
                <strong>From Date:</strong> {{ date('F j, Y', strtotime($filters['from_date'])) }}
            </div>
            <div class="filter-item">
                <strong>To Date:</strong> {{ date('F j, Y', strtotime($filters['to_date'])) }}
            </div>
            <div class="filter-item">
                <strong>Payment Status:</strong> {{ ucfirst($filters['payment_status']) }}
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="filters-section">
            <h3>Summary Statistics</h3>
            <div class="filter-item">
                <strong>Total Payments:</strong> {{ $summary['total_count'] }}
            </div>
            <div class="filter-item">
                <strong>Paid Payments:</strong> {{ $summary['paid_count'] }}
            </div>
            <div class="filter-item">
                <strong>Pending Payments:</strong> {{ $summary['pending_count'] }}
            </div>
            <div class="filter-item">
                <strong>Failed Payments:</strong> {{ $summary['failed_count'] }}
            </div>
        </div>

        <!-- Payments Table -->
        @if(count($payments) > 0)
        <div class="table-container">
            <h3>Payment Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member Name</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $payment->member ? $payment->member->name : 'N/A' }}</td>
                        <td>{{ $payment->package ? $payment->package->name : 'N/A' }}</td>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>
                            <span class="status-badge status-{{ $payment->payment_status }}">
                                {{ ucfirst($payment->payment_status) }}
                            </span>
                        </td>
                        <td>{{ date('M j, Y', strtotime($payment->start_date)) }}</td>
                        <td>{{ date('M j, Y', strtotime($payment->end_date)) }}</td>
                        <td>{{ date('M j, Y', strtotime($payment->created_at)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="filters-section">
            <h3>No Payments Found</h3>
            <p>No payments were found for the selected criteria. Please try adjusting your date range or payment status filter.</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This report was generated automatically by the Gym Management System</p>
            <p>© {{ date('Y') }} Gym Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 