<div class="card-datatable table-responsive">
	<table id="clients" class="datatables-demo table table-striped table-bordered">
		<tbody>
		<tr>
			<td>Name</td>
			<td>{{$user->name}}</td>
		</tr>
		<tr>
			<td>Permissions</td>
			<td>
                @if(!empty($rolePermissions))
                    @foreach($rolePermissions as $v)
                        <label class="label label-success label-inline mr-2 mt-2">{{ ucwords(str_replace("-", " ", $v->name)) }}</label>
                    @endforeach
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

