@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <a href="{{ route('workouts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Workouts
        </a>
    </div>

    <!-- Edit Workout Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Workout: {{ $workout->name }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('workouts.update', $workout->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Workout Name -->
                        <div class="form-group">
                            <label for="name" class="form-label">Workout Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $workout->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Workout Type -->
                        <div class="form-group">
                            <label for="workout_type_id" class="form-label">Workout Type</label>
                            <select class="form-control @error('workout_type_id') is-invalid @enderror" 
                                    id="workout_type_id" name="workout_type_id">
                                <option value="">Select Workout Type</option>
                                @foreach(\App\Models\WorkoutType::where('user_id', auth()->user()->user_id ?? auth()->user()->id)->get() as $type)
                                    <option value="{{ $type->id }}" {{ old('workout_type_id', $workout->workout_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('workout_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <a href="{{ route('workout-types.create') }}" target="_blank">Add new workout type</a>
                            </small>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="10">{{ old('description', $workout->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Current Image -->
                        @if($workout->image)
                        <div class="form-group">
                            <label class="form-label">Current Image</label>
                            <div class="border rounded p-2 text-center">
                                <img src="{{ $workout->image_url }}" alt="{{ $workout->name }}" 
                                     class="img-fluid" style="max-height: 200px;">
                                <p class="text-muted mt-2">{{ $workout->image }}</p>
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

                        <!-- Current Video -->
                        @if($workout->video)
                        <div class="form-group">
                            <label class="form-label">Current Video</label>
                            <div class="border rounded p-2 text-center">
                                <i class="fas fa-video fa-3x text-primary"></i>
                                <p class="text-muted mt-2">{{ $workout->video }}</p>
                                <a href="{{ $workout->video_url }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-play"></i> View Video
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Video Upload -->
                        <div class="form-group">
                            <label for="video" class="form-label">Update Video</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('video') is-invalid @enderror" 
                                       id="video" name="video" accept="video/*">
                                <label class="custom-file-label" for="video">Choose new video...</label>
                            </div>
                            <small class="form-text text-muted">
                                Supported formats: MP4, AVI, MOV, WMV. Max size: 10MB
                            </small>
                            @error('video')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Video Preview -->
                        <div class="form-group">
                            <label class="form-label">New Video Preview</label>
                            <div id="videoPreview" class="border rounded p-2 text-center" style="min-height: 150px;">
                                <i class="fas fa-video fa-3x text-muted"></i>
                                <p class="text-muted mt-2">No new video selected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Workout
                    </button>
                    <a href="{{ route('workouts.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Summernote
    $('#description').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        placeholder: 'Enter workout description...'
    });

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

    // Video preview
    $('#video').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            $('#videoPreview').html(`
                <i class="fas fa-video fa-3x text-primary"></i>
                <p class="text-muted mt-2">${file.name}</p>
                <small class="text-muted">Video will be uploaded after form submission</small>
            `);
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
