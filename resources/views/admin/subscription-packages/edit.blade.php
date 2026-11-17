@extends('admin.layouts.master')
@section('title', $title)
@section('content')
@section('breadcrumb')
  <!--begin::Page Title-->
  <h5 class="text-dark font-weight-bold my-1 mr-5">Features</h5>
  <!--end::Page Title-->
  <!--begin::Breadcrumb-->
  <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
    <li class="breadcrumb-item text-muted">
      <a href="{{ route('subscription-packages.index') }}" class="text-muted">Subscription Packages</a>
    </li>
    <li class="breadcrumb-item text-muted">
      <a href="javascript:void(0)" class="text-muted">Edit Package</a>
    </li>
  </ul>
  <!--end::Breadcrumb-->
@endsection

<!--begin::Card-->
<div class="card card-custom m-5">
  <div class="card-header">
    <div class="card-title">
      <h3 class="card-label">Edit Subscription Package</h3>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('subscription-packages.update', $package->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="name">Package Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $package->name) }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="price">Price ($) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $package->price) }}" required>
            @error('price')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="duration">Duration (Days) <span class="text-danger">*</span></label>
            <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration', $package->duration) }}" required>
            @error('duration')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="image">Package Image</label>
            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
            @error('image')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
            @if($package->image)
              <div class="mt-2">
                <img src="{{ asset('uploads/packages/' . $package->image) }}" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                <small class="form-text text-muted">Current image</small>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="description">Description <span class="text-danger">*</span></label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $package->description) }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label for="features">Features <span class="text-danger">*</span></label>
        <textarea class="form-control @error('features') is-invalid @enderror" id="features" name="features" rows="5" required>{{ old('features', $package->features) }}</textarea>
        @error('features')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Enter each feature on a new line or separate with commas</small>
      </div>

      <div class="form-group">
        <div class="checkbox-inline">
          <label class="checkbox">
            <input type="checkbox" name="active" value="1" {{ old('active', $package->active) ? 'checked' : '' }}>
            <span></span>Active Package
          </label>
        </div>
      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-primary mr-2">Update Package</button>
        <a href="{{ route('subscription-packages.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
<!--end::Card-->

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Preview image before upload
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if ($('#imagePreview').length === 0) {
                    $('#image').after('<div class="mt-2"><img id="imagePreview" src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;"></div>');
                } else {
                    $('#imagePreview').attr('src', e.target.result);
                }
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection 