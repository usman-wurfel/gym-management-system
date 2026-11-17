@extends('admin.layouts.master')
@section('title', $title)
@section('content')
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">
                            <i class="fas fa-upload text-primary mr-2"></i>
                            Import Members
                        </h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('members.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Members
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <!--begin::Import Form-->
                            <div class="card card-custom bg-light-primary">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">
                                        <i class="fas fa-file-upload text-primary mr-2"></i>
                                        Upload File
                                    </h5>
                                    
                                    @if(session('error_message'))
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ session('error_message') }}
                                        </div>
                                    @endif

                                    <form action="{{ route('members.import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                        @csrf
                                        <div class="form-group">
                                            <label for="file" class="form-label">Select File</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                                <label class="custom-file-label" for="file">Choose file...</label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Supported formats: Excel (.xlsx, .xls) and CSV (.csv). Maximum file size: 2MB
                                            </small>
                                            @error('file')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-lg" id="importBtn">
                                                <i class="fas fa-upload mr-2"></i>
                                                Import Members
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="resetForm()">
                                                <i class="fas fa-undo mr-2"></i>
                                                Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!--end::Import Form-->
                        </div>

                        <div class="col-lg-4">
                            <!--begin::Sample Download-->
                            <div class="card card-custom bg-light-success">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">
                                        <i class="fas fa-download text-success mr-2"></i>
                                        Sample File
                                    </h5>
                                    <p class="text-muted mb-3">
                                        Download the sample file to understand the required format and structure for importing members.
                                    </p>
                                    <a href="{{ route('members.download-sample') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Sample CSV
                                    </a>
                                </div>
                            </div>
                            <!--end::Sample Download-->

                            <!--begin::Instructions-->
                            <div class="card card-custom bg-light-info mt-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">
                                        <i class="fas fa-info-circle text-info mr-2"></i>
                                        Instructions
                                    </h5>
                                    <div class="text-muted">
                                        <h6 class="font-weight-bold">Required Fields:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success mr-2"></i>Name (required)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>Phone (required)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>Address (required)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>City (required)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>Code (required, unique)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>Package Name (must exist)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>Start Date (YYYY-MM-DD)</li>
                                            <li><i class="fas fa-check text-success mr-2"></i>Status (1=Active, 0=Inactive)</li>
                                        </ul>
                                        
                                        <h6 class="font-weight-bold mt-3">Optional Fields:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-minus text-warning mr-2"></i>Email (if provided, must be valid format)</li>
                                        </ul>

                                        <h6 class="font-weight-bold mt-3">Important Notes:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Member codes must be unique</li>
                                            <li><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Package names must match existing packages</li>
                                            <li><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Dates must be in YYYY-MM-DD format</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!--end::Instructions-->
                        </div>
                    </div>

                    <!--begin::Error Display-->
                    @if(session('import_errors'))
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-custom bg-light-danger">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                                            Import Errors
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-danger">
                                            <strong>Some rows could not be imported due to the following errors:</strong>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Error</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(session('import_errors') as $error)
                                                        <tr>
                                                            <td>{{ $error }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!--end::Error Display-->
                </div>
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // File input change handler
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        // Form submission handler
        $('#importForm').on('submit', function() {
            var fileInput = $('#file')[0];
            if (fileInput.files.length === 0) {
                alert('Please select a file to import.');
                return false;
            }

            var file = fileInput.files[0];
            var maxSize = 2 * 1024 * 1024; // 2MB

            if (file.size > maxSize) {
                alert('File size must be less than 2MB.');
                return false;
            }

            // Show loading state
            $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Importing...');
        });
    });

    function resetForm() {
        $('#importForm')[0].reset();
        $('.custom-file-label').html('Choose file...');
        $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload mr-2"></i>Import Members');
    }
</script>
@endsection
