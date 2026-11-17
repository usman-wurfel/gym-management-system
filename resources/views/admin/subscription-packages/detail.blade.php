<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="font-weight-bold">Package Name:</label>
            <p>{{ $package->name }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Price:</label>
            <p>{{ $package->formatted_price }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Duration:</label>
            <p>{{ $package->duration_text }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="font-weight-bold">Description:</label>
            <p>{{ $package->description }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="font-weight-bold">Features:</label>
            <div class="bg-light p-3 rounded">
                {!! nl2br(e($package->features)) !!}
            </div>
        </div>
    </div>
</div>

@if($package->image)
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="font-weight-bold">Package Image:</label>
            <div class="mt-2">
                <img src="{{ asset('uploads/packages/' . $package->image) }}" alt="Package Image" class="img-fluid rounded" style="max-width: 300px;">
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Status:</label>
            <p>
                @if($package->active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Created Date:</label>
            <p>{{ $package->created_at->format('M d, Y H:i A') }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="font-weight-bold">Last Updated:</label>
            <p>{{ $package->updated_at->format('M d, Y H:i A') }}</p>
        </div>
    </div>
</div> 