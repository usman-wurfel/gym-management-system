<div class="card-datatable table-responsive">
	<table id="members" class="datatables-demo table table-striped table-bordered">
		<tbody>

		<tr>
			<td>Name</td>
			<td>{{$member->name}}</td>
		</tr>

		<tr>
			<td>Code</td>
			<td>{{$member->code}}</td>
		</tr>

		<tr>
			<td>Email</td>
			<td>{{$member->email}}</td>
		</tr>

		<tr>
			<td>Phone</td>
			<td>{{$member->phone}}</td>
		</tr>

		<tr>
			<td>Address</td>
			<td>{{$member->address}}</td>
		</tr>

		<tr>
			<td>City</td>
			<td>{{$member->city}}</td>
		</tr>

		<tr>
			<td>Package</td>
			<td>{{$member->package ? $member->package->name : 'N/A'}}</td>
		</tr>

		<tr>
			<td>Start Date</td>
			<td>{{$member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A'}}</td>
		</tr>

		<tr>
			<td>Status</td>
			<td>{{$member->status == 1 ? 'Active' : 'Inactive'}}</td>
		</tr>

		<tr>
			<td>Created at</td>
			<td>{{$member->created_at->diffForHumans()}}</td>
		</tr>

		</tbody>
	</table>
</div> 