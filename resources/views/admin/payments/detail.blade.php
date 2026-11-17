<div class="card-datatable table-responsive">
	<table id="payments" class="datatables-demo table table-striped table-bordered">
		<tbody>

		<tr>
			<td>Member</td>
			<td>{{$payment->member ? $payment->member->name .' - '. $payment->member->code : 'N/A'}}</td>
		</tr>

		<tr>
			<td>Package</td>
			<td>{{$payment->package ? $payment->package->name : 'N/A'}}</td>
		</tr>

		<tr>
			<td>Amount</td>
			<td>Rs{{number_format($payment->amount, 2)}}</td>
		</tr>

		<tr>
			<td>Start Date</td>
			<td>{{$payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A'}}</td>
		</tr>

		<tr>
			<td>End Date</td>
			<td>{{$payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A'}}</td>
		</tr>

		<tr>
			<td>Payment Method</td>
			<td>{{ucfirst($payment->payment_method)}}</td>
		</tr>

		<tr>
			<td>Payment Status</td>
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
			<td>Note</td>
			<td>{{$payment->note}}</td>
		</tr>
		@endif

		<tr>
			<td>Created at</td>
			<td>{{$payment->created_at->diffForHumans()}}</td>
		</tr>

		</tbody>
	</table>
</div> 