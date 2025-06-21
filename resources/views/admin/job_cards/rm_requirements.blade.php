@if($data['type'] =="RM")
<table class="table table-stripped table-bordered">
	<tr style="background-color: #E9ECF3;">
		<th colspan="5" class="text-center">RM: {{$rawMaterial['name']}}</th>
		<td colspan="1" class="text-right">
			<a class="btn btn-sm btn-danger deleteRmRequirement" href="javascript:;"><i class="fa fa-times"></i></a>
		</td>
	</tr>
	<tr>
		<th align="left" width="20%">Status</th>
		<th width="15%">Batch No.</th>
		<th width="15%">Stock</th>
		<th width="30%">QC Reports</th>
		<th width="15%">Qty(%)</th>
		<th width="20%">Qty(kg)</td>
	</tr>
	@if(!empty($rmBatches))
		@foreach($rmBatches as $rmBatch)
			@if($rmBatch['remaining_stock'] >0)
				<?php $status_date =""; ?>
				@foreach($rmBatch['rm_histories'] as $rmhistory)
					@if($rmBatch['status'] == $rmhistory['status'])
						<?php $status_date = $rmhistory['created_at']; ?>
					@endif
				@endforeach
				<tr>
					<td align="left">
						@if($rmBatch['status']  =="QC Approved")
							<span style="color:green;"><b>{{$rmBatch['status']}}</b></span>
						@else
							<b>{{$rmBatch['status']}}</b>
						@endif
						@if(!empty($status_date))
							<br><small>({{date('d M Y',strtotime($status_date))}})</small>
						@endif
					</td>
					<td>{{$rmBatch['supplier_batch_no']}}</td>
					<td align="right">{{$rmBatch['remaining_stock']}}</td>
					<td>
						@if($rmBatch['status']  =="QC Approved")
							<a target="_blank" href="{{url('/admin/inventory/rm-pdf/'.$rmBatch['id'].'/filled')}}"> QC Report</a>
						@endif
					</td>
					<td>
						@if($rmBatch['status'] =="QC Approved")
							<input class="form-control reqQty" type="number" step="0.01" name="rm_qty[{{$rmBatch['id']}}]">
						@endif
					</td>
					<td></td>
				</tr>
			@endif
		@endforeach
	@else
		<tr>
			<td colspan="6">No Batches found.</td>
		</tr>
	@endif
</table>
@elseif($data['type'] =="SRM")
<table class="table table-stripped table-bordered">
	<tr style="background-color: #E9ECF3;">
		<th colspan="5" class="text-center">Product: {{$product['product_name']}}</th>
		<td  class="text-right">
			<a class="btn btn-sm btn-danger deleteRmRequirement" href="javascript:;"><i class="fa fa-times"></i></a>
		</td>
	</tr>
	<tr>
		<th align="left" width="20%">Status</th>
		<th width="15%">Batch No.</th>
		<th width="15%">Stock</th>
		<th width="30%">QC Remarks</th>
		<th width="15%">Qty(%)</th>
		<th width="20%">Qty(kg)</td>
	</tr>
	@if(!empty($srmBatches))
		@foreach($srmBatches as $srmBatch)
			@if($srmBatch['remaining_stock']>0)
				<?php $status_date =""; ?>
				@foreach($srmBatch['osp_histories'] as $osphistory)
					@if($srmBatch['status'] == $osphistory['status'])
						<?php $status_date = $osphistory['created_at']; ?>
					@endif
				@endforeach
				<tr>
					<td align="left">
					@if($srmBatch['status']  =="QC Approved")
						<span style="color:green;"><b>{{$srmBatch['status']}}</b></span>
					@else
						<b>{{$srmBatch['status']}}</b>
					@endif
					@if(!empty($status_date))
						<br><small>({{date('d M Y',strtotime($status_date))}})</small>
					@endif
					</td>
					<td>{{$srmBatch['supplier_batch_no']}}</td>
					<td align="right">{{$srmBatch['remaining_stock']}}</td>
					<td>
						@if($srmBatch['status']  =="QC Approved")
							<a target="_blank" href="{{url('/admin/inventory/osp-pdf/'.$srmBatch['id'].'/filled')}}">QC Report</a>
						@endif
					</td>
					<td>
						@if($srmBatch['status'] =="QC Approved")
							<input class="form-control reqQty" type="number" step="0.01" name="srm_qty[{{$srmBatch['id']}}]">
						@endif
					</td>
					<td></td>
				</tr>
			@endif
		@endforeach
	@else
		<tr>
			<td colspan="6">No Batches found.</td>
		</tr>
	@endif
</table>
@endif

