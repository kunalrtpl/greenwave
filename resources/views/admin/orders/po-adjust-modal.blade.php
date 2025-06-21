<div class="modal fade" id="PoAdjustModal" tabindex="-1" role="dialog" aria-labelledby="PoAdjustModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="PoAdjustModalLabel">Update {{ucwords($data['status'])}}</h5>
            </div>
            <form action="{{url('/admin/update-po-adjustment')}}" method="post">@csrf
                <div class="modal-body">
                    <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                    <input type="hidden" name="status" value="{{$data['status']}}">
                    <div class="form-group">
                        <table class="table table-bordered">
                            <tr>
                                <th>Product Name</th>
                                <th>Ordered Qty</th>
                                <th>Sale Invoice Qty</th>
                                <th>{{ucwords($data['status'])}} Qty</th>
                            </tr>
                            @foreach($poDetail['orderitems'] as $orderItem)
                                <?php $po_item_sale_qty = \App\PurchaseOrderItem::po_item_sale_qty($orderItem['id']); ?>
                                <?php $finalQty = $orderItem['actual_qty'] - $po_item_sale_qty ?>
                                @if($finalQty >0)

                                    <tr>
                                        <td>{{$orderItem['product']['product_name']}}</td>
                                        <td>{{$orderItem['actual_qty']}}</td>
                                        <td>{{$po_item_sale_qty}}</td>
                                        <td>{{$finalQty}}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </table>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Reason:</label>
                        <select class="form-control" name="reason" required>
                            <option value="">Please Select</option>
                            @if($data['status'] == "adjustment")
                                <option value="Pending qty adjusted as new order placed by the customer">Pending qty adjusted as new order placed by the customer</option>
                                <option value="Pending qty adjusted due to difference in packing size">Pending qty adjusted due to difference in packing size</option>
                                <option value="Others">Others</option>
                            @else
                                <option value="Order cancelled as payment not received">Order cancelled as payment not received</option>
                                <option value="Order cancelled as P.O. entered twice">Order cancelled as P.O. entered twice</option>
                                <option value="Order cancelled as wrong P.O. was entered">Order cancelled as wrong P.O. was entered</option>
                                <option value="Others">Others</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Comments:</label>
                        <textarea class="form-control" name="comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>