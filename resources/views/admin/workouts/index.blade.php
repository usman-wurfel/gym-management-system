@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <a href="{{ route('workouts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Workout
        </a>
    </div>

    <!-- Workouts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Workouts List</h6>
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

            @if($workouts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Video</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workouts as $workout)
                                <tr>
                                    <td>
                                        @if($workout->image)
                                            <img src="{{ $workout->image_url }}" alt="{{ $workout->name }}" 
                                                 class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $workout->name }}</strong>
                                    </td>
                                    <td>
                                        @if($workout->workoutType)
                                            <span class="badge badge-primary">{{ $workout->workoutType->name }}</span>
                                        @else
                                            <span class="text-muted">No Type</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $workout->description_preview }}">
                                            {!! $workout->short_description !!}
                                        </div>
                                    </td>
                                    <td>
                                        @if($workout->video)
                                            <a href="{{ $workout->video_url }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-play"></i> View Video
                                            </a>
                                        @else
                                            <span class="text-muted">No Video</span>
                                        @endif
                                    </td>
                                    <td>{{ $workout->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('workouts.show', $workout->id) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('workouts.edit', $workout->id) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('workouts.destroy', $workout->id) }}" 
                                                  method="POST" style="display: inline-block;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this workout?')">
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
                    {{ $workouts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No workouts found</h5>
                    <p class="text-muted">Start by adding your first workout.</p>
                    <a href="{{ route('workouts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Workout
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
