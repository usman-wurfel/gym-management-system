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
      <a href="javascript:void(0)" class="text-muted">Manage Subscription Packages</a>
    </li>
  </ul>
  <!--end::Breadcrumb-->
@endsection

<!--begin::Card-->
<div class="card card-custom m-5">
  <div class="card-header">
    <div class="card-toolbar">
      <!--begin::Button-->
      <a href="{{ route('subscription-packages.create') }}" class="btn btn-primary btn-sm font-weight-bolder">
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
        </span>New Package
      </a>
      <!--end::Button-->
    </div>
  </div>
  <div class="card-body">
    <!--begin: Datatable-->
    <table class="table table-bordered table-hover table-checkable" id="subscriptionPackagesTable" style="margin-top: 13px !important">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Duration</th>
          <th>Features</th>
          <th>Status</th>
          <th>Created Date</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
    <!--end: Datatable-->
  </div>
</div>
<!--end::Card-->

<!--begin::Modal-->
<div class="modal fade" id="packageModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="packageModalLabel">Package Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="packageModalBody">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>
</div>
<!--end::Modal-->

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
<script>
$(document).ready(function() {
    var table = $('#subscriptionPackagesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('admin.getSubscriptionPackages') }}",
            "type": "POST",
            "data": {
                "_token": "{{ csrf_token() }}"
            }
        },
        "columns": [
            {"data": "id", "orderable": false, "searchable": false},
            {"data": "name"},
            {"data": "description"},
            {"data": "price"},
            {"data": "duration"},
            {"data": "features"},
            {"data": "active"},
            {"data": "created_at"},
            {"data": "action", "orderable": false, "searchable": false}
        ],
        "order": [[1, "asc"]],
        "pageLength": 25,
        "responsive": true,
        "language": {
            "processing": "Loading packages...",
            "search": "Search:",
            "lengthMenu": "Show _MENU_ packages per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ packages",
            "infoEmpty": "Showing 0 to 0 of 0 packages",
            "infoFiltered": "(filtered from _MAX_ total packages)",
            "emptyTable": "No packages found",
            "zeroRecords": "No matching packages found"
        }
    });

    // View package details
    window.viewInfo = function(id) {
        $.ajax({
            url: "{{ route('admin.getSubscriptionPackage') }}",
            type: "POST",
            data: {
                id: id,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#packageModalBody').html(response);
                $('#packageModal').modal('show');
            },
            error: function() {
                toastr.error('Error loading package details');
            }
        });
    };

    // Delete package
    window.del = function(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "{{ url('admin/subscription-packages') }}/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        toastr.success('Package deleted successfully!');
                        table.ajax.reload();
                    },
                    error: function() {
                        toastr.error('Error deleting package');
                    }
                });
            }
        });
    };

    // Delete selected packages
    $('#deleteSelected').on('click', function() {
        var selectedIds = [];
        $('input[name="packages[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Please select packages to delete');
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete them!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "{{ route('admin.delete-selected-subscription-packages') }}",
                    type: "POST",
                    data: {
                        packages: selectedIds,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        toastr.success('Packages deleted successfully!');
                        table.ajax.reload();
                    },
                    error: function() {
                        toastr.error('Error deleting packages');
                    }
                });
            }
        });
    });
});
</script>
@endsection 