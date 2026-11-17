@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <div>
            <a href="{{ route('workouts.edit', $workout->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Workout
            </a>
            <a href="{{ route('workouts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Workouts
            </a>
        </div>
    </div>

    <!-- Workout Details -->
    <div class="row">
        <div class="col-md-8">
            <!-- Workout Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Workout Information</h6>
                </div>
                <div class="card-body">
                    <h4 class="text-primary mb-3">{{ $workout->name }}</h4>
                    
                    @if($workout->workoutType)
                        <p class="mb-3">
                            <strong>Type:</strong> 
                            <span class="badge badge-primary">{{ $workout->workoutType->name }}</span>
                        </p>
                    @endif
                    
                    @if($workout->description)
                        <div class="workout-description">
                            <h6 class="font-weight-bold">Description:</h6>
                            <div class="border rounded p-3 bg-light">
                                {!! $workout->description !!}
                            </div>
                        </div>
                    @else
                        <p class="text-muted">No description provided.</p>
                    @endif
                </div>
            </div>

            <!-- Video Section -->
            @if($workout->video)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Workout Video</h6>
                </div>
                <div class="card-body">
                    <div class="embed-responsive embed-responsive-16by9">
                        <video controls class="embed-responsive-item">
                            <source src="{{ $workout->video_url }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="mt-3">
                        <a href="{{ $workout->video_url }}" target="_blank" class="btn btn-info">
                            <i class="fas fa-external-link-alt"></i> Open Video in New Tab
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Workout Image -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Workout Image</h6>
                </div>
                <div class="card-body text-center">
                    @if($workout->image)
                        <img src="{{ $workout->image_url }}" alt="{{ $workout->name }}" 
                             class="img-fluid rounded shadow">
                        <p class="text-muted mt-2">{{ $workout->image }}</p>
                    @else
                        <div class="bg-light border rounded p-5">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p class="text-muted mt-2">No image available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Workout Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Workout Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $workout->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $workout->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td>{{ $workout->updated_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Has Image:</strong></td>
                            <td>
                                @if($workout->image)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Has Video:</strong></td>
                            <td>
                                @if($workout->video)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('workouts.edit', $workout->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Workout
                        </a>
                        <form action="{{ route('workouts.destroy', $workout->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this workout?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Delete Workout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.workout-description {
    line-height: 1.6;
}

.workout-description h1,
.workout-description h2,
.workout-description h3,
.workout-description h4,
.workout-description h5,
.workout-description h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.workout-description p {
    margin-bottom: 1rem;
}

.workout-description ul,
.workout-description ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.workout-description img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush
