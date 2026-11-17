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
                <a href="javascript:void(0)" class="text-muted">Edit member</a>
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
        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
          <div class="card-header" style="">
            <div class="card-title">
              <h3 class="card-label">Member Edit Form
                <i class="mr-2"></i>
                <small class="">try to scroll the page</small></h3>

            </div>
            <div class="card-toolbar">

              <a href="{{ route('members.index') }}" class="btn btn-light-primary
              font-weight-bolder mr-2">
                <i class="ki ki-long-arrow-back icon-sm"></i>Back</a>

              <div class="btn-group">
                @can("member-edit")
                <a href=""  onclick="event.preventDefault(); document.getElementById('base_form').submit();" id="kt_btn" class="btn btn-primary font-weight-bolder">
                  <i class="ki ki-check icon-sm"></i>Update</a>
                @endcan



              </div>
            </div>
          </div>
          <div class="card-body">
          @include('admin.partials._messages')
          <!--begin::Form-->
            {{ Form::model($member, [ 'method' => 'PATCH','route' => ['members.update', $member->id],'class'=>'form' ,"id"=>"base_form", 'enctype'=>'multipart/form-data'])}}
              @csrf
              <div class="row">
                <div class="col-xl-2 "></div>
                <div class="col-xl-8">
                  <div class="my-5">

                    <div class="form-group row {{ $errors->has('name') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Name *</label>
                      <div class="col-9">
                        {{ Form::text('name', null, ['class' => 'form-control form-control-solid','id'=>'name','placeholder'=>'Enter member name','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                      </div>
                    </div>
                    
                    <div class="form-group row {{ $errors->has('code') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Code *</label>
                      <div class="col-9">
                        {{ Form::text('code', null, ['class' => 'form-control form-control-solid','id'=>'code','placeholder'=>'Enter member code','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('code') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Email *</label>
                      <div class="col-9">
                        {{ Form::email('email', null, ['class' => 'form-control form-control-solid','id'=>'email','placeholder'=>'Enter member email','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Password</label>
                      <div class="col-9">
                        {{ Form::password('password', ['class' => 'form-control form-control-solid','id'=>'password','placeholder'=>'Enter new password (leave blank to keep current)']) }}
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        <small class="form-text text-muted">Leave blank if you don't want to change the password</small>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Confirm Password</label>
                      <div class="col-9">
                        {{ Form::password('password_confirmation', ['class' => 'form-control form-control-solid','id'=>'password_confirmation','placeholder'=>'Confirm new password']) }}
                        <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('phone') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Phone *</label>
                      <div class="col-9">
                        {{ Form::text('phone', null, ['class' => 'form-control form-control-solid','id'=>'phone','placeholder'=>'Enter member phone','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('phone') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('address') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Address *</label>
                      <div class="col-9">
                        {{ Form::textarea('address', null, ['class' => 'form-control form-control-solid','id'=>'address','placeholder'=>'Enter member address','required'=>'true', 'rows' => '3']) }}
                        <span class="text-danger">{{ $errors->first('address') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('city') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">City *</label>
                      <div class="col-9">
                        {{ Form::text('city', null, ['class' => 'form-control form-control-solid','id'=>'city','placeholder'=>'Enter member city','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('city') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('package_id') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Package *</label>
                      <div class="col-9">
                        {{ Form::select('package_id', $packages->pluck('name', 'id'), null, ['class' => 'form-control form-control-solid','id'=>'package_id','placeholder'=>'Select package','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('package_id') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('start_date') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Start Date *</label>
                      <div class="col-9">
                        {{ Form::date('start_date', null, ['class' => 'form-control form-control-solid','id'=>'start_date','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('start_date') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Status *</label>
                      <div class="col-9">
                        {{ Form::select('status', [1 => 'Active', 0 => 'Inactive'], null, ['class' => 'form-control form-control-solid','id'=>'status','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('status') }}</span>
                      </div>
                    </div>

                  </div>

                </div>
                <div class="col-xl-2"></div>
              </div>
          {{Form::close()}}
            <!--end::Form-->
          </div>
        </div>
        <!--end::Card-->

      </div>
      <!--end::Container-->
    </div>
    <!--end::Entry-->
  </div>
@endsection 