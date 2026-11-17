<div class="modal-header">
    <h5 class="modal-title">
        <i class="fas fa-users text-warning"></i>
        Members for {{ $user->name }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    @if($members->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>{{ $members->count() }}</strong> member(s) found for this business owner.
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0">{{ $members->count() }}</h4>
                        <small>Total Members</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0">{{ $members->where('status', 'active')->count() }}</h4>
                        <small>Active Members</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0">{{ $members->where('status', 'inactive')->count() }}</h4>
                        <small>Inactive Members</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0">{{ $members->whereNotNull('package_id')->count() }}</h4>
                        <small>With Package</small>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No Members Found</h4>
            <p class="text-muted">This business owner doesn't have any members yet.</p>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>


