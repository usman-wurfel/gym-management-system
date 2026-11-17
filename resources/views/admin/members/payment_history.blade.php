<div class="card-datatable table-responsive">
    <div class="row mb-4">
        <div class="col-md-6">
            <h5 class="font-weight-bold">Member Information</h5>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{{$member->name}}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{$member->email}}</td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td>{{$member->phone}}</td>
                </tr>
                <tr>
                    <td><strong>Current Package:</strong></td>
                    <td>{{$member->package ? $member->package->name : 'N/A'}}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5 class="font-weight-bold">Payment Summary</h5>
            @php
                $totalPayments = $member->payments->count();
                $paidPayments = $member->payments->where('payment_status', 'paid')->count();
                $pendingPayments = $member->payments->where('payment_status', 'pending')->count();
                $totalPaid = $member->payments->where('payment_status', 'paid')->sum('amount');
                $totalPending = $member->payments->where('payment_status', 'pending')->sum('amount');
            @endphp
            <table class="table table-borderless">
                <tr>
                    <td><strong>Total Payments:</strong></td>
                    <td>{{$totalPayments}}</td>
                </tr>
                <tr>
                    <td><strong>Paid Payments:</strong></td>
                    <td><span class="badge badge-success">{{$paidPayments}}</span></td>
                </tr>
                <tr>
                    <td><strong>Pending Payments:</strong></td>
                    <td><span class="badge badge-warning">{{$pendingPayments}}</span></td>
                </tr>
                <tr>
                    <td><strong>Total Paid:</strong></td>
                    <td><span class="text-success font-weight-bold">Rs{{number_format($totalPaid, 2)}}</span></td>
                </tr>
                <tr>
                    <td><strong>Total Pending:</strong></td>
                    <td><span class="text-warning font-weight-bold">Rs{{number_format($totalPending, 2)}}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <h5 class="font-weight-bold mb-3">Payment History</h5>
    @if($member->payments->count() > 0)
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
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
                @foreach($member->payments->sortByDesc('created_at') as $index => $payment)
                <tr>
                    <td>{{$index + 1}}</td>
                    <td>{{$payment->package ? $payment->package->name : 'N/A'}}</td>
                    <td><strong>Rs{{number_format($payment->amount, 2)}}</strong></td>
                    <td>{{ucfirst($payment->payment_method)}}</td>
                    <td>
                        @if($payment->payment_status == 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($payment->payment_status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @else
                            <span class="badge badge-danger">Failed</span>
                        @endif
                    </td>
                    <td>{{$payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A'}}</td>
                    <td>{{$payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A'}}</td>
                    <td>{{$payment->created_at->format('M d, Y H:i')}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            No payment history found for this member.
        </div>
    @endif
</div> 