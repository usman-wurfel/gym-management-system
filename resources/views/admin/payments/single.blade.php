@extends('admin.layouts.master')
@section('title',$title)
@section('content')
  <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader" kt-hidden-height="54" style="">
      <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-1">
          <!--begin::Page Heading-->
          <div class="d-flex align-items-baseline flex-wrap mr-5">
            <!--begin::Page Title-->
            <h5 class="text-dark font-weight-bold my-1 mr-5">Features</h5>
            <!--end::Page Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
              <li class="breadcrumb-item text-muted">
                <a href="{{ route('payments.index') }}" class="text-muted">Manage payments</a>
              </li>
              <li class="breadcrumb-item text-muted">
                <a href="javascript:void(0)" class="text-muted">Payment #{{ $payment->id }}</a>
              </li>
            </ul>
            <!--end::Breadcrumb-->
          </div>
          <!--end::Page Heading-->
        </div>
        <!--end::Info-->
      </div>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
      <!--begin::Container-->
      <div class="container">
        <!--begin::Card-->
        <div class="card card-custom">
          <div class="card-header">
            <div class="card-title">
              <h3 class="card-label">Payment Details</h3>
            </div>
            <div class="card-toolbar">
              <a href="{{ route('payments.index') }}" class="btn btn-light-primary font-weight-bolder mr-2">
                <i class="ki ki-long-arrow-back icon-sm"></i>Back
              </a>
              @can("payment-edit")
              <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-primary font-weight-bolder">
                <i class="ki ki-pencil icon-sm"></i>Edit
              </a>
              @endcan
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>Member:</strong></td>
                    <td>{{ $payment->member ? $payment->member->name : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Package:</strong></td>
                    <td>{{ $payment->package ? $payment->package->name : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Amount:</strong></td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                  </tr>
                  <tr>
                    <td><strong>Start Date:</strong></td>
                    <td>{{ $payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A' }}</td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>End Date:</strong></td>
                    <td>{{ $payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                  </tr>
                  <tr>
                    <td><strong>Payment Status:</strong></td>
                    <td>
                      @if($payment->payment_status == 'paid')
                        <span class="badge badge-success">Paid</span>
                      @elseif($payment->payment_status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                      @else
                        <span class="badge badge-danger">Failed</span>
                      @endif
                    </td>
                  </tr>
                  @if($payment->note)
                  <tr>
                    <td><strong>Note:</strong></td>
                    <td>{{ $payment->note }}</td>
                  </tr>
                  @endif
                </table>
              </div>
            </div>
          </div>
        </div>
        <!--end::Card-->
      </div>
      <!--end::Container-->
    </div>
    <!--end::Entry-->
  </div>
@endsection 