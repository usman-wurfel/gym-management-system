<div class="card-datatable table-responsive">
	<table id="clients" class="datatables-demo table table-striped table-bordered">
		<tbody>
		<tr>
			<td>Name</td>
			<td>{{$user->name}}</td>
		</tr>
		<tr>
			<td>Email</td>
			<td>{{$user->email}}</td>
		</tr>
		<tr>
			<td>Status</td>
			<td>
				@if($user->active)
					<label class="label label-success label-inline mr-2">Active</label>
				@else
					<label class="label label-danger label-inline mr-2">Inactive</label>
				@endif
			</td>
		</tr>
		<tr>
			<td>Image</td>
			<td>
				@if($user->image)
					<img src="{{ asset('images/'.$user->image) }}" width="30%" alt="Player Image">
				@endif
			</td>
		</tr>
		<tr>
			<td>Created at</td>
			<td>{{$user->created_at}}</td>
		</tr>
		

		</tbody>
	</table>
	
</div>

<!-- Subscription History Section -->
<div class="mt-5">
	<h5 class="font-weight-bold mb-3">Subscription History</h5>
	@if($user->subscriptionHistory->count() > 0)
		<div class="table-responsive">
			<table class="table table-bordered table-hover">
				<thead class="thead-light">
					<tr>
						<th>#</th>
						<th>Package</th>
						<th>Price</th>
						<th>Start Date</th>
						<th>End Date</th>
						<th>Status</th>
						<th>Created Date</th>
					</tr>
				</thead>
				<tbody>
					@foreach($user->subscriptionHistory()->orderBy('created_at', 'desc')->get() as $index => $subscription)
					<tr>
						<td>{{ $index + 1 }}</td>
						<td>
							<strong>{{ $subscription->package->name }}</strong>
							<br>
							<small class="text-muted">{{ $subscription->package->description }}</small>
						</td>
						<td>
							<span class="font-weight-bold text-success">{{ $subscription->formatted_price }}</span>
						</td>
						<td>{{ $subscription->start_date->format('M d, Y') }}</td>
						<td>{{ $subscription->end_date->format('M d, Y') }}</td>
						<td>
							@if($subscription->is_active)
								<span class="badge badge-success">Active</span>
							@elseif(now()->lt($subscription->start_date))
								<span class="badge badge-warning">Pending</span>
							@else
								<span class="badge badge-danger">Expired</span>
							@endif
						</td>
						<td>{{ $subscription->created_at->format('M d, Y H:i A') }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	@else
		<div class="alert alert-info">
			<i class="fas fa-info-circle"></i>
			No subscription history found for this business owner.
		</div>
	@endif
</div>

