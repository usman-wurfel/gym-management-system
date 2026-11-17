<div class="modal-header">
    <h5 class="modal-title">
        <i class="fas fa-credit-card text-info"></i>
        Payments for {{ $member->name }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <!-- Member Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="font-weight-bold">Member Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{{ $member->name }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $member->email ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td>{{ $member->phone ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Package:</strong></td>
                    <td>
                        @if($member->package)
                            <span class="badge badge-primary">{{ $member->package->name }}</span>
                        @else
                            <span class="text-muted">No Package</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="font-weight-bold">Payment Summary</h6>
            <div class="row">
                <div class="col-6">
                    <div class="card bg-primary text-white text-center">
                        <div class="card-body py-2">
                            <h6 class="mb-0">{{ $payments->count() }}</h6>
                            <small>Total Payments</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body py-2">
                            <h6 class="mb-0">${{ number_format($payments->sum('amount'), 2) }}</h6>
                            <small>Total Amount</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($payments->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
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
                    @foreach($payments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($payment->package)
                                <strong>{{ $payment->package->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $payment->package->description }}</small>
                            @else
                                <span class="text-muted">No Package</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-weight-bold text-success">${{ number_format($payment->amount, 2) }}</span>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($payment->payment_method) }}</span>
                        </td>
                        <td>
                            @if($payment->payment_status == 'paid')
                                <span class="badge badge-success">Paid</span>
                            @elseif($payment->payment_status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($payment->payment_status == 'failed')
                                <span class="badge badge-danger">Failed</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($payment->payment_status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->start_date)
                                {{ \Carbon\Carbon::parse($payment->start_date)->format('M d, Y') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->end_date)
                                {{ \Carbon\Carbon::parse($payment->end_date)->format('M d, Y') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $payment->created_at->format('M d, Y H:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Payments Found</h5>
            <p class="text-muted">This member doesn't have any payment records yet.</p>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
