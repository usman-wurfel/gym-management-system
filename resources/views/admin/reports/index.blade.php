@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Payment Reports</h3>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <div class="row mb-6">
                    <div class="col-lg-12">
                        <form id="reportForm" class="form-inline">
                            <div class="form-group mr-3">
                                <label for="from_date" class="mr-2">From Date:</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" required>
                            </div>
                            <div class="form-group mr-3">
                                <label for="to_date" class="mr-2">To Date:</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" required>
                            </div>
                            <div class="form-group mr-3">
                                <label for="payment_status" class="mr-2">Payment Status:</label>
                                <select class="form-control" id="payment_status" name="payment_status" required>
                                    <option value="all">All</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="form-group mt-2">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>
                                <button type="button" id="downloadPdf" class="btn btn-success" style="display: none;">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                                <button type="button" id="downloadExcel" class="btn btn-info ml-2" style="display: none;">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-6" id="summaryCards" style="display: none;">
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-custom bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0" id="totalAmount">$0</h4>
                                        <p class="mb-0">Total Amount</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-custom bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0" id="paidAmount">$0</h4>
                                        <p class="mb-0">Paid Amount</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-custom bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0" id="pendingAmount">$0</h4>
                                        <p class="mb-0">Pending Amount</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-custom bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0" id="failedAmount">$0</h4>
                                        <p class="mb-0">Failed Amount</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="row" id="resultsSection" style="display: none;">
                    <div class="col-lg-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-label">Payment Details</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="paymentsTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Member Name</th>
                                                <th>Package</th>
                                                <th>Amount</th>
                                                <th>Payment Method</th>
                                                <th>Status</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Created Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="paymentsTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Results Message -->
                <div class="row" id="noResults" style="display: none;">
                    <div class="col-lg-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h4>No payments found for the selected criteria</h4>
                            <p>Try adjusting your date range or payment status filter.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Generating report...</p>
                <button type="button" class="btn btn-secondary mt-3" onclick="$('#loadingModal').modal('hide')">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Set default dates (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#from_date').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#to_date').val(today.toISOString().split('T')[0]);

    // Form submission
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });

    // Download PDF button
    $('#downloadPdf').on('click', function() {
        downloadPDF();
    });

    // Download Excel button
    $('#downloadExcel').on('click', function() {
        downloadExcel();
    });

    function generateReport() {
        const formData = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            payment_status: $('#payment_status').val()
        };

        // Show loading modal
        $('#loadingModal').modal('show');

        $.ajax({
            url: '{{ route("admin.generate-report") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Hide loading modal with timeout to ensure it's hidden
                setTimeout(function() {
                    $('#loadingModal').modal('hide');
                }, 100);
                
                if (response.success) {
                    displayResults(response.data);
                } else {
                    toastr.error('Error generating report');
                }
            },
            error: function(xhr) {
                // Hide loading modal with timeout to ensure it's hidden
                setTimeout(function() {
                    $('#loadingModal').modal('hide');
                }, 100);
                toastr.error('Error generating report');
            },
            complete: function() {
                // Additional safety to hide modal
                setTimeout(function() {
                    $('#loadingModal').modal('hide');
                }, 500);
            }
        });
    }

    function displayResults(data) {
        // Update summary cards
        $('#totalAmount').text('$' + parseFloat(data.summary.total_amount).toFixed(2));
        $('#paidAmount').text('$' + parseFloat(data.summary.paid_amount).toFixed(2));
        $('#pendingAmount').text('$' + parseFloat(data.summary.pending_amount).toFixed(2));
        $('#failedAmount').text('$' + parseFloat(data.summary.failed_amount).toFixed(2));

        // Show/hide sections
        if (data.payments.length > 0) {
            $('#summaryCards').show();
            $('#resultsSection').show();
            $('#noResults').hide();
            $('#downloadPdf').show();
            $('#downloadExcel').show();
            
            // Populate table
            populateTable(data.payments);
        } else {
            $('#summaryCards').hide();
            $('#resultsSection').hide();
            $('#noResults').show();
            $('#downloadPdf').hide();
            $('#downloadExcel').hide();
        }
    }

    function populateTable(payments) {
        const tbody = $('#paymentsTableBody');
        tbody.empty();

        payments.forEach((payment, index) => {
            const statusClass = getStatusClass(payment.payment_status);
            const statusText = payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1);
            
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${payment.member ? payment.member.name : 'N/A'}</td>
                    <td>${payment.package ? payment.package.name : 'N/A'}</td>
                    <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                    <td>${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1)}</td>
                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                    <td>${formatDate(payment.start_date)}</td>
                    <td>${formatDate(payment.end_date)}</td>
                    <td>${formatDate(payment.created_at)}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function getStatusClass(status) {
        switch(status) {
            case 'paid': return 'badge-success';
            case 'pending': return 'badge-warning';
            case 'failed': return 'badge-danger';
            default: return 'badge-secondary';
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }

    function downloadPDF() {
        const formData = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            payment_status: $('#payment_status').val()
        };

        // Create a temporary form to submit
        const form = $('<form>', {
            'method': 'POST',
            'action': '{{ route("admin.download-report-pdf") }}'
        });

        // Add CSRF token
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        }));

        // Add form data
        Object.keys(formData).forEach(key => {
            form.append($('<input>', {
                'type': 'hidden',
                'name': key,
                'value': formData[key]
            }));
        });

        // Submit form
        $('body').append(form);
        form.submit();
        form.remove();
    }

    function downloadExcel() {
        const formData = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            payment_status: $('#payment_status').val()
        };

        // Create a temporary form to submit
        const form = $('<form>', {
            'method': 'POST',
            'action': '{{ route("admin.download-report-excel") }}'
        });

        // Add CSRF token
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        }));

        // Add form data
        Object.keys(formData).forEach(key => {
            form.append($('<input>', {
                'type': 'hidden',
                'name': key,
                'value': formData[key]
            }));
        });

        // Submit form
        $('body').append(form);
        form.submit();
        form.remove();
    }
});
</script>
@endsection 