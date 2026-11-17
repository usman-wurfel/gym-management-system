<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .company-details {
            font-size: 14px;
            line-height: 1.6;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .invoice-date {
            font-size: 14px;
            color: #666;
        }
        .client-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .bill-to {
            flex: 1;
        }
        .bill-to h3 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .client-details {
            font-size: 14px;
            line-height: 1.6;
        }
        .payment-summary {
            flex: 1;
            text-align: right;
        }
        .payment-summary h3 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .summary-label {
            font-weight: bold;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .invoice-table th {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .invoice-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .invoice-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .amount-column {
            text-align: right;
            font-weight: bold;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            border-top: 2px solid #007bff;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
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
        .terms {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .terms h4 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .terms p {
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-logo">
                    {{ $settings['site_title'] ?? 'Gym Management System' }}
                </div>
                <div class="company-details">
                    @if(isset($settings['address']))
                        {{ $settings['address'] }}<br>
                    @endif
                    @if(isset($settings['phone']))
                        Phone: {{ $settings['phone'] }}<br>
                    @endif
                    @if(isset($settings['email']))
                        Email: {{ $settings['email'] }}
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">Invoice #: {{ $invoice_number }}</div>
                <div class="invoice-date">Date: {{ $invoice_date }}</div>
                <div class="invoice-date">Due Date: {{ $due_date }}</div>
            </div>
        </div>

        <!-- Client and Payment Info -->
        <div class="client-info">
            <div class="bill-to">
                <h3>Bill To:</h3>
                <div class="client-details">
                    <strong>{{ $payment->member->name }}</strong><br>
                    @if($payment->member->email)
                        Email: {{ $payment->member->email }}<br>
                    @endif
                    @if($payment->member->phone)
                        Phone: {{ $payment->member->phone }}<br>
                    @endif
                    @if($payment->member->address)
                        Address: {{ $payment->member->address }}<br>
                        {{ $payment->member->city }}
                    @endif
                </div>
            </div>
            <div class="payment-summary">
                <h3>Payment Summary</h3>
                <div class="summary-item">
                    <span class="summary-label">Payment ID:</span>
                    <span>#{{ $payment->id }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Status:</span>
                    <span class="status-badge status-{{ $payment->payment_status }}">
                        {{ ucfirst($payment->payment_status) }}
                    </span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Method:</span>
                    <span>{{ ucfirst($payment->payment_method) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Created:</span>
                    <span>{{ $payment->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Package Details</th>
                    <th>Period</th>
                    <th class="amount-column">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $payment->package->name }}</strong><br>
                        <small>{{ $payment->package->description ?? 'Gym Membership Package' }}</small>
                    </td>
                    <td>
                        Duration: {{ $payment->package->duration ?? 'N/A' }} days<br>
                        @if($payment->package->features)
                            <small>{{ $payment->package->features }}</small>
                        @endif
                    </td>
                    <td>
                        From: {{ date('M d, Y', strtotime($payment->start_date)) }}<br>
                        To: {{ date('M d, Y', strtotime($payment->end_date)) }}
                    </td>
                    <td class="amount-column">${{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax (0%):</span>
                <span>$0.00</span>
            </div>
            <div class="total-row total-amount">
                <span>Total Amount:</span>
                <span>${{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <!-- Notes -->
        @if($payment->note)
        <div class="terms">
            <h4>Notes:</h4>
            <p>{{ $payment->note }}</p>
        </div>
        @endif

        <!-- Terms and Conditions -->
        <div class="terms">
            <h4>Terms & Conditions:</h4>
            <p>
                1. Payment is due within 30 days of invoice date.<br>
                2. Late payments may incur additional charges.<br>
                3. This invoice is computer generated and valid without signature.<br>
                4. For any queries, please contact us at {{ $settings['email'] ?? 'support@gym.com' }}
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Thank you for choosing {{ $settings['site_title'] ?? 'Gym Management System' }}!<br>
                This is a computer-generated invoice. No signature required.
            </p>
        </div>
    </div>
</body>
</html>

