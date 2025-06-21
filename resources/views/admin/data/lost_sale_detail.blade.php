<tr>
	<td>Date</td>
	<td>{{date('d M Y',strtotime($lost_sale_info['report_date']))}}</td>
</tr>
<tr>
	<td>Customer Name</td>
	<td>{{$lost_sale_info['customer_name']}}</td>
</tr>
<tr>
	<td>Product Name</td>
	<td>{{$lost_sale_info['product_name']}}</td>
</tr>
<tr>
	<td>Monthly Sale Requirement</td>
	<td>{{$lost_sale_info['monthly_requirement']}} kg</td>
</tr>
<tr>
	<td>Reason</td>
	<td>{{$lost_sale_info['reason']}}</td>
</tr>
<tr>
	<th colspan="2" class="text-center">Replaced By</th>
</tr>
<tr>
	<td>Replaced by Product Name</td>
	<td>{{$lost_sale_info['replaced_by_product_name']}}</td>
</tr>
<tr>
	<td>Make</td>
	<td>{{$lost_sale_info['replaced_by_company_name']}}</td>
</tr>
<tr>
	<td>Price</td>
	<td>{{$lost_sale_info['replaced_by_price']}}</td>
</tr>
<tr>
	<td>Application</td>
	<td>{{$lost_sale_info['replaced_by_application']}}</td>
</tr>
@if($lost_sale_info['replaced_by_application'] =="Exhaust")
	@if($lost_sale_info['replaced_by_dosage_type'] =="Percentage")
		<tr>
			<td>Dosage</td>
			<td>{{$lost_sale_info['replaced_by_dosage_percent']}}%</td>
		</tr>
		<tr>
			<td>Product Cost</td>
			<td>Rs. {{$lost_sale_info['replaced_by_cost_percent']}}</td>
		</tr>
	@else
		<tr>
			<td>Dosage</td>
			<td>{{$lost_sale_info['replaced_by_dosage_gpl']}} gpl</td>
		</tr>
		<tr>
			<td>MLR</td>
			<td>{{$lost_sale_info['replaced_by_mlr']}}</td>
		</tr>
		<tr>
			<td>Product Cost</td>
			<td>Rs. {{$lost_sale_info['replaced_by_cost_gpl']}}</td>
		</tr>
	@endif
@else
	<tr>
		<td>Pick-up</td>
		<td>{{$lost_sale_info['replaced_by_pick_up']}}%</td>
	</tr>
	<tr>
		<td>Trough Loss</td>
		<td>{{$lost_sale_info['replaced_by_trough_loss']}} litres</td>
	</tr>
	<tr>
		<td>Lot Size</td>
		<td>{{$lost_sale_info['replaced_by_lot_size']}} kg</td>
	</tr>
	<tr>
		<td>Dosage</td>
		<td>{{$lost_sale_info['replaced_by_dosage_pm']}} gpl</td>
	</tr>
	<tr>
		<td>Cost</td>
		<td>Rs.{{$lost_sale_info['replaced_by_cost_pm']}}</td>
	</tr>
@endif
<tr>
	<th colspan="2" class="text-center">Our Prodcut in Comparision</th>
</tr>
<tr>
	<td>Price</td>
	<td>Rs. {{$lost_sale_info['price']}}</td>
</tr>
@if($lost_sale_info['application'] =="Exhaust")
	@if($lost_sale_info['dosage_type'] =="Percentage")
		<tr>
			<td>Dosage</td>
			<td>{{$lost_sale_info['dosage_percent']}}%</td>
		</tr>
		<tr>
			<td>Product Cost</td>
			<td>Rs. {{$lost_sale_info['cost_percent']}}</td>
		</tr>
	@else
		<tr>
			<td>Dosage</td>
			<td>{{$lost_sale_info['dosage_gpl']}} gpl</td>
		</tr>
		<tr>
			<td>MLR</td>
			<td>{{$lost_sale_info['mlr']}}</td>
		</tr>
		<tr>
			<td>Product Cost</td>
			<td>Rs. {{$lost_sale_info['cost_gpl']}}</td>
		</tr>
	@endif
@else
	<tr>
		<td>Pick-up</td>
		<td>{{$lost_sale_info['pick_up']}}%</td>
	</tr>
	<tr>
		<td>Trough Loss</td>
		<td>{{$lost_sale_info['trough_loss']}} litres</td>
	</tr>
	<tr>
		<td>Lot Size</td>
		<td>{{$lost_sale_info['lot_size']}} kg</td>
	</tr>
	<tr>
		<td>Dosage</td>
		<td>{{$lost_sale_info['dosage_pm']}} gpl</td>
	</tr>
	<tr>
		<td>Cost</td>
		<td>Rs.{{$lost_sale_info['cost_pm']}}</td>
	</tr>
@endif
<tr>
	<td>Remarks</td>
	<td>{{$lost_sale_info['remarks']}}</td>
</tr>
<tr>
	<td>Executive</td>
	<td>{{$lost_sale_info['user_name']}}</td>
</tr>