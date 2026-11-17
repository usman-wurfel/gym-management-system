@extends('admin.layouts.master')
@section('title', 'Members - ' . $user->name)

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="card card-custom mb-5">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">
                        <i class="fas fa-users text-warning mr-2"></i>
                        Members for {{ $user->name }}
                    </h3>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('team.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Business List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Business Owner Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($user->active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Member Statistics</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-primary text-white text-center">
                                    <div class="card-body">
                                        <h4 class="mb-0">{{ $members->count() }}</h4>
                                        <small>Total Members</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-success text-white text-center">
                                    <div class="card-body">
                                        <h4 class="mb-0">{{ $members->where('status', 'active')->count() }}</h4>
                                        <small>Active Members</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members Table -->
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Member List</h3>
                </div>
            </div>
            <div class="card-body">
                @if($members->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="membersTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Package</th>
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $index => $member)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $member->name }}</strong>
                                        @if($member->code)
                                            <br><small class="text-muted">Code: {{ $member->code }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $member->email ?: 'N/A' }}</td>
                                    <td>{{ $member->phone ?: 'N/A' }}</td>
                                    <td>
                                        @if($member->package)
                                            <span class="badge badge-primary">{{ $member->package->name }}</span>
                                        @else
                                            <span class="text-muted">No Package</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->status == 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($member->status == 'inactive')
                                            <span class="badge badge-danger">Inactive</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($member->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->start_date)
                                            {{ \Carbon\Carbon::parse($member->start_date)->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $member->created_at->format('M d, Y H:i A') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="showPayments({{ $member->id }})" title="View Payments">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Members Found</h4>
                        <p class="text-muted">This business owner doesn't have any members yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payments Modal -->
<div class="modal fade" id="paymentsModal" data-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="paymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#membersTable').DataTable({
        "order": [[0, "asc"]],
        "pageLength": 25,
        "responsive": true
    });
});

function showPayments(memberId) {
    var CSRF_TOKEN = '{{ csrf_token() }}';
    $.post("{{ route('admin.show-member-payments') }}", {
        _token: CSRF_TOKEN, 
        member_id: memberId
    }).done(function (response) {
        $('#paymentsModal .modal-content').html(response);
        $('#paymentsModal').modal('show');
    }).fail(function() {
        toastr.error('Error loading payments');
    });
}
</script>
@endsection
