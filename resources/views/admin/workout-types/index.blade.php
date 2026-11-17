@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <a href="{{ route('workout-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Workout Type
        </a>
    </div>

    <!-- Workout Types Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Workout Types List</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($workoutTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Workouts Count</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workoutTypes as $workoutType)
                                <tr>
                                    <td>
                                        @if($workoutType->image)
                                            <img src="{{ $workoutType->image_url }}" alt="{{ $workoutType->name }}" 
                                                 class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $workoutType->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $workoutType->workouts_count }} workouts</span>
                                    </td>
                                    <td>{{ $workoutType->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('workout-types.show', $workoutType->id) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('workout-types.edit', $workoutType->id) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('workout-types.destroy', $workoutType->id) }}" 
                                                  method="POST" style="display: inline-block;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this workout type?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $workoutTypes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No workout types found</h5>
                    <p class="text-muted">Start by adding your first workout type.</p>
                    <a href="{{ route('workout-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Workout Type
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
    <!--begin::Page Vendors Styles(used by this page)-->
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
          type="text/css"/>
    <!--end::Page Vendors Styles-->
@endsection

@section('scripts')
    <!--begin::Page Vendors(used by this page)-->
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true
        });
    });
    </script>
@endsection
