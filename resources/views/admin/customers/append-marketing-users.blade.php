<tr>
	<td>
		<input type="hidden" name="marketing_user_shares[]" value="100">
		<input class="form-control" type="date" name="user_dates[]" required>
	</td>
	<td>
		<select class="form-control" name="marketing_user_ids[]" required>
			<option value="">Please Select</option>
			@foreach($users as $user)
				<option value="{{$user['id']}}">{{$user['name']}}</option>
			@endforeach
		</select>
	</td>
	<!-- <td>
		<input type="number" name="average_sales[]" class="form-control" placeholder="Enter Average Sales" required>
	</td> -->
	<td>
		<a title="Remove" class="btn btn-sm red " onClick="$(this).closest('tr').remove();" href="javascript:;"> <i class="fa fa-times"></i></a>
	</td>
</tr>