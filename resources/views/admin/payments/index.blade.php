@extends('admin.layouts.master')
@section('title',$title)
@section('content')
@section('breadcrumb')
  <!--begin::Page Title-->
  <h5 class="text-dark font-weight-bold my-1 mr-5">Features</h5>
  <!--end::Page Title-->
  <!--begin::Breadcrumb-->
  <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
    <li class="breadcrumb-item text-muted">
      <a href="javascript:void(0)" class="text-muted">Manage payments</a>
    </li>
  </ul>
  <!--end::Breadcrumb-->
@endsection


  <!--begin::Card-->
  <div class="card card-custom m-5">
    <div class="card-header">
      <div class="card-toolbar">

        <!--begin::Button-->
        @can("payment-create")
        <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm font-weight-bolder">
											<span class="svg-icon svg-icon-md">
												<!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
												<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
													<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
														<rect x="0" y="0" width="24" height="24" />
														<circle fill="#000000" cx="9" cy="15" r="6" />
														<path d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z" fill="#000000" opacity="0.3" />
													</g>
												</svg>
                        <!--end::Svg Icon-->
											</span>New Record
        </a>
        @endcan
        <a href="{{ route('admin.pending-payments') }}" class="btn btn-warning btn-sm font-weight-bolder" style="margin-left: 5px">
											<span class="svg-icon svg-icon-md">
												<!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
												<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
													<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
														<rect x="0" y="0" width="24" height="24" />
														<circle fill="#000000" cx="9" cy="15" r="6" />
														<path d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z" fill="#000000" opacity="0.3" />
													</g>
												</svg>
                        <!--end::Svg Icon-->
											</span>Pending Payments
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-info btn-sm font-weight-bolder" style="margin-left: 5px">
            <i class="fas fa-chart-bar"></i>
            Reports
        </a>
        @can("payment-delete")
        <a href="javascript:void(0)"   onclick="del_selected()" class="btn btn-danger btn-sm font-weight-bolder" style="margin-left: 5px">
											<span class="svg-icon">
																<!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Design/Flatten.svg-->
																<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
																	<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
																		<rect x="0" y="0" width="24" height="24"></rect>
																		<circle fill="#000000" cx="9" cy="15" r="6"></circle>
																		<path d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z" fill="#000000" opacity="0.3"></path>
																	</g>
																</svg>
                        <!--end::Svg Icon-->
															</span>
          Bulk Delete
        </a>
        @endcan
        <!--end::Button-->
      </div>
    </div>
    <div class="card-body">
	    @include('admin.partials._messages')
      <div class="">
        <h3 class="card-label">Manage payments</h3>
        
        <!-- Date Filter Section -->
        <div class="row mb-4">
          <div class="col-md-12">
            <div class="card card-custom card-stretch">
              <div class="card-header">
                <div class="card-title">
                  <h3 class="card-label">
                    <i class="fas fa-filter text-primary mr-2"></i>
                    Filter by Date
                  </h3>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <label class="form-label">Month</label>
                    <select class="form-control" id="monthFilter">
                      <option value="">All Months</option>
                      <option value="01">January</option>
                      <option value="02">February</option>
                      <option value="03">March</option>
                      <option value="04">April</option>
                      <option value="05">May</option>
                      <option value="06">June</option>
                      <option value="07">July</option>
                      <option value="08">August</option>
                      <option value="09">September</option>
                      <option value="10">October</option>
                      <option value="11">November</option>
                      <option value="12">December</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <select class="form-control" id="yearFilter">
                      <option value="">All Years</option>
                      @for($year = date('Y'); $year >= 2020; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                      @endfor
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                      <button type="button" class="btn btn-primary" onclick="applyDateFilter()">
                        <i class="fas fa-search mr-1"></i> Apply Filter
                      </button>
                      <button type="button" class="btn btn-secondary ml-2" onclick="clearDateFilter()">
                        <i class="fas fa-times mr-1"></i> Clear
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
	      <form action="{{route('admin.delete-selected-payments')}}" method="post" id="delete_form">
	      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <!--begin: Datatable-->
      <div class="table-responsive p-5" >
      <table class="table table-bordered table-hover table-checkable" id="payments" style="margin-top: 13px !important">
        <thead>
        <tr>
	        <th>
		        <label class="checkbox checkbox-outline checkbox-success"><input type="checkbox"><span></span></label>

	        </th>

          <th>Member</th>
          <th>Package</th>
          <th>Amount</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Payment Method</th>
          <th>Status</th>
          <th>Created At</th>
          

          <th>Actions</th>
        </tr>
        </thead>
      </table>
      </div>
	      </form>
      <!--end: Datatable-->
    </div>
    </div>
	  <!-- Modal-->
	  <div class="modal fade" id="paymentModel" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="document">
			  <div class="modal-content">
				  <div class="modal-header">

					  <h4 class="modal-title" id="myModalLabel">Payment Detail</h4> </div>
				  <div class="modal-body"></div>
				  <div class="modal-footer">
					  <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
				  </div>
			  </div>
		  </div>
	  </div>

	  <!-- Status Update Modal-->
	  <div class="modal fade" id="statusUpdateModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="document">
			  <div class="modal-content">
				  <div class="modal-header">
					  <h4 class="modal-title" id="statusUpdateModalLabel">Update Payment Status</h4>
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span>
					  </button>
				  </div>
				  <div class="modal-body">
					  <form id="statusUpdateForm">
						  <input type="hidden" name="_token" value="{{ csrf_token() }}">
						  <input type="hidden" name="payment_id" id="payment_id">
						  
						  <div class="form-group">
							  <label for="payment_status" class="form-label">Payment Status</label>
							  <select class="form-control" id="payment_status" name="payment_status" required>
								  <option value="">Select Status</option>
								  <option value="paid">Paid</option>
								  <option value="pending">Pending</option>
								  <option value="failed">Failed</option>
							  </select>
						  </div>
						  
						  <div class="form-group">
							  <label for="status_note" class="form-label">Note (Optional)</label>
							  <textarea class="form-control" id="status_note" name="status_note" rows="3" placeholder="Add any notes about this status change..."></textarea>
						  </div>
					  </form>
				  </div>
				  <div class="modal-footer">
					  <button type="button" class="btn btn-light-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
					  <button type="button" class="btn btn-primary font-weight-bold" onclick="submitStatusUpdate()">Update Status</button>
				  </div>
			  </div>
		  </div>
	  </div>
  </div>
  <!--end::Card-->
@endsection
@section('stylesheets')
  <!--begin::Page Vendors Styles(used by this page)-->
  <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
  <!--end::Page Vendors Styles-->
@endsection
@section('scripts')
  <!--begin::Page Vendors(used by this page)-->
  <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
  <!--end::Page Vendors-->
  <script>

      $(document).on('click', 'th input:checkbox', function () {

          var that = this;
          $(this).closest('table').find('tr > td:first-child input:checkbox')
              .each(function () {
                  this.checked = that.checked;
                  $(this).closest('tr').toggleClass('selected');
              });
      });
      var payments = $('#payments').DataTable( {
          "order": [
              [1, 'asc']
          ],
          "processing": true,
          "serverSide": true,
          "searchDelay": 500,
          "responsive": true,
          "ajax": {
              "url":"{{ route('admin.getPayments') }}",
              "dataType":"json",
              "type":"POST",
              "data": function(d) {
                  d._token = "{{ csrf_token() }}";
                  d.month = $('#monthFilter').val();
                  d.year = $('#yearFilter').val();
              }
          },
          "columns":[
              {"data":"id","searchable":false,"orderable":false},
              {"data":"member_id"},
              {"data":"package_id"},
              {"data":"amount"},
              {"data":"start_date"},
              {"data":"end_date"},
              {"data":"payment_method"},
              {"data":"payment_status"},
              {"data":"created_at"},
              {"data":"action","searchable":false,"orderable":false}
          ]
      } );
      $(document).ready(function() {
          setTimeout(function() {
              payments.ajax.reload();
          }, 500);
      });
      function viewInfo(id) {

          var CSRF_TOKEN = '{{ csrf_token() }}';
          $.post("{{ route('admin.getPayment') }}", {_token: CSRF_TOKEN, id: id}).done(function (response) {
              $('.modal-body').html(response);
              $('#paymentModel').modal('show');

          });
      }
      function del(id){
          Swal.fire({
              title: "Are you sure?",
              text: "You won't be able to revert this!",
          icon: "warning",
              showCancelButton: true,
              confirmButtonText: "Yes, delete it!"
      }).then(function(result) {
              if (result.value) {
                  Swal.fire(
                      "Deleted!",
                      "Payment has been deleted.",
                      "success"
                  );
                  var APP_URL = {!! json_encode(url('/')) !!}
                  window.location.href = APP_URL+"/admin/payment/delete/"+id;
              }
          });
      }
      function del_selected(){
          Swal.fire({
              title: "Are you sure?",
              text: "You won't be able to revert this!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonText: "Yes, delete it!"
          }).then(function(result) {
              if (result.value) {
                  Swal.fire(
                      "Deleted!",
                      "Payments have been deleted.",
                      "success"
                  );
                  $("#delete_form").submit();
              }
          });
      }

      function printInvoice(id) {
          Swal.fire({
              title: "Print Invoice",
              text: "Do you want to download the invoice for this payment?",
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "Yes, Download",
              cancelButtonText: "Cancel"
          }).then(function(result) {
              if (result.value) {
                  window.open('/admin/payment/invoice/' + id, '_blank');
              }
          });
      }

      function updateStatus(paymentId, currentStatus) {
          // Set the payment ID
          $('#payment_id').val(paymentId);
          
          // Set the current status as selected
          $('#payment_status').val(currentStatus);
          
          // Clear the note field
          $('#status_note').val('');
          
          // Show the modal
          $('#statusUpdateModal').modal('show');
      }

      function submitStatusUpdate() {
          var paymentId = $('#payment_id').val();
          var newStatus = $('#payment_status').val();
          var note = $('#status_note').val();
          
          if (!newStatus) {
              Swal.fire({
                  title: "Error",
                  text: "Please select a status",
                  icon: "error"
              });
              return;
          }
          
          Swal.fire({
              title: "Update Status",
              text: "Are you sure you want to update the payment status?",
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "Yes, Update",
              cancelButtonText: "Cancel"
          }).then(function(result) {
              if (result.value) {
                  // Show loading
                  Swal.fire({
                      title: "Updating...",
                      text: "Please wait",
                      icon: "info",
                      allowOutsideClick: false,
                      showConfirmButton: false,
                      willOpen: () => {
                          Swal.showLoading();
                      }
                  });
                  
                  // Make AJAX request
                  $.ajax({
                      url: "{{ route('admin.updatePaymentStatus') }}",
                      type: "POST",
                      data: {
                          _token: "{{ csrf_token() }}",
                          payment_id: paymentId,
                          payment_status: newStatus,
                          note: note
                      },
                      success: function(response) {
                          Swal.fire({
                              title: "Success",
                              text: "Payment status updated successfully!",
                              icon: "success"
                          }).then(function() {
                              // Close modal
                              $('#statusUpdateModal').modal('hide');
                              // Reload DataTable
                              payments.ajax.reload();
                          });
                      },
                      error: function(xhr) {
                          var errorMessage = "An error occurred while updating the status.";
                          if (xhr.responseJSON && xhr.responseJSON.message) {
                              errorMessage = xhr.responseJSON.message;
                          }
                          Swal.fire({
                              title: "Error",
                              text: errorMessage,
                              icon: "error"
                          });
                      }
                  });
              }
          });
      }

      // Date filter functions
      function applyDateFilter() {
          payments.ajax.reload();
      }

      function clearDateFilter() {
          $('#monthFilter').val('');
          $('#yearFilter').val('');
          payments.ajax.reload();
      }

      // Initialize filters without default values
      $(document).ready(function() {
          // Don't set default values to avoid filtering issues
          // Users can manually select month/year if needed
      });

  </script>
@endsection 