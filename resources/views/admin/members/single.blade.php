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
                <a href="{{ route('members.index') }}" class="text-muted">Manage members</a>
              </li>
              <li class="breadcrumb-item text-muted">
                <a href="javascript:void(0)" class="text-muted">{{ $member->name }}</a>
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
              <h3 class="card-label">Member Details</h3>
            </div>
            <div class="card-toolbar">
              <a href="{{ route('members.index') }}" class="btn btn-light-primary font-weight-bolder mr-2">
                <i class="ki ki-long-arrow-back icon-sm"></i>Back
              </a>
              @can("member-edit")
              <a href="{{ route('members.edit', $member->id) }}" class="btn btn-primary font-weight-bolder">
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
                    <td><strong>Name:</strong></td>
                    <td>{{ $member->name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $member->email }}</td>
                  </tr>
                  <tr>
                    <td><strong>Phone:</strong></td>
                    <td>{{ $member->phone }}</td>
                  </tr>
                  <tr>
                    <td><strong>Address:</strong></td>
                    <td>{{ $member->address }}</td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>City:</strong></td>
                    <td>{{ $member->city }}</td>
                  </tr>
                  <tr>
                    <td><strong>Package:</strong></td>
                    <td>{{ $member->package ? $member->package->name : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Start Date:</strong></td>
                    <td>{{ $member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                      @if($member->status == 1)
                        <span class="badge badge-success">Active</span>
                      @else
                        <span class="badge badge-danger">Inactive</span>
                      @endif
                    </td>
                  </tr>
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