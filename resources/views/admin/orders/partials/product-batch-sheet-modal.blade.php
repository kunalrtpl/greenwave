<div class="modal fade" id="ProductBatchModal" tabindex="-1" role="dialog" aria-labelledby="ProductBatchModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="ProductBatchModalLabel">Dispatch Product Qty</h5>
			</div>
			<form id="UpdateProductDispatchQty" action="javascript:;" method="post">@csrf
				<div class="modal-body">
					<input type="hidden" name="order_item_id" value="{{$orderitemid}}">
					<input type="hidden" name="dealer_info" value="{{$data['dealer_info']}}">
					<input type="hidden" name="product_name" value="{{$data['product_name']}}">
					<table class="table table-bordered table-stripped">
						<tr>
							<th>Batch Number</th>
							<th>Available Stock</th>
							<th>Issue Stock</th>
						</tr>
						@foreach($batchSheets as $batchSheet)
							<input type="hidden" name="batch_ids[]" value="{{$batchSheet['id']}}">
							<tr>
								<td>{{$batchSheet['batch_no_str']}}</td>
								<td>{{$batchSheet['remaining_stock']}}</td>
								<td>
									<input placeholder="Enter Issue Stock" type="number" class="form-control" name="stocks[]" >
									<h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetErr-{{$batchSheet['id']}}"></h4>
								</td>
							</tr>
						@endforeach
					</table>
					<h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetErr-total_stock_error"></h4>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>