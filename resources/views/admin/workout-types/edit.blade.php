@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <a href="{{ route('workout-types.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Workout Types
        </a>
    </div>

    <!-- Edit Workout Type Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Workout Type: {{ $workoutType->name }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('workout-types.update', $workoutType->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Workout Type Name -->
                        <div class="form-group">
                            <label for="name" class="form-label">Workout Type Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $workoutType->name) }}" 
                                   placeholder="e.g., Chest, Back, Legs, Arms" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Enter a descriptive name for the workout type (e.g., Chest, Back, Legs, Arms, Shoulders, etc.)
                            </small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Current Image -->
                        @if($workoutType->image)
                        <div class="form-group">
                            <label class="form-label">Current Image</label>
                            <div class="border rounded p-2 text-center">
                                <img src="{{ $workoutType->image_url }}" alt="{{ $workoutType->name }}" 
                                     class="img-fluid" style="max-height: 200px;">
                                <p class="text-muted mt-2">{{ $workoutType->image }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="form-group">
                            <label for="image" class="form-label">Update Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Choose new image...</label>
                            </div>
                            <small class="form-text text-muted">
                                Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB
                            </small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div class="form-group">
                            <label class="form-label">New Image Preview</label>
                            <div id="imagePreview" class="border rounded p-2 text-center" style="min-height: 150px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                                <p class="text-muted mt-2">No new image selected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Workout Type
                    </button>
                    <a href="{{ route('workout-types.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Image preview
    $('#image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').html(`
                    <img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">
                    <p class="text-muted mt-2">${file.name}</p>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    // Custom file input labels
    $('.custom-file-input').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
@endsection
