<div class="card-datatable table-responsive">
	<table id="packages" class="datatables-demo table table-striped table-bordered">
		<tbody>

		<tr>
			<td>Name</td>
			<td>{{$package->name}}</td>
		</tr>

		<tr>
			<td>Price</td>
			<td>${{number_format($package->price, 2)}}</td>
		</tr>

		<tr>
			<td>Duration</td>
			<td>{{$package->duration}} days</td>
		</tr>

		<tr>
			<td>Created at</td>
			<td>{{$package->created_at->diffForHumans()}}</td>
		</tr>

		</tbody>
	</table>
</div>

