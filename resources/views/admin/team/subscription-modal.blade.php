<div class="modal-header">
    <h5 class="modal-title">Subscribe Business Owner</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form id="subscriptionForm">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="font-weight-bold">Business Owner:</label>
                    <p class="form-control-static">{{ $user->name }} ({{ $user->email }})</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="package_id">Subscription Package <span class="text-danger">*</span></label>
                    <select class="form-control" id="package_id" name="package_id" required>
                        <option value="">Select Package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" data-price="{{ $package->price }}" data-duration="{{ $package->duration }}">
                                {{ $package->name }} - ${{ number_format($package->price, 2) }} ({{ $package->duration }} days)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Package Price:</label>
                    <p class="form-control-static text-success font-weight-bold" id="packagePrice">$0.00</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>End Date:</label>
                    <p class="form-control-static" id="endDate">-</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Package Features:</label>
                    <div class="bg-light p-3 rounded" id="packageFeatures">
                        <p class="text-muted mb-0">Select a package to view features</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="saveSubscription()">Save Subscription</button>
</div>

<script>
$(document).ready(function() {
    // Update package details when package is selected
    $('#package_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var price = selectedOption.data('price') || 0;
        var duration = selectedOption.data('duration') || 0;
        
        $('#packagePrice').text('$' + parseFloat(price).toFixed(2));
        
        // Calculate end date
        var startDate = $('#start_date').val();
        if (startDate && duration > 0) {
            var endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + parseInt(duration));
            $('#endDate').text(endDate.toLocaleDateString());
        }
        
        // Load package features via AJAX
        var packageId = $(this).val();
        if (packageId) {
            $.ajax({
                url: '{{ route("admin.getSubscriptionPackage") }}',
                type: 'POST',
                data: {
                    id: packageId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#packageFeatures').html(response);
                },
                error: function() {
                    $('#packageFeatures').html('<p class="text-danger">Error loading features</p>');
                }
            });
        } else {
            $('#packageFeatures').html('<p class="text-muted mb-0">Select a package to view features</p>');
            $('#endDate').text('-');
        }
    });
    
    // Update end date when start date changes
    $('#start_date').change(function() {
        $('#package_id').trigger('change');
    });
});

function saveSubscription() {
    var formData = $('#subscriptionForm').serialize();
    
    $.ajax({
        url: '{{ route("admin.store-subscription") }}',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#subscriptionModal').modal('hide');
                // Reload the team table
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                }
            } else {
                toastr.error('Error creating subscription');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    toastr.error(value[0]);
                });
            } else {
                toastr.error('Error creating subscription');
            }
        }
    });
}
</script> 