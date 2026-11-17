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
      <a href="javascript:void(0)" class="text-muted">Package Details</a>
    </li>
  </ul>
  <!--end::Breadcrumb-->
@endsection

<!--begin::Card-->
<div class="card card-custom m-5">
  <div class="card-header">
    <div class="card-title">
      <h3 class="card-label">Subscription Package Details</h3>
    </div>
    <div class="card-toolbar">
      <a href="{{ route('subscription-packages.edit', $package->id) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-edit"></i> Edit Package
      </a>
      <a href="{{ route('subscription-packages.index') }}" class="btn btn-secondary btn-sm ml-2">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-8">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Package Name:</label>
              <p class="form-control-static">{{ $package->name }}</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Price:</label>
              <p class="form-control-static text-success font-weight-bold">{{ $package->formatted_price }}</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Duration:</label>
              <p class="form-control-static">{{ $package->duration_text }}</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Description:</label>
              <div class="bg-light p-3 rounded">
                <p class="mb-0">{{ $package->description }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Features:</label>
              <div class="bg-light p-3 rounded">
                {!! nl2br(e($package->features)) !!}
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Status:</label>
              <p class="form-control-static">
                @if($package->active)
                  <span class="badge badge-success badge-pill">Active</span>
                @else
                  <span class="badge badge-danger badge-pill">Inactive</span>
                @endif
              </p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Created Date:</label>
              <p class="form-control-static">{{ $package->created_at->format('M d, Y H:i A') }}</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="font-weight-bold text-dark">Last Updated:</label>
              <p class="form-control-static">{{ $package->updated_at->format('M d, Y H:i A') }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        @if($package->image)
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Package Image</h5>
            </div>
            <div class="card-body text-center">
              <img src="{{ asset('uploads/packages/' . $package->image) }}" alt="Package Image" class="img-fluid rounded">
            </div>
          </div>
        @else
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Package Image</h5>
            </div>
            <div class="card-body text-center">
              <div class="bg-light p-4 rounded">
                <i class="fas fa-image fa-3x text-muted"></i>
                <p class="mt-2 text-muted">No image uploaded</p>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
<!--end::Card-->

@endsection 