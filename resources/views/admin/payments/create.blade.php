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
                <a href="" class="text-muted">Add payment</a>
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
              <h3 class="card-label">Payment Add Form
                <i class="mr-2"></i>
                <small class="">try to scroll the page</small></h3>

            </div>
            <div class="card-toolbar">

              <a href="{{ route('payments.index') }}" class="btn btn-light-primary
              font-weight-bolder mr-2">
                <i class="ki ki-long-arrow-back icon-sm"></i>Back</a>

              <div class="btn-group">
                @can("payment-create")
                <a href="{{ route('payments.store') }}"  onclick="event.preventDefault(); document.getElementById('base_form').submit();" id="kt_btn" class="btn btn-primary font-weight-bolder">
                  <i class="ki ki-check icon-sm"></i>Save</a>
                @endcan


              </div>
            </div>
          </div>
          <div class="card-body">
          @include('admin.partials._messages')
          <!--begin::Form-->
            {{ Form::open([ 'route' => 'payments.store','class'=>'form' ,"id"=>"base_form", 'enctype'=>'multipart/form-data']) }}
              @csrf
              <div class="row">
                <div class="col-xl-2"></div>
                <div class="col-xl-8">
                  <div class="my-5">

                    <div class="form-group row {{ $errors->has('member_id') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Member *</label>
                      <div class="col-9">
                        {{ Form::select('member_id', $members->pluck('name', 'id'), null, ['class' => 'form-control form-control-solid','id'=>'member_id','placeholder'=>'Select member','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('member_id') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('package_id') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Package *</label>
                      <div class="col-9">
                        {{ Form::select('package_id', $packages->pluck('name', 'id'), null, ['class' => 'form-control form-control-solid','id'=>'package_id','placeholder'=>'Select package','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('package_id') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('amount') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Amount *</label>
                      <div class="col-9">
                        {{ Form::number('amount', null, ['class' => 'form-control form-control-solid','id'=>'amount','placeholder'=>'Enter payment amount','required'=>'true', 'step' => '0.01', 'min' => '0']) }}
                        <span class="text-danger">{{ $errors->first('amount') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('start_date') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Start Date *</label>
                      <div class="col-9">
                        {{ Form::date('start_date', null, ['class' => 'form-control form-control-solid','id'=>'start_date','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('start_date') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('end_date') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">End Date *</label>
                      <div class="col-9">
                        {{ Form::date('end_date', null, ['class' => 'form-control form-control-solid','id'=>'end_date','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('end_date') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Payment Method *</label>
                      <div class="col-9">
                        {{ Form::select('payment_method', [
                            'cash' => 'Cash',
                            'card' => 'Card',
                            'bank_transfer' => 'Bank Transfer',
                            'online' => 'Online'
                        ], null, ['class' => 'form-control form-control-solid','id'=>'payment_method','placeholder'=>'Select payment method','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('payment_method') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('payment_status') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Payment Status *</label>
                      <div class="col-9">
                        {{ Form::select('payment_status', [
                            'paid' => 'Paid',
                            'pending' => 'Pending',
                            'failed' => 'Failed'
                        ], 'pending', ['class' => 'form-control form-control-solid','id'=>'payment_status','required'=>'true']) }}
                        <span class="text-danger">{{ $errors->first('payment_status') }}</span>
                      </div>
                    </div>

                    <div class="form-group row {{ $errors->has('note') ? 'has-error' : '' }}">
                      <label class="col-form-label col-3 text-right">Note</label>
                      <div class="col-9">
                        {{ Form::textarea('note', null, ['class' => 'form-control form-control-solid','id'=>'note','placeholder'=>'Enter payment note (optional)','rows' => '3']) }}
                        <span class="text-danger">{{ $errors->first('note') }}</span>
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