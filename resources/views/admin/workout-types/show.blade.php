@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <div>
            <a href="{{ route('workout-types.edit', $workoutType->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Workout Type
            </a>
            <a href="{{ route('workout-types.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Workout Types
            </a>
        </div>
    </div>

    <!-- Workout Type Details -->
    <div class="row">
        <div class="col-md-8">
            <!-- Workout Type Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Workout Type Information</h6>
                </div>
                <div class="card-body">
                    <h4 class="text-primary mb-3">{{ $workoutType->name }}</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Created:</strong> {{ $workoutType->created_at->format('M d, Y H:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Updated:</strong> {{ $workoutType->updated_at->format('M d, Y H:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Associated Workouts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Associated Workouts ({{ $workoutType->workouts->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($workoutType->workouts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Workout Name</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workoutType->workouts as $workout)
                                        <tr>
                                            <td>
                                                <strong>{{ $workout->name }}</strong>
                                            </td>
                                            <td>{{ $workout->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('workouts.show', $workout->id) }}" 
                                                   class="btn btn-sm btn-info" title="View Workout">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-dumbbell fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No workouts associated with this type yet.</p>
                            <a href="{{ route('workouts.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Workout
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Workout Type Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-dumbbell fa-3x text-primary"></i>
                        </div>
                        <h3 class="text-primary">{{ $workoutType->workouts->count() }}</h3>
                        <p class="text-muted">Total Workouts</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('workout-types.edit', $workoutType->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Workout Type
                        </a>
                        <a href="{{ route('workouts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Workout
                        </a>
                        @if($workoutType->workouts->count() == 0)
                            <form action="{{ route('workout-types.destroy', $workoutType->id) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this workout type?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Delete Workout Type
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger" disabled title="Cannot delete - has associated workouts">
                                <i class="fas fa-trash"></i> Delete Workout Type
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
