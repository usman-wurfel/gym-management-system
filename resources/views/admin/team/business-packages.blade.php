<div class="modal-header">
    <h5 class="modal-title">
        <i class="fas fa-box text-primary"></i>
        Packages for {{ $user->name }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <!-- Business Owner Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="font-weight-bold">Business Owner Information</h6>
            <table class="table table-sm table-borderless">
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
            <h6 class="font-weight-bold">Package Summary</h6>
            <div class="row">
                <div class="col-6">
                    <div class="card bg-primary text-white text-center">
                        <div class="card-body py-2">
                            <h6 class="mb-0">{{ $packages->count() }}</h6>
                            <small>Total Packages</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body py-2">
                            <h6 class="mb-0">${{ number_format($packages->sum('price'), 2) }}</h6>
                            <small>Total Value</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($packages->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Package Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $index => $package)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $package->name }}</strong>
                            @if($package->code)
                                <br><small class="text-muted">Code: {{ $package->code }}</small>
                            @endif
                        </td>
                        <td>
                            @if($package->description)
                                {{ Str::limit($package->description, 100) }}
                            @else
                                <span class="text-muted">No description</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-weight-bold text-success">${{ number_format($package->price, 2) }}</span>
                        </td>
                        <td>
                            @if($package->duration)
                                <span class="badge badge-info">{{ $package->duration }} days</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($package->status == 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif($package->status == 'inactive')
                                <span class="badge badge-danger">Inactive</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($package->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $package->created_at->format('M d, Y H:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-box fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Packages Found</h5>
            <p class="text-muted">This business owner doesn't have any packages yet.</p>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>


