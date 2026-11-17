@extends('admin.layouts.master')
@section('title',$title)
@section('content')
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Dashboard-->
            
            <!--begin::Row-->
            <div class="row">
                @php
                    $id = auth()->user()->user_id ?? auth()->user()->id;
                    $totalMembers = \App\Models\Member::where('user_id',$id)->count();
                    $activeMembers = \App\Models\Member::where("status", 1)->where('user_id',$id)->count();
                    $totalPayments = \App\Models\Payment::where('user_id',$id)->count();
                    $totalPackages = \App\Models\Package::where('user_id',$id)->count();
                    
                    // This month's statistics
                    $thisMonth = now()->startOfMonth();
                    $thisMonthPayments = \App\Models\Payment::whereMonth('created_at', $thisMonth->month)
                        ->whereYear('created_at', $thisMonth->year)
                        ->where('user_id',$id)
                        ->count();
                    $thisMonthRevenue = \App\Models\Payment::whereMonth('created_at', $thisMonth->month)
                        ->whereYear('created_at', $thisMonth->year)
                        ->where('user_id',$id)
                        ->where('payment_status', 'paid')
                        ->sum('amount');
                    
                    // Last month's statistics
                    $lastMonth = now()->subMonth()->startOfMonth();
                    $lastMonthPayments = \App\Models\Payment::whereMonth('created_at', $lastMonth->month)
                        ->whereYear('created_at', $lastMonth->year)
                        ->where('user_id',$id)
                        ->count();
                    $lastMonthRevenue = \App\Models\Payment::whereMonth('created_at', $lastMonth->month)
                        ->whereYear('created_at', $lastMonth->year)
                        ->where('user_id',$id)
                        ->where('payment_status', 'paid')
                        ->sum('amount');
                    
                    // Total revenue
                    $totalRevenue = \App\Models\Payment::where('payment_status', 'paid')->where('user_id',$id)->sum('amount');
                    
                    // Pending payments
                    $pendingPayments = \App\Models\Payment::where('payment_status', 'pending')->where('user_id',$id)->count();
                    $pendingAmount = \App\Models\Payment::where('payment_status', 'pending')->where('user_id',$id)->sum('amount');
                    
                    $recentMembers = \App\Models\Member::with('package')->where('user_id',$id)->orderBy('created_at', 'desc')->take(5)->get();
                    $recentPayments = \App\Models\Payment::with(['member', 'package'])->where('user_id',$id)->orderBy('created_at', 'desc')->take(5)->get();
                    
                    // Subscription details for non-super admins
                    $currentUser = auth()->user();
                    $subscription = null;
                    $daysRemaining = 0;
                    $subscriptionStatus = 'No Active Subscription';
                    $subscriptionColor = 'danger';
                    
                    if ($currentUser->super_admin != 1) {
                        // Check if user has parent (user_id is not null)
                        $userToCheck = $currentUser->user_id ? \App\Models\User::find($currentUser->user_id) : $currentUser;
                        
                        if ($userToCheck) {
                            $subscription = $userToCheck->activeSubscription()->first();
                            
                            if ($subscription) {
                                $now = now();
                                $endDate = \Carbon\Carbon::parse($subscription->end_date);
                                $daysRemaining = $now->diffInDays($endDate, false);
                                
                                if ($daysRemaining > 30) {
                                    $subscriptionStatus = 'Active';
                                    $subscriptionColor = 'success';
                                } elseif ($daysRemaining > 7) {
                                    $subscriptionStatus = 'Expiring Soon';
                                    $subscriptionColor = 'warning';
                                } elseif ($daysRemaining > 0) {
                                    $subscriptionStatus = 'Expiring Very Soon';
                                    $subscriptionColor = 'danger';
                                } else {
                                    $subscriptionStatus = 'Expired';
                                    $subscriptionColor = 'danger';
                                }
                            }
                        }
                    }
                @endphp

                <!--begin::Stats Cards-->
                <div class="col-lg-3 col-xxl-3">
                    <!--begin::Stats Widget 1-->
                    <div class="card card-custom bg-primary card-stretch gutter-b">
                        <div class="card-body p-0 position-relative overflow-hidden">
                            <div class="card-spacer">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <span class="text-white font-weight-bold font-size-h6">Total Members</span>
                                        <div class="text-white font-weight-bolder font-size-h2">{{ $totalMembers }}</div>
                                        <div class="text-white opacity-75">Active: {{ $activeMembers }}</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-users fa-3x text-white opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats Widget 1-->
                </div>

                <div class="col-lg-3 col-xxl-3">
                    <!--begin::Stats Widget 2-->
                    <div class="card card-custom bg-success card-stretch gutter-b">
                        <div class="card-body p-0 position-relative overflow-hidden">
                            <div class="card-spacer">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <span class="text-white font-weight-bold font-size-h6">This Month Revenue</span>
                                        <div class="text-white font-weight-bolder font-size-h2">Rs{{ number_format($thisMonthRevenue, 2) }}</div>
                                        <div class="text-white opacity-75">{{ $thisMonthPayments }} payments</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-dollar-sign fa-3x text-white opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats Widget 2-->
                </div>

                <div class="col-lg-3 col-xxl-3">
                    <!--begin::Stats Widget 3-->
                    <div class="card card-custom bg-warning card-stretch gutter-b">
                        <div class="card-body p-0 position-relative overflow-hidden">
                            <div class="card-spacer">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <span class="text-white font-weight-bold font-size-h6">Total Revenue</span>
                                        <div class="text-white font-weight-bolder font-size-h2">Rs{{ number_format($totalRevenue, 2) }}</div>
                                        <div class="text-white opacity-75">{{ $totalPayments }} total payments</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-chart-line fa-3x text-white opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats Widget 3-->
                </div>

                <div class="col-lg-3 col-xxl-3">
                    <!--begin::Stats Widget 4-->
                    <div class="card card-custom bg-info card-stretch gutter-b">
                        <div class="card-body p-0 position-relative overflow-hidden">
                            <div class="card-spacer">
                                <a href="{{ route('admin.pending-payments') }}" class="text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="text-white font-weight-bold font-size-h6">Pending Payments</span>
                                            <div class="text-white font-weight-bolder font-size-h2">{{ $pendingPayments }}</div>
                                            <div class="text-white opacity-75">Rs{{ number_format($pendingAmount, 2) }}</div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-clock fa-3x text-white opacity-75"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats Widget 4-->
                </div>
            </div>
            <!--end::Row-->

            @if(auth()->user()->super_admin != 1)
            <!--begin::Row-->
            <div class="row">
                <!--begin::Subscription Status-->
                <div class="col-lg-12 col-xxl-12">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">
                                    <i class="fas fa-crown text-warning mr-2"></i>
                                    Subscription Status
                                </h3>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($subscription)
                                <div class="row">
                                    <div class="col-lg-8 col-xxl-8">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="symbol symbol-60 symbol-light-{{ $subscriptionColor }} mr-4">
                                                <span class="symbol-label">
                                                    <i class="fas fa-box fa-2x text-{{ $subscriptionColor }}"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h4 class="font-weight-bolder font-size-h3 mb-1">{{ $subscription->package->name }}</h4>
                                                <p class="text-muted mb-0">{{ $subscription->package->description }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="label label-lg label-light-{{ $subscriptionColor }} label-inline font-weight-bold">
                                                    {{ $subscriptionStatus }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-4 col-xxl-4">
                                                <div class="bg-light-primary rounded p-4 text-center">
                                                    <div class="font-weight-bold font-size-h4 text-primary">
                                                        @if($daysRemaining > 0)
                                                            {{ $daysRemaining }}
                                                        @else
                                                            0
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">Days Remaining</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-xxl-4">
                                                <div class="bg-light-success rounded p-4 text-center">
                                                    <div class="font-weight-bold font-size-h4 text-success">
                                                        Rs{{ number_format($subscription->price, 2) }}
                                                    </div>
                                                    <div class="text-muted">Package Price</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-xxl-4">
                                                <div class="bg-light-info rounded p-4 text-center">
                                                    <div class="font-weight-bold font-size-h4 text-info">
                                                        {{ $subscription->package->duration }}
                                                    </div>
                                                    <div class="text-muted">Days Duration</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <h5 class="font-weight-bold mb-3">
                                                <i class="fas fa-calendar-alt text-info mr-2"></i>
                                                Subscription Period
                                            </h5>
                                            <div class="row">
                                                <div class="col-lg-6 col-xxl-6">
                                                    <div class="d-flex align-items-center p-3 bg-light-primary rounded">
                                                        <div class="symbol symbol-40 symbol-light-primary mr-3">
                                                            <span class="symbol-label">
                                                                <i class="fas fa-play text-primary"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">Start Date</div>
                                                            <div class="text-muted">{{ \Carbon\Carbon::parse($subscription->start_date)->format('M d, Y') }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-xxl-6">
                                                    <div class="d-flex align-items-center p-3 bg-light-danger rounded">
                                                        <div class="symbol symbol-40 symbol-light-danger mr-3">
                                                            <span class="symbol-label">
                                                                <i class="fas fa-stop text-danger"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">End Date</div>
                                                            <div class="text-muted">{{ \Carbon\Carbon::parse($subscription->end_date)->format('M d, Y') }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($subscription->package->features)
                                        <div class="mt-4">
                                            <h5 class="font-weight-bold mb-3">
                                                <i class="fas fa-list-check text-success mr-2"></i>
                                                Package Features
                                            </h5>
                                            <div class="bg-light-success rounded p-4">
                                                <div class="row">
                                                    @foreach(explode("\n", $subscription->package->features) as $feature)
                                                        @if(trim($feature))
                                                            <div class="col-lg-6 col-xxl-6 mb-2">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                                    <span>{{ trim($feature) }}</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="col-lg-4 col-xxl-4">
                                        <div class="card card-custom bg-light-{{ $subscriptionColor }} border-{{ $subscriptionColor }}">
                                            <div class="card-body text-center">
                                                <div class="symbol symbol-80 symbol-light-{{ $subscriptionColor }} mx-auto mb-4">
                                                    <span class="symbol-label">
                                                        @if($subscriptionColor == 'success')
                                                            <i class="fas fa-check-circle fa-3x text-success"></i>
                                                        @elseif($subscriptionColor == 'warning')
                                                            <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                                                        @else
                                                            <i class="fas fa-times-circle fa-3x text-danger"></i>
                                                        @endif
                                                    </span>
                                                </div>
                                                
                                                <h4 class="font-weight-bolder mb-3">Subscription Status</h4>
                                                
                                                @if($daysRemaining > 30)
                                                    <p class="text-success mb-4">Your subscription is active and healthy! You have plenty of time remaining.</p>
                                                @elseif($daysRemaining > 7)
                                                    <p class="text-warning mb-4">Your subscription will expire soon. Please contact admin to renew.</p>
                                                @elseif($daysRemaining > 0)
                                                    <p class="text-danger mb-4">Your subscription is expiring very soon! Please contact admin immediately.</p>
                                                @else
                                                    <p class="text-danger mb-4">Your subscription has expired. Please contact admin to renew.</p>
                                                @endif
                                                
                                                <div class="bg-white rounded p-3 mb-4">
                                                    <div class="font-weight-bold text-{{ $subscriptionColor }} mb-2">Contact Admin</div>
                                                    @php
                                                        $setting = \App\Models\Setting::pluck('value','name')->toArray();
                                                        $adminEmail = isset($setting['email']) ? $setting['email'] : 'admin@domain.com';
                                                        $adminPhone = isset($setting['phone']) ? $setting['phone'] : 'N/A';
                                                    @endphp
                                                    <div class="text-muted">
                                                        <div><i class="fas fa-envelope mr-2"></i>{{ $adminEmail }}</div>
                                                        <div><i class="fas fa-phone mr-2"></i>{{ $adminPhone }}</div>
                                                    </div>
                                                </div>
                                                
                                                @if($daysRemaining <= 7)
                                                    <button class="btn btn-{{ $subscriptionColor }} btn-lg btn-block" onclick="alert('Please contact admin to renew your subscription.')">
                                                        <i class="fas fa-sync-alt mr-2"></i>
                                                        Renew Subscription
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="symbol symbol-100 symbol-light-danger mx-auto mb-4">
                                        <span class="symbol-label">
                                            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                                        </span>
                                    </div>
                                    <h4 class="font-weight-bolder text-danger mb-3">No Active Subscription</h4>
                                    <p class="text-muted mb-4">You don't have an active subscription. Please contact admin to get a subscription package.</p>
                                    @php
                                        $setting = \App\Models\Setting::pluck('value','name')->toArray();
                                        $adminEmail = isset($setting['email']) ? $setting['email'] : 'admin@domain.com';
                                        $adminPhone = isset($setting['phone']) ? $setting['phone'] : 'N/A';
                                    @endphp
                                    <div class="bg-light-danger rounded p-4 d-inline-block">
                                        <div class="font-weight-bold text-danger mb-2">Contact Admin</div>
                                        <div class="text-muted">
                                            <div><i class="fas fa-envelope mr-2"></i>{{ $adminEmail }}</div>
                                            <div><i class="fas fa-phone mr-2"></i>{{ $adminPhone }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Subscription Status-->
            </div>
            <!--end::Row-->
            @endif

            <!--begin::Row-->
            <div class="row">
                <!--begin::Additional Stats-->
                <div class="col-lg-12 col-xxl-12">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Monthly Comparison</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 col-xxl-3">
                                    <div class="bg-light-primary rounded p-4 text-center">
                                        <div class="font-weight-bold font-size-h4 text-primary">{{ $thisMonthPayments }}</div>
                                        <div class="text-muted">This Month Payments</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-xxl-3">
                                    <div class="bg-light-success rounded p-4 text-center">
                                        <div class="font-weight-bold font-size-h4 text-success">Rs{{ number_format($thisMonthRevenue, 2) }}</div>
                                        <div class="text-muted">This Month Revenue</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-xxl-3">
                                    <div class="bg-light-warning rounded p-4 text-center">
                                        <div class="font-weight-bold font-size-h4 text-warning">{{ $lastMonthPayments }}</div>
                                        <div class="text-muted">Last Month Payments</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-xxl-3">
                                    <div class="bg-light-info rounded p-4 text-center">
                                        <div class="font-weight-bold font-size-h4 text-info">Rs{{ number_format($lastMonthRevenue, 2) }}</div>
                                        <div class="text-muted">Last Month Revenue</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Additional Stats-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row">
                <!--begin::Recent Members-->
                <div class="col-lg-6 col-xxl-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Recent Members</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-head-custom table-vertical-center">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Package</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentMembers as $member)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40 symbol-light-success mr-4">
                                                        <span class="symbol-label">
                                                            <i class="fas fa-user fa-lg text-success"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bolder font-size-lg">{{ $member->name }}</div>
                                                        <div class="text-muted">{{ $member->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $member->package ? $member->package->name : 'N/A' }}</td>
                                            <td>
                                                @if($member->status)
                                                    <span class="label label-lg label-light-success label-inline">Active</span>
                                                @else
                                                    <span class="label label-lg label-light-danger label-inline">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $member->created_at->diffForHumans() }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Recent Members-->

                <!--begin::Recent Payments-->
                <div class="col-lg-6 col-xxl-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Recent Payments</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-head-custom table-vertical-center">
                                    <thead>
                                        <tr>
                                            <th>Member</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentPayments as $payment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40 symbol-light-warning mr-4">
                                                        <span class="symbol-label">
                                                            <i class="fas fa-credit-card fa-lg text-warning"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bolder font-size-lg">{{ $payment->member ? $payment->member->name : 'N/A' }}</div>
                                                        <div class="text-muted">{{ $payment->package ? $payment->package->name : 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rs{{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                @if($payment->payment_status == 'paid')
                                                    <span class="label label-lg label-light-success label-inline">Paid</span>
                                                @elseif($payment->payment_status == 'pending')
                                                    <span class="label label-lg label-light-warning label-inline">Pending</span>
                                                @else
                                                    <span class="label label-lg label-light-danger label-inline">Failed</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->created_at->diffForHumans() }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Recent Payments-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row">
                <!--begin::Quick Actions-->
                <div class="col-lg-12 col-xxl-12">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Quick Actions</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-xxl-2">
                                    <a href="{{ route('members.index') }}" class="btn btn-primary btn-block btn-lg">
                                        <i class="fas fa-users fa-lg mr-2"></i>
                                        Manage Members
                                    </a>
                                </div>
                                <div class="col-lg-2 col-xxl-2">
                                    <a href="{{ route('payments.index') }}" class="btn btn-warning btn-block btn-lg">
                                        <i class="fas fa-credit-card fa-lg mr-2"></i>
                                        Manage Payments
                                    </a>
                                </div>
                                <div class="col-lg-2 col-xxl-2">
                                    <a href="{{ route('packages.index') }}" class="btn btn-info btn-block btn-lg">
                                        <i class="fas fa-box fa-lg mr-2"></i>
                                        Manage Packages
                                    </a>
                                </div>
                                <div class="col-lg-2 col-xxl-2">
                                    <a href="{{ route('members.create') }}" class="btn btn-success btn-block btn-lg">
                                        <i class="fas fa-user-plus fa-lg mr-2"></i>
                                        Add New Member
                                    </a>
                                </div>
                                <div class="col-lg-2 col-xxl-2">
                                    <a href="{{ route('members.import') }}" class="btn btn-dark btn-block btn-lg">
                                        <i class="fas fa-upload fa-lg mr-2"></i>
                                        Import Members
                                    </a>
                                </div>
                                <div class="col-lg-2 col-xxl-2">
                                    <a href="{{ route('members.download-sample') }}" class="btn btn-secondary btn-block btn-lg">
                                        <i class="fas fa-download fa-lg mr-2"></i>
                                        Download Sample
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Quick Actions-->
            </div>
            <!--end::Row-->

            <!--end::Dashboard-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
@endsection
