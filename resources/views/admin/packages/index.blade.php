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
      <a href="javascript:void(0)" class="text-muted">Manage packages</a>
    </li>
  </ul>
  <!--end::Breadcrumb-->
@endsection


  <!--begin::Card-->
  <div class="card card-custom m-5">
    <div class="card-header">
      <div class="card-toolbar">

        <!--begin::Button-->
        @can("package-create")
        <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm font-weight-bolder">
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
        @can("package-delete")
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
        <h3 class="card-label">Manage packages</h3>
	      <form action="{{route('admin.delete-selected-packages')}}" method="post" id="delete_form">
	      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <!--begin: Datatable-->
      <table class="table table-bordered table-hover table-checkable" id="packages" style="margin-top: 13px !important">
        <thead>
        <tr>
	        <th>
		        <label class="checkbox checkbox-outline checkbox-success"><input type="checkbox"><span></span></label>

	        </th>

          <th>Name</th>
          <th>Price</th>
          <th>Duration</th>
          <th>Created At</th>
          

          <th>Actions</th>
        </tr>
        </thead>
      </table>
	      </form>
      <!--end: Datatable-->
    </div>
    </div>
	  <!-- Modal-->
	  <div class="modal fade" id="packageModel" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="document">
			  <div class="modal-content">
				  <div class="modal-header">

					  <h4 class="modal-title" id="myModalLabel">Package Detail</h4> </div>
				  <div class="modal-body"></div>
				  <div class="modal-footer">
					  <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
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
      var packages = $('#packages').DataTable( {
          "order": [
              [1, 'asc']
          ],
          "processing": true,
          "serverSide": true,
          "searchDelay": 500,
          "responsive": true,
          "ajax": {
              "url":"{{ route('admin.getPackages') }}",
              "dataType":"json",
              "type":"POST",
              "data":{"_token":"<?php echo csrf_token() ?>"}
          },
          "columns":[
              {"data":"id","searchable":false,"orderable":false},
              {"data":"name"},
              {"data":"price"},
              {"data":"duration"},
              {"data":"created_at"},
              {"data":"action","searchable":false,"orderable":false}
          ]
      } );
      function viewInfo(id) {

          var CSRF_TOKEN = '{{ csrf_token() }}';
          $.post("{{ route('admin.getPackage') }}", {_token: CSRF_TOKEN, id: id}).done(function (response) {
              $('.modal-body').html(response);
              $('#packageModel').modal('show');

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
                      "Package has been deleted.",
                      "success"
                  );
                  var APP_URL = {!! json_encode(url('/')) !!}
                  window.location.href = APP_URL+"/admin/package/delete/"+id;
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
                      "Packages have been deleted.",
                      "success"
                  );
                  $("#delete_form").submit();
              }
          });
      }

  </script>
@endsection
