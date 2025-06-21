<div class="modal fade" id="TraderSaleInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="TraderSaleInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="TraderSaleInvoiceModalLabel">Sale Invoice</h5>
            </div>
            <form action="{{url('/admin/create-trader-sale-invoice')}}" method="post">@csrf
                <div class="modal-body">
                    <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                    <div class="form-group">
                        <table class="table table-bordered">
                            <tr>
                                <th>Product Name</th>
                                <th>Sale Invoice Qty</th>
                            </tr>
                            @foreach($poDetail['orderitems'] as $orderItem)
                                <tr>
                                    <td>{{$orderItem['product']['product_name']}}</td>
                                    <td>{{$orderItem['actual_qty']}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Sale Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>