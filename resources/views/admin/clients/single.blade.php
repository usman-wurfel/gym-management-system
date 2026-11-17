@extends('admin.layouts.master')
@section('title',$title)
@section('content')
    <!--begin::Card-->
    <div class="card card-custom m-5">
        <div class="card-header">
            <div class="card-title">

                <h3 class="card-label">{{$user->name}} Shift History </h3>
                <div class="d-flex align-items-center ">

                </div>
            </div>
            <div class="card-toolbar">

            </div>
        </div>
        <div class="card-body">
            @include('admin.partials._messages')
            <div class="">
                <form action="{{route('admin.delete-selected-suppliers')}}" method="post" id="client_form">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <!--begin: Datatable-->
                    <table class="table table-bordered table-hover table-checkable" id="clients"
                           style="margin-top: 13px !important">
                        <thead>
                        <tr>


                            <th>Start Date time</th>
                            <th>Beginning Amount</th>
                            <th>End Date time</th>
                            <th>Ending Amount</th>
                            <th>Money Transfer</th>
                            <th>Money Orders Sold</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($user->shifts as $shifts)
                            <tr>
                                <td>{{$shifts->created_at}}</td>
                                <td>${{number_format($shifts->beginning_amount,2)}}</td>
                                <td>{{$shifts->updated_at}}</td>
                                <td>${{number_format($shifts->ending_amount,2)}}</td>
                                <td>${{number_format($shifts->money_transfer,2)}}</td>
                                <td>${{number_format($shifts->money_orders_sold,2)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </form>
                <!--end: Datatable-->
            </div>
        </div>
        <!-- Modal-->
        <div class="modal fade" id="clientModel" data-backdrop="static" tabindex="-1" role="dialog"
             aria-labelledby="staticBackdrop" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="myModalLabel"></h4></div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">
                            Close
                        </button>
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
          type="text/css"/>
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
        var clients = $('#clients').DataTable();

        $(".balancePopup").click(function () {
            var action = $(this).attr('data-action');
            $("#action").val(action);
            $('#clientModel').modal('show');
        })


    </script>
@endsection
